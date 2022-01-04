<?php

class WCCT_Compatibility_With_YITH_WOO_Product_Bundles {

	public function __construct() {

		/**
		 * Checking If YITH WooCommerce Product Bundles Plugin is installed or not
		 */
		if ( false === class_exists( 'YITH_WCPB' ) ) {
			return;
		}

		add_filter( 'woocommerce_add_cart_item', array( $this, 'wcct_set_item_price_before_product_bundles' ), 5, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'wcct_set_item_price_from_session_before_product_bundles' ), 5, 1 );

	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 *
	 * Run woocommerce product bundles function after finale
	 */
	public function wcct_set_item_price_before_product_bundles( $items ) {
		$cart = YITH_WCPB_Frontend::get_instance();
		remove_filter( 'woocommerce_add_cart_item', array( $cart, 'woocommerce_add_cart_item' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item', array( $cart, 'woocommerce_add_cart_item' ), 999, 2 );

		return $items;
	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 *
	 * Run woocommerce product bundles function after finale
	 */
	public function wcct_set_item_price_from_session_before_product_bundles( $items ) {
		$cart = YITH_WCPB_Frontend::get_instance();
		remove_filter( 'woocommerce_get_cart_item_from_session', array( $cart, 'woocommerce_get_cart_item_from_session' ), 10, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $cart, 'woocommerce_get_cart_item_from_session' ), 99, 3 );

		return $items;
	}

}

new WCCT_Compatibility_With_YITH_WOO_Product_Bundles();
