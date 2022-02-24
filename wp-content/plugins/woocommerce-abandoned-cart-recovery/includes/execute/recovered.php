<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 26-03-19
 * Time: 4:14 PM
 */

namespace WACVP\Inc\Execute;

use WACVP\Inc\Aes_Ctr;
use WACVP\Inc\Data;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Recovered {

	protected static $instance = null;
	public $query;
	public $settings;
	protected $coupon;
	protected $unsub_modal;

	private function __construct() {
		$this->query    = Query_DB::get_instance();
		$this->settings = Data::get_instance();

		add_action( 'template_redirect', array( $this, 'handle_callback_link' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'add_coupon' ) );
		add_action( 'woocommerce_before_cart', array( $this, 'add_coupon' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_modal_script' ) );
		add_action( 'wp_footer', array( $this, 'load_unsubscribe_modal' ) );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function handle_callback_link() {

		if ( is_admin() ) {
			return;
		}
		$this->handle_recover_cart();
		$this->handle_unsubscribe();
		$this->handle_tracking_open();
		$this->handle_recover_order();
	}

	public function handle_recover_cart() {
		if ( isset( $_GET['wacv_recover'] ) && $_GET['wacv_recover'] == 'cart_link' ) {
			if ( '' == session_id() ) {
				@session_start();
			}
			if ( isset( $_GET['valid'] ) ) {
				$pass          = get_option( 'wacv_private_key' );
				$validate_code = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['valid'] ) ) );
				$validate_code = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );

				$explode       = explode( '&', $validate_code );
				$acr_id        = isset( $explode[0] ) ? $explode[0] : '';
				$sent_email_id = isset( $explode[1] ) ? $explode[1] : '';
				$temp_id       = isset( $explode[2] ) ? $explode[2] : '';
				$coupon        = isset( $explode[3] ) ? $explode[3] : '';

//				update_option( 'test_click_' . $sent_email_id, $_SERVER );
				$this->query->update_email_tracking( $sent_email_id, 'clicked' );

				global $wpdb;

				$query = "SELECT * FROM {$this->query->cart_record_tb} WHERE id = %d LIMIT 1";
				$acr   = $wpdb->get_results( $wpdb->prepare( $query, $acr_id ) );
//				$this->query->update_abd_cart_record( array( 'abandoned_cart_time' => current_time( 'timestamp' ) ), array( 'id' => $acr_id ) );

				if ( count( $acr ) > 0 ) {
					$user_id = $acr[0]->user_id;

					if ( $user_id < WACVP_GUEST_ID_LIMIT ) {
						wp_set_current_user( $user_id );
						if ( current_user_can( 'manage_options' ) ) {
							wp_safe_redirect( site_url() );
							exit;
						}
						wp_set_auth_cookie( $user_id );

						$saved_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );

						if ( ! $saved_cart ) {
							wp_safe_redirect( site_url() );
							exit;
						}

						$cart = WC()->session->cart;

						if ( empty( $cart ) || ! is_array( $cart ) || 0 === count( $cart ) ) {
							WC()->session->cart = $saved_cart['cart'];
						}

						if ( isset( $sign_in ) && is_wp_error( $sign_in ) ) {
							echo esc_html( $sign_in->get_error_message() );
							exit;
						}

						$this->query::set_session( 'wacv_order_type', 1 );
						$this->query::set_session( 'wacv_recover_id', $acr_id );
						$this->query::set_session( 'wacv_temp_id', $temp_id );

					} else {
						$cookie_time = current_time( 'timestamp' ) + 86400;
						setCookie( 'wacv_get_email', true, $cookie_time, '/' );

						$rec_cart = json_decode( $acr[0]->abandoned_cart_info, true )['cart'];
						WC()->session->set_customer_session_cookie( true );
						$guest_info = $this->recover_get_info( $user_id );

						$this->query::set_session( 'cart', $rec_cart );
						$this->query::set_session( 'user_id', $user_id );
						$this->query::set_session( 'wacv_recover_id', $acr_id );
						$this->query::set_session( 'guest_info', $guest_info );
						$this->query::set_session( 'wacv_order_type', 1 );
						$this->query::set_session( 'wacv_temp_id', $temp_id );
					}
					if ( $coupon ) {
						wc()->session->set( 'wacv_coupon_to_add', $coupon );
					}

					if ( Data::get_param( 'direct_recover_link' ) ) {
						wp_safe_redirect( wc_get_checkout_url() );
					} else {
						wp_safe_redirect( wc_get_cart_url() );
					}
					exit;
				}
			}
		}
	}

	public function recover_get_info( $user_id ) {
		$result = $this->query->get_guest_info( $user_id );

		return $customer = array(
			"id"                  => $result->id,
			"date_modified"       => '',
			"billing_postcode"    => $result->billing_postcode,
			"billing_city"        => $result->billing_city,
			"billing_address_1"   => $result->billing_address_1,
			"billing_address"     => $result->billing_address_1,
			"billing_address_2"   => $result->billing_address_2,
			"billing_state"       => $result->billing_city,
			"billing_country"     => $result->billing_country,
			"shipping_postcode"   => $result->shipping_postcode,
			"shipping_city"       => $result->shipping_city,
			"shipping_address_1"  => $result->shipping_address_1,
			"shipping_address"    => $result->shipping_address_1,
			"shipping_address_2"  => $result->shipping_address_2,
			"shipping_state"      => $result->shipping_city,
			"shipping_country"    => $result->shipping_country,
			"billing_first_name"  => $result->billing_first_name,
			"billing_last_name"   => $result->billing_last_name,
			"billing_company"     => $result->billing_company,
			"billing_phone"       => $result->billing_phone,
			"billing_email"       => $result->billing_email,
			"shipping_first_name" => $result->shipping_first_name,
			"shipping_last_name"  => $result->shipping_last_name,
			"shipping_company"    => $result->shipping_company,
			"user_ref"            => $result->user_ref
		);
	}

	public function handle_unsubscribe() {
		if ( isset( $_GET['wacv_unsubscribe'] ) ) {
			$pass   = get_option( 'wacv_private_key' );
			$link   = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['wacv_unsubscribe'] ) ) );
			$acr_id = rawurldecode( Aes_Ctr::decrypt( $link, $pass, 256 ) );

			if ( $acr_id ) {
				wc()->cart->empty_cart();
				$this->query->update_abd_cart_record( array( 'unsubscribe_link' => 1 ), array( 'id' => $acr_id ) );

				if ( $this->settings->get_param( 'unsub_type' ) == 'email' ) {
					global $wpdb;
					$query   = "SELECT * FROM {$this->query->cart_record_tb} WHERE id = %d LIMIT 1";
					$acr     = $wpdb->get_row( $wpdb->prepare( $query, $acr_id ) );
					$user_id = $acr->user_id;

					if ( $user_id < WACVP_GUEST_ID_LIMIT ) {
						update_user_meta( $user_id, 'wacv_unsubscribe', true );
					} else {
						$this->query->update_guest_info( $user_id, [ 'status' => 'unsubscribe' ] );
					}
				}

				$this->unsub_modal = true;
			}
		}
	}

	public function enqueue_modal_script() {
		if ( ! $this->unsub_modal ) {
			return;
		}

		wp_register_script( WACVP_SLUG . '-unsubscribe', WACVP_JS . 'unsubscribe-modal.js', array( 'jquery' ), WACVP_VERSION );
		wp_register_style( WACVP_SLUG . '-unsubscribe', WACVP_CSS . 'unsubscribe-modal.css', '', WACVP_VERSION );
	}

	public function load_unsubscribe_modal() {
		if ( ! $this->unsub_modal ) {
			return;
		}
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( WACVP_SLUG . '-unsubscribe' );
		wp_enqueue_style( WACVP_SLUG . '-unsubscribe' );

		$title   = $this->settings::get_param( 'unsub_title' );
		$content = $this->settings::get_param( 'unsub_content' );
		$button  = $this->settings::get_param( 'unsub_button' );
		$href    = $this->settings::get_param( 'unsub_redirect' );

		$title_color     = $this->settings::get_param( 'popup_title_color' );
		$sub_title_color = $this->settings::get_param( 'popup_sub_title_color' );
		$bg_color        = $this->settings::get_param( 'popup_bg_color' );
		$btn_color       = $this->settings::get_param( 'popup_btn_color' );
		$btn_bg_color    = $this->settings::get_param( 'popup_btn_bg_color' );

		$css = ".wacv-unsub-title{color:$title_color}";
		$css .= ".wacv-unsub-message{color:$sub_title_color}";
		$css .= ".wacv-unsub-content-layer{background-color:$bg_color}";
		$css .= ".wacv-unsub-redirect-button{color:$btn_color; background-color:$btn_bg_color}";

		wp_add_inline_style( WACVP_SLUG . '-unsubscribe', $css );
		?>
        <div id="wacv-unsubscribe-modal">
            <div class="wacv-modal-relative-layer">
                <div class="wacv-modal-absolute-layer">
                    <div class="wacv-unsub-content-layer">
                        <div class="wacv-modal-close dashicons dashicons-no-alt" title="<?php esc_html_e( 'Close', 'woo-abandoned-cart-recovery' ); ?>">
                        </div>
                        <div class="wacv-unsub-main-content">
							<?php
							if ( $title ) {
								printf( '<h2 class="wacv-unsub-title">%s</h2>', esc_html( $title ) );
							}

							if ( $content ) {
								printf( '<div class="wacv-unsub-message">%s</div>', esc_html( $content ) );
							}

							if ( $button && $href ) {
								printf( '<a href="%s" class="wacv-unsub-redirect-button">%s</a>', esc_url( $href ), esc_html( $button ) );
							}
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public function handle_tracking_open() {
		if ( isset( $_GET['wacv_open_email'] ) ) {
			$pass          = get_option( 'wacv_private_key' );
			$validate_code = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['wacv_open_email'] ) ) );
			$validate_code = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );
			$pos_acr       = strpos( $validate_code, '&' );
			$pos_email_id  = strpos( $validate_code, '&', $pos_acr + 1 );

			$acr_id        = intval( substr( $validate_code, 0, $pos_acr ) );
			$sent_email_id = $pos_email_id ? substr( $validate_code, $pos_acr + 1, $pos_email_id - $pos_acr - 1 ) : substr( $validate_code, $pos_acr + 1 );
			$this->query->update_email_tracking( $sent_email_id, 'opened' );

		}
	}

	public function handle_recover_order() {
		if ( isset( $_GET['wacv_recover'] ) && $_GET['wacv_recover'] == 'order_link' ) {
			if ( isset( $_GET['valid'] ) ) {
				$pass             = get_option( 'wacv_private_key' );
				$validate_code    = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['valid'] ) ) );
				$validate_code    = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );
				$order_id_pos     = strpos( $validate_code, '&' );
				$sent_mail_id_pos = strpos( $validate_code, '&', $order_id_pos + 1 );
				$order_id         = intval( substr( $validate_code, 0, $order_id_pos ) );
				$sent_email_id    = $sent_mail_id_pos ? substr( $validate_code, 0, $order_id_pos ) : substr( $validate_code, $order_id_pos + 1 );
				$order            = wc_get_order( $order_id );

				if ( $order ) {
					$check_stt = $order->get_status();

					$this->query->update_email_tracking( $sent_email_id, 'clicked' );

					if ( $check_stt == 'cancelled' ) {
						$order->update_status( 'pending' );
					}

					$checkout_url = ( $order->get_checkout_payment_url() );
					wp_safe_redirect( $checkout_url );
				} else {
					wp_safe_redirect( home_url() );
				}
				exit;

			} elseif ( isset( $_GET['unsubscribe'] ) ) {
				$pass          = get_option( 'wacv_private_key' );
				$validate_code = str_replace( ' ', '+', rawurldecode( sanitize_text_field( $_GET['unsubscribe'] ) ) );
				$validate_code = rawurldecode( Aes_Ctr::decrypt( $validate_code, $pass, 256 ) );
				$order_id      = $validate_code;
				update_post_meta( $order_id, '_wacv_reminder_unsubscribe', 1 );
				wp_safe_redirect( home_url() );
				exit;
			}
		}
	}

	public function add_coupon() {
		$coupon = wc()->session->get( 'wacv_coupon_to_add' );
		if ( $coupon ) {
			wc()->cart->apply_coupon( sanitize_text_field( $coupon ) );
			wc()->session->__unset( 'wacv_coupon_to_add' );
			wc()->cart->calculate_totals();
		}
	}
}
