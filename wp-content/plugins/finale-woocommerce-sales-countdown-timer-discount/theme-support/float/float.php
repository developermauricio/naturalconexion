<?php
defined( 'ABSPATH' ) || exit;

$settings = WCCT_Common::get_global_default_settings();
if ( 'old' == $settings['wcct_positions_approach'] ) {
	return;
}

add_action( 'wp', 'wcct_theme_helper_float', 99 );

function wcct_theme_helper_float() {

	$wcct_core = WCCT_Core()->appearance;
	// removing all positions based action hooks
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.2 );

	add_action( 'wcct_float_theme_above_title', array( $wcct_core, 'wcct_position_above_title' ), 10 );
	add_action( 'wcct_float_theme_below_title', array( $wcct_core, 'wcct_position_below_title' ), 10 );
	add_action( 'wcct_float_theme_below_review', array( $wcct_core, 'wcct_position_below_review' ), 10 );
	add_action( 'wcct_float_theme_below_price', array( $wcct_core, 'wcct_position_below_price' ), 10 );
	add_action( 'wcct_float_theme_below_short_desc', array( $wcct_core, 'wcct_position_below_short_desc' ), 10 );
	add_action( 'woocommerce_after_add_to_cart_form', array( $wcct_core, 'wcct_position_below_add_cart' ), 10 );
}

add_action( 'woocommerce_before_template_part', 'wcct_theme_helper_float_before_template_part', 99 );

function wcct_theme_helper_float_before_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
	if ( empty( $template_name ) ) {
		return '';
	}
	if ( $template_name == 'single-product/title.php' ) {
		do_action( 'wcct_float_theme_above_title' );
	}
}

add_action( 'woocommerce_after_template_part', 'wcct_theme_helper_float_after_template_part', 99 );

function wcct_theme_helper_float_after_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
	if ( empty( $template_name ) ) {
		return '';
	}
	if ( $template_name == 'single-product/title.php' ) {
		do_action( 'wcct_float_theme_below_title' );
	} elseif ( $template_name == 'single-product/short-description.php' ) {
		do_action( 'wcct_float_theme_below_short_desc' );
	} elseif ( $template_name == 'single-product/rating.php' ) {
		do_action( 'wcct_float_theme_below_review' );
	} elseif ( $template_name == 'single-product/price.php' ) {
		do_action( 'wcct_float_theme_below_price' );
	} elseif ( $template_name == 'single-product/meta.php' ) {
		do_action( 'wcct_float_theme_below_meta' );
	}
}
