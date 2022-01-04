<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


class Tiktok extends Settings implements Pixel {
    private static $_instance;
    private $configured;


    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    public function __construct() {
        parent::__construct( 'tiktok' );
        $this->locateOptions(
            PYS_PATH . '/modules/tiktok/options_fields.json',
            PYS_PATH . '/modules/tiktok/options_defaults.json'
        );
        add_action( 'pys_register_pixels', function( $core ) {
            /** @var PYS $core */
            $core->registerPixel( $this );
        } );
    }

    public function enabled() {
        return $this->getOption( 'enabled' );
    }

    public function configured() {

        $license_status = PYS()->getOption( 'license_status' );
        $pixel_id = $this->getAllPixels();
       // $disabledPixel =  apply_filters( 'pys_pixel_disabled', '', $this->getSlug() );

        $this->configured = $this->enabled()
            && ! empty( $license_status ) // license was activated before
            && count( $pixel_id ) > 0
            && !empty($pixel_id[0]);
           // && $disabledPixel != 'all';
        return $this->configured;
    }

    public function getPixelIDs() {
        $mainId = (array)$this->getOption( 'pixel_id' );
        if(!$mainId)  return apply_filters("pys_tiktok_ids",[]); // return first id only
        return apply_filters("pys_tiktok_ids",$mainId); // return first id only
    }

    public function getAllPixels($checkLang = true) {
        return $this->getPixelIDs();
    }

    /**
     * @param SingleEvent $event
     * @return array
     */
    public function getAllPixelsForEvent($event) {
        return $this->getAllPixels();
    }

    /**
     * @return array
     */
    public function getPixelOptions() {
        $options = [
            'pixelIds'          => $this->getAllPixels(),
        ];

        if($this->getOption('advanced_matching_enabled')) {
            $options['advanced_matching'] = $this->getAdvancedMatchingParams();
        }

        return $options;
    }

    /**
     * Create pixel event and fill it
     * @param SingleEvent $event
     * @return array
     */
    public function generateEvents($event) {
        $pixelEvents = [];
        if ( ! $this->configured() ) {
            return [];
        }

        $pixelIds = $this->getAllPixelsForEvent($event);

        if(count($pixelIds) > 0) {
            $pixelEvent = clone $event;

            if($this->addParamsToEvent($pixelEvent)) {
                $pixelEvent->addPayload([ 'pixelIds' => $pixelIds ]);
                $pixelEvents[] = $pixelEvent;
            }
        }


        return $pixelEvents;
    }

    public function outputNoScriptEvents()
    {

    }

    /**
     * @param SingleEvent $event
     * @return false
     */
    private function addParamsToEvent(&$event) {
        $isActive = false;

        switch ($event->getId()){
//            case 'init_event': {
//                $isActive = $this->add_page_view_params($event);
//            }break;
            case 'woo_add_to_cart_on_button_click':{
                $isActive = $this->add_woo_add_to_cart_params($event);
            }break;
            case 'woo_view_content':{
                $isActive =  $this->add_woo_view_content_params( $event);
            }break;
            case 'woo_initiate_checkout': {
                $isActive =  $this->add_woo_initiate_checkout_params($event);
            }break;
            case 'woo_purchase':{
                $isActive =  $this->add_woo_purchase_params($event);
            }break;
            case 'edd_view_content': {
                $isActive =  $this->add_edd_view_content_params( $event);
            } break;
            case 'edd_add_to_cart_on_checkout_page': {
                $isActive =  $this->add_edd_add_to_cart_on_check_params( $event);
            } break;
            case 'edd_initiate_checkout':{
                $isActive =  $this->add_edd_init_checkout_params( $event);
            }break;
            case 'edd_purchase':{
                $isActive =  $this->add_edd_purchase_params( $event);
            }break;
            case 'edd_add_to_cart_on_button_click':{
                $isActive =  $this->add_edd_add_to_cart_params( $event);
            }break;
            case 'search_event':{
                $isActive =  $this->add_search_params( $event);
            }break;

            case 'custom_event':{
                $isActive =  $this->add_custom_event_params( $event);
            }break;

            case 'wcf_add_to_cart_on_bump_click':
            case 'wcf_add_to_cart_on_next_step_click': {
                $isActive = $this->add_wcf_add_to_cart_params($event);
            }break;

            case 'wcf_view_content': {
                $isActive =  $this->addwcf_view_content_params($event);
            }break;
        }
        return $isActive;
    }

    public function getEventData( $eventType, $args = null ) {

        return false;
    }

