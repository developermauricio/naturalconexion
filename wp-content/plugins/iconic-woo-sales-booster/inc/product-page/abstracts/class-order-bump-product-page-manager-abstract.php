<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Order_Bump_Product_Page_Manager_Abstract.
 *
 * @class    Iconic_WSB_Order_Bump_Product_Page_Manager_Abstract
 * @version  1.0.0
 * @category Abstract Class
 * @author   Iconic
 */
abstract class Iconic_WSB_Order_Bump_Product_Page_Manager_Abstract {
	/**
	 * Meta key for stored order bump products
	 *
	 * @var string
	 */
	private $meta_key;

	/**
	 * Name order bump products field
	 *
	 * @var string
	 */
	private $field_name;

	/**
	 * label for order bump products field
	 *
	 * @var string
	 */
	private $label;

	/**
	 * description for order bump products field
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Path to template for select
	 *
	 * @var string
	 */
	protected $select_template = 'admin/order-bump/product/products-select.php';

	/**
	 * Singleton
	 *
	 * @return self
	 */
	final public static function get_instance() {
		static $instances = array();

		$calledClass = get_called_class();

		if ( ! isset( $instances [ $calledClass ] ) ) {
			$instances[ $calledClass ] = new $calledClass();
		}

		return $instances[ $calledClass ];
	}

	/**
	 * @return string
	 */
	public function get_field_name() {
		return $this->field_name;
	}

	/**
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Iconic_WSB_Order_Bump_Checkout_Manager_Abstract constructor.
	 *
	 * @param string $meta_key
	 * @param string $field_name
	 * @param string $label
	 * @param string $description
	 */
	protected function __construct( $meta_key, $field_name, $label, $description ) {
		$this->meta_key    = $meta_key;
		$this->field_name  = $field_name;
		$this->label       = $label;
		$this->description = $description;

		$this->common_hooks();
	}

	/**
	 * Register common hooks
	 */
	protected function common_hooks() {
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_bump_products' ), 10 );
	}

	/**
	 * @return string
	 */
	public function get_meta_key() {
		return $this->meta_key;
	}

	/**
	 * Render bump products multiselect.
	 */
	public function get_bump_products_tab_data() {
		global $iconic_wsb_class, $post;

		$bump_ids = $this->get_bump_products_ids( $post->ID );

		$bump_products = array_map( function ( $id ) {
			return wc_get_product( $id );
		}, $bump_ids );
		
		$bump_products = array_filter( $bump_products ); //remove falsy values
		
		return [
			'bump_products' => $bump_products,
			'product'       => $post,
			'name'          => $this->get_field_name(),
			'label'         => $this->get_label(),
			'description'   => $this->get_description(),
		];
	}

	/**
	 * Save product bump products. Triggers on save product
	 *
	 * @param int $product_id
	 */
	public function save_product_bump_products( $product_id ) {
		$bump_products = isset( $_POST[ $this->get_field_name() ] ) ? array_map( 'intval', (array) wp_unslash( $_POST[ $this->get_field_name() ] ) ) : array();
		$this->update_bump_products( $product_id, $bump_products );
	}

	/**
	 * Update product bump products
	 *
	 * @param int   $product_id
	 * @param array $bump_products
	 */
	public function update_bump_products( $product_id, $bump_products ) {
		update_post_meta( $product_id, $this->get_meta_key(), $bump_products );
	}

	/**
	 * @param int $product_id
	 *
	 * @return array
	 */
	public function get_bump_products_ids( $product_id ) {
		$bump_ids = (array) get_post_meta( $product_id, $this->get_meta_key(), true );

		return apply_filters( 'iconic_wsb_product_page_order_bump_ids', array_filter( $bump_ids ), $this, $product_id );
	}

	/**
	 * Remove from list products which already in cart
	 *
	 * @param WC_Product[] $bump_products
	 *
	 * @return WC_Product[]
	 */
	public function remove_already_in_cart_products( $bump_products ) {
		$remove_products = apply_filters( 'iconic_wsb_remove_products', true );

		if ( ! $remove_products ) {
			return $bump_products;
		}

		return array_filter( $bump_products, function ( $bump_product ) {
			if ( $bump_product instanceof WC_Product ) {
				return ! Iconic_WSB_Cart::is_product_in_cart( $bump_product->get_id() );
			}

			return array();
		} );
	}

	/**
	 * Check the product is actually purchasable and should be displayed.
	 *
	 * @param WC_Product[] $bump_products
	 *
	 * @return WC_Product[]
	 */
	public function remove_unpurchasable_products( $bump_products ) {
		return array_filter( $bump_products, static function ( $bump_product ) {
			if ( $bump_product instanceof WC_Product ) {
				return ( $bump_product->is_purchasable() && $bump_product->is_visible() );
			}

			return array();
		} );
	}

	/**
	 * Check if the product is valid to be bump
	 *
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function isValidBump( $product ) {
		if ( ! ( $product instanceof WC_Product ) ) {
			return false;
		}

		$settings = $this->get_settings();
		$visible  = ! empty( $settings['show_hidden_products'] ) ? true : $product->is_visible();

		return $product->is_in_stock() && $product->is_purchasable() && $visible;
	}

	private function __clone() {
	}
}