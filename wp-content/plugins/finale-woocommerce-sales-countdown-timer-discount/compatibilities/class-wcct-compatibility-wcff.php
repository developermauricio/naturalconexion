<?php

class WCCT_Compatibility_WCFF {

	private $discount = true;

	public function __construct() {
		if ( ! class_exists( 'Wcff' ) ) {
			return;
		}

		add_filter( 'wcct_skip_discounts', [ $this, 'skip_discount' ], 1000, 3 );

		add_filter( 'woocommerce_add_cart_item', [ $this, 'remove_discount' ], 990, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'remove_discount' ], 990, 2 );

		add_filter( 'woocommerce_add_cart_item', [ $this, 'apply_discount' ], 1010, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'apply_discount' ], 1010, 2 );
	}

	function remove_discount( $item, $item_key ) {
		$this->discount = false;

		return $item;
	}

	function apply_discount( $item = [], $item_key = '' ) {
		$this->discount = true;

		return $item;
	}

	function skip_discount( $bool, $price, $product ) {
		if ( false == $this->discount && ( $product instanceof WC_Product ) && in_array( $product->get_id(), WCCT_Core()->discount->excluded ) ) {
			return true;
		}

		return $bool;
	}


}

new WCCT_Compatibility_WCFF();