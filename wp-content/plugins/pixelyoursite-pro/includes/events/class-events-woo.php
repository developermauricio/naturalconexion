<?php
namespace PixelYourSite;




class EventsWoo extends EventsFactory {

    private $events = array(
        "woo_frequent_shopper",
        "woo_vip_client",
        "woo_big_whale",
        "woo_view_content",
        "woo_view_category",
        "woo_view_item_list",
        "woo_view_item_list_single",
        "woo_view_item_list_search",
        "woo_view_item_list_shop",
        "woo_view_item_list_tag",
        "woo_add_to_cart_on_cart_page",
        "woo_add_to_cart_on_checkout_page",
        "woo_initiate_checkout",
        "woo_purchase",
        "woo_initiate_set_checkout_option",
        "woo_initiate_checkout_progress_f",
        "woo_initiate_checkout_progress_l",
        "woo_initiate_checkout_progress_e",
        "woo_initiate_checkout_progress_o",
        "woo_remove_from_cart",
        "woo_add_to_cart_on_button_click",
        "woo_affiliate",
        "woo_paypal",
        "woo_select_content_category",
        "woo_select_content_single",
        "woo_select_content_search",
        "woo_select_content_shop",
        "woo_select_content_tag",
        "woo_complete_registration"
    );
    public $doingAMP = false;


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
        return "woo";
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
        return isWooCommerceActive() && PYS()->getOption( 'woo_enabled' );
    }

    function getOptions() {

        if($this->isEnabled()) {
            global $post;
            $data = array(
                'enabled'                       => true,
                'addToCartOnButtonEnabled'      => PYS()->getOption( 'woo_add_to_cart_enabled' ) && PYS()->getOption( 'woo_add_to_cart_on_button_click' ),
                'addToCartOnButtonValueEnabled' => PYS()->getOption( 'woo_add_to_cart_value_enabled' ),
                'addToCartOnButtonValueOption'  => PYS()->getOption( 'woo_add_to_cart_value_option' ),
                'woo_purchase_on_transaction'   => PYS()->getOption( 'woo_purchase_on_transaction' ) ,
                'singleProductId'               => isWooCommerceActive() && is_singular( 'product' ) ? $post->ID : null,
                'affiliateEnabled'              => PYS()->getOption( 'woo_affiliate_enabled' ),
                'removeFromCartSelector'        => isWooCommerceVersionGte( '3.0.0' )
                    ? 'form.woocommerce-cart-form .remove'
                    : '.cart .product-remove .remove',
                'addToCartCatchMethod'  => PYS()->getOption('woo_add_to_cart_catch_method')
            );
            $woo_affiliate_custom_event_type = PYS()->getOption( 'woo_affiliate_custom_event_type' );
            if ( PYS()->getOption( 'woo_affiliate_event_type' ) == 'custom' && ! empty( $woo_affiliate_custom_event_type ) ) {
                $data['affiliateEventName'] = sanitizeKey( PYS()->getOption( 'woo_affiliate_custom_event_type' ) );
            } else {
                $data['affiliateEventName'] = PYS()->getOption( 'woo_affiliate_event_type' );
            }
            return $data;
        } else {
            return array(
                'enabled' => false,
            );
        }

    }

    function isReadyForFire($event)
    {
        switch ($event) {
            case 'woo_affiliate': {
                return PYS()->getOption( 'woo_affiliate_enabled' );
            }
            case 'woo_add_to_cart_on_button_click': {

                return PYS()->getOption( 'woo_add_to_cart_enabled' )
                    && PYS()->getOption( 'woo_add_to_cart_on_button_click' )
                    && PYS()->getOption('woo_add_to_cart_catch_method') == "add_cart_js"; // or use in hook
            }
            case 'woo_select_content_category': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) && !$this->doingAMP && is_tax( 'product_cat' );
            }
            case 'woo_select_content_single': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) && !$this->doingAMP && is_product();
            }
            case 'woo_select_content_search': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) && !$this->doingAMP && is_search();
            }
            case 'woo_select_content_shop': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) && !$this->doingAMP && is_shop()&& !is_search();
            }
            case 'woo_select_content_tag': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) && !$this->doingAMP && is_product_tag();
            }
            case 'woo_paypal': {
                return PYS()->getOption( 'woo_paypal_enabled' ) && is_checkout() && ! is_wc_endpoint_url();
            }
            case 'woo_remove_from_cart': {
                return PYS()->getOption( 'woo_remove_from_cart_enabled') && is_cart();
            }
            case "woo_initiate_checkout_progress_f":
            case "woo_initiate_checkout_progress_l":
            case "woo_initiate_checkout_progress_e":
            case "woo_initiate_checkout_progress_o": {
                return PYS()->getOption( "woo_checkout_steps_enabled" ) && is_checkout() ;
            }
            case 'woo_initiate_set_checkout_option': {
                return PYS()->getOption( "woo_checkout_steps_enabled" )  && is_checkout() && ! is_wc_endpoint_url();
            }
            case 'woo_complete_registration': {
                return
                    PYS()->getOption( 'woo_complete_registration_enabled' )
                    && is_order_received_page()
                    && wooIsRequestContainOrderId();
            }
            case 'woo_purchase' : {
                $status = PYS()->getOption( 'woo_purchase_enabled' )
                    && (is_order_received_page() || (EventsWcf()->isEnabled() && PYS()->getOption('wcf_purchase_on') == 'all' && (isWcfUpSale() || isWcfDownSale())))
                    && wooIsRequestContainOrderId();
                return $status;
            }
            case 'woo_frequent_shopper': {
                if(is_order_received_page() && PYS()->getOption( 'woo_frequent_shopper_enabled' ) &&
                    wooIsRequestContainOrderId()) {
                    $customerTotals = $this->getWooCustomerTotals();
                    $orders_count = (int) PYS()->getOption( 'woo_frequent_shopper_transactions' );
                    return  $customerTotals['orders_count'] >= $orders_count;
                }
                return false;
            }
            case 'woo_vip_client': {
                if(is_order_received_page() && PYS()->getOption( 'woo_vip_client_enabled' )&&
                    wooIsRequestContainOrderId()) {
                    $customerTotals = $this->getWooCustomerTotals();
                    $orders_count = (int) PYS()->getOption( 'woo_vip_client_transactions' );
                    $avg = (int) PYS()->getOption( 'woo_vip_client_average_value' );
                    return $customerTotals['orders_count'] >= $orders_count &&
                        $customerTotals['avg_order_value'] >= $avg;
                }
                return false;
            }
            case 'woo_big_whale': {
                if(is_order_received_page() && PYS()->getOption( 'woo_big_whale_enabled' )&&
                    wooIsRequestContainOrderId()) {
                    $customerTotals = $this->getWooCustomerTotals();
                    $ltv = (int) PYS()->getOption( 'woo_big_whale_ltv' );
                    return $customerTotals['ltv'] >= $ltv;
                }
                return false;
            }

            case 'woo_view_content' : {
                return PYS()->getOption( 'woo_view_content_enabled' )
                    && is_product();
            }

            case 'woo_view_category': {
                return PYS()->getOption( 'woo_view_category_enabled' ) &&  is_tax( 'product_cat' );
            }
            case 'woo_view_item_list': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) &&  is_tax( 'product_cat' );
            }
            case 'woo_view_item_list_single': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) &&  is_product();
            }
            case 'woo_view_item_list_search': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) &&  is_search();
            }
            case 'woo_view_item_list_shop': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) &&  is_shop() && !is_search();
            }
            case 'woo_view_item_list_tag': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) &&  is_product_tag();
            }

            case 'woo_add_to_cart_on_cart_page': {
                return PYS()->getOption( 'woo_add_to_cart_enabled' ) &&
                    PYS()->getOption( 'woo_add_to_cart_on_cart_page' ) &&
                    is_cart()
                    && count(WC()->cart->get_cart())>0;
            }
            case 'woo_add_to_cart_on_checkout_page': {
                return PYS()->getOption( 'woo_add_to_cart_enabled' ) && PYS()->getOption( 'woo_add_to_cart_on_checkout_page' )
                    && is_checkout() && ! is_wc_endpoint_url()
                    && count(WC()->cart->get_cart())>0;
            }

            case 'woo_initiate_checkout': {
                return PYS()->getOption( 'woo_initiate_checkout_enabled' ) && is_checkout() && ! is_wc_endpoint_url();
            }

        }
        return false;
    }

    function getEvent($eventId)
    {
        switch ($eventId) {
            case 'woo_remove_from_cart': {
                return $this->getRemoveFromCartEvents($eventId);
            }
            case 'woo_select_content_search':
            case 'woo_select_content_shop':
            case 'woo_select_content_tag':
            case 'woo_select_content_single':
            case 'woo_select_content_category': {
                return $this->getSelectContentEvents($eventId);
            }
            case 'woo_initiate_checkout': {
                return $this->getInitCheckoutEvent($eventId);
            }
            case 'woo_add_to_cart_on_cart_page':
            case 'woo_add_to_cart_on_checkout_page':
                return $this->getAddToCartOnCartEvent($eventId);
            case 'woo_initiate_set_checkout_option':


            case 'woo_view_item_list_tag':
            case 'woo_view_item_list_shop':
            case 'woo_view_item_list_search':
            case 'woo_view_item_list_single':
            case 'woo_view_category':
            case 'woo_view_item_list':
            case 'woo_big_whale':
            case 'woo_vip_client':
            case 'woo_frequent_shopper':
                return new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());
            case 'woo_view_content': {
                $events = [];
                if( is_product() ) {
                    global $post;
                    $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());
                    $event->args = [
                        "id" => $post->ID,
                        'quantity'  => 1
                    ];
                    $events[] = $event;
                }

                return $events;
            }
            case 'woo_paypal':
                return $this->getPaypalEvent($eventId);
            case 'woo_affiliate':
                return new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
            case 'woo_add_to_cart_on_button_click':{
                $events = [];
                if(isNextWcfCheckoutPage()) {
                    $wcfProducts = getWcfFlowCheckoutProducts();
                    foreach($wcfProducts as $product) {
                        $event =  new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
                        $event->args = [
                            "productId" => $product['product'],
                            'quantity'  => $product['quantity'],
                            'discount_value' => $product['discount_value'],
                            'discount_type' => $product['discount_type']
                        ];
                        $events[] = $event;
                    }
                } else {
                    $events[] =  new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
                }

                return $events;

            }

            case 'woo_complete_registration': {
                return $this->getCompleteRegistrationEvent($eventId);
            }
            case 'woo_purchase' : {
                $order_id = $this->getPurchaseOrderId();
                if(!$order_id) return [];
                if (PYS()->getOption( 'woo_purchase_on_transaction' ) &&
                    get_post_meta( $order_id, '_pys_purchase_event_fired', true ) ) {
                    return [];  // skip woo_purchase if this transaction was fired
                }
                update_post_meta( $order_id, '_pys_purchase_event_fired', true );
                return  $this->create_purchase_event($eventId,$order_id);
            }
            case "woo_initiate_checkout_progress_f":
            case "woo_initiate_checkout_progress_l":
            case "woo_initiate_checkout_progress_e":
            case "woo_initiate_checkout_progress_o":{
                $single =  new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
                $shipping = '';
                $ch_ship_methods = WC()->session->get( 'chosen_shipping_methods' );
                if($ch_ship_methods && is_array($ch_ship_methods) && count($ch_ship_methods) > 0) {
                    $shipping_id = $ch_ship_methods[0];
                    $shipping_id = explode(":",$shipping_id)[0];
                    if(isset(WC()->shipping->get_shipping_methods()[$shipping_id])) {
                        $shipping = WC()->shipping->get_shipping_methods()[$shipping_id]->method_title;
                    }
                }



                $single->args = [
                    'products' => $this->getCartProductData(),
                    'shipping' => $shipping,
                    'coupon'   => $this->getCartCoupon()
                ];
                return $single;
            }
        }

        return null;
    }

    private function isActive($event)
    {
        switch ($event) {
            case 'woo_affiliate': {
                return PYS()->getOption( 'woo_affiliate_enabled' );
            }
            case 'woo_add_to_cart_on_button_click': {
                return PYS()->getOption( 'woo_add_to_cart_enabled' ) && PYS()->getOption( 'woo_add_to_cart_on_button_click' );
            }
            case 'woo_paypal': {
                return PYS()->getOption( 'woo_paypal_enabled' ) ;
            }
            case 'woo_remove_from_cart': {
                return PYS()->getOption( 'woo_remove_from_cart_enabled') ;
            }
           /* case "woo_initiate_checkout_progress_f":
            case "woo_initiate_checkout_progress_l":
            case "woo_initiate_checkout_progress_e":
            case "woo_initiate_checkout_progress_o": {
                return PYS()->getOption( "woo_checkout_steps_enabled" ) ;
            }*/
            case 'woo_initiate_set_checkout_option': {
                return PYS()->getOption( "woo_checkout_steps_enabled" );
            }
            case 'woo_purchase' : {
                return PYS()->getOption( 'woo_purchase_enabled' );
            }
            case 'woo_frequent_shopper': {
                return PYS()->getOption( 'woo_frequent_shopper_enabled' );
            }
            case 'woo_vip_client': {
                return PYS()->getOption( 'woo_vip_client_enabled' );
            }
            case 'woo_big_whale': {
                return PYS()->getOption( 'woo_big_whale_enabled' );
            }

            case 'woo_view_content' : {
                return PYS()->getOption( 'woo_view_content_enabled' ) ;
            }
            case 'woo_view_category': {
                return PYS()->getOption( 'woo_view_category_enabled' ) ;
            }

            case 'woo_select_content_category':
            case 'woo_view_item_list': {
                return PYS()->getOption( 'woo_view_item_list_enabled' ) ;
            }

            case 'woo_initiate_checkout': {
                return PYS()->getOption( 'woo_initiate_checkout_enabled' );
            }

        }
        return false;
    }
    private function getWooCartActiveCategories($activeIds) {
        $fireForCategory = array();
        foreach (WC()->cart->cart_contents as $cart_item_key => $cart_item) {
            $_product =  wc_get_product( $cart_item['product_id'] );
            if(!$_product) continue;
            $productCat = $_product->get_category_ids();
            foreach ($activeIds as $key => $value) {
                if(in_array($key,$productCat)) {
                    $fireForCategory[] = $key;
                }
            }
        }
        return array_unique($fireForCategory);
    }

    private function getWooOrderActiveCategories($orderId,$activeIds) {
        $order = new \WC_Order( $orderId );

        $fireForCategory = array();
        foreach ($order->get_items() as $item) {
            $_product =  wc_get_product( $item->get_product_id() );
            if(!$_product) continue;
            $productCat = $_product->get_category_ids();
            foreach ($activeIds as $key => $value) {
                if(in_array($key,$productCat)) { // fire initiate_checkout for all category pixel
                    $fireForCategory[] = $key;
                }
            }
        }
        return array_unique($fireForCategory);
    }

    function getWooCustomerTotals(){
        return PYS()->getEventsManager()->getWooCustomerTotals();
    }

    function getEvents() {
       return $this->events;
    }


    private function getCompleteRegistrationEvent($eventId) {
        $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());
        $orderId = $this->getPurchaseOrderId();

        if(!$orderId) return null;
        $order = wc_get_order($orderId);
        if(!$order) return null;

        $orderItems = $this->filter_order_items($order,0);
        $products_data = $this->prepare_order_items($orderItems,0,[]);
        $shipping_tax = (float) $order->get_shipping_tax( 'edit' );
        $shipping_cost = ((float)$order->get_shipping_total( 'edit' ));

        $args = [
            'order_id'      => $orderId,
            'shipping_cost' => $shipping_cost,
            'shipping_tax'  => $shipping_tax,
            'products'      => $products_data,
            'currency'      => $order->get_currency(),
        ];
        $event->args = $args;
        return $event;
    }
    private function getPurchaseOrderId() {
        $order_id = wooGetOrderIdFromRequest();
        $order_id = apply_filters("pys_woo_checkout_order_id",$order_id);
        $order = wc_get_order($order_id);
        if(!$order) return false;

        if(EventsWcf()->isEnabled()) {
            $offer_orders_meta = $order->get_meta( '_cartflows_offer_child_orders' );

            $child_count = is_array($offer_orders_meta) ? count($offer_orders_meta) : 0;

            if(is_array($offer_orders_meta) && $child_count > 0) { // send info about last child order
                $keys = array_keys($offer_orders_meta);
                $child_id = $keys[count($keys)-1];
                $order_id = $child_id; // replace parent order to child
            }
        }

        $order = wc_get_order($order_id);
        if(!$order) return false;
        $status = "wc-".$order->get_status("edit");

        $disabledStatuses = (array)PYS()->getOption("woo_order_purchase_disabled_status");

        if( in_array($status,$disabledStatuses)) {
            return false;
        }

        if(EventsWcf()->isEnabled()
            && !PYS()->getOption('wcf_purchase_on_optin_enabled')
            && $order->get_meta('pys_order_type',true) == "wcf-optin") {
            return false;
        }

        return $order_id;
    }

    private function create_purchase_event($eventId,$order_id,$category_id = null) {
        $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());
        $wcf_offer_step_id = 0;
        $order = wc_get_order($order_id);
        $wcf_checkout_products = [];

        if(!$order) return null;

        if(isWcfStep()) {

            //prevent duplicate
           if(get_post_meta($order->get_id(),"pys_wcf_purchase_".count($order->get_items()),true)) {
                return null;
            } else {
                update_post_meta($order->get_id(),"pys_wcf_purchase_".count($order->get_items()),true);
            }

            // try to find products only for upsell or downsell
            $prev = getPrevWcfStep();


            if(PYS()->getOption('wcf_purchase_on') == 'all' && ($prev['type'] == 'upsell' || $prev['type'] == 'downsell')) {
                $wcf_offer_step_id = $order->get_meta('pys_wcf_last_offer_step',true);
            }
            if($prev['type'] == 'checkout'){
                $wcf_checkout_products = getWcfFlowCheckoutProducts();
            }

        }

        // if no offers products  load all products from order

        $order_items = $this->filter_order_items($order,$wcf_offer_step_id);

        $products_data = $this->prepare_order_items($order_items,$wcf_offer_step_id,$wcf_checkout_products);


        if(empty($products_data)) return null;

        // add sipping to total value for offer wcf product
        if($wcf_offer_step_id && isWcfSeparateOrders()) {
            $shipping_cost = wcf_get_offer_shipping($wcf_offer_step_id);
            $shipping_tax = 0;
        } else {
            $shipping_tax = (float) $order->get_shipping_tax( 'edit' );
            $shipping_cost = ((float)$order->get_shipping_total( 'edit' ));
        }

        $args = [
            'order_id'      => $order_id,
            'currency'      => $order->get_currency(),
            'shipping_cost' => $shipping_cost,
            'shipping_tax'  => $shipping_tax,
            'products'      => $products_data,
            'coupon_used'   => 'no',
            'coupon_name'   => '',
            'shipping'      => '',
            'town'          => $order->get_billing_city(),
            'state'         => $order->get_billing_state(),
            'country'       => $order->get_billing_country(),
            'payment_method'=> $order->get_payment_method_title(),
        ];



        if( PYS()->getOption("enable_woo_transactions_count_param")
            || PYS()->getOption("enable_woo_predicted_ltv_param")
            || PYS()->getOption("enable_woo_average_order_param")) {
            $customer_params = PYS()->getEventsManager()->getWooCustomerTotals($order_id);

            $args[  'predicted_ltv'] = $customer_params['ltv'];
            $args['average_order'] = $customer_params['avg_order_value'];
            $args['transactions_count'] = $customer_params['orders_count'];
        }

        // coupons
        if ( $coupons = $order->get_items( 'coupon' ) ) {
            $labels = array();
            foreach ( $coupons as $coupon ) {
                $labels[] = $coupon['name'] ? $coupon['name'] : null;
            }
            $args['coupon_used'] = 'yes';
            $args['coupon_name'] = implode( ', ', $labels );

        }

        // shipping method
        if ( $shipping_methods = $order->get_items( 'shipping' ) ) {

            $labels = array();
            foreach ( $shipping_methods as $shipping ) {
                $labels[] = $shipping['name'] ? $shipping['name'] : null;
            }
            $args['shipping'] = implode( ', ', $labels );
        }


        $event->args = $args;

        $event->addPayload(['woo_order' => $order_id]);
       return $event;
    }


    private function filter_order_items($order,$wcf_offer_step_id) {
        $order_items = [];
        if($wcf_offer_step_id) {

            // remove from order all products except offer
            if(!isWcfSeparateOrders()) {

                $product = get_post_meta( $wcf_offer_step_id, 'wcf-offer-product', true );

                if(!empty($product)) {
                    foreach ( $order->get_items() as $line_item ) {
                        $product_id = empty($line_item['variation_id']) ? $line_item['product_id'] : $line_item['variation_id'];
                        if($product_id == $product[0]) {
                            $order_items[] = $line_item;
                        }
                    }
                }
            }
        }
        if(empty($order_items)) {
            $order_items = $order->get_items();
        }
        return $order_items;
    }
    /**
     * @param \WC_Order_Item_Product[] $order_items
     * @param $wcf_offer_step_id // optional id of offer step from cart flow plugin
     * @return array
     */
    private function prepare_order_items($order_items,$wcf_offer_step_id = false,$wcf_checkout_products = []) {
        $products_data = [];
        foreach ($order_items as $line_item) {
            if( !($line_item instanceof \WC_Order_Item_Product)) continue;

            $product_id = empty($line_item['variation_id']) ? $line_item['product_id'] : $line_item['variation_id'];
            $product = wc_get_product($product_id);

            if(!$product) continue;



            if ( $product->get_type() == 'variation' ) {
                $parent_id = $product->get_parent_id(); // get terms from parent
                $tags = getObjectTerms( 'product_tag', $parent_id );
                $categories = getObjectTermsWithId( 'product_cat', $parent_id );
                $variation_name = implode("/", $product->get_variation_attributes());
            } else {
                $tags = getObjectTerms( 'product_tag', $product->get_id() );
                $categories = getObjectTermsWithId( 'product_cat', $product->get_id() );
                $variation_name = "";
            }

            $sale_price = -1;
//            // need move to filter
            if($wcf_offer_step_id) {
                $sale_price = getWfcProductSalePrice($product,getWcfOfferProduct($wcf_offer_step_id)); // find sale prise for offer product
            } elseif (!empty($wcf_checkout_products)) {
                foreach ($wcf_checkout_products as $product_data) { // find sale prise for offer checkout products
                    if($product_id == $product_data['product']) {
                        $sale_price = getWfcProductSalePrice($product,$product_data);
                    }
                }
            }
            $price = getWooProductPriceToDisplay($product->get_id(),1,$sale_price);

            $product_data = [
                'product_id'    => $product->get_id(),
                'parent_id'     => $product->get_parent_id(),
                'type'          => $product->get_type(),
                'tags'          => $tags,
                'categories'    => $categories,
                'quantity'      => $line_item['qty'],
                'price'         => $price, // price for single product
                'total'         => pys_round($line_item['total']),
                'total_tax'     => pys_round($line_item['total_tax']),
                'subtotal'      => pys_round($line_item['subtotal']),
                'subtotal_tax'  => pys_round($line_item['subtotal_tax']),
                'name'          => $product->get_name(),
                'variation_name'=> $variation_name
            ];

            $products_data[] = $product_data;
        }
        return $products_data;
    }


    function getSelectContentEvents($eventId) {
        $events = [];
        if($eventId == 'woo_select_content_category') {
            global $posts;
            $product_category = "";
            $term = get_term_by( 'slug', get_query_var( 'term' ), 'product_cat' );
            if ( $term ) {
                $product_category = $term->name;
            }
            $list_name =  $product_category." - Category";

            for ( $i = 0; $i < count( $posts ) && $i < 10; $i ++ ) {

                if ( $posts[ $i ]->post_type !== 'product' ) {
                    continue;
                }
                $event = new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
                $item = array(
                    'id'            => GA\Helpers\getWooProductContentId($posts[ $i ]->ID),
                    'name'          => $posts[ $i ]->post_title,
                    'category'      => implode( '/', getObjectTerms( 'product_cat', $posts[ $i ]->ID ) ),
                    'quantity'      => 1,
                    'price'         => getWooProductPriceToDisplay( $posts[ $i ]->ID ),
                    'list_position' => $i + 1,
                    'list_name'     => $list_name,
                );
                $event->addParams(['items'           => array($item),]);
                $event->args = $posts[ $i ]->ID;
                $events[]=$event;
            }
        }
        if ($eventId == 'woo_select_content_single') {
            $product = wc_get_product( get_the_ID() );

            $args = array(
                'posts_per_page' => 4,
                'columns'        => 4,
            );
            $args = apply_filters( 'woocommerce_output_related_products_args', $args );

            $related_products = array_map( 'wc_get_product', GA\Helpers\custom_wc_get_related_products( get_the_ID(), $args['posts_per_page'],$product->get_upsell_ids() ));
            $related_products = wc_products_array_orderby( $related_products, 'rand', 'desc' );

            $list_name = $product->get_name()." - Related products";
            $i = 0;

            foreach ( $related_products as $relate) {

                if(!$relate) continue;
                $event = new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
                $item = array(
                    'id'            => GA\Helpers\getWooProductContentId($relate->get_id()),
                    'name'          => $relate->get_title(),
                    'category'      => implode( '/', getObjectTerms( 'product_cat', $relate->get_id() ) ),
                    'quantity'      => 1,
                    'price'         => getWooProductPriceToDisplay( $relate->get_id() ),
                    'list_position' => $i + 1,
                    'list_name'     => $list_name,
                );
                $event->addParams(['items' => array($item),]);
                $event->args = $relate->get_id();
                $events[]=$event;
                $i++;
            }

        }
        if ($eventId == 'woo_select_content_shop' || $eventId == 'woo_select_content_search') {
            global $posts;

            if($eventId == "woo_select_content_shop") {
                $list_name =  woocommerce_page_title(false);
            } else {
                $list_name = "WooCommerce Search";
            }

            $i = 0;
            foreach ($posts as $post) {
                if( $post->post_type != 'product') continue;
                $event = new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
                $item = array(
                    'id'            => GA\Helpers\getWooProductContentId($post->ID),
                    'name'          => $post->post_title ,
                    'category'      => implode( '/', getObjectTerms( 'product_cat', $post->ID ) ),
                    'quantity'      => 1,
                    'price'         => getWooProductPriceToDisplay( $post->ID ),
                    'list_position' => $i + 1,
                    'list_name'     => $list_name,
                    'post_id'       => $post->ID
                );
                $i++;
                $event->addParams(['items' => array($item),]);
                $event->args = $post->ID;
                $events[]=$event;
            }

        }
        if ($eventId == 'woo_select_content_tag') {
            global $posts;

            $list_name = single_tag_title( '', false )." - Tag";

            for ( $i = 0; $i < count( $posts ); $i ++ ) {

                if ( $posts[ $i ]->post_type !== 'product' ) {
                    continue;
                }
                $event = new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
                $item = array(
                    'id'            => GA\Helpers\getWooProductContentId($posts[ $i ]->ID),
                    'name'          => $posts[ $i ]->post_title,
                    'category'      => implode( '/', getObjectTerms( 'product_cat', $posts[ $i ]->ID ) ),
                    'quantity'      => 1,
                    'price'         => getWooProductPriceToDisplay( $posts[ $i ]->ID ),
                    'list_position' => $i + 1,
                    'list_name'     => $list_name,
                );
                $event->addParams(['items' => array($item),]);
                $event->args = $posts[ $i ]->ID;
                $events[]=$event;
            }
        }

        return $events;
    }

    function getRemoveFromCartEvents($eventId) {
        $events = [];
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $event = new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
            $event->args = ['key'=>$cart_item_key,'item'=>$cart_item];
            $events[]=$event;
        }
        return $events;
    }

    function getAddToCartOnCartEvent($eventId) {
        $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());

        $products_data = $this->getCartProductData();
        if(count($products_data) == 0) return null;

        $event->args = [
            'products'  => $products_data,
            'coupon'    => $this->getCartCoupon()
        ];
        return $event;
}
    function getCartCoupon() {
        $coupons =  WC()->cart->get_applied_coupons();
        if ( count($coupons) > 0 ) {
           return $coupons[0];
        }
        return null;
    }

    function getInitCheckoutEvent($eventId) {
        $event = new SingleEvent($eventId,EventTypes::$STATIC,self::getSlug());

        $products_data = $this->getCartProductData();
        if(count($products_data) == 0) return null;

        $event->args = [
            'products' => $products_data,
            'coupon'    => $this->getCartCoupon()
        ];
        return $event;
    }

    function getPaypalEvent($eventId) {
        $event = new SingleEvent($eventId,EventTypes::$DYNAMIC,self::getSlug());
        $products_data = $this->getCartProductData();
        if(count($products_data) == 0) return null;

        $event->args = [
            'products' => $products_data,
            'coupon'    => $this->getCartCoupon()
        ];
        return $event;
    }

    function getCartProductData() {
        $products_data = [];
        foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
            $product_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
            $product = wc_get_product($product_id);

            if(!$product) continue;

            if ( $product->get_type() == 'variation' ) {
                $parent_id = $product->get_parent_id(); // get terms from parent
                $tags = getObjectTerms( 'product_tag', $parent_id );
                $categories = getObjectTermsWithId( 'product_cat', $parent_id );
                $variation_name = implode("/", $product->get_variation_attributes());
            } else {
                $tags = getObjectTerms( 'product_tag', $product->get_id() );
                $categories = getObjectTermsWithId( 'product_cat', $product->get_id() );
                $variation_name = "";
            }
            $sale_price = -1;


            $price = getWooProductPriceToDisplay($product_id, 1,$sale_price);
            $product_data = [
                'product_id'    => $product->get_id(),
                'parent_id'     => $product->get_parent_id(),
                'type'          => $product->get_type(),
                'tags'          => $tags,
                'categories'    => $categories,
                'quantity'      => $cart_item['quantity'],
                'price'         => $price,
                'total'         => pys_round($cart_item['line_total']), // with coupon sale
                'total_tax'     => pys_round($cart_item['line_tax']),
                'subtotal'      => pys_round($cart_item['line_subtotal']),
                'subtotal_tax'  => pys_round($cart_item['line_subtotal_tax']),
                'name'          => $product->get_name(),
                'variation_name'=> $variation_name
            ];

            $products_data[] = $product_data;
        }

        return $products_data;
    }

    /**
     * @param SingleEvent $event
     * @param $filter
     */
   static function filterEventProductsBy($event,$filter,$filterId) {
        $products = [];

       foreach ($event->args['products'] as $productData) {
           if($filter == 'in_product_cat') {
               $ids = array_column($productData['categories'],'id');
               if(in_array($filterId,$ids)) {
                   $products[]=$productData;
               }
           } elseif ($filter == 'in_product_tag') {
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
 * @return EventsWoo
 */
function EventsWoo() {
    return EventsWoo::instance();
}

EventsWoo();
