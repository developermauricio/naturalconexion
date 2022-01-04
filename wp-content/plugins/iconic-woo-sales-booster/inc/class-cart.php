<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Cart
 *
 * @class    Iconic_WSB_Order_Bump
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Cart {
	/**
	 * Run
	 */
	public static function run() {
		self::hooks();
	}

	/**
	 * Register hooks
	 */
	public static function hooks() {
		add_action( 'wp_loaded', array( __CLASS__, 'add_to_cart_action' ), 11 );
	}

	/**
	 * Handler for iconic-wsb-products-add-to-cart request (Frequently Bought Together)
	 */
	public static function add_to_cart_action() {
		if ( ! isset( $_REQUEST['iconic-wsb-add-selected'] ) || empty( $_REQUEST['iconic-wsb-products-add-to-cart'] ) || ! is_array( $_REQUEST['iconic-wsb-products-add-to-cart'] ) ) {
			return;
		}

		$single_product_id  = $_REQUEST["iconic-wsb-fbt-this-product"];
		$product_ids        = array_map( 'absint', array_filter( $_REQUEST['iconic-wsb-products-add-to-cart'] ) );
		$redirect_after_add = get_option( 'woocommerce_cart_redirect_after_add' );
		$message            = '';

		add_filter( 'pre_woocommerce_cart_redirect_after_add', function () {
			return 'no';
		} );

		$added_all_to_cart = true;

		foreach ( $product_ids as $product_add_to_cart ) {
			$product                     = wc_get_product( $product_add_to_cart );
			$quantity                    = 1;
			$add_to_cart                 = false;
			$variation_dropdown_name     = "iconic-wsb-products-add-to-cart-variation-" . $product_add_to_cart;
			$variation_attributes_hidden = "iconic-wsb-bump-product_attributes-" . $product_add_to_cart;
			$meta_data                   = array(
				"iconic_wsb_fbt" => $single_product_id,
			);

			if ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ) {
				if ( ! empty( $_REQUEST[ $variation_dropdown_name ] ) ) {
					$variation_id   = absint( filter_input( INPUT_POST, $variation_dropdown_name, FILTER_SANITIZE_NUMBER_INT ) );
					$variation_data = filter_input( INPUT_POST, $variation_attributes_hidden );
					$variation_data = json_decode( $variation_data, true );

					$meta_data         = apply_filters( 'iconic_wsb_fbt_before_cart_metadata', $meta_data, $variation_id );
					$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_add_to_cart, $quantity, $variation_id, $meta_data );

					if ( ! $passed_validation ) {
						continue;
					}

					$add_to_cart = WC()->cart->add_to_cart( $product_add_to_cart, $quantity, $variation_id, $variation_data, $meta_data );
				}
			} else {
				$meta_data         = apply_filters( 'iconic_wsb_fbt_before_cart_metadata', $meta_data, $product_add_to_cart );
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_add_to_cart, $quantity, 0, $meta_data );

				if ( ! $passed_validation ) {
					continue;
				}

				$add_to_cart = WC()->cart->add_to_cart( $product_add_to_cart, $quantity, 0, array(), $meta_data );
			}

			if ( false === $add_to_cart ) {
				$added_all_to_cart = false;
				// Translators: Product title.
				$message = sprintf( esc_html__( "Sorry, '%s' could not be added to your cart.", 'iconic-wsb' ), $product->get_title() );
			}
		}

		if ( $added_all_to_cart ) {
			$message = esc_html__( 'All products were added to your cart.', 'iconic-wsb' );
		}

		// To prevent the main product from adding to cart twice.
		unset( $_REQUEST['add-to-cart'] );
		unset( $_REQUEST['variation_id'] );

		$redirect = apply_filters( 'iconic_wsb_cart_redirect_after_add', false );

		if ( wp_doing_ajax() ) {
			wp_send_json(
				array(
					'success' => $added_all_to_cart,
					'message' => $message,
				)
			);
		} else {
			$message_type = $added_all_to_cart ? 'success' : 'error';
			wc_add_notice( $message, $message_type );
		}

		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		} elseif ( 'yes' === $redirect_after_add ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	}

	/**
	 * Check if product in cart
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public static function is_product_in_cart( $product_id ) {
		return self::get_cart_item_by_product_id( $product_id ) != false;
	}

	/**
	 * Get cart item form WC_Cart by product id
	 *
	 * @param int $needle_product_id
	 *
	 * @return bool
	 */
	public static function get_cart_item_by_product_id( $needle_product_id ) {
		if ( empty( $needle_product_id ) ) {
			return false;
		}

		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return false;
		}

		$match               = false;
		$needle_product_type = $needle_product->get_type();

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$cart_item_product_id   = $cart_item['product_id'];
			$cart_item_variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$cart_item_parent_id    = $cart_item_variation_id ? wp_get_post_parent_id( $cart_item_variation_id ) : 0;

			if ( 'variable' === $needle_product_type && $cart_item_parent_id === $needle_product_id ) {
				$match = true;
			} elseif ( 'variation' === $needle_product_type && $cart_item_variation_id === $needle_product_id ) {
				$match = true;
			} elseif ( $cart_item_product_id === $needle_product_id ) {
				$match = true;
			}

			if ( $match ) {
				return $cart_item;
			}
		}

		return false;
	}

	/**
	 * Removes any product from cart which has meta 'iconic_wsb_after_checkout' or 'iconic_wsb_at_checkout' which means
	 * the product was added by Woocoomerce Sales Booster.
	 *
	 * @param str $meta_key Possible values: 'iconic_wsb_after_checkout' & 'iconic_wsb_at_checkout'.
	 *
	 */
	public static function remove_previously_added_item( $meta_key ) {
		global $woocommerce;
		$cart_item = self::get_cart_item( $meta_key );
		WC()->cart->remove_cart_item( $cart_item['key'] );
	}

	/**
	 * Loops through all the cart items to find the item added by Woocommerce sales booster.
	 *
	 * @param str $meta_key Possible values: 'iconic_wsb_after_checkout' & 'iconic_wsb_at_checkout'.
	 *
	 * @return $cart_item | false
	 */
	public static function get_cart_item( $meta_key ) {
		global $woocommerce;

		foreach ( $woocommerce->cart->get_cart() as $key => $cart_item ) {
			if ( isset( $cart_item[ $meta_key ] ) ) {
				return $cart_item;
			}
		}

		return false;
	}

	/**
	 * Returns variation data for the product which is in cart, if no product is in cart for that bump_id then returns
	 * false.
	 *
	 * @param str $meta_key Possible values: 'iconic_wsb_after_checkout' & 'iconic_wsb_at_checkout'.
	 *
	 * @return array variation_data | false
	 */
	public static function get_cart_item_variation_data( $meta_key ) {
		$cart_item = self::get_cart_item( $meta_key );
		if ( isset( $cart_item['variation'] ) ) {
			return self::remove_variation_key_prefix( $cart_item['variation'] );
		} else if ( is_a( $cart_item["data"], "WC_Product_Variable" ) || is_a( $cart_item["data"], "WC_Product_Variation" ) ) {
			$variation_data = $cart_item["data"]->get_variation_attributes();

			return self::remove_variation_key_prefix( $variation_data );
		}

		return false;
	}

	/**
	 * Remove from cart by product id
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public static function remove_from_cart( $product_id ) {
		if ( self::is_product_in_cart( $product_id ) ) {
			foreach ( WC()->cart->cart_contents as $key => $cart_item ) {
				$product_item_id = empty( $cart_item['variation_id'] ) ? $cart_item['product_id'] : $cart_item['variation_id'];

				if ( $product_item_id == $product_id ) {
					WC()->cart->remove_cart_item( $key );
				}
			}
		}

		return true;
	}

	/**
	 * Add product to WC cart
	 *
	 * @param int|WC_Product $product
	 * @param int            $quantity
	 * @param array          $metadata
	 * @param array          $variation_data
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function add_to_cart( $product, $quantity = 1, $metadata = array(), $variation_data = null ) {
		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;

		if ( $product ) {
			$variation_id = $product->is_type( 'variable' ) ? $product->get_id() : null;
			$product_id   = $product->is_type( 'variable' ) ? $product->get_parent_id() : $product->get_id();
			$metadata     = empty( $metadata ) ? null : $metadata;

			// If variation_data is not provided, let's fetch it from the variation.
			if ( $product->is_type( 'variation' ) && $variation_data == null ) {
				$variation_data = array();
				if ( $variation_data == null ) {
					foreach ( $product->get_variation_attributes() as $taxonomy => $term_names ) {
						$taxonomy                                = str_replace( "attribute_", "", $taxonomy );
						$attribute_label_name                    = wc_attribute_label( $taxonomy );
						$variation_data[ $attribute_label_name ] = $term_names;
					}
				}
			}

			if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $metadata ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes 'attribute_' from the array key
	 *
	 * @param arr $variation_data
	 *
	 * @return void
	 */
	public static function remove_variation_key_prefix( $variation_data ) {
		$result_arr = array();
		if ( $variation_data && is_array( $variation_data ) ) {
			foreach ( $variation_data as $attribute_key => $attribute_value ) {
				if ( strpos( $attribute_key, "attribute_" ) === 0 ) {
					$attribute_key = substr( $attribute_key, 10 ); //remove 'attribute_'
				}
				$result_arr[ $attribute_key ] = $attribute_value;
			}
		}

		return $result_arr;
	}
}