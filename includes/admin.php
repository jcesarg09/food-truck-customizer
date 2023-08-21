<?php
// Enqueue admin scripts and styles
function ftc_enqueue_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook && 'toplevel_page_ftc_customizer_settings' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('ftc_admin_script', plugins_url('js/admin.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_enqueue_style('ftc_admin_style', plugins_url('css/admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'ftc_enqueue_admin_scripts');


// Register the meta box for equipment image
function ftc_equipment_meta_box() {
    add_meta_box(
        'ftc_equipment_image',
        'Equipment Image',
        'ftc_equipment_image_callback',
        'ftc_equipment',
        'side'
    );
}
add_action('add_meta_boxes', 'ftc_equipment_meta_box');

// Display the meta box
function ftc_equipment_image_callback($post) {
    $image_url = get_post_meta($post->ID, '_ftc_equipment_image', true);
    echo '<input type="text" name="ftc_equipment_image" id="ftc_equipment_image" value="' . esc_attr($image_url) . '">';
    echo '<button type="button" id="ftc_upload_image_button">Upload Image</button>';
    // Add nonce for security
    wp_nonce_field('ftc_save_equipment_image', 'ftc_equipment_image_nonce');
}

// Save the meta box data
function ftc_save_equipment_image($post_id) {
    // Check nonce for security
    if (!isset($_POST['ftc_equipment_image_nonce']) || !wp_verify_nonce($_POST['ftc_equipment_image_nonce'], 'ftc_save_equipment_image')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['ftc_equipment_image'])) {
        update_post_meta($post_id, '_ftc_equipment_image', sanitize_text_field($_POST['ftc_equipment_image']));
    }
}
add_action('save_post_ftc_equipment', 'ftc_save_equipment_image');

// Additional admin functionalities can be added below

// Registering a custom post type for Equipment
function ftc_register_equipment_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Equipment',
        'menu_icon' => 'dashicons-hammer',
        'supports' => array('title', 'editor', 'thumbnail'),
    );
    register_post_type('ftc_equipment', $args);
}
add_action('init', 'ftc_register_equipment_post_type');


// Add settings page for the customizer
function ftc_customizer_settings_page() {
    add_menu_page('Food Truck Customizer Settings', 'FT Customizer Settings', 'manage_options', 'ftc_customizer_settings', 'ftc_customizer_settings_page_html', 'dashicons-admin-generic');
}
add_action('admin_menu', 'ftc_customizer_settings_page');

function ftc_customizer_settings_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting "ftc_customizer"
            settings_fields('ftc_customizer');
            // Output setting sections and their fields
            do_settings_sections('ftc_customizer');
            // Output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}
function ftc_register_customizer_settings() {
    // Register settings for "ftc_customizer_settings" page
    register_setting('ftc_customizer_settings', 'ftc_default_template');
    register_setting('ftc_customizer_settings', 'ftc_grid_size');
    register_setting('ftc_customizer_settings', 'ftc_default_orientation');

    // Register settings for "ftc_customizer" page
    register_setting('ftc_customizer', 'ftc_base_price');
    register_setting('ftc_customizer', 'ftc_tax_rate');

    // Register a new section in the "ftc_customizer_settings" page
    add_settings_section(
        'ftc_customizer_section',
        'Customizer Settings',
        'ftc_customizer_section_callback',
        'ftc_customizer_settings'
    );

    // Register fields in the "ftc_customizer_section" section, inside the "ftc_customizer_settings" page
    add_settings_field(
        'ftc_default_template_field',
        'Default Food Truck/Trailer Template',
        'ftc_default_template_field_callback',
        'ftc_customizer_settings',
        'ftc_customizer_section'
    );
    add_settings_field(
        'ftc_grid_size_field',
        'Grid Size',
        'ftc_grid_size_field_callback',
        'ftc_customizer_settings',
        'ftc_customizer_section'
    );
    add_settings_field(
        'ftc_default_orientation_field',
        'Default Orientation',
        'ftc_default_orientation_field_callback',
        'ftc_customizer_settings',
        'ftc_customizer_section'
    );

    // Register a new section in the "ftc_customizer" page
    add_settings_section(
        'ftc_pricing_section',
        'Pricing Settings',
        'ftc_pricing_section_callback',
        'ftc_customizer'
    );

    // Register fields in the "ftc_pricing_section" section, inside the "ftc_customizer" page
    add_settings_field(
        'ftc_base_price_field',
        'Base Price',
        'ftc_base_price_field_callback',
        'ftc_customizer',
        'ftc_pricing_section'
    );
    add_settings_field(
        'ftc_tax_rate_field',
        'Tax Rate',
        'ftc_tax_rate_field_callback',
        'ftc_customizer',
        'ftc_pricing_section'
    );
}

