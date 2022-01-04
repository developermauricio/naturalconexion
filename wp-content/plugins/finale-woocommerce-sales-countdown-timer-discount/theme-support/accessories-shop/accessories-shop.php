<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_as', 99 );

function wcct_theme_helper_as() {
	$wcct_core = WCCT_Core()->appearance;
	// removing duplicate price
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );

	// removing wcct action hooks on theme
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.3 );

	// adding wcct action hooks on theme
	add_action( 'accessories_shop_woocommerce_above_title', array( $wcct_core, 'wcct_position_above_title' ), 10.3 );
	add_action( 'accessories_shop_woocommerce_below_title', array( $wcct_core, 'wcct_position_below_title' ), 10.3 );
	add_action( 'accessories_shop_woocommerce_below_review', array( $wcct_core, 'wcct_position_below_review' ), 10.3 );
	add_action( 'accessories_shop_woocommerce_below_price', array( $wcct_core, 'wcct_position_below_price' ), 10.3 );
	add_action( 'accessories_shop_woocommerce_below_short_desc', array( $wcct_core, 'wcct_position_below_short_desc' ), 10.3 );
}
