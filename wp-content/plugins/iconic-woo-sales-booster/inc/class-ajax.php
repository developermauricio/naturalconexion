<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Ajax.
 *
 * All ajax methods.
 *
 * @class    Iconic_WSB_Ajax
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Ajax {
	/**
	 * Init
	 */
	public static function run() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'json_search_products_and_variations_no_variables' => false,
			'checkout_order_bump_calculate_price'              => false,
			'get_variation_price'                              => true,
			'fbt_get_products_price'                           => true,
			'checkout_get_variation'                           => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_iconic_wsb_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_iconic_wsb_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Search for products and echo json.
	 *
	 * Same as the Woo ajax function but it skips variable products.
	 *
	 * @see WC_AJAX::json_search_products()
	 */
	public static function json_search_products_and_variations_no_variables() {
		check_ajax_referer( 'search-products', 'security' );

		if ( empty( $term ) && isset( $_GET['term'] ) ) {
			$term = (string) wc_clean( wp_unslash( $_GET['term'] ) );
		}

		if ( empty( $term ) ) {
			wp_die();
		}

		if ( ! empty( $_GET['limit'] ) ) {
			$limit = absint( $_GET['limit'] );
		} else {
			$limit = absint( apply_filters( 'woocommerce_json_search_limit', 30 ) );
		}

		$include_variations = true;
		$include_ids        = ! empty( $_GET['include'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['include'] ) ) : array();
		$exclude_ids        = ! empty( $_GET['exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) : array();
		$data_store         = WC_Data_Store::load( 'product' );
		$ids                = $data_store->search_products( $term, '', (bool) $include_variations, false, $limit, $include_ids, $exclude_ids );
		$product_objects    = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
		$products           = array();

		foreach ( $product_objects as $product_object ) {
			// Added: This skips variable products.
			if ( $product_object->is_type( 'variable' ) ) {
				continue;
			}

			$formatted_name = $product_object->get_formatted_name();
			$managing_stock = $product_object->managing_stock();

			if ( $managing_stock && ! empty( $_GET['display_stock'] ) ) {
				$stock_amount = $product_object->get_stock_quantity();
				/* Translators: %d stock amount */
				$formatted_name .= ' &ndash; ' . sprintf( __( 'Stock: %d', 'woocommerce' ), wc_format_stock_quantity_for_display( $stock_amount, $product_object ) );
			}

			$products[ $product_object->get_id() ] = rawurldecode( $formatted_name );
		}

		wp_send_json( apply_filters( 'woocommerce_json_search_found_products', $products ) );
	}

	/**
	 * Return calculated discount and sale price
	 */
	public static function checkout_order_bump_calculate_price() {
		$data    = $_REQUEST;
		$product = wc_get_product( $data['product_id'] );

		if ( $product ) {
			$discount_type = ( isset( $data['discount_type'] ) && $data['discount_type'] === 'percentage' ) ? $data['discount_type'] : 'simple';
			$discount      = empty( $data['discount'] ) ? 0 : $data['discount'];

			$discount_amount = $discount_type == 'percentage' ? ( $product->get_price() / 100 ) * $discount : $discount;
			$regular_price   = $product->get_price();
			$sale_price      = $product->get_price() - $discount_amount;

			wp_send_json( array(
				'regular_price'      => $regular_price,
				'sale_price'         => $sale_price,
				'discount_amount'    => $discount_amount,
				'regular_price_html' => wc_price( $regular_price ),
				'sale_price_html'    => wc_price( $sale_price ),
				'image_url'          => $product->get_image_id() ? wp_get_attachment_image_src( $product->get_image_id(),
					[ 100, 100 ] )[0] : false,
				'image_id'           => $product->get_image_id(),
			) );
		}

		wp_send_json( array( 'status' => 'error' ) );
		die;
	}

	/**
	 * Returns price of the given variation_id.
	 */
	public static function get_variation_price() {
		check_ajax_referer( 'iconic_wsb_nonce', '_ajax_nonce' );

		$variation_id = absint( filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( ! $variation_id ) {
			wp_send_json_error();
		}

		$variation = wc_get_product( $variation_id );

		wp_send_json_success(
			array(
				'variation_price_html' => Iconic_WSB_Order_Bump_Product_Page_Manager::get_price_html( $variation ),
			)
		);
	}

	/**
	 * Calculates prices for the given products_ids
	 *
	 * @return void
	 */
	public static function fbt_get_products_price() {
		check_ajax_referer( 'iconic_wsb_nonce', '_ajax_nonce' );
		$product_ids = filter_input( INPUT_POST, 'product_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( empty( $product_ids ) ) {
			wp_send_json(
				array(
					'html' => '',
				)
			);
		}

		$bump_ids   = array_map( 'absint', array_filter( $product_ids ) );
		$product_id = absint( filter_input( INPUT_POST, 'offer_product_id', FILTER_SANITIZE_NUMBER_INT ) );

		$data = array(
			'html' => Iconic_WSB_Order_Bump_Product_Page_Manager::get_price_html_for_bumps( $bump_ids, $product_id ),
		);

		wp_send_json( $data );
	}

	/**
	 * Checks and returns variation if exists exists and also updates price_html to return discounted price
	 * Used in FBT, Checkout Bump and After Checkout Bump
	 *
	 * @return void
	 */
	public static function checkout_get_variation() {
		check_ajax_referer( "iconic_wsb_nonce", "_ajax_nonce" );

		ob_start();
		if ( empty( $_REQUEST['product_id'] ) || empty( $_REQUEST['bump_id'] ) ) {
			wp_die();
		}
		
		$variation    = false;
		$product      = wc_get_product( absint( $_REQUEST['product_id'] ) );
		$bump_id      = $_REQUEST["bump_id"];
		$bump_type    = get_post_type( $bump_id );
		$variation_id = false;

		if ( ! $product ) {
			wp_die();
		}

		if ( $product->is_type( "variation" ) ) {
			$variation_id = $product->get_ID();
			$parent       = wc_get_product( $product->get_parent_id() );
			$variation    = $parent->get_available_variation( $variation_id );
		} else if ( $product->is_type( "variable" ) ) {
			$data_store   = WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, wp_unslash( $_REQUEST ) );
			$variation    = $variation_id ? $product->get_available_variation( $variation_id ) : false;
		}

		if ( $variation['variation_id'] ) {
			require_once ICONIC_WSB_PATH . "/inc/checkout/class-order-bump-at-checkout.php";
			require_once ICONIC_WSB_PATH . "/inc/checkout/abstracts/class-order-bump-checkout-abstract.php";
			require_once ICONIC_WSB_PATH . "/inc/checkout/class-order-bump-after-checkout.php";

			if ( $bump_type == "at_checkout_ob" ) {
				$bump = new Iconic_WSB_Order_Bump_At_Checkout( $_REQUEST['bump_id'] );
			} else {
				$bump = new Iconic_WSB_Order_Bump_After_Checkout( $_REQUEST['bump_id'] );
			}

			$variation['price_html'] = $bump->get_price_html( $variation['variation_id'] );
		}

		wp_send_json( $variation );
	}
}