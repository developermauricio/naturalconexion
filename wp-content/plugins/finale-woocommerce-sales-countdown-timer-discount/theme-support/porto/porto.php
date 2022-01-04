<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_porto', 99 );
if ( ! function_exists( 'wcct_theme_helper_porto' ) ) {

	function wcct_theme_helper_porto() {
		$wcct_core = WCCT_Core()->appearance;

		// removing duplicate price
		//      remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

		// removing wcct action hooks on theme
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.2 );
		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 25 );
	}
}
