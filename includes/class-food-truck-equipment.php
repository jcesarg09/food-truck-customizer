<?php
/**
 * Class for managing the Equipment Custom Post Type.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Food_Truck_Equipment {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_equipment_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_equipment_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_equipment_meta' ) );
    }

    /**
     * Register the Equipment Custom Post Type.
     */
    public function register_equipment_post_type() {
        $labels = array(
            'name'               => _x( 'Equipment', 'post type general name', 'food-truck-customizer' ),
            'singular_name'      => _x( 'Equipment', 'post type singular name', 'food-truck-customizer' ),
            'menu_name'          => _x( 'Equipment', 'admin menu', 'food-truck-customizer' ),
            'name_admin_bar'     => _x( 'Equipment', 'add new on admin bar', 'food-truck-customizer' ),
            'add_new'            => _x( 'Add New', 'equipment', 'food-truck-customizer' ),
            'add_new_item'       => __( 'Add New Equipment', 'food-truck-customizer' ),
            'new_item'           => __( 'New Equipment', 'food-truck-customizer' ),
            'edit_item'          => __( 'Edit Equipment', 'food-truck-customizer' ),
            'view_item'          => __( 'View Equipment', 'food-truck-customizer' ),
            'all_items'          => __( 'All Equipment', 'food-truck-customizer' ),
            'search_items'       => __( 'Search Equipment', 'food-truck-customizer' ),
            'parent_item_colon'  => __( 'Parent Equipment:', 'food-truck-customizer' ),
            'not_found'          => __( 'No equipment found.', 'food-truck-customizer' ),
            'not_found_in_trash' => __( 'No equipment found in Trash.', 'food-truck-customizer' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'equipment' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'thumbnail' )
        );

        register_post_type( 'ftc_equipment', $args );
    }

    /**
     * Add meta boxes for the Equipment post type.
     */
    public function add_equipment_meta_boxes() {
        add_meta_box( 'equipment_details', __( 'Equipment Details', 'food-truck-customizer' ), array( $this, 'render_equipment_meta_box' ), 'ftc_equipment' );
    }

    // Render the Equipment meta box.
public function render_equipment_meta_box( $post ) {
    // Fetch values if they exist
    $width = get_post_meta( $post->ID, '_ftc_width', true );
    $height = get_post_meta( $post->ID, '_ftc_height', true );
    $price = get_post_meta( $post->ID, '_ftc_price', true );
    $orientation = get_post_meta( $post->ID, '_ftc_orientation', true );

    // Nonce field for security
    wp_nonce_field( 'ftc_equipment_nonce', 'ftc_equipment_nonce_field' );

    // Display the form fields
    echo '<label for="ftc_width">' . __( 'Width (m)', 'food-truck-customizer' ) . '</label>';
    echo '<input type="text" id="ftc_width" name="ftc_width" value="' . esc_attr( $width ) . '" />';
    
    echo '<label for="ftc_height">' . __( 'Height (m)', 'food-truck-customizer' ) . '</label>';
    echo '<input type="text" id="ftc_height" name="ftc_height" value="' . esc_attr( $height ) . '" />';
    
    echo '<label for="ftc_price">' . __( 'Price', 'food-truck-customizer' ) . '</label>';
    echo '<input type="text" id="ftc_price" name="ftc_price" value="' . esc_attr( $price ) . '" />';
    
    echo '<label for="ftc_orientation">' . __( 'Orientation', 'food-truck-customizer' ) . '</label>';
    echo '<select id="ftc_orientation" name="ftc_orientation">';
    echo '<option value="front-facing" ' . selected( $orientation, 'front-facing', false ) . '>Front Facing</option>';
    echo '<option value="back-facing" ' . selected( $orientation, 'back-facing', false ) . '>Back Facing</option>';
    echo '</select>';
}

// Save the Equipment meta box data.
public function save_equipment_meta( $post_id ) {
    // ... [rest of the function remains the same]

    // Save the meta box data
    if ( isset( $_POST['ftc_width'] ) ) {
        update_post_meta( $post_id, '_ftc_width', sanitize_text_field( $_POST['ftc_width'] ) );
    }

    if ( isset( $_POST['ftc_height'] ) ) {
        update_post_meta( $post_id, '_ftc_height', sanitize_text_field( $_POST['ftc_height'] ) );
    }

    if ( isset( $_POST['ftc_orientation'] ) ) {
        update_post_meta( $post_id, '_ftc_orientation', sanitize_text_field( $_POST['ftc_orientation'] ) );
    }

    // ... [rest of the function remains the same]

}

// Initialize the class
new Food_Truck_Equipment();
