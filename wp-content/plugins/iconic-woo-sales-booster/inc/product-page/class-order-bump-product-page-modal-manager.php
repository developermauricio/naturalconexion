<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'abstracts/class-order-bump-product-page-manager-abstract.php';

/**
 * Iconic_WSB_Order_Bump_Product_Page_Modal_Manager.
 *
 * @class    Iconic_WSB_Order_Bump_Product_Page_Modal_Manager
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_Product_Page_Modal_Manager extends Iconic_WSB_Order_Bump_Product_Page_Manager_Abstract {
	/**
	 * Iconic_WSB_Order_Bump_Product_Page_Modal_Manager constructor.
	 */
	public function __construct() {
		parent::__construct(
			'_iconic_wsb_product_page_bump_modal_ids',
			'iconic_wsb_product_page_bump_modal_ids',
			__( 'After Add to Cart', 'iconic-wsb' ),
			__( 'Display these suggested products in a modal popup after adding the main product to the cart.', 'iconic-wsb' )
		);

		$this->hooks();

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'init_frontend' ) );
		}
	}

	/**
	 *  Register hooks
	 */
	private function hooks() {
		add_filter( 'wpsf_register_settings_iconic-wsb', array( $this, 'add_settings_section' ) );
	}

	/**
	 * Add service settings section
	 *
	 * @param array $settings
	 *
	 * @return mixed
	 */
	public function add_settings_section( $settings ) {
		$settings['sections']['product-page-modal'] = array(
			'tab_id'              => 'order_bump',
			'section_id'          => 'product_page_modal',
			'section_title'       => __( 'After Add to Cart Modal', 'iconic-wsb' ),
			'section_description' => __( 'These are cross-sells which appear in a modal after adding a product to the cart.', 'iconic-wsb' ),
			'section_order'       => 0,
			'fields'              => array(
				array(
					'id'      => 'header_color',
					'title'   => __( 'Header Bar Color', 'iconic-wsb' ),
					'type'    => 'color',
					'default' => '#24BDAE',
				),
				array(
					'id'      => 'title',
					'title'   => __( 'Cross Sells Title', 'iconic-wsb' ),
					'desc'    => __( 'Leave blank to disable the title.', 'iconic-wsb' ),
					'type'    => 'text',
					'default' => __( 'Customers Also Bought', 'iconic-wsb' ),
				),
			),
		);

		return $settings;
	}

	/**
	 * Init frontend hooks
	 */
	public function init_frontend() {
		add_action( 'woocommerce_after_single_product', array( $this, 'render_bump_modal' ), 99999 );

		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'pre_cart_redirect' ), 99 );
		add_action( 'woocommerce_ajax_added_to_cart', array( $this, 'ajax_added_to_cart' ), 99 );
		add_filter( 'woocommerce_after_cart', array( $this, 'after_cart' ), 99 );
	}

	/**
	 * If the product was added to the cart via ajax, record it.
	 *
	 * @param int $product_id
	 */
	public function ajax_added_to_cart( $product_id ) {
		set_transient( 'iconic-wsb-just-added-to-cart', $product_id, 60 );
	}

	/**
	 * Record which products were in the cart prior to redirection.
	 *
	 * @param string $url Where we would be redirecting to.
	 *
	 * @return string Return url.
	 */
	public function pre_cart_redirect( $url ) {
		if ( isset( $_REQUEST['add-to-cart'] ) && is_numeric( $_REQUEST['add-to-cart'] ) ) {
			set_transient( 'iconic-wsb-just-added-to-cart', $_REQUEST['add-to-cart'], 60 );
		}

		return $url;
	}

	/**
	 * Called for Frequently Bought Together. Record which products were in the cart prior to redirection.
	 *
	 * @param string $url Where we would be redirecting to.
	 *
	 * @return string Return url.
	 */
	public function fbt_pre_cart_redirect( $url ) {
		if ( isset( $_REQUEST['iconic-wsb-fbt-this-product'] ) && is_numeric( $_REQUEST['iconic-wsb-fbt-this-product'] ) ) {
			set_transient( 'iconic-wsb-just-added-to-cart', $_REQUEST['iconic-wsb-fbt-this-product'], 60 );
		}

		return $url;
	}

	/**
	 * After the cart has rendered.
	 */
	public function after_cart() {
		// see if we have a transient set.
		$transient = get_transient( 'iconic-wsb-just-added-to-cart' );
		if ( false === $transient || ! is_numeric( $transient ) ) {
			return;
		}

		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $transient ) );

		$product = wc_get_product( $product_id );

		// see if it is a valid product
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		// render the modal.
		$this->render_bump_modal( $product );
	}

	/**
	 * Render content of bump modal if related products exist
	 *
	 * @param WC_Product|null $added_to_cart_product The product just added to the cart
	 */
	public function render_bump_modal( $added_to_cart_product = null ) {
		// if we have not been passed a product, see if one has just been added to the cart.
		if ( empty( $added_to_cart_product ) ) {
			$added_to_cart_product = $this->get_added_to_cart_product();

			if ( ! is_product() ) {
				return;
			}
		}

		// not a valid product
		if ( ! ( $added_to_cart_product instanceof WC_Product ) ) {
			return;
		}

		// remove the transient (if it is set) as we don't need it anymore.
		delete_transient( 'iconic-wsb-just-added-to-cart' );

		$parent_product_id = $added_to_cart_product->is_type( 'variation' ) ? $added_to_cart_product->get_parent_id() : $added_to_cart_product->get_id();
		
		// no bump products
		if ( empty( $this->get_bump_products_ids( $parent_product_id ) ) ) {
			return;
		}

		global $iconic_wsb_class;
			
		// ids to WC_Product objects
		$offers = array_map( 'wc_get_product', $this->get_bump_products_ids( $parent_product_id ) );

		// Validate bumps
		$offers = array_filter( $offers, array( $this, 'isValidBump' ) );

		// Leave maximum 3
		$offers = array_slice( $offers, 0, 3 );

		if ( ! empty( $offers ) ) {
			if ( $this->need_variable_scripts( $offers ) ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}

			$iconic_wsb_class->template->include_template( 'frontend/order-bump/product/bump-modal.php', array(
				'settings' => $this->get_settings(),
				'product'  => $added_to_cart_product,
				'offers'   => $offers,
			) );
		}
	}

	/**
	 * Check for variable product in product list
	 *
	 * @param WC_Product[] $products
	 *
	 * @return bool
	 */
	private function need_variable_scripts( $products ) {
		$products = array_filter( $products, function ( WC_Product $product ) {
			return $product->is_type( 'variable' );
		} );

		return count( $products ) > 0;
	}

	/**
	 * Get added to cart product
	 *
	 * @return false|WC_Product
	 */
	public function get_added_to_cart_product() {
		$add_to_cart  = absint( filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT ) );
		$this_product = absint( filter_input( INPUT_POST, 'iconic-wsb-fbt-this-product', FILTER_SANITIZE_NUMBER_INT ) );

		if ( ! $add_to_cart && ! $this_product ) {
			return false;
		}

		$product_id   = false;
		$variation_id = absint( filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( $variation_id ) {
			$product_id = $variation_id;
		} elseif ( $add_to_cart ) {
			$product_id = $add_to_cart;
		} elseif ( $this_product ) {
			$product_id = $this_product;
		}

		$product = wc_get_product( $product_id );

		if ( $product instanceof WC_Product ) {
			return $product;
		}

		return false;
	}

	/**
	 * Get service settings
	 *
	 * @return array
	 */
	public function get_settings() {
		global $iconic_wsb_class;

		$prefix = 'order_bump_product_page_modal_';

		$defaults = [
			'header_color' => '#23BDAE',
			'title'        => __( 'Customers Also Bought', 'iconic-wsb' ),
		];

		$settings = [];

		foreach ( $defaults as $key => $default ) {
			$settings[ $key ] = array_key_exists( $prefix . $key, $iconic_wsb_class->settings ) ?
				$iconic_wsb_class->settings[ $prefix . $key ] : $default;
		}

		return apply_filters( 'iconic_wsb_product_page_modal_settings', $settings );
	}
}