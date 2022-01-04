<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_aurum', 99 );
if ( ! function_exists( 'wcct_theme_helper_aurum' ) ) {

	function wcct_theme_helper_aurum() {
		$wcct_core = WCCT_Core()->appearance;

		// removing wcct action hooks on theme
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.3 );
		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 27.3 );
	}
}
