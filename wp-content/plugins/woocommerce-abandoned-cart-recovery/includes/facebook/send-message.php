<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 10-06-19
 * Time: 10:29 AM
 */

namespace WACVP\Inc\Facebook;

use WACVP\Inc\Aes_Ctr;
use WACVP\Inc\Data;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Send_Message {

	protected static $instance = null;
	public $query;
	public $fb_api;
	public $settings;
	public $last_time;
	public $page_access_token;

	private function __construct() {

		$this->query    = Query_DB::get_instance();
		$this->fb_api   = Api::get_instance();
		$this->settings = Data::get_params();

		add_action( 'wacv_cron_send_messenger', array( $this, 'send_reminder_messenger' ) );

	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function send_reminder_messenger() {
		if ( ! empty( $this->settings['messenger_rules'] ) && ! empty( $this->settings['page_id'] ) ) {
			$messenger_rules = $this->settings['messenger_rules'];
			for ( $i = 0; $i < count( $messenger_rules['send_time'] ); $i ++ ) {
				$this->last_time = $i == count( $messenger_rules['send_time'] ) - 1 ? true : false;
				$time_to_send    = current_time( 'timestamp' ) - intval( $messenger_rules['time_to_send'][ $i ] ) * Data::get_instance()->case_unit( $messenger_rules['unit'][ $i ] );

				$lists = $this->query->get_list_message_to_send( $time_to_send, $messenger_rules['send_time'][ $i ] );

				if ( is_array( $lists ) && count( $lists ) > 0 ) {
					if ( isset( $messenger_rules['message'][ $i ] ) ) {
						$this->messenger_content( $lists, $messenger_rules['message'][ $i ] );
					}
				}
			}
		}
	}

	public function messenger_content( $lists, $message ) {
		$user_token = $this->settings['user_token'];
		$page_id    = $this->settings['page_id'];

		if ( ! $user_token || ! $page_id ) {
			return;
		}

		if ( ! $this->page_access_token ) {
			$page_access_token       = $this->fb_api->Get_Access_Token_Page( $user_token, $page_id );
			$this->page_access_token = $page_token = $page_access_token['access_token'];
		} else {
			$page_token = $this->page_access_token;
		}

		if ( ! $page_token ) {
			return;
		}

		foreach ( $lists as $item ) {
			$user_ref = $item->meta_value ? $item->meta_value : $item->user_ref;

			if ( ! empty( $item->user_id ) && ! empty( $user_ref ) ) {

				$array_product = array();

				$cart          = json_decode( $item->abandoned_cart_info );
				$sent_email_id = uniqid() . $item->id;
				$checkout_url  = $this->link_checkout( $item->id, $sent_email_id );

				foreach ( $cart->cart as $pd ) {
					$pid     = $pd->variation_id ? $pd->variation_id : $pd->product_id;
					$product = wc_get_product( $pid );
					if ( $product ) {
						$button_view_url_product = str_replace( 'http:', 'https:', get_the_permalink( $pd->product_id ) );
						$pd_img                  = get_the_post_thumbnail_url( $pid, 'woocommerce_thumbnail' );
						$pd_img                  = $pd_img ? $pd_img : get_the_post_thumbnail_url( $pd->product_id, 'woocommerce_thumbnail' );

						$array_product[] = array(

							"title" => $product->get_name(),

							"subtitle" => $product->get_short_description(),

							"image_url" => str_replace( 'http:', 'https:', $pd_img ),

							"default_action" => array(
								"type"                 => "web_url",
								"url"                  => $button_view_url_product,
								"messenger_extensions" => true,
								"webview_height_ratio" => "tall",
								"fallback_url"         => $button_view_url_product
							),

							"buttons" => array(
								array(
									"type"  => "web_url",
									"url"   => str_replace( 'http:', 'https:', $checkout_url ),
									"title" => __( 'Checkout', 'woo-abandoned-cart-recovery' )
								)
							)
						);
					}
				}

				if ( count( $array_product ) > 0 ) {
					$send_text     = $this->fb_api->send_message_text_user_ref( $page_id, $page_token, $message, $user_ref );
					$send_abd_cart = $this->fb_api->send_message_abd_cart_user_ref( $page_id, $page_token, $user_ref, $array_product );
					if ( ! is_string( $send_abd_cart ) && ! empty( $send_abd_cart->asArray() || $send_text ) ) {
						$complete = $this->last_time ? '1' : null;
						$this->query->update_abd_cart_record(
							array( 'messenger_sent' => $item->messenger_sent + 1, 'messenger_complete' => $complete ),
							array( 'id' => $item->id )
						);
						$this->query->insert_email_history( 'messenger', $item->id, $sent_email_id );
					}
				}
			}
		}
	}

	public function link_checkout( $acr_id, $sent_email_id ) {

		$pass       = get_option( 'wacv_private_key' );
		$url_encode = Aes_Ctr::encrypt( $acr_id . '&' . $sent_email_id . '&0', $pass, 256 );

		return site_url( 'wacv?wacv_recover=cart_link&valid=' ) . $url_encode;
	}


}
