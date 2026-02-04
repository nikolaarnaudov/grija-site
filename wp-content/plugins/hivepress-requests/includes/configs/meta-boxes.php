<?php
/**
 * Meta boxes configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'request_settings' => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'request',
		'model'  => 'request',
		'fields' => [
			'user'   => [
				'label'    => hivepress()->translator->get_string( 'user' ),
				'type'     => 'select',
				'options'  => 'users',
				'source'   => hivepress()->router->get_url( 'users_resource' ),
				'required' => true,
				'_alias'   => 'post_author',
				'_order'   => 10,
			],

			'vendor' => [
				'label'       => hivepress()->translator->get_string( 'vendor' ),
				'type'        => 'select',
				'options'     => 'posts',
				'option_args' => [ 'post_type' => 'hp_vendor' ],
				'source'      => hivepress()->router->get_url( 'vendors_resource' ),
				'disabled'    => true,
				'_alias'      => 'post_parent',
				'_order'      => 20,
			],
		],
	],

	'request_images'   => [
		'title'  => hivepress()->translator->get_string( 'images' ),
		'screen' => 'request',
		'model'  => 'request',

		'fields' => [
			'images' => [
				'caption'   => hivepress()->translator->get_string( 'select_images' ),
				'type'      => 'attachment_upload',
				'multiple'  => true,
				'max_files' => 10,
				'formats'   => [ 'jpg', 'jpeg', 'png' ],
				'_order'    => 10,
			],
		],
	],
];