add_action('admin_init', 'ftc_register_customizer_settings');

// Register the grid size setting
function ftc_register_grid_size_setting() {
    register_setting('ftc_settings', 'ftc_grid_size', array(
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 20
    ));

    add_settings_field(
        'ftc_grid_size_field',
        'Grid Size (in pixels)',
        'ftc_grid_size_field_callback',
        'ftc_settings',
        'ftc_settings_section'
    );
}
add_action('admin_init', 'ftc_register_grid_size_setting');

// Callback function to display the grid size field
function ftc_grid_size_field_callback() {
    $grid_size = get_option('ftc_grid_size', 20);
    echo "<input type='number' name='ftc_grid_size' value='{$grid_size}' min='10' step='1' />";
}


function ftc_pricing_section_callback() {
    echo 'Set the base price and tax rate for the customizer.';
}

function ftc_base_price_field_callback() {
    // Get the value of the setting we've registered with register_setting()
    $setting = get_option('ftc_base_price');
    // Output the field
    echo "<input type='text' name='ftc_base_price' value='" . esc_attr($setting) . "'>";
}

function ftc_tax_rate_field_callback() {
    $setting = get_option('ftc_tax_rate');
    echo "<input type='text' name='ftc_tax_rate' value='" . esc_attr($setting) . "'> %";
}

// Registering a custom taxonomy for Equipment Categories
function ftc_register_equipment_category_taxonomy() {
    $labels = array(
        'name' => 'Equipment Categories',
        'singular_name' => 'Equipment Category',
        'search_items' => 'Search Equipment Categories',
        'all_items' => 'All Equipment Categories',
        'parent_item' => 'Parent Equipment Category',
        'parent_item_colon' => 'Parent Equipment Category:',
        'edit_item' => 'Edit Equipment Category',
        'update_item' => 'Update Equipment Category',
        'add_new_item' => 'Add New Equipment Category',
        'new_item_name' => 'New Equipment Category Name',
        'menu_name' => 'Equipment Categories',
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'equipment-category'),
    );

    register_taxonomy('ftc_equipment_category', array('ftc_equipment'), $args);
}
add_action('init', 'ftc_register_equipment_category_taxonomy');

// Register the meta box for equipment orientation
function ftc_equipment_orientation_meta_box() {
    add_meta_box(
        'ftc_equipment_orientation',
        'Equipment Orientation',
        'ftc_equipment_orientation_callback',
        'ftc_equipment',
        'side'
    );
}
add_action('add_meta_boxes', 'ftc_equipment_orientation_meta_box');

// Display the meta box
function ftc_equipment_orientation_callback($post) {
    $orientation = get_post_meta($post->ID, '_ftc_equipment_orientation', true);
    echo '<select name="ftc_equipment_orientation">';
    echo '<option value="front-facing" ' . selected($orientation, 'front-facing', false) . '>Front Facing</option>';
    echo '<option value="back-facing" ' . selected($orientation, 'back-facing', false) . '>Back Facing</option>';
    echo '<option value="both" ' . selected($orientation, 'both', false) . '>Both</option>';
    echo '</select>';
}

