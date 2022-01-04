<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 *
 * @class       WooEmailCustomizerCompatibleSubscription
 * @author 		Flycart Technologies LLP
 * @package 	WooCommerce Email Customizer with Drag and Drop Email Builder
 * @version     1.0.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('WooEmailCustomizerCompatibleSubscription')){
    class WooEmailCustomizerCompatibleSubscription{
        /**
         * Initialize.
         */
        public static function init() {
            self::add_hooks();
        }

        /**
         * Add hooks
         * */
        protected static function add_hooks(){
            add_filter('woo_email_drag_and_drop_builder_set_order_on_no_order_exists_in_email_template_request', array(__CLASS__, 'setOrderDataForSubscriptionOrders'), 10);
            /**
             * Check if WooCommerce Subscriptions is active
             **/
            if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
                add_filter('woo_email_drag_and_drop_builder_load_additional_shortcode', array(__CLASS__, 'loadAdditionalShortCodeForSubscriptionOrders'), 10);
                add_filter('woo_email_drag_and_drop_builder_load_additional_shortcode_data', array(__CLASS__, 'loadAdditionalShortCodeDataForSubscriptionOrders'), 10, 3);
                add_filter('woo_email_drag_and_drop_builder_get_order_id_on_design_template', array(__CLASS__, 'changeOrderIdForSubscriptionOrder'), 10, 2);
            }
        }

        /**
         * Change Order id for subscription order
         *
         * @param $order_id integer
         * @param $email_type string
         * @return integer
         * */
        public static function changeOrderIdForSubscriptionOrder($order_id, $email_type){
            if(!empty($order_id) && !empty($email_type)){
                if(in_array($email_type, array('cancelled_subscription', 'expired_subscription', 'suspended_subscription')))
                $order = wc_get_order($order_id);
                if(!empty($order)){
                    $subscription_ids = self::getSubscriptionIdsFromOrder($order);
                    if(!empty($subscription_ids)){
                        if(isset($subscription_ids[0]) && !empty($subscription_ids[0])){
                            $order_id = $subscription_ids[0];
                        }
                    }
                }
            }

            return $order_id;
        }

        /**
         * To load the additional shortcode in Woo Email Template
         *
         * @param $shortcodes array
         * @return array
         * */
        public static function loadAdditionalShortCodeForSubscriptionOrders($shortcodes){
            $shortcodes['woo_mb_wcs_subscription_detail_table'] = esc_html__("To load the subscription details in table format", 'woo-email-customizer-page-builder'); // This helps the customer to see the short code in email customizer
            $shortcodes['woo_mb_wcs_subscription_start_on'] = esc_html__("Display subscription start date", 'woo-email-customizer-page-builder');
            $shortcodes['woo_mb_wcs_subscription_end_on'] = esc_html__("Display subscription end date", 'woo-email-customizer-page-builder');
            $shortcodes['woo_mb_wcs_subscription_next_payment'] = esc_html__("Display subscription next payment date", 'woo-email-customizer-page-builder');
            $shortcodes['woo_mb_wcs_subscription_trial_end'] = esc_html__("Display subscription trial end date", 'woo-email-customizer-page-builder');
            $shortcodes['woo_mb_wcs_subscription_cancelled'] = esc_html__("Display subscription cancelled date", 'woo-email-customizer-page-builder');
            $shortcodes['woo_mb_wcs_subscription_payment_retry'] = esc_html__("Display subscription payment retry date", 'woo-email-customizer-page-builder');
            $shortcodes['woo_mb_wcs_subscription_last_order_date_created'] = esc_html__("Display subscription last order created", 'woo-email-customizer-page-builder');

            return $shortcodes;
        }

        /**
         * To send the additional shortcode value
         *
         * @param $shortcodes array
         * @param $order object //might be empty for email template which doesn't has order
         * @param $sending_email boolean
         * @return array
         * */
        public static function loadAdditionalShortCodeDataForSubscriptionOrders($shortcodes, $order, $sending_email){
            $subscription_id = $subscription = false;
            $subscription_ids = self::getSubscriptionIdsFromOrder($order);
            if(!empty($subscription_ids)){
                if(isset($subscription_ids[0]) && !empty($subscription_ids[0])){
                    $subscription_id = $subscription_ids[0];
                }
            }
            if(function_exists('wcs_get_subscription')) {
                if(!empty($subscription_id)){
                    $subscription = wcs_get_subscription($subscription_id);
                }
            }
            $shortcodes['woo_mb_wcs_subscription_detail_table'] = self::getSubscriptionDetailTable($order, $subscription_id); // Here we need to pass the short code value
            $shortcodes['woo_mb_wcs_subscription_start_on'] = self::getSubscriptionDate($subscription, 'start');
            $shortcodes['woo_mb_wcs_subscription_end_on'] = self::getSubscriptionDate($subscription, 'end');
            $shortcodes['woo_mb_wcs_subscription_next_payment'] = self::getSubscriptionDate($subscription, 'next_payment');
            $shortcodes['woo_mb_wcs_subscription_trial_end'] = self::getSubscriptionDate($subscription, 'trial_end');
            $shortcodes['woo_mb_wcs_subscription_cancelled'] = self::getSubscriptionDate($subscription, 'cancelled');
            $shortcodes['woo_mb_wcs_subscription_payment_retry'] = self::getSubscriptionDate($subscription, 'payment_retry');
            $shortcodes['woo_mb_wcs_subscription_last_order_date_created'] = self::getSubscriptionDate($subscription, 'last_order_date_created');

            return $shortcodes;
        }

        /**
         * Get Subscription start date
         *
         * @param $subscription object
         * @param $type string
         * @return string
         * */
        public static function getSubscriptionDate($subscription, $type){
            $date = '';
            if(!empty($subscription)){
                if(method_exists($subscription, 'get_time')){
                    $date_of_type = $subscription->get_time( $type, 'site' );
                    if(!empty($date_of_type)){
                        $date = date_i18n( wc_date_format(), $date_of_type );
                    }
                }
            }


            return $date;
        }

        /**
         * Get subscription detail table
         *
         * @param $order object
         * @param $subscription_id int
         * @return string
         * */
        public static function getSubscriptionDetailTable($order, $subscription_id){
            if(!empty($subscription_id)){
                if(function_exists('wcs_get_subscription')){
                    $subscription = wcs_get_subscription($subscription_id);
                    if(!empty($subscription)){
                        ob_start();
                        global $woo_email_arguments;
                        $template = self::getTemplateOverride('woo_mail/subscription-details.php');
                        $path = WOO_ECPB_DIR . '/templates/woo_mail/subscription-details.php';
                        if($template){
                            $path = $template;
                        }
                        $sent_to_admin = isset($woo_email_arguments['sent_to_admin'])? $woo_email_arguments['sent_to_admin']: false;
                        $plain_text = isset($woo_email_arguments['plain_text'])? $woo_email_arguments['plain_text']: false;
                        $email = isset($woo_email_arguments['email'])? $woo_email_arguments['email']: false;
                        include($path);
                        $html = ob_get_contents();
                        ob_end_clean();

                        return $html;
                    }
                }
            }

            return '';
        }

        /**
         * Load HTMl for the subscription orders
         *
         * @param $args array
         * @return array
         * */
        public static function setOrderDataForSubscriptionOrders($args){
            $accepted_emails = array('cancelled_subscription', 'expired_subscription', 'suspended_subscription');
            if(isset($args['email']) && isset($args['email']->id) && !empty($args['email']->id)){
                if(in_array($args['email']->id, $accepted_emails)){
                    if(isset($args['subscription'])){
                        $args['order'] = $args['subscription'];
                    } else if (isset($args['email']->object)){
                        if(!empty($args['email']->object)){
                            $args['order'] = $args['email']->object;
                        }
                    }
                }
            }

            return $args;
        }

        /**
         * Get subscription Ids from order
         *
         * @param $order object
         * @return array
         * */
        public static function getSubscriptionIdsFromOrder($order){
            $subscriptions_ids = array();
            if(method_exists($order, 'get_id')){
                $order_id = $order->get_id();
                if(method_exists($order, 'get_type')){
                    if(function_exists('wcs_get_subscriptions_for_order')){
                        $subscription_ids = wcs_get_subscriptions_for_order($order_id);
                        // We get the related subscription for this order
                        if(!empty($subscription_ids)){
                            foreach ($subscription_ids as $subscription_id => $subscription_obj){
                                if(method_exists($subscription_obj, 'get_parent')){
                                    $parent_order = $subscription_obj->get_parent();
                                    if($parent_order->get_id() == $order_id) {
                                        $subscriptions_ids[] = $subscription_id;
                                    }
                                } else {
                                    if ($subscription_obj->order->id == $order_id) {
                                        $subscriptions_ids[] = $subscription_id;
                                    }
                                }
                            }
                        }
                    }
                    if(empty($subscriptions_ids)){
                        if($order->get_type() == 'shop_subscription'){
                            $subscriptions_ids[] = $order_id;
                        } else {
                            if(function_exists('wcs_get_subscription')){
                                $subscription = wcs_get_subscription($order_id);
                                if(!empty($subscription)){
                                    $subscriptions_ids[] = $order_id;
                                } else {
                                    if(function_exists('wcs_get_subscriptions_for_renewal_order')){
                                        $subscription_ids = wcs_get_subscriptions_for_renewal_order( $order_id );
                                        foreach ($subscription_ids as $subscription_id => $subscription_obj){
                                            $subscriptions_ids[] = $subscription_id;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $subscriptions_ids;
        }

        /**
         * Get template override
         * $template_name woo_mail/order_items-3.php
         * */
        public static function getTemplateOverride($template_name){
            $template = locate_template(
                array(
                    trailingslashit( dirname(WOO_ECPB_PLUGIN_BASENAME) ) . $template_name,
                    $template_name,
                )
            );

            return $template;
        }
    }

    WooEmailCustomizerCompatibleSubscription::init();
}