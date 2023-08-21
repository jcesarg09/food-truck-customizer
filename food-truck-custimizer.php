<?php
/*
Plugin Name: Food Truck Customizer
Description: Allows customers to visually customize and build their own food truck or trailer.
Version: 1.0
Author: July Csar
*/

// Define constants
define('FTC_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-food-truck-equipment.php';
require_once FTC_PLUGIN_DIR . 'includes/admin.php';
require_once FTC_PLUGIN_DIR . 'includes/frontend.php';

function ftc_register_feedback_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Feedback',
        'menu_icon' => 'dashicons-format-chat',
        'supports' => array('title', 'editor'),
    );
    register_post_type('ftc_feedback', $args);
}
add_action('init', 'ftc_register_feedback_post_type');

// Add a new action hook for the settings page
function ftc_settings_page() {
    add_menu_page('Food Truck Customizer Settings', 'FTC Settings', 'manage_options', 'ftc_settings', 'ftc_settings_page_html', 'dashicons-admin-generic');
}
add_action('admin_menu', 'ftc_settings_page');

function ftc_settings_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

     // Load the settings page template
     include plugin_dir_path(__FILE__) . 'templates/settings.php';
    }
    
    // Corrected the register_activation_hook to reference the main plugin file
    register_activation_hook( plugin_basename( __DIR__ ) . '/food-truck-customizer.php', 'ftc_activate_plugin' );

    
    // ... (rest of the code remains unchanged)

    // Add nonce field for security
    $nonce = wp_create_nonce('ftc_save_settings');
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output nonce field
            echo '<input type="hidden" name="ftc_settings_nonce" value="' . esc_attr($nonce) . '">';
            // Output security fields for the registered setting "ftc_settings"
            settings_fields('ftc_settings');
            // Output setting sections and their fields
            do_settings_sections('ftc_settings');
            // Output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>

// Function to add item to WooCommerce cart
function add_equipment_items_to_cart() {
    $equipment_list = $_SESSION['equipment_list'];

    foreach ($equipment_list as $item) {
        $product_id = absint($item['id']);
        $item_name = sanitize_text_field($item['name']);
        $item_quantity = absint($item['quantity']);
        $item_price = floatval($item['price']);

        // Check if the product exists
        $product = wc_get_product($product_id);

        if ($product) {
            // Add the item to the cart
            $cart_item_data = array(
                'name' => $item_name,
                'quantity' => $item_quantity,
                'data' => $product,
                'price' => $item_price
            );
            WC()->cart->add_to_cart($product_id, $item_quantity, 0, array(), $cart_item_data);
        }
    }
}
add_action('woocommerce_after_checkout_form', 'add_equipment_items_to_cart');

// Start the session
function ftc_start_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'ftc_start_session');

// Enqueue frontend scripts and styles
function ftc_enqueue_frontend_scripts() {
    // Enqueue your other scripts and styles here
    
    // Enqueue the customizer interface script and style
    wp_enqueue_script('ftc_customizer_js', plugins_url('assets/js/customizer.js', __FILE__), array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable'), '1.0.0', true);
    wp_enqueue_style('ftc_customizer_css', plugins_url('assets/css/customizer.css', __FILE__));

    // Localize script with custom settings
    $custom_settings = array(
        'gridSize' => get_option('ftc_grid_size', 20)
    );
    wp_localize_script('ftc_customizer_js', 'ftcSettings', $custom_settings);

}
add_action('wp_enqueue_scripts', 'ftc_enqueue_frontend_scripts');

function ftc_enqueue_admin_scripts() {
    // Enqueue the WordPress Media Uploader scripts
    wp_enqueue_media();

    // Enqueue the admin.js script
    wp_enqueue_script('ftc_admin_js', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), '1.0.0', true);
}

add_action('admin_enqueue_scripts', 'ftc_enqueue_admin_scripts');