// Save the meta box data
function ftc_save_equipment_orientation($post_id) {
    if (isset($_POST['ftc_equipment_orientation'])) {
        update_post_meta($post_id, '_ftc_equipment_orientation', sanitize_text_field($_POST['ftc_equipment_orientation']));
    }
}
add_action('save_post_ftc_equipment', 'ftc_save_equipment_orientation');

// Register the meta boxes for equipment dimensions
function ftc_equipment_dimensions_meta_boxes() {
    add_meta_box(
        'ftc_equipment_width',
        'Equipment Width (m)',
        'ftc_equipment_width_callback',
        'ftc_equipment',
        'side'
    );
    add_meta_box(
        'ftc_equipment_height',
        'Equipment Height (m)',
        'ftc_equipment_height_callback',
        'ftc_equipment',
        'side'
    );
}
add_action('add_meta_boxes', 'ftc_equipment_dimensions_meta_boxes');

// Display the width meta box
function ftc_equipment_width_callback($post) {
    $width = get_post_meta($post->ID, '_ftc_equipment_width', true);
    echo '<input type="text" name="ftc_equipment_width" value="' . esc_attr($width) . '">';
}

// Display the height meta box
function ftc_equipment_height_callback($post) {
    $height = get_post_meta($post->ID, '_ftc_equipment_height', true);
    echo '<input type="text" name="ftc_equipment_height" value="' . esc_attr($height) . '">';
}

// Save the meta box data
function ftc_save_equipment_dimensions($post_id) {
    if (isset($_POST['ftc_equipment_width'])) {
        update_post_meta($post_id, '_ftc_equipment_width', sanitize_text_field($_POST['ftc_equipment_width']));
    }
    if (isset($_POST['ftc_equipment_height'])) {
        update_post_meta($post_id, '_ftc_equipment_height', sanitize_text_field($_POST['ftc_equipment_height']));
    }
}
add_action('save_post_ftc_equipment', 'ftc_save_equipment_dimensions');

add_action('admin_menu', 'ftc_customizer_settings_page');

add_action('admin_init', 'ftc_register_customizer_settings');

function ftc_customizer_section_callback() {
    echo 'Define the default settings for the customizer.';
}

function ftc_default_template_field_callback() {
    $setting = get_option('ftc_default_template');
    echo '<input type="text" name="ftc_default_template" value="' . esc_attr($setting) . '">';
    echo '<button type="button" id="ftc_upload_template_button">Upload Template</button>';
}

function ftc_grid_size_field_callback() {
    $setting = get_option('ftc_grid_size');
    echo '<input type="number" name="ftc_grid_size" value="' . esc_attr($setting) . '"> pixels';
}

function ftc_default_orientation_field_callback() {
    $setting = get_option('ftc_default_orientation');
    echo '<select name="ftc_default_orientation">';
    echo '<option value="front-facing" ' . selected($setting, 'front-facing', false) . '>Front Facing</option>';
    echo '<option value="back-facing" ' . selected($setting, 'back-facing', false) . '>Back Facing</option>';
    echo '<option value="both" ' . selected($setting, 'both', false) . '>Both</option>';
    echo '</select>';
}

// Add fields to the "ftc_pricing_section" section for Base Price and Tax Configuration
function ftc_register_customizer_settings() {
    // ... [Your existing code]

    // Register Base Price field
    add_settings_field(
        'ftc_base_price_field',
        'Base Price',
        'ftc_base_price_field_callback',
        'ftc_customizer',
        'ftc_pricing_section'
    );

    // Register Tax Rate field
    add_settings_field(
        'ftc_tax_rate_field',
        'Tax Rate',
        'ftc_tax_rate_field_callback', // This seems to be missing in your provided code
        'ftc_customizer',
        'ftc_pricing_section'
    );

    // Register a new section for WooCommerce Integration
    add_settings_section(
        'ftc_woocommerce_section',
        'WooCommerce Integration',
        'ftc_woocommerce_section_callback',
        'ftc_customizer'
    );

    // Register WooCommerce Product field
    add_settings_field(
        'ftc_wc_product_field',
        'Food Truck Base Product',
        'ftc_wc_product_field_callback',
        'ftc_customizer',
        'ftc_woocommerce_section'
    );
}

