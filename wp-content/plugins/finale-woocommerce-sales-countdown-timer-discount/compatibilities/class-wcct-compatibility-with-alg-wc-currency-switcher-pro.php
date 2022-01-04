<?php

/**
 * Class WCCT_Compatibility_With_ALG_WC_CURRENCY_SWITCHER_PRO
 * Plugin name : Currency Switcher for WooCommerce Pro
 * Author Name : Algoritmika Ltd
 */
class WCCT_Compatibility_With_ALG_WC_CURRENCY_SWITCHER_PRO {

	private static $active_plugins;

	public function __construct() {
		add_action( 'wcct_before_get_regular_price', array( $this, 'maybe_unhook_regular_price_filters' ) );
		add_action( 'wcct_after_get_regular_price', array( $this, 'maybe_hook_regular_price_filters' ) );
	}

	/**
	 * unhooking the various price related hooks available in the plugin
	 */
	public function maybe_unhook_regular_price_filters() {
		remove_filter( 'woocommerce_product_get_price', array( alg_wc_currency_switcher_plugin()->core, 'change_price_by_currency' ), 9223372036854775807, 2 );
		remove_filter( 'woocommerce_product_variation_get_price', array( alg_wc_currency_switcher_plugin()->core, 'change_price_by_currency' ), PHP_INT_MAX, 2 );
		remove_filter( 'woocommerce_product_variation_get_regular_price', array( alg_wc_currency_switcher_plugin()->core, 'change_price_by_currency' ), PHP_INT_MAX, 2 );
		remove_filter( 'woocommerce_product_variation_get_sale_price', array( alg_wc_currency_switcher_plugin()->core, 'change_price_by_currency' ), PHP_INT_MAX, 2 );
	}

	/**
	 * re-hooking variation price related hooks which were removed above with lesser priority
	 */
	public function maybe_hook_regular_price_filters() {
		add_filter( 'woocommerce_product_variation_get_price', array( alg_wc_currency_switcher_plugin()->core, 'change_price_by_currency' ), PHP_INT_MIN, 2 );
		add_filter( 'woocommerce_product_variation_get_regular_price', array( alg_wc_currency_switcher_plugin()->core, 'change_price_by_currency' ), PHP_INT_MIN, 2 );
		add_filter( 'woocommerce_product_variation_get_sale_price', array( alg_wc_currency_switcher_plugin()->core, 'change_price_by_currency' ), PHP_INT_MIN, 2 );
	}


}

/**
 * checking the existence of plugin's class and return if not exist
 */
if ( ! class_exists( 'Alg_WC_Currency_Switcher' ) ) {
	return;
}

new WCCT_Compatibility_With_ALG_WC_CURRENCY_SWITCHER_PRO();
