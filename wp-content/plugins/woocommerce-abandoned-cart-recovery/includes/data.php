<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 29-03-19
 * Time: 1:04 PM
 */

namespace WACVP\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Data {

	public static $params;

	public static $params_default = array(
		//General settings
		'update_key' => '',

		'member_cut_off_time'              => 15,
		'guest_cut_off_time'               => 15,
		'number_of_mailing'                => 1,
		're_send_email_time'               => 10,
		'delete_record_time'               => 30,
		'email_to_admin_when_cart_recover' => 0,
		'tracking_member'                  => 0,
		'tracking_guest'                   => 0,
		'tracking_include'                 => '',
		'tracking_user_exclude'            => '',
		'tracking_ip_exclude'              => '',
		'tracking_email_exclude'           => '',
		'enable_cart_log'                  => 0,
		'email_from_name'                  => '',
		'email_from_address'               => '',
		'email_reply_address'              => '',
		'send_mail_first_time'             => 30,
		'send_mail_first_time_unit'        => 'minutes',
		'send_mail_more_time'              => 1,
		'send_mail_more_time_unit'         => 'days',
		'number_of_re_send_mail'           => 3,
		'send_email_to_member'             => 0,
		'send_email_to_guest'              => 0,
		'use_template_with_times'          => '',
		'email_subject'                    => 'Hey {customer_name}!! You left something in your cart',
		'price_incl_tax'                   => 0,
		'image_size'                       => 'woocommerce_thumbnail',

		'cron_time'               => 15,
		'cron_time_unit'          => 'minutes',
		'enable_unsubscribe_link' => 0,
		'redirect_to'             => 'cart_page',
		'email_rules'             => array(),
		'abd_orders'              => array(),
		'order_stt'               => array( 'wc-failed', 'wc-cancelled' ),
		'enable_reminder_order'   => 0,

		//SMS data
		'sms_abd_cart'            => array(
			'send_time'    => array( 1 ),
			'time_to_send' => array( 1 ),
			'unit'         => array( 'hours' ),
			'message'      => array( 'Hi {customer_name}, you have a cart not checkout at {checkout_link}' ),
		),
		'sms_abd_order'           => array(
			'send_time'    => array( 1 ),
			'time_to_send' => array( 1 ),
			'unit'         => array( 'hours' ),
			'message'      => array( 'Hi {customer_name}, you have a cart not checkout at {checkout_link}' ),
		),
		'sms_provider'            => 'twilio',
		'sms_abd_cart_enable'     => 0,
		'sms_abd_order_enable'    => 0,
		'sms_app_id'              => '',
		'sms_app_secret'          => '',
		'sms_access_token'        => '',
		'shortlink_enable'        => 1,
		'shortlink_access_token'  => '',
		'sms_order_stt'           => array( 'wc-failed', 'wc-cancelled' ),
		'from_phone'              => '',

		'sms_app_id_nexmo'         => '',
		'sms_app_secret_nexmo'     => '',
		'from_phone_nexmo'         => '',
		'sms_app_id_plivo'         => '',
		'sms_app_secret_plivo'     => '',
		'powerpack_uuid'           => '',


		//Popup Settings
		'popup_page_id'            => '',
		'single_page'              => 0,
		'shop_page'                => 0,
		'cart_page'                => 0,
		'category_page'            => 0,
		'front_page'               => 0,
		'dismiss_delay'            => 60,
		'title_popup'              => 'Please enter your email',
		'sub_title_popup'          => "To add this item to your cart, please enter your email address.",
		'add_to_cart_btn'          => 'Add to cart',
		'invalid_email'            => 'Your email is invalid.',
		'invalid_phone'            => 'Your phone number is invalid.',
		'checkout_btn'             => 'Checkout',
		'dismiss_btn'              => 'No, Thanks',
		'popup_width'              => 350,
		'email_field'              => 1,
		'phone_field'              => 0,
		'info_require'             => 0,
		'phone_require'            => 0,

		// popup design
		'template_popup'           => 'template-1',
		'popup_title_color'        => '#000000',
		'popup_sub_title_color'    => '#000000',
		'popup_bg_color'           => '#ffffff',
		'popup_btn_color'          => '#ffffff',
		'popup_btn_bg_color'       => '#212121',
		'popup_input_border_color' => '#212121',
		'popup_input_text_color'   => '#212121',
		'popup_input_bg_color'     => '#ffffff',
		'popup_notice_color'       => '#000000',
		'redirect_after_atc'       => 'no_redirect',
		'enable_gdpr'              => 0,
		'gdpr_content'             => 'I agree that my submitted data is being collected and stored',

		//fb data
		'app_id'                   => '',
		'page_id'                  => '',
		'app_secret'               => '',
		'app_lang'                 => 'en_US',
		'app_skin'                 => 'light',
		'app_verify_token'         => '',
		'user_token'               => '',
		'checkbox_location'        => 0,
		'checkbox_require'         => 0,

		'messenger_rules' => array(
			'send_time'    => array( 1, 2, 3 ),
			'time_to_send' => array( 1, 24, 72 ),
			'unit'         => array( 'hours', 'hours', 'hours' ),
			'message'      => array(
				'You left something in your cart',
				'You cart have not checkout',
				'Something in your cart'
			)
		),

		'fb_test_mode' => 0,

		'enable_cron_server'  => 0,
		'direct_recover_link' => 1,
		'unsub_page_id'       => '',
		'unsub_title'         => 'Unsubscribe request',
		'unsub_content'       => 'You have successfully unsubscribed from our abandoned cart reminder emails.',
		'unsub_button'        => 'Shop now',
		'unsub_redirect'      => '',
		'unsub_type'          => 'cart',
	);