add_action('admin_init', 'ftc_register_customizer_settings');

// Callback function for WooCommerce section
function ftc_woocommerce_section_callback() {
    echo 'Settings related to integrating with WooCommerce.';
}

// Callback function for WooCommerce Product field
function ftc_wc_product_field_callback() {
    $selected_product = get_option('ftc_wc_product');
    $products = wc_get_products(array('limit' => -1));
    echo "<select name='ftc_wc_product'>";
    foreach ($products as $product) {
        echo "<option value='" . $product->get_id() . "' " . selected($selected_product, $product->get_id(), false) . ">" . $product->get_name() . "</option>";
    }
    echo "</select>";
}

// Registering a custom post type for User Designs
function ftc_register_user_design_post_type() {
    $args = array(
        'public' => false,  // Make it hidden from public queries
        'label'  => 'User Designs',
        'show_ui' => true, // Show in admin
        'menu_icon' => 'dashicons-layout',
        'supports' => array('title'),
    );
    register_post_type('ftc_user_designs', $args);
}
add_action('init', 'ftc_register_user_design_post_type');

// Add a submenu page under our main plugin menu for User Designs
function ftc_add_user_designs_submenu() {
    add_submenu_page(
        'ftc_customizer',
        'User Designs',
        'User Designs',
        'manage_options',
        'ftc_user_designs',
        'ftc_user_designs_callback'
    );
}
add_action('admin_menu', 'ftc_add_user_designs_submenu');

// Display the User Designs page
function ftc_user_designs_callback() {
    // Fetch all user designs
    $args = array(
        'post_type' => 'ftc_user_designs',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    echo '<div class="wrap">';
    echo '<h1>User Designs</h1>';
    
    if ($query->have_posts()) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Title</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<tr>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>';
            // Add edit, delete, and export actions here
            echo '<a href="' . get_edit_post_link() . '">Edit</a> | ';
            echo '<a href="' . get_delete_post_link() . '">Delete</a> | ';
            echo '<a href="' . admin_url('admin-ajax.php?action=export_design&design_id=' . get_the_ID()) . '">Export</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No designs found.</p>';
    }

    echo '</div>';
    wp_reset_postdata();
}

// Handle the export action
function ftc_export_user_design() {
    $design_id = $_GET['design_id'];
    // Fetch the design data and export it (e.g., as a JSON file or any other format)
    // ...
    wp_die();
}
add_action('wp_ajax_export_design', 'ftc_export_user_design');

// Handle the export action
function ftc_export_user_design() {
    $design_id = $_GET['design_id'];

    // Fetch the design data
    $design_post = get_post($design_id);
    $design_title = $design_post->post_title;
    $design_content = $design_post->post_content;

    // Create an image
    $width = 800;  // Define your desired width
    $height = 600;  // Define your desired height
    $image = imagecreatetruecolor($width, $height);

    // Set a background color (white in this case)
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bgColor);

    // Add text to the image (this is a basic example, you can customize further)
    $textColor = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 10, 10, $design_title, $textColor);
    imagestring($image, 3, 10, 40, $design_content, $textColor);

    // Output the image
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $design_title . '.png"');
    imagepng($image);

    // Clean up
    imagedestroy($image);
    exit;
}
add_action('wp_ajax_export_design', 'ftc_export_user_design');

function ftc_feedback_admin_page() {
    add_submenu_page(
        'ftc_customizer',
        'User Feedback',
        'Feedback',
        'manage_options',
        'ftc_feedback',
        'ftc_feedback_admin_page_html'
    );
}
add_action('admin_menu', 'ftc_feedback_admin_page');

