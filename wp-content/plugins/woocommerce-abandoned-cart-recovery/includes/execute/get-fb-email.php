<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 14-03-19
 * Time: 4:26 PM
 */

namespace WACVP\Inc\Execute;

use WACVP\Inc\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_FB_Email {

	protected static $instance = null;
	public $render_email_popup = false;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_enqueue' ] );
		add_action( 'wp_head', [ $this, 'load_email_popup_temp' ], 1 );
		add_action( 'template_redirect', array( $this, 'redirect' ) );
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_messenger_cb' ) );
	}

	public function frontend_enqueue() {
		$min = WP_DEBUG ? '' : '.min';
		wp_register_script( WACVP_SLUG . 'abandoned-cart', WACVP_JS . 'abandoned-cart' . $min . '.js', [ 'jquery' ], WACVP_VERSION, true );
		wp_register_style( WACVP_SLUG . 'get-email', WACVP_CSS . 'get-email.css', '', WACVP_VERSION );

		$settings    = Data::get_params();
		$cond        = $params = [];
		$fb_checkbox = false;

		if ( ! ( is_user_logged_in() || wc()->session && wc()->session->get( 'user_id' ) ) ) {
			$cond[] = is_shop() && $settings['shop_page'];
			$cond[] = is_product() && $settings['single_page'];
			$cond[] = is_cart() && $settings['cart_page'];
			$cond[] = is_front_page() && $settings['front_page'];
			$cond[] = is_home() && $settings['front_page'];
			$cond[] = is_product_category() && $settings['category_page'];
			$cond[] = is_page( explode( ',', str_replace( ' ', '', $settings['popup_page_id'] ) ) );

			if ( array_sum( $cond ) ) {
				$this->render_email_popup = true;

				$params = array_merge( $params, [
					'emailPopup'     => true,
					'cartPage'       => wc_get_cart_url(),
					'checkoutPage'   => wc_get_checkout_url(),
					'dismissDelay'   => $settings['dismiss_delay'] * 60,
					'redirect'       => $settings['redirect_after_atc'] == 'to_cart_page' ? wc_get_cart_url() : ( $settings['redirect_after_atc'] == 'to_checkout_page' ? wc_get_checkout_url() : '' ),
					'i18n_view_cart' => esc_attr__( 'View cart', 'woo-abandoned-cart-recovery' ),
					'emailField'     => $settings['email_field'],
					'phoneField'     => $settings['phone_field'],
					'gdprField'      => $settings['enable_gdpr'],
					'style'          => $settings['template_popup']
				] );

				wp_enqueue_style( WACVP_SLUG . 'get-email' );

				$title_color        = $settings['popup_title_color'];
				$sub_title_color    = $settings['popup_sub_title_color'];
				$notice_color       = $settings['popup_notice_color'];
				$bg_color           = $settings['popup_bg_color'];
				$btn_color          = $settings['popup_btn_color'];
				$btn_bg_color       = $settings['popup_btn_bg_color'];
				$input_bg_color     = $settings['popup_input_bg_color'];
				$input_border_color = $settings['popup_input_border_color'];
				$input_text_color   = $settings['popup_input_text_color'];

				$css = ".wacv-get-email-title{color:$title_color}";
				$css .= ".wacv-get-email-sub-title{color:$sub_title_color}";
				$css .= ".wacv-email-invalid-notice{color:$notice_color}";
				$css .= ".wacv-modal-content{background-color:$bg_color}";
				$css .= ".wacv-get-email-btn{color:$btn_color; background-color:$btn_bg_color}";
				$css .= ".wacv-popup-input-email {background-color:$input_bg_color !important; border: 1px solid $input_border_color !important;color:$input_text_color !important;}";
				$css .= ".wacv-popup-input-phone-number{background-color:$input_bg_color !important;color:$input_text_color !important;}";
				$css .= ".wacv-country-calling-code{color:$input_text_color !important;}";
				$css .= ".wacv-get-customer-phone-number{background-color:$input_bg_color !important; border: 1px solid $input_border_color !important;color:$input_text_color !important;}";
				$css .= "::placeholder{color:$input_text_color !important; opacity:0.7;}";
				$css .= ":-ms-input-placeholder{color:$input_text_color !important; opacity:0.7;}";
				$css .= "::-ms-input-placeholder{color:$input_text_color !important; opacity:0.7;}";

				wp_add_inline_style( WACVP_SLUG . 'get-email', $css );
			}

		}

		if ( array_sum( $cond ) || is_product() ) {
			$fb_checkbox = true;
		}

		if ( $fb_checkbox ) {
			wp_enqueue_script( WACVP_SLUG . 'abandoned-cart' );

			$params = array_merge( $params, [
					'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
					'homeURL'     => home_url(),
					'userToken'   => $settings['user_token'],
					'appLang'     => $settings['app_lang'],
					'appSkin'     => $settings['app_skin'],
					'appID'       => $settings['app_id'],
					'pageID'      => $settings['page_id'],
					'fbCbRequire' => $settings['checkbox_require']
				]
			);
			wp_localize_script( WACVP_SLUG . 'abandoned-cart', 'wacvParams', $params );
		}

	}

	public function load_email_popup_temp() {
		if ( ! $this->render_email_popup ) {
			return;
		}
		$settings = Data::get_params();
		wc_get_template( 'popup-' . $settings['template_popup'] . '.php', array( 'param' => $settings ), '', WACVP_TEMPLATES );
	}

	public function show_messenger_cb() {
		if ( $this->render_email_popup ) {
			return;
		}
		?>
        <div class="fb-messenger-checkbox-container"></div>
		<?php
	}

	public function redirect() {
		if ( isset( $_POST['wacv_redirect'] ) ) {
			wp_safe_redirect( sanitize_text_field( $_POST['wacv_redirect'] ) );
			exit;
		}
	}
}
