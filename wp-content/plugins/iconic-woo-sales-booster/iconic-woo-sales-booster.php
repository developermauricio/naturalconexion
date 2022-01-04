<?php
/**
 * Plugin Name: Iconic Sales Booster for WooCommerce
 * Plugin URI: https://iconicwp.com/products/sales-booster-for-woocommerce/
 * Description: Increase your average order value with strategic cross-sells.
 * Version: 1.1.6
 * Author: Iconic
 * Author URI: https://iconicwp.com
 * Text Domain: iconic-wsb
 * WC requires at least: 3.6.0
 * WC tested up to: 5.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Iconic_Woo_Sales_Booster {
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '1.1.6';

	/**
	 * Variable to hold default/saved settings.
	 *
	 * @var array|null
	 */
	public $settings = null;

	/**
	 * @var Iconic_WSB_Template
	 */
	public $template;

	/**
	 * Construct the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_text_domain' ) );

		$this->define_constants();
		$this->load_classes();
	}

	/**
	 * Load text domain.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'iconic-wsb', false, ICONIC_WSB_DIRNAME . '/languages/' );
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_WSB_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_WSB_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_WSB_INC_PATH', ICONIC_WSB_PATH . 'inc/' );
		$this->define( 'ICONIC_WSB_VENDOR_PATH', ICONIC_WSB_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_WSB_TPL_PATH', ICONIC_WSB_PATH . 'templates/' );
		$this->define( 'ICONIC_WSB_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'ICONIC_WSB_DIRNAME', dirname( ICONIC_WSB_BASENAME ) );
		$this->define( 'ICONIC_WSB_VERSION', self::$version );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name
	 * @param string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load classes
	 */
	private function load_classes() {
		$this->init_autoloader();

		if ( ! Iconic_WSB_Core_Helpers::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return;
		}

		$this->init_settings();
			/*$this->init_licence();

		if ( ! Iconic_WSB_Core_Licence::has_valid_licence() ) {
			return;
		}*/

		$this->template = new Iconic_WSB_Template();

		$this->init_services();
	}

	/**
	 * Init plugin licence
	 */
	private function init_licence() {
		Iconic_WSB_Core_Licence::run( array(
			'basename' => ICONIC_WSB_BASENAME,
			'urls'     => array(
				'product'  => 'https://iconicwp.com/products/', // Product page on iconicwp.com.
				'settings' => admin_url( 'admin.php?page=iconic-wsb-settings' ), // Admin settings page.
				'account'  => admin_url( 'admin.php?page=iconic-wsb-settings-account' ), // Admin account page.
			),
			'paths'    => array(
				'inc'    => ICONIC_WSB_INC_PATH,
				'plugin' => ICONIC_WSB_PATH,
			),
			'freemius' => array(
				'id'           => '3212',
				'slug'         => 'iconic-woo-sales-booster-lite',
				'premium_slug' => 'iconic-woo-sales-booster',
				'public_key'   => 'pk_3ff1f2e5cb38f67915e2b154565d6',
				'menu'         => array(
					'slug'    => 'iconic-wsb-settings',
					'parent'  => array(
						'slug' => 'iconic_wsb_order_bumps',
					),
					'pricing' => false,
				),
			),
		) );
	}

	/**
	 * Init settings framework
	 */
	private function init_settings() {
		Iconic_WSB_Core_Settings::run( array(
			'parent_slug'   => 'iconic_wsb_order_bumps',
			'vendor_path'   => ICONIC_WSB_VENDOR_PATH,
			'title'         => __( 'Sales Booster for WooCommerce', 'iconic-wsb' ), // Plugin title.
			'version'       => self::$version,
			'menu_title'    => __( 'Settings', 'iconic-wsb' ), // Menu title. Defaults to under the `WooCommerce` menu.
			'settings_path' => ICONIC_WSB_INC_PATH . 'admin/settings.php',
			'option_group'  => 'iconic-wsb',
			'docs'          => array(
				'collection'      => '/collection/229-iconic-sales-booster-for-woocommerce', // Docs collection URL path.
				'troubleshooting' => '/category/234-troubleshooting', // Docs troubleshooting URL path.
				'getting-started' => '/category/232-getting-started', // Docs getting started URL path.
			),
			'cross_sells'   => array(
				'iconic-woo-show-single-variations',
				'iconic-woothumbs',
			),
		) );
	}

	/**
	 *  Init plugin autoloader
	 */
	private function init_autoloader() {
		require_once( ICONIC_WSB_INC_PATH . 'class-core-autoloader.php' );

		Iconic_WSB_Core_Autoloader::run( array(
			'prefix'   => 'Iconic_WSB_',
			'inc_path' => ICONIC_WSB_INC_PATH,
		) );
	}

	/**
	 * Set settings.
	 */
	public function set_settings() {
		$this->settings = Iconic_WSB_Core_Settings::$settings;
	}

	/**
	 * Init plugin services
	 */
	private function init_services() {
		Iconic_WSB_Notifier::run();
		Iconic_WSB_Settings::run();
		Iconic_WSB_Assets::run();
		Iconic_WSB_Cart::run();
		Iconic_WSB_Ajax::run();
		Iconic_WSB_Order_Bump::run();
		Iconic_WSB_Admin_Product_Tab::run();
		Iconic_WSB_Compat_Woo_Attributes_Swatches::run();
	}

	/**
	 * Activation hook.
	 */
	public static function activation_hook() {
		update_option( 'iconic_wsb_activated', true );
	}
}

$iconic_wsb_class = new Iconic_Woo_Sales_Booster();

register_activation_hook( __FILE__, array( 'Iconic_Woo_Sales_Booster', 'activation_hook' ) );