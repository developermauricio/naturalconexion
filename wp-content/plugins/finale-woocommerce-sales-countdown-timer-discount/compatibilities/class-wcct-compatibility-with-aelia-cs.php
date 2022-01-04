<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Compatibility_With_Aelia_CS {

	public function __construct() {

		/**
		 * Checking Aelia Currency Switcher existence
		 */
		if ( false === class_exists( 'Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher' ) ) {
			return;
		}
		/**
		 * Allow modification of fixed amount discount in the chosen currency
		 * In this way we do not need to take care about the regular price that we are getting.
		 *
		 */

		add_filter( 'wcct_deal_amount_fixed_amount_simple', array( $this, 'alter_fixed_amount' ), 10 );
		add_filter( 'wcct_deal_amount_fixed_amount_variable', array( $this, 'alter_fixed_amount' ), 10 );
		add_filter( 'wcct_deal_amount_fixed_amount_variation', array( $this, 'alter_fixed_amount' ), 10 );

		/**
		 * Regular price event fixed amount currency modifier
		 */
		add_filter( 'wcct_regular_price_event_value_fixed', array( $this, 'alter_fixed_amount' ), 10 );

		/**
		 * Custom advanced range currency converter filter
		 */
		add_filter( 'wcct_deals_custom_advanced_range', array( $this, 'alter_advanced_range_prices' ) );
	}

	/**
	 * @hooked into `wcct_deals_custom_advanced_range`
	 * Modifies the range for the regular price given by the admin in the currency selected.
	 *
	 * @param array $configuration
	 *
	 * @return array
	 */
	public function alter_advanced_range_prices( $configuration ) {
		$configuration['range_from'] = $this->get_price_in_currency( $configuration['range_from'] );
		$configuration['range_to']   = $this->get_price_in_currency( $configuration['range_to'] );

		return $configuration;
	}

	/**
	 * Basic integration with WooCommerce Currency Switcher, developed by Aelia
	 * (http://aelia.co). This method can be used by any 3rd party plugin to
	 * return prices converted to the active currency.
	 *
	 * Need a consultation? Find us on Codeable: https://aelia.co/hire_us
	 *
	 * @param double price The source price.
	 * @param string to_currency The target currency. If empty, the active currency
	 * will be taken.
	 * @param string from_currency The source currency. If empty, WooCommerce base
	 * currency will be taken.
	 *
	 * @return double The price converted from source to destination currency.
	 * @author Aelia <support@aelia.co>
	 * @link https://aelia.co
	 */
	public function get_price_in_currency( $price, $to_currency = null, $from_currency = null ) {
		// If source currency is not specified, take the shop's base currency as a default
		if ( empty( $from_currency ) ) {
			$from_currency = get_option( 'woocommerce_currency' );
		}
		// If target currency is not specified, take the active currency as a default.
		// The Currency Switcher sets this currency automatically, based on the context. Other
		// plugins can also override it, based on their own custom criteria, by implementing
		// a filter for the "woocommerce_currency" hook.
		//
		// For example, a subscription plugin may decide that the active currency is the one
		// taken from a previous subscription, because it's processing a renewal, and such
		// renewal should keep the original prices, in the original currency.
		if ( empty( $to_currency ) ) {
			$to_currency = get_woocommerce_currency();
		}

		// Call the currency conversion filter. Using a filter allows for loose coupling. If the
		// Aelia Currency Switcher is not installed, the filter call will return the original
		// amount, without any conversion being performed. Your plugin won't even need to know if
		// the multi-currency plugin is installed or active
		return apply_filters( 'wc_aelia_cs_convert', $price, $from_currency, $to_currency );
	}

	/**
	 * @hooked into `wcct_deal_amount_fixed_amount_{$type}` | `wcct_regular_price_event_value_fixed`
	 * Modifies the amount for the fixed discount given by the admin in the currency selected.
	 *
	 * @param integer|float $price
	 *
	 * @return float
	 */
	public function alter_fixed_amount( $price ) {
		return $this->get_price_in_currency( $price );
	}


}

new WCCT_Compatibility_With_Aelia_CS();
