<?php

namespace PixelYourSite;

class EventsEdd extends EventsFactory {
    private $events = array(
        'edd_frequent_shopper',
        'edd_vip_client',
        'edd_big_whale',
        'edd_view_content',
        'edd_view_category',
        'edd_add_to_cart_on_checkout_page',
        'edd_remove_from_cart',
        'edd_initiate_checkout',
        'edd_purchase',
        'edd_add_to_cart_on_button_click'
    );

    private $eddCustomerTotals = array();
    private static $_instance;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    private function __construct() {

    }

    static function getSlug() {
        return "edd";
    }

    function getEvents() {
        return $this->events;
    }

    function getCount()
    {
        $size = 0;
        if(!$this->isEnabled()) {
            return 0;
        }
        foreach ($this->events as $event) {
            if($this->isActive($event)){
                $size++;
            }
        }
        return $size;
    }

    function isEnabled()
    {
        return isEddActive() && PYS()->getOption( 'edd_enabled' );
    }

    function getOptions()
    {
        if($this->isEnabled()) {
            return array(
                'enabled'                       => true,
                'addToCartOnButtonEnabled'      => isEventEnabled( 'edd_add_to_cart_enabled' ) && PYS()->getOption( 'edd_add_to_cart_on_button_click' ),
                'addToCartOnButtonValueEnabled' => PYS()->getOption( 'edd_add_to_cart_value_enabled' ),
                'addToCartOnButtonValueOption'  => PYS()->getOption( 'edd_add_to_cart_value_option' ),
                'edd_purchase_on_transaction'   => PYS()->getOption( 'edd_purchase_on_transaction' )
            );
        } else {
            return array(
                'enabled'                       => false
            );
        }
    }

    function isReadyForFire($event)
    {
        switch ($event) {
            case 'edd_add_to_cart_on_button_click': {
                return PYS()->getOption( 'edd_add_to_cart_enabled' ) && PYS()->getOption( 'edd_add_to_cart_on_button_click' );
            }
            case 'edd_purchase': {
                return $this->checkPurchase();
            }
            case 'edd_initiate_checkout': {
                return  PYS()->getOption( 'edd_initiate_checkout_enabled' ) && edd_is_checkout();
            }
            case 'edd_remove_from_cart': {
                return PYS()->getOption( 'edd_remove_from_cart_enabled') && edd_is_checkout();
            }
            case 'edd_add_to_cart_on_checkout_page' : {
                return PYS()->getOption( 'edd_add_to_cart_enabled' ) && PYS()->getOption( 'edd_add_to_cart_on_checkout_page' )
                    && edd_is_checkout();
            }
            case 'edd_view_category': {
                return PYS()->getOption( 'edd_view_category_enabled' ) && is_tax( 'download_category' );
            }
            case 'edd_view_content' : {
                return PYS()->getOption( 'edd_view_content_enabled' ) && is_singular( 'download' );
            }
            case 'edd_vip_client': {
                $customerTotals = $this->getEddCustomerTotals();
                if(edd_is_success_page() && PYS()->getOption( 'edd_vip_client_enabled' )) {
                    $orders_count = (int) PYS()->getOption( 'edd_vip_client_transactions' );
                    $avg = (int) PYS()->getOption( 'edd_vip_client_average_value' );
                    return $customerTotals['orders_count'] >= $orders_count && $customerTotals['avg_order_value'] >= $avg;
                }
                return false;
            }
            case 'edd_big_whale': {
                $customerTotals = $this->getEddCustomerTotals();
                if(edd_is_success_page() && PYS()->getOption( 'edd_big_whale_enabled' )) {
                    $ltv = (int) PYS()->getOption( 'edd_big_whale_ltv' );
                    return $customerTotals['ltv'] >= $ltv;
                }
                return false;
            }
            case 'edd_frequent_shopper': {
                $customerTotals = $this->getEddCustomerTotals();
                if(edd_is_success_page() && PYS()->getOption( 'edd_frequent_shopper_enabled' )) {
                    $orders_count = (int) PYS()->getOption( 'edd_frequent_shopper_transactions' );
                    return $customerTotals['orders_count'] >= $orders_count;
                }
                return false;
            }

        }
        return false;
    }