	public static $coupon = array(
		//Coupon setting
		'email_subject'                 => 'Hey {customer_name}!! You left something in your cart',
		'subject'                       => 'Hey {customer_name}!! You left something in your cart',
		'use_coupon'                    => 0,
		'use_coupon_generate'           => 0,
		'wc_coupon'                     => 0,
		'gnr_coupon_desc'               => '',
		'gnr_coupon_prefix'             => '',
		'gnr_coupon_type'               => 'percent',
		'gnr_coupon_amount'             => 0,
		'gnr_coupon_date_expiry'        => 30,
		'gnr_coupon_min_spend'          => '',
		'gnr_coupon_max_spend'          => '',
		'gnr_coupon_individual'         => 0,
		'gnr_coupon_exclude_sale_items' => 0,
		'gnr_coupon_products'           => array(),
		'gnr_coupon_exclude_products'   => array(),
		'gnr_coupon_categories'         => array(),
		'gnr_coupon_exclude_categories' => array(),
		'gnr_coupon_limit_per_cp'       => '',
		'gnr_coupon_limit_x_item'       => '',
		'gnr_coupon_limit_user'         => '',
		'gnr_coupon_delete'             => 0,
	);


	protected static $instance = null;

	public function __construct() {
		add_filter( 'wacv_default_params', [ $this, 'unsubscribe_page' ] );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function get_param( $name ) {
		if ( ! self::$params ) {
			self::load_params();
		}

		return isset( self::$params[ $name ] ) ? self::$params[ $name ] : '';
	}

	protected static function load_params() {
		if ( ! self::$params ) {
			self::$params = wp_parse_args( get_option( 'wacv_params' ), self::$params_default );
		}

		return self::$params;
	}

	public static function get_params() {
		if ( ! self::$params ) {
			self::load_params();
		}

		return self::$params;
	}

	public function get_coupon_params() {
		return self::$coupon;
	}

	public function member_cut_time() {
		return WACVP_CURRENT_TIME - self::get_param( 'member_cut_off_time' ) * 60;
	}

	public function guest_cut_time() {
		return WACVP_CURRENT_TIME - self::get_param( 'guest_cut_off_time' ) * 60;
	}

	public static function get_update_params() {
		self::$params = wp_parse_args( get_option( 'wacv_params' ), self::$params_default );

		return self::$params;
	}

	public function member_compare_cut_off_time() {
		return current_time( 'timestamp' ) - self::$params['member_cut_off_time'] * 60;
	}

	public function guest_compare_cut_off_time() {
		return current_time( 'timestamp' ) - self::$params['guest_cut_off_time'] * 60;
	}

	public function case_unit( $param ) {
		switch ( $param ) {
			case 'minutes':
				return MINUTE_IN_SECONDS;
			case 'hours':
				return HOUR_IN_SECONDS;
			case 'days':
				return DAY_IN_SECONDS;
			default:
				return 60;
		}
	}

	public function get_os_platform() {
		$os_platform = __( "Unknown OS Platform", 'woo-abandoned-cart-recovery' );
		if ( $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$os_array = array(
				'/windows nt 10/i'      => 'Windows 10',
				'/windows nt 6.3/i'     => 'Windows 8.1',
				'/windows nt 6.2/i'     => 'Windows 8',
				'/windows nt 6.1/i'     => 'Windows 7',
				'/windows nt 6.0/i'     => 'Windows Vista',
				'/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
				'/windows nt 5.1/i'     => 'Windows XP',
				'/windows xp/i'         => 'Windows XP',
				'/windows nt 5.0/i'     => 'Windows 2000',
				'/windows me/i'         => 'Windows ME',
				'/win98/i'              => 'Windows 98',
				'/win95/i'              => 'Windows 95',
				'/win16/i'              => 'Windows 3.11',
				'/macintosh|mac os x/i' => 'Mac OS X',
				'/mac_powerpc/i'        => 'Mac OS 9',
				'/linux/i'              => 'Linux',
				'/ubuntu/i'             => 'Ubuntu',
				'/iphone/i'             => 'iPhone',
				'/ipod/i'               => 'iPod',
				'/ipad/i'               => 'iPad',
				'/android/i'            => 'Android',
				'/blackberry/i'         => 'BlackBerry',
				'/webos/i'              => 'Mobile'
			);

			foreach ( $os_array as $regex => $value ) {
				if ( preg_match( $regex, $user_agent ) ) {
					$os_platform = $value;
				}
			}
		}

		return $os_platform;
	}


	public function get_browser() {
		if ( $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$browser       = __( "Unknown Browser", 'woo-abandoned-cart-recovery' );
			$browser_array = array(
				'/msie/i'      => 'Internet Explorer',
				'/firefox/i'   => 'Firefox',
				'/safari/i'    => 'Safari',
				'/chrome/i'    => 'Chrome',
				'/edge/i'      => 'Edge',
				'/opera/i'     => 'Opera',
				'/netscape/i'  => 'Netscape',
				'/maxthon/i'   => 'Maxthon',
				'/konqueror/i' => 'Konqueror',
				'/mobile/i'    => 'Handheld Browser'
			);

			foreach ( $browser_array as $regex => $value ) {
				if ( preg_match( $regex, $user_agent ) ) {
					$browser = $value;
				}
			}
		}

		return $browser;
	}

	public function list_language() {
		return array(

			'af_ZA' => 'Afrikaans',// Arabic

			'ar_AR' => 'Arabic',// Azerbaijani

			'az_AZ' => 'Azerbaijani',// Belarusian

			'be_BY' => 'Belarusian',// Bulgarian

			'bg_BG' => 'Bulgarian',// Bengali

			'bn_IN' => 'Bengali',// Bosnian

			'bs_BA' => 'Bosnian',// Catalan

			'ca_ES' => 'Catalan',// Czech

			'cs_CZ' => 'Czech',// Welsh

			'cy_GB' => 'Welsh',// Danish

			'da_DK' => 'Danish',// German

			'de_DE' => 'German',// Greek

			'el_GR' => 'Greek',// English (UK)

			'en_GB' => 'English (GB)',// English (Pirate)

			'en_PI' => 'English (Pirate)',// English (Upside Down)

			'en_UD' => 'English (Upside Down)',// English (US)

			'en_US' => 'English (US)',// Esperanto

			'eo_EO' => 'Esperanto',// Spanish (Spain)

			'es_ES' => 'Spanish (Spain)',// Spanish

			'es_LA' => 'Spanish',// Estonian

			'et_EE' => 'Estonian',// Basque

			'eu_ES' => 'Basque',// Persian

			'fa_IR' => 'Persian',// Leet Speak

			'fb_LT' => 'Leet Speak',// Finnish

			'fi_FI' => 'Finnish',// Faroese

			'fo_FO' => 'Faroese',// French (Canada)

			'fr_CA' => 'French (Canada)',// French (France)

			'fr_FR' => 'French (France)',// Frisian

			'fy_NL' => 'Frisian',// Irish

			'ga_IE' => 'Irish',// Galician

			'gl_ES' => 'Galician',// Hebrew

			'he_IL' => 'Hebrew',// Hindi

			'hi_IN' => 'Hindi',// Croatian

			'hr_HR' => 'Croatian',// Hungarian

			'hu_HU' => 'Hungarian',// Armenian

			'hy_AM' => 'Armenian',// Indonesian

			'id_ID' => 'Indonesian',// Icelandic

			'is_IS' => 'Icelandic',// Italian

			'it_IT' => 'Italian',// Japanese

			'ja_JP' => 'Japanese',// Georgian

			'ka_GE' => 'Georgian',// Khmer

			'km_KH' => 'Khmer',// Korean

			'ko_KR' => 'Korean',// Kurdish

			'ku_TR' => 'Kurdish',// Latin

			'la_VA' => 'Latin',// Lithuanian

			'lt_LT' => 'Lithuanian',// Latvian

			'lv_LV' => 'Latvian',// Macedonian

			'mk_MK' => 'Macedonian',// Malayalam

			'ml_IN' => 'Malayalam',// Malay

			'ms_MY' => 'Malay',// Norwegian (bokmal)

			'nb_NO' => 'Norwegian (bokmal)',// Nepali

			'ne_NP' => 'Nepali',// Dutch

			'nl_NL' => 'Dutch',// Norwegian (nynorsk)

			'nn_NO' => 'Norwegian (nynorsk)',// Punjabi

			'pa_IN' => 'Punjabi',// Polish

			'pl_PL' => 'Polish',// Pashto

			'ps_AF' => 'Pashto',// Portuguese (Brazil)

			'pt_BR' => 'Portuguese (Brazil)',// Portuguese (Portugal)

			'pt_PT' => 'Portuguese (Portugal)',// Romanian

			'ro_RO' => 'Romanian',// Russian

			'ru_RU' => 'Russian',// Slovak

			'sk_SK' => 'Slovak',// Slovenian

			'sl_SI' => 'Slovenian',// Albanian

			'sq_AL' => 'Albanian',// Serbian

			'sr_RS' => 'Serbian',// Swedish

			'sv_SE' => 'Swedish',// Swahili

			'sw_KE' => 'Swahili',// Tamil

			'ta_IN' => 'Tamil',// Telugu

			'te_IN' => 'Telugu',// Thai

			'th_TH' => 'Thai',// Filipino

			'tl_PH' => 'Filipino',// Turkish

			'tr_TR' => 'Turkish',//

			'uk_UA' => 'Ukrainian',// Vietnamese

			'vi_VN' => 'Vietnamese',//

			'zh_CN' => 'Simplified Chinese (China)',//

			'zh_HK' => 'Traditional Chinese (Hong Kong)',//

			'zh_TW' => 'Traditional Chinese (Taiwan)',

		);
	}

	public function plugin_pages() {
		return array( 'edit-wacv_email_template', 'wacv_email_template', 'toplevel_page_wacv_sections', 'abandoned-cart_page_wacv_reports', 'abandoned-cart_page_wacv_settings' );
	}

	public function unsubscribe_page( $params ) {


		return $params;
	}
}
