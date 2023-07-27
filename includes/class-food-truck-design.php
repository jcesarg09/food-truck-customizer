// TODO: Implement customizer design functionality

<?php
class Food_Truck_Design {
    // Register the custom post type for food truck designs
    public static function register_design_post_type() {
        $labels = array(
            'name'               => _x('Food Truck Designs', 'post type general name', 'textdomain'),
            'singular_name'      => _x('Food Truck Design', 'post type singular name', 'textdomain'),
            'menu_name'          => _x('Designs', 'admin menu', 'textdomain'),
            'name_admin_bar'     => _x('Food Truck Design', 'add new on admin bar', 'textdomain'),
            'add_new'            => _x('Add New', 'food truck design', 'textdomain'),
            'add_new_item'       => __('Add New Food Truck Design', 'textdomain'),
            'new_item'           => __('New Food Truck Design', 'textdomain'),
            'edit_item'          => __('Edit Food Truck Design', 'textdomain'),
            'view_item'          => __('View Food Truck Design', 'textdomain'),
            'all_items'          => __('All Food Truck Designs', 'textdomain'),
            'search_items'       => __('Search Food Truck Designs', 'textdomain'),
            'parent_item_colon'  => __('Parent Food Truck Designs:', 'textdomain'),
            'not_found'          => __('No food truck designs found.', 'textdomain'),
            'not_found_in_trash' => __('No food truck designs found in Trash.', 'textdomain'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false, // Set to false so it's not visible to public users
            'publicly_queryable' => false, // Set to false to disable front-end query
            'show_ui'            => true,
            'show_in_menu'       => 'ftc_customizer', // Add this custom post type to the customizer menu
            'query_var'          => false, // Set to false to disable front-end query
            'rewrite'            => false, // Set to false to disable rewriting URLs
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-admin-appearance', // Use a custom icon or any dashicon
        );

        register_post_type('ftc_food_truck_design', $args);
    }
}

// Register the custom post type on plugin activation
function ftc_activate_plugin() {
    Food_Truck_Design::register_design_post_type();
}
register_activation_hook(__FILE__, 'ftc_activate_plugin');
