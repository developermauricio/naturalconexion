<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Compatibility_With_WOO_CURRENCY_SWITCHER {

	public function __construct() {

		/**
		 * Checking If Woo Currency Switcher Plugin is installed or not
		 */
		if ( false === class_exists( 'WOOCS_STARTER' ) ) {
			return;
		}

		/**
		 * Removing our filters that excludes cart prices discounts by setting them one time
		 * But in this case we want to opt the legacy way of applying discount.
		 */
		remove_filter( 'woocommerce_add_cart_item', array( WCCT_Core()->cart, 'maybe_setup_data' ), 99, 2 );
		remove_filter( 'woocommerce_get_cart_item_from_session', array( WCCT_Core()->cart, 'maybe_setup_data' ), 19, 2 );

	}

}

new WCCT_Compatibility_With_WOO_CURRENCY_SWITCHER();
