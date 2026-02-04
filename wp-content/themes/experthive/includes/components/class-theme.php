<?php
/**
 * Theme component.
 *
 * @package HiveTheme\Components
 */

namespace HiveTheme\Components;

use HiveTheme\Helpers as ht;
use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Theme component class.
 *
 * @class Theme
 */
final class Theme extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Render hero content.
		add_filter( 'hivetheme/v1/areas/site_hero', [ $this, 'render_hero_content' ] );

		// Alter styles.
		add_filter( 'hivetheme/v1/styles', [ $this, 'alter_styles' ] );
		add_filter( 'hivepress/v1/styles', [ $this, 'alter_styles' ] );

		// Check HivePress status.
		if ( ! ht\is_plugin_active( 'hivepress' ) ) {
			return;
		}

		// Alter blocks.
		add_filter( 'hivepress/v1/blocks/vendors/meta', [ $this, 'alter_slider_block_meta' ] );
		add_filter( 'hivepress/v1/blocks/testimonials/meta', [ $this, 'alter_slider_block_meta' ] );

		add_filter( 'hivepress/v1/blocks/vendors', [ $this, 'alter_slider_block_args' ], 10, 2 );
		add_filter( 'hivepress/v1/blocks/testimonials', [ $this, 'alter_slider_block_args' ], 10, 2 );

		// Alter models.
		add_filter( 'hivepress/v1/models/listing_category', [ $this, 'alter_listing_category_fields' ] );

		if ( is_admin() ) {

			// Alter meta boxes.
			add_filter( 'hivepress/v1/meta_boxes/listing_category_settings', [ $this, 'alter_listing_category_settings' ] );
		} else {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_category_view_block', [ $this, 'alter_listing_category_view_block' ], 100 );

			add_filter( 'hivepress/v1/templates/listing_categories_view_page', [ $this, 'alter_listing_categories_view_page' ], 100 );

			add_filter( 'hivepress/v1/templates/listing_view_block/blocks', [ $this, 'alter_listing_view_block' ], 100, 2 );
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ], 100 );

			add_filter( 'hivepress/v1/templates/listings_view_page', [ $this, 'alter_listings_view_page' ], 100 );
			add_filter( 'hivepress/v1/templates/vendor_view_page', [ $this, 'alter_listings_view_page' ], 100 );
			add_filter( 'hivepress/v1/templates/listings_favorite_page', [ $this, 'alter_listings_view_page' ], 100 );

			add_filter( 'hivepress/v1/templates/request_view_block', [ $this, 'alter_request_view_block' ], 100 );
			add_filter( 'hivepress/v1/templates/request_view_page', [ $this, 'alter_request_view_page' ], 100 );

			add_filter( 'hivepress/v1/templates/vendor_view_block', [ $this, 'alter_vendor_view_block' ], 100 );

			add_filter( 'hivepress/v1/templates/review_view_block', [ $this, 'alter_review_view_block' ], 100 );
		}

		parent::__construct( $args );
	}

	/**
	 * Renders hero content.
	 *
	 * @param string $output Hero content.
	 * @return string
	 */
	public function render_hero_content( $output ) {
		$classes = [];

		// Render header.
		if ( is_page() ) {

			// Get content.
			$content = '';

			$parts = get_extended( get_post_field( 'post_content' ) );

			if ( $parts['extended'] ) {
				$content = apply_filters( 'the_content', $parts['main'] );

				$classes[] = 'header-hero--large';
			} else {
				$classes[] = 'header-hero--title';
			}

			// Check title.
			$title = get_the_ID() !== absint( get_option( 'page_on_front' ) );

			if ( ht\is_plugin_active( 'hivepress' ) ) {

				// @todo change condition when common category pages are added.
				$title = $title && ! hivepress()->request->get_context( 'post_query' ) && hivepress()->router->get_current_route_name() !== 'listings_view_page';
			}

			if ( $content ) {

				// Get images.
				$images = [];

				if ( is_front_page() && get_theme_mod( 'header_images', true ) ) {
					$images = get_attached_media( 'image' );
				}

				// Render part.
				if ( count( $images ) >= 3 ) {
					$output .= hivetheme()->template->render_part(
						'templates/page/home/page-header',
						[
							'content' => $content,
							'images'  => array_slice( $images, 0, 3 ),
						]
					);
				} else {
					$output .= $content;
				}
			} elseif ( $title ) {
				$output .= hivetheme()->template->render_part( 'templates/page/page-title' );
			}
		} elseif ( is_singular( 'post' ) ) {

			// Add classes.
			$classes = array_merge(
				$classes,
				[
					'post',
					'post--single',
				]
			);

			if ( has_post_thumbnail() ) {
				$classes[] = 'has-post-thumbnail';
			}

			// Render part.
			$output .= hivetheme()->template->render_part( 'templates/post/single/post-header' );
		} elseif ( ht\is_plugin_active( 'hivepress' ) && is_tax( 'hp_listing_category' ) ) {

			// Add classes.
			$classes = array_merge(
				$classes,
				[
					'hp-listing-category',
					'hp-listing-category--view-page',
				]
			);

			// Render part.
			$output .= hivetheme()->template->render_part(
				'hivepress/listing-category/view/page/listing-category-header',
				[
					'listing_category' => \HivePress\Models\Listing_Category::query()->get_by_id( get_queried_object() ),
				]
			);
		}

		// Add wrapper.
		if ( $output ) {
			$output = hivetheme()->template->render_part(
				'templates/page/page-header',
				[
					'class'   => implode( ' ', $classes ),
					'content' => $output,
				]
			);
		}

		return $output;
	}

	/**
	 * Alters styles.
	 *
	 * @param array $styles Styles.
	 * @return array
	 */
	public function alter_styles( $styles ) {
		$styles['fontawesome']['src'] = hivetheme()->get_url( 'parent' ) . '/assets/css/fontawesome.min.css';

		unset( $styles['fontawesome_solid'] );

		return $styles;
	}

	/**
	 * Alters slider block meta.
	 *
	 * @param array $meta Block meta.
	 * @return array
	 */
	public function alter_slider_block_meta( $meta ) {
		$meta['settings']['slider'] = [
			'label'  => esc_html__( 'Display in a slider', 'experthive' ),
			'type'   => 'checkbox',
			'_order' => 100,
		];

		return $meta;
	}

	/**
	 * Alters slider block arguments.
	 *
	 * @param array  $args Block arguments.
	 * @param object $block Block object.
	 * @return array
	 */
	public function alter_slider_block_args( $args, $block ) {
		if ( hp\get_array_value( $args, 'slider' ) ) {
			$attributes = [
				'data-component' => 'slider',
				'class'          => [ 'hp-' . $block::get_meta( 'name' ) . '--slider', 'alignfull' ],
			];

			if ( $block::get_meta( 'name' ) === 'vendors' ) {
				$attributes['data-type'] = 'carousel';
			}

			$args['attributes'] = hp\merge_arrays(
				hp\get_array_value( $args, 'attributes', [] ),
				$attributes
			);
		}

		return $args;
	}

	/**
	 * Alters listing category fields.
	 *
	 * @param array $model Model.
	 * @return array
	 */
	public function alter_listing_category_fields( $model ) {
		$model['fields']['icon'] = [
			'type'      => 'select',
			'options'   => 'icons',
			'_external' => true,
		];

		return $model;
	}

	/**
	 * Alters listing category settings.
	 *
	 * @param array $meta_box Meta box.
	 * @return array
	 */
	public function alter_listing_category_settings( $meta_box ) {
		$meta_box['fields']['icon'] = [
			'label'   => esc_html__( 'Icon', 'experthive' ),
			'type'    => 'select',
			'options' => 'icons',
			'_order'  => 5,
		];

		unset( $meta_box['fields']['image'] );

		return $meta_box;
	}

	/**
	 * Alters listing category view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_category_view_block( $template ) {
		return hivepress()->template->merge_blocks(
			$template,
			[
				'listing_category_details_primary' => [
					'_order' => 100,

					'blocks' => [
						'listing_category_link' => [
							'type'   => 'part',
							'path'   => 'listing-category/view/block/listing-category-link',
							'_order' => 100,
						],
					],
				],
			]
		);
	}

	/**
	 * Alters listing categories view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_categories_view_page( $template ) {
		return hivepress()->template->merge_blocks(
			$template,
			[
				'listing_categories' => [
					'columns' => 4,
				],
			]
		);
	}

	/**
	 * Alters listing view block.
	 *
	 * @param array  $blocks Template blocks.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_listing_view_block( $blocks, $template ) {

		// Remove blocks.
		hivepress()->template->fetch_block( $blocks, 'listing_created_date' );

		// Set blocks.
		$new_blocks = [
			'listing_image'                => [
				'path' => 'listing/view/block/vendor-image',
			],

			'listing_title'                => [
				'blocks' => [
					'listing_featured_badge' => array_merge(
						hivepress()->template->fetch_block( $blocks, 'listing_featured_badge' ),
						[
							'_order' => 30,
						]
					),
				],
			],

			'listing_attributes_secondary' => [
				'type'       => 'container',

				'attributes' => [
					'class' => [ 'hp-listing__attributes', 'hp-listing__attributes--secondary' ],
				],

				'blocks'     => [
					'listing_attributes_secondary_loop' => [
						'type'   => 'part',
						'path'   => 'listing/view/block/listing-attributes-secondary',
						'_order' => 20,
					],
				],
			],
		];

		if ( hivepress()->get_version( 'geolocation' ) ) {

			// Add blocks.
			$location = hivepress()->template->fetch_block( $blocks, 'listing_location' );

			if ( $location ) {
				$new_blocks = hivepress()->template->merge_blocks(
					$new_blocks,
					[
						'listing_attributes_secondary' => [
							'blocks' => [
								'listing_location' => array_merge(
									$location,
									[
										'_order' => 10,
									]
								),
							],
						],
					]
				);
			}
		}

		if ( get_option( 'hp_vendor_enable_display' ) ) {

			// Get listing.
			$listing = $template->get_context( 'listing' );

			if ( $listing ) {

				// Get vendor.
				$vendor = $listing->get_vendor();

				if ( $vendor && $vendor->get_status() === 'publish' ) {

					// Set context.
					$template->set_context( 'vendor', $vendor );

					// Add blocks.
					$new_blocks['listing_details_primary'] = [
						'blocks' => [
							'listing_vendor' => [
								'type'   => 'part',
								'path'   => 'listing/view/block/listing-vendor',
								'_order' => 5,
							],
						],
					];
				}
			}
		}

		return hivepress()->template->merge_blocks( $blocks, $new_blocks );
	}

	/**
	 * Alters listing view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_page( $template ) {

		// Remove blocks.
		hivepress()->template->fetch_block( $template, 'listing_created_date' );

		// Add blocks.
		$template = hivepress()->template->merge_blocks(
			$template,
			[
				'related_listings'             => [
					'columns' => 1,
				],

				'listing_attributes_secondary' => [
					'type'       => 'container',
					'_order'     => 25,

					'attributes' => [
						'class' => [ 'hp-listing__attributes', 'hp-listing__attributes--secondary' ],
					],

					'blocks'     => [
						'listing_attributes_secondary_loop' => [
							'type'   => 'part',
							'path'   => 'listing/view/page/listing-attributes-secondary',
							'_order' => 20,
						],
					],
				],
			]
		);

		if ( hivepress()->get_version( 'geolocation' ) ) {
			$location = hivepress()->template->fetch_block( $template, 'listing_location' );

			if ( $location ) {
				$template = hivepress()->template->merge_blocks(
					$template,
					[
						'listing_attributes_secondary' => [
							'blocks' => [
								'listing_location' => array_merge(
									$location,
									[
										'_order' => 10,
									]
								),
							],
						],
					]
				);
			}
		}

		return $template;
	}

	/**
	 * Alters listings view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listings_view_page( $template ) {
		return hivepress()->template->merge_blocks(
			$template,
			[
				'listings' => [
					'columns' => 1,
				],
			]
		);
	}

	/**
	 * Alters request view block.
	 *
	 * @param object $template Template arguments.
	 * @return array
	 */
	public function alter_request_view_block( $template ) {
		$template = hivepress()->template->merge_blocks(
			$template,
			[
				'request_details_primary'      => [
					'blocks' => [
						'request_user' => [
							'type'   => 'part',
							'path'   => 'request/view/block/request-user',
							'_order' => 5,
						],
					],
				],

				'request_attributes_secondary' => [
					'type'       => 'container',

					'attributes' => [
						'class' => [ 'hp-listing__attributes', 'hp-listing__attributes--secondary' ],
					],

					'blocks'     => [
						'request_attributes_secondary_loop' => [
							'type'   => 'part',
							'path'   => 'request/view/block/request-attributes-secondary',
							'_order' => 20,
						],
					],
				],
			]
		);

		if ( hivepress()->get_version( 'geolocation' ) ) {
			$location = hivepress()->template->fetch_block( $template, 'request_location' );

			if ( $location ) {
				$template = hivepress()->template->merge_blocks(
					$template,
					[
						'request_attributes_secondary' => [
							'blocks' => [
								'request_location' => array_merge(
									$location,
									[
										'_order' => 10,
									]
								),
							],
						],
					]
				);
			}
		}

		return $template;
	}

	/**
	 * Alters request view page.
	 *
	 * @param object $template Template arguments.
	 * @return array
	 */
	public function alter_request_view_page( $template ) {
		$template = hivepress()->template->merge_blocks(
			$template,
			[
				'request_attributes_secondary' => [
					'type'       => 'container',
					'_order'     => 25,

					'attributes' => [
						'class' => [ 'hp-listing__attributes', 'hp-listing__attributes--secondary' ],
					],

					'blocks'     => [
						'request_attributes_secondary_loop' => [
							'type'   => 'part',
							'path'   => 'request/view/page/request-attributes-secondary',
							'_order' => 20,
						],
					],
				],
			]
		);

		if ( hivepress()->get_version( 'geolocation' ) ) {
			$location = hivepress()->template->fetch_block( $template, 'request_location' );

			if ( $location ) {
				$template = hivepress()->template->merge_blocks(
					$template,
					[
						'request_attributes_secondary' => [
							'blocks' => [
								'request_location' => array_merge(
									$location,
									[
										'_order' => 10,
									]
								),
							],
						],
					]
				);
			}
		}

		return $template;
	}

	/**
	 * Alters vendor view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_vendor_view_block( $template ) {
		if ( hivepress()->get_version( 'reviews' ) ) {
			$template = hivepress()->template->merge_blocks(
				$template,
				[
					'vendor_rating' => [
						'_order' => 1,
					],
				]
			);
		}

		return $template;
	}

	/**
	 * Alters review view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_review_view_block( $template ) {
		return hivepress()->template->merge_blocks(
			$template,
			[
				'review_rating' => [
					'_order' => 100,
				],
			]
		);
	}
}
