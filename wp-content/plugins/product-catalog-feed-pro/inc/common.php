<?php

/**
 * Common class.
 *
 * Holds the config about what fields are available.
 */
class WoocommerceWpwoofCommon
{

    private $settings = array();
    private $category_cache = array();
    private static $aTaxRateCountries = array();

    /* Global mapping from feeds list */
    private static $aGlobalData = array();
    private static $aGlobalImage= '';
    private static $aGlobalGoogle = array("id"=>"","name"=>"");
    private static $interval = 0;
    private static $aWMLC = null;
    private static $aWCPBC= null;

    public $feedBaseDir = '';
    public $product_fields = array();
    /* This's list for dropdown mapping */
    public $fields_organize = array(
        'ids' => array(
            'id',
            '_sku'
        ),

        /*
        'general' => array(
            'site_name',
            'id',
            'wpfoof-mpn-name',
            'wpfoof-gtin-name',
            'item_group_id',
            'title',
            'description',
            'link',
            'availability',
            'product_type',
            'is_bundle'
        ),

        'price' => array(
            'price',
            'sale_price',
            'sale_price_effective_date',
        ),
        'shipping' => array(
            //'shipping',
            //'shipping_weight'
        ),
        'additional_data' => array(
            'brand',
            'mpn'
        ),
        'additional_images' => array(
            'wpfoof-box-media-name',
            'wpfoof-carusel-box-media-name',

        ),
       */
    );
    static function isActivatedWMPL()
    {
        return (!function_exists('pll_the_languages') && function_exists('icl_get_languages'));
    }
    static function isActivatedWMCL($act = null, $currency_name=null, $currency_code=null ) {
        if(  is_plugin_active(WPWOOF_MULTI_CRRNC) ){ /* woocommerce-multi-currency */
            self::$aWMLC = get_option('woo_multi_currency_params');
            if(  self::$aWMLC && !empty( self::$aWMLC['enable']) ) {
              switch ($act){
                  case 'settings': return self::$aWMLC;
                  case 'isfixed' : return !empty(self::$aWMLC['enable_fixed_price']);
                  case 'list':
                      if( function_exists( 'alg_get_enabled_currencies' ) ){
                         return alg_get_enabled_currencies($currency_name, $currency_code);
                      }
                      return str_replace(
                          array( '%currency_name%', '%currency_code%' ),
                          array( $currency_name, $currency_code ),
                          get_option( 'alg_currency_switcher_format', '%currency_name%' )
                      );

              }
              return true;
            }
        }
        return false;
    }
    static function isActivatedWCPBC( $act = null) {
        if(   is_plugin_active(WPWOOF_WCPBC)  ){
            if(!self::$aWCPBC) {
                self::$aWCPBC =  get_option( 'wc_price_based_country_regions', false );
            }
            switch( $act) {
                case 'settings' : return self::$aWCPBC;
            }
            return true;
        }
        return false;
    }
    static function isActivatedWCS( $act = null) {

        if(  ( is_plugin_active(WPWOOF_CURRN_SWTCH) || is_plugin_active(WPWOOF_CURRN_SWTPR) )  && 'yes' === get_option( 'alg_wc_currency_switcher_enabled', 'yes' )   ){
            return true;
        }
        return false;
    }
    static function isActivatedWOOCS() {
        
        global $WOOCS;

        if(is_object($WOOCS) && isset($WOOCS->current_currency) ){
            return true;
        }
        return false;
    }
    public $fields_organize_name = array(
        'ids' => 'ID\'s',
        //'general' => 'Products',
        //'price' => 'Price',
        //'shipping' => 'Shipping',
        //'additional_data' => 'Additional',
        //'additional_images' => 'Product images',
        //'custom_label' => 'Custom labels',
    );
    private function _addfieldImages($key)
    {
        return;
        $tmpData = array();
        foreach ($this->fields_organize['additional_images'] as $el) {
            array_push($tmpData, $el);
            if ($el == 'product_image') array_push($tmpData, $key);
        }
        $this->fields_organize['additional_images'] = $tmpData;
        if (!defined('PCFP_WP')) define('PCFP_WP', true);
    }
    public function getPicturesFields()
    {
        return array(
            'wpfoof-box-media-name'         => 'Single product ad',
            'wpfoof-carusel-box-media-name' => 'Carousel ad',

        );
    }
    public function check_plugins()
    {
        if (defined('MASHSB_VERSION')) {
            $data = get_option('mashsb_settings');

            if ($data && isset($data['post_types']) && isset($data['post_types']['product'])) {
                $this->_addfieldImages('mashshare_product_image');
            }
        }
        if (defined('WPSEO_VERSION')) {
            $this->_addfieldImages('yoast_seo_product_image');
        }
    }

    /**
     * Constructor - set up the available product fields
     *
     * @access public
     */
    function __construct()
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        add_action('plugins_loaded', array($this, 'check_plugins'));

        $upload_dir = wp_upload_dir();
        $this->feedBaseDir = $upload_dir['basedir']. "/wpwoof-feed/";

