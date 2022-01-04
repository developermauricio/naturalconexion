<?php
/*
 * Plugin Name: WooCommerce Super Shipping
 * Plugin URI: http://woodemia.com
 * Description: WooCommerce Super Shipping is a WooCommerce's add-on that allows you to configure advanced shipping costs. You can to set different shipping costs for multiple zones, based on the weight, volume, number of items or price of the products.
 * Version: 1.3.1
 * Author: Woodemia
 * Author URI: http://woodemia.com
 * Text Domain: wc-ss
 * Domain Path: /languages
 * Requires at least: 4.4
 * Tested up to: 5.1.1
 * WC requires at least: 2.6
 * WC tested up to: 3.6.1
 * License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Define constant for the current version
 */
if ( ! defined( 'WC_SS_VERSION' ) ) {
	define( 'WC_SS_VERSION', '1.3.1' );
}

/**
 * Firing the setup wizard to the (>=3.x) WooCommerce's native shipping management system
 */
register_activation_hook( __FILE__, 'wss_maybe_enable_setup_wizard' );
function wss_maybe_enable_setup_wizard(){

	$version = get_option( 'woocommerce_super_shipping_version' );
	// Activate the migration system if is needed
	if ( empty( $version ) ) {

		set_transient( 'wss_activation_redirect', 'install', 5 * MINUTE_IN_SECONDS );
	} elseif ( $version && version_compare( $version, '1.2.7', '=' ) ) {

		set_transient( 'wss_activation_redirect', 'migration', 5 * MINUTE_IN_SECONDS );
	} elseif ( $version && version_compare( $version, '1.2.7', '<' ) ) {

		set_transient( 'wss_activation_redirect', 'not_supported', 5 * MINUTE_IN_SECONDS );
	}

	// Update the version of the plugin
	update_version();
}

/**
 * Update WCSS version.
 */
function update_version() {
	delete_option( 'woocommerce_super_shipping_version' );
	add_option( 'woocommerce_super_shipping_version', WC_SS_VERSION );
}

/**
 * Load WSS_Setup_Wizard class
 */
add_action( 'init', 'include_setup_wizard_class' );
function include_setup_wizard_class(){

	if ( ! class_exists( 'WSS_Setup_Wizard' ) ) {
		include_once WC_SS_DIR .'/includes/class-wss-setup-wizard.php';
	}
}

/**
 * Redirect to setup wizard page
 */
add_action( 'admin_init', array( 'WSS_Setup_Wizard', 'admin_redirects' ) );

