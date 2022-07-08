<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
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
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */

class DPLR_Doppler {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		require_once(dirname( __FILE__ ) . '/DopplerAPIClient/DopplerService.php');
		$this->plugin_name = 'Doppler';
		$this->version = DOPPLER_FORM_VERSION ;
		$this->doppler_service = new Doppler_Service();

		$options = get_option('dplr_settings', [
			'dplr_option_apikey' => '',
			'dplr_option_useraccount' => ''
			]);

		 /* Not sure about this block. */
		 try {
		 	//$this->doppler_service->setCredentials(['api_key' => $options['dplr_option_apikey'], 'user_account' => $options['dplr_option_useraccount']]);
		 } catch (Exception $e) {;}

		// inicializar el shortcode aca:
		$this->load_shortcodes();
		$this->load_dependencies();
		$this->set_locale(); 
		$this->define_admin_hooks();
		$this->define_public_hooks(); 
		$this->check_version_update();
	}

	private function load_shortcodes(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-doppler-form-generator.php';
		$this->shortcode = new DPLR_Form_Shortcode();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Doppler_Admin. Defines all hooks for the admin area.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-doppler-form-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-doppler-form-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/doppler-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/doppler-forms-public.php';

		require_once plugin_dir_path( dirname(__FILE__) ) . "includes/models/Form_Model.php";

		require_once plugin_dir_path( dirname(__FILE__) ) . "includes/models/Field_Model.php";

		require_once plugin_dir_path( dirname(__FILE__) ) . "admin/controllers/Form_Controller.php";
		
		require_once plugin_dir_path( dirname(__FILE__) ) . "includes/class-doppler-extension-manager.php";

		$this->loader = new Doppler_Form_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Plugin_Name_i18n();

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
		
		$plugin_admin = new Doppler_Admin( $this->get_plugin_name(), $this->get_version(), $this->doppler_service);
		$extension_manager = new Doppler_Extension_Manager();

		$this->loader->add_action( 'admin_enqueue_scripts', 	$plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', 	$plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', 				$plugin_admin, 'init_settings' );
		$this->loader->add_action( 'admin_menu', 				$plugin_admin, 'init_menu' );
		$this->loader->add_action( 'admin_menu', 				$plugin_admin, 'add_submenu' );
		$this->loader->add_action( 'widgets_init', 				$plugin_admin, 'init_widget' );
		$this->loader->add_action( 'admin_notices', 			$plugin_admin, 'show_admin_notices' );
		$this->loader->add_action( 'wp_ajax_dplr_ajax_connect', $plugin_admin, 'ajax_connect' );
		$this->loader->add_action( 'wp_ajax_dplr_ajax_disconnect', $plugin_admin, 'ajax_disconnect' );
		$this->loader->add_action( 'wp_ajax_dplr_delete_form',  $plugin_admin, 'ajax_delete_form' );
		$this->loader->add_action( 'wp_ajax_dplr_get_lists',	$plugin_admin, 'ajax_get_lists' );
		$this->loader->add_action( 'wp_ajax_dplr_save_list', 	$plugin_admin, 'ajax_save_list' );
		$this->loader->add_action( 'wp_ajax_dplr_delete_list',  $plugin_admin, 'ajax_delete_list' );
		$this->loader->add_action( 'wp_ajax_install_extension', $extension_manager, 'install_extension' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new DPLR_Doppler_Form_Public( $this->get_plugin_name(), $this->get_version(), $this->doppler_service );
		$this->loader->add_action( 'wp_head', $plugin_public, 'add_tracking_script' );
		$this->loader->add_action( 'wp_enqueue_scripts', 		 $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', 		 $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_submit_form', 		 $plugin_public, 'submit_form' );
		$this->loader->add_action( 'wp_ajax_nopriv_submit_form', $plugin_public, 'submit_form' );

	}

	/**
	 * If plugin is upgrading from version 1.0.0, updates database, 
	 * imports current forms, and saves version in db.
	 * 
	 * @since 2.0.0 
	 */
	public function check_version_update(){

		$settings = get_option('dplr_settings');
		$db_version = get_option('dplr_version');

		if( isset($settings['dplr_option_apikey']) && (!$db_version || version_compare($db_version,'2.0.0','<')) ){

			$sidebar_widgets = get_option('sidebars_widgets');
			$actual_widgets = get_option('widget_dplr_subscription_widget');

			$sidebar_widgets_aux = array();

			foreach($sidebar_widgets as $sb=>$wdgts){

				if(is_array($wdgts)){

					foreach($wdgts as $k=>$v){
						
						if(strpos($v,'dplr_subscription_widget')!==false){
							$aux = explode('-',$v);
							$sidebar_widgets[$sb][$k] = 'dplr_form_widget-'.$aux[1];
						}
					}
				}
			}

			update_option('sidebars_widgets',$sidebar_widgets);

			DPLR_Form_Model::init();
			DPLR_Field_Model::init();


			if(!empty($actual_widgets)){
				foreach($actual_widgets as $id=>$v){
					if(is_array($v)){
						
						//Create a form from old widget.
						$data = array('title'=>$v['title'],'description'=>'','list_id'=>$v['selected_lists'][0],'name'=>$v['title']);
						$form_id = DPLR_Form_Model::insert($data); 
						//Save email field
						$field_id = DPLR_Field_Model::insert(array('name'=>'EMAIL','form_id'=>$form_id,'type'=>'email','sort_order'=>1));
						//Email will be required by defalut
						DPLR_Field_Model::setSettings($field_id, array('required'=>'required', 'placeholder'=>'', 'description'=>''));
						//Create new widgets from old widgets
						$new_widget[$id] = array('form_id'=>$form_id);
						update_option('widget_dplr_form_widget', $new_widget);
					}
				}
			}

			delete_option('widget_dplr_subscription_widget');
			update_option('dplr_version','2.0.0');
			update_option('dplr_2_0_updated',1);

		}else{

			update_option('dplr_version', $this->get_version() );
		
		}
	
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
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
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
