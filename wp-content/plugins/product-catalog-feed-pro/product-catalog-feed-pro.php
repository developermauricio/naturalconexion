<?php
/*
Plugin Name: Product Catalog Feed Pro by PixelYourSite
Description: WooCommerce Products Feed for Facebook Product Catalog. You can create XML feeds for Facebook Dynamic Product Ads.
Plugin URI: https://www.pixelyoursite.com/product-catalog-facebook
Author: PixelYourSite
Author URI: https://www.pixelyoursite.com
Version: 5.1.2
WC requires at least: 3.0.0
WC tested up to: 4.2
*/
/* Following are used for updating plugin */

if( !is_admin() && !wp_doing_cron () ){
	return;
}
//Plugin Version
define( 'WPWOOF_VERSION', '5.1.2');
//NOTIFICATION VERSION
define( 'WPWOOF_VERSION_NOTICE', '0.0.0');

//Plugin Update URL
define( 'WPWOOF_SL_STORE_URL', '' );
//Plugin Name
define( 'WPWOOF_SL_ITEM_NAME', 'Product Catalog Feed for WooCommerce' );
define( 'WPWOOF_SL_ITEM_SHNAME', 'Product Catalog' );

//Plugin Base
define( 'WPWOOF_BASE', plugin_basename( __FILE__ ) );
//Plugin PAtH
define( 'WPWOOF_PATH', plugin_dir_path( __FILE__ ) );
//Plugin URL
define( 'WPWOOF_URL', plugin_dir_url( __FILE__ ) );
//Plugin assets URL
define( 'WPWOOF_ASSETS_URL', WPWOOF_URL . 'assets/' );
//Plugin
define( 'WPWOOF_PLUGIN', 'wp-woocommerce-feed');

//Plugin
define( 'WPWOOF_PCFP', 'product-catalog-feed-pro/product-catalog-feed-pro.php');
define( 'WPWOOF_WOO',  'woocommerce/woocommerce.php');
define( 'WPWOOF_YSEO', 'wordpress-seo/wp-seo.php');
define( 'WPWOOF_SMART_OGR', 'smart-opengraph/catalog-plugin.php');
//Brands plugins
// woocommerce brands */
define( 'WPWOOF_BRAND_YWBA',    'yith-woocommerce-brands-add-on/init.php');
define( 'WPWOOF_BRAND_PEWB',    'perfect-woocommerce-brands/main.php');
define( 'WPWOOF_BRAND_PRWB',    'premmerce-woocommerce-brands/premmerce-brands.php');
define( 'WPWOOF_BRAND_PBFW',    'product-brands-for-woocommerce/product-brands-for-woocommerce.php');
define('WPWOOF_MULTI_CRRNC',    'woo-multi-currency/woo-multi-currency.php');
define('WPWOOF_CURRN_SWTCH',    'currency-switcher-woocommerce/currency-switcher-woocommerce.php');
define('WPWOOF_CURRN_SWTPR',    'currency-switcher-woocommerce-pro/currency-switcher-woocommerce-pro.php');
define('WPWOOF_WCPBC',          'woocommerce-product-price-based-on-countries/woocommerce-product-price-based-on-countries.php');
define('WPWOOF_ALLIMPP',        'wp-all-import-pro/wp-all-import-pro.php');
define('WPWOOF_ALLIMP',         'wp-all-import/plugin.php');


//Plugin

require_once('inc/helpers.php');
require_once('inc/generate-feed.php');
require_once('inc/admin.php');
require_once('inc/feed-list-table.php');
require_once('inc/admin_notices.php' );
require_once('inc/tools.php');

if(isset($_GET['WPWOOF_DEBUG']))  update_option('WPWOOF_DEBUG', boolval ($_GET['WPWOOF_DEBUG']));
define( 'WPWOOF_DEBUG', get_option('WPWOOF_DEBUG') );

if( WPWOOF_DEBUG ){
    if (!function_exists('trace')) {
        function trace ($obj,$onexit=0){
            echo "<pre>".print_r($obj,true)."</pre>";
            if($onexit) exit();
        }
    }
    function wpwoofStoreDebug($file,$data){
       trace(date('Y-m-d H:i:s')."\t".print_r($data,true)."\n");
       file_put_contents($file,date('Y-m-d H:i:s')."\t".print_r($data,true)."\n",FILE_APPEND);
    }

}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

class wpwoof_product_catalog
{
     static $interval = '86400';
     static $oTools = null;
     static $schedule = array(
        '0' => 'never',
        '3600' => 'hourly',
        '43200' => 'twicedaily',
        '86400' => 'daily',
        '604800' => 'weekly'
    );
     static $aSMartTags  = array("recent-product"/*,"top-7-days"*/,"top-30-days","on-sale" ); /* smart tags */
     static $field_names = array(
        'wpfoof-exclude-product' => array(
            "title" => 'Exclude this product from feeds',
            // "subscription"=>'Exclude the product from feed',
            //"main" => true,
            "type" => 'trigger',
        ),
        'feed_google_category' => array(
            'title' => 'Google Taxonomy:',
            'type' => 'googleTaxonomy',
            "toImport" => 'text'
        ),
        'wpfoof-mpn-name' => array(
            "title" => 'MPN:',
            // "subscription"=>'Manufacturer part number',
            "type" => 'text',
            "toImport" => 'text'
        ),
        'wpfoof-gtin-name' => array(
            "title" => 'GTIN:',
            // "subscription"=>'Global Trade Item Number(GTINs may be 8, 12, 13 or 14 digits long)',
            "type" => 'text',
            "toImport" => 'text'
        ),
        'wpfoof-brand' => array(
            "title" => 'Brand:',
            "type" => 'text',
        ),
         'wpfoof-identifier_exists' => array(
             'title' => 'identifier_exists:',
             'type' => 'select',
             'options' => array(
                 'true' => 'select',
                 'yes' => 'Yes',
                 'output'=> 'No'
             )
         ),
        'wpfoof-condition' => array(
            'title' => 'Condition:',
            'type' => 'select',
            'topHr' => true,
            'options' => array(
                '' => 'Select',
                'new' => 'new',
                'refurbished' => 'refurbished',
                'used' => 'used'
            ),
            "toImport" => 'radio'
        ),
         'wpfoof-custom-title' => array(
             "title" => 'Custom Title:',
             "type" => 'text',
             'topHr' => true,
             "toImport" => 'text'
         ),
         'wpfoof-custom-descr' => array(
             "title" => 'Custom Description:',
             "type" => 'textarea',
             "toImport" => 'textarea'
         ),
        'wpfoof-custom-url' => array(
            "title" => 'Custom URL:',
            "type" => 'text',
            "toImport" => 'text'
        ),
        'wpfoof-carusel-box-media-name' => array(
            "title" => 'Carousel ad:',
            // "subscription"=>'(1080X1080 recommended)',
            "size" => "1080X1080",
            "type" => "",
            'topHr' => true
        ),
        'wpfoof-box-media-name' => array(
            "title" => 'Single product ad:',
            // "subscription"=>'(1200X628 recommended)',
            "size" => "1200X628",
            "type" => ""

        ),
//        'wpfoof-google' => array(
//            "title" => 'Extra Custom Fields',
//            "type" => 'trigger',
//            'topHr' => true,
//            'show' => 'google',
//            "toImport" => 'trigger'
//        ),
//        'wpfoof-adsensecustom' => array(
//            "title" => 'Extra Custom Fields for Google Ads Custom Feed',
//            "type" => 'trigger',
//            'topHr' => true,
//            'show' => 'adsensecustom',
//        )
    );
     static  $WWC;
     static $category_field_names = array(

        'wpfoof-exclude-category' => array(
            'title' => 'Exclude this category from feeds',
            'type' => 'toggle'
        ),
         'wpfoof-identifier_exists' => array(
             'title' => 'identifier_exists:',
             'type' => 'select',
             'options' => array(
                 'true' => '',
                 'yes' => 'Yes',
                 'output'=> 'No'
             )
         ),
        'feed_google_category' => array(
            'title' => 'Google Taxonomy:',
            'type' => 'googleTaxonomy'
        ),
        'wpfoof-adult' => array(
            'title' => 'Adult:',
            'type' => 'select',
            'options' => array(
                'no' => 'No',
                'yes' => 'Yes'
            )
        ),
        'wpfoof-shipping-label' => array(
            'title' => 'shipping_label:',
            'type' => 'text'
        ),
        'wpfoof-tax-category' => array(
            'title' => 'tax_category:',
            'type' => 'text'
        )
    );

