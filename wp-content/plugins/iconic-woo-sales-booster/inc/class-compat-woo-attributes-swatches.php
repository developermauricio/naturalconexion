<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Compat_Woo_Attributes_Swatches.
 *
 * @class    Iconic_WSB_Compat_Woo_Attributes_Swatches
 * @version  1.0.0
 * @since
 * @author   Iconic
 */
class Iconic_WSB_Compat_Woo_Attributes_Swatches {
	/**
	 * Run
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		if ( ! class_exists( 'Iconic_Woo_Attribute_Swatches' ) ) {
			return;
		}

		add_filter( 'iconic_wsb_inital_price', array( __CLASS__, 'add_initial_fees' ), 10, 2 );
		add_filter( 'iconic_wsb_discounted_price_before_discount', array( __CLASS__, 'add_before_discount_fee' ), 10, 2 );

		add_filter( 'iconic_wsb_fbt_product_price', array( __CLASS__, 'fbt_add_fees' ), 10, 3 );
		add_filter( 'iconic_wsb_fbt_before_cart_metadata', array( __CLASS__, 'fbt_add_fees_metadata' ), 10, 2 );
	}

	/**
	 * Add fees to FBT calculations.
	 * 
	 * @param $price
	 * @param $product
	 * @param $excluding_tax
	 *
	 * @return int
	 */
	public static function fbt_add_fees( $price, $product, $excluding_tax = false ) {
		if ( is_a( $product, 'WC_Product_Variation' ) ) {
			// Checkout/cart should always exclude tax for calculations.
			$is_checkout      = is_cart() || is_checkout();
			$excluding_tax    = $is_checkout || 'incl' !== get_option( 'woocommerce_tax_display_shop' );
			$calculated_price = self::calculate_variation_fee( $product, $excluding_tax );

			if ( false !== $calculated_price ) {
				$price = $calculated_price;
			}
		}

		return $price;
	}

	/**
	 * Add fees metadata.
	 * 
	 * @param $metadata
	 * @param $product
	 *
	 * @return mixed
	 */
	public static function fbt_add_fees_metadata( $metadata, $product ) {
		$product = is_object( $product ) ? $product : wc_get_product( $product );

		if ( is_a( $product, 'WC_Product_Variation' ) ) {
			$excluding_tax = 'incl' !== get_option( 'woocommerce_tax_display_cart' );
			$fees          = self::calculate_variation_fee( $product, $excluding_tax );
			
			if ( false !== $fees ) {
				$metadata['bump_price'] = $fees;
			}
		}

		return $metadata;
	}

	/**
	 * Add initial fees.
	 * 
	 * @param $price
	 * @param $product
	 *
	 * @return int
	 */
	public static function add_initial_fees( $price, $product ) {
		if ( is_a( $product, 'WC_Product_Variation' ) ) {
			return self::calculate_variation_fee( $product );
		}

		return $price;
	}

	/**
	 * Add before discount fee.
	 * 
	 * @param $price
	 * @param $product_id
	 *
	 * @return float
	 */
	public static function add_before_discount_fee( $price, $product_id ) {
		$product = wc_get_product( $product_id );

		if ( is_a( $product, 'WC_Product_Variation' ) ) {
			return self::calculate_variation_fee( $product );
		}

		return $price;
	}

	/**
	 * Checkout bump before add to cart.
	 * 
	 * @param $price
	 * @param $product_id
	 *
	 * @return float
	 */
	public static function checkout_bump_before_add_to_cart( $price, $product_id ) {
		$product = wc_get_product( $product_id );

		if ( is_a( $product, 'WC_Product_Variation' ) ) {
			$fee = self::calculate_variation_fee( $product );

			return $fee;
		}

		return $price;
	}

	/**
	 * Returns the sum of fees of all the attributes for a given variation.
	 *
	 * @param int|object $variation
	 *
	 * @return int $fees
	 */
	public static function calculate_variation_fee( $variation, $excluding_tax = false ) {
		if ( 'integer' === gettype( $variation ) ) {
			$variation = wc_get_product( $variation );
		}

		$total = $excluding_tax ? wc_get_price_excluding_tax( $variation ) : wc_get_price_including_tax( $variation );

		if ( ! class_exists( 'Iconic_WAS_Fees' ) ) {
			return $total;
		}

		if ( ! is_a( $variation, 'WC_Product_Variation' ) ) {
			return false;
		}

		$attributes = $variation->get_variation_attributes();

		foreach ( $attributes as $attribute => $attribute_value ) {
			if ( empty( $attribute_value ) ) {
				continue;
			}

			$attribute = str_replace( 'attribute_', '', $attribute );
			$fee       = Iconic_WAS_Fees::get_fees( $variation->get_parent_id(), $attribute, $attribute_value );

			if ( empty( $fee ) || empty( $fee[ $attribute ][ $attribute_value ] ) ) {
				continue;
			}

			$total += ! $excluding_tax ?
				wc_get_price_including_tax(
					$variation,
					array(
						'qty'   => 1,
						'price' => $fee[ $attribute ][ $attribute_value ],
					)
				) :
				wc_get_price_excluding_tax(
					$variation,
					array(
						'qty'   => 1,
						'price' => $fee[ $attribute ][ $attribute_value ],
					)
				);
		}

		return $total;
	}
}
