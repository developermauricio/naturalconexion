<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 09-05-19
 * Time: 2:45 PM
 */

namespace WACVP\Inc\Execute;

use WACVP\Inc\Data;
use WACVP\Inc\Functions;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cart_Logs {

	protected static $instance = null;

	public $params;
	public $ip_add;
	public $os_platform;
	public $query;

	public function __construct() {
		$settings = Data::get_params();
		if ( ! $settings['enable_cart_log'] || Functions::is_bot() ) {
			return;
		}

		$this->os_platform = $_SERVER['HTTP_USER_AGENT'] ?? '';

		add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart_log' ), 10, 4 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_item_log' ), 10, 2 );
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'cart_update_zero' ), 10, 2 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'cart_update' ), 10, 4 );

		$this->query = Query_DB::get_instance();
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function add_to_cart_log( $cart_item_key, $product_id, $quantity, $variation_id ) {
		$item_id = $variation_id ? $variation_id : $product_id;
		$this->handle_cart_log( $item_id, $quantity, 'add_item' );

	}

	public function handle_cart_log( $product_id, $quantity, $action ) {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->ip_add = \WC_Geolocation::get_ip_address();

		if ( is_user_logged_in() ) {
			$user_id   = get_current_user_id();
			$test_user = 'logged';
		} else {
			$user_id   = ! empty( $this->query::get_session( 'user_id' ) ) ? $this->query::get_session( 'user_id' ) : WC()->session->get_customer_id();
			$test_user = 'no_logged';
		}

		$timestamp    = current_time( 'timestamp' );
		$begin_of_day = strtotime( "midnight", $timestamp );
		$pre_action   = $this->query->select_cart_log_record( $user_id, $begin_of_day );

		if ( empty( $pre_action ) ) {
			$data  = serialize( array(
				array(
					'product_id' => $product_id,
					'quantity'   => $quantity,
					'action'     => $action,
					'time'       => $timestamp
				)
			) );
			$input = array(
				'user_id'     => $user_id,
				'data'        => $data,
				'time_log'    => current_time( 'timestamp' ),
				'ip'          => $this->ip_add,
				'os_platform' => $this->os_platform,
			);
			$this->query->insert_cart_log( $input );
			if ( $user_id == 1 ) {
				update_option( 'test_log_1', $test_user );
			}
		} else {
			$el       = end( $pre_action );
			$data     = $el->data ? unserialize( $el->data ) : array();
			$new_data = array(
				'product_id' => $product_id,
				'quantity'   => $quantity,
				'action'     => $action,
				'time'       => $timestamp
			);
			array_push( $data, $new_data );
			$input = array(
				'data'        => serialize( $data ),
				'time_log'    => current_time( 'timestamp' ),
				'ip'          => $this->ip_add,
				'os_platform' => $this->os_platform,
			);
			$this->query->update_cart_log( $input, array( 'user_id' => $user_id, 'id' => $el->id ) );
			if ( $user_id == 1 ) {
				update_option( 'test_log_2', $test_user );
			}
		}
	}

	public function cart_update_zero( $cart_item_key, $obj ) {

		$cart    = $obj->cart_contents[ $cart_item_key ];
		$item_id = $cart['variation_id'] == 0 ? $cart['product_id'] : $cart['variation_id'];
		$qty     = $cart['quantity'];
		if ( ! $qty ) {
			return;
		}
		$this->handle_cart_log( $item_id, $qty, 'remove_item' );
	}

	public function cart_update( $cart_item_key, $quantity, $old_quantity, $obj ) {
		remove_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart_log' ) );

		$cart         = $obj->cart_contents;
		$item_id      = $cart[ $cart_item_key ]['variation_id'] == 0 ? $cart[ $cart_item_key ]['product_id'] : $cart[ $cart_item_key ]['variation_id'];
		$new_quantity = abs( $quantity - $old_quantity );
		if ( ! $new_quantity ) {
			return;
		}

		$action = $quantity - $old_quantity > 0 ? 'add_item' : 'remove_item';

		$this->handle_cart_log( $item_id, $new_quantity, $action );
	}

	public function remove_item_log( $cart_item_key, $cart ) {
		$remove_data = $cart->removed_cart_contents[ $cart_item_key ];
		$product_id  = $remove_data['variation_id'] == 0 ? $remove_data['product_id'] : $remove_data['variation_id'];
		$quantity    = $remove_data['quantity'];

		$this->handle_cart_log( $product_id, $quantity, 'remove_item' );
	}


}