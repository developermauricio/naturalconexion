<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Includes
 */
class WooEmailCustomizerIntegrationCouponAnalyticsRabbit
{
    protected static $applied_coupon = null;
    /**
     * on payment completed
     * */
    public static function onPaymentCompleted($order_id){
        $disable_existing_coupon = self::disableExistingCoupons();
        $has_retainful_plugin = self::runThroughThirdPartyPlugin();
        if(!$disable_existing_coupon){
            $enabled_coupon_integration = self::isAPIIntegrationEnabled();
            if($enabled_coupon_integration){
                $wooVersion3 = version_compare( WOO_ECPB_WOOCOMMERCE_VERSION, '3.0', ">=" );
                if($wooVersion3){
                    $order = wc_get_order($order_id);
                    if($has_retainful_plugin){
                        $new_coupon_code = '';
                    } else {
                        $new_coupon_code = $order->get_meta('_flycart_emc_coupon_code');
                    }
                    $applied_coupon_code = $order->get_meta('_flycart_emc_coupon_code_applied');
                    $params = self::getDefaultParameterToSendForAnalytics($order);
                    $params['new_coupon'] = $new_coupon_code;
                    $params['applied_coupon'] = '';
                    if(!empty($applied_coupon_code)){
                        $params['applied_coupon'] = strtoupper($applied_coupon_code);
                        $coupondata = self::checkIsValidCoupon($applied_coupon_code, $order);
                        if(!empty($coupondata)){
                            $my_post = array(
                                'ID'           => $coupondata->ID,
                                'post_status'   => 'expired',
                            );

                            // Update the post into the database
                            wp_update_post( $my_post );
                        }
                    }
                    if(!(empty($params['new_coupon']) && empty($params['applied_coupon']))){
                        //TODO: Handle response
                        $timestamp = self::getScheduleRunTime();
                        $campaign_site_url = self::getCampaignRabbitAPIURL('track', $params);
                        as_schedule_single_action( $timestamp, 'woo_email_customizer_page_builder_next_order_coupon_applied', array('url' => $campaign_site_url, 'params' => array(), 'type' => 'GET') );
                    }

//                if(!empty($applied_coupon_code)){
//                    $campaign_site_url = self::getCampaignRabbitAPIURL('coupon/status/order');
//                    //$event = wp_schedule_single_event( $timestamp, 'woo_email_customizer_page_builder_next_order_coupon_applied', array('url' => 'coupon/status/order', 'params' => $params, 'type' => 'PUT') );
//                    as_schedule_single_action( $timestamp, 'woo_email_customizer_page_builder_next_order_coupon_applied', array('url' => $campaign_site_url, 'params' => $params, 'type' => 'PUT') );
//                } else {
//                    $campaign_site_url = self::getCampaignRabbitAPIURL('coupon');
//                    as_schedule_single_action( $timestamp, 'woo_email_customizer_page_builder_next_order_coupon_created', array('url' => $campaign_site_url, 'params' => $params, 'type' => 'POST') );
//                }

                }
            }
        }
    }

    public static function getScheduleRunTime(){
        return time() + 60;
    }

    /**
     * Get default parameter for analytics
     * */
    public static function getDefaultParameterToSendForAnalytics($order){
        $created_date = $order->get_date_created();
        return array(
            'order_id' => $order->get_id(),
            'email' => $order->get_billing_email(),
            'firstname' => $order->get_billing_first_name(),
            'lastname' => $order->get_billing_last_name(),
            'total' => (string)$order->get_total(),
            'order_date' => strtotime($created_date),
        );
    }

    /**
     * To generate random Coupon code
     * */
    public static function generateRandomCouponCode(){
        $length = 8;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';
        for ($p = 0; $p < $length; $p++){
            $string .= $characters[mt_rand(0, strlen($characters))];
        }
        if(function_exists('wc_strtoupper')){
            return wc_strtoupper($string);
        } else {
            return strtoupper($string);
        }
    }

    /**
     * Get campaign rabbit api URL
     * */
    public static function getCampaignRabbitAPIURL($request, $params = array()){
        $settings = WooEmailCustomizerCommon::getEmailCustomizerSettingsForAPI();
        $campaign_app_id = isset($settings->app_id)? $settings->app_id: '';
        $additional_param = '';
        if(!empty($params) && is_array($params)){
            foreach ($params as $key => $value){
                $additional_param .= '&'.$key.'='.urlencode($value);
            }
        }
        $campaign_site_url = WOO_ECPB_RETAINFUL_API_URL.$request.'?app_id='.$campaign_app_id.$additional_param;

        return $campaign_site_url;
    }

