<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** @noinspection PhpIncludeInspection */
require_once PYS_PATH . '/modules/google_analytics/function-helpers.php';
/** @noinspection PhpIncludeInspection */
require_once PYS_PATH . '/modules/google_ads/function-helpers.php';

use PixelYourSite\Ads\Helpers;

class GoogleAds extends Settings implements Pixel {

	private static $_instance;

	private $configured;

	/** @var array $wooOrderParams Cached WooCommerce Purchase and AM events params */
	private $wooOrderParams = array();

	private $googleBusinessVertical;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;

	}

	public function __construct() {

		parent::__construct( 'google_ads' );

		$this->locateOptions(
			PYS_PATH . '/modules/google_ads/options_fields.json',
			PYS_PATH . '/modules/google_ads/options_defaults.json'
		);

		add_action( 'pys_register_pixels', function( $core ) {
			/** @var PYS $core */
			$core->registerPixel( $this );
		} );

		// cache value
		$this->googleBusinessVertical = PYS()->getOption( 'google_retargeting_logic' ) == 'ecomm' ? 'retail' : 'custom';

        add_filter('pys_google_ads_settings_sanitize_ads_ids_field', 'PixelYourSite\Ads\Helpers\sanitizeTagIDs');
        add_action( 'add_meta_boxes', array( $this, 'registerProductMetaBox' ) );
        add_action( 'save_post_product', array( $this, 'saveProductMetaBox' ), 10, 3 );
        add_action( 'wp_head', array( $this, 'output_meta_tag' ) );
	}

	public function enabled() {
		return $this->getOption( 'enabled' );
	}

	public function configured() {

        $license_status = PYS()->getOption( 'license_status' );
        $ads_ids = $this->getAllPixels();
        $disabledPixel =  apply_filters( 'pys_pixel_disabled', '', $this->getSlug() );
        $this->configured = $this->enabled()
                            && ! empty( $license_status ) // license was activated before
                            && count( $ads_ids ) > 0
                            && $disabledPixel != 'all';

		return $this->configured;

	}

	public function getPixelIDs() {

        if(EventsWcf()->isEnabled() && isWcfStep()) {
            $ids = $this->getOption( 'wcf_pixel_id' );
            if(!empty($ids))
                return [$ids];
        }

		$ids = (array) $this->getOption( 'ads_ids' );
        if(count($ids) == 0 || empty($ids[0])) {
            return apply_filters("pys_google_ads_ids",[]);
        } else {
            return apply_filters("pys_google_ads_ids",(array) reset( $ids )); // return first id only
        }
	}

    public function getAllPixels($checkLang = true) {
        $pixels = $this->getPixelIDs();

        if( isSuperPackActive()
            && SuperPack()->getOption( 'enabled' )
            && SuperPack()->getOption( 'additional_ids_enabled' )
        ) {
            $additionalPixels = SuperPack()->getAdsAdditionalPixel();
            foreach ($additionalPixels as $_pixel) {
                if($_pixel->isEnable
                    && (!$checkLang || $_pixel->isValidForCurrentLang())
                ) {
                    $pixels[]=$_pixel->pixel;
                }

            }
        }

        return $pixels;
    }


    /**
     * @param SuperPack\SPPixelId $pixelId
     * @return bool
     */
    private function isValidForCurrentLang($pixelId) {
        if(isWPMLActive()) {
            $current_lang_code = apply_filters( 'wpml_current_language', NULL );
            if(is_array($pixelId->wpmlActiveLang) && !in_array($current_lang_code,$pixelId->wpmlActiveLang)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param PYSEvent $event
     * @return array|mixed|void
     */
    public function getAllPixelsForEvent($event) {
        $pixels = $this->getPixelIDs();

        if(isSuperPackActive('3.0.09')) {
            $additionalPixels = SuperPack()->getAdsAdditionalPixel();
            foreach ($additionalPixels as $_pixel) {
                if($_pixel->isValidForEvent($event) && $_pixel->isConditionalValidForEvent($event)) {
                    $pixels[]=$_pixel->pixel;
                }
            }
        }

        return $pixels;
    }

	public function getPixelOptions() {

		return array(
			'conversion_ids'      => $this->getAllPixels(),
            'wooVariableAsSimple' => $this->getOption( 'woo_variable_as_simple' ),
		);

	}

    /**
     * Create pixel event and fill it
     * @param SingleEvent $event
     * @return SingleEvent[]
     */
    public function generateEvents($event) {
        $disabledPixel =  apply_filters( 'pys_pixel_disabled', false, $this->getSlug() );
        if($disabledPixel == '1' || $disabledPixel == 'all') return [];

        $pixelEvents = [];
        $conversionLabel = []; // only for custom (send only conversion without main pixel)
        $pixelIds = [];

        if($event->getId() == 'custom_event') {
            // ids
            $allIds = $this->getAllPixelsForEvent($event);
            $customEvent = $event->args;
            $conversion_label = $customEvent->google_ads_conversion_label;
            $conversion_id = $customEvent->google_ads_conversion_id;

            if ( $conversion_id == '_all' ) {
                if(count($allIds) > 0) {
                    $conversion_id = $allIds[0];
                }
            }

            if(!in_array($conversion_id,$allIds) || $conversion_id == $disabledPixel) {
                return []; // not fire event if pixel id was disabled or deleted
            }

            // AW-12345678 => AW-12345678/da324asDvas
            if ( ! empty( $conversion_label ) ) {
                $conversionLabel = [$conversion_id. '/' . $conversion_label];
            } else {
                if($conversion_id) {
                    $pixelIds = [$conversion_id];
                }
            }
        } else {
            // filter disabled pixels
            $pixelIds = $this->getAllPixelsForEvent($event);
            if(!empty($disabledPixel)) {
                foreach ($pixelIds as $key => $value) {
                    if($value == $disabledPixel) {
                        array_splice($pixelIds,$key,1);
                    }
                }
            }
        }


        if(count($pixelIds) > 0 || count($conversionLabel) > 0)  {
            $pixelEvent = clone $event;
            if($this->addParamsToEvent($pixelEvent)) {
                if(count($pixelIds) > 0)
                    $pixelEvent->addPayload([ 'conversion_ids' => $pixelIds ]);
                if(count($conversionLabel) > 0)
                    $pixelEvent->addPayload([ 'conversion_labels' => $conversionLabel ]);
                $pixelEvents[] = $pixelEvent;
            }
        }

        $listOfEddEventWithProducts = ['edd_add_to_cart_on_checkout_page','edd_purchase',];
        $listOfWooEventWithProducts = ['woo_purchase','woo_add_to_cart_on_checkout_page','woo_add_to_cart_on_cart_page'];
        $isWooEventWithProducts = in_array($event->getId(),$listOfWooEventWithProducts);
        $isEddEventWithProducts = in_array($event->getId(),$listOfEddEventWithProducts);

        if($isWooEventWithProducts || $isEddEventWithProducts)
        {
            if(isSuperPackActive('3.0.0')
                && SuperPack()->getOption( 'enabled' )
                && SuperPack()->getOption( 'additional_ids_enabled' ))
            {
                $additionalPixels = SuperPack()->getAdsAdditionalPixel();
                foreach ($additionalPixels as $_pixel) {
                    $filter = null;

                    if(!$_pixel->isValidForEvent($event)|| $_pixel->pixel == $disabledPixel) continue;

                    if($isWooEventWithProducts) {
                        $filter = $_pixel->getWooFilter();
                    }
                    if($isEddEventWithProducts) {
                        $filter = $_pixel->getEddFilter();
                    }
                    if($filter != null) {
                        $products = [];

                        if($isWooEventWithProducts) {
                            $products = EventsWoo()->filterEventProductsBy($event,$filter['filter'],$filter['sub_id']);
                        }
                        if($isEddEventWithProducts) {
                            $products = EventsEdd()->filterEventProductsBy($event,$filter['filter'],$filter['sub_id']);
                        }
                        if(count($products) > 0) {
                            $additionalEvent = clone $event;
                            $additionalEvent->addPayload([ 'conversion_ids' => [$_pixel->pixel] ]);
                            $additionalEvent->args['products'] = $products;
                            if($this->addParamsToEvent($additionalEvent)) {
                                $pixelEvents[] = $additionalEvent;
                            }
                        }
                    }
                }
            }
        }


        return $pixelEvents;
    }
    /**
     * @param SingleEvent $event
     * @return boolean
     */
    private function addParamsToEvent(&$event) {
        if ( ! $this->configured() ) {
            return false;
        }
        $isActive = false;
        switch ($event->getId()) {
            case 'init_event':{
                $eventData = $this->getPageViewEventParams();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case "signal_user_signup":
            case "signal_page_scroll":
            case "signal_time_on_page":
            case "signal_tel":
            case "signal_email":
            case "signal_form":
            case "signal_download":
            case "signal_comment":
            case "signal_watch_video": {
                $isActive = $this->getOption('signal_events_enabled');
            }  break;
            case "signal_adsense": break;

            case 'woo_view_content':{
                $eventData = $this->getWooViewContentEventParams($event->args);
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            } break;
            case 'woo_add_to_cart_on_cart_page':
            case 'woo_add_to_cart_on_checkout_page': {
                $isActive = $this->getWooAddToCartOnCartEventParams($event);
            }break;

            case 'woo_view_category':{
                $eventData = $this->getWooViewCategoryEventParams();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;

            case 'woo_purchase':{
                $isActive = $this->getWooPurchaseEventParams($event);

            }break;

            case 'edd_view_content':{
                $eventData = $this->getEddViewContentEventParams();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case 'edd_purchase':
            case 'edd_add_to_cart_on_checkout_page':{
                $isActive = $this->setEddCartEventParams( $event );
            }break;

            case 'edd_view_category':{
                $eventData = $this->getEddViewCategoryEventParams();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;

            case 'custom_event':{
                $eventData =  $this->getCustomEventData( $event );
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;

            case 'woo_add_to_cart_on_button_click': {
                if (  $this->getOption( 'woo_add_to_cart_enabled' ) && PYS()->getOption( 'woo_add_to_cart_on_button_click' ) ) {
                    $isActive = true;
                    if(isset($event->args['productId'])) {
                        $eventData =  $this->getWooAddToCartOnButtonClickEventParams( $event->args );
                        if($eventData) {
                            $event->addParams($eventData["params"]);
                            unset($eventData["params"]);
                            $event->addPayload($eventData);
                        }
                    }


                    $event->addPayload(array(
                        'name'=>"add_to_cart"
                    ));
                }
            }break;

            case 'woo_affiliate': {
                if (  $this->getOption( 'woo_affiliate_enabled' ) ) {
                    $isActive = true;
                    if(isset($event->args['productId'])) {
                        $productId = $event->args['productId'];
                        $quantity = $event->args['quantity'];
                        $eventData = $this->getWooAffiliateEventParams( $productId,$quantity );
                        if($eventData) {
                            $event->addParams($eventData["params"]);
                            unset($eventData["params"]);
                            $event->addPayload($eventData);
                        }
                    }
                }
            }break;

            case 'edd_add_to_cart_on_button_click': {
                if (  $this->getOption( 'edd_add_to_cart_enabled' ) && PYS()->getOption( 'edd_add_to_cart_on_button_click' ) ) {
                    $isActive = true;
                    if($event->args != null) {
                        $eventData =  $this->getEddAddToCartOnButtonClickEventParams( $event->args );
                        $event->addParams($eventData['params']);
                        $event->addPayload(['ids'=>$eventData["ids"]]);
                    }
                    $event->addPayload(array(
                        'name'=>"add_to_cart"
                    ));
                }
            }break;

            case 'wcf_view_content': {
                $isActive =  $this->getWcfViewContentEventParams($event);
            }break;
            case 'wcf_add_to_cart_on_bump_click':
            case 'wcf_add_to_cart_on_next_step_click': {
                $isActive = $this->prepare_wcf_add_to_cart($event);
            }break;

            case 'wcf_remove_from_cart_on_bump_click': {
                    $isActive = $this->prepare_wcf_remove_from_cart($event);
                } break;

            case 'wcf_bump': {
                    $isActive = $this->getOption('wcf_bump_event_enabled');
                }break;

            case 'wcf_page': {
                    $isActive = $this->getOption('wcf_cart_flows_event_enabled');
                }break;

            case 'wcf_step_page': {
                    $isActive = $this->getOption('wcf_step_event_enabled');
                }break;

            case 'wcf_lead': {
                $isActive = PYS()->getOption('wcf_lead_enabled');
            }break;
        }


        return $isActive;
    }

    private function addDataToEvent($eventData,&$event) {
        $params = $eventData["data"];
        unset($eventData["data"]);

        $event->addParams($params);
        $event->addPayload($eventData);
    }

	public function getEventData( $eventType, $args = null ) {

        return false;

    }

    public function outputNoScriptEvents() {

	    /* dont send google ads no script events to google analytics */

    }

    private function getPageViewEventParams() {
        global $post;
        $cpt = get_post_type();
        $params = array();
        $items = array();

        if((!isWooCommerceActive() || ($cpt != "product" && !is_checkout() && !is_cart() && !is_order_received_page() && !is_tax('product_cat'))) &&
            (!isEddActive() || ($cpt != "download" && !edd_is_checkout() && !edd_is_success_page() && !is_tax('download_category')))
            ) {

            if (!$this->getOption("page_view_post_enabled") && $cpt == "post") return false;
            if (!$this->getOption("page_view_page_enabled") && $cpt == "page") return false;

            if ($cpt != "post" && $cpt != "page") {
                $enabledCustom = (array)$this->getOption("page_view_custom_post_enabled");
                if (!in_array("index_" . $cpt, $enabledCustom)) return false;
            }

            if(is_category() ) {
                global $posts;
                if($posts) {
                    foreach ($posts as $p) {
                        $items[] = array(
                            "id"=> $p->ID,
                            "google_business_vertical" => $this->getOption("page_view_business_vertical")
                        );
                    }
                }
            } else {
                if($post) {
                    $items[] = array(
                        "id"=> $post->ID,
                        "google_business_vertical" => $this->getOption("page_view_business_vertical")
                    );
                }

            }
        }

        if ( PYS()->getEventsManager()->doingAMP ) {
            return array(
                'name' => 'PageView',
                'data' => array(),
            );
        }

        $params['items'] = $items;

        return array(
            'name'  => 'page_view',
            'data'  => $params,

        );

    }

    /**
     * @param PYSEvent $event
     *
     * @return array|bool
     */
    private function getCustomEventData( $event ) {
        /**
         * @var CustomEvent $customEvent
         */
        $customEvent = $event->args;
        $ads_action = $customEvent->getGoogleAdsAction();

        if ( ! $customEvent->isGoogleAdsEnabled() || empty( $ads_action ) ) {
            return false;
        }

        $params = array(
            'event_category'  => $customEvent->google_ads_event_category,
            'event_label'     => $customEvent->google_ads_event_label,
            'value'           => $customEvent->google_ads_event_value,
        );

	    // add custom params
        $customParams = $customEvent->getGoogleAdsCustomParams();
	    foreach ( $customParams as $custom_param ) {
		    $params[ $custom_param['name'] ] = $custom_param['value'];
	    }

        // SuperPack Dynamic Params feature
        $params = apply_filters( 'pys_superpack_dynamic_params', $params, 'google_ads' );



	    return array(
		    'name'  => $ads_action,
		    'data'  => $params,
		    'delay' => $customEvent->getDelay(),
	    );

    }

    private function getWooViewCategoryEventParams() {
        global $posts;

        if ( ! $this->getOption( 'woo_view_category_enabled' ) ) {
            return false;
        }

        $term = get_term_by( 'slug', get_query_var( 'term' ), 'product_cat' );
        if(!is_a($term,"WP_Term") || !$term)
            return false;
        $parent_ids = get_ancestors( $term->term_id, 'product_cat', 'taxonomy' );

        $product_categories = array();
        $product_categories[] = $term->name;

        foreach ( $parent_ids as $term_id ) {
            $parent_term = get_term_by( 'id', $term_id, 'product_cat' );
            $product_categories[] = $parent_term->name;
        }

        $list_name = implode( '/', array_reverse( $product_categories ) );

        $items = array();
        $total_value = 0;

        for ( $i = 0; $i < count( $posts ); $i ++ ) {

            if ( $posts[ $i ]->post_type !== 'product' ) {
                continue;
            }

            $item = array(
                'id'            => Helpers\getWooFullItemId( $posts[ $i ]->ID ),
                'google_business_vertical' => $this->googleBusinessVertical,
            );

            $items[] = $item;
            $total_value += getWooProductPriceToDisplay( $posts[ $i ]->ID );

        }

        $params = array(
            'event_category' => 'ecommerce',
            'event_label'    => $list_name,
            'value'          => $total_value,
            'items'          => $items,
        );

        return array(
            'name'  => 'view_item_list',
            'ids' => Helpers\getConversionIDs( 'woo_view_category' ),
            'data'  => $params,
        );

    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    function prepare_wcf_remove_from_cart(&$event) {
        if( ! $this->getOption( 'woo_remove_from_cart_enabled' )
            || empty($event->args['products'])
        ) {
            return false; // return if args is empty
        }
        $product_data = $event->args['products'][0];
        $product_id = $product_data['id'];
        $content_id = Helpers\getWooFullItemId( $product_id );
        $value = getWooProductPriceToDisplay( $product_id, $product_data['quantity'],$product_data['price'] );

        $event->addParams(array(
            'event_category'  => 'ecommerce',
            'currency'        => get_woocommerce_currency(),
            'value'           => $value,
            'items'           => array(
                array(
                    'id'       => $content_id,
                    'google_business_vertical' => $this->googleBusinessVertical,
                ),
            ),
                )
        );

        $event->addPayload([
            'name'=>"remove_from_cart",
        ]);
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function prepare_wcf_add_to_cart(&$event) {
        if(  !$this->getOption( 'woo_add_to_cart_enabled' )
            || empty($event->args['products']) ) {
            return false; // return if args is empty
        }

        if(is_home() || is_front_page()) {
            $ecomm_pagetype = "home";
        }elseif(is_shop()) {
            $ecomm_pagetype = "shop";
        }elseif(is_cart()) {
            $ecomm_pagetype = "cart";
        }elseif(is_single()) {
            $ecomm_pagetype = "product";
        }elseif(is_category()) {
            $ecomm_pagetype = "category";
        } else {
            $ecomm_pagetype = get_post_type();
        }
        $value          = 0;
        $content_ids    = array();
        $content_names  = array();
        $items = array();

        foreach ($event->args['products'] as $product_data) {
            $content_id = Helpers\getWooFullItemId( $product_data['id'] );
            $content_ids[] = $content_id;
            $content_names[] = $product_data['name'];
            $value += getWooProductPriceToDisplay( $product_data['id'], $product_data['quantity'] ,$product_data['price']);
            $items[] = array(
                'id'       => $content_id,
                'google_business_vertical' => $this->googleBusinessVertical,
            );

        }

        $params = array(
            'ecomm_prodid' => $content_ids,
            'ecomm_pagetype'=> $ecomm_pagetype,
            'event_category'  => 'ecommerce',
            'value' => $value,
            'items' => $items
        );

        $event->addParams($params);
        $event->addPayload(array(
            'name'=>"add_to_cart",
            'ids' => Helpers\getConversionIDs( 'woo_add_to_cart' ),
        ));
        return true;
    }

    /**
     * @param SingleEvent $event
     * @return false
     */
    private function getWcfViewContentEventParams(&$event) {
        if ( ! $this->getOption( 'woo_view_content_enabled' )
            || empty($event->args['products'])
        ) {
            return false;
        }

        $product_data = $event->args['products'][0];

        $product_id = $product_data['id'];
        $id = Helpers\getWooFullItemId( $product_id );
        $price = getWooProductPriceToDisplay( $product_id ,$product_data['quantity'],$product_data['price']);
        $params = array(
            'ecomm_prodid'=> $id,
            'ecomm_pagetype'=> 'product',
            'event_category'  => 'ecommerce',
            'value' => $price,
            'items'           => array(
                array(
                    'id'       => $id,
                    'google_business_vertical' => $this->googleBusinessVertical,
                ),
            ),
        );

        $event->addParams($params);
        $event->addPayload([
            'name'  => 'view_item',
            'ids'   => Helpers\getConversionIDs( 'woo_view_content' ),
            'delay' => (int) PYS()->getOption( 'woo_view_content_delay' ),
        ]);
        return true;
    }

    private function getWooViewContentEventParams($eventArgs = null) {


        if ( ! $this->getOption( 'woo_view_content_enabled' ) ) {
            return false;
        }

        $quantity = 1;
        $customProductPrice = -1;
        if($eventArgs && isset($eventArgs['id'])) {
            $productId = $eventArgs['id'];
            $quantity = $eventArgs['quantity'];
            $customProductPrice = getWfcProductSalePrice(wc_get_product($eventArgs['id']),$eventArgs);
        } else {
            global $post;
            $productId = $post->ID ;
        }

        $id = Helpers\getWooFullItemId( $productId );
        $params = array(
            'ecomm_prodid'=> $id,
            'ecomm_pagetype'=> 'product',
            'event_category'  => 'ecommerce',
            'value' => getWooProductPriceToDisplay( $productId ,$quantity,$customProductPrice),
            'items'           => array(
                array(
                    'id'       => $id,
                    'google_business_vertical' => $this->googleBusinessVertical,
                ),
            ),
        );


        return array(
            'name'  => 'view_item',
            'data'  => $params,
            'ids'   => Helpers\getConversionIDs( 'woo_view_content' ),
            'delay' => (int) PYS()->getOption( 'woo_view_content_delay' ),
        );

    }

    private function getWooAddToCartOnButtonClickEventParams( $args ) {
        $product_id = $args['productId'];
        $quantity = $args['quantity'];

        $product = wc_get_product( $product_id );
        if(!$product) return false;

        $customProductPrice = getWfcProductSalePrice($product,$args);

        $price = getWooProductPriceToDisplay( $product_id, $quantity ,$customProductPrice);
        $contentId = Helpers\getWooFullItemId( $product_id );


        if(is_home() || is_front_page()) {
            $ecomm_pagetype = "home";
        }elseif(is_shop()) {
            $ecomm_pagetype = "shop";
        }elseif(is_cart()) {
            $ecomm_pagetype = "cart";
        }elseif(is_single()) {
            $ecomm_pagetype = "product";
        }elseif(is_category()) {
            $ecomm_pagetype = "category";
        } else {
            $ecomm_pagetype = get_post_type();
        }
        $params = array(
            'ecomm_prodid' => $contentId,
            'ecomm_pagetype'=> $ecomm_pagetype,
            'event_category'  => 'ecommerce',
            'value' => $price,
        );

        $product_ids = array();
        $items = array();

        $isGrouped = $product->get_type() == "grouped";
        if($isGrouped) {
            $product_ids = $product->get_children();
        } else {
            $product_ids[] = $product_id;
        }

        foreach ($product_ids as $child_id) {
            $childProduct = wc_get_product($child_id);
            if($childProduct->get_type() == "variable" && $isGrouped) {
                continue;
            }
            $childContentId = Helpers\getWooFullItemId( $child_id );
            $items[] = array(
                'id'       => $childContentId,
                'google_business_vertical' => $this->googleBusinessVertical,
            );
        }
        $params['items'] = $items;

        $data = array(
            'ids' => Helpers\getConversionIDs( 'woo_add_to_cart' ),
            'params'  => $params,
        );


        if($product->get_type() == 'grouped') {
            $grouped = array();
            foreach ($product->get_children() as $childId) {
                $grouped[$childId] = array(
                    'content_id' => Helpers\getWooFullItemId( $childId ),
                    'price' => getWooProductPriceToDisplay( $childId )
                );
            }
            $data['grouped'] = $grouped;
        }

        return $data;

    }

    /**
     * @param SingleEvent $event
     * @return boolean
     */
    private function getWooAddToCartOnCartEventParams(&$event) {

        if ( ! $this->getOption( 'woo_add_to_cart_enabled' ) ) {
            return false;
        }
        $data = [
            'name' => 'add_to_cart',
        ];
        $params = $this->getWooEventCartParams($event);

        if(is_home() || is_front_page()) {
            $ecomm_pagetype = "home";
        }elseif(is_shop()) {
            $ecomm_pagetype = "shop";
        }elseif(is_cart()) {
            $ecomm_pagetype = "cart";
        }elseif(is_single()) {
            $ecomm_pagetype = "product";
        }elseif(is_category()) {
            $ecomm_pagetype = "category";
        } else {
            $ecomm_pagetype = get_post_type();
        }

        $params['ecomm_prodid'] = array_column($params['items'],'id');
        $params['ecomm_pagetype'] = $ecomm_pagetype;
        $params['event_category']  = 'ecommerce';

        $data['ids'] = Helpers\getConversionIDs( 'woo_add_to_cart' );
        $event->addPayload($data);
        $event->addParams($params);


        return  true;
    }



    private function getWooAffiliateEventParams( $product_id,$quantity ) {

        if ( ! $this->getOption( 'woo_affiliate_enabled' ) ) {
            return false;
        }

        $product = get_post( $product_id );

        $params = array(
            'event_category'  => 'ecommerce',
            'items'           => array(
                array(
                    'id'       => Helpers\getWooFullItemId( $product_id ),
                    'name'     => $product->post_title,
                    'category' => implode( '/', getObjectTerms( 'product_cat', $product_id ) ),
                    'quantity' => $quantity,
                    'price'    => getWooProductPriceToDisplay( $product_id, $quantity ),
                ),
            ),
        );

        return array(
            'params'  => $params,
        );

    }


    /**
     * @param SingleEvent $event
     * @return array|false
     */
    private function getWooPurchaseEventParams(&$event) {

        if ( ! $this->getOption( 'woo_purchase_enabled' ) || empty($event->args['order_id']) ) {
            return false;
        }
        $tax = 0;
        $withTax = 'incl' === get_option( 'woocommerce_tax_display_cart' );
        foreach ( $event->args['products'] as $product_data ) {

            $product_id = Helpers\getWooProductDataId( $product_data );
            $content_id = Helpers\getWooFullItemId( $product_id );

            $item = array(
                'id'       => $content_id,
                'google_business_vertical' => $this->googleBusinessVertical,
            );

            $items[] = $item;
            $tax += (float) $product_data['total_tax'];
        }

        if(empty($items)) return false; // order is empty

        $total = getWooEventOrderTotal($event);

        $tax += (float) $event->args['shipping_tax'];

        $params = array(
            'ecomm_prodid'      => array_column($items,'id'),
            'ecomm_pagetype'    => "purchase confirmation",
            'ecomm_totalvalue'  => $total,
            'event_category'    => 'ecommerce',
            'transaction_id'    => $event->args['order_id'],
            'value'             => $total,
            'currency'          => $event->args['currency'],
            'items'             => $items,
            'tax'               => pys_round($tax),
            'shipping'          => $event->args['shipping'],
            'coupon'            => $event->args['coupon_name'],
        );

        $event->addParams($params);
        $event->addPayload([
            'name' => 'purchase',
            'ids' => Helpers\getConversionIDs( 'woo_purchase' ),
        ]);

        return true;
    }

    /**
     * @param SingleEvent $event
     * @return array
     */
    private function getWooEventCartParams( $event ) {
        $items = [];


        foreach ($event->args['products'] as $product) {
            $product_id = Helpers\getWooEventCartItemId( $product );

            if(!$product_id) continue;

            $content_id = Helpers\getWooFullItemId( $product_id );
            $item = array(
                'id'       => $content_id,
                'google_business_vertical' => $this->googleBusinessVertical,
            );
            $items[] = $item;

        }
        $params = array(
            'event_category' => 'ecommerce',
            'value' => getWooEventCartTotal($event),
            'items' => $items,
            'coupon' => $event->args['coupon']
        );
        return $params;
    }
    /**
     * @deprecated
     * @param string $context
     * @return array
     */
    private function getWooCartParams( $context = 'cart' ) {

        $items = array();
        $total_value = 0;

        foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

            $product_id = Helpers\getWooCartItemId( $cart_item );
            if(!$product_id) continue;
            $content_id = Helpers\getWooFullItemId( $product_id );
            $price = getWooProductPriceToDisplay( $product_id );
            $item = array(
                'id'       => $content_id,
                'google_business_vertical' => $this->googleBusinessVertical,
            );

            $items[] = $item;
            $total_value += $price * $cart_item['quantity'];

        }
        $coupons =  WC()->cart->get_applied_coupons();
        if ( count($coupons) > 0 ) {
            $coupon = $coupons[0];
        } else {
            $coupon = null;
        }

        $params = array(
            'event_category' => 'ecommerce',
            'value' => $total_value,
            'items' => $items,
            'coupon' => $coupon
        );

        return $params;

    }


    private function getEddViewContentEventParams() {
        global $post;

        if ( ! $this->getOption( 'edd_view_content_enabled' ) ) {
            return false;
        }

        $price = getEddDownloadPriceToDisplay( $post->ID );
        $id = Helpers\getEddDownloadContentId($post->ID);
        $params = array(
            'ecomm_prodid'=> $id,
            'ecomm_pagetype'=> 'product',
            'event_category'  => 'ecommerce',
            'value' => $price,
            'items'           => array(
                array(
                    'id'       => $id,
                    'google_business_vertical' => $this->googleBusinessVertical,
                ),
            ),
        );

        return array(
            'name'  => 'view_item',
            'ids' => Helpers\getConversionIDs( 'edd_view_content' ),
            'data'  => $params,
            'delay' => (int) PYS()->getOption( 'edd_view_content_delay' ),
        );

    }

    private function getEddAddToCartOnButtonClickEventParams( $download_id ) {


        // maybe extract download price id
        if ( strpos( $download_id, '_') !== false ) {
            list( $download_id, $price_index ) = explode( '_', $download_id );
        } else {
            $price_index = null;
        }

        $price = getEddDownloadPriceToDisplay( $download_id, $price_index );

        if(is_home()) {
            $ecomm_pagetype = "home";
        }elseif(is_category()) {
            $ecomm_pagetype = "category";
        } else {
            $ecomm_pagetype = get_post_type();
        }
        $contentId = Helpers\getEddDownloadContentId($download_id);
        $params = array(
            'ecomm_prodid' => $contentId,
            'ecomm_pagetype'=> $ecomm_pagetype,
            'event_category'  => 'ecommerce',
            'value' => $price,
            'items'           => array(
                array(
                    'id'       => $contentId,
                    'google_business_vertical' => $this->googleBusinessVertical,
                ),
            ),
        );

        return array(
            'ids' => Helpers\getConversionIDs( 'edd_add_to_cart' ),
            'params' => $params,
        );

    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function setEddCartEventParams(&$event) {
        $params = [
            'ecomm_pagetype'=> "purchase confirmation",
            'event_category' => 'ecommerce',
        ];
        $data = [];
        switch ($event->getId()) {
            case 'edd_add_to_cart_on_checkout_page' : {
                if(! $this->getOption( 'edd_add_to_cart_enabled' )) return false;
                $data['name'] = 'add_to_cart';
                $data['ids'] = Helpers\getConversionIDs( 'edd_add_to_cart' );
            }break;
            case 'edd_purchase' : {
                if(! $this->getOption( 'edd_purchase_enabled' )) return false;
                $data['name'] = 'purchase';
                $params['coupon'] = $event->args['coupon'];
                $params['transaction_id'] = $event->args['order_id'];
                $params['currency'] = edd_get_currency();
                $data['ids'] = Helpers\getConversionIDs( 'edd_purchase' );
            }break;
        }

        $items = array();
        $total = 0;
        $total_as_is = 0;
        $tax = 0;
        $include_tax = PYS()->getOption( 'edd_tax_option' ) == 'included';

        foreach ( $event->args['products'] as  $product ) {
            $download_id   = (int) $product['product_id'];

            if ( $event->getId() == 'edd_purchase' ) {

                if ( $include_tax ) {
                    $total += $product['subtotal'] + $product['tax'] - $product['discount'];
                } else {
                    $total += $product['subtotal'] - $product['discount'];
                }
                $tax += $product['tax'];
                $total_as_is += $product['price'];
            } else {
                $total += getEddDownloadPriceToDisplay( $download_id,$product['price_index'] );
                $total_as_is += edd_get_cart_item_final_price( $product['cart_item_key']  );
            }

            $items[] = [
                    'id'       => Helpers\getEddDownloadContentId($download_id),
                    'google_business_vertical' => $this->googleBusinessVertical,
//				'variant'  => $variation_name,
            ];
        }
        $params['value'] =  $total;
        $params['items'] =  $items;

        if ( $event->getId() == 'edd_purchase' ) {

            // calculate value
            if( PYS()->getOption( 'edd_event_value' ) == 'custom' ) {
                $params['value']  = $total;
            } else {
                $params['value']  = $total_as_is;
            }

            $params['tax'] = $tax;
            $params['ecomm_prodid'] = array_column($items,'id');
            $params['ecomm_totalvalue'] = $total;
        }

        $event->addParams($params);
        $event->addPayload($data);

        return true;
    }


    private function getEddViewCategoryEventParams() {
        global $posts;

        if ( ! $this->getOption( 'edd_view_category_enabled' ) ) {
            return false;
        }

        $term = get_term_by( 'slug', get_query_var( 'term' ), 'download_category' );
        if(!$term) return false;
        $parent_ids = get_ancestors( $term->term_id, 'download_category', 'taxonomy' );

        $download_categories = array();
        $download_categories[] = $term->name;

        foreach ( $parent_ids as $term_id ) {
            $parent_term = get_term_by( 'id', $term_id, 'download_category' );
            $download_categories[] = $parent_term->name;
        }

        $list_name = implode( '/', array_reverse( $download_categories ) );

        $items = array();
        $total_value = 0;

        for ( $i = 0; $i < count( $posts ); $i ++ ) {

            $item = array(
                'id'            => Helpers\getEddDownloadContentId($posts[ $i ]->ID),
                'google_business_vertical' => $this->googleBusinessVertical,
            );

            $items[] = $item;
            $total_value += getEddDownloadPriceToDisplay( $posts[ $i ]->ID );

        }

        $params = array(
            'event_category' => 'ecommerce',
            'event_label'    => $list_name,
            'value'          => $total_value,
            'items'          => $items,
        );

        return array(
            'name'  => 'view_item_list',
            'ids' => Helpers\getConversionIDs( 'edd_view_category' ),
            'data'  => $params,
        );

    }

    function registerProductMetaBox () {

        if ( current_user_can( 'manage_pys' ) ) {
            add_meta_box( 'pys-gads-box', 'PYS Google Ads',
                array( $this, 'render_meta_box' ),
                "product","side" );

        }
    }

    function render_meta_box () {
        wp_nonce_field( 'pys_save_meta_box', '_pys_nonce' );
        include 'views/html-meta-box.php';
    }


    /**
     * @param $post_id
     * @param \WP_Post $post
     * @param $update
     */
    function saveProductMetaBox($post_id, $post, $update) {
        if ( ! isset( $_POST['_pys_nonce'] ) || ! wp_verify_nonce( $_POST['_pys_nonce'], 'pys_save_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $data = $_POST['pys_ads_conversion_label'];

        $meta = array(
            'enable' => isset( $data['enable'] ),
            'label'  => isset( $data['label'] ) ? trim( $data['label'] ) : '',
            'id'     => isset( $data['id'] ) ? trim( $data['id'] ) : '',
        );

        update_post_meta($post_id,"_pys_conversion_label_settings",$meta);
    }
    function output_meta_tag() {
        if(EventsWcf()->isEnabled() && isWcfStep()) {
            $tag = $this->getOption( 'wcf_verify_meta_tag' );
            if(!empty($tag)) {
                echo $tag;
                return;
            }
        }
        $metaTags = (array) $this->getOption( 'verify_meta_tag' );
        foreach ($metaTags as $tag) {
            echo $tag;
        }
    }
}

/**
 * @return GoogleAds
 */
function Ads() {
	return GoogleAds::instance();
}

Ads();
