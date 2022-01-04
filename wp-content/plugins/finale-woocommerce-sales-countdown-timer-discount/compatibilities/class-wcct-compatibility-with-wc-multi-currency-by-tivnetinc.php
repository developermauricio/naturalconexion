<?php

use WOOMC as MULTI_WOOMC;

class WCCT_Compatibility_With_WOOMULTI_CURRENCY_BY_TIVNETINC {

	private static $ins = null;
	private static $active_plugins;

	public function __construct() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		$woocommerce_multicurrency_active = in_array( 'woocommerce-multicurrency/woocommerce-multicurrency.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce-multicurrency/woocommerce-multicurrency.php', self::$active_plugins );

		if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( 'woocommerce-multicurrency/woocommerce-multicurrency.php' ) || false === $woocommerce_multicurrency_active ) {
			return;
		}
		remove_filter( 'woocommerce_add_cart_item', array( WCCT_Core()->cart, 'maybe_setup_data' ), 99, 2 );
		remove_filter( 'woocommerce_get_cart_item_from_session', array( WCCT_Core()->cart, 'maybe_setup_data' ), 19, 2 );

		add_filter( 'wcct_finale_discounted_price', array( $this, 'wcct_currency_convert_finale_discounted_price' ), 10, 3 );
	}

	public function wcct_currency_convert_finale_discounted_price( $sale_price, $regular_price, $product_global_id ) {

		global $set_sales_price;

		if ( $set_sales_price ) {
			return $set_sales_price;
		}


		$product = wc_get_product( $product_global_id );

		$user = new MULTI_WOOMC\User();
		$user->setup_hooks();

		$currency_detector = new MULTI_WOOMC\Currency\Detector();
		$currency_detector->setup_hooks();

		$rate_storage = new MULTI_WOOMC\Rate\Storage();
		$rate_storage->setup_hooks();

		$price_rounder = new MULTI_WOOMC\Price\Rounder();

		$price_calculator = new MULTI_WOOMC\Price\Calculator( $rate_storage, $price_rounder );

		$currency_controller = new MULTI_WOOMC\Currency\Controller( $currency_detector );
		$currency_controller->setup_hooks();

		$decimals = new MULTI_WOOMC\Currency\Decimals();
		$decimals->setup_hooks();

		$price_controller = new MULTI_WOOMC\Price\Controller( $price_calculator, $currency_detector );
		$sale_price       = $price_controller->convert( $sale_price, $product );

		if ( ! isset( $set_sales_price ) ) {
			$set_sales_price = $sale_price;
		}

		return $sale_price;
	}


}

new WCCT_Compatibility_With_WOOMULTI_CURRENCY_BY_TIVNETINC();
