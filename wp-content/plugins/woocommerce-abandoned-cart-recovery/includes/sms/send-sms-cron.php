<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 25-03-19
 * Time: 5:00 PM
 */

namespace WACVP\Inc\SMS;

use WACVP\Inc\Aes_Ctr;
use WACVP\Inc\Data;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Send_SMS_Cron {

	protected static $instance = null;

	protected $query;

	protected $data;

	protected $phoneNumberUtil;

	protected $format_e164;

	protected $characters_array;

	protected $error_code;

	protected $last_time;

	private function __construct() {
		$this->query = Query_DB::get_instance();
		$this->data  = Data::get_params();

//		add_action( 'init', array( $this, 'api_init' ) );
//		add_action( 'admin_init', array( $this, 'check_phone' ) );
//		add_action( 'admin_init', array( $this, 'send_sms_init' ) );

		add_action( 'wacv_cron_send_sms', array( $this, 'send_sms_init' ) );
		add_action( 'admin_notices', array( $this, 'sms_balance_notice' ) );
		add_action( 'wp_ajax_wacv_send_test_sms', array( $this, 'send_test_sms' ) );
		add_action( 'admin_head', [ $this, 'check_sms_log' ] );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function check_phone() {
		$phone   = '010 2523 6156';
		$country = 'KR';
		$this->api_init();

		$is_phone = false;
		try {

			$phoneNumberObject = @$this->phoneNumberUtil->parse( $phone, $country );
			$is_phone          = $this->phoneNumberUtil->isPossibleNumber( $phoneNumberObject );
		} catch ( \Exception $e ) {
		}

		if ( $is_phone ) {
			$phone_formated = $this->phoneNumberUtil->format( $phoneNumberObject, $this->format_e164 );
			echo '<pre>', print_r( $phone_formated, true ), '</pre><hr>';
		}
	}

	public function api_init() {
		require_once WACVP_INCLUDES . 'sms/libphonenumber/autoload.php';
		$this->phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$this->format_e164     = \libphonenumber\PhoneNumberFormat::E164;
	}

	public function send_sms_init() {
		$check = $this->check_balance();
		if ( floatval( $check ) < 0.5 ) {
			update_option( 'wacv_check_balance', false );

			return;
		}

		update_option( 'wacv_check_balance', true );

		if ( $this->data['sms_abd_cart_enable'] || $this->data['sms_abd_order_enable'] ) {
			$this->api_init();
			if ( $this->data['sms_abd_cart_enable'] ) {
				$this->send_sms_abd_cart();
			}
			if ( $this->data['sms_abd_order_enable'] ) {
				$this->send_sms_abd_order();
			}
		}
	}

	public function check_balance() {
		if ( defined( 'VI_DEBUG' ) && VI_DEBUG ) {
			return 1;
		}
		$result = 1;
		switch ( $this->data['sms_provider'] ) {
			case 'twilio':
				if ( $this->data['sms_app_id'] && $this->data['sms_app_secret'] ) {
					$url  = "https://api.twilio.com/2010-04-01/Accounts/" . $this->data['sms_app_id'] . "/Balance.json";
					$args = array(
						'headers' => array(
							'Authorization' => 'Basic ' . base64_encode( $this->data['sms_app_id'] . ':' . $this->data['sms_app_secret'] )
						)
					);

					$res = wp_remote_get( $url, $args );

					if ( wp_remote_retrieve_response_code( $res ) < 400 ) {
						$res_body = json_decode( wp_remote_retrieve_body( $res ) );
						$result   = $res_body->balance;
					}
				}
				break;
			case 'nexmo':
				if ( $this->data['sms_app_id_nexmo'] && $this->data['sms_app_secret_nexmo'] ) {
					$url = 'https://rest.nexmo.com/account/get-balance';

					$response      = wp_remote_get( $url, array(
						'body' => array(
							'api_key'    => $this->data['sms_app_id_nexmo'],
							'api_secret' => $this->data['sms_app_secret_nexmo']
						)
					) );
					$response_code = wp_remote_retrieve_response_code( $response );
					if ( $response_code < 400 ) {
						$result = json_decode( wp_remote_retrieve_body( $response ) )->value;
					}
				}
				break;
			case 'plivo':
				if ( $this->data['sms_app_id_plivo'] && $this->data['sms_app_secret_plivo'] ) {
					$url      = "https://api.plivo.com/v1/Account/{$this->data['sms_app_id_plivo']}/";
					$headers  = array(
						'Authorization' => 'Basic ' . base64_encode( $this->data['sms_app_id_plivo'] . ':' . $this->data['sms_app_secret_plivo'] )
					);
					$response = wp_remote_get( $url, array( 'headers' => $headers ) );
					$result   = json_decode( wp_remote_retrieve_body( $response ) )->cash_credits;
				}
				break;
		};

		return $result;
	}

	public function send_sms_abd_cart() {
		if ( ! empty( $this->data['sms_abd_cart'] ) ) {
			$rules = $this->data['sms_abd_cart'];
			for ( $i = 0; $i < count( $rules['send_time'] ); $i ++ ) {
				$this->last_time = $i == count( $rules['send_time'] ) - 1 ? true : false;
				$time_to_send    = current_time( 'timestamp' ) - intval( $rules['time_to_send'][ $i ] ) * Data::get_instance()->case_unit( $rules['unit'][ $i ] );
				$lists           = $this->query->get_list_sms_to_send( $time_to_send, $rules['send_time'][ $i ] );
				if ( is_array( $lists ) && count( $lists ) > 0 ) {
					if ( isset( $rules['message'][ $i ] ) ) {
						foreach ( $lists as $item ) {
							if ( $item->user_id < WACVP_GUEST_ID_LIMIT || $item->user_id >= WACVP_GUEST_ID_LIMIT && $item->billing_phone ) {
								$this->sms_content_cart( $item, $rules['message'][ $i ], $rules['send_time'][ $i ] );
							}
						}
					}
				}
			}
		}
	}

	public function sms_content_cart( $item, $template, $time ) {

		if ( ! empty( $item->user_id ) && $template ) {
			$phone            = $item->billing_phone;
			$customer_name    = $item->billing_first_name;
			$customer_surname = $item->billing_last_name;
			$country          = $item->billing_country;

			if ( $item->user_type == 'member' ) {
				$phone            = get_user_meta( $item->user_id, 'billing_phone', true );
				$country          = get_user_meta( $item->user_id, 'billing_country', true );
				$customer_surname = get_user_meta( $item->user_id, 'billing_last_name', true );
				$customer_name    = get_user_meta( $item->user_id, 'billing_first_name', true );
			}


			$acr_id   = $item->id;
			$is_phone = $phoneNumberObject = false;

			try {
				$phoneNumberObject = $this->phoneNumberUtil->parse( $phone, $country );
				$is_phone          = $this->phoneNumberUtil->isPossibleNumber( $phoneNumberObject );
			} catch ( \Exception $e ) {
				echo esc_html( $e->getMessage() );
			}

			if ( $is_phone && $phoneNumberObject ) {
				$phone_formated = $this->phoneNumberUtil->format( $phoneNumberObject, $this->format_e164 );

				$result = $this->send_sms( 'cart', $phone_formated, $acr_id, $template, $customer_name, $customer_surname );
				if ( $result ) {
					$complete = $this->last_time ? '1' : null;
					$this->query->update_abd_cart_record( array( 'sms_sent' => $time, 'sms_complete' => $complete ), array( 'id' => $acr_id ) );
				}
			} else {
				$this->query->update_abd_cart_record( array( 'valid_phone' => 1 ), array( 'id' => $acr_id ) );
			}
		}
	}

	public function send_sms( $type, $phone, $acr_id, $template, $customer_name, $customer_surname ) {
		$result = false;
		if ( $phone ) {

			$sent_email_id = uniqid() . $acr_id;
			$pass          = get_option( 'wacv_private_key' );
			$url_encode    = Aes_Ctr::encrypt( $acr_id . '&' . $sent_email_id, $pass, 256 );

			$link = site_url( "wacv?wacv_recover={$type}_link&valid=" ) . $url_encode;

			$link = $this->get_shorten_link( $link );

			if ( ! $link ) {
				return false;
			}

			$search  = array( '{customer_name}', '{customer_surname}', '{checkout_link}' );
			$replace = array( $customer_name, $customer_surname, $link );
			$message = str_replace( $search, $replace, $template );

			//Select sms provider
			$func = "send_sms_by_{$this->data['sms_provider']}";

//			if ( defined( 'VI_DEBUG' ) && VI_DEBUG ) {
//				$func = 'test_sms_provider';
//			}

			$result = $this->$func( $phone, $message, $type, $acr_id, $sent_email_id );
		}

		return $result;
	}

	public function get_shorten_link( $long_url ) {
		if ( ! $this->data['shortlink_enable'] || ! $this->data['shortlink_access_token'] ) {
			return $long_url;
		}

//		if ( defined( 'VI_DEBUG' ) && VI_DEBUG ) {
//			$long_url = 'http://test.new2new.com/wacv?wacv_recover=cart_link&valid=UQJyWF4tLF3WcLmb5GgnaDNVxJoLzYc5TGR2SQ=='; //test
//		}

		$url     = 'https://api-ssl.bitly.com/v4/shorten';
		$headers = array(
			'Authorization' => "Bearer {$this->data['shortlink_access_token']}",
			'Content-Type'  => 'application/json',
		);

		$body = array( 'long_url' => $long_url );

		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => json_encode( $body )
		) );

		$stt_code = wp_remote_retrieve_response_code( $response );

		if ( $stt_code >= 400 ) {
			return $long_url;
		}

		return json_decode( $response['body'] )->link;
	}

	public function send_sms_abd_order() {
		if ( ! empty( $this->data['sms_abd_order'] ) ) {
			$rules = $this->data['sms_abd_order'];
			for ( $i = 0; $i < count( $rules['send_time'] ); $i ++ ) {
				$time_to_send = current_time( 'timestamp' ) - intval( $rules['time_to_send'][ $i ] ) * Data::get_instance()->case_unit( $rules['unit'][ $i ] );
				$args         = array(
					'post_type'   => 'shop_order',
					'post_status' => $this->data['sms_order_stt'],
					'date_query'  => array(
						array(
							'before' => array(
								'year'   => date_i18n( 'Y', $time_to_send ),
								'month'  => date_i18n( 'm', $time_to_send ),
								'day'    => date_i18n( 'd', $time_to_send ),
								'hour'   => date_i18n( 'H', $time_to_send ),
								'minute' => date_i18n( 'i', $time_to_send ),
								'second' => date_i18n( 's', $time_to_send ),
							),
							'column' => 'post_modified',
						),
					),
					'meta_query'  => array(
						array(
							'key'   => '_wacv_send_reminder_sms',
							'value' => $rules['send_time'] [ $i ] - 1,
						),
						array(
							'key'   => '_wacv_reminder_unsubscribe',
							'value' => '',
						),
						array(
							'key'   => '_wacv_check_phone_number',
							'value' => '',
						),
					)
				);

				$the_query = new \WP_Query( $args );

				if ( $the_query->have_posts() ) :
					foreach ( $the_query->get_posts() as $order ) {
						$this->sms_content_order( $order->ID, $rules['message'] [ $i ], $rules['send_time'] [ $i ] );
					}
					wp_reset_postdata();
				endif;

			}
		}
	}

	public function sms_content_order( $order_id, $template, $time ) {
		$order            = wc_get_order( $order_id );
		$phone            = $order->get_billing_phone();
		$customer_name    = $order->get_billing_first_name();
		$country          = $order->get_billing_country();
		$customer_surname = $order->get_billing_last_name();

		$is_phone = $phoneNumberObject = false;

		try {
			$phoneNumberObject = $this->phoneNumberUtil->parse( $phone, $country );
			$is_phone          = $this->phoneNumberUtil->isPossibleNumber( $phoneNumberObject );
		} catch ( \Exception $e ) {

		}

		if ( $is_phone && $phoneNumberObject ) {
			$phone_formated = $this->phoneNumberUtil->format( $phoneNumberObject, $this->format_e164 );

			$sent = $this->send_sms( 'order', $phone_formated, $order_id, $template, $customer_name, $customer_surname );
			if ( $sent ) {
				update_post_meta( $order_id, '_wacv_send_reminder_sms', $time );
			}
		} else {
			update_post_meta( $order_id, '_wacv_check_phone_number', 'NG' );
		}
	}

	public function send_sms_by_nexmo( $phone, $message, $type, $acr_id, $sent_email_id ) {
		$result = false;
		$url    = 'https://rest.nexmo.com/sms/json?' . http_build_query( [
				'api_key'    => $this->data['sms_app_id_nexmo'],
				'api_secret' => $this->data['sms_app_secret_nexmo'],
				'to'         => $phone,
				'from'       => $this->data['from_phone_nexmo'],
				'text'       => $message
			] );

		$response = wp_remote_post( $url );
		$res_code = wp_remote_retrieve_response_code( $response );

		if ( $res_code < 400 ) {
			$res        = (array) json_decode( wp_remote_retrieve_body( $response ) );
			$res_status = $res['message-count'];
			if ( $res_status ) {
				$this->query->insert_email_history( 'sms_' . $type, $acr_id, $sent_email_id );
				$result = true;
			}
		} else {
			if ( $type == 'order' ) {
				update_post_meta( $acr_id, '_wacv_check_phone_number', 'NG' );
			} elseif ( $type == 'cart' ) {
				$this->query->update_abd_cart_record( array( 'valid_phone' => 1 ), array( 'id' => $acr_id ) );
			}
		}


		return $result;
	}

	public function send_sms_by_twilio( $phone, $message, $type, $acr_id, $sent_email_id ) {
		$result  = false;
		$url     = "https://api.twilio.com/2010-04-01/Accounts/" . $this->data['sms_app_id'] . "/Messages.json";
		$data    = array(
			'From' => $this->data['from_phone'],
			'To'   => $phone,
			'Body' => $message
		);
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->data['sms_app_id'] . ':' . $this->data['sms_app_secret'] )
		);

		$response = wp_remote_post( $url, array(
			'body'    => $data,
			'headers' => $headers
		) );

		$res_code = wp_remote_retrieve_response_code( $response );

		$res = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $res_code < 400 ) {
			$res_status = $res->status;
			if ( in_array( $res_status, array( 'queued', 'sent' ) ) ) {
				$this->query->insert_email_history( 'sms_' . $type, $acr_id, $sent_email_id );
				$result = true;
			}

		} else {
			if ( $type == 'order' ) {
				update_post_meta( $acr_id, '_wacv_check_phone_number', 'NG' );
			} elseif ( $type == 'cart' ) {
				$this->query->update_abd_cart_record( array( 'valid_phone' => 1 ), array( 'id' => $acr_id ) );
			}
		}

		if ( method_exists( $res, 'error_code' ) && $res->error_code != null ) {
			$key           = 'wacv_send_sms_log';
			$log           = get_option( $key, [] );
			$log           = array_slice( $log, - 30, 30 );
			$phone         = str_replace( ' ', '', $phone );
			$log[ $phone ] = $res;
			update_option( $key, $log );
		}

		return $result;
	}


	public function send_sms_by_plivo( $phone, $message, $type, $acr_id, $sent_email_id ) {
		$result  = false;
		$url     = "https://api.plivo.com/v1/Account/" . $this->data['sms_app_id_plivo'] . "/Message/";
		$data    = array(
			"powerpack_uuid" => $this->data['powerpack_uuid'],
			"dst"            => $phone,
			"text"           => $message
		);
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->data['sms_app_id_plivo'] . ':' . $this->data['sms_app_secret_plivo'] )
		);

		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => $data
		) );

		$res_code = wp_remote_retrieve_response_code( $response );

		if ( $res_code < 400 ) {
			$message = wp_remote_retrieve_response_message( $response );
			if ( strtolower( $message ) == 'accepted' ) {
				$this->query->insert_email_history( 'sms_' . $type, $acr_id, $sent_email_id );
				$result = true;
			}
		} else {
			if ( $type == 'order' ) {
				update_post_meta( $acr_id, '_wacv_check_phone_number', 'NG' );
			} elseif ( $type == 'cart' ) {
				$this->query->update_abd_cart_record( array( 'valid_phone' => 1 ), array( 'id' => $acr_id ) );
			}
		}

		return $result;
	}

	public function sms_balance_notice() {
		if ( $this->data['sms_abd_cart_enable'] || $this->data['sms_abd_order_enable'] ) {
			$check = get_option( 'wacv_check_balance' );
			if ( $check ) {
				return;
			}
			?>
            <div id="message" class="notice notice-error is-dismissible">
                <p><?php _e( 'You don\'t have enough balance to send the SMS or account is incorrect.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
			<?php
		}
	}


	public function send_test_sms() {
		$provider = ! empty( $_POST['provider'] ) ? sanitize_text_field( $_POST['provider'] ) : '';
		$id       = ! empty( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		$secret   = ! empty( $_POST['secret'] ) ? sanitize_text_field( $_POST['secret'] ) : '';
		$from     = ! empty( $_POST['number'] ) ? sanitize_text_field( $_POST['number'] ) : '';
		$to       = ! empty( $_POST['to'] ) ? sanitize_text_field( $_POST['to'] ) : '';
		if ( $provider && $id && $secret && $from && $to ) {
			$func   = $provider . '_test';
			$result = $this->$func( $id, $secret, $from, $to );
			if ( $result['code'] < 400 ) {
				wp_send_json_success( $result['message'] );
			} else {
				wp_send_json_error( $result['message'] );
			}
		} else {
			wp_send_json_error( __( 'Please fill all fields', 'woo-abandoned-cart-recovery' ) );
		}
		wp_die();
	}


	public function twilio_test( $id, $secret, $from, $to ) {
		$url     = "https://api.twilio.com/2010-04-01/Accounts/" . $id . "/Messages.json";
		$data    = array(
			'From' => $from,
			'To'   => $to,
//			'Body' => 'Hello Smith, to complete your order at shopxtraa.ng, please follow the link to checkout https://shopxtraa.ng/wacv?wacv_recover=order_link&valid=aglJpRHrYGCe8X4nargCUA8NwjnMqk5RNoHc5wZ. If you encounter any issue call 09045075731'
			'Body' => __( 'Hello, this is test sms message from Twilio', 'woo-abandoned-cart-recovery' )
		);
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $id . ':' . $secret )
		);

		$response = wp_remote_post( $url, array(
			'body'    => $data,
			'headers' => $headers
		) );
		$res_code = wp_remote_retrieve_response_code( $response );
		$body     = wp_remote_retrieve_body( $response );
		$message  = $res_code < 400 ? __( 'Successful.', 'woo-abandoned-cart-recovery' ) : json_decode( $body )->message;

		$return = array(
			'code'    => $res_code,
			'message' => $message
		);

		return $return;
	}


	public function nexmo_test( $id, $secret, $from, $to ) {
		$url  = 'https://rest.nexmo.com/sms/json';
		$data = array(
			'api_key'    => $id,
			'api_secret' => $secret,
			'to'         => $to,
			'from'       => $from,
			'text'       => __( 'Hello, this is test sms message from Nexmo', 'woo-abandoned-cart-recovery' )
		);

		$response = wp_remote_post( $url, array( 'body' => $data ) );
		$res_code = wp_remote_retrieve_response_code( $response );
		$body     = wp_remote_retrieve_body( $response );
		$message  = (array) json_decode( $body )->messages[0];
		$message  = $res_code < 400 ? __( 'Successful.', 'woo-abandoned-cart-recovery' ) : $message['error-text'];
		$return   = array(
			'code'    => $res_code,
			'message' => $message
		);

		return $return;
	}


	public function plivo_test( $id, $secret, $from, $to ) {
		$url     = "https://api.plivo.com/v1/Account/" . $id . "/Message/";
		$data    = array(
			"powerpack_uuid" => $from,
			"dst"            => $to,
			"text"           => __( 'Hello, this is test sms message from Plivo', 'woo-abandoned-cart-recovery' )
		);
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $id . ':' . $secret )
		);

		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => $data
		) );
		$res_code = wp_remote_retrieve_response_code( $response );
		$body     = wp_remote_retrieve_body( $response );
		$message  = $res_code < 400 ? __( 'Successful.', 'woo-abandoned-cart-recovery' ) : json_decode( $body )->error;
		$return   = array(
			'code'    => $res_code,
			'message' => $message
		);

		return $return;
	}

	public function test_sms_provider( $phone, $message, $type, $acr_id, $sent_email_id ) {

		$this->query->insert_email_history( 'sms_' . $type, $acr_id, $sent_email_id );
		$result = true;

		return $result;
	}

	public function check_sms_log() {
		if ( isset( $_GET['wacv_check_sms_log'] ) && $_GET['wacv_check_sms_log'] ) {
			$log = get_option( 'wacv_send_sms_log' );
			echo '<pre>' . print_r( $log, true ) . '</pre>';
		}
	}
}
