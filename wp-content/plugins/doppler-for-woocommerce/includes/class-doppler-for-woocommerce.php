<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.fromdoppler.com/
 * @since      1.0.0
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 * @author     Doppler LLC <info@fromdoppler.com>
 */
class Doppler_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Doppler_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected $doppler_service;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		require_once( DOPPLER_PLUGINS_PATH . 'doppler-form/includes/DopplerAPIClient/DopplerService.php' );
		$this->doppler_service = new Doppler_Service();

		if ( defined( 'DOPPLER_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = DOPPLER_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		
		$this->plugin_name = 'doppler-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->schedule_cron();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Doppler_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Doppler_For_Woocommerce_i18n. Defines internationalization functionality.
	 * - Doppler_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Doppler_For_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-doppler-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-doppler-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-doppler-for-woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-doppler-for-woocommerce-public.php';

		/**
		 * The class responsible of managing the abandoned cart.
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-doppler-for-woocommerce-ac.php';

		/**
		 * The class responsible of tracking the product views.
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-doppler-for-woocommerce-visited-products.php';
		
		/**
		 * The class responsible of defining the custom API endpoint to get abandoned carts data.
		 */
		//require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-doppler-for-woocommerce-rest-controller.php';
		
		$this->loader = new Doppler_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Doppler_For_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Doppler_For_Woocommerce_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 * 
	 * These admin functions are located in admin/class-doppler-for-woocommerce-admin.php
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Doppler_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version(), $this->doppler_service );
		
		$this->loader->add_action( 'dplrwoo_synch_cron', $plugin_admin, 'dplrwoo_synch_cron_func' );
		$this->loader->add_action( 'dplrwoo_cron_job', $plugin_admin, 'dplrwoo_delete_carts' );
		$this->loader->add_action( 'dplrwoo_cron_clean_views', $plugin_admin, 'dplrwoo_delete_product_views' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'dplrwoo_check_parent' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'dplr_add_extension_submenu', $plugin_admin, 'dplrwoo_init_menu' );
		$this->loader->add_action( 'wp_ajax_dplrwoo_ajax_save_list', $plugin_admin, 'dplrwoo_save_list' );
		$this->loader->add_action( 'wp_ajax_dplrwoo_ajax_synch', $plugin_admin, 'dplrwoo_ajax_synch' );
		$this->loader->add_action( 'wp_ajax_dplrwoo_ajax_create_lists' , $plugin_admin, 'dplrwoo_create_default_lists' );
		$this->loader->add_action( 'wp_ajax_dplrwoo_ajax_clear_lists', $plugin_admin, 'dplrwoo_clear_lists');
		$this->loader->add_action( 'wp_ajax_dplrwoo_ajax_verify_keys', $plugin_admin, 'dplrwoo_verify_keys');
		$this->loader->add_action( 'woocommerce_created_customer', $plugin_admin, 'dplrwoo_created_customer', 10, 3);
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin, 'dplrwoo_customer_checkout_success' );
		$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'dplrwoo_order_status_changed', 10, 4 );
		$this->loader->add_action( 'user_register', $plugin_admin, 'dprwoo_after_register');
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'show_admin_notice' );
		$this->loader->add_action( 'admin_footer-plugins.php', $plugin_admin, 'display_deactivation_confirm_html' );

		//Custom API endpoint
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'dplrwoo_abandoned_endpoint' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		global $wpdb;
	
		$plugin_public = new Doppler_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );
		$doppler_abandoned_cart = new Doppler_For_Woocommerce_Abandoned_Cart( $wpdb->prefix . DOPPLER_ABANDONED_CART_TABLE );
		$doppler_visited_products = new Doppler_For_WooCommerce_Visited_Products( $wpdb->prefix . DOPPLER_VISITED_PRODUCTS_TABLE );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//Abandoned cart public hooks.
		//$this->loader->add_action( 'woocommerce_after_checkout_form', $plugin_public, 'add_additional_scripts_on_checkout' ); //Adds additional functionality only to Checkout page
		$this->loader->add_action( 'wp_ajax_nopriv_save_data', $doppler_abandoned_cart, 'save_frontend_user_data' ); //Handles data saving using Ajax after any changes made by the user on the E-mail or Phone field in Checkout form
		$this->loader->add_action( 'wp_ajax_save_data', $doppler_abandoned_cart, 'save_frontend_user_data' ); //Handles data saving using Ajax after any changes made by the user on the E-mail field for Logged in users
		
		//Save cart session for logged in user.
		$this->loader->add_action( 'woocommerce_add_to_cart', $doppler_abandoned_cart, 'save_cart_session', 200 ); //Handles data saving if an item is added to shopping cart, 200 = priority set to run the function last after all other functions are finished
		$this->loader->add_action( 'woocommerce_cart_actions', $doppler_abandoned_cart, 'save_cart_session', 200 ); //Handles data updating if a cart is updated. 200 = priority set to run the function last after all other functions are finished
		$this->loader->add_action( 'woocommerce_cart_item_removed', $doppler_abandoned_cart, 'save_cart_session', 200 ); //Handles data updating if an item is removed from cart. 200 = priority set to run the function last after all other functions are finished
		
		//Save cart session for unknown user.
		$this->loader->add_action( 'woocommerce_add_to_cart', $doppler_abandoned_cart, 'update_cart_session', 210 );
		$this->loader->add_action( 'woocommerce_cart_actions', $doppler_abandoned_cart, 'update_cart_session', 210 );
		$this->loader->add_action( 'woocommerce_cart_item_removed', $doppler_abandoned_cart, 'update_cart_session', 210 );
		
		//Delete cart session row
		$this->loader->add_action( 'woocommerce_new_order', $doppler_abandoned_cart, 'delete_cart_session', 30 ); //Deletes row after order is created from checkout.
		$this->loader->add_action( 'woocommerce_thankyou', $doppler_abandoned_cart, 'delete_cart_session', 30 ); //Retry if previous hook is not triggered.
		//$this->loader->add_filter( 'woocommerce_checkout_fields', $doppler_abandoned_cart, 'dplr_restore_input_data', 1); //Restoring previous user input in Checkout form
		
		//Save visited product.
		$this->loader->add_action( 'woocommerce_before_single_product', $doppler_visited_products, 'save_visited_product');
		
		//Restore cart from URL
		$this->loader->add_action( 'template_redirect', $doppler_abandoned_cart, 'restore_cart' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Doppler_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function schedule_cron() {
		
		add_filter( 'cron_schedules', function() {
			// Adds once every minute to the existing schedules.
			$schedules['15min'] = array(
				'interval' => 15*60,
				'display' => __( 'Doppler Once Every 15 Minutes' )
			);
			$schedules['2min'] = array(
				'interval' => 2*60,
				'display' => __( 'Doppler Once Every 2 Minutes' )
			);
			$schedules['5min'] = array(
				'interval' => 5*60,
				'display' => __( 'Doppler Once Every 5 Minutes' )
			);
			return $schedules;
		} );
		
		//houry, daily, twicedaily
		add_action('wp', function() {
			if( !wp_next_scheduled( 'dplrwoo_cron_job' ) ) {  
				wp_schedule_event( time(), 'daily', 'dplrwoo_cron_job' );  
			}
			if( !wp_next_scheduled( 'dplrwoo_cron_clean_views' ) ) {  
				wp_schedule_event( time(), 'daily', 'dplrwoo_cron_clean_views' );  
			}
			if( !wp_next_scheduled( 'dplrwoo_synch_cron' ) ) {  
				wp_schedule_event( time(), '2min', 'dplrwoo_synch_cron' );  
			}
		});
	}

}
