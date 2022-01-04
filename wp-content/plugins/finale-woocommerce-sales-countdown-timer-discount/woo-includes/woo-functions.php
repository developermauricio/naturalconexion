<?php
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WCCT_WC_Dependencies' ) ) {
	require_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'woo-includes/class-wcct-wc-dependencies.php';
}

/**
 * WC Detection
 */
if ( ! function_exists( 'wcct_is_woocommerce_active' ) ) {
	function wcct_is_woocommerce_active() {
		return WCCT_WC_Dependencies::woocommerce_active_check();
	}
}
