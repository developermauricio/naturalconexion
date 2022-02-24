<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 20-03-19
 * Time: 9:35 AM
 */

namespace WACVP\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @subpackage Plugin
 */
class Plugin {

	protected static $instance = null;

	private function __construct() {
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function deactivate() {
	}

	public static function uninstall() {
		global $wpdb;

		$abd_record_tb   = $wpdb->prefix . "wacv_abandoned_cart_record";
		$guest_record_tb = $wpdb->prefix . "wacv_guest_info_record";
		$mail_log_tb     = $wpdb->prefix . "wacv_email_history";
		$cart_log_tb     = $wpdb->prefix . "wacv_cart_log";

		$sql = "DROP TABLE IF EXISTS  {$abd_record_tb}, {$guest_record_tb}, {$mail_log_tb}, {$cart_log_tb}";
		$wpdb->query( $sql );
	}

	public function activate( $network_wide ) {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
			$current_blog = $wpdb->blogid;
			$blogs        = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog );
				$this->single_active();
			}
			switch_to_blog( $current_blog );
		} else {
			$this->single_active();
		}
	}

	public function single_active() {
		$this->create_database();

		if ( ! get_option( 'wacv_private_key' ) ) {
			update_option( 'wacv_private_key', uniqid() );
		}

		if ( ! get_option( 'wacv_cron_key' ) ) {
			update_option( 'wacv_cron_key', md5( uniqid() ) );
		}
		update_option( 'wacv_check_balance', true );
		$this->create_default_templates();
//		$this->create_unsubscribe_page();

	}

	public function create_database() {
		global $wpdb;
		$wcav_collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$wcav_collate = $wpdb->get_charset_collate();
		}

		$abd_record_tb = $wpdb->prefix . "wacv_abandoned_cart_record";

		$query = "CREATE TABLE IF NOT EXISTS {$abd_record_tb} (
                             `id` int(11) NOT NULL AUTO_INCREMENT,
                             `user_id` int(11) NOT NULL,
                             `abandoned_cart_info` text COLLATE utf8_unicode_ci NOT NULL,
                             `abandoned_cart_time` int(11) NOT NULL,
                             `cart_ignored` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
                             `recovered_cart` int(11) NOT NULL,
                             `recovered_cart_time` int(11) NOT NULL,
                             `order_type` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
                             `user_type` text,
                             `unsubscribe_link` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
                             `session_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                             `send_mail_time` int(11),
                             `number_of_mailing` int(3) NOT NULL,
                             `email_complete` enum('0','1'),
                             `messenger_sent` int(3) NOT NULL,
                          	 `messenger_complete` enum('0','1'),
							 `sms_sent` int(3) NOT NULL,
							 `sms_complete` enum('0','1'),
                             `valid_phone` int(3) NOT NULL,
                             `customer_ip` tinytext COLLATE utf8_unicode_ci,
                             `os_platform` tinytext COLLATE utf8_unicode_ci,
                             `browser` tinytext COLLATE utf8_unicode_ci,
                             PRIMARY KEY  (`id`)
                             ) $wcav_collate";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $query );

		$guest_record_tb = $wpdb->prefix . "wacv_guest_info_record";

		$query = "CREATE TABLE IF NOT EXISTS {$guest_record_tb} (
                `id` int(15) NOT NULL AUTO_INCREMENT,
                `user_ref` text,
                `input_date` int(11),
                `billing_first_name` text,
                `billing_last_name` text,
                `billing_company` text,
                `billing_address_1` text,
                `billing_address_2` text,
                `billing_city` text,
                `billing_country` text,
                `billing_postcode` text,
                `billing_email` text,
                `billing_phone` text,
                `ship_to_billing` text,
                `order_notes` text,
                `shipping_first_name` text,
                `shipping_last_name` text,
                `shipping_company` text,
                `shipping_address_1` text,
                `shipping_address_2` text,
                `shipping_city` text,
                `shipping_country` text,
                `shipping_postcode` double,
                `status` varchar(12),
                PRIMARY KEY  (`id`)
                ) $wcav_collate AUTO_INCREMENT=100000000";
		dbDelta( $query );

		$mail_log_tb = $wpdb->prefix . "wacv_email_history";

		$query = "CREATE TABLE IF NOT EXISTS {$mail_log_tb} (
						`id` int(11) NOT NULL auto_increment,
						`type` tinytext  COLLATE utf8_unicode_ci,
						`billing_email` varchar(50) collate utf8_unicode_ci,
						`template_id` varchar(40) collate utf8_unicode_ci NOT NULL,
						`acr_id` int(11) NOT NULL,
						`sent_time` int(11) NOT NULL,
						`clicked` int(11),
						`opened` int(11) ,
						`coupon` tinytext COLLATE utf8_unicode_ci,
						`sent_email_id` tinytext COLLATE utf8_unicode_ci,
						PRIMARY KEY  (`id`)
						) $wcav_collate AUTO_INCREMENT=1 ";

		dbDelta( $query );

		$cart_log_tb = $wpdb->prefix . "wacv_cart_log";
		$query       = "CREATE TABLE IF NOT EXISTS {$cart_log_tb} (
						`id` int(11) NOT NULL auto_increment,
						`user_id` varchar(50) collate utf8_unicode_ci,
						`data` longtext collate utf8_unicode_ci,
						`time_log` int(11) ,
						`ip` tinytext NOT NULL ,
						`os_platform` tinytext NOT NULL ,
						`browser` tinytext NOT NULL ,
						PRIMARY KEY  (`id`)
						) $wcav_collate AUTO_INCREMENT=1 ";
		dbDelta( $query );
	}

	public function create_default_templates() {
		$temp_id = array();

		if ( count( get_posts( array( 'post_type' => 'wacv_email_template' ) ) ) == 0 ) {

			ob_start();
			require_once WACVP_TEMPLATES . 'email-default.php';
			$content = ob_get_clean();

			ob_start();
			require_once WACVP_TEMPLATES . 'email-template-edit.php';
			$template_edit = ob_get_clean();
			$template_edit = str_replace( "\\", "\\\\", $template_edit );

			$subject = array(
				'Hey {customer_name}!! You left something in your cart',
				'Hey {customer_name}!! You cart have not checkout',
				'Hey {customer_name}!! Something in your cart'
			);

			for ( $i = 0; $i < 3; $i ++ ) {
				$arg = array(
					'post_content' => $content,
					'post_title'   => 'Template ' . ( $i + 1 ),
					'post_status'  => 'publish',
					'post_type'    => 'wacv_email_template'
				);

				$temp_id[] = $post_id = wp_insert_post( $arg );

				update_post_meta( $post_id, 'wacv_email_html_edit', $template_edit );

				$setting = array(
					'subject'                => $subject[ $i ],
					'gnr_coupon_type'        => 'percent',
					'gnr_coupon_amount'      => '5',
					'gnr_coupon_date_expiry' => '30',
				);
				update_post_meta( $post_id, 'wacv_email_settings_new', $setting );
			}
		}

		if ( ! get_option( 'wacv_params' ) ) {
			$random_app_verify_token = md5( rand( 111111111, 999999999 ) );
			$params                  = array(
				'tracking_member'      => 1,
				'tracking_guest'       => 1,
				'email_from_name'      => get_bloginfo(),
				'email_from_address'   => get_bloginfo( 'admin_email' ),
				'email_reply_address'  => get_bloginfo( 'admin_email' ),
				'send_email_to_member' => 1,
				'send_email_to_guest'  => 1,
				'email_rules'          => array(
					'send_time'    => array( 1, 2, 3 ),
					'time_to_send' => array( 1, 24, 72 ),
					'unit'         => array( 'hours', 'hours', 'hours' ),
					'template'     => array( $temp_id[0], $temp_id[1], $temp_id[2], )
				),

				'app_verify_token' => $random_app_verify_token,

			);
			update_option( 'wacv_params', $params );
		}
	}

	public function create_unsubscribe_page() {
		$unsub_page_id = get_option( 'wacv_unsubscribe_endpoint' );
		$page          = get_post( $unsub_page_id );

		if ( ! $unsub_page_id || ! $page ) {
			$shop_page_id = get_option( 'woocommerce_shop_page_id' );
			$shop_url     = site_url( "?p={$shop_page_id}" );
			$args         = [
				'post_type'    => 'page',
				'post_content' => '<h3>' . esc_html__( 'You have successfully unsubscribed.' ) . "<a href='{$shop_url}'>" . esc_html__( 'Continue shopping', 'woo-abandoned-cart-recovery' ) . "</a></h3>",
				'post_title'   => esc_html__( 'Unsubscribe abandoned cart reminder', 'woo-abandoned-cart-recovery' ),
				'post_status'  => 'publish',
			];

			$page_id = wp_insert_post( $args );
			update_option( 'wacv_unsubscribe_endpoint', $page_id );
		}
	}
}
