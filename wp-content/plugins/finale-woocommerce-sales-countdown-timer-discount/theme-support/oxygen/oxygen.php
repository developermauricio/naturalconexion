<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_oxygen', 99 );
if ( ! function_exists( 'wcct_theme_helper_oxygen' ) ) {

	function wcct_theme_helper_oxygen() {
		$wcct_core = WCCT_Core()->appearance;

		// removing wcct action hooks on theme
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.3 );

		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 1.3 );
		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 27.3 );
	}
}
