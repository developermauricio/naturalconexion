<?php
defined( 'ABSPATH' ) || exit;

/**
 * @author: XLPlugins
 * Class WCCT_API
 */
class WCCT_API {


	public static function get_sale_price( $product = 0, $formatted = false ) {

		if ( ! $product instanceof WC_Product && 0 != $product ) {
			$product = wc_get_product( $product );
		}

		if ( $product instanceof WC_Product ) {
			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product );

			WCCT_Core()->public->wcct_get_product_obj( $parent_id );
			WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );

			return ( false == $formatted ) ? $product->get_sale_price() : wc_price( $product->get_sale_price() );

		}

		return false;
	}

	public static function get_regular_price( $product = 0, $formatted = false ) {

		if ( ! $product instanceof WC_Product && 0 != $product ) {
			$product = wc_get_product( $product );
		}

		if ( $product instanceof WC_Product ) {
			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product );

			WCCT_Core()->public->wcct_get_product_obj( $parent_id );
			WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );

			return ( false == $formatted ) ? $product->get_regular_price() : wc_price( $product->get_regular_price() );

		}

		return false;

	}


	public static function get_price_html( $product = 0 ) {
		if ( ! $product instanceof WC_Product && 0 != $product ) {
			$product = wc_get_product( $product );
		}

		if ( $product instanceof WC_Product ) {
			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product );

			WCCT_Core()->public->wcct_get_product_obj( $parent_id );
			WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );

			return $product->get_price_html();

		}

		return false;

	}
}
