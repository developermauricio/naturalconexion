<?php
/**
 * Bundled Item class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles
 */

defined( 'YITH_WCPB' ) || exit;

// phpcs:disable Generic.Classes.DuplicateClassName.Found

if ( ! class_exists( 'YITH_WC_Bundled_Item' ) ) {
	/**
	 * Class YITH_WC_Bundled_Item
	 */
	class YITH_WC_Bundled_Item {

		/**
		 * Item ID.
		 *
		 * @var int
		 */
		public $item_id;

		/**
		 * Product ID.
		 *
		 * @var int
		 */
		public $product_id;

		/**
		 * The product.
		 *
		 * @var WC_Product|false
		 */
		public $product = false;

		/**
		 * Quantity.
		 *
		 * @var int|mixed
		 */
		private $quantity;

		/**
		 * The parent bundle product.
		 *
		 * @var WC_Product_Yith_Bundle
		 */
		public $parent;

		/**
		 * YITH_WC_Bundled_Item constructor.
		 *
		 * @param WC_Product_Yith_Bundle $parent    The parent bundle product.
		 * @param int                    $item_id   Item ID.
		 * @param array|false            $item_data Item data.
		 */
		public function __construct( $parent, $item_id, $item_data = false ) {
			$this->parent = $parent;

			if ( false === $item_data ) {
				$item_data = $parent->bundle_data[ $item_id ];
			}

			$this->item_id    = $item_id;
			$this->product_id = $item_data['product_id'];
			$this->product_id = yith_wcpb()->compatibility->wpml->wpml_object_id( $this->product_id, 'product', true );

			$this->quantity = $item_data['bp_quantity'] ?? 1;

			$bundled_product = wc_get_product( $this->product_id );

			if ( $bundled_product ) {
				$this->product = $bundled_product;
			}
		}

		/**
		 * Return true if the related product exists.
		 *
		 * @return  bool
		 */
		public function exists() {
			return ! empty( $this->product );
		}

		/**
		 * Return the related product, or false if the product doesn't exist.
		 *
		 * @return WC_Product|false
		 */
		public function get_product() {
			return ! empty( $this->product ) ? $this->product : false;
		}

		/**
		 * Return the product id
		 *
		 * @return int
		 */
		public function get_product_id() {
			return $this->product_id;
		}

		/**
		 * Return the quantity.
		 *
		 * @return  int
		 */
		public function get_quantity() {
			return ! empty( $this->quantity ) ? $this->quantity : 0;
		}

		/**
		 * Retrieve the parent bundle product
		 *
		 * @return WC_Product_YITH_Bundle
		 * @since 1.4.0
		 */
		public function get_bundle() {
			return $this->parent;
		}
	}
}
