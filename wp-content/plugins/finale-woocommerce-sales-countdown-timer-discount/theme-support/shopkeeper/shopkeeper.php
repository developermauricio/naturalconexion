<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_shopkeeper' );

function wcct_theme_helper_shopkeeper() {

	$wcct_core = WCCT_Core()->appearance;

	// removing duplicate price
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

	// removing below price and below add to cart buttton action hook of plugin
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.2 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.2 );

	// hooking below functions for 'shopkeeper' theme
	add_action( 'woocommerce_single_product_summary_single_title', array( $wcct_core, 'wcct_position_above_title' ), 2 );
	add_action( 'woocommerce_single_product_summary_single_title', array( $wcct_core, 'wcct_position_below_title' ), 9 );
	add_action( 'woocommerce_single_product_summary_single_title', array( $wcct_core, 'wcct_position_below_review' ), 1 );
	add_action( 'woocommerce_single_product_summary_single_price', array( $wcct_core, 'wcct_position_below_price' ), 19 );
	add_action( 'woocommerce_single_product_summary_single_excerpt', array( $wcct_core, 'wcct_position_below_short_desc' ), 21 );
	add_action( 'woocommerce_single_product_summary_single_add_to_cart', array( $wcct_core, 'wcct_position_below_add_cart' ), 31 );
}