        $this->product_fields = array(

            'id' => array(
                'delimiter' => true,
                'header' => __('ID Settings', 'woocommerce_wpwoof'),
                'label' => __('ID', 'woocommerce_wpwoof'),
                /*'desc' => __('product_group_id is added when appropriate', 'woocommerce_wpwoof'),*/
                'type' => 'ID',
                'funcgetdata'=> '_id_format',
                'value' => false,
                'needcheck' => true,
                'setting' => true,
                'feed_type' => array('facebook', 'all', 'google', 'pinterest', 'adsensecustom'),
                'length' => 100,
                'filterattr' => 'ids',
                'woocommerce_default' => array('label' => 'ID', 'value' => 'id'),
                'xml' => 'g:id',
                'csv' => 'ID',
                'CDATA' => false,

            ),
            'id_prefix' => array(
                'label' => __('Prefix', 'woocommerce_wpwoof'),
                'type' => 'ID',
                'value' => false,
                'inputtype' => 'text',
                'setting' => true,
                'feed_type' => array('facebook', 'all', 'google', 'pinterest', 'adsensecustom'),
                'filterattr' => 'id',
                'CDATA' => false,

            ),
            'id_postfix' => array(
                'label' => __('Postfix', 'woocommerce_wpwoof'),
                'type' => 'ID',
                'value' => false,
                'inputtype' => 'text',
                'setting' => true,
                'feed_type' => array('facebook', 'all', 'google', 'pinterest', 'adsensecustom'),
                'filterattr' => 'id',
                'CDATA' => false,

            ),
            'title' => array(
                'label' => __('Title', 'woocommerce_wpwoof'),
                'desc' => __('The title of the product.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 150,
                'delimiter' => true,
                'woocommerce_default' => array('label' => 'Title', 'value' => 'title', 'automap'=>true),
                'type' => 'notoutput',
                'define' => true,
                'xml'       => 'g:title',
                'csv'       => 'title',
                'CDATA'     => false,
            ),
            'description' => array(
                'label'     => __('Description', 'woocommerce_wpwoof'),
                'desc'      => __('Description of the product.', 'woocommerce_wpwoof'),
                'value'     => false,
                'setting'   => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length'    => 5000,
                'woocommerce_default' => array('label' => 'Description'),
                'type'      => 'notoutput',
                'define'    => true,
                'xml'       => 'g:description',
                'csv'       => 'description',
                'CDATA'     => true,
            ),
            'short_description' => array(
                'label'     => __('Description', 'woocommerce_wpwoof'),
                'desc'      => __('Description of the product.', 'woocommerce_wpwoof'),
                'value'     => false,
                'setting'   => true,
                'needcheck' => true,
                'feed_type' => array('facebook'),
                'length'    => 5000,
                'woocommerce_default' => array('label' => 'Short Description', 'value' => 'short_description', 'automap' => true),
                'type'      => 'notoutput',
                'define'    => true,
                'xml'       => 'g:short_description',
                'csv'       => 'short_description',
                'CDATA'     => true,
            ),
            'availability' => array(
                'label' => __('Availability', 'woocommerce_wpwoof'),
                'desc' => __('Whether or not the item is in stock.', 'woocommerce_wpwoof'),
                'value' => 'in stock,out of stock,preorder,available for order',
                'setting' => true,
                'delimiter' => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Availability', 'value' => 'availability', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:availability',
                'csv'       => 'availability',
                'CDATA'     => false
            ),
            'condition' => array(
                'label' => __('Condition', 'woocommerce_wpwoof'),
                'desc' => __('The condition of the product.', 'woocommerce_wpwoof'),
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'type' => 'notoutput',
                'define' => true,
                'xml'       => 'g:condition',
                'csv'       => 'condition',
                'CDATA'     => false,
            ),
            'price' => array(
                'dependet' => true,
                'header' => __('Price and Tax', 'woocommerce_wpwoof'),
                'headerdesc' => __('Tax should be included for all countries except US, Canada and India. If you choose to include or exclude tax your price and sale price values will be recalculated for the feed based on your woocommerce settings.', 'woocommerce_wpwoof'),
                'delimiter' => true,
                'label' => __('Price', 'woocommerce_wpwoof'),
                'desc' => __('The cost of the product and currency', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'all', 'google', 'pinterest', 'adsensecustom'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Price', 'value' => 'price', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:price',
                'csv'       => 'price',
                'CDATA'     => false
            ),
            'link' => array(
                'label' => __('Link', 'woocommerce_wpwoof'),
                'desc' => __('Link to the merchant’s site where you can buy the item.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Link', 'value' => 'link', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:link',
                'csv'       => 'link',
                'CDATA'     => true
            ),
            'image_link' => array(
                'label' => __('Featured image', 'woocommerce_wpwoof'),
                'desc' => __('Link to an image of the item. This is the image used in the feed.', 'woocommerce_wpwoof'),
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Featured image', 'value' => 'product_image', 'automap' => true),
                'type' => 'automap',
                'define' => true,
                'xml'       => 'g:image_link',
                'csv'       => 'image_link',
                'CDATA'     => true,
            ),
            'brand' => array(
                'label' => __('Brand', 'woocommerce_wpwoof'),
                'desc' => __('The name of the brand.', 'woocommerce_wpwoof'),
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length'    => 100,
                'woocommerce_default' => array('label' => 'Brand', 'value' => '', 'automap' => true),
                'type'      => 'notoutput',
                'define'    => true,
                'xml'       => 'g:brand',
                'csv'       => 'brand',
                'CDATA'     => true,
                'canSetCustomValue' => true,
            ),
            'inventory' => array(
                'dependet'  => true,
                'header'    => __('Inventory', 'woocommerce_wpwoof'),
                'delimiter' => true,
                'label'     => __('Inventory', 'woocommerce_wpwoof'),
                'value'     => false,
                'setting'   => true,
                'needcheck' => true,
                'feed_type' => array('facebook'),
                'length'    => false,
                'woocommerce_default' => array('label' => 'Inventory', 'value' => '_stock', 'automap' => true),
                'type'      => 'automap',
                'xml'       => 'g:inventory',
                'csv'       => 'inventory',
                'CDATA'     => false,
                'value_type'     => 'int'
            ),
            'google_taxonomy' => array(
                'type' => 'required',
                'callback' => 'wpwoof_render_taxonomy',
                'feed_type' => array('google', 'pinterest', 'facebook', 'adsensecustom'),
                'define' => true
            ),
            'sale_price' => array(
                'dependet' => true,
                'label' => __('Sale Price', 'woocommerce_wpwoof'),
                'desc' => __('The discounted price if the item is on sale.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Sale Price', 'value' => 'sale_price', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:sale_price',
                'csv'       => 'sale_price',
                'CDATA'     => false
            ),
            'sale_pricea' => array(
                'dependet' => true,
                'label' => __('Sale Price', 'woocommerce_wpwoof'),
                'desc' => __('The discounted price if the item is on sale.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => false,
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Sale Price', 'value' => 'sale_price', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:sale_price',
                'csv'       => 'sale price',
                'CDATA'     => false
            ),
            'sale_price_effective_date' => array(
                'dependet' => true,
                'label' => __('Sale Price Effective Date', 'woocommerce_wpwoof'),
                'desc' => __('The start and end date/time of the sale, separated by slash.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Sale Price Effective Date', 'value' => 'sale_price_effective_date', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:sale_price_effective_date',
                'csv'       => 'sale_price_effective_date',
                'CDATA'     => false
            ),
            'shipping' => array(
                'label' => __('Shipping', 'woocommerce_wpwoof'),
                'delimiter' => true,
                'header' => __('Shipping:', 'woocommerce_wpwoof'),
                'desc' => __('You must configure shipping from inside your Google Merchant account - <a target="_blank" href="https://support.google.com/merchants/answer/6069284">help</a>', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'define' => true,
                'type' => 'toedittab',
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:shipping',
                'csv'       => 'shipping',
                'CDATA'     => false
            ),
            'shipping_weight' => array(
                'label' => __('shipping_weight', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('google', 'pinterest','facebook'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324503',
                'woocommerce_default' => array( 'value' => 'shipping_weight', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:shipping_weight',
                'csv'       => 'shipping_weight',
                'CDATA'     => false
            ),
            'shipping_length' => array(// For Google Feed
                'label' => __('shipping_length', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324498',
                'woocommerce_default' => array( 'value' => 'shipping_length', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:shipping_length',
                'csv'       => 'shipping_length',
                'CDATA'     => false
            ),
            'shipping_height' => array(// For Google Feed
                'label' => __('shipping_height', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324498',
                'woocommerce_default' => array( 'value' => 'shipping_height', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:shipping_height',
                'csv'       => 'shipping_height',
                'CDATA'     => false
            ),
            'shipping_width' => array(// For Google Feed
                'label' => __('shipping_width', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324498',
                'woocommerce_default' => array( 'value' => 'shipping_width', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:shipping_width',
                'csv'       => 'shipping_width',
                'CDATA'     => false
            ),
            'item_group_id' => array(
                'dependet' => true,
                'label' => __('Group ID', 'woocommerce_wpwoof'),
                'desc' => __('Is this item a variant of a product? If so, all of the items in a group should share an item_group_id.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Group ID', 'value' => 'item_group_id', 'automap' => true),
                'type' => 'automap',
                'xml'       => 'g:item_group_id',
                'csv'       => 'item_group_id',
                'CDATA'     => false
            ),
            'gtin' => array(
                'delimiter' => true,
                'header' => __('GTIN:', 'woocommerce_wpwoof'),
                'subheader' => __('<br/><br/>The plugin will fill GTIN in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom GTIN. The plugin adds a dedicated GTIN field.', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'value' => false,
                'type' => array('dashboardRequired','required'),
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 100,
                'canSetCustomValue' => true,
                'xml'       => 'g:gtin',
                'csv'       => 'gtin',
                'CDATA'     => false,

            ),
            'mpn' => array(
                'delimiter' => true,
                'header' => __('MPN:', 'woocommerce_wpwoof'),
                'subheader' => __('<br/><br/>The plugin will fill MPN in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom MPN. The plugin adds a dedicated MPN field.', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'value' => true,
                'type' => array('dashboardRequired','required'),
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 100,
                'woocommerce_default' => array('label' => 'ID', 'value' => 'id'),
                'canSetCustomValue' => true,
                'xml'       => 'g:mpn',
                'csv'       => 'mpn',
                'CDATA'     => false,

            ),
            'custom_label_0' => array(
                'feed_type'		=> array('google', 'pinterest','facebook'),
                'facebook_len'	=> false,
                'text'			=> true,
                'length' => 100,
                'type'          =>'automap',
                'xml'       => 'g:custom_label_0',
                'csv'       => 'custom_label_0',
                'woocommerce_default' => array( 'value' => 'toptag', 'automap'=>true),
                'CDATA'     => false
            ),
            'custom_label_1' => array(
                'feed_type'		=>array('google', 'pinterest','facebook'),
                'facebook_len'	=> false,
                'text'			=> true,
                'length' => 100,
                'type'          =>'automap',
                'xml'       => 'g:custom_label_1',
                'csv'       => 'custom_label_1',
                'woocommerce_default' => array( 'value' => 'toptag', 'automap'=>true),
                'CDATA'     => false
            ),
            'custom_label_2' => array(
                'delimiter' => true,
                'header' => __('Custom Labels', 'woocommerce_wpwoof'),
                'subheader' => __("<br/><br/>custom_label_0 is used for the \"recent-product\",\"on-sale\",\"top-30-days\" tags.<br/><br/>custom_label_1 is used  for the product tags.", 'woocommerce_wpwoof'),
                'label' => __('custom_label_2', 'woocommerce_wpwoof'),
                'value' => false,
                'feed_type' => array('google', 'pinterest', 'facebook'),
                'length' => 100,
                'filterattr' => 'attribute',
                'canSetCustomValue' => true,
                'xml'       => 'g:custom_label_2',
                'csv'       => 'custom_label_2',
                'CDATA'     => false
            ),
            'custom_label_3' => array(
                'label' => __('custom_label_3', 'woocommerce_wpwoof'),
                'value' => false,
                'feed_type' => array('google', 'pinterest', 'facebook'),
                'length' => 100,
                'filterattr' => 'attribute',
                'canSetCustomValue' => true,
                'xml'       => 'g:custom_label_3',
                'csv'       => 'custom_label_3',
                'CDATA'     => false
            ),
            'custom_label_4' => array(
                'label' => __('custom_label_4', 'woocommerce_wpwoof'),
                'value' => false,
                'feed_type' => array('google', 'pinterest', 'facebook'),
                'length'     => 100,
                'filterattr' => 'attribute',
                'canSetCustomValue' => true,
                'xml'       => 'g:custom_label_4',
                'csv'       => 'custom_label_4',
                'CDATA'     => false
            ),
            'identifier_exists' => array(
                'delimiter' => true,
                'header'    => __('Identifier exists:', 'woocommerce_wpwoof'),
                'label'     => __('This value', 'woocommerce_wpwoof'),
                'optional'  => true,
                'needcheck' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length'    => false,
                'value'     => false,
                'custom'    => array("select" => "true",' Yes' => 'yes', "No" => "no" ),
                'helplink'  => 'https://support.google.com/merchants/answer/6324478',
                'type'      => array('dashboardRequired','required','toedittab'),
                'xml'       => 'g:identifier_exists',
                'csv'       => 'identifier_exists',
                'CDATA'     => false,
                "toImport" => 'radio',
                'canSetCustomValue' => true,
            ),
            'adult' => array( // For Google Feed
                //'delimiter'     => true,
                'header' => __('adult', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the adult field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "adult" field. The plugin adds a custom field on every product<br><br>Custom category "adult" field. The plugin adds a custom field on every category', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'optional' => true,
                'needcheck' => false,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'value' => false,
                'custom' => array("No" => "false", "Yes" => "true"),
                'helplink' => 'https://support.google.com/merchants/answer/6324508',
                'type' => array('dashboardExtra','toedittab'),
                'xml'       => 'g:adult',
                'csv'       => 'adult',
                'CDATA'     => false,
                "toImport" => 'radio',
                'canSetCustomValue' => true,
            ),
            'age_group' => array(// For Google Feed
                //'delimiter'     => true,
                'header' => __('age_group', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the age_group field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "age_group" field. The plugin adds a custom field on every product', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'custom' => array("select" => "", "newborn" => "newborn", "infant" => "infant", "toddler" => "toddler", "kids" => "kids", "adult" => "adult"),
                'value' => false,
                'optional' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324463',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:age_group',
                'csv'       => 'age_group',
                'CDATA'     => false,
                "toImport" => 'radio',
                'canSetCustomValue' => true,
            ),
            'multipack' => array(// For Google Feed
                'header' => __('multipack', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                // 'desc'			=> __( 'Multipacks are packages that include several identical products to create a larger unit of sale, submitted as a single item.', 'woocommerce_wpwoof' ),
                'helplink' => 'https://support.google.com/merchants/answer/6324488',
                'value' => false,
                'setting' => true,
                'inputtext' => 'number',
                'feed_type' => array('google', 'pinterest'),
                'length' => 6,
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:multipack',
                'csv'       => 'multipack',
                'CDATA'     => false,
                "toImport" => 'text',
                'canSetCustomValue' => true,
            ),
            'color' => array( // For Google Feed
                'header' => __('color', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the color field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "color" field. The plugin adds a custom field on every product', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'value' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 100,
                'helplink' => 'https://support.google.com/merchants/answer/6324487',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:color',
                'csv'       => 'color',
                'CDATA'     => false,
                "toImport" => 'text',
                'canSetCustomValue' => true,

            ),
            'gender' => array(// For Google Feed
                //'delimiter'     => true,
                'header' => __('gender', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the gender field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "gender" field. The plugin adds a custom field on every product', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'custom' => array('select' => '', 'male' => 'male', 'female' => 'female', 'unisex' => 'unisex'),
                'value' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 100,
                'helplink' => 'https://support.google.com/merchants/answer/6324479',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:gender',
                'csv'       => 'gender',
                'CDATA'     => false,
                "toImport" => 'radio',
                'canSetCustomValue' => true,
            ),
            'material' => array(
                'header' => __('Material', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:material',
                'csv'       => 'material',
            ),
            'size' => array(// For Google Feed
                //'delimiter'     => true,
                'header' => __('Size', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the size field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "size" field. The plugin adds a custom field on every product', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'value' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 100,
                'helplink' => 'https://support.google.com/merchants/answer/6324492',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:size',
                'csv'       => 'size',
                'CDATA'     => false,
                "toImport" => 'text',
                'canSetCustomValue' => true,
            ),
            'size_type' => array(// For Google Feed
                //'delimiter'     => true,
                'header' => __('Size Type', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the size_type field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "size_type" field. The plugin adds a custom field on every product', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'), 'value' => false,
                'custom' => array("select" => "", "regular" => "regular", "petite" => "petite", "plus" => "plus", "big and tall" => "big and tall", "maternity" => "maternity"),
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324497',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:size_type',
                'csv'       => 'size_type',
                'CDATA'     => false,
                "toImport" => 'radio',
                'canSetCustomValue' => true,
            ),
            'size_system' => array(// For Google Feed
                'header' => __('Size System', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the size_system field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "size_system" field. The plugin adds a custom field on every product', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'), 'value' => false,
                'custom' => array("select" => "", "US" => "US", "UK" => "UK", "EU" => "EU", "DE" => "DE", "FR" => "FR", "JP" => "JP", "CN" => "CN", "IT" => "IT", "BR" => "BR", "MEX" => "MEX", "AU" => "AU"),
                'feed_type' => array('google', 'pinterest'),
                'length' => 2,
                'helplink' => 'https://support.google.com/merchants/answer/6324502',
                'type'       => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:size_system',
                'csv'       => 'size_system',
                'CDATA'     => false,
                "toImport" => 'text',
                'canSetCustomValue' => true,
            ),
            'product_dimensions' => array(
                'header' => __('Product Dimensions', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'woocommerce_default' => array('label' => 'product_dimensions', 'value' => 'product_dimensions', 'automap' => true),
                'xml'       => 'g:product_dimensions',
                'csv'       => 'product_dimensions',
            ),
            'bed_size' => array(
                'header' => __('Bed Size', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:bed_size',
                'csv'       => 'bed_size',
            ),
            'compatible_devices' => array(
                'header' => __('Compatible Devices', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:compatible_devices',
                'csv'       => 'compatible_devices',
            ),
            'model' => array(
                'header' => __('Model', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:model',
                'csv'       => 'model',
            ),
            'display_technology' => array(
                'header' => __('Display Technology', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:display_technology',
                'csv'       => 'display_technology',
            ),
            'resolution' => array(
                'header' => __('Resolution', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:resolution',
                'csv'       => 'resolution',
            ),
            'screen_size' => array(
                'header' => __('Screen Size', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:screen_size',
                'csv'       => 'screen_size',
            ),
            'age_range' => array(
                'header' => __('Age Range', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:age_range',
                'csv'       => 'age_range',
            ),
            'max_handling_time' => array(// For Google Feed
                'label' => __('max_handling_time', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/7388496',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:min_handling_time',
                'csv'       => 'min_handling_time',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'min_handling_time' => array(// For Google Feed
                'label' => __('min_handling_time', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/7388496',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:max_handling_time',
                'csv'       => 'max_handling_time',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'energy_efficiency_class' => array( // For Google Feed
                'delimiter' => true,
                'label' => __('energy_efficiency_class', 'woocommerce_wpwoof'),
                'value' => "G,F,E,D,C,B,A,A+,A++,A+++",
                'custom' => array('select' => '', 'A+++' => 'A+++', 'A++' => 'A++', 'A+' => 'A+', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G'),
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/7562785',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:energy_efficiency_class',
                'csv'       => 'energy_efficiency_class',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'max_energy_efficiency_class' => array( // For Google Feed
                //'delimiter'     => true,
                'label' => __('max_energy_efficiency_class', 'woocommerce_wpwoof'),
                'value' => "G,F,E,D,C,B,A,A+,A++,A+++",
                'custom' => array('select' => '', 'A+++' => 'A+++', 'A++' => 'A++', 'A+' => 'A+', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G'),
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/7562785',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:max_energy_efficiency_class',
                'csv'       => 'max_energy_efficiency_class',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'min_energy_efficiency_class' => array( // For Google Feed
                //'delimiter'     => true,
                'label' => __('min_energy_efficiency_class', 'woocommerce_wpwoof'),
                'value' => "G,F,E,D,C,B,A,A+,A++,A+++",
                'custom' => array('select' => '', 'A+++' => 'A+++', 'A++' => 'A++', 'A+' => 'A+', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G'),
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/7562785',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:min_energy_efficiency_class',
                'csv'       => 'min_energy_efficiency_class',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'unit_pricing_measure' => array( // For Google Feed
                'delimiter' => true,
                'label' => __('unit_pricing_measure', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324455',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:unit_pricing_measure',
                'csv'       => 'unit_pricing_measure',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'unit_pricing_base_measure' => array( // For Google Feed
                'label' => __('unit_pricing_base_measure', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => false,
                'helplink' => 'https://support.google.com/merchants/answer/6324490',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:unit_pricing_base_measure',
                'csv'       => 'unit_pricing_base_measure',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'installment'    => array(
                'label'      => __('installment', 'woocommerce_wpwoof'),
                'value'      => false,
                'setting'    => true,
                'feed_type'  => array('google', 'pinterest'),
                'callback'   => 'wpwoof_render_installment',
                'helplink'   => 'https://support.google.com/merchants/answer/6324474',
                'type'       => 'toedittab',
                'xml'        => 'g:installment',  /* <g:months>6</g:months>  <g:amount>50 BRL</g:amount> */
                'csv'        => 'installment',
                'CDATA'      => false
            ),
            'installmentmonths'    => array(
                'value'      => false,
                'setting'    => true,
                'feed_type'  => array('google', 'pinterest'),
                'callback'   => 'wpwoof_render_empty',
                'type'       => array('dashboardExtra','toedittab'),
            ),
            'installmentamount'    => array(
                'value'      => false,
                'setting'    => true,
                'feed_type'  => array('google', 'pinterest'),
                'callback'   => 'wpwoof_render_empty',
                'type'       => array('dashboardExtra','toedittab'),
            ),
            'promotion_id' => array( // For Google Feed
                'delimiter' => true,
                'label' => __('promotion_id', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('google', 'pinterest'),
                'length' => 50,
                'helplink' => 'https://support.google.com/merchants/answer/7050148',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:promotion_id',
                'csv'       => 'promotion_id',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'pattern' => array(// For Google Feed
                //'delimiter'     => true,
                'header' => __('Pattern:', 'woocommerce_wpwoof'),
                'subheader' => __('The plugin will fill the pattern field in this order:', 'woocommerce_wpwoof'),
                'headerdesc' => __('Custom product "pattern" field. The plugin adds a custom field on every product', 'woocommerce_wpwoof'),
                'label' => __('This value', 'woocommerce_wpwoof'),
                'value' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 100,
                'helplink' => 'https://support.google.com/merchants/answer/6324483',
                'type' => array('dashboardExtra','toedittab'),
                'funcgetdata'=> '_get_ExtraData',
                'xml'       => 'g:pattern',
                'csv'       => 'pattern',
                'CDATA'     => false,
                "toImport" => 'text',
                'canSetCustomValue' => true,
            ),
            'style' => array(
                'header' => __('Style', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:style',
                'csv'       => 'style',
            ),
            'shoe_width' => array(
                'header' => __('Shoe Width', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:shoe_width',
                'csv'       => 'shoe_width',
            ),
            'decor_style' => array(
                'header' => __('Decor Style', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:decor_style',
                'csv'       => 'decor_style',
            ),
            'finish' => array(
                'header' => __('Finish', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:finish',
                'csv'       => 'finish',
            ),
            'is_assembly_required' => array(
                'header' => __('Is Assembly Required', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:is_assembly_required',
                'csv'       => 'is_assembly_required',
            ),
            'thread_count' => array(
                'header' => __('Thread Count', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:thread_count',
                'csv'       => 'thread_count',
            ),
            'capacity' => array(
                'header' => __('Capacity', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:capacity',
                'csv'       => 'capacity',
            ),
            'ingredients' => array(
                'header' => __('Ingredients', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:ingredients',
                'csv'       => 'ingredients',
            ),
            'product_form' => array(
                'header' => __('Product Form', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:product_form',
                'csv'       => 'product_form',
            ),
            'recommended_use' => array(
                'header' => __('Recommended Use', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:recommended_use',
                'csv'       => 'recommended_use',
            ),
            'scent' => array(
                'header' => __('Scent', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:scent',
                'csv'       => 'scent',
            ),
            'gemstone' => array(
                'header' => __('Gemstone', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:gemstone',
                'csv'       => 'gemstone',
            ),
            'ring_size' => array(
                'header' => __('Ring Size', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:ring_size',
                'csv'       => 'ring_size',
            ),
            'watch_case_diameter' => array(
                'header' => __('Watch Case Diameter', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:watch_case_diameter',
                'csv'       => 'watch_case_diameter',
            ),
            'hair_type' => array(
                'header' => __('Hair Type', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:hair_type',
                'csv'       => 'hair_type',
            ),
            'skin_care_concern' => array(
                'header' => __('Skin Care Concern', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:skin_care_concern',
                'csv'       => 'skin_care_concern',
            ),
            'skin_tone' => array(
                'header' => __('Skin Tone', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:skin_tone',
                'csv'       => 'skin_tone',
            ),
            'skin_type' => array(
                'header' => __('Skin Type', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:skin_type',
                'csv'       => 'skin_type',
            ),
            'count' => array(
                'header' => __('Count', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:count',
                'csv'       => 'count',
            ),
            'health_concern' => array(
                'header' => __('Health Concern', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:health_concern',
                'csv'       => 'health_concern',
            ),
            'front_facing_camera_megapixel' => array(
                'header' => __('Front Facing Camera Megapixel', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:front_facing_camera_megapixel',
                'csv'       => 'front_facing_camera_megapixel',
            ),
            'operating_system' => array(
                'header' => __('Operating System', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:operating_system',
                'csv'       => 'operating_system',
            ),
            'rear_facing_camera_megapixels' => array(
                'header' => __('Rear Facing Camera Megapixels', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:rear_facing_camera_megapixels',
                'csv'       => 'rear_facing_camera_megapixels',
            ),
            'storage_capacity' => array(
                'header' => __('Storage Capacity', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:storage_capacity',
                'csv'       => 'storage_capacity',
            ),
            'video_game_platform' => array(
                'header' => __('Video Game Platform', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:video_game_platform',
                'csv'       => 'video_game_platform',
            ),
            'number_of_licenses' => array(
                'header' => __('Number of Licenses', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:number_of_licenses',
                'csv'       => 'number_of_licenses',
            ),
            'software_system_requirements' => array(
                'header' => __('Software System Requirements', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:software_system_requirements',
                'csv'       => 'software_system_requirements',
            ),
            'throw_ratio' => array(
                'header' => __('Throw Ratio', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:throw_ratio',
                'csv'       => 'throw_ratio',
            ),
            'brightness' => array(
                'header' => __('Brightness', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:brightness',
                'csv'       => 'brightness',
            ),
            'digital_zoom' => array(
                'header' => __('Digital Zoom', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:digital_zoom',
                'csv'       => 'digital_zoom',
            ),
            'megapixels' => array(
                'header' => __('Megapixels', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:megapixels',
                'csv'       => 'megapixels',
            ),
            'optical_zoom' => array(
                'header' => __('Optical Zoom', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:optical_zoom',
                'csv'       => 'optical_zoom',
            ),
            'crib_bed_size' => array(
                'header' => __('Crib Bed Size', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:crib_bed_size',
                'csv'       => 'crib_bed_size',
            ),
            'maximum_weight' => array(
                'header' => __('Maximum Weight', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:maximum_weight',
                'csv'       => 'maximum_weight',
            ),
            'minimum_weight' => array(
                'header' => __('Minimum Weight', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:minimum_weight',
                'csv'       => 'minimum_weight',
            ),
            'baby_food_stage' => array(
                'header' => __('Baby Food Stage', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:baby_food_stage',
                'csv'       => 'baby_food_stage',
            ),
            'flavor' => array(
                'header' => __('Flavor', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:flavor',
                'csv'       => 'flavor',
            ),
            'diaper_size' => array(
                'header' => __('Diaper Size', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:diaper_size',
                'csv'       => 'diaper_size',
            ),
            'package_quantity' => array(
                'header' => __('Package Quantity', 'woocommerce_wpwoof'),
                'feed_type' => array('facebook'),
                'type'      => array('dashboardExtra'),
                'xml'       => 'g:package_quantity',
                'csv'       => 'package_quantity',
            ),
            'is_bundle' => array( // For Google Feed
                'dependet' => true,
                'label' => __('Is Bundle', 'woocommerce_wpwoof'),
                'desc' => __('Merchant-defined bundles are custom groupings of different products defined by a merchant and sold together for a single price. A bundle features a main item sold with various accessories or add-ons, such as a camera combined with a bag and a lens.', 'woocommerce_wpwoof'),
                'value' => 'true,false',
                'setting' => true,
                'needcheck' => false,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'delimiter' => true,
                'length' => false,
                'woocommerce_default' => array('label' => 'Is Bundle', 'value' => 'is_bundle', "automap" => true),
                'type' => 'deleted',
                'xml'       => 'g:is_bundle',
                'csv'       => 'is_bundle',
                'CDATA'     => false
            ),
            'google_product_category' => array(
                'dependet' => true,
                'label' => __('Product Type', 'woocommerce_wpwoof'),
                'desc' => __('The retailer-defined category of the product as a string.', 'woocommerce_wpwoof'),
                'value' => true,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 750,
                'woocommerce_default' => array('label' => 'Woo Prod Categories', 'value' => 'google_product_category', 'automap' => true),
                'type'      => 'automap',
                'xml'       => 'g:google_product_category',
                'csv'       => 'google_product_category',
                'CDATA'     => false

            ),
            'product_type' => array(
                'dependet' => true,
                'label' => __('Product Type', 'woocommerce_wpwoof'),
                'desc' => __('The retailer-defined category of the product as a string.', 'woocommerce_wpwoof'),
                'value' => true,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length' => 750,
                'woocommerce_default' => array('label' => 'Woo Prod Categories', 'value' => 'product_type', 'automap' => true),
                'type'      => 'automap',
                'xml'       => 'g:product_type',
                'csv'       => 'product_type',
                'CDATA'     => false
            ),
            'shipping_label' => array(// For Google Feed
                'label'     => __('shipping_label', 'woocommerce_wpwoof'),
                'value'     => false,
                'setting'   => true,
                'feed_type' => array('facebook', 'google', 'pinterest'),
                'length'    => 1000,
                'text'      => true,
                'type'      => 'notoutput',
                'woocommerce_default' => array( 'value' => 'shipping_class', "automap" => true),
                'helplink'  => '​https://support.google.com/merchants/answer/6324504',
                'xml'       => 'g:shipping_label',
                'csv'       => 'shipping_label',
                'CDATA'     => false
            ),
            'expand_more_images' => array(
                'feed_type' => array('google', 'pinterest','facebook'),
                'length' => false,
                'type' => 'automap',
                'xml'       => 'g:additional_image_link',
                'csv'       => 'additional_image_link',
                'CDATA'     => true
            ),
            'item address' => array(
                'label' => __('Item address', 'woocommerce_wpwoof'),/*https://support.google.com/google-ads/answer/6053288?co=ADWORDS.IsAWNCustomer%3Dfalse&hl=en*/
                'value' => false,
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'setting' => true,
                'callback' => 'wpwoof_item_address',
                'define' => true,
                'csv'       => 'item address',
                'CDATA'     => false
            ),
            'contextual keywords' => array(
                'delimiter' => true,
                'header' => __('Contextual tags', 'woocommerce_wpwoof'),
                'subheader' => __('<br/><br/>The plugin will fill item contextual tags in this order:<br><br>The custom product field added by the plugin', 'woocommerce_wpwoof'),
                'label' => __('Product tags', 'woocommerce_wpwoof'),
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'inputtype' => 'checkbox',
                'define' => true,
                'csv'       => 'contextual keywords',
                'CDATA'     => false
            ),
            'item subtitle' => array(
                'label' => __('item subtitle', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('adsensecustom'),
                'length' => 25,
                'delimiter' => true,
                'funcgetdata'=> '_get_ExtraData',
                'additional_options' => array('uc_every_first' => ''),
                'helplink' => 'https://support.google.com/google-ads/answer/6053288?co=ADWORDS.IsAWNCustomer%3Dfalse',
                'type' => array('dashboardExtra','toedittab'),
                'csv'       => 'item subtitle',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'tracking template' => array(
                'label' => __('tracking template', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'helplink' => 'https://support.google.com/google-ads/answer/6053288?co=ADWORDS.IsAWNCustomer%3Dfalse',
                'type' => array('dashboardExtra','toedittab'),
                'csv'       => 'tracking template',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'custom parameter' => array(
                'label' => __('custom parameter', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'funcgetdata'=> '_get_ExtraData',
                'helplink' => 'https://support.google.com/google-ads/answer/6053288?co=ADWORDS.IsAWNCustomer%3Dfalse',
                'type' => array('dashboardExtra','toedittab'),
                'csv'       => 'custom parameter',
                'CDATA'     => false,
                'canSetCustomValue' => true,
            ),
            'item title' => array(
                'dependet' => true,
                'label' => __('item title', 'woocommerce_wpwoof'),
                'desc' => __('The title of the product.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('adsensecustom'),
                'length' => 50,//25,
                'delimiter' => true,
                'woocommerce_default' => array('label' => 'Title', 'value' => 'title', "automap" => true),
                'type' => 'notoutput',
                'csv'       => 'item title',
                'CDATA'     => false
            ),
            'item description' => array(
                'dependet' => true,
                'label' => __('Description', 'woocommerce_wpwoof'),
                'desc' => __('Description of the product <b>(highly recommended)</b> (max 25 chars).', 'woocommerce_wpwoof'),
                'value' => false,
                'feed_type' => array('adsensecustom'),
                //'length' => 25,
                'woocommerce_default' => array('label' => 'Description', 'value' => 'description', 'automap' => true),
                'type' => 'notoutput',
                'csv'       => 'item description',
                'CDATA'     => false
            ),
            'final URL' => array(
                'dependet' => true,
                'label' => __('Link', 'woocommerce_wpwoof'),
                'desc' => __('Link to the merchant’s site where you can buy the item.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Link', 'value' => 'link', 'automap' => true),
                'type'      => 'notoutput',
                'csv'       => 'final URL',
                'CDATA'     => false
            ),
            'image URL' => array(
                'dependet' => true,
                'label' => __('Featured image', 'woocommerce_wpwoof'),
                'desc' => __('Link to an image of the item. This is the image used in the feed.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'needcheck' => true,
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Featured image', 'value' => 'image_link', 'automap' => true),
                'type'      => 'notoutput',
                'csv'       => 'image URL',
                'CDATA'     => false

            ),
            'item category' => array(
                'dependet' => true,
                'label' => __('Item Category', 'woocommerce_wpwoof'),
                'desc' => __('The retailer-defined category of the product as a string.', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('adsensecustom'),
                'length' => 750,
                'woocommerce_default' => array('label' => 'Woo Prod Categories', 'value' => 'product_type', 'automap' => true),
                'type'      => 'notoutput',
                'csv'       => 'item category',
                'CDATA'     => false
            ),
            /*
            'destination URL' => array(
                'dependet' => true,
                'label' => __('Destination URL', 'woocommerce_wpwoof'),
                'desc' => __('Same domain as your website. Begins with "http://" or "https://"', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('adsensecustom'),
                'length' => false,
                'woocommerce_default' => array('label' => 'Link', 'value' => 'link', 'automap' => true),
                'type'      => 'notoutput',
                'csv'       => 'final URL',
                'CDATA'     => false
            ),

            'final_mobile_url' => array( // For Google Feed
                'dependet' => true,
                'label' => __('Mobile Link', 'woocommerce_wpwoof'),
                'desc' => __('Recommended if you have mobile-optimized versions of your landing pages.', 'woocommerce_wpwoof'),
                'setting' => true,
                'value' => false,
                'feed_type' => array('adsensecustom'),
                'woocommerce_default' => array('label' => 'Link', 'value' => 'link', 'automap' => true),
                'type'      => 'notoutput',
                'csv'       => 'destination URL',
                'CDATA'     => false
            ),
            */
            //////////////////////////////// SPECIAL FIELDS ///////////////////////////////////////////////////
            'tax' => array(
                'label' => __('Include/Exclude Tax', 'woocommerce_wpwoof'),
                'value' => false,
                'attr' => array("id" => "ID_tax_field", "onchange" => "showHideCountries(this.value);"),
                'setting' => true,
                'needcheck' => false,
                'feed_type' => array('google', 'pinterest', 'adsensecustom', 'facebook'),
                'length' => false,
                'custom' => array("Include tax in price" => 'true', "Exclude tax from price" => 'false'),
                'second_field' => 'tax_countries',
                'type' => 'TAX'
            ),
            'tax_countries' => array(
                'label' => __('Select Tax', 'woocommerce_wpwoof'),
                'value' => false,
                'needcheck' => false,
                'feed_type' => array('google', 'pinterest', 'adsensecustom', 'facebook'),
                'length' => false,
                'custom' => $this->getTaxRateCountries(),
                'rendervalues' => 'buidCountryValues',
                'cssclass' => 'CSS_tax_countries',
                'type' => 'TAX'
            ),
            'remove_currency' => array(
                'label' => __('Remove currency from the prices', 'woocommerce_wpwoof'),
                'value' => false,
                'setting' => true,
                'feed_type' => array('google', 'pinterest', 'adsensecustom', 'facebook'),
                'length' => false,
                'inputtype' => 'checkbox',
                'type' => 'TAX'
            ),
            'taxlabel' => array(
                // 'delimiter'     => true,
                'header' => __('US Tax:', 'woocommerce_wpwoof'),
                'subheader' => __('<br/><br/>For US, You must configure taxes from inside your Google Merchant account - <a target="_blank" href="https://support.google.com/merchants/answer/6069284">help</a><br/><br/>Product tax class will be used for the tax_category field.<br/><br/>', 'woocommerce_wpwoof'),
                'feed_type' => array('google'),
                'define' => true,
                'woocommerce_default' => array( 'value' => 'taxlabel', 'automap' => true),
                'type' => 'TAX',
                'xml'       => 'g:taxlabel',
                'csv'       => 'taxlabel',
                'CDATA'     => false
            )
            ////////////////////////////// END SPECIAL FIELDS ///////////////////////////////////////////////////
        );

        if (get_option('woocommerce_calc_taxes', null) != 'yes') {
            unset($this->product_fields['tax']);
        }
        $this->product_fields = apply_filters('woocommerce_wpwoof_all_product_fields', $this->product_fields);
    }

    /**
     * Helper function to remove blank array elements
     *
     * @access public
     * @param array $array The array of elements to filter
     * @return array The array with blank elements removed
     */
    private function remove_blanks($array)
    {
        if (empty($array) || !is_array($array)) {
            return $array;
        }
        foreach (array_keys($array) as $key) {
            if (empty($array[$key]) || empty($this->settings['product_fields'][$key])) {
                unset($array[$key]);
            }
        }
        return $array;
    }
    public function getTaxRateCountries($id = "")
    {
        global $wpdb;
        $key = !$id ? 'all' : $id;

        if (!empty(self::$aTaxRateCountries[$key])) {
            return self::$aTaxRateCountries[$key];
        }

        $sWhere = ($id && is_numeric($id)) ? " where  `tax_rate_id`='" . $id . "' " : "";
        self::$aTaxRateCountries[$key] = $wpdb->get_results("SELECT tax_rate_country as shcode, `tax_rate_class` as `class`, `tax_rate_id` as `id`,`tax_rate` as `rate`, `tax_rate_name` as `name` FROM {$wpdb->prefix}woocommerce_tax_rates " . $sWhere . " Order By tax_rate_class, tax_rate_country ", ARRAY_A);
        //trace(self::$aTaxRateCountries);
        return self::$aTaxRateCountries[$key];
    }
    /**
     * Helper function to remove items not needed in this feed type
     *
     * @access public
     * @param array $array The list of fields to be filtered
     * @param string $feed_format The feed format that should have its fields maintained
     * @return array The list of fields filtered to only contain elements that apply to the selectedd $feed_format
     */
    private function remove_other_feeds($array, $feed_format)
    {
        if (empty($array) || !is_array($array)) {
            return $array;
        }
        foreach (array_keys($array) as $key) {
            if (empty($this->product_fields[$key]) || !in_array($feed_format, $this->product_fields[$key]['feed_types'])) {
                unset ($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Retrieve the values that should be output for a particular product
     * Takes into account store defaults, category defaults, and per-product
     * settings
     *
     * @access public
     * @param  int $product_id The ID of the product to retrieve info for
     * @param  string $feed_format The feed format being generated
     * @param  boolean $defaults_only Whether to retrieve the
     *         store/category defaults only
     * @return array                  The values for the product
     */
    public function get_values_for_product($product_id = null, $feed_format = 'all', $defaults_only = false)
    {
        if (!$product_id) {
            return false;
        }
        // Get Store defaults
        if (!isset($this->settings['product_defaults'])) {
            $this->settings['product_defaults'] = array();
        }
        $settings = $this->remove_blanks($this->settings['product_defaults']);
        // Merge category settings
        $categories = wp_get_object_terms($product_id, 'product_cat', array('fields' => 'ids'));

        foreach ($categories as $category_id) {
            $category_settings = $this->get_values_for_category($category_id);
            $category_settings = $this->remove_blanks($category_settings);
            if ('all' != $feed_format) {
                $category_settings = $this->remove_other_feeds($category_settings, $feed_format);
            }
            if ($category_settings) {
                $settings = array_merge($settings, $category_settings);
            }
        }
        if ($defaults_only) {
            return $settings;
        }
        // Merge prepopulated data if required.
        if (!empty($this->settings['product_prepopulate'])) {
            $prepopulated_values = $this->get_values_to_prepopulate($product_id);
            $prepopulated_values = $this->remove_blanks($prepopulated_values);
            $settings = array_merge($settings, $prepopulated_values);
        }
        // Merge per-product settings.
        $product_settings = get_post_meta($product_id, '_woocommerce_wpwoof_data', true);
        if ($product_settings) {
            $product_settings = $this->remove_blanks($product_settings);
            $settings = array_merge($settings, $product_settings);
        }
        if ('all' != $feed_format) {
            $settings = $this->remove_other_feeds($settings, $feed_format);
        }
        $settings = $this->limit_max_values($settings);

        return $settings;
    }

    /**
     * Make sure that each element does not contain more values than it should.
     *
     * @param   array $data The data for a product / category.
     * @return                 The modified data array.
     */
    private function limit_max_values($data)
    {
        foreach ($this->product_fields as $key => $element_settings) {
            if (empty($element_settings['max_values']) ||
                empty($data[$key]) ||
                !is_array($data[$key])) {
                continue;
            }
            $limit = intval($element_settings['max_values']);
            $data[$key] = array_slice($data[$key], 0, $limit);
        }
        return $data;
    }

    /**
     * Retrieve category defaults for a specific category
     *
     * @access public
     * @param  int $category_id The category ID to retrieve information for
     * @return array            The category data
     */
    private function get_values_for_category($category_id)
    {
        if (!$category_id) {
            return false;
        }
        if (isset ($this->category_cache[$category_id])) {
            return $this->category_cache[$category_id];
        }
        $values = get_metadata('woocommerce_term', $category_id, '_woocommerce_wpwoof_data', true);
        $this->category_cache[$category_id] = &$values;

        return $this->category_cache[$category_id];
    }

    /**
     * Get all of the prepopulated values for a product.
     *
     * @param  int $product_id The product ID.
     *
     * @return array               Array of prepopulated values.
     */
    private function get_values_to_prepopulate($product_id = null)
    {
        $results = array();
        foreach ($this->settings['product_prepopulate'] as $gpf_key => $prepopulate) {
            if (empty($prepopulate)) {
                continue;
            }
            $value = $this->get_prepopulate_value_for_product($prepopulate, $product_id);
            if (!empty($value)) {
                $results[$gpf_key] = $value;
            }
        }
        return $results;
    }

    /**
     * Gets a specific prepopulated value for a product.
     *
     * @param  string $prepopulate The prepopulation value for a product.
     * @param  int $product_id The product ID being queried.
     *
     * @return string                The prepopulated value for this product.
     */
    private function get_prepopulate_value_for_product($prepopulate, $product_id)
    {
        $result = array();
        list($type, $value) = explode(':', $prepopulate);
        switch ($type) {
            case 'tax':
                $terms = wp_get_object_terms($product_id, array($value), array('fields' => 'names'));
                if (!empty($terms)) {
                    $result = $terms;
                }
                break;
            case 'field':
                $result = $this->get_field_prepopulate_value_for_product($value, $product_id);
                break;
        }
        return $result;
    }

    /**
     * Get a prepopulate value for a specific field for a product.
     *
     * @param  string $field Details of the field we want.
     * @param  int $product_id The product ID.
     *
     * @return array                The value for this field on this product.
     */
    private function get_field_prepopulate_value_for_product($field, $product_id)
    {
        global $woocommerce_wpwoof_frontend;

        $product = $woocommerce_wpwoof_frontend->load_product($product_id);
        if (!$product) {
            return array();
        }
        if ('sku' == $field) {
            $sku = $product->get_sku();
            if (!empty($sku)) {
                return array($sku);
            }
        }
        return array();
    }

    /**
     * Generate a list of choices for the "prepopulate" options.
     *
     * @return array  An array of preopulate choices.
     */
    public function get_prepopulate_options()
    {
        $options = array();
        $options = array_merge($options, $this->get_available_taxonomies());
        $options = array_merge($options, $this->get_prepopulate_fields());
        return $options;
    }

    /**
     * get a list of the available fields to use for prepopulation.
     *
     * @return array  Array of the available fields.
     */
    private function get_prepopulate_fields()
    {
        $fields = array(
            'field:sku' => 'SKU',
        );
        asort($fields);
        return array_merge(array('disabled:fields' => __('- Product fields -', 'woo_gpf')), $fields);
    }

    /**
     * Get a list of the available taxonomies.
     *
     * @return array Array of available product taxonomies.
     */
    private function get_available_taxonomies()
    {
        $taxonomies = get_object_taxonomies('product');
        $taxes = array();
        foreach ($taxonomies as $taxonomy) {
            $tax_details = get_taxonomy($taxonomy);
            $taxes['tax:' . $taxonomy] = $tax_details->labels->name;
        }
        asort($taxes);
        return array_merge(array('disabled:taxes' => __('- Taxonomies -', 'woo_gpf')), $taxes);
    }
    public function get_feed_count()
    {
        global $wpdb;
        $tablenm = $wpdb->prefix . 'options';
        $wpdb->get_results("SELECT *  FROM " . $tablenm . " WHERE option_name LIKE '%wpwoof_feedlist_%'");
        define("FEED_COUNT", $wpdb->num_rows);
        return $wpdb->num_rows;
    }
    private function getStatusFilePath($feedID)
    {
        $aFile = wpwoof_feed_dir($feedID, 'json');
        if (!file_exists($aFile['pathtofile'])) {
            return wp_mkdir_p($aFile['pathtofile']);
        }
        return $aFile['path'];
    }
    public function get_feed_status($feed_id, $counter = 0)
    {
        //echo $feed_id;
        //trace(get_option('wpwoof_status_'.$feed_id));
        $filePath = $this->getStatusFilePath($feed_id);
        $jBuf = is_file($filePath)?@file_get_contents($filePath):false;
        $feedStatus = ($jBuf) ? json_decode($jBuf, true) : array();
        if(empty($feedStatus) && is_file($filePath) && $counter < 3 ){ //file can be empty when upadte_feed_status() work
            usleep(1000); //wait 0.001 sec
            return self::get_feed_status($feed_id, ++$counter);
        }
        //$feedStatus= get_option('wpwoof_status_'.$feed_id,array());
        if (empty($feedStatus['time'])) $feedStatus['time'] = 0;
        if (empty($feedStatus['products_left'])) $feedStatus['products_left'] = false;// array product IDs
        if (empty($feedStatus['total_products'])) $feedStatus['total_products'] = 0; // num total products
        if (empty($feedStatus['parsed_products'])) {
            $feedStatus['parsed_products'] = in_array($feed_id, self::getScheduledFeeds())?-1:0;  // -1 if feed scheduled
        }
        if (empty($feedStatus['parsed_product_ids'])) $feedStatus["parsed_product_ids"] = array();
        if (empty($feedStatus['type'])) $feedStatus["type"] = '';
        return $feedStatus;
    }
    public function upadte_feed_status($feed_id, $newvalue, $isExit = false)
    {
        $filePath = $this->getStatusFilePath($feed_id);
        if (WPWOOF_DEBUG) {
            echo "UPDATE STATUS:" . $feed_id . "=>" . print_r($newvalue, true) . "\n";
        }
        $newvalue['time'] = time();
        @file_put_contents($filePath.'.tmp', json_encode($newvalue)); //file will be broken if script die(timeout or memory) 
        rename($filePath.'.tmp', $filePath);
        //update_option( 'wpwoof_status_'.$feed_id, $newvalue );
        //if(WPWOOF_DEBUG && $isExit) exit;
    }
    public function delete_feed_status($feed_id)
    {
        @unlink($this->getStatusFilePath($feed_id));
        //delete_option( 'wpwoof_status_'.$feed_id );
    }
    /////////////////////// Start BLOCK Global Values for fields //////////////////////////////////////////////////////
    /*
     Get Global Mapping fields
    */
    public function getGlobalData()
    {
        if (count(self::$aGlobalData) == 0) {
            $tmp_data = get_option('wpwoof-global-data', array());
            if (isset($tmp_data['brand']) and isset($tmp_data['brand']['define']) and !empty($tmp_data['brand']['define'])) {
                $tmp_data['brand']['define'] = wp_unslash($tmp_data['brand']['define']);
            }

            if(isset($tmp_data['google'])&&isset($tmp_data['adsensecustom'])) {
                $tmp_data['extra'] = array_merge($tmp_data['google'], $tmp_data['adsensecustom']);
            } elseif(isset($tmp_data['google'])) {
                $tmp_data['extra'] = $tmp_data['google'];
            } elseif(isset($tmp_data['adsensecustom'])) {
                $tmp_data['extra'] =  $tmp_data['adsensecustom'];
            } elseif (!isset ($tmp_data['extra'])) $tmp_data['extra'] = array();
            
            unset($tmp_data['google']);
            unset($tmp_data['enable_google']);
            unset($tmp_data['adsensecustom']);
            unset($tmp_data['enable_adsensecustom']);
            
            self::$aGlobalData = $tmp_data;
        }
        /*trace(self::$aGlobalData);*/
        return self::$aGlobalData;
    }
    public function setGlobalData($data)
    {
        self::$aGlobalData = $data;
        update_option('wpwoof-global-data', $data);
    }
    public function getGlobalImg()
    {
        if ( empty(self::$aGlobalImage) ) self::$aGlobalImage = get_option('wpwoof-global-image', '');
        return self::$aGlobalImage;
    }
    public function setGlobalImg($img)
    {
        self::$aGlobalImage = $img;
        update_option('wpwoof-global-image', $img);
    }
    public function getGlobalGoogleCategory() {
        if ( empty(self::$aGlobalGoogle['id']) ) self::$aGlobalGoogle = get_option( 'wpwoof-global-google-category', array( 'id'=>'', 'name'=>'' ) );
        return self::$aGlobalGoogle;
    }
    public function setGlobalGoogleCategory($data){
        self::$aGlobalGoogle = $data;
        update_option('wpwoof-global-google-category', $data);
    }
    function getInterval() {
        if(!self::$interval)  self::$interval = get_option('wpwoof_schedule', '86400');
        return self::$interval;
    }
    function getAllGlobals(){
        return array(
            "data"  => $this->getGlobalData(),
            "img"   => $this->getGlobalImg(),
            "google"=> $this->getGlobalGoogleCategory()
        );

    }
    public function getWpTimezone() {
        $timezone_string = get_option('timezone_string');
        if (!empty($timezone_string)) {
            return $timezone_string;
        }
        $offset = get_option('gmt_offset');
        $hours = (int) $offset;
        $minutes = abs(( $offset - (int) $offset ) * 60);
        $offset = sprintf('%+03d:%02d', $hours, $minutes);
        return $offset;
    }
    
    public function getScheduledFeeds() {
        $ids = array();
        foreach (get_option('cron', array()) as $cron) {
            if (isset($cron['wpwoof_generate_feed'])) {
                $ids[] = (int)$cron['wpwoof_generate_feed'][array_key_first($cron['wpwoof_generate_feed'])]['args'][0];
            }
        }
        return $ids;
    }
    
    public function checkSchedulerStatus() {
        foreach (get_option('cron', array()) as $timestamp => $cron) {
            if ($timestamp > time()  - 300) return true;
            if (isset($cron['wpwoof_generate_feed']) || isset($cron['wpwoof_feed_update'])) {
                return false;
            }
        }
        return true;
    }

}

global $woocommerce_wpwoof_common;
$woocommerce_wpwoof_common = new WoocommerceWpwoofCommon();
