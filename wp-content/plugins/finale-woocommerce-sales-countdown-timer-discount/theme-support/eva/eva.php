<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_eva', 99 );
if ( ! function_exists( 'wcct_theme_helper_eva' ) ) {
	function wcct_theme_helper_eva() {
		$wcct_core = WCCT_Core()->appearance;

		// removing duplicate price
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

		// removing wcct action hooks on theme
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.3 );

		// adding wcct action hooks on theme
		add_action( 'woocommerce_single_product_summary_single_title', array( $wcct_core, 'wcct_position_above_title' ), 4.3 );
		add_action( 'woocommerce_single_product_summary_single_title', array( $wcct_core, 'wcct_position_below_title' ), 6.3 );
		add_action( 'woocommerce_single_product_summary_single_rating', array( $wcct_core, 'wcct_position_below_review' ), 12.3 );
		add_action( 'woocommerce_single_product_summary_single_price', array( $wcct_core, 'wcct_position_below_price' ), 12.3 );
		add_action( 'woocommerce_single_product_summary_single_excerpt', array( $wcct_core, 'wcct_position_below_short_desc' ), 22.3 );
		add_action( 'woocommerce_single_product_summary_single_add_to_cart', array( $wcct_core, 'wcct_position_below_add_cart' ), 32.3 );
	}
}