    private function get_edd_add_to_cart_on_button_click_params( $download_id ) {
        global $post;

        // maybe extract download price id
        if ( strpos( $download_id, '_') !== false ) {
            list( $download_id, $price_index ) = explode( '_', $download_id );
        } else {
            $price_index = null;
        }

        $params = array(
            'content_type' => 'product',
        );

        // content_name, category_name
        $params['content_name'] = $post->post_title;
        $params['content_category'] = implode( ', ', getObjectTerms( 'download_category', $download_id ) );

        // currency, value
        if ( PYS()->getOption( 'edd_add_to_cart_value_enabled' ) ) {

            if( PYS()->getOption( 'edd_event_value' ) == 'custom' ) {
                $amount = getEddDownloadPrice( $post->ID, $price_index );
            } else {
                $amount = getEddDownloadPriceToDisplay( $post->ID, $price_index );
            }

            $params['currency'] = edd_get_currency();
            $params['value'] = $amount;
        }

        // contents
        $params['contents'] =  array(
            array(
                'content_id'         => (string) $download_id,
                'quantity'   => 1,

            )
        );

        return $params;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    function add_page_view_params(&$event) {
        global $post;


        $cpt = get_post_type();
        $params = array(
            'content_name' => $post->post_title,
            'content_id'    => $post->ID,
        );

        if(isWooCommerceActive() && $cpt == 'product') {
            $params['content_category'] = implode( ', ', getObjectTerms( 'product_cat', $post->ID ) );
        } elseif (isEddActive() && $cpt == 'download') {
            $params['content_category'] = implode( ', ', getObjectTerms( 'download_category', $post->ID ) );
        } elseif ($post instanceof \WP_Post) {
            $catIds = wp_get_object_terms( $post->ID, 'category', array( 'fields' => 'names' ) );
            $params['content_category'] = implode(", ",$catIds);
        }

        $data = array(
            'name'  => 'ViewContent',
        );
        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     * content_type, quantity, description, content_id, currency, value
     */
    private function add_woo_add_to_cart_params(&$event) {

        if (  !$this->getOption( 'woo_add_to_cart_enabled' ) ) {
            return false;
        }

        if(isset($event->args['productId'])) {
            $product_id = $event->args['productId'];
            $quantity = $event->args['quantity'];
            $product = wc_get_product( $product_id );
            if(!$product) {
                return false;
            }
            $params = [
                'content_category'  => implode( ', ', getObjectTerms( 'product_cat', $product_id ) ),
                'content_type'  => 'product',
                'quantity'      => $quantity,
                'currency'      => get_woocommerce_currency(),
                'content_name'   => $product->get_name(),
                'content_id'    => $product_id,
            ];
            $customProductPrice = getWfcProductSalePrice($product,$event->args);
            $isGrouped = $product->get_type() == "grouped";
            if($isGrouped) {
                $product_ids = $product->get_children();
            } else {
                $product_ids[] = $product_id;
            }
            $price = 0;
            foreach ($product_ids as $child_id) {
                $childProduct = wc_get_product($child_id);
                if($childProduct->get_type() == "variable" && $isGrouped) {
                    continue;
                }
                $price += getWooProductPriceToDisplay( $child_id, $quantity,$customProductPrice );
            }

            $params['value'] = $price;
            $event->addParams($params);
        }

        $data = [
            'name' => 'AddToCart'
        ];

        $event->addPayload($data);
        return true;
    }


    /**
     * @param SingleEvent $event
     * @return bool
     * content_type, quantity, description, content_id, currency, value
     */
    private function add_woo_view_content_params(&$event) {
        if ( ! $this->getOption( 'woo_view_content_enabled' ) ) {
            return false;
        }
        $product = wc_get_product($event->args['id']);
        $quantity = $event->args['quantity'];
        $customProductPrice = getWfcProductSalePrice($product,$event->args);

        if(!$product)  return false;

        $product_id = $product->get_id();
        $price = getWooProductPriceToDisplay( $product->get_id(),$quantity,$customProductPrice);
        $params = [
            'content_type'  => 'product',
            'quantity'      => $quantity,
            'currency'      => get_woocommerce_currency(),
            'content_name'   => $product->get_name(),
            'content_category'  => implode( ', ', getObjectTerms( 'product_cat', $product_id ) ),
            'content_id'    => $product_id,
        ];
        $data = [
            'name' => 'ViewContent'
        ];

        if(PYS()->getOption('woo_view_content_value_enabled')) {
            $params['value'] = $price;
        }

        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     * content_type, quantity, description, content_id, currency, value
     */
    function addwcf_view_content_params(&$event) {
        if ( ! $this->getOption( 'woo_view_content_enabled' )
            || empty($event->args['products'])
        ) {
            return false;
        }
        $contents = [];
        $content_names = [];
        $content_categories = [];
        $quantity = 0;
        $total = 0;
        foreach ($event->args['products'] as $product_data) {
            $product_id = $product_data['id'];
            $content_names[] = $product_data['name'];
            $content_categories[] = implode( ', ',array_column($product_data['categories'],"name"));

            $contents[] = [
                'content_id'        => $product_id,
                'quantity'          => $product_data['quantity'],
            ];
            $quantity += $product_data['quantity'];
            $total += $product_data['price'] * $product_data['quantity'];
        }
        $params = [
            'content_type'      => 'product',
            'currency'          => get_woocommerce_currency(),
            'content_name'      => implode( ', ', $content_names ),
            'content_category'  => implode( ', ', $content_categories ),
            'contents'          => $contents,
        ];

        $data = [
            'name' => 'ViewContent'
        ];

        if(PYS()->getOption('woo_view_content_value_enabled')) {
            $params['value'] = $total;
        }

        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_woo_initiate_checkout_params(&$event) {

        if ( ! $this->getOption( 'woo_initiate_checkout_enabled' ) ) {
            return false;
        }

        $num_items = 0;
        $content_categories = [];
        $content_names = [];
        $contents = [];
        foreach ($event->args['products'] as $product) {
            $product_id = $product['product_id'];
            $content_names[]      = $product['name'];
            $content_categories   = array_merge( $content_categories, array_column($product['categories'],"name") );
            $contents[] = array(
                'content_id'         => $product_id,
                'quantity'   => $product['quantity'],

            );
            $num_items += $product['quantity'];
        }

        $params = [
            'content_type'      => "product",
            'contents'          => $contents,
            'currency'          => get_woocommerce_currency(),
            'value'             => getWooEventCartSubtotal($event),
            'content_name'      => implode( ', ', $content_names ),
            'content_category'  => implode( ', ', $content_categories ),
        ];
        $data = [
            'name' => 'InitiateCheckout'
        ];

        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_woo_purchase_params(&$event) {
        if ( ! $this->getOption( 'woo_purchase_enabled' ) ) {
            return false;
        }
        $num_items = 0;
        $content_categories = [];
        $content_names = [];
        $contents = [];

        foreach ($event->args['products'] as $product_data) {
            $product_id = $product_data['product_id'];
            $content_names[]      = $product_data['name'];
            $content_categories   = array_merge( $content_categories, array_column($product_data['categories'],"name") );
            $contents[] = array(
                'content_id'         => $product_id,
                'quantity'   => $product_data['quantity'],

            );
            $num_items += $product_data['quantity'];
        }

        $params = [
            'content_type'      => 'product',
            'contents'          => $contents,
            'currency'          => get_woocommerce_currency(),
            'value'             => getWooEventOrderTotal($event),
            'content_name'      => implode( ', ', $content_names ),
            'content_category'  => implode( ', ', $content_categories ),
        ];
        $data = [
            'name' => 'PlaceAnOrder'
        ];

        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_edd_add_to_cart_on_check_params(&$event) {
        if(!$this->getOption( 'edd_add_to_cart_enabled' )) return false;

        $data = [
            'name' => 'AddToCart'
        ];
        $params = $this->getEddProductParams($event);
        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }
    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_edd_view_content_params(&$event) {
        if(!$this->getOption( 'edd_view_content_enabled' )) return false;

        $data = [
            'name' => 'ViewContent'
        ];
        $params = $this->getEddProductParams($event);
        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_edd_init_checkout_params(&$event) {
        if(!$this->getOption( 'edd_initiate_checkout_enabled' )) return false;

        $params = $this->getEddProductParams($event);

        $data = [
            'name' => 'InitiateCheckout'
        ];

        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_edd_purchase_params(&$event) {
        if(!$this->getOption( 'edd_purchase_enabled' )) return false;
        $data = [
            'name' => 'PlaceAnOrder'
        ];
        $params = $this->getEddProductParams($event);
        $params['content_id'] = $event->args['order_id'];

        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_edd_add_to_cart_params(&$event) {
        if(!$this->getOption( 'edd_add_to_cart_enabled' )) return false;
        $params = [];
        if($event->args != null) {
            $params = $this->get_edd_add_to_cart_on_button_click_params( $event->args );
        }
        $data = [
            'name' => 'AddToCart'
        ];
        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_search_params(&$event) {
        if ( ! $this->getOption( 'search_event_enabled' ) ) {
            return false;
        }
        $params = array();
        $params['query'] = empty( $_GET['s'] ) ? null : $_GET['s'];
        $data = [
            'name' => 'Search'
        ];
        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_custom_event_params(&$event) {
        /**
         * @var CustomEvent $customEvent
         */
        $customEvent = $event->args;
        if(!$customEvent->isTikTokEnabled()) return false;

        $params = [];

        if($customEvent->tiktok_params_enabled) {
            $params = $customEvent->tiktok_params;
            $customParams = $customEvent->tiktok_custom_params;
            foreach ( $customParams as $custom_param ) {
                $params[ $custom_param['name'] ] = $custom_param['value'];
            }
            // SuperPack Dynamic Params feature
            $params = apply_filters( 'pys_superpack_dynamic_params', $params, 'tiktok' );
        }

        $data = [
            'name'  => $customEvent->getTikTokEventType(),
            'delay' => $customEvent->getDelay(),
        ];
        $event->addPayload($data);
        $event->addParams($params);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function add_wcf_add_to_cart_params(&$event) {
        if(  !$this->getOption( 'woo_add_to_cart_enabled' )
            || empty($event->args['products'])
        ) {
            return false; // return if args is empty
        }
        $contents = [];
        $content_names = [];
        $content_categories = [];
        $quantity = 0;
        $total = 0;
        foreach ($event->args['products'] as $product_data) {
            $product_id = $product_data['id'];
            $content_names[] = $product_data['name'];
            $content_categories[] = implode( ', ',array_column($product_data['categories'],"name"));

            $contents[] = [
                'content_id'         => $product_id,
                'quantity'   => $product_data['quantity'],

            ];
            $quantity += $product_data['quantity'];
            $total += $product_data['price'] * $product_data['quantity'];
        }
        $params = [
            'content_type'      => 'product',
            'currency'          => get_woocommerce_currency(),
            'content_name'      => implode( ', ', $content_names ),
            'content_category'  => implode( ', ', $content_categories ),
            'content_id'        => $product_id,
            'contents'          => $contents,
            'value'             => $total
        ];
        $data = [
            'name' => 'AddToCart'
        ];

        $event->addParams($params);
        $event->addPayload($data);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return array
     */
    private function getEddProductParams($event) {

        switch ($event->getId()) {

            default: {
                $value_enabled = true;
            }
        }

        $num_items = 0;
        $content_categories = [];
        $content_names = [];
        $contents = [];
        $total = 0;
        $total_as_is = 0;
        $tax = 0;
        foreach ( $event->args['products'] as $product ) {
            $download_id   = (int) $product['product_id'];
            $content_names[]    = $product['name'];
            $content_categories = array_merge($content_categories,array_column($product['categories'],'name'));
            $num_items += $product['quantity'];
            $contents[] = array(
                'content_id'         => (string) $download_id,
                'quantity'   => $product['quantity'],
            );
            if ( $event->getId() == 'edd_purchase' ) {
                if ( PYS()->getOption( 'edd_tax_option' ) == 'included' ) {
                    $total += $product['subtotal'] + $product['tax'] - $product['discount'];
                } else {
                    $total += $product['subtotal'] - $product['discount'];
                }
                $tax += $product['tax'];
                $total_as_is += $product['price'];

            } else {

                $total += getEddDownloadPrice( $download_id,$product['price_index']  ) * $product['quantity'];
                if(isset($product['cart_item_key']) ){
                    $total_as_is += edd_get_cart_item_final_price( $product['cart_item_key']  );
                } else {
                    $total_as_is += floatval(edd_get_download_final_price( $download_id ,[]));
                }


            }
        }
        $content_categories = array_unique($content_categories);

        $params = [
            'content_type'      => 'product',
            'contents'          => $contents,
            'content_name'      => implode( ', ', $content_names ),
            'content_category'  => implode( ', ', $content_categories ),
            'quantity'          => $num_items
        ];


        if($value_enabled) {
            if( PYS()->getOption( 'edd_event_value' ) == 'custom' ) {
                $params['value'] = $total;
            } else {
                $params['value'] = $total_as_is;
            }
            $params['currency'] = get_woocommerce_currency();
        }

        return $params;
    }

    function getAdvancedMatchingParams() {

        $params = array();
        $user = wp_get_current_user();

        if ( $user->ID ) {
            // get user regular data
            $userEmail = $user->get( 'user_email' );
            $userPhone = get_user_meta( $user->ID,'user_phone',true);
            if(!empty($userEmail)) {
                $params['sha256_email'] = hash('sha256', $userEmail);
            }
            if(!empty($userPhone)) {
                $params['sha256_phone_number'] = hash('sha256',$userPhone);
            }

        }

        if ( isWooCommerceActive() && PYS()->getOption( 'woo_enabled' ) ) {
            if ( $user->ID ) {
                $billing_phone = $user->get( 'billing_phone' );
                if(!empty($billing_phone)) {
                    $params['sha256_phone_number'] = hash('sha256',$billing_phone);
                }
            }
        }


        return $params;

    }
}


/**
 * @return Tiktok
 */
function Tiktok() {
    return Tiktok::instance();
}

Tiktok();