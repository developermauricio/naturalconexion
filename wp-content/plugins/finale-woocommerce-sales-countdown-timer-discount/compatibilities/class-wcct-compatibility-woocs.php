<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Compatibility_With_WOOCS {

	public function __construct() {
		add_action( 'wcct_before_get_regular_price', array( $this, 'maybe_unhook_regular_price_filters' ) );
		add_action( 'wcct_after_get_regular_price', array( $this, 'maybe_rehook_regular_price_filters' ) );
		if ( isset( $GLOBALS['WOOCS'] ) && $GLOBALS['WOOCS'] instanceof WOOCS ) {
			remove_filter( 'woocommerce_get_variation_prices_hash', array( $GLOBALS['WOOCS'], 'woocommerce_get_variation_prices_hash' ), 9999, 3 );
		}
		add_action( 'init', array( $this, 'maybe_initiate_data_setup_in_case_of_ajax' ) );
	}

	public function maybe_unhook_regular_price_filters() {
		if ( isset( $GLOBALS['WOOCS'] ) && $GLOBALS['WOOCS'] instanceof WOOCS && $GLOBALS['WOOCS']->is_multiple_allowed == '1' ) {
			remove_filter( 'woocommerce_product_get_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			//wp-content\plugins\woocommerce\includes\abstracts\abstract-wc-data.php
			//protected function get_prop
			remove_filter( 'woocommerce_product_variation_get_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			remove_filter( 'woocommerce_product_variation_get_regular_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			//comment next code line if on single product page for variable prices you see crossed out price which equal to the regular one,
			//I mean you see 2 same prices (amounts) and one of them is crossed out which by logic should not be visible at all
			//remove_filter('woocommerce_product_variation_get_sale_price', array($this, 'raw_woocommerce_price'), 9999, 2);
			//new  function  for sale price
			remove_filter( 'woocommerce_product_variation_get_sale_price', array( $GLOBALS['WOOCS'], 'raw_sale_price_filter' ), 9999, 2 );
			remove_filter( 'woocommerce_product_get_regular_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			remove_filter( 'woocommerce_product_get_sale_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price_sale' ), 9999, 2 );
			remove_filter( 'woocommerce_get_variation_regular_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 4 );
			remove_filter( 'woocommerce_get_variation_sale_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 4 );
			remove_filter( 'woocommerce_variation_prices', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			remove_filter( 'woocommerce_variation_prices_price', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			remove_filter( 'woocommerce_variation_prices_regular_price', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			remove_filter( 'woocommerce_variation_prices_sale_price', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			remove_filter( 'woocommerce_get_variation_prices_hash', array( $GLOBALS['WOOCS'], 'woocommerce_get_variation_prices_hash' ), 9999, 3 );
		}
	}

	public function maybe_rehook_regular_price_filters() {
		if ( isset( $GLOBALS['WOOCS'] ) && $GLOBALS['WOOCS'] instanceof WOOCS && $GLOBALS['WOOCS']->is_multiple_allowed == '1' ) {
			add_filter( 'woocommerce_product_get_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			//wp-content\plugins\woocommerce\includes\abstracts\abstract-wc-data.php
			//protected function get_prop
			add_filter( 'woocommerce_product_variation_get_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			//comment next code line if on single product page for variable prices you see crossed out price which equal to the regular one,
			//I mean you see 2 same prices (amounts) and one of them is crossed out which by logic should not be visible at all
			//remove_filter('woocommerce_product_variation_get_sale_price', array($this, 'raw_woocommerce_price'), 9999, 2);
			//new  function  for sale price
			add_filter( 'woocommerce_product_variation_get_sale_price', array( $GLOBALS['WOOCS'], 'raw_sale_price_filter' ), 9999, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 2 );
			//	add_filter( 'woocommerce_product_get_sale_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price_sale' ), 9999, 2 );
			add_filter( 'woocommerce_get_variation_regular_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 4 );
			add_filter( 'woocommerce_get_variation_sale_price', array( $GLOBALS['WOOCS'], 'raw_woocommerce_price' ), 9999, 4 );
			add_filter( 'woocommerce_variation_prices', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			add_filter( 'woocommerce_variation_prices_price', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			add_filter( 'woocommerce_variation_prices_regular_price', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			add_filter( 'woocommerce_variation_prices_sale_price', array( $GLOBALS['WOOCS'], 'woocommerce_variation_prices' ), 9999, 3 );
			//	add_filter( 'woocommerce_get_variation_prices_hash', array( $GLOBALS['WOOCS'], 'woocommerce_get_variation_prices_hash' ), 9999, 3 );
		}
	}

	/**
	 * hooked over `init`
	 * for caching pluugin WooCs try to send an ajax and refresh all the prices for the front end
	 * To make finale run with this behavior we first need to setup our data
	 *
	 */
	public function maybe_initiate_data_setup_in_case_of_ajax() {
		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX && filter_input( INPUT_POST, 'action' ) === 'woocs_get_products_price_html' ) {
			$get_ids = ( isset( $_POST['products_ids'] ) ? $_POST['products_ids'] : array() );
			if ( $get_ids && is_array( $get_ids ) && count( $get_ids ) > 0 ) {
				foreach ( $get_ids as $id ) {
					WCCT_Core()->public->get_single_campaign_pro_data( $id, true );
				}
			}
		}
	}
}

new WCCT_Compatibility_With_WOOCS();
