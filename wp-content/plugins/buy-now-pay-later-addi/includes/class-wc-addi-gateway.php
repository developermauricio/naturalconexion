<?php
class WC_Addi_Gateway extends WC_Payment_Gateway {
    /**
     * Class constructor
     */
    public function __construct() {

        global $woocommerce;
        global $post;
        global $wp;
        global $wpdb;

        $background_color = get_background_color();

        // Define plugin attributes.
        $this->id = 'addi';
        $this->icon = strpos($background_color, '000' ) !== false ? plugins_url( '../assets/ADDI_logo_white.png', __FILE__ ) : plugins_url( '../assets/ADDI_logo.png', __FILE__ );
        $this->has_fields = false;
        $this->method_title = _x( 'Addi', 'Addi', 'buy-now-pay-later-addi' );
        $this->method_description = __( 'Pago a cuotas - ADDI.', 'buy-now-pay-later-addi' );

        $this->supports = array(
            'products'
        );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        // Pre defined , this cannot be changed.
        $this->title = __( 'Paga a cuotas', 'buy-now-pay-later-addi' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->widget_enabled = $this->get_option( 'widget_enabled' );
        $this->widget_slug  = $this->get_option( 'widget_slug' );

        $this->description  = $this->get_option( 'description' );
        $this->field_billing_first_name = $this->get_option( 'field_billing_first_name' );
        $this->field_billing_last_name = $this->get_option( 'field_billing_last_name' );
        $this->field_id = $this->get_option( 'field_id' );
        $this->field_billing_city = $this->get_option( 'field_billing_city' );
        $this->field_billing_email = $this->get_option( 'field_billing_email' );
        $this->field_billing_phone = $this->get_option( 'field_billing_phone' );

        $this->prod_client_id = $this->get_option( 'prod_client_id' );
        $this->prod_client_secret = $this->get_option( 'prod_client_secret' );
        $this->testmode = 'yes' === $this->get_option( 'testmode' );
        $this->custom_order_status = 'yes' === $this->get_option( 'custom_order_status' );
        $this->logs = 'yes' === $this->get_option( 'logs' );
        // Pre defined , this cannot be changed.
        $this->callback_user = 'AddiWooCommercePlugin2021';
        $this->callback_password = 'jDb!mW!ePWjt9z6';

        //Widget position
        $this->conf_widget_position = $this->get_option( 'conf_widget_position' );

        //Widget Css properties
        $this->widgetBorderColor = $this->get_option( 'widgetBorderColor' );
        $this->widgetBorderRadius = $this->get_option( 'widgetBorderRadius' );
        $this->widgetFontColor = $this->get_option( 'widgetFontColor' );
        $this->widgetFontFamily = $this->get_option( 'widgetFontFamily' );
        $this->widgetFontSize = $this->get_option( 'widgetFontSize' );
        $this->widgetBadgeBackgroundColor = $this->get_option( 'widgetBadgeBackgroundColor' );
        $this->widgetInfoBackgroundColor = $this->get_option( 'widgetInfoBackgroundColor' );
        $this->widgetMargin = $this->get_option( 'widgetMargin' );
        //Modal Css properties
        $this->modalBackgroundColor = $this->get_option( 'modalBackgroundColor' );
        $this->modalFontColor = $this->get_option( 'modalFontColor' );
        $this->modalPriceColor = $this->get_option( 'modalPriceColor' );
        $this->modalBadgeBackgroundColor = $this->get_option( 'modalBadgeBackgroundColor' );
        $this->modalBadgeBorderRadius = $this->get_option( 'modalBadgeBorderRadius' );
        $this->modalBadgeFontColor = $this->get_option( 'modalBadgeFontColor' );
        $this->modalBadgeLogoStyle = 'yes' === $this->get_option( 'modalBadgeLogoStyle' );
        $this->modalCardColor = $this->get_option( 'modalCardColor' );
        $this->modalButtonBorderColor = $this->get_option( 'modalButtonBorderColor' );
        $this->modalButtonBorderRadius = $this->get_option( 'modalButtonBorderRadius' );
        $this->modalButtonBackgroundColor = $this->get_option( 'modalButtonBackgroundColor' );
        $this->modalButtonFontColor = $this->get_option( 'modalButtonFontColor' );

        //Widget Home properties
        $this->field_widget_position = $this->get_option( 'field_widget_position' );
        $this->field_widget_type = $this->get_option( 'field_widget_type' );
        $this->element_reference = $this->get_option( 'element_reference' );
        $this->widget_home_enabled = $this->get_option( 'widget_home_enabled' );

        // action hook to update options to new payment gateway
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        // action hook to link css / javascripts files or related to it.
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

        // action hook to init callback_handler function with the class
        add_action('init', 'addi_callback_handler');

        add_action( 'woocommerce_review_order_before_submit', array( $this, 'log_gateway_loaded' ));

        // action hook to register callack function to a woocommerce rest api
        add_action( 'woocommerce_api_wc_addi_gateway', array( $this, 'addi_callback_handler', ) );

        add_action('update_option', function( $option_name, $old_value, $value ) {

            global $wpdb;

            $table_config_name = $wpdb->prefix . "wc_addi_config";

            $newValue = $this->get_option( 'widget_enabled' );
            $newSlug = $this->get_option( 'widget_slug' );
            $newWidgetPosition = $this->get_option( 'conf_widget_position' );

            //field id control
            $newfieldId = $this->get_option( 'field_id' );

            //custom order status
            $customOrderStatus = $this->get_option( 'custom_order_status' );

            //Widget Home
            $newFieldWidgetPosition = $this->get_option( 'field_widget_position' );
            $newFieldWidgetType = $this->get_option( 'field_widget_type' );
            $newElementReference = $this->get_option( 'element_reference' );
            $newWidgetHomeEnabled = $this->get_option( 'widget_home_enabled' );

            $modalBadgeLogoStyleValue_ = 'false';

            switch($newFieldWidgetPosition) {
                case 'on_header':
                    $newElementReference = 'header';
                    break;
                case 'on_footer':
                    $newElementReference = '#content';
                    break;
            }

            //variables for widget css
            $widgetBorderColor_ = $this->get_option( 'widgetBorderColor' );
            $widgetBorderRadius_ = $this->get_option( 'widgetBorderRadius' );
            $widgetFontColor_ = $this->get_option( 'widgetFontColor' );
            $widgetFontFamily_ = $this->get_option( 'widgetFontFamily' );
            $widgetFontSize_ = $this->get_option( 'widgetFontSize' );
            $widgetBadgeBackgroundColor_ = $this->get_option( 'widgetBadgeBackgroundColor' );
            $widgetInfoBackgroundColor_ = $this->get_option( 'widgetInfoBackgroundColor' );
            $widgetMargin_ = $this->get_option( 'widgetMargin' );
            //varibles for modal css
            $modalBackgroundColor_ = $this->get_option( 'modalBackgroundColor' );
            $modalFontColor_ = $this->get_option( 'modalFontColor' );
            $modalPriceColor_ = $this->get_option( 'modalPriceColor' );
            $modalBadgeBackgroundColor_ = $this->get_option( 'modalBadgeBackgroundColor' );
            $modalBadgeBorderRadius_ = $this->get_option( 'modalBadgeBorderRadius' );
            $modalBadgeFontColor_ = $this->get_option( 'modalBadgeFontColor' );
            $modalBadgeLogoStyle_ = 'yes' === $this->get_option( 'modalBadgeLogoStyle' );
            $modalCardColor_ = $this->get_option( 'modalCardColor' );
            $modalButtonBorderColor_ = $this->get_option( 'modalButtonBorderColor' );
            $modalButtonBorderRadius_ = $this->get_option( 'modalButtonBorderRadius' );
            $modalButtonBackgroundColor_ = $this->get_option( 'modalButtonBackgroundColor' );
            $modalButtonFontColor_ = $this->get_option( 'modalButtonFontColor' );

            $modalBadgeLogoStyleValue_ = ($modalBadgeLogoStyle_ == 1 || $modalBadgeLogoStyle_ == '1')  ?
                'true' : 'false';

            if($option_name == 'woocommerce_addi_settings') {

                $result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget"));

                if(isset($result) && count($result) > 0) {
                    $wpdb->update( $table_config_name, array( 'value' => $newValue . '|' . $newSlug), array( 'element' => 'widget' ));
                }
                else {
                    $wpdb->insert($table_config_name, array('element' => 'widget', 'value' => $newValue . '|' . $newSlug));
                }

                /* FIELD ID */
                $resultFI = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","field_id"));

                if(isset($resultFI) && count($resultFI) > 0) {
                    $wpdb->update( $table_config_name, array( 'value' => $newfieldId), array( 'element' => 'field_id' ));
                }
                else {
                    $wpdb->insert($table_config_name, array('element' => 'field_id', 'value' => $newfieldId));
                }
                /* FIELD ID */

                /* WIDGET POSITION */
                $resultV = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget_position"));

                if(isset($resultV) && count($resultV) > 0) {
                    $wpdb->update( $table_config_name, array( 'value' => $newWidgetPosition), array( 'element' => 'widget_position' ));
                }
                else {
                    $wpdb->insert($table_config_name, array('element' => 'widget_position', 'value' => $newWidgetPosition));
                }
                /* WIDGET POSITION */

                $conf_result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element like %s","conf_%"));

                if(isset($conf_result) && count($conf_result) > 0) {
                    //update statements for widget css
                    $wpdb->update( $table_config_name, array( 'value' => $widgetBorderColor_), array( 'element' => 'conf_widgetBorderColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $widgetBorderRadius_), array( 'element' => 'conf_widgetBorderRadius' ));
                    $wpdb->update( $table_config_name, array( 'value' => $widgetFontColor_), array( 'element' => 'conf_widgetFontColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $widgetFontFamily_), array( 'element' => 'conf_widgetFontFamily' ));
                    $wpdb->update( $table_config_name, array( 'value' => $widgetFontSize_), array( 'element' => 'conf_widgetFontSize' ));
                    $wpdb->update( $table_config_name, array( 'value' => $widgetBadgeBackgroundColor_), array( 'element' => 'conf_widgetBadgeBackgroundColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $widgetInfoBackgroundColor_), array( 'element' => 'conf_widgetInfoBackgroundColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $widgetMargin_), array( 'element' => 'conf_widgetMargin' ));
                    //update statements for modal css
                    $wpdb->update( $table_config_name, array( 'value' => $modalBackgroundColor_), array( 'element' => 'conf_modalBackgroundColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalFontColor_), array( 'element' => 'conf_modalFontColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalPriceColor_), array( 'element' => 'conf_modalPriceColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalBadgeBackgroundColor_), array( 'element' => 'conf_modalBadgeBackgroundColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalBadgeBorderRadius_), array( 'element' => 'conf_modalBadgeBorderRadius' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalBadgeFontColor_), array( 'element' => 'conf_modalBadgeFontColor' ));
                    if(count($conf_result) <= 19) {
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeLogoStyle', 'value' => $modalBadgeLogoStyleValue_));
                    }
                    else {
                        $wpdb->update( $table_config_name, array( 'value' => $modalBadgeLogoStyleValue_), array( 'element' => 'conf_modalBadgeLogoStyle' ));
                    }

                    $wpdb->update( $table_config_name, array( 'value' => $modalCardColor_), array( 'element' => 'conf_modalCardColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalButtonBorderColor_), array( 'element' => 'conf_modalButtonBorderColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalButtonBorderRadius_), array( 'element' => 'conf_modalButtonBorderRadius' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalButtonBackgroundColor_), array( 'element' => 'conf_modalButtonBackgroundColor' ));
                    $wpdb->update( $table_config_name, array( 'value' => $modalButtonFontColor_), array( 'element' => 'conf_modalButtonFontColor' ));
                }
                else {
                    //insert statements for widget css
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetBorderColor', 'value' => $widgetBorderColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetBorderRadius', 'value' => $widgetBorderRadius_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetFontColor', 'value' => $widgetFontColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetFontFamily', 'value' => $widgetFontFamily_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetFontSize', 'value' => $widgetFontSize_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetBadgeBackgroundColor', 'value' => $widgetBadgeBackgroundColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetInfoBackgroundColor', 'value' => $widgetInfoBackgroundColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_widgetMargin', 'value' => $widgetMargin_));
                    //insert statements for modal css
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalBackgroundColor', 'value' => $modalBackgroundColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalFontColor', 'value' => $modalFontColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalPriceColor', 'value' => $modalPriceColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeBackgroundColor', 'value' => $modalBadgeBackgroundColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeBorderRadius', 'value' => $modalBadgeBorderRadius_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeFontColor', 'value' => $modalBadgeFontColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeLogoStyle', 'value' => $modalBadgeLogoStyle_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalCardColor', 'value' => $modalCardColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonBorderColor', 'value' => $modalButtonBorderColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonBorderRadius', 'value' => $modalButtonBorderRadius_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonBackgroundColor', 'value' => $modalButtonBackgroundColor_));
                    $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonFontColor', 'value' => $modalButtonFontColor_));
                }

                /** WIDGET HOME  **/

                $resultH = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget_home"));

                if(isset($resultH) && count($resultH) > 0) {
                    $wpdb->update( $table_config_name, array( 'value' => $newWidgetHomeEnabled . '|' . $newFieldWidgetType . '|' . $newElementReference . '|' . $newSlug), array( 'element' => 'widget_home' ));
                }
                else {
                    $wpdb->insert($table_config_name, array('element' => 'widget_home', 'value' => $newWidgetHomeEnabled . '|' . $newFieldWidgetType . '|' . $newElementReference . '|' . $newSlug));
                }

                /** WIDGET HOME  **/

                /** CUSTOM ORDER STATUS  **/

                $resultCOS = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","custom_order_status"));

                if(isset($resultCOS) && count($resultCOS) > 0) {
                    $wpdb->update( $table_config_name, array( 'value' => $customOrderStatus), array( 'element' => 'custom_order_status' ));
                }
                else {
                    $wpdb->insert($table_config_name, array('element' => 'custom_order_status', 'value' => $customOrderStatus));
                }

                /** CUSTOM ORDER STATUS **/

            }

        }, 10, 3);

        /* Register action hook. */
        add_action('init', array( $this, 'addi_start_session' ), 1);

        /*
        * Callback response managament
        * In this place of the code it is verifying the order id and status from callback response,
        * so then, an action is taken ( display notice  or redirect to order received page).
        */

        // LOAD THE WC LOGGER
        $logger = wc_get_logger();

        // verify if this request is coming from admin site or frontend site
        if ( (! is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )) ) {

            $order_id = $order_status = $woocommerce_order_id_query_param = null;
            $table_name = $wpdb->prefix . "wc_addi_gateway";

            $querys = $_SERVER['QUERY_STRING'];

            if(strpos($querys, 'wc-order-id' ) !== false) {
                $woocommerce_order_id_query_param = $_GET["wc-order-id"];
                $_SESSION["order_id_query_param"] = $woocommerce_order_id_query_param;
            }

            try {

                if(isset($woocommerce_order_id_query_param)) {

                    // This id is registered on database, so it's needed to see its status.
                    $result = $wpdb->get_results($wpdb->prepare("select * from {$table_name} where order_id = %d", $woocommerce_order_id_query_param));

                    // verifying the integrity of the resulset, otherwise could throw an error.
                    if(isset($result) && count($result) > 0) {
                        try{

                            foreach ($result as $item) {
                                $order_id = $item->order_id;
                                $order_status = $item->order_status;
                                $wpdb->delete( $table_name, array( 'order_id' => $item->order_id ) );
                            }
                        }
                        catch(Exception $e) {
                            if ($this->logs == 'yes') {
                                $logger->info( 'Error getting data from database: ' . $e . ' ', array( 'source' => 'addi-error-handler-log' ) );
                            }
                        }

                    }

                    // verifying assignment variables was ok , otherwise it will show a notification
                    if(isset($order_id) && isset($order_status)) {
                        if($order_status !== 'APPROVED') {

                            add_filter( 'woocommerce_checkout_fields' , function ( $fields ) {
                                global $woocommerce;
                                global $wp;

                                $order_id = $_SESSION["order_id_query_param"];

                                $order = wc_get_order( $order_id );

                                // Get the Order meta data in an unprotected array
                                $order_data  = $order->get_data(); // The Order data

                                if($this->custom_order_status == 'yes') {
                                    $order->update_status( 'addi-declined', '', true );
                                }

                                //loop in array to verify if billing fields are populated or not
                                foreach($fields['billing'] as $key1 => $billing) {

                                    if(!isset($fields['billing'][$key1]['default']) ) {
                                        switch ($key1) {
                                            case "billing_first_name":
                                                if(isset($order_data['billing']['first_name'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_first_name();
                                                }
                                                break;
                                            case "billing_last_name":
                                                if(isset($order_data['billing']['last_name'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_last_name();
                                                }
                                                break;
                                            case "billing_company":
                                                if(isset($order_data['billing']['company'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_company();
                                                }
                                                break;
                                            case "billing_address_1":
                                                if(isset($order_data['billing']['address_1'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_address_1();
                                                }
                                                break;
                                            case "billing_address_2":
                                                if(isset($order_data['billing']['address_2'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_address_2();
                                                }
                                                break;
                                            case "billing_city":
                                                if(isset($order_data['billing']['city'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_city();
                                                }
                                                break;
                                            case "billing_state":
                                                if(isset($order_data['billing']['state'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_state();
                                                }
                                                break;
                                            case "billing_postcode":
                                                if(isset($order_data['billing']['postcode'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_postcode();
                                                }
                                                break;
                                            case "billing_country":
                                                if(isset($order_data['billing']['country'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_country();
                                                }
                                                break;
                                            case "billing_email":
                                                if(isset($order_data['billing']['email'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_email();
                                                }
                                                break;
                                            case "billing_phone":
                                                if(isset($order_data['billing']['phone'])) {
                                                    $fields['billing'][$key1]['default'] = $order->get_billing_phone();
                                                }
                                                break;
                                        }
                                    }
                                }

                                //loop in array to verify if shipping fields are populated or not
                                foreach($fields['shipping'] as $key1 => $billing) {

                                    if(!isset($fields['shipping'][$key1]['default']) ) {
                                        switch ($key1) {
                                            case "shipping_first_name":
                                                if(isset($order_data['shipping']['first_name'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_first_name();
                                                }
                                                break;
                                            case "shipping_last_name":
                                                if(isset($order_data['shipping']['last_name'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_last_name();
                                                }
                                                break;
                                            case "shipping_company":
                                                if(isset($order_data['shipping']['company'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_company();
                                                }
                                                break;
                                            case "shipping_address_1":
                                                if(isset($order_data['shipping']['addres_1'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_address_1();
                                                }
                                                break;
                                            case "shipping_address_2":
                                                if(isset($order_data['shipping']['addres_2'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_address_2();
                                                }
                                                break;
                                            case "shipping_city":
                                                if(isset($order_data['shipping']['city'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_city();
                                                }
                                                break;
                                            case "shipping_state":
                                                if(isset($order_data['shipping']['state'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_state();
                                                }
                                                break;
                                            case "shipping_postcode":
                                                if(isset($order_data['shipping']['postcode'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_postcode();
                                                }
                                                break;
                                            case "shipping_country":
                                                if(isset($order_data['shipping']['country'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_country();
                                                }
                                                break;
                                            case "shipping_email":
                                                if(isset($order_data['shipping']['email'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_email();
                                                }
                                                break;
                                            case "shipping_phone":
                                                if(isset($order_data['shipping']['phone'])) {
                                                    $fields['shipping'][$key1]['default'] = $order->get_shipping_phone();
                                                }
                                                break;
                                        }
                                    }
                                }

                                $_SESSION["order_id_query_param"]  = null;
                                return $fields;
                            } );
                            // display notification
                            wc_add_notice( __('Tu pago no fue aprobado. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
                        }
                    }
                }
            }
            catch(Exception $e) {
                if ($this->logs == 'yes') {
                    $logger->info( ' Fatal Error :   ' . $e . ' ', array( 'source' => 'addi-gateway-log' ) );
                }
            }
        }
        /*
        * Callback response management
        * In this place of the code it is verifying the order id and status from callback response,
        * so then, an action is taken ( display notice  or redirect to order received page).
        * --- END OF CODE ----
        */

    }

    /**
     * Plugin options
     */
    public function init_form_fields(){

        global $woocommerce;

        $this->form_fields = array(
            'enabled' => array(
                'title'       => __( 'Habilitar/Deshabilitar', 'buy-now-pay-later-addi' ),
                'label'       => __('Habilitar Addi', 'buy-now-pay-later-addi' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'widget_slug' => array(
                'title'       => __('Ally slug en ADDI','buy-now-pay-later-addi' ),
                'type'        => 'text',
            ),
            'hr3' => array(
                'type'  => 'hr',
                'class' => 'hr-default',
            ),
            'addi_section_checkout_page' => array(
                'title'    => __('Checkout', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'description'  => array(
                'title'       => __( 'Descripción', 'buy-now-pay-later-addi' ),
                'type'        => 'textarea',
                'description' => __( 'Esta descricpión es visible en el checkout.', 'buy-now-pay-later-addi' ),
                'default'     => __( '<b>Finaliza tu compra con ADDI</b></br><b>Es simple, rápido y seguro</b></br><b>1.</b> Sin tarjeta de crédito y en minutos.</br><b>2.</b> Proceso 100% online y sin papeleo.</br><b>3.</b> Solo necesitas tu cédula y WhatsApp para aplicar.', 'buy-now-pay-later-addi' ),
                'desc_tip'    => true,
            ),
            'addi_sub_section_checkout_page' => array(
                'title'    => __('Información del checkout', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'addi_sub_section_checkout_page' => array(
                'title'    => __('Información del checkout', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'addi_description_checkout_page' => array(
                'title'    => __('Indícanos aquí el nombre con el que identificas cada uno de estos datos en tu checkout. Si no has configurado nada especial, déjalo en blanco.', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-description-header',
            ),
            'field_billing_first_name' => array(
                'title'       => __('Campo Nombres','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'default'     => '',
                'desc_tip'    => false,
            ),
            'field_billing_last_name' => array(
                'title'       => __('Campo Apellidos','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'default'     => '',
                'desc_tip'    => false,
            ),
            'field_id' => array(
                'title'       => __('Campo Documento','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'default'     => '',
                'desc_tip'    => false,
            ),
            'field_billing_address_1' => array(
                'title'       => __('Campo Dirección','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'default'     => '',
                'desc_tip'    => false,
            ),
            'field_billing_city' => array(
                'title'       => __('Campo Ciudad','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'default'     => '',
                'desc_tip'    => false,
            ),
            'field_billing_email' => array(
                'title'       => __('Campo Email','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'default'     => '',
                'desc_tip'    => false,
            ),
            'field_billing_phone' => array(
                'title'       => __('Campo Teléfono','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'default'     => '',
                'desc_tip'    => false,
            ),
            'prod_client_id' => array(
                'title'       => 'Client ID',
                'type'        => 'password',
            ),
            'prod_client_secret' => array(
                'title'       => 'Client Secret',
                'type'        => 'password'
            ),
            'testmode' => array(
                'title'       => __('Ambiente pruebas', 'buy-now-pay-later-addi' ),
                'label'       => __('Habilitar ambiente de pruebas', 'buy-now-pay-later-addi' ),
                'type'        => 'checkbox',
                'description' => __('Colocar este método de pago en ambiente de pruebas.', 'buy-now-pay-later-addi' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'custom_order_status' => array(
                'title'       => __( 'Estados personalizados de Addi', 'buy-now-pay-later-addi' ),
                'label'       => __('Habilitar los estados personalizados de Addi en los pedidos', 'buy-now-pay-later-addi' ),
                'type'        => 'checkbox',
                'description' => __('Esta opción cambiará los estados de los pedidos de compra cuando sean con Addi a transacciones aprobadas o no aprobadas.', 'buy-now-pay-later-addi' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'logs' => array(
                'title'       => __('Logs', 'buy-now-pay-later-addi' ),
                'label'       => __('Habilitar Logs', 'buy-now-pay-later-addi' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'hr1' => array(
                'type'  => 'hr',
                'class' => 'hr-default',
            ),
            'addi_section_product_page' => array(
                'title'    => __('Página de producto', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'widget_enabled' => array(
                'title'       => __('Widget', 'buy-now-pay-later-addi' ),
                'label'       => __('Habilitar widget', 'buy-now-pay-later-addi' ),
                'type'        => 'checkbox',
                'description' => __('Habilitar widget ADDI en la página de producto.','buy-now-pay-later-addi' ),
                'default'     => 'no',
            ),
            'conf_widget_position' => array(
                'title'       => __('Posición del widget','buy-now-pay-later-addi' ),
                'type'        => 'select',
                'default'     => 'woocommerce_before_add_to_cart_form',
                'desc_tip'    => false,
                'options' => array(
                    'woocommerce_before_single_product_summary' => __('Encima de título de producto','buy-now-pay-later-addi' ),
                    'woocommerce_before_add_to_cart_form' => __('Default','buy-now-pay-later-addi' ),
                    'woocommerce_before_variations_form' => __('Encima de formulario de variaciones de precio','buy-now-pay-later-addi' ),
                    'woocommerce_before_single_variation' => __('Encima de precio variación','buy-now-pay-later-addi' ),
                    'woocommerce_after_add_to_cart_button' => __('Debajo de botón añadir al carrito','buy-now-pay-later-addi' ),
                    'woocommerce_after_variations_form' => __('Debajo de formulario de variaciones de precio','buy-now-pay-later-addi' ),
                    'woocommerce_after_add_to_cart_form' => __('Debajo de formulario de agregar producto','buy-now-pay-later-addi' ),
                    'woocommerce_product_meta_start' => __('Encima de información extra','buy-now-pay-later-addi' ),
                    'woocommerce_product_meta_end' => __('Debajo de información extra','buy-now-pay-later-addi' ),
                    'woocommerce_share' => __('Encima de redes sociales','buy-now-pay-later-addi' ),
                )
            ),
            'widget_section_widget_header' => array(
                'title'    => __('Configuración Estilos Widget', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'widget_section_widget_header' => array(
                'title'    => __('Configuración Estilos Widget', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'widgetBorderColor' => array(
                'title'       => __('Color del borde','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para el borde del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => 'black',
                'desc_tip'    => true,
            ),
            'widgetBorderRadius' => array(
                'title'       => __('Curvatura del borde','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el tamaño de la curvatura para el borde del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => '5px',
                'desc_tip'    => true,
            ),
            'widgetFontColor' => array(
                'title'       => __('Color de fuente','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para la fuente del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => 'black',
                'desc_tip'    => true,
            ),
            'widgetFontFamily' => array(
                'title'       => __('Tipo de fuente','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el tipo de fuente que quieres usar para el widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => 'system-ui',
                'desc_tip'    => true,
            ),
            'widgetFontSize' => array(
                'title'       => __('Tamaño de fuente','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el tamaño de fuente del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => '14px',
                'desc_tip'    => true,
            ),
            'widgetBadgeBackgroundColor' => array(
                'title'       => __('Color de fondo ícono ADDI','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color del fondo del cuadro con el logo de ADDI para el widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => '#fff',
                'desc_tip'    => true,
            ),
            'widgetInfoBackgroundColor' => array(
                'title'       => __('Color de fondo widget','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __(' Indica el color del fondo del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => 'transparent',
                'desc_tip'    => true,
            ),
            'widgetMargin' => array(
                'title'       => __('Margen','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el tamaño de margen para el widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => '0',
                'desc_tip'    => true,
            ),
            'modalBadgeLogoStyle' => array(
                'title'       => __('Logo ADDI en blanco','buy-now-pay-later-addi' ),
                'label'       => __(' ','buy-now-pay-later-addi' ),
                'type'        => 'checkbox',
                'default'     => 'no',
                'desc_tip'    => false,
            ),
            'widget_section_modal_header' => array(
                'title'    => __('Configuración Estilos Modal', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'modalBackgroundColor' => array(
                'title'       => __('Color de fondo','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) del fondo para el modal con la información de ADDI.', 'buy-now-pay-later-addi' ),
                'default'     => '#eee',
                'desc_tip'    => true,
            ),
            'modalFontColor' => array(
                'title'       => __('Color de fuente','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) de la fuente para el modal con la información de ADDI.', 'buy-now-pay-later-addi' ),
                'default'     => 'black',
                'desc_tip'    => true,
            ),
            'modalPriceColor' => array(
                'title'       => __('Color precio','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para el precio en el modal.', 'buy-now-pay-later-addi' ),
                'default'     => '#3c65ec',
                'desc_tip'    => true,
            ),
            'modalBadgeBackgroundColor' => array(
                'title'       => __('Color fondo banner','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para el fondo del banner de tasa de interés.', 'buy-now-pay-later-addi' ),
                'default'     => '#4cbd99',
                'desc_tip'    => true,
            ),
            'modalBadgeBorderRadius' => array(
                'title'       => __('Curvatura del borde banner','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el tamaño de la curvatura para el borde del banner de tasa de interés.', 'buy-now-pay-later-addi' ),
                'default'     => '5px',
                'desc_tip'    => true,
            ),
            'modalBadgeFontColor' => array(
                'title'       => __('Color fuente banner','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para la fuente del banner de tasa de interés.', 'buy-now-pay-later-addi' ),
                'default'     => 'white',
                'desc_tip'    => true,
            ),
            'modalCardColor' => array(
                'title'       => __('Color fondo modal','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para el fondo del modal. ', 'buy-now-pay-later-addi' ),
                'default'     => 'white',
                'desc_tip'    => true,
            ),
            'modalButtonBorderColor' => array(
                'title'       => __('Color borde botón','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __(' Indica el color (palabra o código HEX) para el borde del botón del modal.', 'buy-now-pay-later-addi' ),
                'default'     => '#4cbd99',
                'desc_tip'    => true,
            ),
            'modalButtonBorderRadius' => array(
                'title'       => __('Curvatura del borde del botón','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el tamaño de la curvatura para el borde del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                'default'     => '5px',
                'desc_tip'    => true,
            ),
            'modalButtonBackgroundColor' => array(
                'title'       => __('Color de fondo botón','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para el fondo del botón.', 'buy-now-pay-later-addi' ),
                'default'     => 'transparent',
                'desc_tip'    => true,
            ),
            'modalButtonFontColor' => array(
                'title'       => __('Color de fondo fuente botón','buy-now-pay-later-addi' ),
                'type'        => 'text',
                'description' => __('Indica el color (palabra o código HEX) para el fondo de la fuente del botón.', 'buy-now-pay-later-addi' ),
                'default'     => '#4cbd99',
                'desc_tip'    => true,
            ),
            'widget_section_widget_home' => array(
                'title'    => __('Home', 'buy-now-pay-later-addi' ),
                'type'     => 'text',
                'class'    => 'widget-section-header',
            ),
            'field_widget_position' => array(
                'title'       => __('Posición del widget','buy-now-pay-later-addi' ),
                'type'        => 'select',
                'default'     => 'on_header',
                'desc_tip'    => false,
                'options' => array(
                    'on_header' => 'Debajo de header',
                    'on_footer' => 'Encima de footer',
                    'custom' => 'Personalizado',
                ) // array of options for select/multiselects only
            ),
            'element_reference' => array(
                'type'        => 'text',
                'default'     => '',
            ),
            'field_widget_type' => array(
                'title'       => __('Tipo de Widget','buy-now-pay-later-addi' ),
                'type'        => 'select',
                'default'     => 'default',
                'desc_tip'    => false,
                'options' => array(
                    'default' => 'default',
                    'banner_01' => 'banner_01',
                    'banner_02' => 'banner_02',
                    'banner_03' => 'banner_03',
                ) // array of options for select/multiselects only
            ),
            'widget_home_enabled' => array(
                'title'       => __('Widget en el Home', 'buy-now-pay-later-addi' ),
                'label'       => __('Habilitar widget en el home', 'buy-now-pay-later-addi' ),
                'type'        => 'checkbox',
                'default'     => 'no',
                'desc_tip'    => true,
            ),
        );

        $brazilCheckoutFieldspluginPath = 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php';
        $checkoutFieldEditorAndManagerForWoocommercePath = 'checkout-field-editor-and-manager-for-woocommerce/start.php';
        $fieldEditorForWoocommercePluginPath = 'woo-checkout-field-editor-pro/checkout-form-designer.php';
        $yithWoocommerceCheckoutManagerPath = 'yith-woocommerce-checkout-manager/init.php';

        if(is_plugin_active( $fieldEditorForWoocommercePluginPath ) ||
            is_plugin_active( $checkoutFieldEditorAndManagerForWoocommercePath ) ||
            is_plugin_active( $brazilCheckoutFieldspluginPath ) ||
            is_plugin_active( $yithWoocommerceCheckoutManagerPath )) {
            set_transient( "buy-now-pay-later-addi", "alive", 3 );
        }
        else {

            $wc_array = array();

            if(isset(WC()->checkout)) {
                $wc_array = WC()->checkout->get_checkout_fields();
            }

            if ( isset( $wc_array )  && isset ( $wc_array['billing'] ) ) {

                foreach($wc_array['billing'] as $key1 => $billing) {

                    switch ($key1) {
                        case "billing_cedula":
                            setcookie("billingField", "billing_cedula", time()+120);
                            break;
                        case "billing_cpf":
                            setcookie("billingField", "billing_cpf", time()+120);
                            break;
                        case "billing_id":
                            setcookie("billingField", "billing_id", time()+120);
                    }

                }

            }
        }

        if (!function_exists('fx_addi_brazilcheckouteditor_notice') && is_plugin_active( $brazilCheckoutFieldspluginPath )) {
            /* Add admin notice */
            add_action( 'admin_notices',function (){
                if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                    ?>
                    <div class="notice-warning notice is-dismissible">
                        <p>O plug-in <strong>Brazilian market for WooCommerce</strong> está instalado e modifica os campos de checkout. Certifique-se de indicar o identificador do campo do documento para garantir o funcionamento correto do <strong>ADDI</strong> plug-in.</p>
                    </div>
                    <?php
                    /* Delete transient, only display this notice once. */
                    delete_transient("buy-now-pay-later-addi");
                }
                else {
                    return;
                }
            });
        }

        if (!function_exists('fx_addi_checkouteditor_notice') && is_plugin_active( $checkoutFieldEditorAndManagerForWoocommercePath )) {
            /* Add admin notice */
            add_action( 'admin_notices', function (){
                if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                    ?>
                    <div class="notice-warning notice is-dismissible">
                        <p>El plug-in <strong>Checkout Field Editor and Manager for Woocommerce</strong> está instalado y este modifica los campos del checkout. Por favor, asegúrate de configurar el nombre para el campo de documento y así asegurar el correcto funcionamiento de <strong>ADDI</strong>.</p>
                    </div>
                    <?php

                }
                else {
                    return;
                }
                /* Delete transient, only display this notice once. */
                delete_transient("buy-now-pay-later-addi");
            });
        }

        if (!function_exists('fx_addi_checkouteditor_notice') && is_plugin_active( $fieldEditorForWoocommercePluginPath )) {
            /* Add admin notice */
            add_action( 'admin_notices', function (){
                if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                    ?>
                    <div class="notice-warning notice is-dismissible">
                        <p>El plug-in <strong>Checkout Field Editor for Woocommerce</strong> está instalado y este modifica los campos del checkout. Por favor, asegúrate de configurar el nombre para el campo de documento y así asegurar el correcto funcionamiento de <strong>ADDI</strong>.</p>
                    </div>
                    <?php

                }
                else {
                    return;
                }
                /* Delete transient, only display this notice once. */
                delete_transient("buy-now-pay-later-addi");
            });
        }

        if (!function_exists('fx_addi_checkouteditor_notice') && is_plugin_active( $yithWoocommerceCheckoutManagerPath )) {
            /* Add admin notice */
            add_action( 'admin_notices', function (){
                if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                    ?>
                    <div class="notice-warning notice is-dismissible">
                        <p>El plug-in <strong>Yith Woocommerce Checkout Manager</strong> está instalado y este modifica los campos del checkout. Por favor, asegúrate de configurar el nombre para el campo de documento y así asegurar el correcto funcionamiento de <strong>ADDI</strong>.</p>
                    </div>
                    <?php

                }
                else {
                    return;
                }
                /* Delete transient, only display this notice once. */
                delete_transient("buy-now-pay-later-addi");
            });
        }
    }

    /**
     * You will need it if you want your custom credit card form, Step 4 is about it
     */
    public function payment_fields() {
        global $woocommerce;
        global $wp;

        $totals = $woocommerce->cart->total;

        $options_api = [
            'headers'     => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'WWW-Authenticate' => "Basic realm='" . gethostname() ."'",
            ],
            'timeout'     => 60,
            'data_format' => 'body',
        ];

        if((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
            $api_domain = $this->testmode ? 'https://channels-public-api.addi-staging-br.com/allies/' : 'https://channels-public-api.addi.com.br/allies/';
            $api_app_url = $api_domain . $this->widget_slug .'/config?requestedAmount=' . $totals;
        }
        else {
            $api_domain = $this->testmode ? 'https://channels-public-api.addi-staging.com/allies/' : 'https://channels-public-api.addi.com/allies/';
            $api_app_url = $api_domain . $this->widget_slug .'/config?requestedAmount=' . $totals;
        }

        // request
        $api_response = wp_remote_get($api_app_url, $options_api );
        AddiLogger::logger_dna('GET_ALLY_CONFIG');

        if( !is_wp_error( $api_response ) ) {
                // getting decoded body
                $body_api_response = json_decode( $api_response['body'], true );
                // Checking for erros
                if (!isset($body_api_response['widgetConfig'])) {
                    // Logging an error
                    $error_msg = $body_api_response['code'] . ' ' . $body_api_response['message'];
                    AddiLogger::logger_dna('GET_ALLY_CONFIG_ERROR', $error_msg, $api_app_url, 'GET');
                }
                $country = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'br' : 'co';
                $installments  = number_format($totals/4, 2);
                // Including the required template
                $plugin_path = WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__DIR__));

                // Getting the template version
                $widgetversion = isset($body_api_response['widgetConfig']['widgetVersion']) ? $body_api_response['widgetConfig']['widgetVersion'] : null;
                $template_version = 'bnpl';
                $discount = (isset($body_api_response['policy']['discount']) && $body_api_response['policy']['discount'] > 0) ? $body_api_response['policy']['discount'] : false;
                $min_amount = false;
                $max_amount = false;
                if(isset($body_api_response['minAmount']) && $body_api_response['minAmount'] !== ""
                    || $body_api_response['code'] == '007-015') {
                    $min_amount = number_format(intval($body_api_response['minAmount']),0,',','.');
                }

                if(isset($body_api_response['maxAmount']) && $body_api_response['maxAmount'] !== "" ) {
                    $max_amount = number_format(intval($body_api_response['maxAmount']),0,',','.');
                }

                if (isset($widgetversion) && ($widgetversion === '1.0.2' || $widgetversion === 'ADDI_TEMPLATE_02')) {
                    $template_version = 'bnpl_bnpn';
                }

                if (isset($widgetversion) && $widgetversion === 'ADDI_TEMPLATE_FLEX') {
                    $template_version = 'flex';
                }

                $template = $plugin_path . '/templates/' . $country . '/' . $template_version . '.php';
                $min_amount_int = $body_api_response['minAmount'];
                $max_amount_int = $body_api_response['maxAmount'];

                if($country == 'br') {
                    $params = array('installments' => $installments, 'total' => $totals,
                                    'discount' => $discount, 'min_amount' => $min_amount, 'max_amount' => $max_amount,
                                    'min_amount_int' => $min_amount_int, 'max_amount_int' => $max_amount_int);

                } else {
                    $params = array('discount' => $discount, 'widgetversion' => $widgetversion, 'total' => $totals,
                                    'min_amount' => $min_amount, 'max_amount' => $max_amount,
                                    'min_amount_int' => $min_amount_int, 'max_amount_int' => $max_amount_int);

                }
                echo  $this->render_template($template, $params);

        }
        else {
            $error_msg = $api_response->get_error_message();
            AddiLogger::logger_dna('GET_ALLY_CONFIG_ERROR', $error_msg, $api_app_url, 'GET');
        }
    }

    private function render_template(/*$template, $variables*/) {
        ob_start();
        foreach ( func_get_args()[1] as $key => $value) {
            ${$key} = $value;
        }
        include func_get_args()[0];
        return ob_get_clean();
    }

    /*
     * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
     */
    public function payment_scripts() {


    }

    /*
     * We're processing the payments here
     */
    public function process_payment( $order_id ) {

        global $woocommerce;
        global $wp;

        // LOAD THE WC LOGGER
        $logger = wc_get_logger();

        // we need it to get any order details
        $order = wc_get_order( $order_id );
        $_SESSION["order_id_process_payment"] = $order_id;

        try{
            if( !is_admin() ){
                WC()->session->set( 'order_id_payment_session' , $order_id );
            }
        }
        catch(Exception $e) {
            if($this->logs == 'yes') {
                $logger->info( '  ERROR saving variable session in Woocommerce :   ' . $e . ' ', array( 'source' => 'addi-error-handler-log' ) );
            }
        }

        //taking corresponding api and credentials
        $api_selected = $this->testmode ? ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi-staging-br.com' : 'https://api.staging.addi.com') : ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi.com.br' : 'https://api.addi.com');
        $client_id_selected = $this->prod_client_id;
        $client_secret_selected = $this->prod_client_secret;

        /*
          * Array with parameters for API interaction
         */

        $body_auth = [
            'audience'  => $api_selected,
            'grant_type' => 'client_credentials',
            'client_id' => $client_id_selected,
            'client_secret' => $client_secret_selected,
        ];

        $body_auth = wp_json_encode( $body_auth );

        $options_auth = [
            'body'        => $body_auth,
            'headers'     => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ],
            'timeout'     => 60,
            'data_format' => 'body',
        ];

        // getting api url based on test mode checkbox
        $auth_app_url = $this->testmode ? ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://auth.addi-staging-br.com/oauth/token' : 'https://auth.addi-staging.com/oauth/token') : ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://auth.addi.com.br/oauth/token' : 'https://auth.addi.com/oauth/token');

        // request
        $auth_response = wp_remote_post($auth_app_url, $options_auth );

        // verify if body response is an error or contains data
        $body_auth_response = json_decode( $auth_response['body'], true );

        if(!is_array($body_auth_response)) {
            $body_auth_response = array();
        }

        $denied = in_array("access_denied", $body_auth_response) || in_array("Unauthorized", $body_auth_response);

        if( !is_wp_error( $auth_response ) && !$denied) {
            // getting decoded body
            $items = [];
            $client = new stdClass();
            $client_address = new stdClass();
            $allyUrlRedirection = new stdClass();

            // Get and Loop Over Order Items
            foreach ( $order->get_items() as $item_id => $item ) {

                $object = new stdClass();
                //Get the WC_Product object
                $product = $item->get_product();
                $object->sku = $product->get_sku();
                $object->name = $item->get_name();
                $object->quantity = $item->get_quantity();
                $object->unitPrice = $product->get_regular_price();
                $object->tax = $item->get_subtotal_tax();
                $object->pictureUrl = wp_get_attachment_url( $product->get_image_id() );
                $object->category = $product->get_type();
                array_push($items, $object);
            }

            // //Get Address Client
            $client_address->lineOne = isset($this->field_billing_address_1) && ($this->field_billing_address_1 !== '') ?
                WC()->checkout->get_value('' . $this->field_billing_address_1 . '') :
                (($order->get_shipping_address_1() !== "" && $order->get_shipping_address_1() !== " ") ?
                    $order->get_shipping_address_1() : $order->get_billing_address_1());

            $client_address->city =    isset($this->field_billing_city) && ($this->field_billing_city !== '') ?
                WC()->checkout->get_value('' . $this->field_billing_city . '') :
                (($order->get_shipping_city() !== "" && $order->get_shipping_city() !== " ") ?
                    $order->get_shipping_city() : $order->get_billing_city());

            $client_address->country = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'BR' : 'CO';


            // // Get Order Client
            $id = '';
            if(isset($this->field_id) && ($this->field_id !== '')) {
                $id = WC()->checkout->get_value('' . $this->field_id . '');

                if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                    $id = str_replace( '.', '', $id );
                    $id = str_replace( '-', '', $id );
                }
            }
            else{
                if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                    $id = WC()->checkout->get_value('billing_cpf') !== "" && WC()->checkout->get_value('billing_cpf') !== " " ? WC()->checkout->get_value('billing_cpf') : WC()->checkout->get_value('billing_id');

                    $id = str_replace( '.', '', $id );
                    $id = str_replace( '-', '', $id );

                }
                else {
                    $billing_cedula = WC()->checkout->get_value('billing_cedula');
                    $billing_id = WC()->checkout->get_value('billing_id');
                    $billing_nmero = WC()->checkout->get_value('billing_nmero');
                    $billing_numero = WC()->checkout->get_value('billing_numero');

                    if (isset($billing_id)) {
                        $id = $billing_id;
                    }
                    else if(isset($billing_cedula)) {
                        $id = $billing_cedula;
                    }
                    else if (isset($billing_nmero)) {
                        $id = $billing_nmero;
                    }
                    else if (isset($billing_numero)) {
                        $id = $billing_numero;
                    }
                }
            }

            $client->idType = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'CPF' : 'CC';
            $client->idNumber =  $id;
            $client->firstName = isset($this->field_billing_first_name) && ($this->field_billing_first_name !== '') ?
                WC()->checkout->get_value('' . $this->field_billing_first_name . '') :
                (($order->get_shipping_first_name() !== "" && $order->get_shipping_first_name() !== " ")  ?
                    $order->get_shipping_first_name() : $order->get_billing_first_name());

            $client->lastName = isset($this->field_billing_last_name) && ($this->field_billing_last_name !== '') ?
                WC()->checkout->get_value('' . $this->field_billing_last_name . '') :
                (($order->get_shipping_last_name() !== "" && $order->get_shipping_last_name() !== " ")  ?
                    $order->get_shipping_last_name() : $order->get_billing_last_name());

            $client->email = isset($this->field_billing_email) && ($this->field_billing_email !== '') ?
                WC()->checkout->get_value('' . $this->field_billing_email . '') :
                $order->get_billing_email();

            $client->cellphone = isset($this->field_billing_phone) && ($this->field_billing_phone !== '') ?
                WC()->checkout->get_value('' . $this->field_billing_phone . '') :
                $order->get_billing_phone();

            $client->cellphoneCountryCode = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? '+55' : '+57';
            $client->address = $client_address;

            // //Get URL redirection
            $site_url = get_site_url();
            // note: may this code will not be needed in the future
            // $url = str_replace( 'https://', 'http://', $site_url );
            $allyUrlRedirection->logoUrl = '';
            $allyUrlRedirection->callbackUrl = $site_url . '/?wc-api=wc_addi_gateway';
            $allyUrlRedirection->redirectionUrl = wc_get_checkout_url() . '?wc-order-id=' . $order_id;

            /*
            * Array with parameters for API interaction
            */
            $body_online_application = [
                'orderId'  => $order->get_id(),
                'totalAmount' => number_format($order->get_total(), 1, '.', ''),
                'shippingAmount' => number_format($order->get_shipping_total(), 1, '.', ''),
                'totalTaxesAmount' => number_format(round((($order->get_total()/1.19) * 1.19) - ($order->get_total()/1.19), 1), 1, '.', ''),
                'currency' => (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'BRL' : 'COP',
                'items' => $items,
                'client' => $client,
                'shippingAddress' => $client_address,
                'allyUrlRedirection' => $allyUrlRedirection,
            ];

            // print_r(array_values($body_online_application));

            $body_online_application = wp_json_encode( $body_online_application );

            $options_online_application = [
                'body'        => $body_online_application,
                'headers'     => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '. $body_auth_response['access_token'].'',
                ],
                'timeout'     => 100,
                'data_format' => 'body',
            ];

            // getting api url based on test mode checkbox
            $online_app_url = $this->testmode ?
                ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ?
                    'https://api.addi-staging-br.com/v1/online-applications' : 'https://api.addi-staging.com/v1/online-applications') :
                ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ?
                    'https://api.addi.com.br/v1/online-applications' : 'https://api.addi.com/v1/online-applications');

            // request
            $online_application_response = wp_remote_post( $online_app_url, $options_online_application );

            // verify if body response is an error or contains data
            $body_online_application_response = json_decode( $online_application_response['body'], true );

            if(!is_array($body_online_application_response)) {
                $body_online_application_response = array();
            }

            $invalid = in_array("000-009", $body_online_application_response) || in_array("El documento de identidad es inválido", $body_auth_response) || in_array("documento", $body_auth_response) || in_array("inválido", $body_auth_response);
            // verify if body response is an error or contains data
            if( !is_wp_error( $online_application_response) && !$invalid) {

                try {
                    // getting decoded body
                    $http_response_history = $online_application_response['http_response']->get_response_object()->history;
                    $found = false;
                    $location_value = null;

                    //loop in response in order to look for a location header with a determined search parameter, it will
                    // contain Addi redirect url
                    foreach($http_response_history as $key => $value) {
                        $response_headers = $value->headers;
                        if(isset($response_headers)) {
                            $location_array = $response_headers->getValues('location');
                            if(isset($location_array)) {
                                $location_string = $location_array[0];
                                $search = 'token';
                                $location_contain = preg_match("/{$search}/i",$location_string);
                                if($location_contain) {
                                    $found = true;
                                    $location_value =  $location_array[0];
                                    break;
                                }
                            }
                        }
                    }

                    //Redirect to addi checkout page
                    return array(
                        'result' => 'success',
                        'redirect' => $location_value
                    );
                }
                catch(Exception $e) {
                    // If something go wrong, show notification
                    wc_add_notice( __('Error procesando el pedido. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
                    return;
                }

            }
            else {
                // If something go wrong, show notification
                wc_add_notice( __('Error procesando el pedido. Documento de identidad inválido. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
                return;
            }

            return;
        }
        else {
            // If something go wrong, show notification
            wc_add_notice( __('Error procesando el pedido. Credenciales inválidas. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
            return;
        }
    }

    /* Function to start sessions : sessions are necesarry to store order id and use it when third party page is redirecting to checkout page
    * Disclaimer: use of this function may conflict with server based cache services, we cannot support it’s use on servers. if this is the case,
    * please contact an administrator.
    */
    function addi_start_session() {

        global $wp;
        global $wpdb;

        if(!session_id()) {
            session_start();
        }
    }

    /*
     * Callback function to process Addi response from official website
     */
    public function addi_callback_handler() {

        global $woocommerce;
        global $wp;
        global $wpdb;

        // LOAD THE WC LOGGER
        $logger = wc_get_logger();

        // set init headers
        // content should be json
        // accept json only
        header('Content-type: application/json');
        header('Accept: application/json');

        if($this->logs == 'yes') {
            $logger->info( 'auth user ' . $_SERVER['PHP_AUTH_USER'] . '', array( 'source' => 'auth-log' ) );
            $logger->info( 'auth PW ' . $_SERVER['PHP_AUTH_PW'] . '', array( 'source' => 'auth-log' ) );
            $logger->info( 'remote User ' . $_SERVER['REMOTE_USER'] . '', array( 'source' => 'auth-log' ) );
            $logger->info( 'Server Auth ' . $_SERVER['HTTP_AUTHORIZATION'] . '', array( 'source' => 'auth-log' ) );
        }

        // verify if user/password are correct
        if ((base64_encode($_SERVER['PHP_AUTH_USER']) != base64_encode($this->callback_user)) ||
            (base64_encode($_SERVER['PHP_AUTH_PW']) != base64_encode($this->callback_password))) {
            // if not, will return a 401 Unauthorized error
            header('WWW-Authenticate: Basic realm="' . gethostname() .'"');
            header('HTTP/1.0 401 Unauthorized');
            return 'Bad request, try again.';
            exit;
        }
        else {
            // init headers to return a success response
            header("Authorization: Basic " . base64_encode("$this->callback_user':'$this->callback_password"));
            header( 'HTTP/1.1 200 OK' );
            // read parameter from body request / response
            $raw_post = file_get_contents( 'php://input' );
            $table_name = $wpdb->prefix . "wc_addi_gateway";

            if (!empty($raw_post))
            {
                // handle post data
                $callback_response = json_decode( $raw_post, true );

                $callback_order_id = $callback_response['orderId'];
                $callback_status = $callback_response['status'];
                $callback_applicationId = $callback_response['applicationId'];

                try{
                    if( !is_admin() ){
                        WC()->session->set( 'order_id_callback' , $callback_order_id );
                        WC()->session->set( 'order_status_callback' , $callback_status );
                    }
                }
                catch(Exception $e) {
                    //error logged in logger object
                    $logger->info( '  ERROR saving order id/ order status variable in callback method. Error details: ' . $e . ' ', array( 'source' => 'addi-gateway-log' ) );
                }
                // insert in table taking callback order id / callback status
                $wpdb->insert($table_name, array('order_id' => $callback_order_id, 'order_status' => $callback_status, 'date' => date("Y-m-d h:i:s")) );

                if($callback_status == 'APPROVED') {

                    try {

                        // get woocommerce order object
                        $order = wc_get_order( $callback_order_id );
                        // The text for the note
                        $note = __("ApplicationId : " . $callback_applicationId);
                        // Add the note
                        $order->add_order_note( $note );
                        // mark this order as completed
                        $order->payment_complete();

                        if($this->custom_order_status == 'yes') {
                            $order->update_status( 'addi-approved', '', true );
                        }

                        // Reduce stock of product in the store
                        $order->reduce_order_stock();
                        $order->set_transaction_id($callback_applicationId);
                        $order->save();

                        // // Empty cart
                        if(isset($woocommerce) && isset($woocommerce->cart)){
                            $woocommerce->cart->empty_cart();
                        }

                        if($this->logs == 'yes') {
                            $logger->info( 'Order with ID = ' . $callback_order_id . '. not proccesed correctly. ', array( 'source' => 'auth-log' ) );
                        }
                    }
                    catch(Exception $e) {
                        if($this->logs == 'yes') {
                            $logger->info( 'Error processing order with ID =  ' . $callback_order_id . '. Details : ' . $e, array( 'source' => 'auth-log' ) );
                        }
                    }
                }
                else {
                    // get woocommerce order object
                    $order = wc_get_order( $callback_order_id );
                    // The text for the note
                    $note = __("ApplicationId : " . $callback_applicationId);
                    // Add the note
                    $order->add_order_note( $note );
                    $order->set_transaction_id($callback_applicationId);
                    $order->save();
                }

                // returning same data post
                echo $raw_post;
                // exit
                die();
            }
            else {
                // returning same data post
                echo $raw_post;
                // exit
                die();
            }
        }
    }

    public function log_gateway_loaded() {
        AddiLogger::logger_dna('DISPLAY_PAYMENT_METHOD');
    }
}