function ftc_feedback_admin_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Display feedback entries
    $feedback_args = array(
        'post_type' => 'ftc_feedback',
        'posts_per_page' => -1,
    );
    $feedback_query = new WP_Query($feedback_args);
    ?>
    <div class="wrap">
        <h1>User Feedback</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Design ID</th>
                    <th>Feedback Content</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($feedback_query->have_posts()) {
                    $feedback_query->the_post();
                    $design_id = get_post_meta(get_the_ID(), '_ftc_design_id', true);
                    $feedback_content = get_the_content();
                    ?>
                    <tr>
                        <td><?= get_the_ID(); ?></td>
                        <td><?= get_the_date(); ?></td>
                        <td><?= $design_id; ?></td>
                        <td><?= $feedback_content; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
    wp_reset_postdata();
}

// File: admin.php

// Step 1: Register a new settings page
function ftc_customizer_settings_page() {
    add_menu_page('Food Truck Customizer', 'FT Customizer', 'manage_options', 'ftc_customizer', 'ftc_customizer_settings_page_html', 'dashicons-admin-generic');
}
add_action('admin_menu', 'ftc_customizer_settings_page');

// Step 2: Display the settings page content
function ftc_customizer_settings_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting "ftc_customizer"
            settings_fields('ftc_customizer');
            // Output setting sections and their fields
            do_settings_sections('ftc_customizer');
            // Output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Step 3: Register customizer settings
function ftc_register_customizer_settings() {
    // Register a new setting for "ftc_customizer" page
    register_setting('ftc_customizer', 'ftc_base_price');
    register_setting('ftc_customizer', 'ftc_tax_rate');

    // Register a new section in the "ftc_customizer" page
    add_settings_section(
        'ftc_pricing_section',
        'Pricing Settings',
        'ftc_pricing_section_callback',
        'ftc_customizer'
    );

    // Register fields in the "ftc_pricing_section" section, inside the "ftc_customizer" page
    add_settings_field(
        'ftc_base_price_field',
        'Base Price',
        'ftc_base_price_field_callback',
        'ftc_customizer',
        'ftc_pricing_section'
    );
    add_settings_field(
        'ftc_tax_rate_field',
        'Tax Rate',
        'ftc_tax_rate_field_callback',
        'ftc_customizer',
        'ftc_pricing_section'
    );
}
add_action('admin_init', 'ftc_register_customizer_settings');

// File: admin.php

// Step 2: Add meta box for template image
function ftc_template_image_meta_box() {
    add_meta_box(
        'ftc_template_image',
        'Default Food Truck/Trailer Template',
        'ftc_template_image_callback',
        'ftc_customizer',
        'side'
    );
}
add_action('add_meta_boxes', 'ftc_template_image_meta_box');

// Step 3: Display the template image meta box
function ftc_template_image_callback($post) {
    $image_url = get_post_meta($post->ID, '_ftc_template_image', true);
    echo '<input type="text" name="ftc_template_image" id="ftc_template_image" value="' . esc_attr($image_url) . '">';
    echo '<button type="button" id="ftc_upload_template_image_button">Upload Image</button>';
    // Add nonce for security
    wp_nonce_field('ftc_save_template_image', 'ftc_template_image_nonce');
}

// File: admin.php

// Step 4: Save the template image data
function ftc_save_template_image($post_id) {
    // Check nonce for security
    if (!isset($_POST['ftc_template_image_nonce']) || !wp_verify_nonce($_POST['ftc_template_image_nonce'], 'ftc_save_template_image')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['ftc_template_image'])) {
        update_post_meta($post_id, '_ftc_template_image', sanitize_text_field($_POST['ftc_template_image']));
    }
}
add_action('save_post_ftc_customizer', 'ftc_save_template_image');

// File: admin.php

