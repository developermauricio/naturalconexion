<?php
/**
 * Frontend class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles
 */

defined( 'YITH_WCPB' ) || exit;

if ( ! class_exists( 'YITH_WCPB_Frontend' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the Frontend behaviors.
	 */
	class YITH_WCPB_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCPB_Frontend
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 * @deprecated 1.4.12
		 */
		public $version = YITH_WCPB_VERSION;

		/**
		 * Singleton implementation.
		 *
		 * @return YITH_WCPB_Frontend|YITH_WCPB_Frontend_Premium
		 */
		public static function get_instance() {
			/**
			 * The class.
			 *
			 * @var YITH_WCPB_Frontend|YITH_WCPB_Frontend_Premium $self
			 */
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
		}

		/**
		 * YITH_WCPB_Frontend constructor.
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Handle Cart.
			add_action( 'woocommerce_yith_bundle_add_to_cart', array( $this, 'woocommerce_yith_bundle_add_to_cart' ) );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woocommerce_add_to_cart_validation' ), 10, 6 );

			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woocommerce_add_cart_item_data' ), 10, 2 );
			add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart' ), 10, 6 );
			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'woocommerce_cart_item_remove_link' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woocommerce_cart_item_quantity' ), 10, 2 );
			add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_cart_item_quantity' ), 1, 2 );
			add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'update_cart_item_quantity' ), 1 );

			add_filter( 'woocommerce_cart_item_price', array( $this, 'woocommerce_cart_item_price' ), 99, 3 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'bundles_item_subtotal' ), 99, 3 );
			add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'bundles_item_subtotal' ), 10, 3 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'woocommerce_add_cart_item' ), 10, 2 );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'woocommerce_cart_item_removed' ), 10, 2 );
			add_action( 'woocommerce_cart_item_restored', array( $this, 'woocommerce_cart_item_restored' ), 10, 2 );

			add_filter( 'woocommerce_cart_contents_count', array( $this, 'woocommerce_cart_contents_count' ) );

			add_filter( 'woocommerce_cart_item_class', array( $this, 'add_cart_item_class_for_bundles' ), 10, 3 );

			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woocommerce_get_cart_item_from_session' ), 10, 3 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'remove_bundled_items_without_parent_bundle' ), 99 );

			// Handle Orders.
			add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'woocommerce_order_formatted_line_subtotal' ), 10, 3 );
			add_filter( 'woocommerce_checkout_create_order_line_item', array( $this, 'woocommerce_checkout_create_order_line_item' ), 10, 4 );
			add_filter( 'woocommerce_order_item_class', array( $this, 'add_order_item_class_for_bundles' ), 10, 3 );

			add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'woocommerce_order_item_needs_processing' ), 10, 3 );

			// Handle Order Again.
			add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'woocommerce_order_again_cart_item_data' ), 10, 2 );
			add_action( 'woocommerce_ordered_again', array( $this, 'woocommerce_ordered_again' ), 10, 3 );

			// Handle Shipping.
			add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'woocommerce_cart_shipping_packages' ), 99 );
		}

		/**
		 * Remove bundled items in cart if the bundle is not in cart
		 * (added to fix an issue when removing the bundle if YITH Dynamic Pricing is active)
		 *
		 * @since 1.2.18 Premium
		 */
		public function remove_bundled_items_without_parent_bundle() {
			if ( empty( WC()->cart->cart_contents ) ) {
				return;
			}
			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['bundled_by'] ) ) {
					$bundle_key = $cart_item['bundled_by'];
					if ( ! isset( WC()->cart->cart_contents[ $bundle_key ] ) ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
			}
		}


		/**
		 * Add cart item data for bundles when 'Order Again'
		 *
		 * @param array         $cart_item_data The cart item data.
		 * @param WC_Order_Item $item           The order item.
		 *
		 * @return array
		 * @since 1.2.11
		 */
		public function woocommerce_order_again_cart_item_data( $cart_item_data, $item ) {
			if ( $item instanceof WC_Order_Item_Product ) {

				$product = $item->get_product();
				if ( $product && $product->is_type( 'yith_bundle' ) ) {
					$cartstamp = $item->get_meta( '_cartstamp' );

					if ( $cartstamp ) {
						$cart_item_data['cartstamp']             = $cartstamp;
						$cart_item_data['bundled_items']         = array();
						$cart_item_data['yith_wcpb_order_again'] = true;
					}
				} else {
					$_bundled_by = $item->get_meta( '_bundled_by' );

					if ( $_bundled_by ) {
						$cart_item_data['yith_wcpb_order_again_bundled_item_to_remove'] = true;
					}
				}
			}

			return $cart_item_data;
		}


		/**
		 * Get mapping to transform cart-stamp data to item data
		 *
		 * @return array
		 * @since 1.4.7 Premium
		 */
		protected function get_cart_stamp_to_item_data_mapping() {
			return array();
		}

		/**
		 * Get bundled item data from its cart-stamp.
		 *
		 * @param array $item_cart_stamp The bundled item cart-stamp.
		 * @param array $cart_item_data  The cart item data.
		 *
		 * @return array|false
		 * @since 1.4.7 Premium
		 */
		protected function get_bundled_item_data_from_cart_stamp( $item_cart_stamp, $cart_item_data = array() ) {
			$quantity     = $item_cart_stamp['quantity'];
			$product_id   = $item_cart_stamp['product_id'];
			$variation_id = $item_cart_stamp['variation_id'] ?? false;
			$variation    = ! ! $variation_id ? wc_get_product( $variation_id ) : false;

			foreach ( $this->get_cart_stamp_to_item_data_mapping() as $cart_stamp_key => $cart_data_key ) {
				if ( isset( $item_cart_stamp[ $cart_stamp_key ] ) ) {
					$cart_item_data[ $cart_data_key ] = $item_cart_stamp[ $cart_stamp_key ];
				}
			}

			$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity );
			$cart_item_key  = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

			if ( 'product_variation' === get_post_type( $product_id ) ) {
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $variation_id );
			}

			$product_data = wc_get_product( ! ! $variation_id ? $variation_id : $product_id );
			$product_data->add_meta_data( 'yith_wcpb_is_bundled', true, true );

			$data = false;

			if ( ! ! $cart_item_key ) {
				$data = array(
					'key'  => $cart_item_key,
					'item' => apply_filters(
						'woocommerce_add_cart_item',
						array_merge(
							$cart_item_data,
							array(
								'product_id'   => $product_id,
								'variation_id' => $variation_id,
								'variation'    => $variation,
								'quantity'     => $quantity,
								'data'         => $product_data,
							)
						),
						$cart_item_key
					),
				);
			}

			return $data;
		}

		/**
		 * Handle order again cart.
		 *
		 * @param int   $order_id    The order ID.
		 * @param array $order_items The order items.
		 * @param array $cart        The cart.
		 *
		 * @since 1.4.7 Premium
		 */
		public function woocommerce_ordered_again( $order_id, $order_items, &$cart ) {
			foreach ( $cart as $key => $item ) {
				if ( isset( $item['yith_wcpb_order_again_bundled_item_to_remove'] ) ) {
					unset( $cart[ $key ] );
				}
			}

			$new_cart = array();

			foreach ( $cart as $key => $item ) {
				$new_cart[ $key ] = $item;

				if ( isset( $item['cartstamp'] ) ) {
					$bundled_items = array();

					foreach ( $item['cartstamp'] as $id => $item_cart_stamp ) {
						$cart_data                    = array( 'bundled_by' => $key );
						$cart_data['bundled_item_id'] = $id;

						$bundled_item_data = $this->get_bundled_item_data_from_cart_stamp( $item_cart_stamp, $cart_data );

						if ( $bundled_item_data ) {
							$bundled_item_key  = $bundled_item_data['key'];
							$bundled_item_cart = $bundled_item_data['item'];

							if ( $bundled_item_key && ! in_array( $bundled_item_key, $bundled_items, true ) ) {
								$bundled_items[] = $bundled_item_key;

								$new_cart[ $bundled_item_key ] = $bundled_item_cart;
							}
						}
					}

					$new_cart[ $key ]['bundled_items'] = $bundled_items;
				}
			}

			$cart = $new_cart;
		}

		/**
		 * Edit the count of cart contents
		 * exclude bundled items from the count
		 *
		 * @param int $count The count.
		 *
		 * @return int
		 */
		public function woocommerce_cart_contents_count( $count ) {
			$cart_contents = WC()->cart->cart_contents;

			$bundled_items_count = 0;
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				if ( ! empty( $cart_item['bundled_by'] ) ) {
					$bundled_items_count += $cart_item['quantity'];
				}
			}

			return intval( $count - $bundled_items_count );
		}


		/**
		 * Filter the table item class
		 *
		 * @param string $classname     The class name.
		 * @param array  $cart_item     The cart item.
		 * @param string $cart_item_key The cart item key.
		 *
		 * @return string
		 * @deprecated 1.4.4 | use YITH_WCPB_Frontend::add_cart_item_class_for_bundles or YITH_WCPB_Frontend::add_order_item_class_for_bundles
		 */
		public function table_item_class_bundle( $classname, $cart_item, $cart_item_key = '' ) {
			return $classname;
		}

		/**
		 * Add cart item classes for bundle and bundled items
		 *
		 * @param string $class_name    The class name.
		 * @param array  $cart_item     The cart item.
		 * @param string $cart_item_key The cart item key.
		 *
		 * @return string
		 */
		public function add_cart_item_class_for_bundles( $class_name, $cart_item, $cart_item_key = '' ) {
			$is_bundled_item = isset( $cart_item['bundled_by'] );
			$is_bundle       = isset( $cart_item['cartstamp'] );

			if ( $is_bundled_item ) {
				$class_name .= ' yith-wcpb-child-of-bundle-table-item';
			} elseif ( $is_bundle ) {
				$class_name .= ' yith-wcpb-bundle-table-item ';
			}

			$last_bundled_item = false;

			if ( $cart_item_key && $is_bundled_item ) {
				$cart           = WC()->cart->get_cart();
				$cart_item_keys = array_keys( $cart );
				$idx            = array_search( $cart_item_key, $cart_item_keys, true );
				if ( $idx && isset( $cart_item_keys[ $idx + 1 ] ) ) {
					$next_key       = $cart_item_keys[ $idx + 1 ];
					$next_cart_item = $cart[ $next_key ];
					if ( ! isset( $next_cart_item['bundled_by'] ) ) {
						$last_bundled_item = true;
					}
				} elseif ( $idx && ! isset( $keys[ $idx + 1 ] ) ) {
					$last_bundled_item = true;
				}
			}

			if ( $last_bundled_item ) {
				$class_name .= ' yith-wcpb-child-of-bundle-table-item--last ';
			}

			return $class_name;
		}

		/**
		 * Add cart item classes for bundle and bundled items
		 *
		 * @param string                $class_name The class name.
		 * @param WC_Order_Item_Product $item       The cart item.
		 * @param WC_Order              $order      The cart item key.
		 *
		 * @return string
		 */
		public function add_order_item_class_for_bundles( $class_name, $item, $order ) {
			$is_bundled_item = isset( $item['bundled_by'] );
			$is_bundle       = isset( $item['cartstamp'] );

			if ( $is_bundled_item ) {
				$class_name .= ' yith-wcpb-child-of-bundle-table-item';
			} elseif ( $is_bundle ) {
				$class_name .= ' yith-wcpb-bundle-table-item ';
			}

			$last_bundled_item = false;

			if ( $is_bundled_item ) {
				$items = $order->get_items();
				$keys  = array_keys( $items );
				$idx   = array_search( $item->get_id(), $keys, true );
				if ( $idx && isset( $keys[ $idx + 1 ] ) ) {
					$next_key       = $keys[ $idx + 1 ];
					$next_cart_item = $items[ $next_key ];
					if ( ! isset( $next_cart_item['bundled_by'] ) ) {
						$last_bundled_item = true;
					}
				} elseif ( $idx && ! isset( $keys[ $idx + 1 ] ) ) {
					$last_bundled_item = true;
				}
			}

			if ( $last_bundled_item ) {
				$class_name .= ' yith-wcpb-child-of-bundle-table-item--last ';
			}

			return $class_name;
		}

		/**
		 * Filter cart item data when adding a bundle to the cart, by adding the 'cartstamp' param if not exist
		 *
		 * @param array $cart_item_data The cart item data.
		 * @param int   $product_id     The product ID.
		 *
		 * @return array
		 */
		public function woocommerce_add_cart_item_data( $cart_item_data, $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product || ! $product->is_type( 'yith_bundle' ) ) {
				return $cart_item_data;
			}

			/**
			 * The bundle product.
			 *
			 * @var WC_Product_Yith_Bundle $product
			 */

			if ( isset( $cart_item_data['cartstamp'] ) && isset( $cart_item_data['bundled_items'] ) ) {
				return $cart_item_data;
			}

			$bundled_items = $product->get_bundled_items();
			if ( ! ! $bundled_items ) {
				$cartstamp = array();

				foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

					$id                   = $bundled_item->product_id;
					$bundled_product_type = $bundled_item->product->get_type();

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$bundled_product_quantity = isset( $_REQUEST[ apply_filters( 'woocommerce_product_yith_bundle_field_prefix', '', $product_id ) . 'yith_bundle_quantity_' . $bundled_item_id ] ) ? absint( $_REQUEST[ apply_filters( 'woocommerce_product_yith_bundle_field_prefix', '', $product_id ) . 'yith_bundle_quantity_' . $bundled_item_id ] ) : $bundled_item->get_quantity();

					$cartstamp[ $bundled_item_id ]['product_id'] = $id;
					$cartstamp[ $bundled_item_id ]['type']       = $bundled_product_type;
					$cartstamp[ $bundled_item_id ]['quantity']   = $bundled_product_quantity;
					$cartstamp[ $bundled_item_id ]               = apply_filters( 'woocommerce_yith_bundled_item_cart_item_identifier', $cartstamp[ $bundled_item_id ], $bundled_item_id );
				}

				$cart_item_data['cartstamp']     = $cartstamp;
				$cart_item_data['bundled_items'] = array();
			}

			return $cart_item_data;
		}

		/**
		 * Add to cart for Bundle products.
		 *
		 * @param string     $cart_item_key  The cart item key.
		 * @param int        $product_id     The product ID.
		 * @param int        $quantity       The quantity.
		 * @param string|int $variation_id   The variation ID.
		 * @param array      $variation      Variation attribute values.
		 * @param array      $cart_item_data The cart item data.
		 */
		public function woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			if ( isset( $cart_item_data['cartstamp'] ) && ! isset( $cart_item_data['bundled_by'] ) ) {
				$bundled_items_cart_data = array( 'bundled_by' => $cart_item_key );

				foreach ( $cart_item_data['cartstamp'] as $bundled_item_id => $bundled_item_stamp ) {
					$bundled_item_cart_data                    = $bundled_items_cart_data;
					$bundled_item_cart_data['bundled_item_id'] = $bundled_item_id;

					$item_quantity = $bundled_item_stamp['quantity'];
					$i_quantity    = $item_quantity * $quantity;
					$prod_id       = $bundled_item_stamp['product_id'];

					$bundled_item_cart_key = $this->bundled_add_to_cart( $product_id, $prod_id, $i_quantity, $variation_id, '', $bundled_item_cart_data );

					if ( $bundled_item_cart_key && ! in_array( $bundled_item_cart_key, WC()->cart->cart_contents[ $cart_item_key ]['bundled_items'], true ) ) {
						WC()->cart->cart_contents[ $cart_item_key ]['bundled_items'][] = $bundled_item_cart_key;
						WC()->cart->cart_contents[ $cart_item_key ]['yith_parent']     = $cart_item_key;
					}
				}
			}
		}

		/**
		 * Add single bundled item to cart.
		 *
		 * @param int        $bundle_id      The bundle product ID.
		 * @param int        $product_id     The product ID.
		 * @param int        $quantity       The quantity.
		 * @param string|int $variation_id   The variation ID.
		 * @param string     $variation      Variation attribute values.
		 * @param array      $cart_item_data Cart item data.
		 *
		 * @return false|string
		 */
		public function bundled_add_to_cart( $bundle_id, $product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_item_data = array() ) {
			if ( $quantity <= 0 ) {
				return false;
			}

			$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity );
			$cart_id        = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );
			$cart_item_key  = WC()->cart->find_product_in_cart( $cart_id );

			if ( 'product_variation' === get_post_type( $product_id ) ) {
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $variation_id );
			}

			$product = wc_get_product( ! ! $variation_id ? $variation_id : $product_id );
			$product->add_meta_data( 'yith_wcpb_is_bundled', true, true );

			if ( ! $cart_item_key ) {
				$cart_item_key                              = $cart_id;
				WC()->cart->cart_contents[ $cart_item_key ] = apply_filters(
					'woocommerce_add_cart_item',
					array_merge(
						$cart_item_data,
						array(
							'product_id'   => $product_id,
							'variation_id' => $variation_id,
							'variation'    => $variation,
							'quantity'     => $quantity,
							'data'         => $product,
						)
					),
					$cart_item_key
				);
			}

			return $cart_item_key;
		}

		/**
		 * Remove 'remove link' for bundled product in cart.
		 *
		 * @param string $link          The link.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return string
		 */
		public function woocommerce_cart_item_remove_link( $link, $cart_item_key ) {
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['bundled_by'] ) ) {
				$bundle_cart_key = WC()->cart->cart_contents[ $cart_item_key ]['bundled_by'];
				if ( isset( WC()->cart->cart_contents[ $bundle_cart_key ] ) ) {
					return '';
				}
			}

			return $link;
		}

		/**
		 * Filter the cart item quantity field to show a fixed quantity instead of the numeric field for bundled items.
		 *
		 * @param int    $quantity      Product quantity field.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return int
		 */
		public function woocommerce_cart_item_quantity( $quantity, $cart_item_key ) {
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['bundled_by'] ) ) {
				return WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
			}

			return $quantity;
		}

		/**
		 * Update cart item quantity
		 *
		 * @param string $cart_item_key Cart item key.
		 * @param int    $quantity      Product quantity.
		 */
		public function update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
			if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ] ) ) {

				if ( $quantity <= 0 ) {
					$quantity = 0;
				} else {
					$quantity = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
				}

				if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ]['cartstamp'] ) && ! isset( WC()->cart->cart_contents[ $cart_item_key ]['bundled_by'] ) ) {
					$stamp = WC()->cart->cart_contents[ $cart_item_key ]['cartstamp'];
					foreach ( WC()->cart->cart_contents as $key => $value ) {
						if ( isset( $value['bundled_by'] ) && $cart_item_key === $value['bundled_by'] ) {
							$bundle_item_id  = $value['bundled_item_id'];
							$bundle_quantity = $stamp[ $bundle_item_id ]['quantity'];
							WC()->cart->set_quantity( $key, $quantity * $bundle_quantity, false );
						}
					}
				}
			}
		}

		/**
		 * Remove cart item price for bundled items.
		 *
		 * @param string $price         The price.
		 * @param array  $cart_item     Cart item data.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return string
		 */
		public function woocommerce_cart_item_price( $price, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['bundled_by'];
				if ( isset( WC()->cart->cart_contents[ $bundle_cart_key ] ) ) {
					return '';
				}
			}

			return $price;
		}

		/**
		 * Filter cart item subtotal for bundled items and bundle products.
		 *
		 * @param string $subtotal      The price.
		 * @param array  $cart_item     Cart item data.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return string
		 */
		public function bundles_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['bundled_by'];
				if ( isset( WC()->cart->cart_contents[ $bundle_cart_key ] ) ) {
					return '';
				}
			}
			if ( isset( $cart_item['bundled_items'] ) ) {
				if ( $cart_item['data']->get_price() === 0 ) {
					return '';
				}
			}

			return $subtotal;
		}

		/**
		 * Render "add to cart" template for bundle products.
		 */
		public function woocommerce_yith_bundle_add_to_cart() {
			/**
			 * The bundle product.
			 *
			 * @var WC_Product_Yith_Bundle $product
			 */
			global $product;
			$bundled_items = $product->get_bundled_items();
			if ( $bundled_items ) {
				wc_get_template( 'single-product/add-to-cart/yith-bundle.php', array(), '', YITH_WCPB_TEMPLATE_PATH . '/' );
			}
		}

		/**
		 * Add to cart validation for bundle products.
		 *
		 * @param bool       $add_flag         Result of the validation.
		 * @param int        $product_id       Product ID.
		 * @param int        $product_quantity Product quantity.
		 * @param string|int $variation_id     Variation ID.
		 * @param array      $variations       Variation attributes.
		 * @param array      $cart_item_data   Cart item data.
		 *
		 * @return bool
		 */
		public function woocommerce_add_to_cart_validation( $add_flag, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {
			$product = wc_get_product( $product_id );

			if ( $product && $product->is_type( 'yith_bundle' ) && get_option( 'woocommerce_manage_stock' ) === 'yes' ) {
				/**
				 * Bundle product.
				 *
				 * @var WC_Product_Yith_Bundle $product
				 */
				$bundled_items = $product->get_bundled_items();
				foreach ( $bundled_items as $bundled_item ) {
					$bundled_prod = $bundled_item->get_product();
					if ( ! $bundled_prod->has_enough_stock( intval( $bundled_item->get_quantity() ) * intval( $product_quantity ) ) ) {
						wc_add_notice( __( 'You cannot add this quantity of items, because there are not enough in stock.', 'yith-woocommerce-product-bundles' ), 'error' );

						return false;
					}
				}
			}

			return $add_flag;
		}

		/**
		 * Set bundled item price to zero in cart.
		 *
		 * @param array  $cart_item Cart item data.
		 * @param string $cart_key  Cart item key.
		 *
		 * @return array
		 */
		public function woocommerce_add_cart_item( $cart_item, $cart_key ) {

			$cart_contents = WC()->cart->cart_contents;

			if ( isset( $cart_item['bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['bundled_by'];
				if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
					/**
					 * The product.
					 *
					 * @var WC_Product $product
					 */
					$product = $cart_item['data'];
					$product->set_price( 0 );
					$product->add_meta_data( 'bundled_item_price_zero', true, true );
				}
			}

			return $cart_item;
		}

		/**
		 * Remove bundled items when the related bundle product is removed from cart.
		 *
		 * @param string  $cart_item_key Cart item key.
		 * @param WC_Cart $cart          The Cart.
		 */
		public function woocommerce_cart_item_removed( $cart_item_key, $cart ) {

			if ( ! empty( $cart->removed_cart_contents[ $cart_item_key ]['bundled_items'] ) ) {

				$bundled_item_cart_keys = $cart->removed_cart_contents[ $cart_item_key ]['bundled_items'];

				foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {

					if ( ! empty( $cart->cart_contents[ $bundled_item_cart_key ] ) ) {
						$cart->removed_cart_contents[ $bundled_item_cart_key ] = $cart->cart_contents[ $bundled_item_cart_key ];

						unset( $cart->cart_contents[ $bundled_item_cart_key ] );

						do_action( 'woocommerce_cart_item_removed', $bundled_item_cart_key, $cart );
					}
				}
			}
		}

		/**
		 * Restore bundled items when the related bundle is restored in cart.
		 *
		 * @param string  $cart_item_key Cart item key.
		 * @param WC_Cart $cart          The Cart.
		 *
		 * @since  1.0.19
		 */
		public function woocommerce_cart_item_restored( $cart_item_key, $cart ) {
			if ( ! empty( $cart->cart_contents[ $cart_item_key ]['bundled_items'] ) ) {
				$bundled_item_cart_keys = $cart->cart_contents[ $cart_item_key ]['bundled_items'];
				foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {
					$cart->restore_cart_item( $bundled_item_cart_key );
				}
			}
		}

		/**
		 * Get cart item from session.
		 *
		 * @param array  $cart_item           Cart item.
		 * @param array  $item_session_values Session stored values.
		 * @param string $cart_item_key       Cart item key.
		 *
		 * @return array
		 */
		public function woocommerce_get_cart_item_from_session( $cart_item, $item_session_values, $cart_item_key ) {
			$cart_contents = ! empty( WC()->cart ) ? WC()->cart->cart_contents : '';
			if ( isset( $item_session_values['bundled_items'] ) && ! empty( $item_session_values['bundled_items'] ) ) {
				$cart_item['bundled_items'] = $item_session_values['bundled_items'];
			}

			if ( isset( $item_session_values['cartstamp'] ) ) {
				$cart_item['cartstamp'] = $item_session_values['cartstamp'];
			}

			if ( isset( $item_session_values['bundled_by'] ) ) {
				$cart_item['bundled_by']      = $item_session_values['bundled_by'];
				$cart_item['bundled_item_id'] = $item_session_values['bundled_item_id'];
				$bundle_cart_key              = $cart_item['bundled_by'];

				if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
					/**
					 * The product.
					 *
					 * @var WC_Product $product
					 */
					$product = $cart_item['data'];
					$product->set_price( 0 );
				}
			}

			return $cart_item;
		}

		/*
		|--------------------------------------------------------------------------
		| Orders
		|--------------------------------------------------------------------------
		|
		| Handle orders.
		*/

		/**
		 * Remove subtotal for bundled items in order.
		 *
		 * @param string        $subtotal Subtotal.
		 * @param WC_Order_Item $item     The item.
		 * @param WC_Order      $order    The order.
		 *
		 * @return string
		 */
		public function woocommerce_order_formatted_line_subtotal( $subtotal, $item, $order ) {
			if ( isset( $item['bundled_by'] ) ) {
				return '';
			}

			return $subtotal;
		}

		/**
		 * Add meta in order
		 *
		 * @param int    $item_id       Item UD.
		 * @param array  $values        Values.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @deprecated 1.2.11
		 */
		public function woocommerce_add_order_item_meta( $item_id, $values, $cart_item_key ) {
			// Do nothing.
		}

		/**
		 * Add bundle data to order items.
		 *
		 * @param WC_Order_Item_Product $item          Order item.
		 * @param string                $cart_item_key Cart item key.
		 * @param array                 $cart_item     Cart item data.
		 * @param WC_Order              $order         The order.
		 *
		 * @since 1.2.11
		 */
		public function woocommerce_checkout_create_order_line_item( $item, $cart_item_key, $cart_item, $order ) {
			$is_bundle       = isset( $cart_item['cartstamp'] );
			$is_bundled_item = isset( $cart_item['bundled_by'] );
			$meta_to_store   = array();

			if ( $is_bundle ) {
				$meta_to_store = array(
					'_cartstamp' => $cart_item['cartstamp'],
				);
			} elseif ( $is_bundled_item ) {
				$meta_to_store = array(
					'_bundled_by' => $cart_item['bundled_by'],
				);
			}

			if ( $meta_to_store ) {
				foreach ( $meta_to_store as $key => $value ) {
					$item->add_meta_data( $key, $value );
				}
			}
		}

		/**
		 * Filter shipping packages
		 *
		 * @param array $packages Shipping packages.
		 *
		 * @return array
		 */
		public function woocommerce_cart_shipping_packages( $packages ) {

			if ( ! empty( $packages ) ) {
				foreach ( $packages as $package_key => $package ) {
					if ( ! empty( $package['contents'] ) ) {
						foreach ( $package['contents'] as $cart_item => $cart_item_data ) {
							if ( isset( $cart_item_data['bundled_items'] ) && isset( $cart_item_data['yith_parent'] ) ) {
								// Handle singular shipping for the whole bundle.
								$parent_bundle_key = $cart_item_data['yith_parent'];
								if ( isset( $package['contents'][ $parent_bundle_key ] ) ) {
									unset( $packages[ $package_key ]['contents'][ $parent_bundle_key ] );
								}
							}
						}
					}
				}
			}

			return $packages;
		}

		/**
		 * Set order_needs_processing to false for Bundles
		 *
		 * @param bool       $needs_processing "Needs processing" flag.
		 * @param WC_Product $product          The product.
		 * @param int        $order_id         The order ID.
		 *
		 * @return bool
		 */
		public function woocommerce_order_item_needs_processing( $needs_processing, $product, $order_id ) {
			if ( $product->is_type( 'yith_bundle' ) ) {
				return false;
			}

			return $needs_processing;
		}

		/**
		 * Return the current page type (product, bundle_product, cart, checkout, ...).
		 *
		 * @return array
		 * @since 1.4.10
		 */
		protected function get_current_page_info() {
			static $info = null;
			global $post;
			if ( is_null( $info ) ) {
				$info = array(
					'cart'       => is_cart(),
					'checkout'   => is_checkout(),
					'product'    => is_product(),
					'my-account' => is_account_page(),
					'bundle'     => false,
					'widget'     => ! ! is_active_widget( false, false, 'yith_wcpb_bundle_widget' ),
				);

				if ( is_product() ) {
					$product = wc_get_product();
					if ( $product && $product->is_type( 'yith_bundle' ) ) {
						$info['bundle'] = true;
					}
				} elseif ( is_singular() && is_a( $post, 'WP_Post' ) ) {
					$ids  = array();
					$skus = array();

					if ( has_shortcode( $post->post_content, 'product_page' ) ) {
						$info['product'] = true;

						if ( preg_match_all( '/' . get_shortcode_regex( array( 'product_page' ) ) . '/s', $post->post_content, $matches ) && array_key_exists( 2, $matches ) ) {

							if ( ! empty( $matches[3] ) ) {
								foreach ( $matches[3] as $shortcode_attrs ) {
									if ( preg_match( '/id="?(\d+)"?/', $shortcode_attrs, $id ) ) {
										$ids[] = $id[1];
									}

									if ( preg_match( '/sku="(\s+)"/', $shortcode_attrs, $sku ) ) {
										$skus[] = $sku[1];
									}
								}
							}
						}
					}

					foreach ( $ids as $id ) {
						$product = wc_get_product( $id );
						if ( $product && $product->is_type( 'yith_bundle' ) ) {
							$info['bundle'] = true;
							break;
						}
					}

					if ( ! $info['bundle'] ) {
						foreach ( $skus as $sku ) {
							$id      = wc_get_product_id_by_sku( $sku );
							$product = wc_get_product( $id );
							if ( $product && $product->is_type( 'yith_bundle' ) ) {
								$info['bundle'] = true;
								break;
							}
						}
					}

					if ( ! $info['bundle'] ) {
						if ( has_shortcode( $post->post_content, 'bundle_add_to_cart' ) ) {
							$info['bundle'] = true;
						}
					}
				}

				$info = apply_filters( 'yith_wcpb_get_current_page_info', $info );
			}

			return $info;
		}

		/**
		 * Check if current page is one of the specified ones.
		 *
		 * @param string|array $pages The pages.
		 *
		 * @return bool
		 * @since 1.4.10
		 */
		protected function current_page_is( $pages = array() ) {
			$pages         = (array) $pages;
			$current_pages = array_keys( array_filter( $this->get_current_page_info() ) );

			return ! ! array_intersect( $current_pages, $pages );
		}

		/**
		 * Return assets to enqueue
		 *
		 * @return array
		 * @since 1.4.10
		 */
		protected function get_assets() {
			$assets = array(
				'styles'  => array(
					'yith_wcpb_bundle_frontend_style' => array(
						'path'  => YITH_WCPB_ASSETS_URL . '/css/frontend.css',
						'deps'  => array(),
						'where' => array( 'bundle', 'cart', 'checkout', 'my-account', 'widget' ),
					),
				),
				'scripts' => array(),
			);

			return apply_filters( 'yith_wcpb_get_frontend_assets', $assets );
		}

		/**
		 * Enqueue scripts.
		 */
		public function enqueue_scripts() {
			$assets  = $this->get_assets();
			$styles  = $assets['styles'] ?? array();
			$scripts = $assets['scripts'] ?? array();

			foreach ( $styles as $handle => $style ) {
				$defaults = array(
					'version' => YITH_WCPB_VERSION,
					'deps'    => array(),
					'path'    => false,
					'where'   => false,
				);
				$style    = wp_parse_args( $style, $defaults );

				if ( $style['path'] ) {
					wp_register_style( $handle, $style['path'], $style['deps'], $style['version'] );

					if ( false === $style['where'] || $this->current_page_is( $style['where'] ) ) {
						wp_enqueue_style( $handle );
					}
				}
			}

			foreach ( $scripts as $handle => $script ) {
				$defaults = array(
					'version'  => YITH_WCPB_VERSION,
					'deps'     => array(),
					'path'     => false,
					'where'    => false,
					'footer'   => true,
					'localize' => false,
				);
				$script   = wp_parse_args( $script, $defaults );

				if ( $script['path'] ) {
					wp_register_script( $handle, $script['path'], $script['deps'], $style['version'], $script['footer'] );

					if ( $script['localize'] ) {
						foreach ( $script['localize'] as $object_name => $object ) {
							wp_localize_script( $handle, $object_name, $object );
						}
					}

					if ( false === $script['where'] || $this->current_page_is( $script['where'] ) ) {
						wp_enqueue_script( $handle );
					}
				}
			}
		}

	}
}
/**
 * Unique access to instance of YITH_WCPB_Frontend class
 *
 * @return YITH_WCPB_Frontend|YITH_WCPB_Frontend_Premium
 */
function yith_wcpb_frontend() {
	return YITH_WCPB_Frontend::get_instance();
}
