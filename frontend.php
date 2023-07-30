<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue frontend scripts and styles
function ftc_enqueue_frontend_scripts() {
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
    wp_enqueue_script('ftc_customizer_js', plugins_url('js/customizer.js', __FILE__), array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable'), '1.0.0', true);
    wp_enqueue_style('tailwind-css', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
}
add_action('wp_enqueue_scripts', 'ftc_enqueue_frontend_scripts');

// Display the visual customizer on the product page
function ftc_display_customizer() {
    // Query the equipment from the database
    $args = array(
        'post_type' => 'ftc_equipment',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        echo '<div id="ftc_customizer" class="w-full max-w-screen-xl mx-auto relative p-4 border-2 border-dashed">'; // Tailwind classes
        while ($query->have_posts()) {
            $query->the_post();
            $dimensions = get_post_meta(get_the_ID(), '_ftc_dimensions', true);
            $price = get_post_meta(get_the_ID(), '_ftc_price', true);
            echo '<div class="equipment-item w-24 h-24 float-left mr-2.5 bg-gray-200 p-2 cursor-pointer" data-id="'. get_the_ID() .'">'; // Tailwind classes
            // Display each equipment with its details
            echo '</div>';
            // Add a section to display the itemized list
    echo '<div id="itemized-list" class="mt-4"></div>'; // Tailwind classes

        }
        echo '</div>';
    }
    wp_reset_postdata();
}

// Shortcode to display the customizer
function ftc_customizer_shortcode() {
    ob_start();
    ftc_display_customizer();
    return ob_get_clean();
}
add_shortcode('ftc_customizer', 'ftc_customizer_shortcode');

// Ajax handler for saving the design
function ftc_handle_ajax_save_design() {
    // Handle the AJAX request to save the design
    // You'll need to implement the logic to save the design to the database
    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_ftc_save_design', 'ftc_handle_ajax_save_design');
add_action('wp_ajax_nopriv_ftc_save_design', 'ftc_handle_ajax_save_design');

// Ajax handler for loading a saved design
function ftc_handle_ajax_load_design() {
    // Handle the AJAX request to load a saved design
    // You'll need to implement the logic to fetch the saved design from the database
    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_ftc_load_design', 'ftc_handle_ajax_load_design');
add_action('wp_ajax_nopriv_ftc_load_design', 'ftc_handle_ajax_load_design');

// Additional helper functions, filter hooks, action hooks, and templates can be added below as needed

add_action('wp_ajax_add_custom_product_to_cart', 'ftc_add_custom_product_to_cart');
add_action('wp_ajax_nopriv_add_custom_product_to_cart', 'ftc_add_custom_product_to_cart');

function ftc_add_custom_product_to_cart() {
    $designDetails = $_POST['designDetails'];
    $equipmentList = $_POST['equipmentList'];
    $totalCost = $_POST['totalCost'];

    // Get the WooCommerce product ID for the custom food truck
    $product_id = get_option('ftc_wc_product');

    // Add the product to the cart with the custom price and details
    $cart_item_data = array(
        'custom_data' => array(
            'designDetails' => $designDetails,
            'equipmentList' => $equipmentList
        )
    );
    WC()->cart->add_to_cart($product_id, 1, '', '', $cart_item_data);

    // Set the custom price
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            $cart_item['data']->set_price($totalCost);
        }
    }

    echo json_encode(array('success' => true));
    wp_die();
}

function ftc_feedback_form() {
    ?>
    <h2>Feedback</h2>
    <form method="post">
        <label for="feedback_content">Your Feedback:</label>
        <textarea name="feedback_content" id="feedback_content" cols="30" rows="5"></textarea>
        <input type="hidden" name="design_id" value="<?= get_the_ID(); ?>">
        <?php wp_nonce_field('ftc_submit_feedback', 'ftc_feedback_nonce'); ?>
        <input type="submit" value="Submit Feedback">
    </form>
    <?php
}

add_shortcode('ftc_feedback_form', 'ftc_feedback_form');

function ftc_handle_feedback_submission() {
    if (isset($_POST['ftc_feedback_nonce']) && wp_verify_nonce($_POST['ftc_feedback_nonce'], 'ftc_submit_feedback')) {
        $design_id = absint($_POST['design_id']);
        $feedback_content = sanitize_textarea_field($_POST['feedback_content']);

        // Create the feedback post
        $feedback_post = array(
            'post_title' => 'Feedback for Design #' . $design_id,
            'post_content' => $feedback_content,
            'post_type' => 'ftc_feedback',
            'post_status' => 'publish',
        );

        $feedback_id = wp_insert_post($feedback_post);

        if (!is_wp_error($feedback_id)) {
            echo 'Feedback submitted successfully.';
        } else {
            echo 'Error submitting feedback.';
        }
    }
}
add_action('init', 'ftc_handle_feedback_submission');

// File: frontend.php

// Step 8: Applying Snap-to-Grid Functionality and Default Orientation
function ftc_apply_snap_and_orientation() {
    $grid_size = get_option('ftc_grid_size', 32); // Default grid size is 32px
    $default_orientation = get_option('ftc_default_orientation', false); // Default orientation is false

    ?>
    <script>
        jQuery(function($) {
            $(".equipment-item").draggable({
                snap: ".grid-cell",
                snapMode: "outer",
                snapTolerance: <?php echo $grid_size / 2; ?>,
                grid: [<?php echo $grid_size; ?>, <?php echo $grid_size; ?>],
                <?php if ($default_orientation) : ?>
                // Restrict dragging to horizontal or vertical direction based on data-orientation attribute
                drag: function(event, ui) {
                    const orientation = $(this).data('orientation');
                    if (orientation === 'horizontal') {
                        ui.position.top = $(this).position().top;
                    } else if (orientation === 'vertical') {
                        ui.position.left = $(this).position().left;
                    }
                },
                <?php endif; ?>
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'ftc_apply_snap_and_orientation');

<input type="text" id="equipmentSearch" placeholder="Search equipment...">

<input type="text" id="discountCode" placeholder="Enter discount code">
<button id="applyDiscount">Apply</button>

<button id="listView" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
    List View
</button>
<button id="gridView" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-4">
    Grid View
</button>


<div id="costBreakdown" class="bg-white p-4 rounded shadow-md">
    <!-- Display individual equipment costs and total here -->
</div>