    /**
     * send a request
     * */
    public static function sendCampaignRabbitAPIRequest($url, $type = 'GET', $request = array()){
//        $settings = WooEmailCustomizerCommon::getEmailCustomizerSettingsForAPI();
//        $campaign_app_id = isset($settings->app_id)? $settings->app_id: '';
        $response_data = '';
        if(is_callable('curl_init')) {
            try {
                $curl_options = array();
                $curl_options[CURLOPT_RETURNTRANSFER] = 1;
                $curl_options[CURLOPT_URL] = $url;
                $curl_options[CURLOPT_USERAGENT] = 'WooCommerce Email Customizer';
                $curl_options[CURLOPT_HTTPHEADER] = array('Origin: ' . self::getDomainName(), 'Request-From-Domain: '.self::getDomainName());
                $curl_options[CURLOPT_REFERER] = self::getDomainName();
                if($type == 'POST'){
//                    $request['app_id'] = $campaign_app_id;
                    $curl_options[CURLOPT_POST] = true;
                    $curl_options[CURLOPT_POSTFIELDS] = http_build_query($request);
                } else if($type == 'PUT'){
//                    $request['app_id'] = $campaign_app_id;
                    $curl_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                    $curl_options[CURLOPT_POSTFIELDS] = http_build_query($request);
                }

                $curl = curl_init();
                curl_setopt_array($curl, $curl_options);

                // Send the request & save response to $resp
                $response_data = curl_exec($curl);
                if(is_string($response_data)){
                    try{
                        $response_data = json_decode($response_data);
                    } catch (Exception $e){}

                }
                // Close request to clear up some resources
                curl_close($curl);
            } catch (Exception $e) {
                //
            }
        }

        return $response_data;
    }

    protected static function getDomainName(){
        // server protocol
        $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        // domain name
        $server_domain = $_SERVER['SERVER_NAME'];
        $domain = $protocol.$server_domain;

        return $domain;
    }

