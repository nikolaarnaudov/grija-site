<?php
// Enqueue parent theme styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('experthive-child', get_stylesheet_uri(), ['experthive']);
});

// Register Custom Attributes programmaticall upon theme switch
add_action('after_switch_theme', function () {
    if (!function_exists('hivepress'))
        return;

    // Define attributes to create
    $attributes = [
        'years_experience' => [
            'label' => 'Years of Experience',
            'type' => 'number',
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
            'indexable' => true,
            'display_areas' => ['view_block_primary', 'view_page_primary'], // Show in primary area of card/page
        ],
        'city' => [
            'label' => 'City',
            'type' => 'select', // Select allows predefined list, easy filtering
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
            'indexable' => true,
            'display_areas' => ['view_block_secondary', 'view_page_secondary'],
        ],
        'hourly_rate' => [
            'label' => 'Hourly Rate ($)',
            'type' => 'number',
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
            'indexable' => true,
            'display_areas' => ['view_block_primary', 'view_page_primary'],
        ]
    ];

    foreach ($attributes as $name => $args) {
        $meta_key = 'hp_vendor_' . $name;

        // Simple check if attribute likely exists as a post (not perfect but avoids huge duplication on every activation)
        // A better way in prod is to use a flag in functionality plugin, but for theme setup this works.
        $existing = get_posts([
            'post_type' => 'hp_attribute',
            'name' => $name, // Slug
            'post_status' => 'publish',
            'numberposts' => 1
        ]);

        if (empty($existing)) {
            $attribute_id = wp_insert_post([
                'post_type' => 'hp_attribute',
                'post_title' => $args['label'],
                'post_name' => $name,
                'post_status' => 'publish',
            ]);

            if ($attribute_id) {
                // Update meta for the attribute configuration
                update_post_meta($attribute_id, 'hp_type', $args['type']);
                update_post_meta($attribute_id, 'hp_filterable', $args['filterable']);
                update_post_meta($attribute_id, 'hp_sortable', $args['sortable']);
                update_post_meta($attribute_id, 'hp_indexable', $args['indexable']); // Crucial for search

                // Assign to Vendor model (scope)
                // HivePress models are terms in 'hp_models' taxonomy usually, or just meta.
                // In latest HivePress, scopings are stored in meta 'hp_models' as array
                update_post_meta($attribute_id, 'hp_models', ['vendor']);

                // Set Display Areas
                update_post_meta($attribute_id, 'hp_display_areas', $args['display_areas']);
            }
        }
    }
});

// Customize Vendor Block (Card) Layout via Hooks
// We want to ensure specific fields appear.
// By registering attributes with 'display_areas' above, HivePress SHOULD automatically render them.
// If customization of the layout order is needed, we can intervene in:
// hivepress/v1/templates/vendor_view_block

add_filter('hivepress/v1/templates/vendor_view_block', function ($template) {
    // We want to ensure the order: Image -> Title -> Rating -> Categories -> City -> Experience -> Price
    // Most are default. We just need to make sure our custom attributes are there.
    // The attributes registered with 'display_areas' => ['view_block_primary'] usually appear.

    // Let's force some layout changes if needed. 
    // For now, let's verify if 'view_block_primary' handles it.
    // The user wants: Name, Validation checkmark, Years of experience, City, Category, Price.

    return hivepress()->template->merge_blocks($template, [
        'vendor_content' => [
            'blocks' => [
                'vendor_details_primary' => [
                    'type' => 'container',
                    'attributes' => ['class' => ['hp-vendor__details', 'hp-vendor__details--primary']],
                    'blocks' => [
                        'vendor_name' => [
                            'type' => 'vendor_name',
                            '_order' => 10,
                        ],
                        'vendor_verified' => [ // This is the checkmark
                            'type' => 'vendor_verified_badge',
                            '_order' => 20,
                        ],
                        // Force City display. 
                        // Attributes usually auto-display if 'display_areas' is set, but let's be explicit.
                        'vendor_location' => [
                            'type' => 'part',
                            'path' => 'vendor/view/block/vendor-location', // We'll create this part if needed, or use generic
                            '_order' => 25,
                        ],
                        'vendor_attributes_primary' => [ // This holds our custom attributes
                            'type' => 'part',
                            'path' => 'vendor/view/vendor-attributes-primary',
                            '_order' => 30,
                        ],
                    ],
                    '_order' => 20,
                ],
            ],
        ],
    ]);
});

// Since we are referencing a custom part for location, let's just make sure the attribute itself renders.
// Actually, 'hp_vendor_city' is the attribute.
add_filter('hivepress/v1/models/vendor/attributes', function ($attributes) {
    if (isset($attributes['city'])) {
        $attributes['city']['display_areas'] = ['view_block_primary', 'view_page_primary'];
    }
    return $attributes;
}, 1000);
