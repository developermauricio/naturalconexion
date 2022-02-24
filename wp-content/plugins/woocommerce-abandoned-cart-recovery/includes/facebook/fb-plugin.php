<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 06-06-19
 * Time: 8:31 AM
 */

namespace WACVP\Inc\Facebook;

use WACVP\Inc\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FB_Plugin {
	protected static $instance = null;
	private $settings;

	public function __construct() {
		$this->settings = Data::get_params();

		add_action( 'init', array( $this, 'facebook_verify' ) );
		add_action( 'wp_ajax_wacv_fb_message', array( $this, 'facebook_return' ) );
		add_action( 'wp_ajax_nopriv_wacv_fb_message', array( $this, 'facebook_return' ) );
		add_action( 'wp_ajax_wacv_logout_fb', array( $this, 'logout_fb' ) );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function facebook_verify() {
		if ( isset( $_GET['wacv_fb_verify'] ) && $_GET['wacv_fb_verify'] == 'true' ) {
			$verify_token = $this->settings['app_verify_token'];
			if ( isset( $_REQUEST['hub_mode'] ) && $_REQUEST['hub_mode'] == 'subscribe' ) {
				$challenge        = $_REQUEST['hub_challenge'];
				$hub_verify_token = $_REQUEST['hub_verify_token'];
				if ( $hub_verify_token === $verify_token ) {
					header( 'HTTP/1.1 200 OK' );
					echo esc_html( $challenge );
					wp_die();
				}
			}
			die;
		}
	}

	public function facebook_return() {
		$hub_verify_token = null;
		$verify_token     = $this->settings['app_verify_token'];
		if ( isset( $_REQUEST['hub_mode'] ) && $_REQUEST['hub_mode'] == 'subscribe' ) {
			$challenge        = $_REQUEST['hub_challenge'];
			$hub_verify_token = $_REQUEST['hub_verify_token'];
			if ( $hub_verify_token === $verify_token ) {
				header( 'HTTP/1.1 200 OK' );
				echo esc_html( $challenge );
				wp_die();
			}
		}
		$json     = file_get_contents( 'php://input' );
		$action   = json_decode( $json, true );
		$settings = Data::get_params();

		if ( $settings['fb_test_mode'] ) {
			$user_id      = isset( $action['entry'][0]['messaging'][0]['sender']['id'] ) ? $action['entry'][0]['messaging'][0]['sender']['id'] : '';
			$message      = isset( $action['entry'][0]['messaging'][0]['message']['text'] ) ? strtolower( $action['entry'][0]['messaging'][0]['message']['text'] ) : '';
			$send_message = '';

			if ( $user_id ) {
				$fb_api            = Api::get_instance();
				$page_id           = $settings['page_id'];
				$user_token        = $settings['user_token'];
				$page_access_token = $fb_api->Get_Access_Token_Page( $user_token, $page_id );
				$page_token        = $page_access_token['access_token'];
				$user_profile      = $fb_api->get_user_profile( $page_token, $user_id );
				$name[]            = $user_profile['first_name'] ?? '';
				$name[]            = $user_profile['last_name'] ?? '';
				$name              = implode( ' ', $name );

				switch ( $message ) {
					case 'hi':
					case 'hello':
						$send_message = __( 'Hi', 'woo-abandoned-cart-recovery' ) . " {$name}. " . __( 'Welcome to our store. Can I help you?', 'woo-abandoned-cart-recovery' );
						break;
					case 'help':
						$send_message = __( 'Can I help you?', 'woo-abandoned-cart-recovery' );
						break;
					case 'information':
					case 'info':
						$send_message = __( 'Please view our information at ', 'woo-abandoned-cart-recovery' ) . home_url();
						break;
				}

				try {
					$fb_api->send_message_text_user_id( $page_id, $page_token, $send_message, $user_id );
				} catch ( \Exception $e ) {
//					$e->getMessage();
//				update_option( 'fb_webhook_result', $e->getMessage() );
				}
			}
		}
		wp_die();
	}

	public function logout_fb() {
		$new_data = array( 'user_token' => '', 'page_id' => '' );
		$data     = wp_parse_args( $new_data, $this->settings );
		$result   = update_option( 'wacv_params', $data );
		if ( $result ) {
			wp_send_json_success();
		}
		wp_die();
	}

}