    function getEvent($eventId)
    {
        switch ($eventId) {

            case 'edd_view_category': {
                $event = new SingleEvent($eventId, EventTypes::$STATIC, self::getSlug());
                return $event;
            }
            case 'edd_view_content': {
                global  $post;
                $event = new SingleEvent($eventId, EventTypes::$STATIC, self::getSlug());
                $event->args = ['products' => [$this->getEddProductParams($post->ID)]] ;
                return $event;
        }

            case 'edd_remove_from_cart': {
                return $this->getRemoveFromCartEvents($eventId);
            }
            case 'edd_add_to_cart_on_button_click': {

                return new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
            }
            case 'edd_add_to_cart_on_checkout_page':
            case 'edd_initiate_checkout': {
                $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());
                $event->args = ['products' => $this->getEddCartProducts()] ;
                return $event;
            }
            case 'edd_vip_client':
            case 'edd_big_whale':
            case 'edd_frequent_shopper': {
                $payment_key = getEddPaymentKey();
                $order_id = (int) edd_get_purchase_id_by_key( $payment_key );
                if(!$order_id) return null;

                $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());
                $event->args = ['products' => $this->getEddCheckOutProducts($order_id)] ;
                return $event;
            }
            case 'edd_purchase': {
                return $this->getPurchaseEvent($eventId);
            }
        }
    }

    private function isActive($event)
    {
        switch ($event) {
            case 'edd_add_to_cart_on_button_click': {
                return PYS()->getOption( 'edd_add_to_cart_enabled' ) && PYS()->getOption( 'edd_add_to_cart_on_button_click' );
            }
            case 'edd_purchase': {
                return PYS()->getOption( 'edd_purchase_enabled' );
            }
            case 'edd_initiate_checkout': {
                return  PYS()->getOption( 'edd_initiate_checkout_enabled' ) ;
            }
            case 'edd_remove_from_cart': {
                return PYS()->getOption( 'edd_remove_from_cart_enabled');
            }
            case 'edd_add_to_cart_on_checkout_page' : {
                return PYS()->getOption( 'edd_add_to_cart_enabled' ) && PYS()->getOption( 'edd_add_to_cart_on_checkout_page' );
            }
            case 'edd_view_category': {
                return PYS()->getOption( 'edd_view_category_enabled' ) ;
            }
            case 'edd_view_content' : {
                return PYS()->getOption( 'edd_view_content_enabled' ) ;
            }
            case 'edd_vip_client': {
                return PYS()->getOption( 'edd_vip_client_enabled' );
            }
            case 'edd_big_whale': {
                return PYS()->getOption( 'edd_big_whale_enabled' );
            }
            case 'edd_frequent_shopper': {
                return PYS()->getOption( 'edd_frequent_shopper_enabled' );
            }
        }
        return false;
    }

    private function getRemoveFromCartEvents($eventId) {
        $events = [];


        foreach (edd_get_cart_contents() as $cart_item_key => $cart_item) {
            $event = new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
            $event->args = ['key'=>$cart_item_key,'item'=>$cart_item];
            $events[]=$event;
        }
        return $events;
    }

    public function getEddCustomerTotals() {
        return PYS()->getEventsManager()->getEddCustomerTotals();
    }

    private function checkPurchase() {
        if(PYS()->getOption( 'edd_purchase_enabled' ) && edd_is_success_page()) {
            /**
             * When a payment gateway used, user lands to Payment Confirmation page first, which does automatic
             * redirect to Purchase Confirmation page. We filter Payment Confirmation to avoid double Purchase event.
             */
            if ( isset( $_GET['payment-confirmation'] ) ) {
                //@fixme: some users will not reach success page and event will not be fired
                //return;
            }
            $payment_key = getEddPaymentKey();
            $order_id = (int) edd_get_purchase_id_by_key( $payment_key );
            $status = edd_get_payment_status( $order_id );

            // pending payment status used because we can't fire event on IPN
            if ( strtolower( $status ) != 'publish' && strtolower( $status ) != 'pending' ) {
                return false;
            }


            if ( PYS()->getOption( 'edd_purchase_on_transaction' ) &&
                get_post_meta( $order_id, '_pys_purchase_event_fired', true ) ) {
                return false; // skip woo_purchase if this transaction was fired
            }
            update_post_meta( $order_id, '_pys_purchase_event_fired', true );

            return true;
        }
        return false;
    }

    function getEddProductParams($productId, $quantity = 1) {
        $post = get_post(  $productId );
        $tags = getObjectTerms( 'download_tag', $productId );
        $categories = getObjectTermsWithId( 'download_category', $productId );
        $data = [
            'product_id'    => $productId,
            'name'          => $post->post_title,
            'tags'          => $tags,
            'categories'    => $categories,
            'quantity'      => $quantity,
            'price_index'   => null
        ];

        return $data;
    }

    function getEddCartProducts() {
        $products = [];
        foreach (edd_get_cart_contents() as $cart_item_key => $cart_item) {
            $productId = (int) $cart_item['id'];
            $post = get_post(  $productId );
            $tags = getObjectTerms( 'download_tag', $productId );
            $categories = getObjectTermsWithId( 'download_category', $productId );

            if ( ! empty( $cart_item['options'] ) && $cart_item['options']['price_id'] !== 0 ) {
                $price_index = $cart_item['options']['price_id'];
            } else {
                $price_index = null;
            }

            $products[] = [
                'cart_item_key' => $cart_item_key,
                'product_id'    => $productId,
                'name'          => $post->post_title,
                'tags'          => $tags,
                'categories'    => $categories,
                'quantity'      => $cart_item['quantity'],
                'price_index'   => $price_index
            ];
        }
        return $products;
    }

    function getPurchaseEvent($eventId) {

        $payment_key = getEddPaymentKey();
        $order_id = (int) edd_get_purchase_id_by_key( $payment_key );
        if(!$order_id) return null;

        $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());
        $event->addPayload(['edd_order'=>$order_id]);
        $args = [
            'products' => $this->getEddCheckOutProducts($order_id),
            'order_id'=>$order_id,
        ];

        $user = edd_get_payment_meta_user_info( $order_id );
        // coupons
        $coupons = isset( $user['discount'] ) && $user['discount'] != 'none' ? $user['discount'] : null;

        if ( ! empty( $coupons ) ) {
            $coupons = explode( ', ', $coupons );
            $args['coupon'] = $coupons[0];
        } else {
            $args['coupon'] = '';
        }

        $event->args = $args;

        return $event;
    }

    function getEddCheckOutProducts($orderId) {
        $products = [];
        $cart = edd_get_payment_meta_cart_details($orderId, true );
        foreach ($cart as $cart_item_key => $cart_item) {
            $productId = (int) $cart_item['id'];
            $post = get_post(  $productId );
            $tags = getObjectTerms( 'download_tag', $productId );
            $categories = getObjectTermsWithId( 'download_category', $productId );

            $options = $cart_item['item_number']['options'];
            if ( ! empty( $options ) && $options !== 0 ) {
                $price_index = $options['price_id'];
            } else {
                $price_index = null;
            }

            $products[] = [
                'cart_item_key' => $cart_item_key,
                'product_id' => $productId,
                'name'  => $post->post_title,
                'tags'          => $tags,
                'categories'    => $categories,
                'quantity'  => $cart_item['quantity'],
                'subtotal'  =>  $cart_item['subtotal'],
                'tax'  => $cart_item['tax'] ,
                'discount'  => $cart_item['discount'],
                'price'  => $cart_item['price'],
                'price_index'=>$price_index
            ];
        }
        return $products;
    }

    /**
     * @param SingleEvent $event
     * @param $filter
     */
    static function filterEventProductsBy($event,$filter,$filterId) {
        $products = [];

        foreach ($event->args['products'] as $productData) {
            if($filter == 'in_download_category') {
                $ids = array_column($productData['categories'],'id');

                if(in_array($filterId,$ids)) {
                    $products[]=$productData;
                }
            } elseif ($filter == 'in_download_tag') {
                if(isset($productData['tags'][$filterId])) {
                    $products[]=$productData;
                }
            } else  {
                if( $productData['product_id'] == $filterId) {
                    $products[]=$productData;
                }
            }

        }
        return $products;
    }
}

/**
 * @return EventsEdd
 */
function EventsEdd() {
    return EventsEdd::instance();
}

EventsEdd();