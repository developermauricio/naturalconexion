<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Order_Bump.
 *
 * @class    Iconic_WSB_Order_Bump
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump {
	const MENU_SLUG = 'iconic_wsb_order_bumps';

	/**
	 * Path to template for tab
	 *
	 * @var string
	 */
	protected static $tab_template = 'admin/order-bump/product/product-data-panel.php';

	/**
	 * Run
	 */
	public static function run() {
		self::require_managers();
		self::register_menu_item();
		self::add_product_tab();
		self::init_managers();
	}

	/**
	 * Init all bump managers
	 */
	protected static function init_managers() {
		/* Product Page Bump */
		Iconic_WSB_Order_Bump_Product_Page_Manager::get_instance();
		Iconic_WSB_Order_Bump_Product_Page_Modal_Manager::get_instance();

		/* Checkout Page Bumps */
		Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance();
		Iconic_WSB_Order_Bump_After_Checkout_Manager::get_instance();
	}

	/**
	 * Load all bump managers
	 */
	protected static function require_managers() {
		// Checkout
		require_once 'checkout/class-order-bump-after-checkout-manager.php';
		require_once 'checkout/class-order-bump-at-checkout-manager.php';

		// Product Page
		require_once 'product-page/class-order-bump-product-page-manager.php';
		require_once 'product-page/class-order-bump-product-page-modal-manager.php';
	}

	/**
	 * Register common menu item for checkout order bumps
	 */
	protected static function register_menu_item() {
		add_action( 'admin_menu', function () {
			add_menu_page( __( 'Sales Booster', 'iconic-wsb' ), __( 'Sales Booster', 'iconic-wsb' ), 'manage_options',
				self::MENU_SLUG, null, 'dashicons-chart-area', 50 );
		} );
	}

	/**
	 * Add tabs to product edit page.
	 */
	protected static function add_product_tab() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tabs' ), 10, 1 );
	}

	/**
	 * Add "Sales Booster" tab.
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public static function product_data_tabs( $tabs ) {
		$tabs['iconic-wsb'] = array(
			'label'    => __( 'Sales Booster', 'iconic-wsb' ),
			'target'   => 'iconic_wsb',
			'class'    => array( 'hide_if_grouped', 'hide_if_external' ),
			'priority' => 45,
		);

		return $tabs;
	}

	/**
	 * Add data panel for sales booster.
	 */
	public static function product_data_panels() {
		global $iconic_wsb_class;

		$iconic_wsb_class->template->include_template( self::$tab_template );
	}
}