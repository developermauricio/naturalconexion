<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_sober', 99 );
if ( ! function_exists( 'wcct_theme_helper_sober' ) ) {

	function wcct_theme_helper_sober() {
		$wcct_core = WCCT_Core()->appearance;

		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.2 );
		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 21.2 );

		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.2 );
		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 16.2 );

		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.2 );
		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 11.2 );
	}
}