    static $tag_field_names = array(

        'wpfoof-exclude-category' => array(
            'title' => 'Exclude products with this tag from feeds',
            'type' => 'toggle'
        ),

    );


    function __construct()
    {
        /*if( ! empty( $_GET['pcbpys_license_deactivate'] ) ) {
            $_POST['pcbpys_license_deactivate'] = true;
        }*/
        global $xml_has_some_error, $woocommerce_wpwoof_common;


        self::$WWC =  $woocommerce_wpwoof_common;
        $xml_has_some_error = false;
        self::$oTools = new wpWoofTools();
        register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
        register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));

        add_action('init', array(__CLASS__, 'init'),90);
        add_action('admin_init', array(__CLASS__, 'admin_init'),90);
        //



        // extra fields on category form
        add_action('product_cat_edit_form_fields', array(__CLASS__, 'edit_extra_fields_category'), 20, 2);
        add_action('product_cat_add_form_fields', array(__CLASS__, 'add_extra_fields_category'), 20, 2);

        add_action('edited_product_cat', array(__CLASS__, 'save_extra_fields_category'), 10, 2);
        add_action('create_product_cat', array(__CLASS__, 'save_extra_fields_category'), 10, 2);

        add_action('product_tag_edit_form_fields', array(__CLASS__, 'edit_extra_fields_tag'), 20, 2);
        add_action('product_tag_add_form_fields', array(__CLASS__, 'add_extra_fields_tag'), 20, 2);

        add_action('edited_product_tag', array(__CLASS__, 'save_extra_fields_category'), 10, 2);
        add_action('create_product_tag', array(__CLASS__, 'save_extra_fields_category'), 10, 2);





        // extra fields on product form
        //'woocommerce_product_options_general_product_data'
        add_filter( 'woocommerce_product_data_tabs',array(__CLASS__, 'woo_woof_product_tab'), 99, 1 );
        //add_action('woocommerce_product_options_woof_tab_product_data', array(__CLASS__, 'add_extra_fields'), 10);



        add_action('woocommerce_product_after_variable_attributes', array(__CLASS__, 'add_extra_fields_variable'), 10, 3);
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'save_extra_fields'), 10, 2);
        add_action('woocommerce_save_product_variation', array(__CLASS__, 'save_extra_fields'), 10, 2);


        add_action('admin_menu', array(__CLASS__, 'admin_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));

        add_filter('cron_schedules', array(__CLASS__, 'cron_schedules'));
        add_action('wpwoof_feed_update', array(__CLASS__, 'wpwoof_feed_update'));
        add_action('wpwoof_generate_feed', array(__CLASS__, 'do_this_generate'), 10, 3);
        //////////////////////////////////////


        add_filter('http_request_host_is_external', array(__CLASS__, 'http_request_host_is_external'), 10, 3);
        if (!class_exists('WPWOOF_Plugin_Updater')) {
            include plugin_dir_path(__FILE__) . 'inc/plugin-updater.php';
        }
        $license_status =  get_option( 'pcbpys_license_status' );
        if($license_status == 'valid'  && function_exists('is_plugin_active') && (is_plugin_active(WPWOOF_ALLIMPP) || is_plugin_active(WPWOOF_ALLIMP))) {
            require_once('inc/import-addon.php');
        }
    }


    static function  woo_woof_product_tab( $default_tabs ) {
        $default_tabs['woof_tab'] = array(
            'label'    =>  __( WPWOOF_SL_ITEM_SHNAME, 'feedpro' ),
            'target'   =>  'woof_add_extra_fields',
            'priority' =>  90,
            //'class'    =>  array('panel', 'woocommerce_options_panel')
        );
        add_action( 'woocommerce_product_data_panels', array(__CLASS__, 'woof_add_extra_fields') );
        return $default_tabs;
    }



    static function init() {
        self::$interval = self::$WWC->getInterval(); //get_option('wpwoof_schedule', '0');
        $is_xml = (isset($_GET['wpwoofeedxmldownload']) && wp_verify_nonce($_GET['wpwoofeedxmldownload'], 'wpwoof_download_nonce'));
        $is_csv = (isset($_GET['wpwoofeedcsvdownload']) && wp_verify_nonce($_GET['wpwoofeedcsvdownload'], 'wpwoof_download_nonce'));
        if ($is_xml || $is_csv) {

            $option_id = $_GET['feed'];
            $data = wpwoof_get_feed($option_id);
            $data = unserialize($data);
            $data['edit_feed'] = $option_id;
            $feedname = $data['feed_name'];
            $upload_dir = wpwoof_feed_dir($feedname, ($is_xml ? 'xml' : 'csv'));
            $file = $upload_dir['path'];
            $path = $upload_dir['path'];
            $fileurl = $upload_dir['url'];
            $file_name = $upload_dir['file'];
            //trace($upload_dir,1);
            if(file_exists($path)){
                self::downloadFile($file, $file_name, $is_csv);
            }
            /*
            if ($is_csv && empty($_GET['dwnl'])){
                @unlink($path);
            }
            else self::downloadFile($file, $file_name, $is_csv);
            */
            $dir_path = str_replace($file_name, '', $path);
            $create_csv = false;
//            if (wpwoof_checkDir($dir_path)) {
//                $create_csv = wpwoofeed_generate_feed($data, $is_xml ? 'xml' : 'csv');
//            }
            return;
        }
    }
    static function downloadFile($file, $file_name, $is_csv = false)
    {
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            if (!$is_csv) {
                header('Content-Type: text/xml');
            } else {
                header('Content-Type: application/octet-stream');
            }
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            //if ($is_csv) @unlink($file);
            exit;
        } else if (!$is_csv) {
            wp_die('Error: File not found', (!$is_csv ? 'XML Download' : 'CSV Download'));
            exit;
        }
    }
    static function feed_dir($feedname, $file_type = 'xml')
    {
        $feedname = str_replace(' ', '-', $feedname);
        $feedname = strtolower($feedname);
        $upload_dir = wp_upload_dir();
        $base = $upload_dir['basedir'];
        $baseurl = $upload_dir['baseurl'];
        $feedService = 'facebook';
        $path = "{$base}/wpwoof-feed/{$feedService}/{$file_type}";
        $baseurl = $baseurl . "/wpwoof-feed/{$feedService}/{$file_type}";
        $file = "{$path}/{$feedname}.{$file_type}";
        $fileurl = "{$baseurl}/{$feedname}.{$file_type}";

        return array('path' => $file, 'url' => $fileurl, 'file' => $feedname . '.' . $file_type);
    }
    static function set_disable_status()
    {
        if (isset($_POST['set_disable_status'])) {
            if (!empty($_POST['feed_id']) && is_numeric($_POST['feed_id']))
                $value = unserialize(wpwoof_get_feed($_POST['feed_id']));

            if (!empty($value['feed_name'])) {
                $value['noGenAuto'] = empty($_POST['set_disable_status']) ? 0 : 1;
                wpwoof_update_feed($value, $_POST['feed_id'], true);
            }

            header('Content-Type: application/json');
            exit(json_encode(array("status" => "OK")));
        }
        wp_die();
    }
    static function set_wpwoof_image()   {

        if (isset($_POST['wpwoof_image'])) {
            self::$WWC->setGlobalImg($_POST['wpwoof_image']);
            exit("OK");
        }
        wp_die();
    }
    static function set_wpwoof_category(){
        $data=array();
        if (isset($_POST['wpwoof_feed_google_category'])) {
            $data['name'] = $_POST['wpwoof_feed_google_category'];
            self::$WWC->setGlobalGoogleCategory($data);

        }
        exit('OK');
    }
    static function wpwoof_status(){
        global $wpdb;
        $result = array();
        if( isset($_POST['wpwoof_status']) && isset($_POST['feedids']) && !empty($_POST['feedids'])){
            foreach($_POST['feedids'] as $val){
                $val=(int)$val;
                $status = self::$WWC->get_feed_status($val);
                $result[$val] = array();
                $result[$val]['total'] = $status['total_products'];
                $result[$val]['processed'] = $status['parsed_products'];
            }
        }
        header('Content-Type: application/json');
        exit(json_encode($result));
    }
    static function wpwoof_addfeed_submit() {
            $values = $_POST;
            unset($values['wpwoof-addfeed-submit']);
            unset($values['action']);
            $values['added_time'] = time();
            $feed_name = sanitize_text_field($values['feed_name']);
            //trace($values,1);
            if( isset($_POST['edit_feed']) && !empty($_POST['edit_feed']) ){
                if( isset($_POST['old_feed_name']) && !empty($_POST['old_feed_name'])) {
                    $oldfile = trim($_POST['old_feed_name']);
                    $oldfile = strtolower($oldfile);
                    $newfile = trim($_POST['feed_name']);
                    $newfile = strtolower($newfile);
                    if( $newfile != $oldfile ) {
                        wpwoof_delete_feed_file($_POST['edit_feed']);
                        wpwoof_update_feed($values, $_POST['edit_feed'],false,$feed_name);
                    }
                }
                $url = wpwoof_create_feed($values);
                $values['url'] = $url;
                $updated = wpwoof_update_feed($values, $_POST['edit_feed']);
                update_option('wpwoof_message', 'Feed Updated Successully.');
                $wpwoof_message = 'success';
            } else {
                if (update_option('wpwoof_feedlist_' . $feed_name, $values)) {
                    global $wpdb;
                    $sql = "SELECT * FROM $wpdb->options WHERE option_name = 'wpwoof_feedlist_" . esc_sql($feed_name) . "' Limit 1";
                    $result = $wpdb->get_results($sql, 'ARRAY_A');
                    if (count($result) == 1) {
                        $values['edit_feed'] = $result[0]['option_id'];
                        $url = wpwoof_create_feed($values);
                    }
                }
            }
            /* Reload the current page */
            if(isset($wpwoof_message)) wpwoof_refresh($wpwoof_message);

    }
    static function check_feed_name (){
             global $wpdb;
             $feed_name = sanitize_text_field($_POST['check_feed_name']);
             header('Content-Type: application/json');
             if (! get_option('wpwoof_feedlist_' . $feed_name, false) ){
                 exit( json_encode( array("status"=>"OK") ) );
             }
             $aExists =  Array();
             $sql = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'wpwoof_feedlist_" . $feed_name . "%'";
             $res = $wpdb->get_results($sql, 'ARRAY_A');
             foreach($res as $val){
                 $aExists[]=$val['option_name'];
             }
             exit(json_encode($aExists));
     }
    static function set_wpwoof_global_data(){

         $data = array(
                 'extra'               => isset($_POST['extra']) ? $_POST['extra'] : array(),
                 'brand'                => isset($_POST['brand']) ? $_POST['brand'] : array(),
         );
         self::$WWC->setGlobalData($data);
         exit('OK');

     }
    static function set_wpwoof_shedule(){
        if( isset($_POST['wpwoof_schedule']) ){
            $option = $_POST['wpwoof_schedule'];


            if(!empty( self::$schedule[$option])) {
                self::$interval = $option;
                update_option('wpwoof_schedule', self::$interval);


                wp_clear_scheduled_hook('wpwoof_feed_update');
                if ( self::$interval*1>0 ) {
                    wp_schedule_event(time(), self::$schedule[$option], 'wpwoof_feed_update');
                }
            }
            exit('OK');
        }
         wp_die();
     }
    static protected function _is_canRun(){
            global $wp_roles;
            $roles_selected = get_option('wpwoof_permissions_role',array('administrator'));
            foreach ( $wp_roles->roles as $role => $options ) {
                if ( in_array( $role, $roles_selected ) ) {
                    $wp_roles->add_cap( $role, 'manage_feedpro' );
                } else {
                    $wp_roles->remove_cap( $role, 'manage_feedpro' );
                }
            }
            if( is_user_logged_in() ) {
                $user = wp_get_current_user();
                if(is_super_admin($user->ID)) return true;

                $roles = ( array )$user->roles;
                foreach ($roles as $r) {
                    if (in_array($r, $roles_selected))  return true;
                }
            }
            return false;
    }
    static function admin_init()
    {

        // retrieve our license key from the DB
        $license_key = trim(get_option('pcbpys_license_key'));
        // setup the updater
        $edd_updater = new WPWOOF_Plugin_Updater(WPWOOF_SL_STORE_URL, __FILE__, array(
            'version' => WPWOOF_VERSION,      // current version number
            'license' => $license_key,        // license key (used get_option above to retrieve from DB)
            'item_name' => WPWOOF_SL_ITEM_NAME, // name of this plugin
            'author' => 'PixelYourSite'      // author of this plugin
        ));
        global $wpdb, $wpwoof_values, $wpwoof_add_button, $wpwoof_add_tab, $wpwoof_message, $wpwoofeed_oldname;
        $wpwoof_values = array();
        $wpwoof_add_button = 'Save & Generate the Feed';
        $wpwoof_add_tab = 'Add New Feed';
        $wpwoof_message = '';
        $wpwoofeed_oldname = '';


        if ( self::_is_canRun() ) {

            add_action('wp_ajax_set_disable_status', array(__CLASS__, 'set_disable_status'));
            add_action('wp_ajax_set_wpwoof_category', array(__CLASS__, 'set_wpwoof_category'));
            add_action('wp_ajax_set_wpwoof_image', array(__CLASS__, 'set_wpwoof_image'));
            add_action('wp_ajax_set_wpwoof_shedule', array(__CLASS__, 'set_wpwoof_shedule'));
            add_action('wp_ajax_set_wpwoof_global_data', array(__CLASS__, 'set_wpwoof_global_data'));
            add_action('wp_ajax_check_feed_name', array(__CLASS__, 'check_feed_name'));
            add_action('wp_ajax_wpwoof-addfeed-submit', array(__CLASS__, 'wpwoof_addfeed_submit'));
            add_action('wp_ajax_wpwoof_status', array(__CLASS__, 'wpwoof_status'));

            if (!self::$WWC->checkSchedulerStatus()) {
                add_action('admin_notices', array(__CLASS__, 'wpwoof_showSchedulerError'));
            }

            if (!isset($_REQUEST['page']) || $_REQUEST['page'] != 'wpwoof-settings') {
                return;
            }

            $nonce = isset($_REQUEST['_wpnonce']) ? wp_verify_nonce($_REQUEST['_wpnonce'], 'wooffeed-nonce') : false ;


            if ($nonce && isset($_REQUEST['delete']) && !empty($_REQUEST['delete'])) {
                $id = (int)$_REQUEST['delete'];
                $deleted = wpwoof_delete_feed($id);

                if ($deleted) {
                    wp_cache_flush();
                    update_option('wpwoof_message', 'Feed Deleted Successully.');
                    $wpwoof_message = 'success';
                } else {
                    update_option('wpwoof_message', 'Failed To Delete Feed.');
                    $wpwoof_message = 'error';
                }
                /* Reload the current page */
                wpwoof_refresh($wpwoof_message);

            } else if ($nonce && isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])) {
                $option_id = (int)$_REQUEST['edit'];
                $feed = wpwoof_get_feed($option_id);
                $wpwoof_values = unserialize($feed);
                $wpwoof_values['edit_feed'] = $option_id;
                $wpwoofeed_oldname = isset($wpwoof_values['feed_name']) ? $wpwoof_values['feed_name'] : '';
                $wpwoof_add_button = 'Update the Feed';
                $wpwoof_add_tab = 'Edit Feed : ' . $wpwoof_values['feed_name'];
            } else if ( $nonce &&  isset($_REQUEST['update']) && !empty($_REQUEST['update'])) {
                $option_id = (int)$_REQUEST['update'];
                $feed = wpwoof_get_feed($option_id);
                $wpwoof_values = unserialize($feed);

                $wpwoof_values['edit_feed'] = $option_id;
                $wpwoof_values['added_time'] = time();
                $url = wpwoof_create_feed($wpwoof_values);
                $wpwoof_values['url'] = $url;
                $updated = wpwoof_update_feed($wpwoof_values, $option_id);
                $wpwoof_message = '';
                if ($url) {
                    update_option('wpwoof_message', 'Feed Regenerated Successully.');
                    $wpwoof_message = 'success';
                }


                /* Reload the current page */
                wpwoof_refresh($wpwoof_message);
            } else if ($nonce &&  isset($_REQUEST['generate']) && !empty($_REQUEST['generate'])) {
                $option_id = $_REQUEST['generate'];
                $feed = wpwoof_get_feed($option_id);
                $wpwoof_values = unserialize($feed);
                $wpwoof_values['edit_feed'] = $option_id;

                $url = wpwoof_create_feed($wpwoof_values);

                $wpwoof_message = '';
                if ($url) {
                    update_option('wpwoof_message', 'Feed Generated Successully.');
                    $wpwoof_message = 'success';
                }


                /* Reload the current page */
                wpwoof_refresh($wpwoof_message);
            } else if ($nonce &&  isset($_REQUEST['copy']) && !empty($_REQUEST['copy'])) {
                $option_id = $_REQUEST['copy'];
                $feed = wpwoof_get_feed($option_id);
                $wpwoof_values = unserialize($feed);
                unset($wpwoof_values['edit_feed']);
                $aExists =  Array();
                $copy_suffix = " - Copy ";
                $sql = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'wpwoof_feedlist_" . sanitize_text_field($wpwoof_values['feed_name'].$copy_suffix) . "%'";
                $res = $wpdb->get_results($sql, 'ARRAY_A');
                foreach($res as $val){
                    $aExists[]=$val['option_name'];
                }
                $ind=0;
                do {
                    $ind++;
                    $feed_name = sanitize_text_field('wpwoof_feedlist_'.$wpwoof_values['feed_name'].$copy_suffix.$ind);
                } while (array_search($feed_name, $aExists)!==false);
                $feed_name = str_replace('wpwoof_feedlist_', "", $feed_name);
                $wpwoof_values['feed_name'] =$wpwoof_values['old_feed_name'] = $wpwoof_values['feed_name'].$copy_suffix.$ind;
                $wpwoof_values['noGenAuto'] = 1;
                $wpwoof_values['added_time'] = time();
//                trace($wpwoof_values);
                $url = wpwoof_create_feed($wpwoof_values);
                $wpwoof_values['url'] = $url;
                $wpwoof_message = '';
                if ($url) {
                    update_option('wpwoof_message', 'Feed generated Successully.');
                    $wpwoof_message = 'success';
                }


                /* Reload the current page */
                wpwoof_refresh($wpwoof_message);
            }
        } //current_user_can('administrator')
    }

    static function wpwoof_showSchedulerError(){
        echo '<div class="notice notice-error is-dismissible"> <p><b>'.WPWOOF_SL_ITEM_NAME.'</b>: Feeds won\'t be generated if your WordPress Cron is disabled, or if your website is password protected. </p></div>';
    }

    static function admin_menu() {
        if ( !self::_is_canRun() ) return;
        add_menu_page( 'Product Catalog', 'Product Catalog Pro',  'manage_feedpro', 'wpwoof-settings', array(__CLASS__, 'menu_page_callback'), WPWOOF_URL . '/assets/img/favicon.png');
    }

    static function menu_page_callback() {
        require_once('view/admin/settings.php');
    }

    static function admin_enqueue_scripts() {
        wp_enqueue_style( WPWOOF_PLUGIN.'-fastselect', WPWOOF_ASSETS_URL . 'css/fastselect.min.css', array(), WPWOOF_VERSION, false );
        wp_enqueue_script( WPWOOF_PLUGIN.'-fastselect', WPWOOF_ASSETS_URL . 'js/fastselect.min.js', array('jquery'), WPWOOF_VERSION, false );
        if(isset($_GET['page']) && $_GET['page'] == 'wpwoof-settings' ){
            //Admin Style

            wp_enqueue_style( WPWOOF_PLUGIN.'-style', WPWOOF_ASSETS_URL . 'css/admin-dashboard.css', array(), WPWOOF_VERSION, false );
            //Admin Javascript
            wp_enqueue_script( WPWOOF_PLUGIN.'-script', WPWOOF_ASSETS_URL . 'js/admin.js', array('jquery'), WPWOOF_VERSION, false );
            wp_enqueue_script( WPWOOF_PLUGIN.'-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array('jquery'), WPWOOF_VERSION, false );

            wp_enqueue_script( 'jquery.inputmask.bundle.min.js', WPWOOF_ASSETS_URL . 'js/jquery.inputmask.bundle.min.js', array('jquery'), '4.0.9', false );

            wp_enqueue_media();
            wp_enqueue_script( WPWOOF_PLUGIN.'-media-script', WPWOOF_ASSETS_URL . 'js/media.js', array('jquery'), WPWOOF_VERSION, false );

            wp_localize_script( WPWOOF_PLUGIN.'-script', 'WPWOOF', array( 'ajaxurl'=> admin_url('admin-ajax.php'), 'loading' => admin_url('images/loading.gif') ) );
        }
    }
    static function cron_schedules($schedules) {
        $interval = self::$interval;

        foreach(self::$schedule as $sec => $name){
            if($sec*1>0 && !isset($schedules[$name])){
                $schedules[$name] = array(
                    'interval' => $sec*1,
                    'display' => __($name));
            }
        }

        return $schedules;
    }
    static function do_this_generate( $feed_id ) {
        if(WPWOOF_DEBUG) file_put_contents(self::$WWC->feedBaseDir.'cron-wpfeed.log',date("Y-m-d H:i:s")."\tSTART do_this_generate\t".$feed_id."\n",FILE_APPEND);
        self::wpwoof_feed_go_update(array(0=>array("option_id"=>$feed_id)));
    }
    static function wpwoof_feed_update() {
        global $wpdb;
        $var = "wpwoof_feedlist_";
        $sql = "SELECT option_id FROM $wpdb->options WHERE option_name LIKE '".$var."%' and option_value not like '%noGenAuto\";i:1%'";
        if(WPWOOF_DEBUG) file_put_contents(self::$WWC->feedBaseDir.'cron-wpfeed.log',date("Y-m-d H:i:s")."\tSTART wpwoof_feed_update\n",FILE_APPEND);
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        self::wpwoof_feed_go_update($result);
    }
    static function wpwoof_feed_go_update($result) {
        $vFirst=null;
        $time = time();
        foreach ($result as $key => $value) {
            if(!$vFirst) {
                $vFirst=$value;
            } else {
                $time+=180;//3min
                wp_schedule_single_event( $time, 'wpwoof_generate_feed', array( (int)$value['option_id'] ) );
                if(WPWOOF_DEBUG) file_put_contents(self::$WWC->feedBaseDir.'cron-wpfeed.log',date("Y-m-d H:i:s")."\tSET wpwoof_generate_feed\t".$value['option_id']."\tTIME:".date("Y-m-d H:i:s",$time)."\n",FILE_APPEND);
            }
        }
        if($vFirst){
            $option_id = $vFirst['option_id'];
            if($option_id){
                if(WPWOOF_DEBUG) file_put_contents(self::$WWC->feedBaseDir.'cron-wpfeed.log',date("Y-m-d H:i:s")."\tSTART wpwoof_feed_go_update\t".$option_id."\n",FILE_APPEND);

                $feed = wpwoof_get_feed($option_id);
                $wpwoof_values = unserialize($feed);
                $wpwoof_values['edit_feed']=$option_id;

                if(!isset($wpwoof_values['feed_name'])) {
                    file_put_contents(WPWOOF_PATH . 'critical.log', date("Y-m-d H:i:s") . "\tERROR Structure:ID:|". $option_id."|\t". print_r($wpwoof_values, true) . "\n", FILE_APPEND);
                    exit;
                }

                $wpwoof_values['added_time'] = time();
                $url = wpwoof_create_feed($wpwoof_values, false);
                $wpwoof_values['url'] = $url;
                $updated = wpwoof_update_feed($wpwoof_values, $option_id);
                if(WPWOOF_DEBUG) file_put_contents(self::$WWC->feedBaseDir.'cron-wpfeed.log',date("Y-m-d H:i:s")."\tEND wpwoof_feed_go_update\t". $option_id."|\t".print_r($updated,true)."\n",FILE_APPEND);
            }
        }
    }
    static function activate() {
        $interval = self::$WWC->getInterval();
        if(!isset( self::$schedule[$interval])) {
            $interval = self::$interval;
            update_option('wpwoof_schedule', $interval);
        }
        wp_schedule_event(time(), self::$schedule[$interval], 'wpwoof_feed_update');

        $path_upload 	= wp_upload_dir();
        $path_upload 	= $path_upload['basedir'];
/*
        foreach(self::$aSMartTags as $tag){
            if( ! term_exists( $tag ) ){
                wp_insert_term( $tag, 'product_tag', array(
                    'description' => 'smart tag:' . $tag,
                    'parent'      => 0,
                    'slug'        => strtolower(trim($tag)),
                ) );
            }
        }
*/


        $pathes = array(
            array('wpwoof-feed', 'facebook', 'xml'),
            array('wpwoof-feed', 'facebook', 'csv'),
        );
        foreach($pathes as $path) {
            $path_folder = $path_upload;
            foreach($path as $folder) {
                $path_created = false;
                if( is_writable($path_folder) ) {
                    $path_folder = $path_folder.'/'.$folder;
                    $path_created = is_dir($path_folder);
                    if( ! $path_created ) {
                        $path_created = mkdir($path_folder, 0755);
                    }
                }
                if( ! is_writable($path_folder) || ! $path_created ) {
                    self::deactivate_generate_error('Cannot create folders in uploads folder', true, true);
                    die('Cannot create folders in uploads folder');
                }
            }
        }
    }
    static function deactivate() {
        wp_clear_scheduled_hook('wpwoof_feed_update');
    }
    static function deactivate_generate_error($error_message, $deactivate = true, $echo_error = false) {
        if( $deactivate ) {
            deactivate_plugins(array(__FILE__));
        }
        if($error_message) {
            $message = "<div class='notice notice-error is-dismissible'>
            <p>" . $error_message . "</p></div>";
            if ($echo_error) {
                echo $message;
            } else {
                add_action('admin_notices', create_function('', 'echo "' . $message . '";'), 9999);
            }
        }
    }
    static function http_request_host_is_external( $allow, $host, $url ) {
        if ( $host == 'woocommerce-5661-12828-90857.cloudwaysapps.com' )
            $allow = true;
        return $allow;
    }

    static function add_extra_fields_tag($term){
        self::add_extra_fields_category( $term,"tag");
    }
    static function edit_extra_fields_tag($term) {
        self::edit_extra_fields_category($term,"tag");
    }
    static function edit_extra_fields_category($term, $isTag = false) {
        $termData = get_term_meta($term->term_id);
        //echo "TERMDATA:";
        //trace($termData);
        wp_enqueue_script( WPWOOF_PLUGIN.'-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array('jquery'), WPWOOF_VERSION, false );
        wp_enqueue_script( WPWOOF_PLUGIN.'-script', WPWOOF_ASSETS_URL . 'js/admin.js', array('jquery'), WPWOOF_VERSION, false );
        wp_enqueue_style( WPWOOF_PLUGIN.'-style', WPWOOF_ASSETS_URL . 'css/admin.css', array(), WPWOOF_VERSION, false );
        ?>
        <!-- /table><br><br><br -->
        <tr><td colspan="2"><h1>Product Catalog Feed Pro Options:</h1></td></tr>
        <!-- table class="form-table" -->
        <?php
        $cats = $isTag=="tag" ? self::$tag_field_names : self::$category_field_names;
        foreach($cats as $fieldId => $field) {
            switch ($field['type']) {
                case 'toggle':
                    ?>
                    <tr class="form-field">
                        <th>
                            <input  name="<?php echo $fieldId; ?>" type="hidden" value="0" />
                            <input id="<?php echo $fieldId; ?>" name="<?php echo $fieldId; ?>" type="checkbox" class="ios-switch" <?php echo (isset($termData[$fieldId][0]) && $termData[$fieldId][0] ? 'checked="checked"' : ''); ?> />
                            <div class="switch"></div>
                        </th>
                        <td><label for="<?php echo $fieldId; ?>"><?php echo $field['title']; ?></label></td>
                    </tr>
                    <?php
                    break;
                case 'text':
                    ?>
                    <tr class="form-field">
                        <th><?php echo $field['title']; ?></th>
                        <td>
                            <input type='text' name="<?php echo $fieldId; ?>" value="<?php echo (isset($termData[$fieldId][0]) && $termData[$fieldId][0] ? $termData[$fieldId][0] : ''); ?>" />
                        </td>
                    </tr>
                    <?php
                    break;
                case 'select':
                    ?>
                    <tr class="form-field">
                        <th><?php echo $field['title']; ?></th>
                        <td>
                            <select name="<?php echo $fieldId; ?>">
                                <?php
                                if (isset($field['options']) && $field['options'])
                                    foreach ($field['options'] as $key => $text) {
                                        echo '<option value="' . $key . '" ' . (isset($termData[$fieldId][0]) && $termData[$fieldId][0] && $termData[$fieldId][0] == $key ? 'selected' : '') . '>' . $text . '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'googleTaxonomy':
                     $textCats =  isset($termData[$fieldId][0]) ? $termData[$fieldId][0] : "";
                    ?>
                    <tr class="form-field">
                        <th>
                            <?php echo $field['title']; ?>
                        </th>
                        <td class="addfeed-top-value">
                            <input class="wpwoof_google_category_cat_name" type="hidden" name="<?php echo $fieldId; ?>" value="<?php echo htmlspecialchars($textCats,ENT_QUOTES); ?>"  />
                            <input type="text" name="wpwoof_google_category_cat" class="wpwoof_google_category_cat" value="" style='display:none;' />
                        </td>
                    </tr>
                    <script type="text/javascript">
                        jQuery(function($) {
                            loadTaxomomy(".wpwoof_google_category_cat");
                        });
                    </script>
                    <?php
                    break;

            }
        }
        /* adding custom fields */

    }
    static function add_extra_fields_category($term, $isTag=false) {
        $termData = (!isset($term) || !isset($term->term_id)) ? array() : get_term_meta($term->term_id);


        wp_enqueue_script( WPWOOF_PLUGIN.'-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array('jquery'), WPWOOF_VERSION, false );
        wp_enqueue_script( WPWOOF_PLUGIN.'-script', WPWOOF_ASSETS_URL . 'js/admin.js', array('jquery'), WPWOOF_VERSION, false );
        wp_enqueue_style( WPWOOF_PLUGIN.'-style', WPWOOF_ASSETS_URL . 'css/admin.css', array(), WPWOOF_VERSION, false );
        ?>
        <!-- /table><br><br><br -->

        <tr><td colspan="2"><h1>Product Catalog Feed Pro Options:</h1></td></tr>
        <!-- table class="form-table" -->
        <?php
        $cats = $isTag ? self::$tag_field_names : self::$category_field_names;
        foreach($cats as $fieldId => $field) {
            switch ($field['type']) {
                case 'toggle':
                    ?>
                    <div class="form-field">
                        <input  name="<?php echo $fieldId; ?>" type="hidden" value="0" />
                        <label for="<?php echo $fieldId; ?>"><?php echo $field['title']; ?></label>
                        <input id="<?php echo $fieldId; ?>" name="<?php echo $fieldId; ?>" type="checkbox" class="ios-switch" <?php echo (isset($termData[$fieldId][0]) && $termData[$fieldId][0] ? 'checked="checked"' : ''); ?> />
                        <div class="switch"></div>
                    </div>
                    <?php
                    break;
                case 'text':
                    ?>
                    <div class="form-field">
                        <label><?php echo $field['title']; ?></label>
                        <input type='text' name="<?php echo $fieldId; ?>" value="<?php echo (isset($termData[$fieldId][0]) && $termData[$fieldId][0] ? $termData[$fieldId][0] : ''); ?>" />
                    </div>
                    <?php
                    break;
                case 'select':
                    ?>
                    <div class="form-field">
                        <label><?php echo $field['title']; ?></label>
                        <select name="<?php echo $fieldId; ?>">
                            <?php
                            if (isset($field['options']) && $field['options'])
                                foreach ($field['options'] as $key => $text) {
                                    echo '<option value="' . $key . '" ' . (isset($termData[$fieldId][0]) && $termData[$fieldId][0] && $termData[$fieldId][0] == $key ? 'selected' : '') . '>' . $text . '</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <?php
                    break;
                case 'googleTaxonomy':
                     $textCats =  isset($termData[$fieldId][0]) ? $termData[$fieldId][0] : "";
                    ?>
                    <div class="form-field">
                        <label>
                            <?php echo $field['title']; ?>
                        </label>
                        <div>
                            <input class="wpwoof_google_category_cat_name" type="hidden" name="<?php echo $fieldId; ?>" value="<?php echo htmlspecialchars($textCats,ENT_QUOTES); ?>"  />
                            <input type="text" name="wpwoof_google_category" class="wpwoof_google_category_cat" value="" style='display:none;' />
                        </div>
                    </div>
                    <script type="text/javascript">
                        jQuery(function($) {
                            loadTaxomomy(".wpwoof_google_category_cat");
                        });
                    </script>
                    <?php
                    /*
                    $taxSrc = admin_url('admin-ajax.php');
                    $taxSrc = add_query_arg( array( 'action'=>'wpwoofgtaxonmy'), $taxSrc);
                    $preselect = !empty($preselect) ? self::$oTools->convertToJSStringArray($preselect) : "";
                    ?>
                    <script>
                    var WPWOOFtaxSrc    =  '<?php echo $taxSrc; ?>';
                    var WPWOOFpreselect =  [<?php echo $preselect; ?>];
                    var WPWOOFspiner    =  '<?php echo home_url( '/wp-includes/images/wpspin.gif'); ?>';
                    </script>
                    <?php
                    */
                    break;

            }
        }
    }
    static function save_extra_fields_category($term_id) {

        $term = get_term($term_id);
        $fields = $term->taxonomy=="product_tag" ?  self::$tag_field_names : self::$category_field_names;
        foreach($fields as $fieldId => $field){
            if( isset( $_POST[$fieldId."_id"] ) ){ update_term_meta($term_id, $fieldId."_id", $_POST[$fieldId."_id"]); }
            if( isset($_POST[$fieldId]) ) update_term_meta($term_id, $fieldId, $_POST[$fieldId]);
        }
    }
    static function add_extra_fields_variable($loop, $variation_data, $post){
        ?><div class="woocommerce_variable_attributes product-catalog-feed-pro">
        <br><strong class="woof-extra-title">Product Catalog Feed Options for Variable:</strong>
        <br><br> You must configure shipping from inside your Google Merchant account - <a target="_blank" href="https://support.google.com/merchants/answer/6069284">help</a>
        <?php
        self::extra_fields_box_func( $post );
        ?></div><?php
    }
    static function woof_add_extra_fields(){
        global $post;
        ?><div id="woof_add_extra_fields"  class="panel woocommerce_options_panel" style="display:none;"><?php /* class="woocommerce_options_panel" */ ?>
        <p><strong class="woof-extra-title">&nbsp;&nbsp;Product Catalog Feed Options:</strong></p>
        <p>You must configure shipping from inside your Google Merchant account - <a target="_blank" href="https://support.google.com/merchants/answer/6069284">help</a></p>
        <?php
        self::extra_fields_box_func($post,true);
        //trace(self::$tabs);
        ?></div><?php
      /*  add_meta_box( 'extra_fields', 'Product Catalog Feed Ads Images',array(__CLASS__, 'extra_fields_box_func'), 'product', 'normal', 'high'  );*/
    }

    static function save_extra_fields( $post_id, $post ){
        if ( !isset( $_POST['wpfoof-box-media'] ) ) return;
        if ( ! isset( $_POST['nonce_name'] ) ) //make sure our custom value is being sent
            return;
        if ( ! wp_verify_nonce( $_POST['nonce_name'], 'nonce_action' ) ) //verify intent
            return;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) //no auto saving
            return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) //verify permissions
            return;
        //exit(print_r($_POST,true));



        $new_value = $_POST['wpfoof-box-media']; //array_map( 'trim', $_POST['wpfoof-box-media'] ); //sanitize

        if(isset( $_POST["feed_google_category"][$post_id."-feed_google_category"] )){
            //kostyl for js optiontree

            $new_value[$post_id."-feed_google_category"]    = $_POST["feed_google_category"][$post_id."-feed_google_category"];
        }

        //trace($new_value,true);

        foreach ( $new_value as $k => $v ) {
            $k = str_replace($post_id."-","",$k);
            $k = str_replace("0-","",$k);
            if($k == 'extra'){
                update_post_meta( $post_id, 'wpwoof'.$k, $v );
            }else {
                $old_val = trim(get_post_meta($post_id, $k, true));
                if ($old_val != $v || !empty($v)) {
                    update_post_meta($post_id, $k, trim($v) );
                } //save
            }
            //else { delete_post_meta( $post_id, $k); }
        }

    }

    static function extra_fields_box_func( $post ,$isMain=false){
        global $woocommerce_wpwoof_common;

        $post_id = (isset($post->ID)) ? $post->ID : '0';
        wp_enqueue_media();
        wp_enqueue_script( WPWOOF_PLUGIN.'-media-script', WPWOOF_ASSETS_URL . 'js/media.js', array('jquery'), WPWOOF_VERSION, false );
        wp_enqueue_script( WPWOOF_PLUGIN.'-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array('jquery'), WPWOOF_VERSION, false );
        wp_enqueue_script( WPWOOF_PLUGIN.'-script', WPWOOF_ASSETS_URL . 'js/admin.js', array('jquery'), WPWOOF_VERSION, false );
        wp_enqueue_style( WPWOOF_PLUGIN.'-style', WPWOOF_ASSETS_URL . 'css/admin-product.css', array(), WPWOOF_VERSION, false );
        wp_nonce_field( 'nonce_action', 'nonce_name' );
        require_once dirname(__FILE__).'/inc/feedfbgooglepro.php';
        $all_fields = wpwoof_get_all_fields();


        $meta_keys        = wpwoof_get_product_fields();
        $meta_keys_sort   = wpwoof_get_product_fields_sort();
        $attributes       = wpwoof_get_all_attributes();
        $gm = get_post_meta( $post_id, 'wpwoofextra', true );

        //compatibility <= 4.1.4
        if (empty($gm)) {
            $gm = @array_merge(get_post_meta( $post_id, 'wpwoofgoogle', array() ),get_post_meta( $post_id, 'wpwoofadsensecustom', array() ));
        }
        $oFeed = new FeedFBGooglePro( $meta_keys, $meta_keys_sort, $attributes);
        $select_values = $helpLinks = array();
        foreach ($all_fields['dashboardExtra'] as $key => $value) {
            if (isset($value['custom']) && !empty($value['custom'])) {
                $select_values[$key] = $value['custom'];
            }
            $helpLinks[$key]= $oFeed->getHelpLinks($value);
        }
        $link2mainFieldlist = array(
            'wpfoof-custom-descr' => 'description',
            'wpfoof-custom-title' => 'title',
            'wpfoof-mpn-name' => 'mpn',
            'wpfoof-gtin-name' => 'gtin',
            'wpfoof-brand' => 'brand',
            'wpfoof-identifier_exists' => 'identifier_exists',
            'wpfoof-condition' => 'condition',
        );

        foreach ( self::$field_names as $key => $val ) {
            if( !$isMain && empty($val['main']) || $isMain ){

                $value = $rawvalue = ($post_id) ? get_post_meta( $post_id, $key, true ) : '';
                $key   = esc_attr( $key );
                $value = esc_attr( $value );

                //compatibility <= 4.1.4
                if($key=='wpfoof-identifier_exists' && $value==='') {
                    if(isset($gm['identifier_exists']['value'])) $value = $gm['identifier_exists']['value'];
                }


                if (isset($val['topHr']) && $val['topHr'])
                        echo '<hr>';
                ?><div><p class="form-field custom_field_type"><?php
                if( empty($val['type'])){
                    $s = explode("x",$val['size']);
                    $image = ! $rawvalue ? '' : wp_get_attachment_image( $rawvalue, 'full', false, array('style' => 'display:block; /*margin-left:auto;*/ margin-right:auto;max-width:30%;height:auto;') );
                    ?>
                    <span  id='IDprev-<?php echo $post_id."-".$key; ?>'class='image-preview'><?php echo ($image) ? ($image."<br/>") : "" ?></span>
                    <label  for="<?php echo $post_id."-".$key; ?>-value"><?php echo $val['title'];?></label>
                    <span class="wrap wpwoof-required-value">
                    <input type='hidden' id='_value-<?php echo $post_id."-".$key; ?>'      name='wpfoof-box-media[<?php echo $post_id."-".$key?>]'   value='<?php echo $value?>' />
                    <input type='button' id='<?php echo $post_id."-".$key; ?>'   onclick="jQuery.fn.clickWPfoofClickUpload(this);"     class='button wpfoof-box-upload-button'        value='Upload' />
                    <input type='button' id='<?php echo $post_id."-".$key; ?>-remove' onclick="jQuery.fn.clickWPfoofClickRemove(this);" <?php if(empty($image)) {?>style="display:none;"<?php } ?> class='button wpfoof-box-upload-button-remove' value='Remove' />
                    </span>
                    <span class="unlock_pro_features" data-size='<?php echo esc_attr( $val['size']);?>'  id='<?php echo $post_id."-".$key; ?>-alert'>
                    </span>
                    <?php
                }//if(empty($val['type'])){

                else if($val['type']=="checkbox"){
                    ?>
                    <label for="<?php echo $post_id."-".$key; ?>-value"><?php echo $val['title'];?></label>
                    <span class="wrap wpwoof-required-value">
                    <input type='hidden'   id='value-<?php echo $post_id."-".$key; ?>'      name='wpfoof-box-media[<?php echo $post_id."-".$key?>]'   value='0' />
                    <input type='checkbox' id='_value-<?php echo $post_id."-".$key; ?>'     name='wpfoof-box-media[<?php echo $post_id."-".$key?>]'   value='1'  <?php if($value) echo "checked='true'"; ?> />
                    </span>
                    <?php
                }   else if($val['type']=="textarea"){
                    ?>
                    <label for="<?php echo $post_id."-".$key; ?>-value"><?php echo $val['title'];?></label>
                    <span class="wrap wpwoof-required-value">
                        <textarea   class='short wc_input_<?php echo $key; ?>' id='value-<?php echo $post_id."-".$key; ?>'      name='wpfoof-box-media[<?php echo $post_id."-".$key?>]' ><?php echo $value; ?></textarea>
                    </span>
                <?php
                    echo isset($link2mainFieldlist[$key])?'<span class="extra-link-2-wrapper">'.$oFeed->getHelpLinks($woocommerce_wpwoof_common->product_fields[$link2mainFieldlist[$key]]).'</span>':'';
                }  else if($val['type']=="text"){
                    ?>
                    <label for="<?php echo $post_id."-".$key; ?>-value"><?php echo $val['title'];?></label>
                    <span class="wrap wpwoof-required-value">
                    <input type='text' id='_value-<?php echo $post_id."-".$key; ?>'   class='short wc_input_<?php echo $key; ?>'  name='wpfoof-box-media[<?php echo $post_id."-".$key?>]'   value='<?php echo $value; ?>' />
                    </span>
                    <?php
                    echo isset($link2mainFieldlist[$key])?'<span class="extra-link-2-wrapper">'.$oFeed->getHelpLinks($woocommerce_wpwoof_common->product_fields[$link2mainFieldlist[$key]]).'</span>':'';
                }   else if($val['type'] == 'select') {
                    ?>
                    <label><?php echo $val['title'];?></label>
                    <select name='wpfoof-box-media[<?php echo $post_id . '-' . $key; ?>]' id='_value-<?php echo $post_id."-".$key; ?>' class="select short">
                        <?php
                        if (isset($val['options']) && $val['options'])
                            foreach ($val['options'] as $key2 => $text)
                                echo '<option value="' . $key2 . '" ' . (isset($value) && $value && $value == $key2 ? 'selected' : '') . '>' . $text . '</option>';
                        ?>
                    </select>
                    <?php
                    echo isset($link2mainFieldlist[$key])?'<span class="extra-link-2-wrapper">'.$oFeed->getHelpLinks($woocommerce_wpwoof_common->product_fields[$link2mainFieldlist[$key]]).'</span>':'';
                } else if ($val['type'] == 'googleTaxonomy') {
                    ?>
                    <label><?php echo $val['title']; ?></label>
                    <span class="catalog-pro-variations-google-taxonomy-container">
                        <input class="wpwoof_google_category<?php echo $post_id;?>_name" type="hidden" name="<?php echo $key;?>[<?php echo $post_id."-".$key?>]" value="<?php echo htmlspecialchars($value,ENT_QUOTES);?>"  />
                        <input type="text"   class="wpwoof_google_category<?php echo $post_id;?>" id="wpwoof_google_category_<?php echo $post_id;?>" name="wpwoof_google_category_<?php echo $post_id;?>"  value="" style='display:none;' />

                    </span>
                    <?php

                        $taxSrc = admin_url('admin-ajax.php');
                        $taxSrc = add_query_arg( array( 'action'=>'wpwoofgtaxonmy'), $taxSrc);
                    ?>
                    <script>
                         <?php if($isMain) { ?>
                                jQuery(function($) {
                                    loadTaxomomy("#wpwoof_google_category_<?php echo $post_id; ?>");
                                });
                         <?php } else {
                           ?>loadTaxomomy("#wpwoof_google_category_<?php echo $post_id;?>"); <?php
                         }  ?>

                    </script>
                    <?php

                }

                if (isset($val['subscription']) && $val['subscription']) {
                    ?><span class="woocommerce-help-tip" data-tip="<?php echo esc_attr($val['subscription']); ?>" ></span><?php
                }

                echo '</p></div>';
                if (isset($val['type']) && $val['type'] == 'trigger') {
                    ?><div class="trigger_div">
                        <input type='hidden'   name='wpfoof-box-media[<?php  echo $post_id . '-' . $key;  ?>]' value="0" />
                        <input type='checkbox'  value="1" <?php if(!empty($val['show'])){ ?> onclick="jQuery.fn.wpwoofOpenCloseFieldList('<?php echo $post_id.$val['show']; ?>',this.checked);"<?php } ?> class="ios-switch" id='_value-<?php
                            echo $post_id . '-' . $key;
                        ?>' name='wpfoof-box-media[<?php
                            echo $post_id . '-' . $key;
                        ?>]' <?php
                            echo ($value ? "checked='true'" : "");
                        ?> />

                        <div class="switch"></div>
                        <?php echo !empty($va['subtitle']) ? $va['subtitle'] : ''; ?>
                        <label class="woof-switcher-title" for="_value-<?php echo $post_id . '-' . $key; ?>"><?php echo $val['title'];?></label>
                    </div>
                    <?php
                }

                if(!empty($val['show'])){
                    $WpWoofTopSave = "";
                    ?> <div id="id<?php echo $post_id.$val['show']; ?>Fields" style="display:<?php echo !empty($value) ? 'block' : 'none'; ?>;"><?php
                    //trace(($post_id) ? get_post_meta( $post_id, 'wpwoof'.$val['show'], true ) : array());
                    $oFeed->renderFieldsToTab( $all_fields['toedittab'], $val['show'] ,($post_id) ? get_post_meta( $post_id, 'wpwoof'.$val['show'], true ) : array() );
                    ?></div><?php
                }


            }//if(!$isMain && empty($val['main']) || $isMain){

        }
        ?>
                        <hr><p><strong class="woof-extra-title">Add extra fields:</strong></p>
                        <?php
                        if ($gm && count($gm)) {
                            foreach ($gm as $key => $value) {
                                if ($value['value'] === '' || $key == 'identifier_exists' || $key == 'installmentamount')
                                    continue;
                                $isCustomTag = isset($value['custom_tag_name']) ? true : false;
                                echo '<div><p class="form-field custom_field_type add-extra-fields">';
                                if ($isCustomTag) {
                                    ?>
                        <input type="text" name="wpfoof-box-media[extra][<?= $key ?>][custom_tag_name]"  value="<?= $value['custom_tag_name'] ?>" style="margin-left: -190px;width: 187px;">
                                    <?php
                                    } else
                                        echo ' <label>' . $key . ':</label>';
                                    if (isset($select_values[$key])) {
                                        echo '<select name="wpfoof-box-media[extra][' . $key . '][value]" class="select short">';
                                        foreach ($select_values[$key] as $keySel => $valueSel) {
                                            echo '<option value="' . $keySel . '" ' . selected($valueSel, $value['value']) . '>' . $valueSel . '</option>';
                                        }
                                        echo '</select>';
                                    } else {
                                        echo '<input type="text" name="wpfoof-box-media[extra][' . $key . '][value]" placeholder="Custom value" value="' . $value['value'] . '" class="short wc_input_' . $key . '">';
                                    }
                                    echo '<input type="button" class="button remove-extra-field-product-btn" value="remove">';
//									echo '<span class="extra-link-2-wrapper">FB | G</span>';
                                    if ($isCustomTag):
                                        ?>

										<span class="extra-link-wrapper">
                                        <input type="checkbox"  id="wpfoof-box-media[extra][<?= $key ?>][feed_type][facebook]" name="wpfoof-box-media[extra][<?= $key ?>][feed_type][facebook]" <?php checked(isset($value['feed_type']['facebook'])); ?>>
                                                   <label for="wpfoof-box-media[extra][<?= $key ?>][feed_type][facebook]">Facebook</label>&emsp;&emsp;
                                         <input type="checkbox" id="wpfoof-box-media[extra][<?= $key ?>][feed_type][google]" name="wpfoof-box-media[extra][<?= $key ?>][feed_type][google]" <?php checked(isset($value['feed_type']['google'])); ?>>
                                                   <label for="wpfoof-box-media[extra][<?= $key ?>][feed_type][google]">Google Merchant</label>&emsp;&emsp;
                                         <input type="checkbox" id="wpfoof-box-media[extra][<?= $key ?>][feed_type][adsensecustom]" name="wpfoof-box-media[extra][<?= $key ?>][feed_type][adsensecustom]" <?php checked(isset($value['feed_type']['adsensecustom'])); ?>>
                                                   <label for="wpfoof-box-media[extra][<?= $key ?>][feed_type][adsensecustom]">Google Custom Remarketing</label>
                                         <input type="checkbox" id="wpfoof-box-media[extra][<?= $key ?>][feed_type][pinterest]" name="wpfoof-box-media[extra][<?= $key ?>][feed_type][pinterest]" <?php checked(isset($value['feed_type']['pinterest'])); ?>>
                                                   <label for="wpfoof-box-media[extra][<?= $key ?>][feed_type][pinterest]">Pinterest</label>
										</span>
                <?php
                else:
                    echo '<span class="extra-link-2-wrapper">'.$helpLinks[$key].'</span>';
                endif;
                if ($key=='installmentmonths') {
                    ?>
                <p class="installmentamount-wrapper form-field custom_field_type add-extra-fields">
                <label>installmentamount:</label>
                <input type="text" name="wpfoof-box-media[extra][installmentamount][value]" placeholder="Custom value" class="short wc_input_installmentamount" value="<?=$gm['installmentamount']['value']?>">
				</p>
                <?php
                }
                echo "</p></div>";
            }
        }
        ?>
                            <script> let wpwoof_select_values = <?= json_encode($select_values) ?>;
                                let wpwoof_help_links = <?= json_encode($helpLinks) ?>;
                            </script>
                        <hr id="hr-befor-add-new-field">
		<div class="wpwoof-box add-new-field-wrapper">
			<p style="display: flex;">
				<?php
					$oFeed->renderFieldsForDropbox($all_fields['dashboardExtra']);
				?>
				<input type="button" id="add-extra-field-product-btn" class="button" value="Add new field">
			</p>
		</div>

        <?php
    }




}
global $wpWoofProdCatalog;
$wpWoofProdCatalog = new wpwoof_product_catalog();
