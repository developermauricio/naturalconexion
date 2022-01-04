<?php
namespace PixelYourSite;

class EventsWcf extends EventsFactory {

    private $events = array(
        'wcf_add_to_cart_on_next_step_click', // Fired when next button click and next page is checkout
        'wcf_add_to_cart_on_bump_click', // Fired when order bump is accepted
        'wcf_remove_from_cart_on_bump_click',
        'wcf_page',// Fired on each step
        'wcf_step_page', // Fired when order user open landing page
        'wcf_bump', // Fired when order bump is accepted
        'wcf_lead',// Fired on check out page if order is from optin page
        'wcf_view_content',// Fired on each step
    );

    private static $_instance;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    private function __construct() {
        add_action( 'woocommerce_checkout_update_order_meta',array($this,'woo_save_checkout_fields'),10, 2);
    }

    function woo_save_checkout_fields($order_id, $data) {
        $pys_order_type = isset($_REQUEST['pys_order_type']) ? sanitize_text_field($_REQUEST['pys_order_type']) : "";
        if($pys_order_type == 'wcf-optin')
            update_post_meta($order_id,"pys_order_type",$pys_order_type);
    }

    function getCount()
    {
        return count($this->events);
    }

    public static function getSlug()
    {
        return "wcf";
    }

    function isEnabled()
    {
        return PYS()->getOption('wcf_enabled') && isWcfActive() && isWooCommerceActive();
    }

    function getOptions()
    {
        return array();
    }

    function getEvents()
    {
        return $this->events;
    }

    function isReadyForFire($event_id)
    {
        switch ($event_id) {
            case 'wcf_view_content': {
                return PYS()->getOption( 'woo_view_content_enabled' )
                    && (
                        isWcfLanding()
                        || ( PYS()->getOption( 'wcf_sell_step_view_content_enabled' )
                            && (isWcfDownSale() || isWcfUpSale())
                           )
                    );

            }

            case 'wcf_add_to_cart_on_next_step_click': {
                return PYS()->getOption( 'woo_add_to_cart_enabled' )
                    && PYS()->getOption( 'woo_add_to_cart_on_button_click' )
                    && isNextWcfCheckoutPage();
                }
            case 'wcf_add_to_cart_on_bump_click': {
                return PYS()->getOption( 'woo_add_to_cart_enabled' )
                    && PYS()->getOption( 'woo_add_to_cart_on_button_click' )
                    && PYS()->getOption('wcf_add_to_cart_on_bump_click_enabled')
                    && isWcfCheckoutPage();
            }
            case 'wcf_remove_from_cart_on_bump_click': {
                return  PYS()->getOption('wcf_add_to_cart_on_bump_click_enabled') && isWcfCheckoutPage();
            }
            case 'wcf_page': {
                return PYS()->getOption('wcf_cart_flows_event_enabled') && isWcfStep();
            }
            case 'wcf_step_page': {
                return PYS()->getOption("wcf_step_event_enabled") && isWcfStep();
            }
            case 'wcf_bump': {
                return PYS()->getOption("wcf_bump_event_enabled")
                    && isWcfCheckoutPage()
                    && getWcfFlowBumpProduct();
            }

            case 'wcf_lead': {
                return PYS()->getOption('wcf_lead_enabled')
                    && is_order_received_page()
                    && wooIsRequestContainOrderId();
            }

            default: return false;
        }
    }

    function getEvent($event_id) {
        switch ($event_id) {

            case 'wcf_view_content': {

                global $post;
                if(isWcfLanding()) {
                    $wcfProducts = getWcfFlowCheckoutProducts();
                } else {
                    $wcfProducts = [
                        getWcfOfferProduct($post->ID)
                    ];
                }
                $event = new SingleEvent($event_id,EventTypes::$STATIC,self::getSlug());
                $event->args = $this->getViewContentArgs($wcfProducts);

                return $event;
            }
            case 'wcf_add_to_cart_on_next_step_click': {
                $event = new SingleEvent($event_id,EventTypes::$DYNAMIC,self::getSlug());
                $event->args = $this->getWooAddToCartArgs();
                return $event;
            }
            case 'wcf_remove_from_cart_on_bump_click':
            case 'wcf_add_to_cart_on_bump_click': {
                $event = new SingleEvent($event_id,EventTypes::$DYNAMIC,self::getSlug());
                $event->args = $this->getBumpArgs();
                return $event;
            }

            case 'wcf_bump': {
                $event = new SingleEvent($event_id,EventTypes::$DYNAMIC,self::getSlug());
                $step = getWcfCurrentStep();
                $event->addParams([
                    'flow' => getWcfFlowTitle(),
                    'step' => $step->get_step_type()
                ]);
                $event->addPayload([
                    'name' => 'CartFlows_bump'
                ]);
                return $event;
            }

            case 'wcf_page': {
                $event = new SingleEvent($event_id,EventTypes::$STATIC,self::getSlug());
                $step = getWcfCurrentStep();
                $event->addParams([
                    'flow' => getWcfFlowTitle(),
                    'step' => $step->get_step_type()
                ]);
                $event->addPayload([
                    'name' => 'CartFlows'
                ]);
                return $event;
            }
            case 'wcf_step_page': {
                $event = new SingleEvent($event_id,EventTypes::$STATIC,self::getSlug());
                $step = getWcfCurrentStep();
                $event->addParams([
                    'flow' => getWcfFlowTitle(),
                ]);
                $event->addPayload([
                    'name' => 'CartFlows_'.$step->get_step_type()
                ]);
                return $event;
            }
            case 'wcf_lead': {
                $order_id = wooGetOrderIdFromRequest();
                $order = wc_get_order($order_id);
                if(!$order || $order->get_meta('pys_order_type',true) != "wcf-optin" ) return null;

                $event = new SingleEvent($event_id,EventTypes::$STATIC,self::getSlug());
                $event->addParams([

                ]);
                $event->addPayload([
                    'name' => 'Lead'
                ]);
                return $event;
            }

            default: return null;
        }
    }

    private function getViewContentArgs($wcf_products) {
        $args = [
            'products'=>[],
            'currency' => get_woocommerce_currency()
        ];
        foreach ($wcf_products as $wcf_product) {
            $product = wc_get_product($wcf_product['product']);
            if(!$product) continue;
            $args['products'][] = pys_woo_get_product_data($product,$wcf_product);
        }

        return $args;
    }

    private function getBumpArgs() {
        $args = [
            'products'=>[],
            'currency' => get_woocommerce_currency()
        ];
        $wcf_product = getWcfFlowBumpProduct();
        $product = wc_get_product($wcf_product['product']);
        if(!$product) return null;
        $args['products'][] = pys_woo_get_product_data($product,$wcf_product);
        return $args;
    }

    private function getWooAddTOCartArgs() {
        $args = [
            'products'=>[],
            'currency' => get_woocommerce_currency()
        ];

        $wcf_products = getWcfFlowCheckoutProducts();
        foreach($wcf_products as $wcf_product) {
            $product = wc_get_product($wcf_product['product']);
            if(!$product) return null;
            $product_data = pys_woo_get_product_data($product,$wcf_product);
            $args['products'][] = $product_data;
        }
        return $args;
    }


}

/**
 * @return EventsWcf
 */
function EventsWcf() {
    return EventsWcf::instance();
}

EventsWcf();