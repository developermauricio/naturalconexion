<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
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
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Smart_Marketing_Addon_Sms_Order_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.3.4';
		}
		$this->plugin_name = 'smart-marketing-addon-sms-order';

		$this->load_dependencies();
		$this->set_locale();

		$haslists = get_option('egoi_has_list');
		if ($haslists) {
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smart_Marketing_Addon_Sms_Order_Loader. Orchestrates the hooks of the plugin.
	 * - Smart_Marketing_Addon_Sms_Order_i18n. Defines internationalization functionality.
	 * - Smart_Marketing_Addon_Sms_Order_Admin. Defines all hooks for the admin area.
	 * - Smart_Marketing_Addon_Sms_Order_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smart-marketing-addon-sms-order-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smart-marketing-addon-sms-order-i18n.php';

		/**
		 * The class responsible for defining all helper methods
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smart-marketing-addon-sms-order-helper.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-smart-marketing-addon-sms-order-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-smart-marketing-addon-sms-order-public.php';

		$this->loader = new Smart_Marketing_Addon_Sms_Order_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smart_Marketing_Addon_Sms_Order_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Smart_Marketing_Addon_Sms_Order_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Smart_Marketing_Addon_Sms_Order_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'smsonw_add_options_page', 11 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'smsonw_enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'smsonw_enqueue_scripts' );

		// CRON and payment reminder
		$this->loader->add_action('egoi_sms_order_event', $plugin_admin, 'smsonw_sms_order_reminder');
		$this->loader->add_action('egoi_sms_order_event', $plugin_admin, 'smsonw_email_order_reminder');
		
		// Box send SMS in admin order page
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'smsonw_order_add_sms_meta_box');
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'smsonw_order_add_track_number_box');
		$this->loader->add_action('wp_ajax_smsonw_order_action_sms_meta_box', $plugin_admin, 'smsonw_order_action_sms_meta_box');
        $this->loader->add_action('wp_ajax_smsonw_order_add_tracking_number', $plugin_admin, 'smsonw_order_add_tracking_number');
        $this->loader->add_action('wp_ajax_smsonw_order_delete_tracking_number', $plugin_admin, 'smsonw_order_delete_tracking_number');
        $this->loader->add_action('wp_ajax_smsonw_add_custom_carrier', $plugin_admin, 'smsonw_add_custom_carrier');
        $this->loader->add_action('wp_ajax_smsonw_remove_custom_carrier', $plugin_admin, 'smsonw_remove_custom_carrier');


        // Check type of payment and send SMS
		$this->loader->add_action('woocommerce_order_status_on-hold', $plugin_admin, 'smsonw_order_send_sms_payment_data');

		// When change order status, send SMS
		$this->loader->add_action('woocommerce_order_status_changed', $plugin_admin, 'smsonw_order_send_sms_new_status');

        // PagSeguro integration
        $this->loader->add_action('rest_api_init', $plugin_admin, 'smsonw_billet_endpoint');

        $this->loader->add_action( 'woocommerce_before_product_object_save', $plugin_admin, 'update_the_product_price', 10, 1 );

        //abandoned cart reminder
        $this->loader->add_action('egoi_sms_order_event', $plugin_admin, 'smsonw_sms_abandoned_cart_process');

    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Smart_Marketing_Addon_Sms_Order_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'smsonw_enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'smsonw_enqueue_scripts' );

		// Checkbox "I want to be notified by SMS" in order checkout form
		$this->loader->add_action('woocommerce_after_checkout_billing_form', $plugin_public, 'smsonw_notification_checkout_field');
		$this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_public, 'smsonw_notification_checkout_field_update_order_meta');

		// Add follow price button to product page
		$follow_price = json_decode(get_option('egoi_sms_follow_price'), true);
		if( isset($follow_price["follow_price_enable"]) && $follow_price["follow_price_enable"] == "on" ){
			if(isset($follow_price['follow_price_position'])){
				$this->loader->add_action($follow_price['follow_price_position'], $plugin_public, 'smsonw_follow_price_add_button');
			}
		}

		//abandoned_cart
		$this->loader->add_action('wp_head', $plugin_public, 'smsonw_notification_abandoned_cart_trigger');
        $this->loader->add_action('woocommerce_new_order', $plugin_public, 'smsonw_notification_abandoned_cart_clear');

        $this->loader->add_action('wp_ajax_egoi_cellphone_actions', $plugin_public, 'egoi_cellphone_actions');
        $this->loader->add_action('wp_ajax_nopriv_egoi_cellphone_actions', $plugin_public, 'egoi_cellphone_actions');

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
	 * @return    Smart_Marketing_Addon_Sms_Order_Loader    Orchestrates the hooks of the plugin.
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

}
