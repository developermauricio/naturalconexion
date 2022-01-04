<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/** @noinspection PhpIncludeInspection */
require_once PYS_PATH . '/modules/google_analytics/function-helpers.php';

use PixelYourSite\GA\Helpers;

require_once PYS_PATH . '/modules/google_analytics/function-collect-data-4v.php';

class GA extends Settings implements Pixel {
	
	private static $_instance;
	
	private $configured;
    private $checkout_step = 2;
	/** @var array $wooOrderParams Cached WooCommerce Purchase and AM events params */
	private $wooOrderParams = array();
	
	public static function instance() {
		
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
		
	}
	
    public function __construct() {
		
        parent::__construct( 'ga' );
	
	    $this->locateOptions(
		    PYS_PATH . '/modules/google_analytics/options_fields.json',
		    PYS_PATH . '/modules/google_analytics/options_defaults.json'
	    );
	    
	    add_action( 'pys_register_pixels', function( $core ) {
		    /** @var PYS $core */
		    $core->registerPixel( $this );
	    } );
    
        add_action( 'wp_head', array( $this, 'outputOptimizeSnippet' ), 1 );
    }
	
	public function enabled() {
		return $this->getOption( 'enabled' );
	}
	
	public function configured() {

        $license_status = PYS()->getOption( 'license_status' );
        $tracking_id = $this->getAllPixels();
        $disabledPixel =  apply_filters( 'pys_pixel_disabled', '', $this->getSlug() );

        $this->configured = $this->enabled()
                            && ! empty( $license_status ) // license was activated before
                            && count( $tracking_id ) > 0
                            && $disabledPixel != '1' && $disabledPixel != 'all';

		
		return $this->configured;

	}
	
	public function getPixelIDs() {

        if(EventsWcf()->isEnabled() && isWcfStep()) {
            $ids = $this->getOption( 'wcf_pixel_id' );
            if(!empty($ids))
                return [$ids];
        }

		$ids = (array) $this->getOption( 'tracking_id' );
        if(count($ids) == 0|| empty($ids[0])) {
            return apply_filters("pys_ga_ids",[]);
        } else {
            return apply_filters("pys_ga_ids",(array) reset( $ids )); // return first id only
        }

		
	}

    public function getPixelDebugMode() {

        $flags = (array) $this->getOption( 'is_enable_debug_mode' );

        if ( isSuperPackActive() && SuperPack()->getOption( 'enabled' ) && SuperPack()->getOption( 'additional_ids_enabled' ) ) {
            return $flags;
        } else {
            return (array) reset( $flags ); // return first id only
        }
    }

    
    public function getPixelOptions() {
        
        return array(
            'trackingIds'                   => $this->getAllPixels(),
            'enhanceLinkAttr'               => $this->getOption( 'enhance_link_attribution' ),
            'anonimizeIP'                   => $this->getOption( 'anonimize_ip' ),
            'retargetingLogic'              => PYS()->getOption( 'google_retargeting_logic' ),
            'crossDomainEnabled'            => $this->getOption( 'cross_domain_enabled' ),
            'crossDomainAcceptIncoming'     => $this->getOption( 'cross_domain_accept_incoming' ),
            'crossDomainDomains'            => $this->getOption( 'cross_domain_domains' ),
            'wooVariableAsSimple'           => $this->getOption( 'woo_variable_as_simple' ),
            'isDebugEnabled'                => $this->getPixelDebugMode(),
            'disableAdvertisingFeatures'    => $this->getOption( 'disable_advertising_features' ),
            'disableAdvertisingPersonalization' => $this->getOption( 'disable_advertising_personalization' )
        );
    }

