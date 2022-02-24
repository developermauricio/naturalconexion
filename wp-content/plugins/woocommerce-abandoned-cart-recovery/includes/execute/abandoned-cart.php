<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 14-03-19
 * Time: 4:26 PM
 */

namespace WACVP\Inc\Execute;

use WACVP\Inc\Data;
use WACVP\Inc\Functions;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Abandoned_Cart {

	protected static $instance = null;

	public $query;

	public $params;

	public $os_platform;

	public $browser;

	public $ip_add;

	public $data;


	public function __construct() {
		$this->query = Query_DB::get_instance();
		add_action( 'woocommerce_add_to_cart', array( $this, 'save_abandoned_cart' ), 999 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'save_abandoned_cart' ), 999 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'save_abandoned_cart' ), 999 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'save_abandoned_cart' ), 999 );
		add_action( 'woocommerce_calculate_totals', array( $this, 'save_abandoned_cart' ), 999 );

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'remove_abandoned_cart_after_success_order' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'remove_abandoned_cart_at_thank_you_page' ) );
		add_action( 'wp_login', array( $this, 'user_login' ), 10, 2 );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function user_login( $user_name, $user_info ) {
		if ( ! wc()->session ) {
			return;
		}

		$u_id        = $user_info->ID;
		$g_id        = WC()->session->get( 'user_id' );
		$session_key = WC()->session->get_customer_id();

		if ( $g_id ) {
			$this->query->update_abd_cart_record(
				array( 'user_id' => $u_id, 'user_type' => 'member' ),
				array( 'user_id' => $g_id )
			);
			$this->query->update_cart_log(
				array( 'user_id' => $u_id ),
				array( 'user_id' => $g_id )
			);

			$user_ref = $this->query->get_user_ref( $g_id );
			$user_ref = current( $user_ref );
			if ( $user_ref ) {
				update_user_meta( $u_id, 'wacv_user_ref', $user_ref );
			}
		} else {
			$abc_id = wc()->session->get( 'wacv_cart_record_id' );
			$this->query->update_abd_cart_record(
				array( 'user_id' => $u_id, 'user_type' => 'member' ),
				array( 'id' => $abc_id )
			);
			$this->query->update_cart_log(
				array( 'user_id' => $u_id ),
				array( 'user_id' => $session_key )
			);
		}
	}

	public function save_abandoned_cart() {
		if ( Functions::is_bot() || ( is_admin() && ! is_ajax() ) || current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$this->data        = Data::get_instance();
		$this->params      = $this->data::get_params();
		$this->os_platform = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$this->ip_add      = \WC_Geolocation::get_ip_address();

		if ( is_user_logged_in() ) {
			if ( $this->params['tracking_member'] ) {
				$this->tracking_abandoned_cart( 'member' );
			}
		} else {
			if ( $this->params['tracking_guest'] ) {
				$this->tracking_abandoned_cart( 'guest' );
			}
		}
	}

	public function tracking_abandoned_cart( $type ) {
		if ( '/favicon.ico' === $_SERVER['REQUEST_URI'] || isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'heartbeat' || empty( $_REQUEST ) ) {
			return;
		}

		$check_insert = false;

		if ( ! wc()->session ) {
			return;
		}

		$recover_id = wc()->session->get( 'wacv_recover_id' );
		$cart_count = wc()->cart->get_cart_contents_count();
		$abdc_data  = $cart_count ? json_encode( array( 'cart' => wc()->session->cart, 'currency' => get_woocommerce_currency() ) ) : '';

		if ( $recover_id ) {
			$result = $this->query->get_abdc_record_by_recover_id( $recover_id ); //select record with cart_ignore = 0
			if ( ! empty( $result ) ) {
				$old_abd_cart = $result->abandoned_cart_info;
				$compare      = $this->compare_cart( $old_abd_cart, $abdc_data );
				if ( ! $compare ) {
					$this->query->update_abd_cart_record( array( 'cart_ignored' => '1' ), array( 'id' => $recover_id ) );
				} else {
					return;
				}
			}
		}

		$user_id = $type === 'member' ? get_current_user_id() : wc()->session->get( 'user_id' );
		$user_id = $user_id ? $user_id : 0;

		$check_unsub = '';
		if ( $type == 'member' ) {
			$check_unsub = get_user_meta( $user_id, 'wacv_unsubscribe', true );
		} else {
			$user = $this->query->get_guest_info( $user_id );

			if ( $user ) {
				$check_unsub = $user->status == 'unsubscribe' ? true : false;
			}
		}
		if ( $check_unsub ) {
			return;
		}

		$abdc_id = wc()->session->get( 'wacv_cart_record_id' );
		$debug   = [ 'session_key' => wc()->session->get_customer_id() ];
		if ( $abdc_id ) {
			$result = $this->query->get_abdc_record( $abdc_id ); //select record with cart_ignore = 0
			if ( $result ) {
				if ( $abdc_data ) {
					$cut_time = 'member' == $type ? $this->data->member_cut_time() : $this->data->guest_cut_time();
					if ( $cut_time > $result->abandoned_cart_time ) { //out time
						$this->query->remove_abd_record( $abdc_id );
						$check_insert = true;
					} else {  //in time
						$this->query->update_abd_cart_record(
							array(
								'abandoned_cart_info' => $abdc_data,
								'abandoned_cart_time' => WACVP_CURRENT_TIME,
								'user_id'             => $user_id,
								'user_type'           => $type,
								'browser'             => json_encode( $_REQUEST )
							),
							array( 'id' => $abdc_id ) );
					}
				} else {
					$this->query->remove_abd_record( $abdc_id );
				}
			} else {
				$check_insert = true;
			}
		} else {
			$check_insert = true;
		}
		if ( $check_insert && $abdc_data ) {
			if ( $user_id ) {
				$this->query->remove_abd_record_by_user_id( $user_id );
			}
			$insert_id = $this->query->insert_abd_cart_record( array(
				'user_id'             => $user_id,
				'abandoned_cart_info' => $abdc_data,
				'abandoned_cart_time' => WACVP_CURRENT_TIME,
				'user_type'           => $type,
				'customer_ip'         => $this->ip_add,
				'os_platform'         => $this->os_platform,
				'browser'             => json_encode( $_REQUEST )
			) );

			wc()->session->set( 'wacv_cart_record_id', $insert_id );
			$current_list   = (array) wc()->session->get( 'wacv_cart_record_ids_list' );
			$current_list[] = $insert_id;
			wc()->session->set( 'wacv_cart_record_ids_list', $current_list );
		}
	}

	public function remove_abandoned_cart_at_thank_you_page( $order_id ) {
		$this->remove_abandoned_cart_when_cart_is_purchase( $order_id );
	}

	public function remove_abandoned_cart_when_cart_is_purchase( $order_id ) {
		if ( ! wc()->session ) {
			return;
		}

		$id = $this->query::get_session( 'wacv_cart_record_id' );
		if ( $id ) {
			$this->query->remove_abd_record( $id );
			wc()->session->__unset( 'wacv_cart_record_id' );
		}

		$ids = wc()->session->get( 'wacv_cart_record_ids_list' );
		if ( ! empty( $ids ) ) {
			$this->query->remove_mutlti_abd_record( $ids );
			wc()->session->__unset( 'wacv_cart_record_ids_list' );
		}

		$ip = get_post_meta( $order_id, '_customer_ip_address', true );
		if ( $ip ) {
			$this->query->remove_abd_record_via_ip( $ip );
		}
	}

	public function remove_abandoned_cart_after_success_order( $order_id ) {


		// Mark recover
		$order_type     = ! empty( $this->query::get_session( 'wacv_order_type' ) ) ? 1 : 0;
		$recovered_time = $order_type ? current_time( 'timestamp' ) : null;
		$recovered_id   = $order_type ? $this->query::get_session( 'wacv_recover_id' ) : null;

		if ( $order_type ) {
			$cart_count = wc()->cart->get_cart_contents_count();
			$abdc_data  = $cart_count ? json_encode( array( 'cart' => wc()->session->cart ) ) : '';
			$this->query->update_abd_cart_record(
				array(
					'abandoned_cart_info' => $abdc_data,
					'order_type'          => $order_type,
					'recovered_cart_time' => $recovered_time,
					'recovered_cart'      => $order_id
				),
				array( 'id' => $recovered_id ) );
			wc()->session->__unset( 'wacv_order_type' );
		}

		//Remove record if order success
		$this->remove_abandoned_cart_when_cart_is_purchase( $order_id );

		//Send to admin
		if ( $order_type == 1 && $this->params['email_to_admin_when_cart_recover'] ) {
			$headers [] = "Content-Type: text/html";

			$subject = __( 'Have a recovered order from abandoned cart', 'woo-abandoned-cart-recovery' );

			$message = 'You’ve received a recovered order from abandoned cart. Click here to view detail <a href="' . admin_url( "post.php?post=$order_id&action=edit" ) . '">Order#' . $order_id . '</a>';

			wp_mail( get_option( 'admin_email' ), $subject, $message, $headers );
		}

//		$this->query::set_session( 'wacv_order_processed', true );

		if ( $temp_id = $this->query::get_session( 'wacv_temp_id' ) ) {
			$curr_used = get_post_meta( $temp_id, 'wacv_template_used', true );
			update_post_meta( $temp_id, 'wacv_template_used', intval( $curr_used ) + 1 );
		}
	}

	public function compare_cart( $cart_1, $cart_2 ) {
		$cart_1 = ! is_array( $cart_1 ) ? json_decode( $cart_1 )->cart : $cart_1;
		$cart_2 = ! is_array( $cart_2 ) ? json_decode( $cart_2 )->cart : $cart_2;

		if ( count( (array) $cart_1 ) != count( (array) $cart_2 ) ) {
			return false;
		}

		if ( is_object( $cart_1 ) && is_object( $cart_2 ) ) {
			$temp_1 = $temp_2 = array();
			foreach ( $cart_1 as $item ) {
				$product            = $item->variation_id ? $item->variation_id : $item->product_id;
				$qty                = $item->quantity;
				$temp_1[ $product ] = $qty;
			}
			foreach ( $cart_2 as $item ) {
				$product            = $item->variation_id ? $item->variation_id : $item->product_id;
				$qty                = $item->quantity;
				$temp_2[ $product ] = $qty;
			}

			if ( $temp_1 == $temp_2 ) {
				return true;
			}
		}

		return false;
	}

}