add_action( 'plugins_loaded', 'wocommerce_super_shipping_init', 0 );
function wocommerce_super_shipping_init() {

	/**
	 * Define constant for the plugin absolute dir path
	 */
	if ( ! defined( 'WC_SS_DIR' ) ) {
		define( 'WC_SS_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	/**
	 * Define constant for the plugin file name
	 */
	if ( ! defined( 'WC_SS_FILE_NAME' ) ) {
		define( 'WC_SS_FILE_NAME', basename( __FILE__ ) );
	}

	/**
	 * Load is_plugin_active_for_network function necessary to check active plugin at WP MU installation
	 **/    	
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	/**
	 * Load get_editable_roles function to get the users roles availables
	 */
	if ( ! function_exists( 'get_editable_roles' ) ) { 
		require_once( ABSPATH . '/wp-admin/includes/user.php' ); 
	}
		
	/**
	 * Check if WooCommerce is active
	 **/
	if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {

		class WooCommerce_Super_Shipping extends WC_Shipping_Method{

			/**
			 * Some required plugin information
			 */
			public static $version = WC_SS_VERSION;

			/**
			 * @var Array tables rates shippings
			 */
			public $tables_rates = array();

			/**
			 * @var WPML plugin status
			 */
			public $wpml_is_active = false;

			/**
			 * @var WC Multilingual plugin status
			 */
			public $wc_multilingual_is_active = false;

			/**
			 * @var WooCommerce_Super_Shipping The single instance of the class
			 */
			protected static $_instance = null;
		
			/**
			 * Main WooCommerce_Super_Shipping Instance.
			 *
			 * Ensures only one instance of WooCommerce_Super_Shipping is loaded or can be loaded.
			 *
			 * @static
			 * @return WooCommerce_Super_Shipping Main instance
			 */
			public static function instance() {

				if ( is_null( self::$_instance ) ){

					self::$_instance = new self();
				}

				return self::$_instance;
			}
			
			/*
			 *	Required __construct() function that initalizes the WC_Super_Shipping
			*/
			public function __construct( $instance_id = 0 ) {

				// Register plugin text domain for translations files
				load_plugin_textdomain( 'wc-ss', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

				$this->id = 'super_shipping';
				$this->instance_id = absint( $instance_id );
				$this->method_title = 'Super Shipping';
				$this->method_description = __( 'With this shipping method you\'ll can to set up multiples shipping rates based on products weight, number of items, price or even volume.', 'wc-ss' );
				$this->supports = array(
					'shipping-zones',
					'instance-settings',
				);
				$this->zones_settings = $this->id . 'zones_settings';
	            $this->rates_settings = $this->id . 'rates_settings';

				// Check if WPML is actived
				if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) || is_plugin_active_for_network( 'sitepress-multilingual-cms/sitepress.php' ) ){

					$this->wpml_is_active = true;
				}

				// Check if WC Multilingual is actived
				if ( in_array( 'woocommerce-multilingual/wpml-woocommerce.php', get_option( 'active_plugins' ) ) || is_plugin_active_for_network( 'woocommerce-multilingual/wpml-woocommerce.php' ) ){

					$this->wc_multilingual_is_active = true;
				}

				$this->init();

				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_and_scripts' ) ); 
				
				// Add support for WPML
				add_filter( 'woocommerce_package_rates', array( $this, 'prepare_shipping_methods_string_translations' ), 10, 2 );
				add_filter( 'woocommerce_package_rates', array( $this, 'show_only_free_shipping_method_when_is_active' ), 10, 2 );
				add_filter( 'woocommerce_cart_no_shipping_available_html', array( $this, 'custom_no_shipping_available_message' ) );
				add_filter( 'woocommerce_no_shipping_available_html', array( $this, 'custom_no_shipping_available_message' ) );
			}
				
			/*
			 *	init function
			*/
			function init() {

				// Load Users Roles
				$this->get_users_roles();
				$this->instance_form_fields = array(
				'title' => array(
								'title' 		=> __( 'Method Title', 'wc-ss' ),
								'type' 			=> 'text',
								'description' 		=> __( 'This is the title which the customer sees in the cart and checkout page.', 'wc-ss' ),
								'default'		=> __( 'Super Shipping', 'wc-ss' ),
								'desc_tip'      	=> true,
							),

				'tax_status' => array(
								'title' 		=> __( 'Are you going apply taxes?', 'wc-ss' ),
								'type' 			=> 'select',
								'default' 		=> 'taxable',
								'options'		=> array(
									'taxable' 	=> __( 'Yes', 'wc-ss' ),
									'none' 		=> __( 'No', 'wc-ss' )
								)
							),
				'volumetric_weight_measure' => array(
								'title' 		=> __( 'Measure Volumetric Weight', 'wc-ss' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'If you consider the volumetric weight in the shipping, you have to check this option.', 'wc-ss' ),
								'default' 		=> 'no',
								'desc_tip'		=> true,
							),
				'volumetric_weight_factor' => array(
								'title'         => __( 'Volumetric Weight Factor', 'wc-ss' ),
								'type'          => 'decimal',
								'description'   => __( 'The factor value to calculate the volumetric weight.', 'wc-ss' ),
								'desc_tip'		=> true,
								'placeholder'   => __( 'i.e. 5000', 'wc-ss' )
						    ),
				'calculation_type' => array(
								'title' 		=> __( 'Calculation Type', 'wc-ss' ),
								'type' 			=> 'select',
								'default' 		=> 'order',
								'options'		=> array(
									'order' 	=> __( 'Per order', 'wc-ss' ),
									'item' 		=> __( 'Per item', 'wc-ss' ),
									'class'		=> __( 'Per class', 'wc-ss' )
								)
							),
				'users_roles' => array(
								'title' 		=> __( 'Apply by user rol', 'wc-ss' ),
								'type' 			=> 'multiselect',
								'default' 		=> 'all-users',
								'class'			=> 'chosen_select',
								'options'		=> array_merge( array(
									'all-users' 	=> __( 'All users', 'wc-ss' ),
									'guest-user' 	=> __( 'Guest user', 'wc-ss' )
								), $this->users_roles )
							),
				'including_tax' => array(
								'title' 		=> __( 'Including tax', 'wc-ss' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'Use only when you have shipping rules based on price.', 'wc-ss' ),
								'default' 		=> 'no'
							),
				'apply_percentage' => array(
								'title' 		=> __( 'Apply percentage of the cart total cost', 'wc-ss' ),
								'type' 			=> 'checkbox',
								'label'			=> __( 'If you check this option, the rules calculates the shipping rate based on a percentage of the cart total cost, instead of a fixed cost.', 'wc-ss' ),
								'description' 		=> __( 'For example: a rule with the cost value 10, it mean that the shipping cost is 10% of the cart total cost.', 'wc-ss' ),
								'default' 		=> 'no',
								'desc_tip'		=> true,
							),
				'shipping_class_priority' => array(
								'title' 		=> __( 'Apply shipping class priority', 'wc-ss' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'If you check this option, the final shipping rate will match with the cost of the shipping class with the higher priority.', 'wc-ss' ),
								'default' 		=> 'no'
							),
				);

				// Check compatibility with "Departamentos y Ciudades de Colombia para Woocommerce" plugin.
				if ( !empty( $this->get_cities_for_colombian_department() ) ) {
					
					$this->instance_form_fields = array_merge( $this->instance_form_fields, array(
						'colombian_cities' => array(
	        					'title' => __('Colombian\'s cities to allow this shipping method','wc-ss'),
	        					'type' => 'multiselect',
	        					'class'       => 'wc-enhanced-select',
	        					'description' => __( 'Select the city referring to the region that you have previously added', 'wc-ss' ),
	        					'options' => $this->get_cities_for_colombian_department(),
	        					'desc_tip'    => true,
	        					)));
				}

				$this->instance_form_fields = array_merge( $this->instance_form_fields, array(
				'special_rate' => array(
								'title'         => __( 'Shipping rules', 'wc-ss' ),
								'type'          => 'title',
								'description'   => sprintf( __( 'Set up the shipping rules below. <span %1s>Don\'t you know how the shipphing rules work yet? This can help you &rarr; <a href="%2s" %3s>See the documentation</a></span>', 'wc-ss' ), 'style="float: right;"','https://supershipping.helpscoutdocs.com/', 'class="button button-primary"' )
						    ),

				'shipping_rules' => array(
								'type'		=> 'delivery_special_rate_table'
							),
				'shipping_extra_fees' => array(
								'title'         => __( 'Shipping Extra Fees', 'wc-ss' ),
								'type'          => 'title',
								'description'   => __( 'Here you can to set up the extra fees for each table.', 'wc-ss' )
						    ),
				'shipping_extra_fees_table' => array(
								'type'		=> 'shipping_extra_fees_table'
							),

				'shipping_classes_priority' => array(
								'title'         => __( 'Shipping Classes Priority', 'wc-ss' ),
								'type'          => 'title',
								'description'   => __( 'Here you have to define the shipping class priority.', 'wc-ss' )
						    ),

				'shipping_classes_priority_table' => array(
								'type'		=> 'shipping_classes_priority_table'
							)
				));

				// Define user set variables
				$this->enabled 									= $this->get_option( 'enabled' );
				$this->title 		  							= $this->get_option( 'title' );
				$this->calculation_type 						= $this->get_option( 'calculation_type' );
				$this->tax_status	  							= $this->get_option( 'tax_status' );
				$this->no_shipping_methods_available_message 	= get_option( 'no_shipping_methods_available_message' );
				$this->volumetric_weight_measure				= $this->get_option( 'volumetric_weight_measure' );
				$this->volumetric_weight_factor					= $this->get_option( 'volumetric_weight_factor' );
				$this->users_roles								= is_array( $this->get_option( 'users_roles' ) )? $this->get_option( 'users_roles' ) : (array) $this->get_option( 'users_roles' );
				$this->including_tax							= $this->get_option( 'including_tax' );
				$this->apply_percentage							= $this->get_option( 'apply_percentage' );
				$this->shipping_class_priority					= $this->get_option( 'shipping_class_priority' );
				$this->show_only_free_shipping 					= get_option( 'show_only_free_shipping' );
				$this->show_all_free_shipping_methods 			= get_option( 'show_all_free_shipping_methods' );
				
				// Load shipping rules
				$this->get_shipping_rules();
				
				// Load extra_fees data
				$this->get_shipping_extra_fees();
				
				// Load shipping classes priority data 
				$this->get_shipping_classes_priority();
				
				// [Departamentos y Ciudades de Colombia para Woocommerce] Check compatibility and set list of cities for this instance.
				if ( !empty( $this->get_cities_for_colombian_department() ) ) {
					$this->colombian_cities = $this->get_option( 'colombian_cities' );
				}
				
				// Load default float round system
				$this->float_ceil_system = apply_filters( 'woocommerce_super_shipping_float_ceil_system', false );
				
				// Load the settings API
	            $this->init_form_fields();
	            $this->init_settings();
			}
		
			/*
			 *	Register and enqueue admin style/scripts sheets.
			*/
			function load_styles_and_scripts(){

				// Register style and script files
				wp_register_style( 'wcss_admin_style', plugin_dir_url( __FILE__ ) . 'assets/css/wcss-admin-style.css', array(), WC_SS_VERSION );
				wp_register_script( 'wcss_admin_script', plugin_dir_url( __FILE__ ) . 'assets/js/wcss-admin-script.js', array( 'jquery-ui-dialog' ), WC_SS_VERSION );
				
				// Data to pass to the script
				$data = array(
					'id' => $this->id,
					'users_roles' => json_encode( $this->users_roles ),
					'shipping_classes' => json_encode( WC()->shipping->get_shipping_classes() ),
					'apply_shipping_class_priority_string' => __( "Apply shipping class priority", "wc-ss" ),
					'help_tip_ascp_string' => wc_help_tip( __( "If you check this option, the final shipping rate will match with the cost of the shipping class with the higher priority.", "wc-ss" ) ),
					'apply_percentage_cost_string' => __( "Apply percentage of the cart total cost", "wc-ss" ),
					'help_tip_apc_string' => wc_help_tip( __( "If you check this option, the rules applies a percentage of the cart total cost instead of a fixed cost, i.e: a rule with the cost value 10, it mean that the shipping cost is 10% of the cart total cost.", "wc-ss" ) ),
					'including_tax_string' => __( "Including tax", "wc-ss" ),
					'help_tip_it_string' => wc_help_tip( __( "Use only when you have shipping rules based on price.", "wc-ss" ) ),
					'apply_by_user_rol_string' => __( "Apply by user rol", "wc-ss" ),
					'choose_users_roles' => __( "Choose users roles", "wc-ss" ),
					'shipping_zone_string' => __( "Shipping zone", "wc-ss" ),
					'local_pickup_zone_string' => __( "Where do you want to enable the local pick up?", "wc-ss" ),
					'calculation_type_string' => __( 'Calculation Type', 'wc-ss' ),
					'calculation_type_per_order_string' => __( 'Per order', 'wc-ss' ),
					'calculation_type_per_item_string' => __( 'Per item', 'wc-ss' ),
					'calculation_type_per_class_string' => __( 'Per class', 'wc-ss' ),
					'table_name_string' => __( "Shipping Method Name", "wc-ss" ),
					'help_tip_smn_string' => wc_help_tip( __( 'This text will be shown as shipping method title in Cart and Checkout pages.', 'wc-ss' ) ) ,
					'table_name_default_string' => __( "National Shipping", "wc-ss" ),
					'local_pickup_table_name_default' => __( "Local Pickup", "wc-ss" ),
					'table_string' => __( "Table", "wc-ss" ),
					'error_tip_table_name' => __( "The table name can not be empty.", "wc-ss" ),
					'shipping_class_column_string' => __( "Shipping class", "wc-ss" ),
					'select_shipping_zones_string' => __( "Select a shipping zone", "wc-ss" ),
					'condition_column_string' => __( "Condition", "wc-ss" ),
					'range_column_string' => __( "Range [min] and [max]", "wc-ss" ),
					'cost_column_string' => __( "Cost", "wc-ss" ),
					'help_tip_cost_column_string' => __( "You can add a percentage (without % symbol) or a fixed cost, excluding tax.", 'wc-ss' ),
					'additional_cost_column_string' => __( "Cost per additional unit" , "wc-ss" ),
					'all_users_option_string' => __( "All users", "wc-ss" ),
					'guest_user_option_string' => __( "Guest user", "wc-ss" ),
					'add_row_button_string' => __( "Add row", "wc-ss" ),
					'duplicate_row_button_string' => __( "Duplicate row", "wc-ss" ),
					'delete_row_button_string' => __( "Delete selected rows", "wc-ss" ),
					'no_class_string' => __( "No Class", "wc-ss" ),
					'weight_string' => __( "Weight", "wc-ss" ),
					'price_string' => __( "Price", "wc-ss" ),
					'item_count_string' => __( "Item count", "wc-ss" ),
					'volume_string' => __( "Volume", "wc-ss" ),
					'min_string' => __( "Min", "wc-ss" ),
					'max_string' => __( "Max", "wc-ss" ),
					'remove_region_table_confirm_string' => __( 'Are you sure to delete the selected tables?', 'wc-ss' ),
					'delete_selected_rates_confirm_string' => __( 'Delete the selected rates?', 'wc-ss' ),
					'error_empty_fields_string' => __( 'The range and cost fields can not be empty', 'wc-ss' ),
					'dialog_title' => __( 'Select the type of table', 'wc-ss' )
					);
				wp_localize_script( 'wcss_admin_script', 'wcss_object', $data );
				
				// Load style and script files
				if ( isset( $_GET[ 'page' ] ) && ( $_GET[ 'page' ] == 'wc-settings' ) ) {
					
					wp_enqueue_script( 'wcss_admin_script' );
					wp_enqueue_style( 'wcss_admin_style' );
				}
			}

			/**
			* Initialise Shipping Method Settings Form Fields
			*/
			function init_form_fields() {

				$this->form_fields = array(
				'special_rate' => array(
								'title'         => __( 'Shipping Tables Rates', 'wc-ss' ),
								'type'          => 'title',
								'description'   => __( 'Here you have to define the shipping table rates.', 'wc-ss' )
						    ),

				'delivery_special_rate_table' => array(
								'type'		=> 'delivery_special_rate_table'
							)
				);
			} // End init_form_fields()

			/**
	 		 * Generate Title HTML.
			 *
			 * @param string $key Field key.
			 * @param array  $data Field data.
			 * @since  1.0.0
			 * @return string
			 */
			public function generate_title_html( $key, $data ) {
				$field_key = $this->get_field_key( $key );
				$defaults  = array(
					'title' => '',
					'class' => '',
				);
		
				$data = wp_parse_args( $data, $defaults );
		
				ob_start();
				?>
					</table>
					<h3 class="wc-settings-sub-title <?php echo esc_attr( $data['class'] ); ?>" id="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></h3>
					<?php if ( ! empty( $data['description'] ) ) : ?>
						<p><?php echo wp_kses_post( $data['description'] ); ?></p>
					<?php endif; ?>
					<table class="wcss-form-table">
				<?php
		
				return ob_get_clean();
			}
		
			/**
			* Calculate shipping cost.
			*/	
			function calculate_shipping( $package = array() ) {
				global $woocommerce;
				$final_increase = 0;
				$rates = array();
				$last_product_bundle_key = '';

				$country = $package[ 'destination' ][ 'country' ];
				$state = $package[ 'destination' ][ 'state' ];
				$zipcode = $package[ 'destination' ][ 'postcode' ];

				// Check if package contents has bundle or box type products. If so, the package will be processed
				foreach ( $package[ 'contents' ] as $key => $product_line ) {

					if ( 'wdm_bundle_product' === $product_line[ 'data' ]->get_type() ) {
						
						$last_product_bundle_key = $key;
						$package[ 'contents' ][ $key ] = $this->process_product_bundle( $product_line, $product_line[ 'data' ]->get_type() );
					}elseif ( array_key_exists( 'wdm_custom_bundled_by' , $product_line ) ) {
						
						// Update line totals for bundle product
						$package[ 'contents' ][ $last_product_bundle_key ][ 'line_total' ] += $product_line[ 'line_total' ];
						$package[ 'contents' ][ $last_product_bundle_key ][ 'line_subtotal' ] += $product_line[ 'line_subtotal' ];
						$package[ 'contents' ][ $last_product_bundle_key ][ 'line_tax' ] += $product_line[ 'line_tax' ];
						$package[ 'contents' ][ $last_product_bundle_key ][ 'line_subtotal_tax' ] += $product_line[ 'line_subtotal_tax' ];

						// Delete bundled item from cart contents
						unset( $package[ 'contents' ][ $key ] );
					}
				}
					
				// Check if free shipping is active in order to hide the rest of the shipping methods
				if ( ( $this->show_only_free_shipping == 'yes' ) && array_key_exists( 'free_shipping' , $package[ 'rates' ] ) ) {

					$free_shipping_rate = $package[ 'rates' ][ 'free_shipping' ];
					$package[ 'rates' ] = array();
					$package[ 'rates' ][ 'free_shipping' ] = $free_shipping_rate;
				}else{
						
					$taxes = '';
					switch ( $this->calculation_type ) {

						case 'order':

							$final_increase = $this->get_shipping_cost_by_order( $this->table_rate, $package );
							break;
						
						case 'item':
							
							$final_increase = $this->get_shipping_cost_by_item( $this->table_rate, $package );
							break;

						case 'class':
							
							$final_increase = $this->get_shipping_cost_by_shipping_class( $this->table_rate, $package );
							break;
					}

					// Round decimals when taxes are included
					if ( strcmp( 'taxable' , $this->tax_status ) == 0 ) {

						$shipping_taxes = WC_Tax::get_shipping_tax_rates();
						$shipping_tax = !empty( $shipping_taxes )? current( WC_Tax::get_shipping_tax_rates() ) : 0;
						$key = key( WC_Tax::get_shipping_tax_rates() );
						$taxes = array( $key => round( $final_increase * ( $shipping_tax[ 'rate' ] / 100 ), 2, PHP_ROUND_HALF_DOWN ) );
					}
					
					if ( isset( $final_increase ) ) {
						// Check if this rate has an extra fee
						if ( !empty( $this->shipping_extra_fees ) ){

							foreach ( $this->shipping_extra_fees as $extra_fee ) {
								
								$final_increase += $extra_fee[ 'amount' ];
							}
						}
						$rates[] = array(
							'id' 		=> $this->id . $this->instance_id,
							'label'		=> $this->title,
							'cost' 		=> $final_increase,
							'taxes'		=> isset( $taxes )? $taxes : ''
							);
					}
				}

				if ( !empty( $rates ) ){

					$tables_list = apply_filters( 'woocommerce_super_shipping_only_show_higher_rate', array(), $rates );

					if ( !empty( $tables_list ) && is_array( $tables_list ) ) {

					 	$new_rates = array();
					 	foreach ( $tables_list as $table_id ) {

					 		foreach ( $rates as $key => $rate ) {
					 			
					 			if ( 'table_' . $table_id == $rate[ 'id' ] ) {
					 				$new_rates[] = $rate;
					 				unset( $rates[ $key ] );
					 				break;
					 			}
					 		}
					 	}

					 	// Init higher rate with first rate table
					 	$higher_rate = reset( $new_rates );
					 	$pos = substr( $higher_rate[ 'id' ], -1 );

					 	foreach ( $new_rates as $rate ) {
					 		if ( $higher_rate[ 'cost' ] < $rate[ 'cost' ] ) {

					 			// Update higher rate
					 			$higher_rate = $rate;
					 			$pos = substr( $rate[ 'id' ], -1 );
					 		}
					 	}

					 	if ( isset( $pos ) ) {

					 		$rates[ $pos ] = $higher_rate;

					 		// Resorting rates array
					 		ksort( $rates );
					 	}
					}

					// Add shipping methods
					foreach ($rates as $rate) {

						$this->add_rate( $rate );
					}	
				}
			}

			/**
			* Calculates shipping cost for the entire cart.
			*/
			function get_shipping_cost_by_order( $shipping_table, $package ){

				$final_increase = NULL;
				$cart_total = 0;
				$current_user = wp_get_current_user();

				// Check if volumetric weight measurement is active
				$volumetric_weight = $this->volumetric_weight_measure == 'yes'? $this->calculate_volume( $package, 'order', $this->volumetric_weight_factor ) : array();

				if ( in_array( 'all-users', $this->users_roles ) 
					|| in_array( isset( $current_user->roles[0] )? $current_user->roles[0] : 'all-users' , $this->users_roles )
					|| ( empty( $current_user->roles ) && ( in_array( 'guest-user' , $this->users_roles ) ) ) ){

					if ( !empty( $this->table_rate ) ) {
					
						foreach ( $this->table_rate as $key => $rate ) {
							
							if ( $rate[ 'conditional' ] == 1 ) {
								
								// Calculate shipping based on total weight
								$cart_total  = WC()->cart->cart_contents_weight;

								if ( ( $this->volumetric_weight_measure == 'yes' ) && ( current( $volumetric_weight ) > $cart_total ) ) {
									
									$cart_total  = current( $volumetric_weight );
								}
							}elseif( $rate[ 'conditional' ] == 2 ) {
			
								// Calculate shipping based on total price
								if ( 'yes' == $this->including_tax ) {
			
									$cart_subtotal = 0;
			
									// Calculate cart subtotal just for items
									foreach ($package[ 'contents' ] as $key => $product) {
			
										$cart_subtotal += $product[ 'line_subtotal_tax' ];
									}
										
									$cart_total = $package[ 'contents_cost' ] + $cart_subtotal;
								}else{
			
									$cart_total = $package[ 'contents_cost' ];
								}
							}elseif( $rate[ 'conditional' ] == 3 ) {
								
								// Calculate shipping based on total numbers of items
								$cart_total = WC()->cart->cart_contents_count;

								// Support quantity for product bundle
								foreach ( $package[ 'contents' ] as $key => $product ) {
									
									if ( 'wdm_bundle_product' === $product[ 'data' ]->get_type() ) {

										$cart_total -= $product[ 'quantity' ];
										$cart_total += $product[ 'items_quantity' ];
									}
								}
							}elseif( $rate[ 'conditional' ] == 4 ) {
								
								// Calculate shipping based on volume
								$volume_order = $this->calculate_volume( $package, 'order' );
								$cart_total = current( $volume_order );
							}

							if ( ( ( $cart_total > $rate[ 'range' ][ 'min' ] ) && ( $cart_total <= $rate[ 'range' ][ 'max' ] ) )
								|| ( ( $cart_total > $rate[ 'range' ][ 'min' ] ) && ( $rate[ 'range' ][ 'max' ] == '*' ) ) ) {

								if ( !empty( $rate[ 'cost_per_additional_unit' ] ) && ( $rate[ 'range' ][ 'max' ] == '*' ) && ( 'no' == $this->apply_percentage ) ) {
								
									// Load default float round system
									if ( !$this->float_ceil_system ){

										$final_increase = $rate[ 'cost' ] + ( round( $cart_total - $rate[ 'range' ][ 'min' ], 0, PHP_ROUND_HALF_DOWN ) * $rate[ 'cost_per_additional_unit' ] );
									}else{

										$final_increase = $rate[ 'cost' ] + ( ceil( $cart_total - $rate[ 'range' ][ 'min' ] ) * $rate[ 'cost_per_additional_unit' ] );
									}
								}else{
			
									// Check if is activate apply percentage option
									if ( 'yes' == $this->apply_percentage ) {
										
										$final_increase = ( $rate[ 'cost' ] / 100 ) * WC()->cart->cart_contents_total;
									}else{

										$final_increase = $rate[ 'cost' ];
									}
								}
				
								break;
							}	
						}
					}
				}

				return $final_increase;
			}

			/**
			* Calculates shipping cost based on each item in the basket.
			*/
			function get_shipping_cost_by_item( $shipping_table, $package ){

				$final_increase = NULL;
				$line_item_total = 0;
				$cont = 0;
				$current_user = wp_get_current_user();
				$volume_item = array();
				$quantity = 0;
				$product_weight = 0;

				// Check if volumetric weight measurement is active
				$this->volumetric_weight_measure == 'yes'? $items_volumetric_weight = $this->calculate_volume( $package, 'item', $this->volumetric_weight_factor ) : $items_volumetric_weight = array();

				if ( in_array( 'all-users', $this->users_roles ) 
					|| in_array( isset( $current_user->roles[0] )? $current_user->roles[0] : 'all-users' , $this->users_roles )
					|| ( empty( $current_user->roles ) && ( in_array( 'guest-user' , $this->users_roles ) ) ) ){

					if ( !empty( $this->table_rate ) ) {

						foreach ( $package[ 'contents' ] as $key => $product ) {

							// Get the volumetric weight of the current product
							$volumetric_weight = !empty( $items_volumetric_weight[ $cont ] )? $items_volumetric_weight[ $cont ] : 0;

							foreach ( $this->table_rate as $key => $rate ) {

								if ( $rate[ 'conditional' ] == 1 ) {
									
									// Calculate shipping based on total weight
									// Support weight for product bundle
									if ( 'wdm_bundle_product' === $product[ 'data' ]->get_type() ) {
										
										$line_item_total = $product[ 'items_total_weight' ];
									}else{
		
										// Backward compatibilty with WooCommerce < 3.0.0
										if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

											$line_item_total = $product[ 'quantity' ] * $product[ 'data' ]->weight;
										}else{

											$line_item_total = $product[ 'quantity' ] * $product[ 'data' ]->get_weight();
										}
									}

									// Compare volumetric weight with the weight
									if ( ( $this->volumetric_weight_measure == 'yes' ) && ( $volumetric_weight > $line_item_total ) ) {
										
										$line_item_total  = $volumetric_weight;
									}
								}elseif( $rate[ 'conditional' ] == 2 ) {
									
									// Calculate shipping based on total price
									if ( 'yes' == $this->including_tax ) {
										
										$line_item_total = $product[ 'line_subtotal' ] + $product[ 'line_subtotal_tax' ];
									}else{
			
										$line_item_total = $product[ 'line_subtotal' ];
									}
								}elseif( $rate[ 'conditional' ] == 3 ) {

									// Support quantity for product bundle
									if ( 'wdm_bundle_product' === $product[ 'data' ]->get_type() ) {
										
										$quantity = $product[ 'items_quantity' ];
									}else{
		
										$quantity = $product[ 'quantity' ];
									}
									
									// Calculate shipping based on numbers of items
									$line_item_total = $quantity;	
								}elseif( $rate[ 'conditional' ] == 4 ) {
									
									// Calculate shipping based on volume
									$volume_item = $this->calculate_volume( $package, 'item' );

									if ( $volume_item[ $cont ] > 0 ) {

										$line_item_total = $volume_item[ $cont ];
									}else{

										break;
									}
								}
				
								if ( ( ( $line_item_total > $rate[ 'range' ][ 'min' ] ) && ( $line_item_total <= $rate[ 'range' ][ 'max' ] ) )
									|| ( ( $line_item_total > $rate[ 'range' ][ 'min' ] ) && ( $rate[ 'range' ][ 'max' ] == '*' ) ) ) {
				
									if ( !empty( $rate[ 'cost_per_additional_unit' ] ) && ( $rate[ 'range' ][ 'max' ] == '*' ) && ( 'no' == $this->apply_percentage ) ) {
								
										// Load default float round system
										if ( !$this->float_ceil_system ){

											$final_increase += $rate[ 'cost' ] + ( round( $line_item_total - $rate[ 'range' ][ 'min' ], 0, PHP_ROUND_HALF_DOWN ) * $rate[ 'cost_per_additional_unit' ] );
										}else{

											$final_increase += $rate[ 'cost' ] + ( ceil( $line_item_total - $rate[ 'range' ][ 'min' ] ) * $rate[ 'cost_per_additional_unit' ] );
										}
									}else{
										
										// Check if is activate apply percentage option
										if ( 'yes' == $this->apply_percentage ) {
											
											$final_increase += ( $rate[ 'cost' ] / 100 ) * WC()->cart->cart_contents_total;
										}else{

											$final_increase += $rate[ 'cost' ];
										}
									}
			
									break;
								}
							}

							$cont++;
						}
					}
				}

				return $final_increase;
			}

			/**
			* Calculates shipping cost based on each shipping class.
			*/
			function get_shipping_cost_by_shipping_class( $shipping_table, $package ){

				$final_increase = NULL;
				$shipping_class_cost = 0;
				$most_expensive_shipping_cost = NULL;
				$line_item_total = 0;
				$current_user = wp_get_current_user();

				$package_group_shipping_class = $this->group_by_shipping_class( $package );

				if ( in_array( 'all-users', $this->users_roles ) 
					|| in_array( isset( $current_user->roles[0] )? $current_user->roles[0] : 'all-users' , $this->users_roles )
					|| ( empty( $current_user->roles ) && ( in_array( 'guest-user' , $this->users_roles ) ) ) ){

					foreach ( $package_group_shipping_class as $shipping_class_id => $product_list ) {

						if ( !empty( $this->table_rate ) ) {
		
							foreach ( $this->table_rate as $key => $rate ) {
							
								if ( $rate[ 'conditional' ] == 1 ) {
									
									// Calculate shipping based on total weight
									$line_item_total = $product_list[ 'total_weight' ];

									// Compare volumetric weight with the weight
									if ( ( $this->volumetric_weight_measure == 'yes' ) && ( $product_list[ 'volumetric_weight' ] > $line_item_total ) ) {
										
										$line_item_total  = $product_list[ 'volumetric_weight' ];
									}
								}elseif( $rate[ 'conditional' ] == 2 ) {
									
									// Calculate shipping based on total price
									if ( 'yes' == $this->including_tax ) {
										
										$line_item_total = $product_list[ 'total_price_with_tax' ];
									}else{
			
										$line_item_total = $product_list[ 'total_price' ];
									}
								}elseif( $rate[ 'conditional' ] == 3 ) {
									
									// Calculate shipping based on numbers of items
									$line_item_total = $product_list[ 'total_items' ];	
								}elseif( $rate[ 'conditional' ] == 4 ) {
									
									// Calculate shipping based on volume
									$line_item_total = $product_list[ 'total_volume' ];
								}

								if ( ( ( strcmp( $rate[ 'shipping_class' ], $shipping_class_id ) == 0 ) && ( $line_item_total > $rate[ 'range' ][ 'min' ] ) && ( $line_item_total <= $rate[ 'range' ][ 'max' ] ) )
									|| ( strcmp( $rate[ 'shipping_class' ], $shipping_class_id) == 0 ) && ( ( $line_item_total > $rate[ 'range' ][ 'min' ] ) && ( $rate[ 'range' ][ 'max' ] == '*' ) ) ) {

									if ( !empty( $rate[ 'cost_per_additional_unit' ] ) && ( $rate[ 'range' ][ 'max' ] == '*' ) && ( 'no' == $this->apply_percentage ) ) {	

										// Load default float round system
										if ( !$this->float_ceil_system ){

											$shipping_class_cost = $rate[ 'cost' ] + ( round( $line_item_total - $rate[ 'range' ][ 'min' ], 0, PHP_ROUND_HALF_DOWN ) * $rate[ 'cost_per_additional_unit' ] );
										}else{

											$shipping_class_cost = $rate[ 'cost' ] + ( ceil( $line_item_total - $rate[ 'range' ][ 'min' ] ) * $rate[ 'cost_per_additional_unit' ] );
										}
										
										// If differents shipping classes have the same priority, then the function return the most expensive shipping cost
										if ( ( 'yes' == $this->shipping_class_priority ) && ( $most_expensive_shipping_cost < $shipping_class_cost ) ) {
				
											$most_expensive_shipping_cost = $shipping_class_cost;
										}else{

											$final_increase += $shipping_class_cost;
										}
									}else{
										
										// Check if is activate apply percentage option
										if ( 'yes' == $this->apply_percentage ) {
											
											$shipping_class_cost = ( $rate[ 'cost' ] / 100 ) * WC()->cart->cart_contents_total;
										}else{

											$shipping_class_cost = $rate[ 'cost' ];
										}

										// If differents shipping classes have the same priority, then the function return the most expensive shipping cost
										if ( ( 'yes' == $this->shipping_class_priority ) && ( $most_expensive_shipping_cost < $shipping_class_cost ) ) {
				
											$most_expensive_shipping_cost = $shipping_class_cost;
										}else{

											$final_increase += $shipping_class_cost;
										}
									}
				
									break;
								}
							}
						}
					}
				}

				return $this->shipping_class_priority == 'yes'? $most_expensive_shipping_cost : $final_increase;
			}

			/**
			* Group list of items by shipping class.
			*/
			function group_by_shipping_class( $package ){

				$package_group_shipping_class = array();
				$group_shipping_class_with_higher_priority = array();
				$volumetric_weight = 0;

				foreach ( $package[ 'contents' ] as $key => $product ) {
						
					$shipping_class = $product[ 'data' ]->get_shipping_class();

					// WPML support for translated shipping classes
					if ( $this->wpml_is_active && $this->wc_multilingual_is_active ) {
						global $sitepress;

						$shipping_class_translated_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

						if ( $shipping_class_translated_term ) {
							
							$original_shipping_class_term_id = icl_object_id( $shipping_class_translated_term->term_id, 'product_shipping_class', false, $sitepress->get_default_language() );
							remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
							$shipping_class = get_term_by( 'id', $original_shipping_class_term_id, 'product_shipping_class' );
							$shipping_class = $shipping_class->slug;
							add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );
						}
					}

					if ( !empty( $shipping_class ) ) {

						// Total weight of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ $shipping_class ][ 'total_weight' ] ) ) {
							
							// Support weight for product bundle
							if ( 'wdm_bundle_product' === $product[ 'data' ]->get_type() ) {
								
								$package_group_shipping_class[ $shipping_class ][ 'total_weight' ] = $product[ 'items_total_weight' ];
							}else{

								// Backward compatibilty with WooCommerce < 3.0.0
								if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
									
									$package_group_shipping_class[ $shipping_class ][ 'total_weight' ] = $product[ 'quantity' ] * $product[ 'data' ]->weight;
								}else{

									$package_group_shipping_class[ $shipping_class ][ 'total_weight' ] = !empty( $product[ 'data' ]->get_weight() )? $product[ 'quantity' ] * $product[ 'data' ]->get_weight() : 0;
								}
							}

							// Check if volumetric weight measurement is active
							if ( ( $this->volumetric_weight_measure == 'yes' ) && !isset( $package_group_shipping_class[ $shipping_class ][ 'volumetric_weight' ] ) ) {
								
								$volumetric_weight = $this->calculate_volume( $package, 'shipping_class', $this->volumetric_weight_factor, $product );
								$package_group_shipping_class[ $shipping_class ][ 'volumetric_weight' ] = current( $volumetric_weight );
							}
						}else{

							// Support weight for product bundle
							if ( 'wdm_bundle_product' === $product[ 'data' ]->get_type() ) {
								
								$package_group_shipping_class[ $shipping_class ][ 'total_weight' ] += $product[ 'items_total_weight' ];
							}else{
							
								// Backward compatibilty with WooCommerce < 3.0.0
								if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

									$package_group_shipping_class[ $shipping_class ][ 'total_weight' ] += $product[ 'quantity' ] * $product[ 'data' ]->weight;
								}else{

									$package_group_shipping_class[ $shipping_class ][ 'total_weight' ] += !empty( $product[ 'data' ]->get_weight() )? $product[ 'quantity' ] * $product[ 'data' ]->get_weight() : 0;
								}
							}

							// Check if volumetric weight measurement is active
							if ( $this->volumetric_weight_measure == 'yes' ) {
								
								$volumetric_weight = $this->calculate_volume( $package, 'shipping_class', $this->volumetric_weight_factor, $product );
								$package_group_shipping_class[ $shipping_class ][ 'volumetric_weight' ] += current( $volumetric_weight ); 
							}
						}

						// Total price of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ $shipping_class ][ 'total_price' ] ) ) {
							
							$package_group_shipping_class[ $shipping_class ][ 'total_price' ] = $product[ 'line_subtotal' ];
						}else{

							$package_group_shipping_class[ $shipping_class ][ 'total_price' ] += $product[ 'line_subtotal' ];
						}

						// Total price including tax of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ $shipping_class ][ 'total_price_with_tax' ] ) ) {
							
							$package_group_shipping_class[ $shipping_class ][ 'total_price_with_tax' ] = $product[ 'line_subtotal' ] + $product[ 'line_subtotal_tax' ];
						}else{

							$package_group_shipping_class[ $shipping_class ][ 'total_price_with_tax' ] += $product[ 'line_subtotal' ] + $product[ 'line_subtotal_tax' ];
						}

						// Total items of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ $shipping_class ][ 'total_items' ] ) ) {
							
							// Support quantity for product bundle
							if ( 'wdm_bundle_product' === $product[ 'data' ]->get_type() ) {
								
								$package_group_shipping_class[ $shipping_class ][ 'total_items' ] = $product[ 'items_quantity' ];
							}else{
								
								$package_group_shipping_class[ $shipping_class ][ 'total_items' ] = $product[ 'quantity' ];
							}
						}else{

							// Support quantity for product bundle
							if ( 'wdm_bundle_product' === $product[ 'data' ]->get_type() ) {
								
								$package_group_shipping_class[ $shipping_class ][ 'total_items' ] += $product[ 'items_quantity' ];
							}else{
								
								$package_group_shipping_class[ $shipping_class ][ 'total_items' ] += $product[ 'quantity' ];
							}
						}

						// Total volume of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ $shipping_class ][ 'total_volume' ] ) ) {
							
							$volume_by_shipping_class = $this->calculate_volume( $package, 'shipping_class', 1, $product );
							$package_group_shipping_class[ $shipping_class ][ 'total_volume' ] = current( $volume_by_shipping_class );
						}else{

							$volume_by_shipping_class = $this->calculate_volume( $package, 'shipping_class', 1, $product );
							$package_group_shipping_class[ $shipping_class ][ 'total_volume' ] += current( $volume_by_shipping_class );
						}

						// List of product grouped by shipping class
						$package_group_shipping_class[ $shipping_class ][ 'products' ][] = $product;
					}else{

						// Total weight of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ 'no-class' ][ 'total_weight' ] ) ) {
							
							// Backward compatibilty with WooCommerce < 3.0.0
							if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

								$package_group_shipping_class[ 'no-class' ][ 'total_weight' ] = $product[ 'quantity' ] * $product[ 'data' ]->weight;
							}else{

								$package_group_shipping_class[ 'no-class' ][ 'total_weight' ] = !empty( $product[ 'data' ]->get_weight() )? $product[ 'quantity' ] * floatval( $product[ 'data' ]->get_weight() ) : 0;
							}

							// Check if volumetric weight measurement is active
							if ( ( $this->volumetric_weight_measure == 'yes' ) && !isset( $package_group_shipping_class[ 'no-class' ][ 'volumetric_weight' ] ) ) {
								
								$volumetric_weight = $this->calculate_volume( $package, 'shipping_class', $this->volumetric_weight_factor, $product );
								$package_group_shipping_class[ 'no-class' ][ 'volumetric_weight' ] = current( $volumetric_weight );
							}
						}else{

							// Check if volumetric weight measurement is active
							if ( $this->volumetric_weight_measure == 'yes' ) {
								
								$volumetric_weight = $this->calculate_volume( $package, 'shipping_class', $this->volumetric_weight_factor, $product );
								$package_group_shipping_class[ 'no-class' ][ 'volumetric_weight' ] += current( $volumetric_weight );
							}

							// Backward compatibilty with WooCommerce < 3.0.0
							if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
								
								$package_group_shipping_class[ 'no-class' ][ 'total_weight' ] += $product[ 'quantity' ] * $product[ 'data' ]->weight;
							}else{

								$package_group_shipping_class[ 'no-class' ][ 'total_weight' ] += !empty( $product[ 'data' ]->get_weight() )? $product[ 'quantity' ] * $product[ 'data' ]->get_weight() : 0;
							}
						}

						// Total price of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ 'no-class' ][ 'total_price' ] ) ) {
							
							$package_group_shipping_class[ 'no-class' ][ 'total_price' ] = $product[ 'line_subtotal' ];
						}else{

							$package_group_shipping_class[ 'no-class' ][ 'total_price' ] += $product[ 'line_subtotal' ];
						}

						// Total price including tax of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ 'no-class' ][ 'total_price_with_tax' ] ) ) {
							
							$package_group_shipping_class[ 'no-class' ][ 'total_price_with_tax' ] = $product[ 'line_subtotal' ] + $product[ 'line_subtotal_tax' ];
						}else{

							$package_group_shipping_class[ 'no-class' ][ 'total_price_with_tax' ] += $product[ 'line_subtotal' ] + $product[ 'line_subtotal_tax' ];
						}

						// Total items of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ 'no-class' ][ 'total_items' ] ) ) {
							
							$package_group_shipping_class[ 'no-class' ][ 'total_items' ] = $product[ 'quantity' ];
						}else{

							$package_group_shipping_class[ 'no-class' ][ 'total_items' ] += $product[ 'quantity' ];
						}

						// Total volume of the entire shipping class group
						if ( !isset( $package_group_shipping_class[ 'no-class' ][ 'total_volume' ] ) ) {
							
							$volume_by_shipping_class = $this->calculate_volume( $package, 'shipping_class', 1, $product );
							$package_group_shipping_class[ 'no-class' ][ 'total_volume' ] = current( $volume_by_shipping_class );
						}else{

							$volume_by_shipping_class = $this->calculate_volume( $package, 'shipping_class', 1, $product );
							$package_group_shipping_class[ 'no-class' ][ 'total_volume' ] += current( $volume_by_shipping_class );
						}

						// List of product grouped by shipping class
						$package_group_shipping_class[ 'no-class' ][ 'products' ][] = $product;
					}
				} 

				if ( ( 'yes' == $this->shipping_class_priority ) && !empty( $this->shipping_classes_priority ) ){

					$higher_priority = $this->get_first_class_priority_of_the_cart( $this->shipping_classes_priority, current( array_keys( $package_group_shipping_class ) ) );

					foreach ($package_group_shipping_class as $key => $group_shipping_class) {
						
						foreach ($this->shipping_classes_priority as $shipping_priority) {
							
							if ( $key == $shipping_priority[ 'shipping_class' ] ) {

								if ( $higher_priority > $shipping_priority[ 'priority' ] ) {
									
									if ( count( $group_shipping_class_with_higher_priority ) > 0 ) {
										
										$group_shipping_class_with_higher_priority = array( $key => $group_shipping_class );
									}else{
										$group_shipping_class_with_higher_priority = array_merge( array( $key => $group_shipping_class ), $group_shipping_class_with_higher_priority );	
									}

									// Update higher priority with the current value
									$higher_priority = $shipping_priority[ 'priority' ];
								}elseif ( $higher_priority == $shipping_priority[ 'priority' ] ) {

									$group_shipping_class_with_higher_priority = array_merge( $group_shipping_class_with_higher_priority, array( $key => $group_shipping_class ) );
									
									// Update higher priority with the current value
									$higher_priority = $shipping_priority[ 'priority' ];
								}
							}else{ 

								continue;
							}
						}
					}

					$package_group_shipping_class = $group_shipping_class_with_higher_priority; 
				}

				return $package_group_shipping_class;
			}

			/**
			* get_first_class_priority_of_the_cart function.
			*
			* @access public
			* @return int
			*/
			private function get_first_class_priority_of_the_cart( $shipping_classes_priority, $first_group_shipping_class_cart ){

				$first_class_priority = 999;

				foreach ($shipping_classes_priority as $key => $value) {
					
					if ( $value[ 'shipping_class' ] == $first_group_shipping_class_cart ) {
						
						$first_class_priority = $value[ 'priority' ];
						break;
					}
				}

				return $first_class_priority;
			}

			/**
			* calculate_bundle_weight_and_quantity function.
			*
			* @access private
			* @return array $bundle_weight_and_quantity
			*/
			private function calculate_bundle_weight_and_quantity( $bundled_items, $product_bundle_quantity, $default_bundle_weight = 0 ){
				$product_bundle_weight = 0;
				$items_bundled = 0;
				$bundle_weight_and_quantity = array( 
						'weight' => $default_bundle_weight, 
						'quantity' => $product_bundle_quantity );

				foreach ( $bundled_items as $product_id => $product ) {
								
					if ( 0 < $product[ 'quantity' ] ) {

						$product_object = wc_get_product( $product_id );

						// Backward compatibilty with WooCommerce < 3.0.0
						if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

							$product_weight = $product_object->weight; 
						}else{

							$product_weight = $product_object->get_weight();
						}

						$product_bundle_weight += $product[ 'quantity' ] * $product_bundle_quantity * $product_weight;
						$items_bundled += $product[ 'quantity' ];
					}
				}
					
				$bundle_weight_and_quantity[ 'weight' ] += $product_bundle_weight;
				$bundle_weight_and_quantity[ 'quantity' ] *= $items_bundled;

				return $bundle_weight_and_quantity;
			}

			/**
			* calculate_volume function.
			*
			* @access private
			* @return array
			*/
			private function calculate_volume( $package, $calculation_type, $volumetric_weight_factor = 1 , $product = '' ){

				$volumetric_weight_array = array();
				$volumetric_weight = 0;

				if ( $calculation_type == 'order' ) {
					
					foreach ( $package[ 'contents' ] as $key => $product ) {

						if ( $volumetric_weight_factor == 1 ) {
							
							// Backward compatibilty with WooCommerce < 3.0.0
							if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

								$volumetric_weight += ( $product[ 'data' ]->width * $product[ 'data' ]->height * $product[ 'data' ]->length ) * $product[ 'quantity' ];
							}else{

								$volumetric_weight += ( $product[ 'data' ]->get_width() * $product[ 'data' ]->get_height() * $product[ 'data' ]->get_length() ) * $product[ 'quantity' ];
							}
						}elseif ( $volumetric_weight_factor > 1 ) {

							// Backward compatibilty with WooCommerce < 3.0.0
							if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
							
								$volumetric_weight += ( $product[ 'data' ]->width * $product[ 'data' ]->height * $product[ 'data' ]->length / $volumetric_weight_factor ) * $product[ 'quantity' ];
							}else{

								$volumetric_weight += ( $product[ 'data' ]->get_width() * $product[ 'data' ]->get_height() * $product[ 'data' ]->get_length() / $volumetric_weight_factor ) * $product[ 'quantity' ];
							}
						}
					}

					$volumetric_weight_array[] = $volumetric_weight;
				}elseif ( $calculation_type == 'item' ) {
					
					foreach ( $package[ 'contents' ] as $key => $product ) {
						
						if ( $volumetric_weight_factor == 1 ) {
							
							// Backward compatibilty with WooCommerce < 3.0.0
							if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

								$volumetric_weight_array[] = ( $product[ 'data' ]->width * $product[ 'data' ]->height * $product[ 'data' ]->length ) * $product[ 'quantity' ];
							}else{

								$volumetric_weight_array[] = ( $product[ 'data' ]->get_width() * $product[ 'data' ]->get_height() * $product[ 'data' ]->get_length() ) * $product[ 'quantity' ];
							}
						}elseif ( $volumetric_weight_factor > 1 ) {
							
							// Backward compatibilty with WooCommerce < 3.0.0
							if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
							
								$volumetric_weight_array[] = ( $product[ 'data' ]->width * $product[ 'data' ]->height * $product[ 'data' ]->length / $volumetric_weight_factor ) * $product[ 'quantity' ];
							}else{

								$volumetric_weight_array[] = ( $product[ 'data' ]->get_width() * $product[ 'data' ]->get_height() * $product[ 'data' ]->get_length() / $volumetric_weight_factor ) * $product[ 'quantity' ];
							}
						}
					}
				}elseif( ( $calculation_type == 'shipping_class' ) && !empty( $product ) ){

					if ( $volumetric_weight_factor == 1 ) {
						
						// Backward compatibilty with WooCommerce < 3.0.0
						if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

							$volumetric_weight = ( $product[ 'data' ]->width * $product[ 'data' ]->height * $product[ 'data' ]->length ) * $product[ 'quantity' ];
						}else{

							$volumetric_weight = ( floatval( $product[ 'data' ]->get_width() ) * floatval( $product[ 'data' ]->get_height() ) * floatval( $product[ 'data' ]->get_length() ) ) * $product[ 'quantity' ];
						}
					}elseif ( $volumetric_weight_factor > 1 ) {
						
						// Backward compatibilty with WooCommerce < 3.0.0
						if ( version_compare( WC()->version, '3.0.0', '<' ) ) {	
							
							$volumetric_weight = ( $product[ 'data' ]->width * $product[ 'data' ]->height * $product[ 'data' ]->length / $volumetric_weight_factor ) * $product[ 'quantity' ];
						}else{

							$volumetric_weight = ( $product[ 'data' ]->get_width() * $product[ 'data' ]->get_height() * $product[ 'data' ]->get_length() / $volumetric_weight_factor ) * $product[ 'quantity' ];
						}
					}

					$volumetric_weight_array[] = $volumetric_weight;
				}

				return $volumetric_weight_array;
			}
			
			/**
			* generate_delivery_special_rate_table_html function.
			*
			* @access public
			* @return void
			*/
			function generate_delivery_special_rate_table_html() {
				
				ob_start();
				include( 'includes/html-shipping-tables.php' );
				return ob_get_clean();
			}

			/**
			* generate_shipping_extra_fees_table_html function.
			*
			* @access public
			* @return void
			*/
			function generate_shipping_extra_fees_table_html() {
				
				ob_start();
				include( 'includes/html-shipping-extra-fees-table.php' );
				return ob_get_clean();
			}

			/**
			* generate_shipping_classes_priority_table_html function.
			*
			* @access public
			* @return void
			*/
			function generate_shipping_classes_priority_table_html() {
				
				ob_start();
				include( 'includes/html-shipping-classes-priority.php' );
				return ob_get_clean();
			}

			/**
			* add_plugin_action_link function.
			*
			* @access public
			* @return void
			*/
			public static function add_plugin_action_link( $links ){

				$setting_link = array(
					'<a href="'. admin_url( 'index.php?page=wss-setup' ) .'" style="color: #a16696; font-weight: bold;">'. __( 'Start here', 'wc-ss' ) .'</a>',
					'<a href="https://supershipping.helpscoutdocs.com/" target="_blank" >'. __( 'Documentation', 'wc-ss' ) .'</a>'
				);

				return array_merge( $setting_link, $links );
			}	

			/**
			* define_plugin_row_meta function.
			*
			* @access public
			* @return array $links 
			*/
			public static function define_plugin_row_meta( $links, $file ) {

				if ( strpos( $file, WC_SS_FILE_NAME ) !== false ) {
					$new_links = array(
								'<a href="https://supershipping.helpscoutdocs.com/" target="_blank"><strong>'. __( 'Doubs?', 'wc-ss' ) .'</strong></a>'
							);
					
					$links = array_merge( $links, $new_links );
				}
				
				return $links;
			}

			/**
				* Processes and saves options.
				* If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
				* 
				* @return bool was anything saved?
				*/
			public function process_admin_options(){

				parent::process_admin_options();
				$this->process_shipping_tables_data();
				$this->process_shipping_extra_fees();
				$this->process_shipping_classes_priority();
			}

			/**
			* process_product_bundle function.
			*
			* @access public
			* @return array $processed_product_bundle
			*/
			function process_product_bundle( $product_line, $bundle_type ){
				$processed_product_bundle = $product_line;
				$bundle_weight_and_quantity = array();

				if ( 'wdm_bundle_product' === $bundle_type ) {
					
					// Backward compatibilty with WooCommerce < 3.0.0
					if ( version_compare( WC()->version, '3.0.0', '<' ) ) {

						$bundle_weight_and_quantity = $this->calculate_bundle_weight_and_quantity( $product_line[ 'wdm_custom_stamp' ], $product_line[ 'quantity' ], $product_line[ 'data' ]->weight  );
					}else{

						$bundle_weight_and_quantity = $this->calculate_bundle_weight_and_quantity( $product_line[ 'wdm_custom_stamp' ], $product_line[ 'quantity' ], $product_line[ 'data' ]->get_weight()  );
					}

					// Define a new array register with the total of items weight
					$processed_product_bundle[ 'items_total_weight' ] = $bundle_weight_and_quantity[ 'weight' ];

					// Define a new array register with the total of items quantity
					$processed_product_bundle[ 'items_quantity' ] = $bundle_weight_and_quantity[ 'quantity' ];
				}

				return $processed_product_bundle;
			}
			
			/**
			* process_shipping_tables_data function.
			*
			* @access public
			* @return void
			*/
			public function process_shipping_tables_data() {
				$new_instance_settings = get_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings', array() );

				if( isset( $_POST[ $this->id ] ) ){

					$new_instance_settings[ 'shipping_rules' ] = $this->process_shipping_rate_table( $_POST[ $this->id ] );
				}

				update_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings', $new_instance_settings );
				$this->get_shipping_rules();
			}

			/**
			* process_shipping_rate_table function.
			*
			* @access private
			* @param $data Array with form fields of shipping table
			* @param $region Shipping table ID
			* @return $shipping_table_processed Array with processed data of shipping table 
			*/
			private function process_shipping_rate_table( $data ){
				$shipping_table_processed = array();
				
				if ( isset( $data[ 'shipping_class' ] ) ) {
					$max = count( $data[ 'shipping_class' ] ) - 1;
				
					$cont = 0;
					for( $i=0; $i<=$max; $i++ ){

						if( isset( $data[ 'cost' ][$i] )
							&& ( $data[ 'cost' ][$i] >= 0 )
							&& !empty( $data[ 'range' ][ 'min' ] ) 
							&& !empty( $data[ 'range' ][ 'max' ] ) ){
							$shipping_table_processed[ $cont ] = array( 'shipping_class' 	=> $data[ 'shipping_class' ][$i], 
										'conditional' 				=> $data[ 'conditional' ][$i],
										'range' 				=> array( 'min' => $data[ 'range' ][ 'min' ][$i], 'max' => $data[ 'range' ][ 'max' ][$i] ),
										'cost' 					=> $data[ 'cost' ][$i],
										'cost_per_additional_unit' 		=> $data[ 'cost_per_additional_unit' ][$i]);
							$cont++;
						}
					}
				}

				return $shipping_table_processed;
			}

			/**
			* process_shipping_extra_fees function.
			*
			* @access public
			* @return void
			*/
			function process_shipping_extra_fees(){

				$new_instance_settings = get_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings', array() );
				$cont = 1;

				if( isset( $_POST[ $this->id .'_extra_fees' ] ) ){
					
					$shipping_extra_fees_list = $_POST[ $this->id .'_extra_fees' ];
					$new_instance_settings[ 'shipping_extra_fees_table' ] = array();

					foreach ( $shipping_extra_fees_list as $key => $fee ){
						
						if ( !empty( $fee[ 'amount' ] ) && !empty( $fee[ 'label' ] ) ) {

							$new_instance_settings[ 'shipping_extra_fees_table' ][] = array( 
								'label' => !empty( $fee[ 'label' ] )? wc_clean( $fee[ 'label' ] ) : __( 'Fee', 'wc-ss' ) .' '. $cont,
								'amount' => wc_clean( $fee[ 'amount' ] ) );
							$cont++;
						}
					}
				}elseif ( !empty( $_GET['do_update_woocommerce_super_shipping'] ) ) {

					delete_option( 'woocommerce_super_shipping_tables_shipping_extra_fees' );
				}

				update_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings', $new_instance_settings );
				$this->get_shipping_extra_fees(); 
			}

			/**
			* process_shipping_classes_priority function.
			*
			* @access public
			* @return void
			*/
			function process_shipping_classes_priority(){

				$new_instance_settings = get_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings', array() );
				$new_shipping_classes_priority_list = array();  

				if( isset( $_POST[ $this->id .'_classes_priority' ] ) ){
					
					$shipping_classes_priority_list = $_POST[ $this->id .'_classes_priority' ];
					$new_instance_settings[ 'shipping_classes_priority_table' ] = array();
					
					foreach ( $shipping_classes_priority_list as $key => $shipping_priority ){
						
						if ( !empty( $shipping_priority[ 'priority' ] ) ) {
							
							// Avoid save row if it allready exist a priority row for the same shipping class 
							if ( in_array( $shipping_priority[ 'shipping_class' ], wp_list_pluck( $new_shipping_classes_priority_list, 'shipping_class' ) ) ) continue;

							$new_instance_settings[ 'shipping_classes_priority_table' ][] = array( 
								'shipping_class' => $shipping_priority[ 'shipping_class' ],
								'priority' => $shipping_priority[ 'priority' ] 
							); 
						}
					}
				}elseif ( !empty( $_GET['do_update_woocommerce_super_shipping'] ) ) {

					delete_option( 'woocommerce_super_shipping_tables_shipping_classes_priority' );
				}

				update_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings', $new_instance_settings );
				$this->get_shipping_classes_priority();
			}

			/**
			* prepare_shipping_methods_string_translations function.
			*
			* @access public
			* @param array $rates Array of rates found for the package
			* @param array $package The package array/object being shipped
			* @return string
			*/
			function prepare_shipping_methods_string_translations( $rates, $package ) {

				if( !function_exists( 'icl_t' ) ) return $rates;

				foreach( $rates as $key => $rate ){
						
						$rate->label = icl_t( 'wc-ss', 'shipping-title-' . sanitize_title( $rate->id ), $rate->label );
				}

					return $rates;
			}

			/**
			* show_only_free_shipping_method_when_is_active function.
			*
			* @access public
			* @return void
			*/
			function show_only_free_shipping_method_when_is_active( $rates, $package ) {

				$this->show_only_free_shipping 	= get_option( 'show_only_free_shipping' );
				$this->show_all_free_shipping_methods = get_option( 'show_all_free_shipping_methods' );
				$shipping_method_meta_data = array();
				$there_is_free_shipping = false;
				$new_rates = array();

				if ( $this->show_only_free_shipping == 'yes' ){

					foreach ( $rates as $key => $value ) {
						
						if( $value->method_id == 'free_shipping' ){
							$new_rates[ $value->id ] = $value;
							return $new_rates;
						}

						if( $value->method_id == 'super_shipping' ){

							$shipping_method_meta_data = $value->get_meta_data();
							if ( $value->cost == 0 ) {
								
								$free_super_shipping	= $value;
								$new_rates[ $value->id ] = $free_super_shipping;
								$there_is_free_shipping = true;
							}
						}
					}

					// Check if there is more than one free shipping method.
					if ( $this->show_all_free_shipping_methods == 'no' ) {

						if( count( $new_rates ) > 1 ){

							// Excluding local pickup shipping method
							$local_pickup_rates = array();
							$shipping_method_meta_data = array();
							foreach ( $new_rates as $key => $rate ) {

								$shipping_method_meta_data = $rate->get_meta_data();
								if ( isset( $shipping_method_meta_data[ 'type_of_table' ] ) && ( 'local_pickup' == $shipping_method_meta_data[ 'type_of_table' ] ) ) {
									
									$local_pickup_rates[] = $rate;
								} 	
							}
							reset( $new_rates );
							$first_free_super_shipping = current( $new_rates );
							$new_rates = array();
							$new_rates[] = $first_free_super_shipping;
							$new_rates = array_merge( $new_rates, $local_pickup_rates );
						}
					}
				}
				
				return !empty( $new_rates ) && $there_is_free_shipping? $new_rates : $rates ;;
			}

			/**
			* custom_no_shipping_available_message function.
			*
			* @access public
			* @return string
			*/
			function custom_no_shipping_available_message( $message ){

				return !empty( $this->no_shipping_methods_available_message )? wpautop( $this->no_shipping_methods_available_message ) : $message;
			}

			/**
			* get_users_roles function.
			*
			* @access public
			* @return void
			*/
			function get_users_roles(){
				$new_users_roles_list = array();
				$this->users_roles = get_editable_roles();

				foreach ( $this->users_roles as $key => $value) {
					$new_users_roles_list[ $key ] = __( $value[ 'name' ], 'wc-ss' );
				}

				$this->users_roles = $new_users_roles_list;
			}

			/**
			* get_special_increase_rates function.
			*
			* @access public
			* @return void
			*/
			function get_shipping_rules() {
				$saved_settings = get_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings' );
				$this->table_rate = $saved_settings[ 'shipping_rules' ]; 
			}

			/**
			* get_shipping_extra_fees function.
			*
			* @access public
			* @return void
			*/
			function get_shipping_extra_fees() {
				$saved_settings = get_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings' );
				$this->shipping_extra_fees = $saved_settings[ 'shipping_extra_fees_table' ]; 
			}

			/**
			* get_shipping_classes_priority function.
			*
			* @access public
			* @return void
			*/
			function get_shipping_classes_priority() {
				$saved_settings = get_option( 'woocommerce_'. $this->id .'_'. $this->instance_id .'_settings' );
				$this->shipping_classes_priority = $saved_settings[ 'shipping_classes_priority_table' ];
			}
			
			/**
			* is_available function.
			*
			* @access public
			* @param mixed $package
			* @return bool
			*/
			function is_available( $package ) {
				global $woocommerce;

				if ($this->enabled=="no") return false;

					if ($this->availability=='including') :

						if (is_array($this->countries)) :
							if ( ! in_array( $package['destination']['country'], $this->countries) ) return false;
						endif;

					else :

						if (is_array($this->countries)) :
							if ( in_array( $package['destination']['country'], $this->countries) ) return false;
						endif;

					endif;

				return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true );
			}

			/**
			* get_tables_rates function.
			*
			* @access public
			* @return array
			*/
			public function get_tables_rates() {

				foreach ( $this->table_rate as $key => $value) {
					$this->tables_rates[] = $key;
				}

				return $this->tables_rates;
			}

			/**
			 * get_cities_for_colombian_department
			 * Compatibility with "Departamentos y Ciudades de Colombia para Woocommerce" plugin (https://es.wordpress.org/plugins/departamentos-y-ciudades-de-colombia-para-woocommerce/)
			 * @access public
			 * @return bool
			 */
			public function get_cities_for_colombian_department(){

				$list_of_cities = array();
				if ( class_exists( 'Filters_By_Cities_Method' ) ) {

					$instance_zone = WC_Shipping_Zones::get_zone_by( 'instance_id', $this->instance_id );
					$locations = $instance_zone->get_zone_locations();

					foreach ( $locations as $state ) {
						
						if ( is_object( $state ) && ( 'state' === $state->type ) && ( strpos( $state->code, 'CO:' ) !== false ) ) {
							
							require_once( ABSPATH . '/wp-content/plugins/departamentos-y-ciudades-de-colombia-para-woocommerce/includes/filter-by-cities.php' );
							$dccwc_object = new Filters_By_Cities_Method();
							$list_of_cities = $dccwc_object->showCitiesRegions();
							break;
						}
					} 
				}

				return $list_of_cities;
			}
		}

		/**
		 * Initialize plugin. Main instance of WooCommerce Super Shipping.
		 *
		 * Returns the main instance of WCSS to prevent the need to use globals.
		 *
		 * @return WooCommerce_Super_Shipping
		 */
		function woocommerce_super_shipping_instance() {

			return WooCommerce_Super_Shipping::instance();
		}
		woocommerce_super_shipping_instance();
			
		// Add Super Shipping method to the shipping methods list
		add_filter( 'woocommerce_shipping_methods', 'add_super_shipping_method' );
		function add_super_shipping_method( $methods ) {

			$methods[ 'super_shipping' ] = 'WooCommerce_Super_Shipping'; return $methods;
		}

		// Add Setting link and Shipping Zones link in the plugin action links and row meta respectively
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( 'WooCommerce_Super_Shipping', 'add_plugin_action_link' ) );
		add_filter( 'plugin_row_meta', array( 'WooCommerce_Super_Shipping', 'define_plugin_row_meta' ), 10, 2 );

		// Add new field in the general shipping options to customize the message when there are not shipping methods available 
		add_filter( 'woocommerce_get_settings_shipping', 'shipping_methods_when_free_shipping_is_active_settings' );
		function shipping_methods_when_free_shipping_is_active_settings( $settings ){

			$new_setting = array( array(
						'title'			=> __( 'How to display shipping methods', 'wc-ss' ),
						'desc' 			=> __( 'Hide the rest of shipping methods when free shipping is available', 'wc-ss' ),
						'type' 			=> 'checkbox',
						'id'			=> 'show_only_free_shipping',
						'default' 		=> 'no',
						'checkboxgroup' 	=> 'start',
					),
					array(
						'desc' 		=> __( 'Show all free shipping methods', 'wc-ss' ),
						'type' 			=> 'checkbox',
						'id'			=> 'show_all_free_shipping_methods',
						'desc_tip' 			=> __( 'If there are more than one free shipping methods availables, the plugin will show all of them.', 'wc-ss' ),
						'default' 		=> 'no',
						'checkboxgroup'		=> 'end'
					),
					array(	'title' 		=> __( 'No shipping methods available message', 'wc-ss' ),
						'type' 			=> 'textarea',
						'id'			=> 'no_shipping_methods_available_message',
						'description' 		=> __( 'This message will be shown in the cart page when no shipping methods availables.', 'wc-ss' ),
						'default'		=> __( 'Sorry, right now there are no shipping methods available for your address', 'wc-ss' )
					)
			);
			array_splice( $settings , count( $settings )-1, 0, $new_setting );

			return $settings;
		}
	}
}

?>