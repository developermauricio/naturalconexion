<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_merchandiser', 99 );
if ( ! function_exists( 'wcct_theme_helper_merchandiser' ) ) {

	function wcct_theme_helper_merchandiser() {
		$wcct_core = WCCT_Core()->appearance;

		// removing wcct action hooks on theme
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.2 );

		add_action( 'woocommerce_single_product_summary_single_title', array( $wcct_core, 'wcct_position_above_title' ), 3 );
		add_action( 'woocommerce_single_product_summary_single_title', array( $wcct_core, 'wcct_position_below_title' ), 6 );
		add_action( 'woocommerce_single_product_summary_single_excerpt', array( $wcct_core, 'wcct_position_below_review' ), 16 );
		add_action( 'woocommerce_single_product_summary_single_excerpt', array( $wcct_core, 'wcct_position_below_price' ), 17 );
		add_action( 'woocommerce_single_product_summary_single_excerpt', array( $wcct_core, 'wcct_position_below_short_desc' ), 21 );
		add_action( 'woocommerce_single_product_summary_single_add_to_cart', array( $wcct_core, 'wcct_position_below_add_cart' ), 31 );
	}
}
