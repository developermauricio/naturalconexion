<?php
/**
 * Bundle Product class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles
 */

defined( 'YITH_WCPB' ) || exit;

// phpcs:disable Generic.Classes.DuplicateClassName.Found

if ( ! class_exists( 'WC_Product_Yith_Bundle' ) ) {
	/**
	 * Class WC_Product_Yith_Bundle
	 */
	class WC_Product_Yith_Bundle extends WC_Product {

		/**
		 * Bundle data.
		 *
		 * @var array
		 */
		public $bundle_data = array();

		/**
		 * Bundled items.
		 *
		 * @var YITH_WC_Bundled_Item[]
		 */
		private $bundled_items;

		/**
		 * WC_Product_Yith_Bundle constructor.
		 *
		 * @param int|WC_Product|object $product Product to init.
		 */
		public function __construct( $product ) {
			parent::__construct( $product );

			$this->bundle_data = get_post_meta( $this->get_id(), '_yith_wcpb_bundle_data', true );

			if ( ! empty( $this->bundle_data ) ) {
				$this->load_items();
			}

		}

		/**
		 * Get product type.
		 *
		 * @return string
		 */
		public function get_type() {
			return 'yith_bundle';
		}

		/**
		 * Load bundled items
		 *
		 * @since  1.0.0
		 */
		private function load_items() {
			$virtual = true;
			foreach ( $this->bundle_data as $b_item_id => $b_item_data ) {
				$b_item = new YITH_WC_Bundled_Item( $this, $b_item_id );
				if ( $b_item->exists() ) {
					$this->bundled_items[ $b_item_id ] = $b_item;
					if ( ! $b_item->product->is_virtual() ) {
						$virtual = false;
					}
				}
			}
			$this->set_virtual( $virtual );
		}

		/**
		 * Return bundled items array
		 *
		 * @return YITH_WC_Bundled_Item[]
		 */
		public function get_bundled_items() {
			return ! empty( $this->bundled_items ) ? $this->bundled_items : array();
		}

		/**
		 * Returns false if the product cannot be bought.
		 *
		 * @return bool
		 */
		public function is_purchasable() {
			$purchasable = true;

			if ( ! $this->exists() ) {
				$purchasable = false;
			} elseif ( $this->get_price() === '' ) {
				$purchasable = false;

			} elseif ( $this->get_status() !== 'publish' && ! current_user_can( 'edit_post', $this->get_id() ) ) {
				$purchasable = false;
			}

			// Check bundle items are purchasable.
			$bundled_items = $this->get_bundled_items();
			foreach ( $bundled_items as $bundled_item ) {
				if ( ! $bundled_item->get_product()->is_purchasable() ) {
					$purchasable = false;
				}
			}

			return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
		}

		/**
		 * Returns true if all items is in stock
		 *
		 * @return bool
		 */
		public function all_items_in_stock() {
			$response = true;

			$bundled_items = $this->get_bundled_items();
			foreach ( $bundled_items as $bundled_item ) {
				if ( ! $bundled_item->get_product()->is_in_stock() ) {
					$response = false;
				}
			}

			return $response;
		}

		/**
		 * Returns true if one item at least is variable product.
		 *
		 * @return bool
		 */
		public function has_variables() {
			return false;
		}

		/**
		 * Get the add to cart url used in loops.
		 *
		 * @return string
		 */
		public function add_to_cart_url() {
			$url = $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->get_id() ) ) : get_permalink( $this->get_id() );

			return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
		}

		/**
		 * Get the add to cart button text
		 *
		 * @return string
		 */
		public function add_to_cart_text() {
			$text = $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock() ? __( 'Add to cart', 'woocommerce' ) : __( 'Read more', 'woocommerce' );

			return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
		}


		/**
		 * Get the title of the post.
		 *
		 * @access public
		 * @return string
		 */
		public function get_title() {
			$title = get_the_title( $this->get_id() );

			if ( $this->get_parent_id() > 0 ) {
				$title = get_the_title( $this->get_parent_id() ) . ' &rarr; ' . $title;
			}

			return apply_filters( 'woocommerce_product_title', $title, $this );
		}

		/**
		 * Sync grouped products with the children lowest price (so they can be sorted by price accurately).
		 *
		 * @access public
		 * @return void
		 */
		public function grouped_product_sync() {
			if ( ! $this->get_parent_id() ) {
				return;
			}

			$children_by_price = get_posts(
				array(
					'post_parent'    => $this->get_parent_id(),
					'orderby'        => 'meta_value_num',
					'order'          => 'asc',
					'meta_key'       => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'posts_per_page' => 1,
					'post_type'      => 'product',
					'fields'         => 'ids',
				)
			);

			if ( $children_by_price ) {
				foreach ( $children_by_price as $child ) {
					$child_price = get_post_meta( $child, '_price', true );
					update_post_meta( $this->get_parent_id(), '_price', $child_price );
				}
			}

			delete_transient( 'wc_products_onsale' );

			do_action( 'woocommerce_grouped_product_sync', $this->get_id(), $children_by_price );
		}

		/**
		 * Retrieve the bundle product version
		 *
		 * @return string
		 * @since 1.4.0
		 */
		public function get_bundle_product_version() {
			static $version = false;
			if ( false === $version ) {
				$version = $this->get_meta( '_yith_bundle_product_version' );
				if ( ! $version ) {
					$version = '1.0.0';
				}
			}

			return $version;
		}

	}
}
