<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * This class allows the display of admin notices
 * after redirects through a database field.
 * 
 * @since 1.0.2
 */

class Doppler_For_WooCommerce_Admin_Notice
{
    const NOTICE_FIELD = 'dplrwoo_notice_field';

    public static function display_admin_notice() {
        $option      = get_option(self::NOTICE_FIELD);
        $message     = isset($option['notice_message']) ? $option['notice_message'] : false;
        $noticeLevel = !empty($option['notice_class']) ? $option['notice_class'] : 'notice-error';
    
        if ($message) {
            echo "<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>";
            delete_option(self::NOTICE_FIELD);
        }
    }

    public static function display_error($message) {
        static::update_option_field($message, 'notice-error');
    }

    public static function display_warning($message) {
        static::update_option_field($message, 'notice-warning');
    }

    public static function display_info($message) {
        static::update_option_field($message, 'notice-info');
    }

    public static function display_success($message) {
        static::update_option_field($message, 'notice-success');
    }
    
    protected static function update_option_field( $notice_message, $notice_class){
        update_option(  self::NOTICE_FIELD, 
                        array(  'notice_message' => $notice_message,
                                'notice_class' => $notice_class)
        );
    }

}