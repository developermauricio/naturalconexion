<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * PixelYourSite Core class.
 */
final class PYS extends Settings implements Plugin {
	
	private static $_instance;

	/** @var $eventsManager EventsManager */
	private $eventsManager;
	
    /** @var $registeredPixels array Registered pixels */
    private $registeredPixels = array();
	
    /** @var $registeredPlugins array Registered plugins */
    private $registeredPlugins = array();

    private $adminPagesSlugs = array();

    /**
     * @var PYS_Logger
     */
    private $logger;
	
	public static function instance() {
		
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
		
	}
	
	public function getPluginName() {
		return PYS_PLUGIN_NAME;
	}
	
	public function getPluginFile() {
		return PYS_PLUGIN_FILE;
	}
	
	public function getPluginVersion() {
		return PYS_VERSION;
	}

    public function __construct() {
	
	    add_filter( 'plugin_row_meta', array( $this, 'pluginRowMeta' ), 10, 2 );

	    // initialize settings
	    parent::__construct( 'core' );

	    add_action( 'admin_init', array( $this, 'updatePlugin' ), 0 );
	    add_action( 'admin_init', 'PixelYourSite\manageAdminPermissions' );
	
	    /**
	     * Priority 9 used because on some events, like EDD's CompleteRegistration, are fired on 'init' action
	     * with default (10) priority and PYS should be initialized before it.
	     *
	     * 3rd party extensions, like Pinterest addon, should be loaded with lower priority.
	     */
        add_action( 'init', array( $this, 'init' ), 9 );
        add_action( 'init', array( $this, 'afterInit' ), 11 );

        add_action( 'admin_menu', array( $this, 'adminMenu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ) );
        add_action( 'admin_notices', 'PixelYourSite\adminRenderNotices' );
        add_action( 'admin_init', array( $this, 'adminProcessRequest' ), 11 );

        // run Events Manager
        add_action( 'template_redirect', array( $this, 'managePixels' ) );

	    // track user registrations
	    add_action( 'user_register', array( $this, 'userRegisterHandler' ) );

	    // "admin_permission" option custom sanitization function
	    add_filter( 'pys_core_settings_sanitize_admin_permissions_field', function( $value ) {

	    	// "administrator" always should be allowed
	    	if ( ! is_array( $value ) || ! in_array( 'administrator', $value ) ) {
	    		$value[] = 'administrator';
		    }

		    manageAdminPermissions();

	    	return $this->sanitize_multi_select_field( $value );

	    } );

	    // redirect template for generate file
        add_action( 'admin_init', array($this,'generate_event_export_json') );
        add_action( 'wp_ajax_pys_import_events', array( $this, 'import_custom_events' ) );

	    add_action( 'wp_ajax_pys_get_gdpr_filters_values', array( $this, 'ajaxGetGdprFiltersValues' ) );
	    add_action( 'wp_ajax_nopriv_pys_get_gdpr_filters_values', array( $this, 'ajaxGetGdprFiltersValues' ) );

        /*
         * Restore settings after COG plugin
         * */
        add_action( 'deactivate_pixel-cost-of-goods/pixel-cost-of-goods.php',array($this,"restoreSettingsAfterCog"));

        /*
         * Create facebook category pixel id field for woo
         * */
        add_action('product_cat_add_form_fields', array($this,'add_product_category_fb_pixel_field'));
        add_action('product_cat_edit_form_fields', array($this,'add_product_category_fb_pixel_field'));

        add_action('edited_product_cat', array($this,'save_product_category_fb_woo_pixel_field'));
        add_action('create_product_cat', array($this,'save_product_category_fb_woo_pixel_field'));

        /*
         * For EDD
         * */
        add_action('download_category_add_form_fields', array($this,'add_product_category_fb_edd_pixel_field'));
        add_action('download_category_edit_form_fields', array($this,'add_product_category_fb_edd_pixel_field'));

        add_action('edited_download_category', array($this,'save_product_category_fb_edd_pixel_field'));
        add_action('create_download_category', array($this,'save_product_category_fb_edd_pixel_field'));

        $this->logger = new PYS_Logger();
    }

    public function init() {
	
	    register_post_type( 'pys_event', array(
		    'public' => false,
		    'supports' => array( 'title' )
	    ) );
	
	    // initialize options
	    $this->locateOptions(
		    PYS_PATH . '/includes/options_fields.json',
		    PYS_PATH . '/includes/options_defaults.json'
	    );
	    
	    // register pixels and plugins (add-ons)
	    do_action( 'pys_register_pixels', $this );
	    do_action( 'pys_register_plugins', $this );
	    
        // load dummy Pinterest plugin for admin UI
	    if ( ! array_key_exists( 'pinterest', $this->registeredPlugins ) ) {
		    /** @noinspection PhpIncludeInspection */
		    require_once PYS_PATH . '/modules/pinterest/pinterest.php';
	    }

        // load dummy Bing plugin for admin UI
        if ( ! array_key_exists( 'bing', $this->registeredPlugins ) ) {
            /** @noinspection PhpIncludeInspection */
            require_once PYS_PATH . '/modules/bing/bing.php';
        }
	    
        // maybe disable Facebook for WooCommerce pixel output
	    if ( isWooCommerceActive() && $this->getOption( 'woo_enabled' )
	         && array_key_exists( 'facebook', $this->registeredPixels ) && Facebook()->configured() ) {
		    add_filter( 'facebook_for_woocommerce_integration_pixel_enabled', '__return_false' );
	    }

        EnrichOrder()->init();
	    $this->logger->init();
        AjaxHookEventManager::instance()->addHooks();
    }
	
	/**
	 * Extend options after post types are registered
	 */
    public function afterInit() {
	
	    // add available public custom post types to settings
	    foreach ( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) as $post_type ) {
		
		    // skip product post type when WC is active
		    if ( isWooCommerceActive() && $post_type->name == 'product' ) {
			    continue;
		    }
		
		    // skip download post type when EDD is active
		    if ( isEddActive() && $post_type->name == 'download' ) {
			    continue;
		    }
		
	    }
	
	    maybeMigrate();
	    
    }

    public function import_custom_events() {
        if(isset($_FILES["import_events_file"])) {
            if($_FILES["import_events_file"]['size'] == 0) {
                wp_send_json_error("File is empty ");
                return;
            }
            if( $_FILES["import_events_file"]['type'] != "application/json") {
                wp_send_json_error("File has wrong format ".$_FILES["import_events_file"]['type']);
                return;
            }
            $content = file_get_contents($_FILES["import_events_file"]['tmp_name']);
            $data = json_decode($content,true);

            if(!isset($data['events'])) {
                wp_send_json_error("Events not found");
                return;
            }

            // replace new site url
            $oldSiteUrl = str_replace("/","\/",$data["site_url"]);
            $siteUrl = str_replace("/","\/",site_url());
            $content = str_replace($oldSiteUrl,$siteUrl,$content);

            $data = json_decode(  $content,true);

            // create custom events
            foreach ($data['events'] as $event) {
                CustomEventFactory::import($event);
            }
            wp_send_json_success("OK");
        } else {
            wp_send_json_error("File not found");
        }
    }

    public function generate_event_export_json() {
        if(isset($_GET['tab']) && $_GET['tab'] == "events"  &&
            isset($_GET['action']) && $_GET['action'] == 'export' ) {
            include "views/html-main-events-export.php";
            die();
        }
    }
	
	/**
	 * @param Pixel|Settings $pixel
	 */
    public function registerPixel( &$pixel ) {
	    $this->registeredPixels[ $pixel->getSlug() ] = $pixel;
    }
	
	/**
	 * Return array of registered pixels
	 *
	 * @return Pixel[]
	 */
	public function getRegisteredPixels() {
		return $this->registeredPixels;
	}
	
	/**
	 * @param Pixel|Settings $plugin
	 */
	public function registerPlugin( &$plugin ) {
		$this->registeredPlugins[ $plugin->getSlug() ] = $plugin;
	}
	
	/**
	 * Return array of registered plugins
	 *
	 * @return array
	 */
    public function getRegisteredPlugins() {
	    return $this->registeredPlugins;
    }

	/**
	 * Front-end entry point
	 */
    public function managePixels() {

        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        // disable Events Manager on Customizer and preview mode
        if (is_admin() || is_customize_preview() || is_preview()) {
            return;
        }

        // disable Events Manager on Elementor editor
        if (did_action('elementor/preview/init') || did_action('elementor/editor/init')) {
            return;
        }

        // Disable Events Manager on Divi Builder
        if (function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled()) {
            return;
        }

    	// output debug info
	    add_action( 'wp_head', function() {
		    echo "<script type='application/javascript'>console.log('PixelYourSite PRO version " . PYS_VERSION . "');</script>\r\n";
	    }, 1 );

	    if ( isDisabledForCurrentRole() ) {
	    	return;
	    }

	    // at least one pixel should be configured
	    if ( ! Facebook()->configured() && ! GA()->configured() && ! Ads()->configured()
            && ! Pinterest()->configured() && ! Bing()->configured() ) {

		    add_action( 'wp_head', function() {
			    echo "<script type='application/javascript'>console.warn('PixelYourSite PRO: no pixel configured.');</script>\r\n";
		    } );

	    	return;

	    }

	    // setup events
	    $this->eventsManager = new EventsManager();

    }
    
    public function ajaxGetGdprFiltersValues() {

	    wp_send_json_success( array(
		    'all_disabled_by_api'       => apply_filters( 'pys_disable_by_gdpr', false ),
		    'facebook_disabled_by_api'  => apply_filters( 'pys_disable_facebook_by_gdpr', false ),
            'tiktok_disabled_by_api'  => apply_filters( 'pys_disable_tiktok_by_gdpr', false ),
		    'analytics_disabled_by_api' => apply_filters( 'pys_disable_analytics_by_gdpr', false ),
            'google_ads_disabled_by_api' => apply_filters( 'pys_disable_google_ads_by_gdpr', false ),
		    'pinterest_disabled_by_api' => apply_filters( 'pys_disable_pinterest_by_gdpr', false ),
            'bing_disabled_by_api' => apply_filters( 'pys_disable_bing_by_gdpr', false ),
	    ) );
    
    }
	
	public function userRegisterHandler( $user_id ) {
		
		if ( PYS()->getOption( 'signal_user_signup_enabled' ) || PYS()->getOption( 'woo_complete_registration_enabled' )) {
			update_user_meta( $user_id, 'pys_complete_registration', true );
		}
		
	}
	
	public function getEventsManager() {
		return $this->eventsManager;
	}
	
    public function adminMenu() {
        global $submenu;
	    
        add_menu_page( 'PixelYourSite', 'PixelYourSite', 'manage_pys', 'pixelyoursite',
            array( $this, 'adminPageMain' ), PYS_URL . '/dist/images/favicon.png' );

        add_submenu_page( 'pixelyoursite', 'Licenses', 'Licenses',
            'manage_pys', 'pixelyoursite_licenses', array( $this, 'adminPageLicenses' ) );

        add_submenu_page( 'pixelyoursite', 'System Report', 'System Report',
            'manage_pys', 'pixelyoursite_report', array( $this, 'adminPageReport' ) );

        // core admin pages
        $this->adminPagesSlugs = array(
            'pixelyoursite',
            'pixelyoursite_licenses',
            'pixelyoursite_report',
        );

        // rename first submenu item
        if ( isset( $submenu['pixelyoursite'] ) ) {
            $submenu['pixelyoursite'][0][0] = 'Dashboard';
        }
	
	    $this->adminSaveSettings();
     
    }

    public function add_product_category_fb_pixel_field($term) {

        if(!Facebook()->enabled()) return;

        if(is_a($term,"WP_Term") ) { // edit category view

            $term_id = $term->term_id;
            $categoryIds = Facebook()->getOption("category_pixel_ids");
            $severIds = Facebook()->getOption("category_pixel_server_ids");
            $testCodes = Facebook()->getOption("category_pixel_server_test_code");
            ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="pys_fb_pixel_id">Facebook Category Pixel ID:</label></th>
                <td>
                    <input type="text" name="pys_fb_pixel_id" id="pys_fb_pixel_id"
                           value="<?php echo isset($categoryIds[$term_id]) ? $categoryIds[$term_id] : ''; ?>">
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="pys_fb_pixel_server_id">Facebook Server Access Token:</label></th>
                <td>
                    <textarea  name="pys_fb_pixel_server_id" id="pys_fb_pixel_server_id"><?php echo isset($severIds[$term_id]) ? $severIds[$term_id] : ''; ?></textarea>
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="pys_fb_pixel_server_test_code">Facebook test_event_code:</label></th>
                <td>
                    <input type="text" name="pys_fb_pixel_server_test_code" id="pys_fb_pixel_server_test_code"
                           value="<?php echo isset($testCodes[$term_id]) ? $testCodes[$term_id] : ''; ?>">
                    <p class="description"></p>
                </td>
            </tr>
            <?php
        } else { // new category view
            ?>
            <div class="form-field">
                <label for="pys_fb_pixel_id">Facebook Category Pixel ID:</label>
                <input type="text" name="pys_fb_pixel_id" id="pys_fb_pixel_id">
                <p class="description"></p>
            </div>

            <div class="form-field">
                <label for="pys_fb_pixel_server_id">Facebook Server Access Token:</label>
                <textarea name="pys_fb_pixel_server_id" id="pys_fb_pixel_server_id"></textarea>
                <p class="description"></p>
            </div>

            <div class="form-field">
                <label for="pys_fb_pixel_server_test_code">Facebook test_event_code:</label>
                <input type="text" name="pys_fb_pixel_server_test_code" id="pys_fb_pixel_server_test_code">
                <p class="description"></p>
            </div>
            <?php
        }
    }

    public function save_product_category_fb_woo_pixel_field($term_id) {
        $id = filter_input(INPUT_POST, 'pys_fb_pixel_id');
        $serverId = filter_input(INPUT_POST, 'pys_fb_pixel_server_id');
        $testCode = filter_input(INPUT_POST, 'pys_fb_pixel_server_test_code');

        // save pixel Id
        $categoryIds = (array)Facebook()->getOption("category_pixel_ids");
        if($id) {
            $categoryIds[$term_id] = $id;
        } else {
            if(isset($categoryIds[$term_id]))
                unset($categoryIds[$term_id]);
        }
        Facebook()->updateOptions(array("category_pixel_ids" => $categoryIds));

        // Save server token
        $categoryServerIds = (array)Facebook()->getOption("category_pixel_server_ids");
        if($serverId) {
            $categoryServerIds[$term_id] = $serverId;
        } else {
            if(isset($categoryServerIds[$term_id]))
                unset($categoryServerIds[$term_id]);
        }
        Facebook()->updateOptions(array("category_pixel_server_ids" => $categoryServerIds));

        //Save server test code
        $categoryServerTestCode = (array)Facebook()->getOption("category_pixel_server_test_code");
        if($testCode) {
            $categoryServerTestCode[$term_id] = $testCode;
        } else {
            if(isset($categoryServerTestCode[$term_id]))
                unset($categoryServerTestCode[$term_id]);
        }
        Facebook()->updateOptions(array("category_pixel_server_test_code" => $categoryServerTestCode));
    }

    public function add_product_category_fb_edd_pixel_field($term) {

        if(!Facebook()->enabled()) return;

        if(is_a($term,"WP_Term") ) { // edit category view

            $term_id = $term->term_id;
            $categoryIds = Facebook()->getOption("edd_category_pixel_ids");
            $severIds = Facebook()->getOption("edd_category_pixel_server_ids");
            $testCodes = Facebook()->getOption("edd_category_pixel_server_test_code");
            ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="pys_fb_pixel_id">Facebook Category Pixel ID:</label></th>
                <td>
                    <input type="text" name="pys_fb_pixel_id" id="pys_fb_pixel_id"
                           value="<?php echo isset($categoryIds[$term_id]) ? $categoryIds[$term_id] : ''; ?>">
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="pys_fb_pixel_server_id">Facebook Server Access Token:</label></th>
                <td>
                    <textarea  name="pys_fb_pixel_server_id" id="pys_fb_pixel_server_id"><?php echo isset($severIds[$term_id]) ? $severIds[$term_id] : ''; ?></textarea>
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="pys_fb_pixel_server_test_code">Facebook test_event_code:</label></th>
                <td>
                    <input type="text" name="pys_fb_pixel_server_test_code" id="pys_fb_pixel_server_test_code"
                           value="<?php echo isset($testCodes[$term_id]) ? $testCodes[$term_id] : ''; ?>">
                    <p class="description"></p>
                </td>
            </tr>
            <?php
        } else { // new category view
            ?>
            <div class="form-field">
                <label for="pys_fb_pixel_id">Facebook Category Pixel ID:</label>
                <input type="text" name="pys_fb_pixel_id" id="pys_fb_pixel_id">
                <p class="description"></p>
            </div>

            <div class="form-field">
                <label for="pys_fb_pixel_server_id">Facebook Server Access Token:</label>
                <textarea name="pys_fb_pixel_server_id" id="pys_fb_pixel_server_id"></textarea>
                <p class="description"></p>
            </div>

            <div class="form-field">
                <label for="pys_fb_pixel_server_test_code">Facebook test_event_code:</label>
                <input type="text" name="pys_fb_pixel_server_test_code" id="pys_fb_pixel_server_test_code">
                <p class="description"></p>
            </div>
            <?php
        }
    }

    public function save_product_category_fb_edd_pixel_field($term_id) {
        $id = filter_input(INPUT_POST, 'pys_fb_pixel_id');
        $serverId = filter_input(INPUT_POST, 'pys_fb_pixel_server_id');
        $testCode = filter_input(INPUT_POST, 'pys_fb_pixel_server_test_code');

        // save pixel Id
        $categoryIds = (array)Facebook()->getOption("edd_category_pixel_ids");
        if($id) {
            $categoryIds[$term_id] = $id;
        } else {
            if(isset($categoryIds[$term_id]))
                unset($categoryIds[$term_id]);
        }
        Facebook()->updateOptions(array("edd_category_pixel_ids" => $categoryIds));

        // Save server token
        $categoryServerIds = (array)Facebook()->getOption("edd_category_pixel_server_ids");
        if($serverId) {
            $categoryServerIds[$term_id] = $serverId;
        } else {
            if(isset($categoryServerIds[$term_id]))
                unset($categoryServerIds[$term_id]);
        }
        Facebook()->updateOptions(array("edd_category_pixel_server_ids" => $categoryServerIds));

        //Save server test code
        $categoryServerTestCode = (array)Facebook()->getOption("edd_category_pixel_server_test_code");
        if($testCode) {
            $categoryServerTestCode[$term_id] = $testCode;
        } else {
            if(isset($categoryServerTestCode[$term_id]))
                unset($categoryServerTestCode[$term_id]);
        }
        Facebook()->updateOptions(array("edd_category_pixel_server_test_code" => $categoryServerTestCode));
    }

    public function adminEnqueueScripts() {

        if ( in_array( getCurrentAdminPage(), $this->adminPagesSlugs ) ) {
	
	        wp_register_style( 'select2_css', PYS_URL . '/dist/styles/select2.min.css' );
	        wp_register_script( 'select2_js', PYS_URL . '/dist/scripts/select2.min.js',array( 'jquery' ) );

            wp_deregister_script( 'jquery' );
            wp_enqueue_script( 'jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js' );
	
	        wp_enqueue_script( 'popper', PYS_URL . '/dist/scripts/popper.min.js', 'jquery' );
	        wp_enqueue_script( 'bootstrap', PYS_URL . '/dist/scripts/bootstrap.min.js', 'jquery','popper' );
	        
            wp_enqueue_style( 'pys_css', PYS_URL . '/dist/styles/admin.css', array( 'select2_css' ), PYS_VERSION );
            wp_enqueue_script( 'pys_js', PYS_URL . '/dist/scripts/admin.js', array( 'jquery', 'select2_js', 'popper',
                                                                                 'bootstrap' ), PYS_VERSION );

        }

    }

    public function adminPageMain() {
	    
        $this->adminResetSettings();
        $this->adminExportCustomAudiences();

        include 'views/html-wrapper-main.php';

    }

	public function adminPageReport() {
		include 'views/html-report.php';
	}

	public function adminPageLicenses() {
		
    	$this->adminUpdateLicense();
		
		/** @var Plugin|Settings $plugin */
		foreach ( $this->registeredPlugins as $plugin ) {
			if ( $plugin->getSlug() !== 'head_footer' ) {
				$plugin->adminUpdateLicense();
			}
		}

		include 'views/html-licenses.php';

	}
	
	public function adminProcessRequest() {
        $this->adminCheckLicense();
        $this->adminUpdateCustomEvents();
        $this->adminEnableGdprAjax();
    }
	
	private function adminCheckLicense() {
    	
    	$is_dashboard = isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pixelyoursite';
		$license_status = $this->getOption( 'license_status' );
		
		// redirect to license page in case if license was never activated
		if ( $is_dashboard && empty( $license_status ) ) {
			wp_safe_redirect( buildAdminUrl( 'pixelyoursite_licenses' ) );
			exit;
		}
		
	}
	
	public function adminUpdateLicense() {

		if ( ! $this->adminSecurityCheck() ) {
			return;
		}

		updateLicense( $this );

	}

	public function updatePlugin() {
        
        foreach ( $this->registeredPlugins as $slug => $plugin ) {
            
            if ( $slug == 'head_footer' ) {
                continue;
            }
            
            updatePlugin( $plugin );
            
        }
        
		updatePlugin( $this );
  
	}

	public function adminSecurityCheck() {

		// verify user access
		if ( ! current_user_can( 'manage_pys' ) ) {
			return false;
		}

		// nonce filed and PYS data are required request
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_REQUEST['pys'] ) ) {
			return false;
		}

