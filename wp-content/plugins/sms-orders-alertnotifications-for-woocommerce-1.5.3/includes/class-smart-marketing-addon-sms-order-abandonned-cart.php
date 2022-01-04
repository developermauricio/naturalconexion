<?php

/**
 * Class Smart_Marketing_Addon_Sms_Order_Abandoned_Cart
 */
class Smart_Marketing_Addon_Sms_Order_Abandoned_Cart {

    const SESSION_TAG_UID = 'egoi_tracking_uid';
    const EGOI_ABANDONED_CART_STANDBY = 'standby';

    protected $helper;

    /**
     * @return false
     */
    public function start(){
        if ( WC()->cart->is_empty() ) { return false; }
        $abandoned_cart_obj = json_decode(get_option('egoi_sms_abandoned_cart'), true);


        if(!empty($abandoned_cart_obj["enable"]) && $abandoned_cart_obj["enable"] == "on") {
            $this->helper = new Smart_Marketing_Addon_Sms_Order_Helper();
            //$url = self::getCartUrl($this->getCartSession());//url recuperação carrinho

	        $wooSessionKey = $this->getCartSession();

	        if ( (!empty($_POST['action']) && $_POST['action'] == 'egoiSaveCellphone')  && !empty($_POST['prefixEgoiphone']) && !empty($_POST['egoiPhone']) && !empty($wooSessionKey) ){
		        $_SESSION[self::SESSION_TAG_UID] = (int) $_POST['prefixEgoiphone'] . '-' . (int) str_replace(' ', '', $_POST['egoiPhone']);
	        }

	        $cellPhone = $this->isKnowNumber();

	        if (empty($cellPhone)) {
                require_once plugin_dir_path( dirname( __FILE__ ) ).'includes/class-smart-marketing-addon-sms-order-cellphone-popup.php';
                $popUp = new Smart_Marketing_Addon_Sms_Order_Cellphone_Popup();
                $popUp->printPopup();
            } else {

                if (!empty($wooSessionKey)) {
                    $items = $this->getCartBySessionId($wooSessionKey);

                    if (!empty($items)) {
                        return $this->manageEgoiAbandonedCart($wooSessionKey, $cellPhone);
                    }
                }
            }
        }
    }

	/**
	 * @param int $order_id
	 *
	 * @return false
	 */
    public function convertCart($order_id = 0){
        if(empty($_SESSION['sid_eg'])){//didnt came from sms url
            return false;
        }

        try{
            global $wpdb;
            $wpdb->update($wpdb->prefix.'egoi_sms_abandoned_carts', ['status' => 'sold', 'order_id' => $order_id], ['php_session_key' => $_SESSION['sid_eg']]);
            unset($_SESSION['sid_eg']);
        } catch (Exception $e){
			//do nothing
        }
    }


    /**
     * @param $wc_session
     * @return string
     */
    public static function getCartUrl($wc_session){
        $cartArray = self::getCartBySessionId($wc_session);
        return wc_get_checkout_url().self::cartArrayToUrlParam($cartArray,$wc_session);
    }

    /**
     * @param $cartArray
     * @param $wc_session
     * @return string
     */
    public static function cartArrayToUrlParam($cartArray,$wc_session){
        global $wpdb;
        $query = sprintf(
            "SELECT %s FROM %s%s WHERE %s = '%s' AND %s = '%s'",
            'php_session_key',
            $wpdb->prefix,
            'egoi_sms_abandoned_carts',
            'woo_session_key',
            $wc_session,
            'status',
            'waiting'
        );

        $result = $wpdb->get_var($query);

        $output='?create-cart=';
        foreach ($cartArray as $product_id => $quantity){
            $output .= "$product_id:$quantity,";
        }
        return $output.(!empty($result))?'&sid_eg='.$result:'';
    }

    /**
     * @return bool|string
     */
    private function isKnowNumber(){
        $cellphone = $this->getCellphoneLogged();
        //not logged
        if($cellphone === false){

            if(empty($_SESSION[self::SESSION_TAG_UID])){
                return false;
            }
	        $cellphone = $_SESSION[self::SESSION_TAG_UID];
        }else if ($cellphone === ''){//logged but no phone
            return false;
        }
        return $cellphone;
    }

    /**
     *
     */
    public function saveAbandonedCart(){

    }

    /**
     * @return @return string[]|false
     */
    private function getCartSession(){
        $separator = '%7C%7C';
        $needle = 'wp_woocommerce_session_';
        if(!is_array($_COOKIE)){return false;}

        foreach ($_COOKIE as $key => $value) {
            if(strpos($key,'woocommerce_session_') !== false){
                return explode('||',$value)[0];
            }
        }

        return [];
    }

    /**
     *
     */
    private function isSavedCart(){

    }

    /**
     * @param $wc_session
     * @return array|false
     */
    private static function getCartBySessionId($wc_session){

        global $wpdb;
        $query = sprintf(
            "SELECT %s FROM %s%s WHERE %s = '%s'",
            'session_value',
            $wpdb->prefix,
            'woocommerce_sessions',
            'session_key',
            $wc_session
        );

        $result = $wpdb->get_var($query);
        if(empty($result)){return false;}
        $cart = unserialize(unserialize($result)['cart']);

        $output = [];
        foreach ($cart as $item){
            if(!empty($item['variation_id'])){
                $output[$item['variation_id']] = $item['quantity'];
                continue;
            }
            $output[$item['product_id']] = $item['quantity'];
        }
        return $output;
    }

    /**
     * Manages Egoi Abandoned Carts
     *
     * @param string $wooSessionKey
     * @param string $cellPhone
     * @return bool
     */
    private function manageEgoiAbandonedCart($wooSessionKey, $cellPhone)
    {
        global $wpdb;

	    $phpSessionKey = !empty($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : null;

	    if(!empty($_SESSION['sid_eg'])){// came from sms url so is recovering the cart
		    return false;
	    }

        if ( empty($wooSessionKey) || empty($cellPhone) || empty($phpSessionKey) ) {
            return false;
        }

        $table = $wpdb->prefix."egoi_sms_abandoned_carts";

        $wpdb->delete(
            $table,
            array(
                'woo_session_key' => $wooSessionKey,
                'status' => self::EGOI_ABANDONED_CART_STANDBY
            )
        );

        $wpdb->delete(
            $table,
            array(
                'php_session_key' => $phpSessionKey,
                'status' => self::EGOI_ABANDONED_CART_STANDBY
            )
        );

        $wpdb->insert(
            $table,
            array(
                "time" => current_time('mysql'),
                "woo_session_key" => $wooSessionKey,
                "php_session_key" => $phpSessionKey,
                "cellphone" => $cellPhone,
                "status" => self::EGOI_ABANDONED_CART_STANDBY
            )
        );

        return true;
    }

    /**
     * Return false if not logged
     * empty string if cellphone is not found
     *
     * @return bool | string
     */
    private function getCellphoneLogged(){
        $current_user = wp_get_current_user();
        if(!$current_user->exists()){
            return false;
        }
        $billing_phone = get_user_meta( $current_user->ID, 'billing_phone', true );
        $billing_country = get_user_meta( $current_user->ID, 'billing_country', true );
        if(empty($billing_phone)){return '';}
        return $this->helper->smsonw_get_valid_recipient($billing_phone, $billing_country);
    }
}