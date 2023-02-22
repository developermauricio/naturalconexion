<?php
/**
 * Register Post type functionality
 *
 * @package WP Logo Showcase Responsive Slider
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Function to register post type 
 * 
 * @since 1.0
 */
function wplss_logo_showcase_post_types() {

	$labels = apply_filters( 'sp_logo_showcase_slider_labels', array(
		'name'					=> __('Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'singular_name'			=> __('Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'add_new'				=> __('Add New', 'wp-logo-showcase-responsive-slider-slider'),
		'add_new_item'			=> __('Add New Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'edit_item'				=> __('Edit Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'new_item'				=> __('New Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'all_items'				=> __('All Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'view_item'				=> __('View Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'search_items'			=> __('Search Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'not_found'				=> __('No Logo Showcase found', 'wp-logo-showcase-responsive-slider-slider'),
		'not_found_in_trash'	=> __('No Logo Showcase found in Trash', 'wp-logo-showcase-responsive-slider-slider'),
		'parent_item_colon'		=> '',
		'menu_name'				=> __('Logo Showcase', 'wp-logo-showcase-responsive-slider-slider'),
		'featured_image'		=> __('Logo Image', 'wp-logo-showcase-responsive-slider-slider'),
		'set_featured_image'	=> __('Set Logo Image', 'wp-logo-showcase-responsive-slider-slider'),
		'remove_featured_image'	=> __('Remove logo image', 'wp-logo-showcase-responsive-slider-slider'),
		'use_featured_image'	=> __('Use as logo image', 'wp-logo-showcase-responsive-slider-slider'),
	));

	$args = array(
		'labels'				=> $labels,
		'menu_icon'				=> 'dashicons-images-alt2',
		'capability_type' 		=> 'post',
		'public' 				=> false,
		'show_ui' 				=> true,
		'show_in_menu' 			=> true,
		'query_var' 			=> false,
		'hierarchical' 			=> false,
		'supports' 				=> apply_filters( 'sp_logoshowcase_post_supports', array('title', 'thumbnail') )
	);

	register_post_type( WPLS_POST_TYPE, apply_filters( 'sp_logoshowcase_post_type_args', $args ) );
}

// Action to register plugin post type
add_action( 'init', 'wplss_logo_showcase_post_types' );

/**
 * Function to register post taxonomies 
 * 
 * @since 1.0
*/
function wplss_logo_showcase_taxonomies() {

	$labels = array(
		'name'				=> __( 'Logo Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'singular_name'		=> __( 'Logo Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'search_items'		=> __( 'Search Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'all_items'			=> __( 'All Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'parent_item'		=> __( 'Parent Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'parent_item_colon'	=> __( 'Parent Category:', 'wp-logo-showcase-responsive-slider-slider' ),
		'edit_item'			=> __( 'Edit Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'update_item'		=> __( 'Update Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'add_new_item'		=> __( 'Add New Category', 'wp-logo-showcase-responsive-slider-slider' ),
		'new_item_name'		=> __( 'New Category Name', 'wp-logo-showcase-responsive-slider-slider' ),
		'menu_name'			=> __( 'Logo Category', 'wp-logo-showcase-responsive-slider-slider' ),
	);

	$args = array(
		'labels'			=> $labels,
		'public'			=> false,
		'hierarchical'		=> true,
		'show_ui'			=> true,
		'show_admin_column'	=> true,
		'query_var'			=> true,
		'rewrite'			=> false,
	);

	register_taxonomy( WPLS_CAT_TYPE, array( WPLS_POST_TYPE ), $args );
}

// Action to register plugin taxonomies
add_action( 'init', 'wplss_logo_showcase_taxonomies' );

/**
 * Function to update post messages
 * 
 * @package WP Logo Showcase Responsive Slider Pro
 * @since 1.0
 */
function wplss_post_updated_messages( $messages ) {

	global $post;

	$messages[WPLS_POST_TYPE] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Logo Showcase updated.', 'wp-logo-showcase-responsive-slider-slider' ) ),
		2 => __( 'Custom field updated.', 'wp-logo-showcase-responsive-slider-slider' ),
		3 => __( 'Custom field deleted.', 'wp-logo-showcase-responsive-slider-slider' ),
		4 => __( 'Logo Showcase updated.', 'wp-logo-showcase-responsive-slider-slider' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Logo Showcase restored to revision from %s', 'wp-logo-showcase-responsive-slider-slider' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Logo Showcase published.', 'wp-logo-showcase-responsive-slider-slider' ) ),
		7 => __( 'Logo Showcase saved.', 'wp-logo-showcase-responsive-slider-slider' ),
		8 => sprintf( __( 'Logo Showcase submitted.', 'wp-logo-showcase-responsive-slider-slider' ) ),
		9 => sprintf( __( 'Logo Showcase scheduled for: <strong>%1$s</strong>.', 'wp-logo-showcase-responsive-slider-slider' ),
		  date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ) ),
		10 => sprintf( __( 'Logo Showcase draft updated.', 'wp-logo-showcase-responsive-slider-slider' ) ),
	);

	return $messages;
}

// Filter to update logo showcase post message
add_filter( 'post_updated_messages', 'wplss_post_updated_messages' );