		return true;

	}
	
    private function adminEnableGdprAjax() {
        
        if ( ! $this->adminSecurityCheck() ) {
            return;
        }
    
        if ( isset( $_REQUEST['pys']['enable_gdpr_ajax'] ) ) {
            $this->updateOptions( array(
                'gdpr_ajax_enabled' => true,
                'gdpr_cookie_law_info_integration_enabled' => true,
                'consent_magic_integration_enabled' => true,
            ) );

            add_action( 'admin_notices', 'PixelYourSite\adminGdprAjaxEnabledNotice' );
            purgeCache();
        }
        
    }
    
	private function adminUpdateCustomEvents() {
		
		if ( ! $this->adminSecurityCheck() ) {
			return;
		}
		
		/**
		 * Single Custom Event Actions
		 */
		if ( isset( $_REQUEST['pys']['event'] ) && isset( $_REQUEST['action']) && is_array($_REQUEST['pys']['event'])  ) {
			
			$nonce   = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : null;
			$action  = $_REQUEST['action'];
			if(isset( $_REQUEST['pys']['event']['post_id'] )) {
                $post_id = sanitize_key( $_REQUEST['pys']['event']['post_id']) ;
            } else {
                $post_id =  false;
            }

			
			if ( $action == 'update' && wp_verify_nonce( $nonce, 'pys_update_event' ) ) {

			    $pys_event = $_REQUEST['pys']['event'];

				if ( $post_id ) {
					$event = CustomEventFactory::getById( $post_id );
					$event->update( $pys_event );
				} else {
					CustomEventFactory::create( $pys_event );
				}
				
			} elseif ( $action == 'enable' && $post_id && wp_verify_nonce( $nonce, 'pys_enable_event' ) ) {
				
				$event = CustomEventFactory::getById( $post_id );
				$event->enable();
				
			} elseif ( $action == 'disable' && $post_id && wp_verify_nonce( $nonce, 'pys_disable_event' ) ) {
				
				$event = CustomEventFactory::getById( $post_id );
				$event->disable();
				
			} elseif ( $action == 'remove' && $post_id && wp_verify_nonce( $nonce, 'pys_remove_event' ) ) {
				
				CustomEventFactory::remove( $post_id );
				
			}
			
			purgeCache();
			
			// redirect to events tab
			wp_safe_redirect( buildAdminUrl( 'pixelyoursite', 'events' ) );
			exit;
			
		}
		
		/**
		 * Bulk Custom Events Actions
		 */
		if ( isset( $_REQUEST['pys']['bulk_event_action'], $_REQUEST['pys']['selected_events'] )
		     && isset( $_REQUEST['pys']['bulk_event_action_nonce'] )
		     && wp_verify_nonce( $_REQUEST['pys']['bulk_event_action_nonce'], 'bulk_event_action' )
		     && is_array( $_REQUEST['pys']['selected_events'] ) ) {
			
			foreach ( $_REQUEST['pys']['selected_events'] as $event_id ) {
				
				$event_id = (int) $event_id;
				
				switch ( $_REQUEST['pys']['bulk_event_action'] ) {
					case 'enable':
						$event = CustomEventFactory::getById( $event_id );
						$event->enable();
						break;
					
					case 'disable':
						$event = CustomEventFactory::getById( $event_id );
						$event->disable();
						break;
					
					case 'clone':
						CustomEventFactory::makeClone( $event_id );
						break;
					
					case 'delete':
						CustomEventFactory::remove( $event_id );
						break;
				}
				
			}
			
			purgeCache();
			
			// redirect to events tab
			wp_safe_redirect( buildAdminUrl( 'pixelyoursite', 'events' ) );
			exit;
			
		}
		
	}
	
	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 *
	 * @return array
	 */
	public function pluginRowMeta( $links, $file ) {
		
		if ( PYS_PLUGIN_BASENAME === $file ) {
			$links[] = '<a href="https://www.pixelyoursite.com/documentation">Help</a>';
		}
		
		return (array) $links;
  
	}
    
    private function adminSaveSettings() {

    	if ( ! $this->adminSecurityCheck() ) {
    		return;
	    }

        if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'pys_save_settings' ) ) {

            if(isset( $_POST['pys']['core'] ) && is_array($_POST['pys']['core'])) {
                $core_options =  $_POST['pys']['core'];
            } else {
                $core_options =  array();
            }


            $gdpr_ajax_enabled = isset( $core_options['gdpr_ajax_enabled'] )
                ? $core_options['gdpr_ajax_enabled']        // value from form data
                : $this->getOption('gdpr_ajax_enabled');    // previous value

            // allow 3rd party plugins to by-pass option value
            $core_options['gdpr_ajax_enabled'] = apply_filters( 'pys_gdpr_ajax_enabled', $gdpr_ajax_enabled );

	        if (isPixelCogActive() ) {
		        if (isset($core_options['woo_purchase_value_option'])) {
                    $core_options = $this->updateDefaultNoCogOption($core_options,'woo_purchase_value_option','woo_purchase_value_cog');
		        }
		        if (isset($core_options['woo_view_content_value_option'])) {
                    $core_options = $this->updateDefaultNoCogOption($core_options,'woo_view_content_value_option','woo_content_value_cog');
                }
		        if (isset($core_options['woo_add_to_cart_value_option'])) {
                    $core_options = $this->updateDefaultNoCogOption($core_options,'woo_add_to_cart_value_option','woo_add_to_cart_value_cog');
                }
		        if (isset($core_options['woo_initiate_checkout_value_option'])) {
                    $core_options = $this->updateDefaultNoCogOption($core_options,'woo_initiate_checkout_value_option','woo_initiate_checkout_value_cog');
                }
	        }

            // update core options
            $this->updateOptions( $core_options );
        	
        	$objects = array_merge( $this->registeredPixels, $this->registeredPlugins );

        	// update plugins and pixels options
	        foreach ( $objects as $obj ) {
	        	/** @var Plugin|Pixel|Settings $obj */
		        $obj->updateOptions();
	        }
	
	        purgeCache();
	        
        }
	    
    }

    private function updateDefaultNoCogOption($core_options,$optionName,$defaultOptionName) {
        $val = $core_options[$optionName];
        $currentVal = $this->getOption($optionName);
        if($val != 'cog') {
            $core_options[$defaultOptionName] = $val;
        } elseif ( $currentVal != 'cog' ) {
            $core_options[$defaultOptionName] = $currentVal;
        }
        return $core_options;
    }
    
    private function adminResetSettings() {
	
	    if ( ! $this->adminSecurityCheck() ) {
		    return;
	    }
	
	    if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'pys_save_settings' ) && isset( $_REQUEST['pys']['reset_settings'] ) ) {
		    
		    if ( isSuperPackActive() ) {
			
			    $old_options = array(
				    'license_key'     => SuperPack()->getOption( 'license_key' ),
				    'license_status'  => SuperPack()->getOption( 'license_status' ),
				    'license_expires' => SuperPack()->getOption( 'license_expires' ),
			    );
			    
		    	SuperPack()->resetToDefaults();
		    	SuperPack()->updateOptions( $old_options );
			   
		    }
		    
		    if ( isPinterestActive() ) {
			
			    $old_options = array(
				    'license_key'     => Pinterest()->getOption( 'license_key' ),
				    'license_status'  => Pinterest()->getOption( 'license_status' ),
				    'license_expires' => Pinterest()->getOption( 'license_expires' ),
				    'pixel_id'        => Pinterest()->getPixelIDs(),
			    );
			
			    Pinterest()->resetToDefaults();
			    Pinterest()->updateOptions( $old_options );
		    	
		    }
		    
		    // Core
		    $old_options = array(
			    'license_key'     => $this->getOption( 'license_key' ),
			    'license_status'  => $this->getOption( 'license_status' ),
			    'license_expires' => $this->getOption( 'license_expires' ),
		    );
		
		    PYS()->resetToDefaults();
		    PYS()->updateOptions( $old_options );
		
		    // Facebook
		    $old_options = array(
			    'pixel_id' => Facebook()->getPixelIDs(),
		    );
		    
		    Facebook()->resetToDefaults();
		    Facebook()->updateOptions( $old_options );
		    
		    // Google Analytics
		    $old_options = array(
			    'tracking_id' => GA()->getPixelIDs(),
		    );
		
		    GA()->resetToDefaults();
		    GA()->updateOptions( $old_options );
		
		    // Google Analytics
		    $old_options = array(
			    'ads_ids' => Ads()->getPixelIDs(),
                'woo_purchase_conversion_labels' => Ads()->getOption( 'woo_purchase_conversion_labels' ),
                'woo_initiate_checkout_conversion_labels' => Ads()->getOption( 'woo_initiate_checkout_conversion_labels' ),
                'woo_add_to_cart_conversion_labels' => Ads()->getOption( 'woo_add_to_cart_conversion_labels' ),
                'woo_view_content_conversion_labels' => Ads()->getOption( 'woo_view_content_conversion_labels' ),
                'woo_view_category_conversion_labels' => Ads()->getOption( 'woo_view_category_conversion_labels' ),
                'edd_purchase_conversion_labels' => Ads()->getOption( 'edd_purchase_conversion_labels' ),
                'edd_initiate_checkout_conversion_labels' => Ads()->getOption( 'edd_initiate_checkout_conversion_labels' ),
                'edd_add_to_cart_conversion_labels' => Ads()->getOption( 'edd_add_to_cart_conversion_labels' ),
                'edd_view_content_conversion_labels' => Ads()->getOption( 'edd_view_content_conversion_labels' ),
                'edd_view_category_conversion_labels' => Ads()->getOption( 'edd_view_category_conversion_labels' ),
		    );
		    Ads()->resetToDefaults();
            Ads()->updateOptions( $old_options );
		    
		    //HeadFooter()->resetToDefaults();
		    
		    // do redirect
		    wp_safe_redirect( buildAdminUrl( 'pixelyoursite' ) );
		    exit;
		    
	    }
    
    }
    
    private function adminExportCustomAudiences() {
	
	    if ( ! $this->adminSecurityCheck() ) {
		    return;
	    }
	
	    if ( isset( $_REQUEST['pys']['export_custom_audiences'] )
	         && wp_verify_nonce( $_REQUEST['_wpnonce'], 'pys_save_settings' ) ) {
	    	
	    	if ( $_REQUEST['pys']['export_custom_audiences'] == 'woo' && isWooCommerceActive() ) {
			    wooExportCustomAudiences();
		    } elseif ( $_REQUEST['pys']['export_custom_audiences'] == 'edd' ) {
			    eddExportCustomAudiences();
		    }
		    
	    }
    
    }

    public function restoreSettingsAfterCog() {
        $old = Facebook()->getOption("woo_complete_registration_custom_value_old");
        if(!empty($old) ) {
            Facebook()->updateOptions(array(
                "woo_complete_registration_custom_value" => $old,
                'woo_complete_registration_custom_value_old' => ""));
        }
        $params = array();
        $oldPurchase = $this->getOption("woo_purchase_value_cog");
        $oldContent = $this->getOption("woo_content_value_cog");
        $oldAddCart = $this->getOption("woo_add_to_cart_value_cog");
        $oldInitCheckout = $this->getOption("woo_initiate_checkout_value_cog");

        if($this->getOption('woo_purchase_value_option') == 'cog') {
            if(!empty($oldPurchase)) $params['woo_purchase_value_option'] = $oldPurchase;
            else $params['woo_purchase_value_option'] = "price";
        }
        if($this->getOption('woo_view_content_value_option') == 'cog') {
            if(!empty($oldContent)) $params['woo_view_content_value_option'] = $oldContent;
            else $params['woo_view_content_value_option'] = "price";
        }
        if($this->getOption('woo_add_to_cart_value_option') == 'cog') {
            if(!empty($oldAddCart)) $params['woo_add_to_cart_value_option'] = $oldAddCart;
            else $params['woo_add_to_cart_value_option'] = "price";
        }
        if($this->getOption('woo_initiate_checkout_value_option') == 'cog') {
            if(!empty($oldInitCheckout)) $params['woo_initiate_checkout_value_option'] = $oldInitCheckout;
            else $params['woo_initiate_checkout_value_option'] = "price";
        }

        $params['woo_purchase_value_cog'] = '';
        $params['woo_content_value_cog'] = '';
        $params['woo_add_to_cart_value_cog'] = '';
        $params['woo_initiate_checkout_value_cog'] = '';

        $this->updateOptions($params);
    }

    public function getLog() {
	    return $this->logger;
    }

}

/**
 * @return PYS
 */
function PYS() {
    return PYS::instance();
}