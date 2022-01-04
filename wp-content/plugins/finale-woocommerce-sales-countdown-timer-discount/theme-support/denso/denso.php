<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_denso' );

function wcct_theme_helper_denso() {
	$wcct_core = WCCT_Core()->appearance;
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	// removing wcct action hooks on theme
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.3 );

	// adding wcct action hooks on theme
	add_action( 'denso_woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.3 );
	add_action( 'denso_woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.2 );
	add_action( 'denso_woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.3 );
	add_action( 'denso_woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.3 );
	add_action( 'denso_woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 26.3 );
}