// Step 5: Enqueue scripts and styles for template image upload
function ftc_enqueue_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('ftc_admin_script', plugins_url('js/admin.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_enqueue_style('ftc_admin_style', plugins_url('css/admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'ftc_enqueue_admin_scripts');

// File: admin.php

// Step 6: Register and render the settings fields
function ftc_register_settings() {
    // Register a new setting for "ftc_settings" page
    register_setting('ftc_settings', 'ftc_grid_size');
    register_setting('ftc_settings', 'ftc_default_orientation');

    // Register a new section in the "ftc_settings" page
    add_settings_section(
        'ftc_grid_section',
        'Grid Settings',
        'ftc_grid_section_callback',
        'ftc_settings'
    );

    // Register fields in the "ftc_grid_section" section, inside the "ftc_settings" page
    add_settings_field(
        'ftc_grid_size_field',
        'Grid Size',
        'ftc_grid_size_field_callback',
        'ftc_settings',
        'ftc_grid_section'
    );

    // Register a new section in the "ftc_settings" page
    add_settings_section(
        'ftc_orientation_section',
        'Orientation Settings',
        'ftc_orientation_section_callback',
        'ftc_settings'
    );

    // Register fields in the "ftc_orientation_section" section, inside the "ftc_settings" page
    add_settings_field(
        'ftc_default_orientation_field',
        'Default Orientation',
        'ftc_default_orientation_field_callback',
        'ftc_settings',
        'ftc_orientation_section'
    );
}
add_action('admin_init', 'ftc_register_settings');

// Callback functions for the settings sections
function ftc_grid_section_callback() {
    echo 'Set the size of the grid for the snap-to-grid functionality.';
}

function ftc_orientation_section_callback() {
    echo 'Define default orientations and constraints for equipment placement.';
}

// Callback functions for the settings fields
function ftc_grid_size_field_callback() {
    // Get the value of the setting we've registered with register_setting()
    $setting = get_option('ftc_grid_size');
    // Output the field
    echo "<input type='text' name='ftc_grid_size' value='" . esc_attr($setting) . "'> px";
}

function ftc_default_orientation_field_callback() {
    // Get the value of the setting we've registered with register_setting()
    $setting = get_option('ftc_default_orientation');
    // Output the field
    echo "<input type='checkbox' name='ftc_default_orientation' value='1' " . checked(1, $setting, false) . "> Enable Default Orientation";
}

// File: admin.php

// Step 7: Save the grid and orientation settings
function ftc_save_settings() {
    // Check nonce for security
    if (!isset($_POST['ftc_settings_nonce']) || !wp_verify_nonce($_POST['ftc_settings_nonce'], 'ftc_save_settings')) {
        return;
    }

    // Save the grid size setting
    if (isset($_POST['ftc_grid_size'])) {
        $grid_size = absint($_POST['ftc_grid_size']);
        update_option('ftc_grid_size', $grid_size);
    }

    // Save the default orientation setting
    if (isset($_POST['ftc_default_orientation'])) {
        $default_orientation = ($_POST['ftc_default_orientation'] === '1') ? true : false;
        update_option('ftc_default_orientation', $default_orientation);
    }
}
add_action('admin_init', 'ftc_save_settings');

// Enqueue admin scripts and styles
function ftc_enqueue_admin_scripts($hook) {
    // ... (existing code)
}
add_action('admin_enqueue_scripts', 'ftc_enqueue_admin_scripts');

// ... (Any existing code at the top of the file)

// Function to add a meta box for equipment details
function ftc_equipment_meta_box() {
    add_meta_box(
        'ftc_equipment_meta_box_id',           // Unique ID
        'Equipment Details',                   // Box title
        'ftc_equipment_meta_box_callback',     // Content callback
        'equipment'                            // Post type
    );
}
add_action('add_meta_boxes', 'ftc_equipment_meta_box');

// Callback function to display the content of the meta box
function ftc_equipment_meta_box_callback($post) {
    // Retrieve an existing value from the database
    $equipment_price = get_post_meta($post->ID, '_ftc_equipment_price', true);

    // Display the form, using the current value
    echo '<label for="ftc_equipment_price">Price</label>';
    echo '<input type="text" id="ftc_equipment_price" name="ftc_equipment_price" value="' . esc_attr($equipment_price) . '">';

    // Add a nonce field so we can check for it later
    wp_nonce_field('ftc_equipment_data', 'ftc_equipment_nonce');
}

// ... (Any existing code at the bottom of the file)


// Registering a custom post type for Equipment
function ftc_register_equipment_post_type() {
    // ... (existing code)
}
add_action('init', 'ftc_register_equipment_post_type');

// Food Truck Customizer Settings Page
function ftc_customizer_settings_page() {
    add_menu_page('Food Truck Customizer', 'FT Customizer', 'manage_options', 'ftc_customizer', 'ftc_customizer_settings_page_html', 'dashicons-admin-generic');
}
add_action('admin_menu', 'ftc_customizer_settings_page');

function ftc_customizer_settings_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting "ftc_customizer"
            settings_fields('ftc_customizer');
            // Output setting sections and their fields
            do_settings_sections('ftc_customizer');
            // Output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function ftc_register_customizer_settings() {
    // Register a new setting for "ftc_customizer" page
    register_setting('ftc_customizer', 'ftc_base_price');
    register_setting('ftc_customizer', 'ftc_tax_rate');

    // Register a new section in the "ftc_customizer" page for Pricing and Tax Settings
    add_settings_section(
        'ftc_pricing_section',
        'Pricing and Tax Settings',
        'ftc_pricing_section_callback',
        'ftc_customizer'
    );

    // Register fields in the "ftc_pricing_section" section, inside the "ftc_customizer" page
    add_settings_field(
        'ftc_base_price_field',
        'Base Price',
        'ftc_base_price_field_callback',
        'ftc_customizer',
        'ftc_pricing_section'
    );
    add_settings_field(
        'ftc_tax_rate_field',
        'Tax Rate',
        'ftc_tax_rate_field_callback',
        'ftc_customizer',
        'ftc_pricing_section'
    );
}
add_action('admin_init', 'ftc_register_customizer_settings');

