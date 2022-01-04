<?php
/**
 * WooCommerce Mail Builder and Customizer
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!function_exists('woo_mb_get_settings')) {
    function woo_mb_get_settings($template_id = false, $filter_args = array())
    {
            return false;
    }
}

if (!function_exists('woo_mb_save_settings')) {
    function woo_mb_save_settings()
    {
        $returnVal = false;
        $isAdmin = is_admin();
        if($isAdmin){
            if(isset($_REQUEST["settings_based_on_template_lang"]) && $_REQUEST["settings_based_on_template_lang"]){
                if(isset($_REQUEST["settings_lang"])){
                    $postValues = $_REQUEST["settings_lang"];
                    $woo_mb_settings = json_encode($postValues);
                    $option = get_option('woo_mb_settings_lang', '');
                    if($option != ''){
                        update_option('woo_mb_settings_lang', $woo_mb_settings);
                    } else {
                        add_option('woo_mb_settings_lang', $woo_mb_settings);
                    }
                    $returnVal = true;
                }
            } else if(isset($_REQUEST["settings_for_api_integration"]) && $_REQUEST["settings_for_api_integration"]){
                if(isset($_REQUEST["settings_integration"])){
                    $postValues = $_REQUEST["settings_integration"];
                    $postValues['validated_app_id'] = false;
                    if(!empty($postValues["app_id"])){
                        $postValues['validated_app_id'] = WooEmailCustomizerIntegrationCouponAnalyticsRabbit::validateAppId($postValues["app_id"]);
                    }
                    $woo_mb_settings = json_encode($postValues);
                    $option = get_option('woo_mb_settings_analytics_rabbit', '');
                    if($option != ''){
                        update_option('woo_mb_settings_analytics_rabbit', $woo_mb_settings);
                    } else {
                        add_option('woo_mb_settings_analytics_rabbit', $woo_mb_settings);
                    }
                    $result['validated_next_order_api'] = $postValues['validated_app_id'];
                    $returnVal = true;
                }
            } else {
                if(isset($_REQUEST["settings"])){
                    $postValues = $_REQUEST["settings"];
                    $postValues['licence_key_status'] = 'invalid';
                    $result['licence_key_message'] = '<span class="warning">'.esc_html__('Invalid', 'woo-email-customizer-page-builder').'</span>';
                    if(isset($postValues['licence_key']) && !empty($postValues['licence_key'])){
                        $license_result = WooEmailBuilderUpdateChecker::validateLicenceKey($postValues['licence_key']);
                        if($license_result == 'active'){
                            $postValues['licence_key_status'] = 'active';
                            $result['licence_key_message'] = '<span class="success">'.esc_html__('Active', 'woo-email-customizer-page-builder').'</span>';
                        } elseif ($license_result == 'expired'){
                            $postValues['licence_key_status'] = 'expired';
                            $result['licence_key_message'] = '<span class="warning">'.esc_html__('Active and expired', 'woo-email-customizer-page-builder').'</span>';
                            $licence_key_message_detail = '<br>';
                            $licence_key_message_detail .= '<div class="emc-update-notice notice-warning"><p>';
                            $licence_key_message_detail .= WooEmailBuilderUpdateChecker::get_message_on_licence_expired();
                            $licence_key_message_detail .= '</p></div';
                            $result['licence_key_message_detail'] = $licence_key_message_detail;
                        } else {
                            $postValues['licence_key_status'] = 'invalid';
                            $result['licence_key_message'] = '<span class="warning">'.esc_html__('Invalid', 'woo-email-customizer-page-builder').'</span>';
                        }
                    }
                    $result['licence_key_status'] = $postValues['licence_key_status'];
                    if(!empty($postValues['custom_css'])){
                        $postValues['custom_css'] = str_replace('\"', '"', $postValues['custom_css']);
                        $postValues['custom_css'] = str_replace("\'", "'", $postValues['custom_css']);
                    }
                    $woo_mb_settings = json_encode($postValues);
                    $option = get_option('woo_mb_settings', '');
                    if($option != ''){
                        update_option('woo_mb_settings', $woo_mb_settings);
                    } else {
                        add_option('woo_mb_settings', $woo_mb_settings);
                    }
                    $returnVal = true;
                }
            }
        }
        if($returnVal){
            $result['status'] = 'SUCCESS';
            $result['status_code'] = 200;
            $result['status_message'] = esc_html__('Saved successfully');
        } else {
            $result['status'] = 'FAILED';
            $result['status_code'] = 0;
            $result['status_message'] = esc_html__('Save Failed');
        }
        echo json_encode($result);
        die();
    }
}