    /**
     * Create pixel event and fill it
     * @param SingleEvent $event
     */
    public function generateEvents($event) {
        $pixelEvents = [];
        $disabledPixel =  apply_filters( 'pys_pixel_disabled', false, $this->getSlug() );

        if($disabledPixel == '1' || $disabledPixel == 'all') return [];


        if($event->getId() == 'woo_remove_from_cart') {
            $product_id = $event->args['item']['product_id'];
            add_filter('pys_conditional_post_id', function($id) use ($product_id) { return $product_id; });
        }

        $pixelIds = $this->getAllPixelsForEvent($event);

        if($event->getId() == 'woo_remove_from_cart') {
            remove_all_filters('pys_conditional_post_id');
        }

         if($event->getId() == 'custom_event'){
            $preselectedPixel = $event->args->ga_pixel_id;

            if($preselectedPixel == 'all') {
                if(count($pixelIds) > 0) {
                    $preselectedPixel = $pixelIds[0];
                }
            }

             if(!in_array($preselectedPixel,$pixelIds) || $preselectedPixel == $disabledPixel) {
                 return []; // not fire event if pixel id was disabled or deleted
             }

             $pixelIds = [$preselectedPixel];
         } else {
             // filter disabled pixels
             if(!empty($disabledPixel)) {
                 foreach ($pixelIds as $key => $value) {
                     if($value == $disabledPixel) {
                         array_splice($pixelIds,$key,1);
                     }
                 }
             }
         }

        // if list of pixels are empty return empty array
        if(count($pixelIds) > 0) {
            $pixelEvent = clone $event;
            if($this->addParamsToEvent($pixelEvent)) {
                $pixelEvent->addPayload([ 'trackingIds' => $pixelIds ]);
                $pixelEvents[] = $pixelEvent;
            }

        }

        $listOfEddEventWithProducts = ['edd_add_to_cart_on_checkout_page','edd_initiate_checkout','edd_purchase','edd_frequent_shopper','edd_vip_client','edd_big_whale'];
        $listOfWooEventWithProducts = ['woo_initiate_checkout_progress_o','woo_initiate_checkout_progress_e','woo_initiate_checkout_progress_l','woo_initiate_checkout_progress_f','woo_purchase','woo_initiate_checkout','woo_paypal','woo_add_to_cart_on_checkout_page','woo_add_to_cart_on_cart_page'];
        $isWooEventWithProducts = in_array($event->getId(),$listOfWooEventWithProducts);
        $isEddEventWithProducts = in_array($event->getId(),$listOfEddEventWithProducts);

        if($isWooEventWithProducts || $isEddEventWithProducts)
        {
            if(isSuperPackActive('3.0.0')
                && SuperPack()->getOption( 'enabled' )
                && SuperPack()->getOption( 'additional_ids_enabled' ))
            {
                $additionalPixels = SuperPack()->getGaAdditionalPixel();
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
                            $additionalEvent->addPayload([ 'trackingIds' => [$_pixel->pixel] ]);
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
            case "signal_adsense": {
                //not active
            }break;
            case "signal_user_signup":
            case "signal_download":
            case "signal_page_scroll":
            case "signal_time_on_page":
            case "signal_tel":
            case "signal_email":
            case "signal_form":
            case "signal_comment":
            case "signal_watch_video":
            case "signal_click" : {
                $isActive = $this->getOption('signal_events_enabled');
                $event->addParams(array('non_interaction'=>$this->getOption("signal_events_non_interactive")));
            }break;

            case 'woo_frequent_shopper':
            case 'woo_vip_client':
            case 'woo_big_whale':{
                $eventData =  $this->getWooAdvancedMarketingEventParams( $event->getId() );
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case 'woo_view_content': {
                $eventData =  $this->getWooViewContentEventParams($event->args);
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case 'woo_view_item_list':
            {
                $eventData = $this->getWooViewCategoryEventParams();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case 'woo_view_item_list_single':
            {
                $eventData = $this->getWooViewItemListSingleParams();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case "woo_view_item_list_search":{
                $eventData =  $this->getWooViewItemListSearch();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;

            case "woo_view_item_list_shop":{
                $eventData =  $this->getWooViewItemListShop();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;

            case "woo_view_item_list_tag":{
                $eventData =  $this->getWooViewItemListTag();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case 'woo_add_to_cart_on_cart_page':
            case 'woo_add_to_cart_on_checkout_page':{
                $isActive =  $this->setWooAddToCartOnCartEventParams($event);
            }break;
            case 'woo_initiate_checkout':{
                $isActive =  $this->setWooInitiateCheckoutEventParams($event);

            }break;
            case 'woo_purchase':{
                $isActive =  $this->getWooPurchaseEventParams($event);

            }break;
            case 'woo_initiate_set_checkout_option':{
                $eventData =  $this->getWooSetСheckoutOptionEventParams();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;

            case 'woo_initiate_checkout_progress_f':
            case 'woo_initiate_checkout_progress_l':
            case 'woo_initiate_checkout_progress_e':
            case 'woo_initiate_checkout_progress_o':{
                $isActive =  $this->setWooCheckoutProgressEventParams($event);
            }break;
            case 'woo_remove_from_cart':{
                $eventData =  $this->getWooRemoveFromCartParams( $event->args['item'] );
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);

                }
            }break;
            case 'woo_paypal':{
                $isActive =  $this->setWooPayPalEventParams($event);

            }break;
            case "woo_select_content_category":
                $isActive = $this->getWooSelectContent("category",$event);break;
            case "woo_select_content_single":
                $isActive = $this->getWooSelectContent("single",$event);break;
            case "woo_select_content_search":
                $isActive = $this->getWooSelectContent("search",$event);break;
            case "woo_select_content_shop":
                $isActive = $this->getWooSelectContent("shop",$event);break;
            case "woo_select_content_tag":
                $isActive = $this->getWooSelectContent("tag",$event);break;
                //Edd
            case 'edd_view_content': {
                    $eventData = $this->getEddViewContentEventParams();
                    if ($eventData) {
                        $isActive = true;
                        $this->addDataToEvent($eventData, $event);
                    }
                }break;
            case 'edd_add_to_cart_on_checkout_page':  {
                $isActive = $this->setEddCartEventParams($event);

                }break;

            case 'edd_remove_from_cart': {
                    $eventData =  $this->getEddRemoveFromCartParams( $event->args['item'] );
                    if ($eventData) {
                        $isActive = true;
                        $this->addDataToEvent($eventData, $event);
                    }
                }break;

            case 'edd_view_category': {
                    $eventData = $this->getEddViewCategoryEventParams();
                    if ($eventData) {
                        $isActive = true;
                        $this->addDataToEvent($eventData, $event);
                    }
                }break;

            case 'edd_initiate_checkout': {
                $isActive = $this->setEddCartEventParams($event);

                }break;

            case 'edd_purchase': {
                $isActive = $this->setEddCartEventParams($event);

                }break;

            case 'edd_frequent_shopper':
            case 'edd_vip_client':
            case 'edd_big_whale': {
                $isActive = $this->setEddCartEventParams($event);
            }break;
            case 'search_event': {
                $eventData =  $this->getSearchEventData();
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }

            }break;

            case 'custom_event': {
                $eventData = $this->getCustomEventData($event);
                if ($eventData) {
                    $isActive = true;
                    $this->addDataToEvent($eventData, $event);
                }
            }break;
            case 'woo_add_to_cart_on_button_click': {
                if (  $this->getOption( 'woo_add_to_cart_enabled' ) && PYS()->getOption( 'woo_add_to_cart_on_button_click' ) ) {
                    $isActive = true;
                    if(isset($event->args['productId'])) {
                        $eventData =  $this->getWooAddToCartOnButtonClickEventParams(  $event->args );

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
                        $eventData =  $this->getWooAffiliateEventParams( $productId,$quantity );
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
                        $event->addParams($eventData);
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

        if($isActive) {
            if( !isset($event->payload['trackingIds'])) {
                $event->payload['trackingIds'] = $this->getAllPixelsForEvent($event);
            }
        }
        return $isActive;
    }

    private function addDataToEvent($eventData,&$event) {
        $params = $eventData["data"];
        unset($eventData["data"]);
        //unset($eventData["name"]);
        $event->addParams($params);
        $event->addPayload($eventData);
    }

	public function getEventData( $eventType, $args = null ) {
        return false;
	}
	
	public function outputNoScriptEvents() {
		
		if ( ! $this->configured() ) {
			return;
		}
		
		$eventsManager = PYS()->getEventsManager();
		
		foreach ( $eventsManager->getStaticEvents( 'ga' ) as $eventName => $events ) {
			foreach ( $events as $event ) {
				foreach ( $this->getAllPixels() as $pixelID ) {
					
					$args = array(
						'v'   => 1,
						'tid' => $pixelID,
						't'   => 'event',
						'aip' => $this->getOption( 'anonimize_ip' ),
					);
					
					//@see: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#ec
					if ( isset( $event['params']['event_category'] ) ) {
						$args['ec'] = urlencode( $event['params']['event_category'] );
					}
					
					if ( isset( $event['params']['event_action'] ) ) {
						$args['ea'] = urlencode( $event['params']['event_action'] );
					}
					
					if ( isset( $event['params']['event_label'] ) ) {
						$args['el'] = urlencode( $event['params']['event_label'] );
					}

					if ( isset( $event['params']['value'] ) ) {
						$args['ev'] = urlencode( $event['params']['value'] );
					}
					
					if ( isset( $event['params']['items'] ) && is_array( $event['params']['items'] )) {
						
						foreach ( $event['params']['items'] as $key => $item ) {
                            if(isset($item['id']))
							    @$args["pr{$key}id" ] = urlencode( $item['id'] );
                            if(isset($item['name']))
							    @$args["pr{$key}nm"] = urlencode( $item['name'] );
                            if(isset($item['category']))
							    @$args["pr{$key}ca"] = urlencode( $item['category'] );
							//@$args["pr{$key}va"] = urlencode( $item['id'] ); // variant
                            if(isset($item['price']))
							    @$args["pr{$key}pr"] = urlencode( pys_round($item['price']) );
                            if(isset($item['quantity']))
							    @$args["pr{$key}qt"] = urlencode( $item['quantity'] );

						}
						
						//@todo: not tested
						//https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#pa
						$args["pa"] = 'detail'; // required

					}
                    $src = add_query_arg( $args, 'https://www.google-analytics.com/collect' );
                    $src = str_replace("[","%5B",$src);
                    $src = str_replace("]","%5D",$src);

					// ALT tag used to pass ADA compliance
					printf( '<noscript><img height="1" width="1" style="display: none;" src="%s" alt="google_analytics"></noscript>',
						$src );
					
					echo "\r\n";
					
				}
			}
		}
		
	}
	
	public function outputOptimizeSnippet() {
	    
	    $optimize_id = $this->getOption( 'optimize_id' );
	    
        if ( $this->configured() && $this->getOption( 'optimize_enabled' ) && ! empty( $optimize_id ) ) {
            $optimizeError ="";
            ob_start();

            if($this->getOption( 'enable_anti_flicker' )):
                $optimizeError='onerror="dataLayer.hide.end && dataLayer.hide.end()"';
                $timeOut = $this->getOption( 'anti_flicker_timeout' );
                ?>
                <style>.async-hide { opacity: 0 !important} </style>
                <script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.start=1*new Date;
                h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
                (a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);h.timeout=c;
                })(window,document.documentElement,'async-hide','dataLayer',<?=$timeOut?>,
                {'<?=$optimize_id?>':true});</script>
            <?php endif; ?>

            <script async src="https://www.googleoptimize.com/optimize.js?id=<?=$optimize_id?>" <?=$optimizeError?>></script>
            <?php
    
            $snippet = ob_get_clean();
          //  $snippet = sprintf( $snippet, $optimize_id,$optimize_id );
            
            echo $snippet;
        }
        
    }
	
	private function getPageViewEventParams() {
		
		if ( PYS()->getEventsManager()->doingAMP ) {
			
			return array(
				'name' => 'PageView',
				'data' => array(),
			);
			
		} else {
			return false; // PageView is fired by tag itself
		}
		
	}

	private function getSearchEventData() {
		global $posts;

		if ( ! $this->getOption( 'search_event_enabled' ) ) {
			return false;
		}

		$params['event_category'] = 'WordPress Search';
		$params['search_term']    = empty( $_GET['s'] ) ? null : $_GET['s'];

		if ( isWooCommerceActive() && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {
			$params['event_category'] = 'WooCommerce Search';
		}
		
		$params['non_interaction'] = $this->getOption( 'search_event_non_interactive' );
		
		$product_ids = array();
		$total_value = 0;
		
		for ( $i = 0; $i < count( $posts ) ; $i ++ ) {
			
			if ( $posts[ $i ]->post_type == 'product' ) {
				$total_value += getWooProductPriceToDisplay( $posts[ $i ]->ID );
			} elseif ( $posts[ $i ]->post_type == 'download' ) {
				$total_value += getEddDownloadPriceToDisplay( $posts[ $i ]->ID );
			} else {
				continue;
			}
			
			$product_ids[] = $posts[ $i ]->ID;
			
		}

		$dyn_remarketing = array(
			'product_id'  => $product_ids,
			'page_type'   => 'search',
			'total_value' => $total_value,
		);
		
		$dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
		$params          = array_merge( $params, $dyn_remarketing );

		return array(
			'name'  => 'search',
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
		$ga_action = $customEvent->getGoogleAnalyticsAction();

		if ( ! $customEvent->isGoogleAnalyticsEnabled() || empty( $ga_action ) ) {
			return false;
		}

		if($customEvent->isGaV4()) {
            $params = $customEvent->getGaParams();

            $customParams = $customEvent->getGACustomParams();
            foreach ($customParams as $item)
                $params[$item['name']]=$item['value'];

        } else {
            $params = array(
                'event_category'  => $customEvent->ga_event_category,
                'event_label'     => $customEvent->ga_event_label,
                'value'           => $customEvent->ga_event_value,
            );
        }
        $params['non_interaction'] = $customEvent->ga_non_interactive;
		
		// SuperPack Dynamic Params feature
		$params = apply_filters( 'pys_superpack_dynamic_params', $params, 'ga' );

		return array(
			'name'  => $customEvent->getGoogleAnalyticsAction(),
			'data'  => $params,
			'delay' => $customEvent->getDelay(),

		);

	}

	private function getWooViewItemListTag() {
        global $posts;

        if ( ! $this->getOption( 'woo_view_item_list_enabled' ) ) {
            return false;
        }

        $list_name =  single_tag_title( '', false )." - Tag";

        $items = array();

        for ( $i = 0; $i < count( $posts )&& $i < 10; $i ++ ) {

            if ( $posts[ $i ]->post_type !== 'product' ) {
                continue;
            }

            $item = array(
                'id'            => Helpers\getWooProductContentId($posts[ $i ]->ID),
                'name'          => $posts[ $i ]->post_title,
                'category'      => implode( '/', getObjectTerms( 'product_cat', $posts[ $i ]->ID ) ),
                'quantity'      => 1,
                'price'         => getWooProductPriceToDisplay( $posts[ $i ]->ID ),
                'list_position' => $i + 1,
                'list_name'      => $list_name,
            );

            $items[] = $item;

        }

        $params = array(
            'event_category'  => 'ecommerce',
            'event_label'     => $list_name,
            'items'           => $items,
        );

        return array(
            'name'  => 'view_item_list',
            'data'  => $params,
        );
    }

	private function getWooViewItemListShop() {
        /**
         * @var \WC_Product $product
         * @var $related_products \WC_Product[]
         */

        global $posts;

        if ( ! $this->getOption( 'woo_view_item_list_enabled' ) ) {
            return false;
        }


        $list_name = woocommerce_page_title(false);

        $items = array();
        $i = 0;

        foreach ( $posts as $post) {
            if( $post->post_type != 'product') continue;
            $item = array(
                'id'            => Helpers\getWooProductContentId($post->ID),
                'name'          => $post->post_title ,
                'category'      => implode( '/', getObjectTerms( 'product_cat', $post->ID ) ),
                'quantity'      => 1,
                'price'         => getWooProductPriceToDisplay( $post->ID ),
                'list_position' => $i + 1,
                'list_name'     => $list_name,
            );

            $items[] = $item;
            $i++;
        }

        $params = array(
            'event_category'  => 'ecommerce',
            'event_label'     => $list_name,
            'items'           => $items,
        );


        return array(
            'name'  => 'view_item_list',
            'data'  => $params,
        );
    }

    private function getWooViewItemListSearch() {
        /**
         * @var \WC_Product $product
         * @var $related_products \WC_Product[]
         */

        global $posts;

        if ( ! $this->getOption( 'woo_view_item_list_enabled' ) ) {
            return false;
        }



        $list_name = "WooCommerce Search";

        $items = array();
        $i = 0;

        foreach ( $posts as $post) {
            if( $post->post_type != 'product') continue;
            $item = array(
                'id'            => Helpers\getWooProductContentId($post->ID),
                'name'          => $post->post_title ,
                'category'      => implode( '/', getObjectTerms( 'product_cat', $post->ID ) ),
                'quantity'      => 1,
                'price'         => getWooProductPriceToDisplay( $post->ID ),
                'list_position' => $i + 1,
                'list_name'          => $list_name,
            );

            $items[] = $item;
            $i++;
        }

        $params = array(
            'event_category'  => 'ecommerce',
            'event_label'     => $list_name,
            'items'           => $items,
        );


        return array(
            'name'  => 'view_item_list',
            'data'  => $params,
        );
    }

    /**
     * @param string $type
     * @param SingleEvent $event
     * @return bool
     */
    private function getWooSelectContent($type,&$event) {

	    if(!$this->getOption('woo_select_content_enabled')) {
	        return false;
        }

        $event->addParams( array(
            'event_category'  => 'ecommerce',
            'content_type'     => "product",
        ));
        $event->addPayload( array(
            'name'=>"select_content"
        ));

        return true;
    }


	private function getWooViewItemListSingleParams() {
        /**
         * @var \WC_Product $product
         * @var $related_products \WC_Product[]
         */
        $product = wc_get_product( get_the_ID() );

	    if ( !$product || ! $this->getOption( 'woo_view_item_list_enabled' ) ) {
            return false;
        }

        $related_products = array();

        $args = array(
            'posts_per_page' => 4,
            'columns'        => 4,
        );
        $args = apply_filters( 'woocommerce_output_related_products_args', $args );

        $ids =  Helpers\custom_wc_get_related_products( get_the_ID(), $args['posts_per_page'] );
        $ids = array_slice($ids, 0, 10);
        foreach ( $ids as $id) {
            $rel = wc_get_product($id);
            if($rel) {
                $related_products[] = $rel;
            }
        }



        $list_name = $product->get_name()." - Related products";

        $items = array();
        $i = 0;

        foreach ( $related_products as $relate) {

            $item = array(
                'id'            => Helpers\getWooProductContentId($relate->get_id()),
                'name'          => $relate->get_title(),
                'category'      => implode( '/', getObjectTerms( 'product_cat', $relate->get_id() ) ),
                'quantity'      => 1,
                'price'         => getWooProductPriceToDisplay( $relate->get_id() ),
                'list_position' => $i + 1,
                'list_name'     => $list_name,
            );

            $items[] = $item;
            $i++;
        }

        $params = array(
            'event_category'  => 'ecommerce',
            'event_label'     => $list_name,
            'items'           => $items,
            'non_interaction' => $this->getOption( 'woo_view_category_non_interactive' ),
        );


        return array(
            'name'  => 'view_item_list',
            'data'  => $params,
        );
    }

	private function getWooViewCategoryEventParams() {
		global $posts;

		if ( ! $this->getOption( 'woo_view_item_list_enabled' ) ) {
			return false;
		}
        
        $product_category = "";
		$term = get_term_by( 'slug', get_query_var( 'term' ), 'product_cat' );
		
		if ( $term ) {
            $product_category = $term->name;
        }

        $list_name =  $product_category." - Category";

		$items = array();

		for ( $i = 0; $i < count( $posts ) && $i < 10; $i ++ ) {
			
			if ( $posts[ $i ]->post_type !== 'product' ) {
				continue;
			}

			$item = array(
				'id'            => Helpers\getWooProductContentId($posts[ $i ]->ID),
				'name'          => $posts[ $i ]->post_title,
				'category'      => implode( '/', getObjectTerms( 'product_cat', $posts[ $i ]->ID ) ),
				'quantity'      => 1,
				'price'         => getWooProductPriceToDisplay( $posts[ $i ]->ID ),
				'list_position' => $i + 1,
				'list_name'          => $list_name,
			);

			$items[] = $item;

		}
		
		$params = array(
			'event_category'  => 'ecommerce',
			'event_label'     => $list_name,
			'items'           => $items,
			'non_interaction' => $this->getOption( 'woo_view_category_non_interactive' ),
		);

		return array(
			'name'  => 'view_item_list',
			'data'  => $params,
		);

	}
    /**
     * @param SingleEvent $event
     * @return false
     */
    function prepare_wcf_remove_from_cart(&$event) {
        if (  !$this->getOption( 'woo_remove_from_cart_enabled' )
            || empty($event->args['products'])
        ) {
            return false;
        }
        $product_data = $event->args['products'][0];
        $product_id = $product_data['id'];
        $content_id = Helpers\getWooProductContentId( $product_id );
        $price = getWooProductPriceToDisplay($product_id, $product_data['quantity'],$product_data['price']);
        $categories = implode( ', ', array_column($product_data['categories'],"name") );
        $variation_name = empty($product_data['variation_attr'])
            ? null
            : implode( '/', $product_data['variation_attr'] );
        $params = [
            'event_category'  => 'ecommerce',
            'currency'        => get_woocommerce_currency(),
            'non_interaction' => $this->getOption( 'woo_remove_from_cart_non_interactive' ),
            'items'           => [
                [
                    'id'       => $content_id,
                    'name'     => $product_data['name'],
                    'category' => $categories,
                    'quantity' => $product_data['quantity'],
                    'price'    => $price,
                    'variant'  => $variation_name
                ]
            ]
        ];
        $event->addParams($params);
        $event->addPayload([
            'name' => "remove_from_cart",
        ]);
        return true;
    }
    /**
     * @param SingleEvent $event
     * @return false
     */
    private function prepare_wcf_add_to_cart(&$event) {
        if (  !$this->getOption( 'woo_add_to_cart_enabled' )
            || empty($event->args['products'])
        ) {
            return false;
        }
        $content_ids        = array();
        $items              = array();
        $value = 0;
        foreach ($event->args['products'] as $product_data) {
            $product_id = $product_data['id'];
            $content_id = Helpers\getWooProductContentId( $product_id );
            $category = implode( ', ',array_column($product_data['categories'],"name"));
            $price = getWooProductPriceToDisplay( $product_id,$product_data['quantity'],$product_data['price'] );

            $item = array(
                'id'       => $content_id,
                'name'     => $product_data['name'],
                'category' => $category,
                'quantity' => $product_data['quantity'],
                'price'    => $price,
                'variant'  => empty($product_data['variation_attr']) ? null : implode("/", $product_data['variation_attr']),
            );

            $items[] = $item;
            $content_ids[] = $content_id;
            $value += $price;
        }

        $params = array(
            'event_category'  => 'ecommerce',
            'non_interaction' => $this->getOption( 'woo_add_to_cart_non_interactive' ),
            'items' => $items
        );

        $dyn_remarketing = array(
            'product_id'  => $content_ids,
            'page_type'   => 'cart',
            'total_value' => $value,
        );
        $dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
        $params = array_merge( $params, $dyn_remarketing );


        $event->addParams($params);

        $event->addPayload([
            'name'=>"add_to_cart"
        ]);
        return true;

    }
    /**
     * @param SingleEvent $event
     * @return false
     */
    private function getWcfViewContentEventParams(&$event)  {
        if ( ! $this->getOption( 'woo_view_content_enabled' )
            || empty($event->args['products'])
        ) {
            return false;
        }
        $product_data = $event->args['products'][0];
        $content_id = Helpers\getWooProductContentId($product_data['id']);
        $category = implode( ', ', array_column($product_data['categories'],"name") );
        $price = getWooProductPriceToDisplay( $product_data['id'],$product_data['quantity'],$product_data['price']);

        $params = array(
            'event_category'  => 'ecommerce',
            'items'           => array(
                array(
                    'id'       => $content_id,
                    'name'     => $product_data['name'],
                    'category' => $category,
                    'quantity' => $product_data['quantity'],
                    'price'    => $price,
                ),
            ),
            'non_interaction' => $this->getOption( 'woo_view_content_non_interactive' ),
        );

        $dyn_remarketing = array(
            'product_id'  => $content_id,
            'page_type'   => 'product',
            'total_value' => $price,
        );

        $dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
        $params = array_merge( $params, $dyn_remarketing );

        $event->addParams($params);

        $event->addPayload([
            'name'  => 'view_item',
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
            $product = wc_get_product($eventArgs['id']);
            $quantity = $eventArgs['quantity'];
            $customProductPrice = getWfcProductSalePrice($product,$eventArgs);
        } else {
            global $post;
            $product = wc_get_product( $post->ID );
        }
        if(!$product)  return false;

        $productId = Helpers\getWooProductContentId($product->get_id());
		$params = array(
			'event_category'  => 'ecommerce',
			'items'           => array(
				array(
					'id'       => $productId,
					'name'     => $product->get_name(),
					'category' => implode( '/', getObjectTerms( 'product_cat',$product->get_id() ) ),
					'quantity' => $quantity,
					'price'    => getWooProductPriceToDisplay( $product->get_id(),$quantity,$customProductPrice),
				),
			),
			'non_interaction' => $this->getOption( 'woo_view_content_non_interactive' ),
		);
		
		$dyn_remarketing = array(
			'product_id'  => $productId,
			'page_type'   => 'product',
			'total_value' => getWooProductPriceToDisplay( $product->get_id(),$quantity,$customProductPrice ),
		);
		
		$dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
		$params = array_merge( $params, $dyn_remarketing );

		return array(
			'name'  => 'view_item',
			'data'  => $params,
			'delay' => (int) PYS()->getOption( 'woo_view_content_delay' ),
		);

	}

	private function getWooAddToCartOnButtonClickEventParams($args) {

        $product_id = $args['productId'];
        $quantity = $args['quantity'];
        $contentId = Helpers\getWooProductContentId($product_id);
		$product = wc_get_product( $product_id );
        if(!$product) return false;


        $customProductPrice = getWfcProductSalePrice($product,$args);
		$params = array(
			'event_category'  => 'ecommerce',
			'non_interaction' => $this->getOption( 'woo_add_to_cart_non_interactive' ),
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
            $childContentId = Helpers\getWooProductContentId( $child_id );
            $price = getWooProductPriceToDisplay( $child_id, $quantity,$customProductPrice );

            if ( $childProduct->get_type() == 'variation' ) {
                $parentId = $childProduct->get_parent_id();
                $name = $childProduct->get_title();
                $category = implode( '/', getObjectTerms( 'product_cat', $parentId ) );
                $variation_name = implode("/", $childProduct->get_variation_attributes());
            } else {
                $name = $childProduct->get_name();
                $category = implode( '/', getObjectTerms( 'product_cat', $child_id ) );
                $variation_name = null;
            }

            $items[] =  array(
                    'id'       => $childContentId,
                    'name'     => $name,
                    'category' => $category,
                    'quantity' => $quantity,
                    'price'    => $price,
                    'variant'  => $variation_name,
                );
        }
        $params['items'] = $items;

		
		$dyn_remarketing = array(
			'product_id'  => $contentId,
			'page_type'   => 'cart',
			'total_value' => getWooProductPriceToDisplay( $product_id, $quantity ,$customProductPrice),
		);
		
		$dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
		$params = array_merge( $params, $dyn_remarketing );


        $data = array(
            'params'  => $params,
        );

        if($product->get_type() == 'grouped') {
            $grouped = array();
            foreach ($product->get_children() as $childId) {
                $grouped[$childId] = array(
                    'content_id' => Helpers\getWooProductContentId( $childId ),
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
	private function setWooAddToCartOnCartEventParams(&$event) {

		if ( ! $this->getOption( 'woo_add_to_cart_enabled' ) ) {
			return false;
		}
		
		$params = $this->getWooEventCartParams($event);
		$params['non_interaction'] = true;
        $event->addParams($params);
        $event->addPayload(['name' => 'add_to_cart']);

        return true;
	}

	private function getWooRemoveFromCartParams( $cart_item ) {

		if ( ! $this->getOption( 'woo_remove_from_cart_enabled' ) ) {
			return false;
		}


        $product_id = Helpers\getWooCartItemId( $cart_item );
        $content_id = Helpers\getWooProductContentId( $product_id );

        $_product = wc_get_product($product_id);

        if(!$_product) return false;

        if($_product->get_type() == "bundle") {
            $price = getWooBundleProductCartPrice($cart_item);
        } else {
            $price = getWooProductPriceToDisplay($product_id, $cart_item['quantity']);
        }

        $product = get_post( $product_id );

		if ( ! empty( $cart_item['variation_id'] ) ) {
			$variation = wc_get_product( (int) $cart_item['variation_id'] );
            if(is_a($variation, 'WC_Product_Variation')) {
                $parentId = $variation->get_parent_id();
                $name = $variation->get_title();
                $categories = implode( '/', getObjectTerms( 'product_cat', $parentId ) );
                $variation_name = implode("/", $variation->get_variation_attributes());
            } else {
                $name = $product->post_title;
                $variation_name = null;
                $categories = implode( '/', getObjectTerms( 'product_cat', $product_id ) );
            }
		} else {
            $name = $product->post_title;
			$variation_name = null;
            $categories = implode( '/', getObjectTerms( 'product_cat', $product_id ) );
		}


		return array(
		        'name' => "remove_from_cart",
			'data' => array(
				'event_category'  => 'ecommerce',
				'currency'        => get_woocommerce_currency(),
				'items'           => array(
					array(
						'id'       => $content_id,
						'name'     => $name,
						'category' => $categories,
						'quantity' => $cart_item['quantity'],
						'price'    => $price,
						'variant'  => $variation_name,
					),
				),
				'non_interaction' => $this->getOption( 'woo_remove_from_cart_non_interactive' ),
			),
		);

	}


    /**
     * @param SingleEvent $event
     * @return boolean
     */
	private function setWooInitiateCheckoutEventParams(&$event) {

		if ( ! $this->getOption( 'woo_initiate_checkout_enabled' ) ) {
			return false;
		}
		$data = ['name'  => 'begin_checkout',];
		$params = $this->getWooEventCartParams( $event );
		$params['non_interaction'] = false;
		$event->addParams($params);
        $event->addPayload($data);
        return true;

	}

    private function getWooSetСheckoutOptionEventParams() {

        if ( ! $this->getOption( 'woo_initiate_checkout_enabled' ) || !$this->getOption( 'woo_initiate_set_checkout_option_enabled' )) {
            return false;
        }
        $user = wp_get_current_user();
        if ( $user->ID !== 0 ) {
            $user_roles = implode( ',', $user->roles );
        } else {
            $user_roles = 'guest';
        }

        $params = array (
            'event_category'=> 'ecommerce',
            'event_label'     => $user_roles,
            'checkout_step'   => '1',
            'checkout_option' => $user_roles,
        );
        $params['non_interaction'] = false;
        return array(
            'name'  => 'set_checkout_option',
            'data'  => $params
        );


    }

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function setWooCheckoutProgressEventParams($event) {

        if ( ! $this->getOption( 'woo_initiate_checkout_enabled' ) || ! $this->getOption( $event->getId()."_enabled" ) ) {
            return false;
        }

        $params = [];
        $params['checkout_step'] = $this->checkout_step;
        $this->checkout_step++;
        $params['non_interaction'] = false;
        $params['event_category'] = "ecommerce";
        $cartParams = $this->getWooEventCartParams( $event );
        $params['items'] = $cartParams['items'];

        switch ($event->getId()) {
            case 'woo_initiate_checkout_progress_f': {
                $params['event_label'] = $params['checkout_option'] = "Add First Name";
                break;
            }
            case 'woo_initiate_checkout_progress_l': {
                $params['event_label'] = $params['checkout_option'] = "Add Last Name";
                break;
            }
            case 'woo_initiate_checkout_progress_e': {
                $params['event_label'] = $params['checkout_option'] = "Add Email";
                break;
            }
            case 'woo_initiate_checkout_progress_o': {
                $params['event_label'] = "Click Place Order";
                $params['coupon'] = $cartParams['coupon'];
                if( !empty($cartParams['shipping']) )
                    $params['checkout_option'] = $cartParams['shipping'];
                break;
            }
        }
        $event->addPayload(['name'=> 'checkout_progress']);
        $event->addParams($params);

        return true;
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
					'id'       => $product_id,
					'name'     => $product->post_title,
					'category' => implode( '/', getObjectTerms( 'product_cat', $product_id ) ),
					'quantity' => $quantity,
					'price'    => getWooProductPriceToDisplay( $product_id, $quantity ),
				),
			),
			'non_interaction' => $this->getOption( 'woo_affiliate_non_interactive' ),
		);

		return array(
			'params'  => $params,
		);

	}

    /**
     * @param SingleEvent $event
     * @return boolean
     */
	private function setWooPayPalEventParams($event) {

		if ( ! $this->getOption( 'woo_paypal_enabled' ) ) {
			return false;
		}

		$params = $this->getWooEventCartParams( $event );
		$params['non_interaction'] = $this->getOption( 'woo_paypal_non_interactive' );
		unset( $params['coupon'] );

        $event->addPayload(['name' => getWooPayPalEventName(),]);
        $event->addParams($params);

        return true;
	}

	private function getWooPurchaseEventParams(&$event) {

		if ( ! $this->getOption( 'woo_purchase_enabled' ) || empty($event->args['order_id']) ) {
			return false;
		}

		$items = array();
		$product_ids = array();
        $tax = 0;
        $withTax = 'incl' === get_option( 'woocommerce_tax_display_cart' );
		foreach ( $event->args['products'] as $product_data ) {

            $product_id  = Helpers\getWooProductDataId( $product_data );
            $content_id  = Helpers\getWooProductContentId( $product_id );

			/**
			 * Discounted(total) price used instead of price as is on Purchase event only to avoid wrong numbers in
			 * Analytic's Product Performance report.
			 */
            $price = $product_data['total'];
            if ( $withTax  ) {
                $price += $product_data['total_tax'] ;
            }

			$item = array(
				'id'       => $content_id,
				'name'     => $product_data['name'],
				'category' => implode( '/', array_column($product_data['categories'],'name') ),
				'quantity' => $product_data['quantity'],
				'price'    => pys_round($price / $product_data['quantity']),
				'variant'  => $product_data['variation_name'],
			);
			$tax += $product_data['total_tax'];
			$items[] = $item;
			$product_ids[] = $item['id'];
		}

        if(empty($items)) return false; // order is empty

        $tax += $event->args['shipping_tax'];

        $total_value = getWooEventOrderTotal($event);
		$params = array(
			'event_category'  => 'ecommerce',
			'transaction_id'  => $event->args['order_id'],
			'value'           => $total_value,
			'currency'        => $event->args['currency'],
			'items'           => $items,
			'tax'             => pys_round($tax),
			'shipping'        => $event->args['shipping'],
			'coupon'          => $event->args['coupon_name'],
			'non_interaction' => $this->getOption( 'woo_purchase_non_interactive' ),
		);
		
		$dyn_remarketing = array(
			'product_id'  => $product_ids,
			'page_type'   => 'purchase',
			'total_value' => $total_value,
		);
		
		$dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
		$params = array_merge( $params, $dyn_remarketing );


        $event->addParams($params);
        $event->addPayload([
            'name' => 'purchase',
        ]);

		return true;
	}

	private function getWooAdvancedMarketingEventParams( $eventType ) {

		if ( ! $this->getOption( $eventType . '_enabled' ) ) {
			return false;
		}

        $params = array(
          //  "plugin" => "PixelYourSite",
        );


        switch ( $eventType ) {
            case 'woo_frequent_shopper':
                $eventName = 'FrequentShopper';
                break;

            case 'woo_vip_client':
                $eventName = 'VipClient';
                break;

            default:
                $eventName = 'BigWhale';
        }

		return array(
			'name'  => $eventName,
			'data'  => $params,
		);

	}

    /**
     * @param SingleEvent $event
     * @return array
     */
    private function getWooEventCartParams( $event ){
        $params = [
            'event_category' => 'ecommerce',
        ];
        $items = array();
        $product_ids = array();
        $withTax = 'incl' === get_option( 'woocommerce_tax_display_cart' );

        foreach ( $event->args['products'] as $product ){
            $product_id = Helpers\getWooEventCartItemId( $product );
            $content_id = Helpers\getWooProductContentId( $product_id );
            $price = $product['subtotal'];
            if($withTax) {
                $price += $product['subtotal_tax'];
            }

            $item = array(
                'id'       => $content_id,
                'name'     => $product['name'],
                'category' => implode( '/', array_column( $product['categories'], 'name' ) ),
                'quantity' => $product['quantity'],
                'price'    => pys_round( $price /$product['quantity']),
                'variant'  => $product['variation_name'],
            );

            $items[] = $item;
            $product_ids[] = $item['id'];
        }

        $params['items'] = $items;
        $params['coupon'] = $event->args['coupon'];

        if($event->getId() == 'woo_add_to_cart_on_cart_page'
            || $event->getId() == 'woo_add_to_cart_on_checkout_page'
            || $event->getId() == 'woo_initiate_checkout'
        ) {
            if($event->getId() == 'woo_initiate_checkout') {
                $page_type = 'checkout';
            } else {
                $page_type = 'cart';
            }
            $dyn_remarketing = array(
                'product_id'  => $product_ids,
                'page_type'   => $page_type,
                'total_value' => getWooEventCartTotal($event),
            );

            $dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
            $params = array_merge( $params, $dyn_remarketing );
        }


        if($event->getId() == 'woo_initiate_checkout_progress_f'
            || $event->getId() == 'woo_initiate_checkout_progress_l'
            || $event->getId() == 'woo_initiate_checkout_progress_e'
            || $event->getId() == 'woo_initiate_checkout_progress_o'
        ) {
            $params["shipping"] = $event->args['shipping'];
        }

        return $params;
    }


	private function getEddViewContentEventParams() {
		global $post;

		if ( ! $this->getOption( 'edd_view_content_enabled' ) ) {
			return false;
		}

		$params = array(
			'event_category'  => 'ecommerce',
			'items'           => array(
				array(
					'id'       => Helpers\getEddDownloadContentId($post->ID),
					'name'     => $post->post_title,
					'category' => implode( '/', getObjectTerms( 'download_category', $post->ID ) ),
					'quantity' => 1,
					'price'    => getEddDownloadPriceToDisplay( $post->ID ),
				),
			),
			'non_interaction' => $this->getOption( 'edd_view_content_non_interactive' ),
		);
		
		$dyn_remarketing = array(
			'product_id'  => Helpers\getEddDownloadContentId($post->ID),
			'page_type'   => 'product',
			'total_value' => getEddDownloadPriceToDisplay( $post->ID ),
		);
		
		$dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
		$params = array_merge( $params, $dyn_remarketing );

		return array(
			'name'  => 'view_item',
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

		$download_post = get_post( $download_id );
		
		$params = array(
			'event_category'  => 'ecommerce',
			'items'           => array(
				array(
					'id'       => Helpers\getEddDownloadContentId($download_id),
					'name'     => $download_post->post_title,
					'category' => implode( '/', getObjectTerms( 'download_category', $download_id ) ),
					'quantity' => 1,
					'price'    => getEddDownloadPriceToDisplay( $download_id, $price_index ),
				),
			),
			'non_interaction' => $this->getOption( 'edd_add_to_cart_non_interactive' ),
		);
		
		$dyn_remarketing = array(
			'product_id'  => Helpers\getEddDownloadContentId($download_id),
			'page_type'   => 'cart',
			'total_value' => getEddDownloadPriceToDisplay( $download_id, $price_index )
		);
		
		$dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
		$params          = array_merge( $params, $dyn_remarketing );

		return $params;

	}

    /**
     * @param SingleEvent $event
     * @return bool
     */
    private function setEddCartEventParams(&$event) {

        $data = [];
        $params = [
            'event_category' => 'ecommerce',
        ];
        switch($event->getId()) {

            case 'edd_add_to_cart_on_checkout_page': {
                if( !$this->getOption( 'edd_add_to_cart_enabled' ) ) return false;
                $data['name'] = 'add_to_cart';
                $params['non_interaction'] = true;
            }break;
            case 'edd_initiate_checkout': {
                if( !$this->getOption( 'edd_initiate_checkout_enabled' ) ) return false;
                $data['name'] = 'begin_checkout';
                $params['non_interaction'] = $this->getOption( 'edd_initiate_checkout_non_interactive' );
            }break;
            case 'edd_purchase': {
                if( !$this->getOption( 'edd_purchase_enabled' ) ) return false;
                $data['name'] = 'purchase';
                $params['non_interaction'] = $this->getOption( 'edd_purchase_non_interactive' );
                $params['coupon'] = $event->args['coupon'];
                $params['transaction_id'] = $event->args['order_id'];
                $params['currency'] = edd_get_currency();
            }break;
            case 'edd_frequent_shopper': {
                if( !$this->getOption( $event->getId() . '_enabled' ) ) return false;
                $data['name'] = 'FrequentShopper';
                $params['non_interaction'] = $this->getOption( 'edd_frequent_shopper_non_interactive' );
            }break;
            case 'edd_vip_client': {
                if( !$this->getOption( $event->getId() . '_enabled' ) ) return false;
                $data['name'] = 'VipClient';
                $params['non_interaction'] = $this->getOption( 'edd_vip_client_non_interactive' );
            }break;
            case 'edd_big_whale': {
                if( !$this->getOption( $event->getId() . '_enabled' ) ) return false;
                $data['name'] = 'BigWhale';
                $params['non_interaction'] = $this->getOption( 'edd_big_whale_non_interactive' );
            }break;
        }

        $items = array();
        $product_ids = array();
        $total = 0;
        $total_as_is = 0;
        $tax = 0;

        $include_tax = PYS()->getOption( 'edd_tax_option' ) == 'included';

        foreach ($event->args['products'] as $product) {
            $download_id   = (int) $product['product_id'];

            if ( $event->getId() == 'edd_purchase' ) {

                if ( $include_tax ) {
                    $price = $product['subtotal'] + $product['tax'] - $product['discount'];
                } else {
                    $price = $product['subtotal'] - $product['discount'];
                }
                $tax += $product['tax'];
                $total_as_is += $product['price'];
            } else {
                $price = getEddDownloadPriceToDisplay( $download_id,$product['price_index'] );
                $total_as_is += edd_get_cart_item_final_price( $product['cart_item_key']  );
            }
            $download_content_id = Helpers\getEddDownloadContentId($download_id);

            $items[] = array(
                'id'       => $download_content_id,
                'name'     => $product['name'],
                'category' => implode( '/', array_column( $product['categories'],'name') ),
                'quantity' => $product['quantity'],
                'price'    => pys_round($price/$product['quantity'])
//				'variant'  => $variation_name,
            );
            $product_ids[] = $download_content_id;
            $total+=$price;
        }
        $params['items']=$items;

        if($event->getId() == 'edd_purchase') {
            if( PYS()->getOption( 'edd_event_value' ) == 'custom' ) {
                $params['value']  = $total;
            } else {
                $params['value']  = $total_as_is;
            }
            $params['tax'] = $tax;
        }

        if ( $event->getId() == 'edd_add_to_cart_on_checkout_page' ) {
            $page_type = 'cart';
        } elseif ( $event->getId() == 'edd_initiate_checkout' ) {
            $page_type = 'checkout';
        } else {
            $page_type = 'purchase';
        }

        //DynamicRemarketing
        $dyn_remarketing = array(
            'product_id'  => $product_ids,
            'page_type'   => $page_type,
            'total_value' => $total,
        );

        $dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
        $params = array_merge( $params, $dyn_remarketing );

        // add all
        $event->addPayload($data);
        $event->addParams($params);

        return true;
    }

	private function getEddCartEventParams( $context = 'add_to_cart' ) {


		

		
		return array(
			'name' => $context,
			'data' => $params,
		);

	}

	private function getEddRemoveFromCartParams( $cart_item ) {

		if ( ! $this->getOption( 'edd_remove_from_cart_enabled' ) ) {
			return false;
		}

		$download_id = $cart_item['id'];
		$download_post = get_post( $download_id );

		$price_index = ! empty( $cart_item['options'] ) ? $cart_item['options']['price_id'] : null;
		
		return array(
		        'name' => 'remove_from_cart',
			'data' => array(
				'event_category'  => 'ecommerce',
				'currency'        => edd_get_currency(),
				'items'           => array(
					array(
						'id'       => Helpers\getEddDownloadContentId($download_id),
						'name'     => $download_post->post_title,
						'category' => implode( '/', getObjectTerms( 'download_category', $download_id ) ),
						'quantity' => $cart_item['quantity'],
						'price'    => getEddDownloadPriceToDisplay( $download_id, $price_index ),
//						'variant'  => $variation_name,
					),
				),
				'non_interaction' => $this->getOption( 'edd_remove_from_cart_non_interactive' ),
			),
		);

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
		$product_ids = array();
		$total_value = 0;

		for ( $i = 0; $i < count( $posts ) && $i < 10; $i ++ ) {

			$item = array(
				'id'            => Helpers\getEddDownloadContentId($posts[ $i ]->ID),
				'name'          => $posts[ $i ]->post_title,
				'category'      => implode( '/', getObjectTerms( 'download_category', $posts[ $i ]->ID ) ),
				'quantity'      => 1,
				'price'         => getEddDownloadPriceToDisplay( $posts[ $i ]->ID ),
				'list_position' => $i + 1,
				'list'          => $list_name,
			);

			$items[] = $item;
			$product_ids[] = $item['id'];
			$total_value += $item['price'];

		}
		
		$params = array(
			'event_category'  => 'ecommerce',
			'event_label'     => $list_name,
			'items'           => $items,
			'non_interaction' => $this->getOption( 'edd_view_category_non_interactive' ),
		);
		
		$dyn_remarketing = array(
			'product_id'  => $product_ids,
			'page_type'   => 'category',
			'total_value' => $total_value,
		);
		
		$dyn_remarketing = Helpers\adaptDynamicRemarketingParams( $dyn_remarketing );
		$params = array_merge( $params, $dyn_remarketing );

		return array(
			'name'  => 'view_item_list',
			'data'  => $params,
		);

	}



    public function getAllPixels($checkLang = true) {
        $pixels = $this->getPixelIDs();

        if(isSuperPackActive()
            && SuperPack()->getOption( 'enabled' )
            && SuperPack()->getOption( 'additional_ids_enabled' )
        ) {
            $additionalPixels = SuperPack()->getGaAdditionalPixel();
            foreach ($additionalPixels as $_pixel) {
                if($_pixel->isEnable
                    && (!$checkLang || $_pixel->isValidForCurrentLang())
                ) {
                    $pixels[] = $_pixel->pixel;
                }
            }
        }

        return $pixels;
    }

    /**
     * @param PYSEvent $event
     * @return array|mixed|void
     */
    public function getAllPixelsForEvent($event) {
        $pixels = $this->getPixelIDs();

        if(isSuperPackActive('3.0.0')) {
            $additionalPixels = SuperPack()->getGaAdditionalPixel();
            foreach ($additionalPixels as $_pixel) {
                if($_pixel->isValidForEvent($event) && $_pixel->isConditionalValidForEvent($event)) {
                    $pixels[]=$_pixel->pixel;
                }
            }
        }

        return $pixels;
    }

}

/**
 * @return GA
 */
function GA() {
	return GA::instance();
}

GA();