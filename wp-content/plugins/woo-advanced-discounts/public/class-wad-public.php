<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.orionorigin.com/
 * @since      0.1
 *
 * @package    Wad
 * @subpackage Wad/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wad
 * @subpackage Wad/public
 * @author     ORION <support@orionorigin.com>
 */
class Wad_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name ) {

		$this->plugin_name = $plugin_name;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wad_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wad_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wad-public.css', array(), WAD_VERSION, 'all' );
		wp_enqueue_style( 'o-tooltip', WAD_URL . 'public/css/tooltip.min.css', array(), WAD_VERSION, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wad_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wad_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wad-public.js', array( 'jquery' ), WAD_VERSION, false );
		wp_enqueue_script( 'o-tooltip', WAD_URL . 'public/js/tooltip.min.js', array( 'jquery' ), WAD_VERSION, false );
	}

	function init_globals() {
		global $wad_discounts;
		global $wad_settings;
		global $wad_cart_discounts;
		global $wad_user_role;
		global $wad_user_groups;
		global $wad_last_products_fetch;
		global $wad_products_lists;
		global $wad_ignore_product_prices_calculations;
		global $wad_reviewed_products_by_customer;
		global $wad_cart_total_without_taxes;
		$wad_cart_discounts                     = 0;
		$wad_reviewed_products_by_customer      = false;
		$wad_ignore_product_prices_calculations = false;
		$wad_user_groups                        = false;
		$wad_last_products_fetch                = false;
		$wad_products_lists                     = array();
		$wad_settings                           = get_option( 'wad-options' );
		$wad_user_role                          = wad_get_user_role();
		if ( class_exists( 'WooCommerce' ) ) {
			if ( function_exists( 'WC' ) && WC()->cart ) {
				$cart_count = WC()->cart->get_cart_contents_count();
				if ( $cart_count > 0 ) {
					$wad_cart_total_without_taxes = wad_get_cart_total( false );
				}
			}

			$all_discounts = wad_get_active_discounts( true );
			foreach ( $all_discounts as $discount_type => $discounts ) {
				$wad_discounts[ $discount_type ] = array();
				foreach ( $discounts as $discount_id ) {
					$wad_discounts[ $discount_type ][ $discount_id ] = new WAD_Discount( $discount_id );
				}
			}
			define( 'WAD_INITIALIZED', true );
		}
	}

	public static function register_shortcodes() {
		if ( ! is_admin() ) {
			add_shortcode( 'wad_product_pricing_table', array( 'WAD_Discount', 'get_quantity_pricing_tables' ) );
		}		
	}


	/**
	 * Initialize the plugin sessions
	 */
	function init_sessions() {
		if ( ! isset( $_SESSION['active_discounts'] ) ) {
			$_SESSION['active_discounts'] = array();
		}
	}
}
