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
class WooEmailCustomizerNotices
{
    /**
     * Stores notices.
     *
     * @var array
     */
    private static $notices = array();
    private static $available_notices = array('dashboard');

    public static function init(){
        self::set_notices();
        add_action( 'admin_print_styles', 'WooEmailCustomizerNotices::add_notices' );
        add_action( 'wp_loaded', 'WooEmailCustomizerNotices::hide_notices');
    }

    /**
     * Set notices
     * */
    protected static function set_notices(){
        $available_notices = self::$available_notices;
        foreach ($available_notices as $available_notice){
            $notice_on = self::display_notice($available_notice);
            if($notice_on){
                self::$notices[] = $available_notice;
            }
        }
    }

    /**
     * Get notices
     * */
    protected static function get_notices(){
        return self::$notices;
    }

    /**
     * Add notices + styles if needed.
     */
    public static function add_notices() {
        if(function_exists('get_current_screen')){
            $screen          = get_current_screen();
            $screen_id       = $screen ? $screen->id : '';
        } else {
            $screen_id = '';
        }

        $show_on_screens = array(
            'dashboard',
        );
        if(!in_array($screen_id, $show_on_screens)) return;

        $notices = self::get_notices();

        if ( empty( $notices ) ) {
            return;
        }

        foreach ($notices as $notice){
            add_action( 'admin_notices', array( __CLASS__, $notice.'_notice' ), 1);
        }
    }

    /**
     * If we need to update, include a message with the update button.
     */
    public static function dashboard_notice() {
        $notice_on_for_user = get_user_meta( get_current_user_id(), 'dismissed_woo_email_customizer_admin_dashboard_notice', true );
        if(!$notice_on_for_user){
            include WOO_ECPB_DIR . '/pages/notices/html-notice-dashboard.php';
        }
    }

    /**
     * Hide a notice if the GET variable is set.
     */
    public static function hide_notices() {
        if ( isset( $_GET['wemc-hide-notice'] ) && isset( $_GET['_wemc_notice_nonce'] ) ) { // WPCS: input var ok, CSRF ok.
            if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wemc_notice_nonce'] ) ), 'woo_email_customizer_hide_notices_nonce' ) ) { // WPCS: input var ok, CSRF ok.
                wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
            }

            $hide_notice = sanitize_text_field( wp_unslash( $_GET['wemc-hide-notice'] ) ); // WPCS: input var ok, CSRF ok.

            update_user_meta( get_current_user_id(), 'dismissed_woo_email_customizer_admin_' . $hide_notice . '_notice', true );

            do_action( 'woo_email_customizer_hide_' . $hide_notice . '_notice' );
        }
    }

    /**
     * Check display notice or not
     *
     * @param string $name Notice name.
     * @return boolean
     */
    public static function display_notice( $name ) {
        return apply_filters( 'woo_email_customizer_display_admin_notice_' . $name, true);
    }

    public static function display_notice_in_customizer_page(){
        $notice_on_for_user = get_user_meta( get_current_user_id(), 'dismissed_woo_email_customizer_admin_customizer_page_notice', true );//customizer_page
        if($notice_on_for_user) return false;
        return true;
    }
}
WooEmailCustomizerNotices::init();