function ftc_pricing_section_callback() {
    echo 'Set the base price and tax rate for the customizer.';
}

function ftc_base_price_field_callback() {
    // Get the value of the setting we've registered with register_setting()
    $setting = get_option('ftc_base_price');
    // Output the field
    echo "<input type='text' name='ftc_base_price' value='" . esc_attr($setting) . "'>";
}

function ftc_tax_rate_field_callback() {
    $setting = get_option('ftc_tax_rate');
    echo "<input type='text' name='ftc_tax_rate' value='" . esc_attr($setting) . "'> %";
}

// Add a new menu item in the WordPress admin sidebar for the customizer interface
function ftc_customizer_admin_menu() {
    add_menu_page(
        'Food Truck Customizer',
        'FT Customizer',
        'manage_options',
        'ftc_customizer',
        'ftc_display_customizer_interface',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'ftc_customizer_admin_menu');

// Display the customizer interface in the WordPress admin area
function ftc_display_customizer_interface() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Load the customizer interface template
    include_once(plugin_dir_path(__FILE__) . 'templates/customizer-interface.php');
}

// Save the customizer data from the AJAX request
function ftc_save_customizer_data() {
    if (isset($_POST['data'])) {
        $customizer_data = $_POST['data'];

        // Save the customizer data to the WordPress database
        $post_id = wp_insert_post(array(
            'post_title' => 'Food Truck Design', // Set a default title for the design
            'post_type' => 'ftc_food_truck_design',
            'post_status' => 'publish',
        ));

        if (!is_wp_error($post_id)) {
            // Save the customizer data as post meta
            update_post_meta($post_id, '_ftc_customizer_data', $customizer_data);
        }

        // Return a success response
        wp_send_json_success();
    }

    // If no data is sent, return an error response
    wp_send_json_error();
}
add_action('wp_ajax_ftc_save_customizer_data', 'ftc_save_customizer_data');