    /**
     * Add coupon code to order meta for sending through email
     * */
    public static function addNextCouponCodeToOrder($order_id, $data){
        $disable_existing_coupon = self::disableExistingCoupons();
        $has_retainful_plugin = self::runThroughThirdPartyPlugin();
        if(!$disable_existing_coupon){
            $enabled_coupon_integration = self::isAPIIntegrationEnabled();
            if($enabled_coupon_integration){
                $wooVersion3 = version_compare( WOO_ECPB_WOOCOMMERCE_VERSION, '3.0', ">=" );
                if($wooVersion3) {
                    $order = wc_get_order($order_id);
                    if(!$has_retainful_plugin){
                        $exists = self::checkCouponExists($order_id);
                        if (empty($exists)) {
                            $new_coupon_code = self::generateRandomCouponCode();
                            $email = $order->get_billing_email();
                            self::addEmailCustomizerCouponCode($new_coupon_code, $order_id, $email);
                        } else {
                            $new_coupon_code = $exists;
                        }
                        update_post_meta($order_id, '_flycart_emc_coupon_code', $new_coupon_code);
                    }

                    $used_coupons = self::getUsedCoupons($order);//$order->get_used_coupons();
                    if(!empty($used_coupons)){
                        foreach( $used_coupons as $coupon) {
                            $coupon_data = self::getCouponData($coupon);
                            if(!empty($coupon_data)){
                                update_post_meta($order_id, '_flycart_emc_coupon_code_applied', $coupon);
                                update_post_meta($coupon_data->ID, 'applied_for', $order_id);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get used coupons of order
     * @param $order
     * @return null
     */
    public static function getUsedCoupons($order)
    {
        if (version_compare(WOO_ECPB_WOOCOMMERCE_VERSION, '3.7.0', '<')) {
            if (method_exists($order, 'get_used_coupons')) {
                return $order->get_used_coupons();
            }
        } else {
            if (method_exists($order, 'get_coupon_codes')) {
                return $order->get_coupon_codes();
            }
        }

        return null;
    }

    /**
     * Check Coupon code already exists
     * */
    public static function checkCouponExists($order_id){
        $post_args = array('post_type' => 'flycart_wmc_coupon', 'numberposts' => '1', 'post_status' => 'publish');
        $post_args['meta_key'] = 'order_id';
        $post_args['meta_value'] = $order_id;
        $posts = get_posts($post_args);
        if(!empty($posts)){
            if(isset($posts[0]->post_title)){
                return $posts[0]->post_title;
            }
        }
        return '';
    }

    /**
     * Check Coupon code already exists
     * */
    public static function checkIsValidCoupon($coupon, $order = null){
        $settings = WooEmailCustomizerCommon::getEmailCustomizerSettingsForAPI();
        $use_coupon_by = isset($settings->use_coupon_by)? $settings->use_coupon_by: 'all';
        $post_args = array('post_type' => 'flycart_wmc_coupon', 'numberposts' => '1', 'post_status' => 'publish', 'title' => strtoupper($coupon));
        $posts = get_posts($post_args);
        if(!empty($posts)){
            if($use_coupon_by != 'all'){
                if(!empty($order)){
                    $current_user_id = $order->get_user_id();
                    if($use_coupon_by != 'login_users') {
                        $current_email = $order->get_billing_email();
                    }
                } else {
                    $current_user_id = get_current_user_id();
                    if($use_coupon_by != 'login_users') {
                        $current_email = self::getCurrentEmail();
                    }
                }
            }

            foreach ($posts as $post){
                if($use_coupon_by == 'all'){
                    return $post;
                } else if($use_coupon_by == 'login_users'){
                    $user_id = get_post_meta($post->ID, 'user_id', true);
                    if($current_user_id == $user_id) return $post;
                } else {
                    $user_id = get_post_meta($post->ID, 'user_id', true);
                    $email = get_post_meta($post->ID, 'email', true);
                    if(!empty($current_user_id) || !empty($current_email)){
                        if($current_user_id == $user_id || $current_email == $email) return $post;
                    } else if(empty($current_user_id) && empty($current_email)){
                        return $post;
                    }
                }
            }
        }
        return '';
    }

    /**
     * Check Coupon code already exists
     * */
    public static function getCouponData($coupon){
        $post_args = array('post_type' => 'flycart_wmc_coupon', 'numberposts' => '1', 'title' => strtoupper($coupon));
        $posts = get_posts($post_args);
        if(!empty($posts)){
            $settings = WooEmailCustomizerCommon::getEmailCustomizerSettingsForAPI();
            $use_coupon_by = isset($settings->use_coupon_by)? $settings->use_coupon_by: 'all';
            if($use_coupon_by != 'all'){
                $current_user_id = get_current_user_id();
                if($use_coupon_by != 'login_users') {
                    $current_email = self::getCurrentEmail();
                }
            }

            foreach ($posts as $post){
                if($use_coupon_by == 'all'){
                    return $post;
                } else if($use_coupon_by == 'login_users'){
                    $user_id = get_post_meta($post->ID, 'user_id', true);
                    if($current_user_id == $user_id) return $post;
                } else {
                    $user_id = get_post_meta($post->ID, 'user_id', true);
                    $email = get_post_meta($post->ID, 'email', true);
                    if($current_user_id == $user_id || $current_email == $email) return $post;
                }
            }
        }
        return '';
    }

    public static function getCurrentEmail(){
        $postData = isset($_REQUEST['post_data'])? $_REQUEST['post_data']: '';
        $postDataArray = array();
        if(is_string($postData) && $postData != ''){
            parse_str($postData, $postDataArray);
        }
        $postBillingEmail = isset($_REQUEST['billing_email'])? $_REQUEST['billing_email']: '';
        if($postBillingEmail != ''){
            $postDataArray['billing_email'] = $postBillingEmail;
        }
        if(!get_current_user_id()){
            $order_id = isset($_REQUEST['order-received'])? $_REQUEST['order-received']: 0;
            if($order_id){
                $order = wc_get_order($order_id);
                $postDataArray['billing_email'] = $order->get_billing_email();
            }
        }
        $user_email = '';
        if(isset($postDataArray['billing_email']) && $postDataArray['billing_email'] != ''){
            $user_email = $postDataArray['billing_email'];
        } else if(get_current_user_id()){
            $user_email = get_user_meta( get_current_user_id(), 'billing_email', true );
            if($user_email != '' && !empty($user_email)){
                return $user_email;
            } else {
                $user_details = get_userdata( get_current_user_id() );
                if(isset($user_details->data->user_email) && $user_details->data->user_email != ''){
                    $user_email = $user_details->data->user_email;
                    return $user_email;
                }
            }
        }

        return $user_email;
    }

    /**
     * Add email customizer coupon code
     * */
    public static function addEmailCustomizerCouponCode($new_coupon_code, $order_id, $email){
        $post = array(
            'post_title' => $new_coupon_code,
            'post_name' => $new_coupon_code.'-'.$order_id,
            'post_content' => 'Virtual coupon code created through WooCommerce Email Customizer with Drag and Drop Email Builder',
            'post_type' => 'flycart_wmc_coupon',
            'post_status' => 'publish'
        );
        $id = wp_insert_post($post);
        if($id){
            $settings = WooEmailCustomizerCommon::getEmailCustomizerSettingsForAPI();
            add_post_meta($id, 'order_id', $order_id);
            add_post_meta($id, 'email', $email);
            $user_id = get_current_user_id();
            add_post_meta($id, 'user_id', $user_id);
            $discount_type = isset($settings->discount_type)? $settings->discount_type: 'percent';
            $discount_value = isset($settings->discount_value)? $settings->discount_value: '';
            add_post_meta($id, 'coupon_type', $discount_type);
            add_post_meta($id, 'coupon_value', $discount_value);
        }
        return $id;
    }

    /**
     * Add virtual coupon to cart
     * */
    public static function addVirtualCoupon($response, $coupon_code){
        $disable_existing_coupon = self::disableExistingCoupons();
        if($disable_existing_coupon) return $response;

        if(empty($coupon_code)) return $response;
        $coupon_data = self::checkIsValidCoupon($coupon_code);
        if(!empty($coupon_data)){
            $coupon_applied_already = false;
            if(!empty(self::$applied_coupon) && self::$applied_coupon != $coupon_code){
                $coupon_applied_already = true;
            }
            if(isset($coupon_data->ID) && $coupon_data->ID && !$coupon_applied_already){
                self::$applied_coupon = $coupon_code;
                $coupon_type = get_post_meta($coupon_data->ID, 'coupon_type', true);
                $coupon_value = get_post_meta($coupon_data->ID, 'coupon_value', true);
                $remove_coupon = isset($_REQUEST['remove_coupon'])? $_REQUEST['remove_coupon']: false;
                if ($remove_coupon == $coupon_code) return false;
                if($coupon_type == 'percent'){
                    $discount_type = 'percent';
                } else {
                    $discount_type = 'fixed_cart';
                }

                $amount = $coupon_value;

                $coupon = array(
                    'id' => 321123 . rand(2, 9),
                    'amount' => $amount,
                    'individual_use' => false,
                    'product_ids' => array(),
                    'exclude_product_ids' => array(),
                    'usage_limit' => '',
                    'usage_limit_per_user' => '',
                    'limit_usage_to_x_items' => '',
                    'usage_count' => '',
                    'expiry_date' => '',
                    'apply_before_tax' => 'yes',
                    'free_shipping' => false,
                    'product_categories' => array(),
                    'exclude_product_categories' => array(),
                    'exclude_sale_items' => false,
                    'minimum_amount' => '',
                    'maximum_amount' => '',
                    'customer_email' => '',
                );
                $wooVersion3 = version_compare( WOO_ECPB_WOOCOMMERCE_VERSION, '3.2', ">=" );
                if($wooVersion3) {
                    $coupon['discount_type'] = $discount_type;
                } else {
                    $coupon['type'] = $discount_type;
                }
                /*
                $email = self::getCurrentEmail();
                if(!empty($email)){
                    $params['email'] = $email;
                    $campaign_site_url = self::getCampaignRabbitAPIURL('coupon/'.strtoupper($coupon_code).'/order', $params);
                    //$response = self::sendCampaignRabbitAPIRequest($campaign_site_url);
                    //TODO: handle response
                    $timestamp = self::getScheduleRunTime();
                    //$event = wp_schedule_single_event( $timestamp, 'woo_email_customizer_page_builder_next_order_coupon_applied_in_cart', array('url' => $campaign_site_url) );
                    as_schedule_single_action( $timestamp, 'woo_email_customizer_page_builder_next_order_coupon_applied_in_cart', array('url' => $campaign_site_url) );
                }*/

                return $coupon;
            }
        }

        return $response;
    }

    public static function isAPIIntegrationEnabled(){
        $settings_api = WooEmailCustomizerCommon::getEmailCustomizerSettingsForAPI();
        $app_id = isset($settings_api->app_id)? $settings_api->app_id: '';
        $validated_app_id = isset($settings_api->validated_app_id)? $settings_api->validated_app_id: 0;
        if(!empty($app_id) && $validated_app_id){
            return true;
        }
        return false;
    }

    /**
     * Process coupon short code container
     * */
    public static function process_coupon_code_short_code_container($html, $short_code){
        $has_coupon = false;
        if(isset($short_code['[wec_next_order_coupon]']) && !empty($short_code['[wec_next_order_coupon]'])){
            $has_coupon = true;
        }
        $short_code_matches = self::parseShortCodes($html);
        if(isset($short_code_matches[0]) && count($short_code_matches[0])) {
            foreach ( $short_code_matches as $single_match ) {
                if(isset($single_match[0]) && isset($single_match[1])){
                    if($has_coupon){
                        $html = str_replace($single_match[0], $single_match[1], $html);
                    } else {
                        $html = str_replace($single_match[0], '', $html);
                    }
                }
            }
        }

        return $html;
    }

    /**
     * parse shortcode to process coupon short code container
     * */
    protected static function parseShortCodes($html) {
        $regex		= "'{wem_coupon_con_identifier}(.*?){\\/wem_coupon_con_identifier}'is";
        preg_match_all($regex, $html, $new_matches, PREG_SET_ORDER);

        return $new_matches;
    }

    /**
     * Validate App Id
     * */
    public static function validateAppId($app_id){
        if(!empty($app_id)){
            $campaign_site_url = self::getCampaignRabbitAPIURL('app/'.$app_id);
            $response = self::sendCampaignRabbitAPIRequest($campaign_site_url);
            if(isset($response->success) && $response->success){
                return true;
            }
        }

        return false;
    }

    /**
     * To get dashboard URL
     * */
    public static function get_dashboard_url(){
        return 'https://app.retainful.com/';
    }

    /**
     * To get download URL
     * */
    public static function get_download_url(){
        return 'https://www.retainful.com/product/features/woocommerce';
    }

    /**
     * get coupon value
     * */
    public static function getCouponValueForShortCode($coupon_code, $order){
        $text = '';
        $coupon_data = self::checkIsValidCoupon($coupon_code, $order);
        if(!empty($coupon_data)){
            if(isset($coupon_data->ID) && $coupon_data->ID) {
                $coupon_type = get_post_meta($coupon_data->ID, 'coupon_type', true);
                $coupon_value = get_post_meta($coupon_data->ID, 'coupon_value', true);
                if($coupon_type == 'percent'){
                    $text = $coupon_value.'%';
                } else {
                    $text = wc_price($coupon_value);
                }
            }
        }

        return $text;
    }

    /**
     * Set coupon in session
     * */
    public static function get_custom_coupon_code_to_session(){
        $disable_existing_coupon = self::disableExistingCoupons();
        if(!$disable_existing_coupon){
            if( isset($_REQUEST['wec_coupon_code']) ){
                $coupon_code = WC()->session->get('wec_coupon_code');
                if(empty($coupon_code)){
                    $coupon_code = esc_attr( $_REQUEST['wec_coupon_code'] );
                    WC()->session->set( 'wec_coupon_code', $coupon_code ); // Set the coupon code in session
                }
            }
        }
    }

    /**
     * Apply coupon from session
     * */
    public static  function add_coupon_to_checkout( ) {
        $disable_existing_coupon = self::disableExistingCoupons();
        if(!$disable_existing_coupon){
            $coupon_code = WC()->session->get('wec_coupon_code');
            $cart = WC()->cart;
            if(!empty($cart)){
                if ( ! empty( $coupon_code ) && ! WC()->cart->has_discount( $coupon_code ) ){
                    WC()->cart->add_discount( $coupon_code ); // apply the coupon discount
                    WC()->session->__unset('wec_coupon_code'); // remove coupon code from session
                }
            }
        }
    }

    /**
     * Run scheduled tasks
     * */
    public static function runScheduledRequest($url, $params = array(), $type = 'GET'){
        if(!empty($url)) $url = htmlspecialchars_decode($url);
        //self::log($url);
        $response = self::sendCampaignRabbitAPIRequest($url, $type, $params);
        //TODO: Handle response
    }

    /**
     * For doing log
     * */
    public static function log($data){
        if(is_array($data) || is_object($data)){
            $data = json_encode($data);
        }
        $file = wp_upload_dir('log', true, true);
        if(!empty($file)){
            $file_path = $file['path'].'email_customizer/log.txt';
            $f = fopen($file_path, 'a');
            fwrite($f, "\n\n" . date('Y-m-d H:i:s'));
            fwrite($f, "\n" . $data);
            fclose($f);
        }

    }

    /**
     * Run retainful coupon through third party plugin
     * */
    public static function runThroughThirdPartyPlugin(){
        return apply_filters('woo_email_drag_and_drop_builder_handling_retainful', false);
    }

    /**
     * Run retainful coupon through third party plugin
     * */
    public static function disableExistingCoupons(){
        return apply_filters('woo_email_drag_and_drop_builder_retainful_disable_existing_coupon', false);
    }

    /**
     * get retainful settings page url
     * */
    public static function getSettingsPageURL(){
        $url = admin_url("admin.php?page=woo_email_customizer_page_builder&settings=integrations");
        return apply_filters('woo_email_drag_and_drop_builder_retainful_settings_url', $url);
    }
}