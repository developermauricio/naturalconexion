<?php

/*
 * WSS Setup Wizard
 * 
 * When the plugin is updated to v1.3, takes the users through some basic step to setup their data from Super Shipping old shipping zones system to the WC native system.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Data setup class
 */

if ( !class_exists( 'WSS_Setup_Wizard' ) ) {

	class WSS_Setup_Wizard{

		public function __construct(){

			if ( current_user_can( 'manage_woocommerce' ) ) {
				
				$this->include_dependencies();
				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_init', array( $this, 'show_wizard_page' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'wp_ajax_start_migration_process', array( $this, 'start_migration_process' ) );
			}
		}

		/**
	 	* Add admin menus/screens.
	 	*/
		public function admin_menus(){

			add_dashboard_page( '', '', 'manage_options', 'wss-setup', '' );
		}

		/**
		 * Handle redirects to setup wizard page after updates.
		 */
		public static function admin_redirects(){

			// Setup wizard redirect
			if ( get_transient( 'wss_activation_redirect' ) ) {

				if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'wss-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
					
					return;
				}

				// If the update is not supported, then show an error message and deactivate the plugin
				if ( 'not_supported' == get_transient( 'wss_activation_redirect' ) ) {
					
					deactivate_plugins( 'woocommerce-super-shipping/woocommerce-super-shipping.php' );
					delete_transient( 'wss_activation_redirect' );
					$message = sprintf( __( 'Sorry, but <strong>this version is not compatible with your current Super Shipping\'s version</strong>.<br><br> You need to have installed at least the 1.2.7 version in order to can update to the last version.<br><br> Contact with the technical support to ask for help <a href="%s" target="_blank">clicking here</a>.', 'wc-ss' ), 'https://supershipping.helpscoutdocs.com/');
					$message .= '</p><p><a class="button button-primary button-large" href="'. admin_url( 'plugins.php' ) .'">'. __( 'Finish and exit', 'wc-ss' ) .'</a>';
					wp_die( $message );
				}
	
				// If the user needs to migrate data, send them to the setup wizard
				wp_safe_redirect( admin_url( 'index.php?page=wss-setup' ) );
				exit;
			}
		}

		/**
		 * Register/enqueue scripts and styles for the Setup Wizard.
		 *
		 * Hooked onto 'admin_enqueue_scripts'.
		 */
		public function enqueue_scripts() {

			// Setup/welcome
			if ( ! empty( $_GET['page'] ) && 'wss-setup' == $_GET['page'] ) {

				wp_register_script( 'wss-setup', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/wcss-setup-wizard.js', array( 'jquery', 'jquery-blockui' ), null, false );
				wp_localize_script( 'wss-setup', 
									'wss_setup_param',
									array(
										'url'							=> admin_url( 'admin-ajax.php' ),
										'start_migration_wizard_nonce'	=> wp_create_nonce( 'start_migration_wizard' ),
										'action'						=> 'start_migration_process',
										'success_message'				=> __( 'Congratulation! Your settings are migrated successfully. You can check it clicking on the button below.<br><br><strong>IMPORTANT!</strong>: You shouldn\'t delete the shipping zones named with the term "Zone" followed by a number (i.e. <em>Zone 0</em>) and which don\'t have any shipping methods assigned. These zones have been created to exclude those shipping areas.', 'wc-ss' ),
										'error_message'					=> __( '<strong>ERROR:</strong> The migration process couldn\'t finish successfully.', 'wc-ss' ),
										'shipping_admin_url'			=> admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
										'check_results_button'			=> __( 'Check the result out', 'wc-ss' ),
										'exit_button'					=> __( 'Exit', 'wc-ss' )
									) 
								);
				wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
				wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array( 'dashicons', 'install' ), WC_VERSION );
			}
		}

		/**
		 * Include WSS_Data_Migration class
		 */
		public function include_dependencies(){

			if ( ! class_exists( 'WSS_Data_Migration' ) ) {
				include_once WC_SS_DIR .'/includes/class-wss-data-migration.php';
			}
		}

		/**
		 * Show the wizard page.
		 */
		public function show_wizard_page() {

			// Setup/welcome
			if ( ! empty( $_GET['page'] ) && 'wss-setup' == $_GET['page'] ) {

				ob_start();
				include_once 'html-setup-wizard.php';
				delete_transient( 'wss_activation_redirect' );
				exit;
			}
		}

		/**
		 *  Start the migration process by AJAX
		 */
		public function start_migration_process(){

			check_ajax_referer( 'start_migration_wizard', 'security' );
			set_transient( 'wss_create_backup', $_POST[ 'create_backup' ], 5 * MINUTE_IN_SECONDS );
			new WSS_Data_Migration();
			wp_die();
		}
	}
}

new WSS_Setup_Wizard();