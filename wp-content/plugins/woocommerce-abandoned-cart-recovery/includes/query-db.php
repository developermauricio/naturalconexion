<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 18-03-19
 * Time: 9:30 AM
 */


namespace WACVP\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Query_DB {

	protected static $instance = null;

	public $cart_record_tb;

	public $guest_info_tb;

	public $email_history_tb;

	public $cart_log_tb;

	public $cart_meta;

	public $params;
	public $wpdb;

	private function __construct() {
		global $wpdb;
		$this->wpdb             = $wpdb;
		$this->cart_record_tb   = $wpdb->prefix . "wacv_abandoned_cart_record";
		$this->guest_info_tb    = $wpdb->prefix . "wacv_guest_info_record";
		$this->email_history_tb = $wpdb->prefix . "wacv_email_history";
		$this->cart_log_tb      = $wpdb->prefix . "wacv_cart_log";
		$this->params           = Data::get_params();
	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	protected $format = array(
		'user_id'             => '%d',
		'abandoned_cart_info' => '%s',
		'abandoned_cart_time' => '%s',
		'cart_ignored'        => '%s',
		'recovered_cart'      => '%d',
		'recovered_cart_time' => '%d',
		'user_type'           => '%s',
		'unsubscribe_link'    => '%s',
		'session_id'          => '%s',
		'order_type'          => '%d',
		'send_mail_time'      => '%d',
		'number_of_mailing'   => '%d',
		'email_complete'      => '%s',
		'sms_complete'        => '%s',
		'messenger_complete'  => '%s',
		'customer_ip'         => '%s',
		'os_platform'         => '%s',
		'browser'             => '%s',
	);

	public static function set_session( $key, $value ) {
		WC()->session->set( $key, $value );
	}

	public static function get_session( $key ) {
		return WC()->session ? WC()->session->get( $key ) : '';
	}

	// Query with abandoned cart record table

	public function update_abd_cart_record( $data = array(), $where = array() ) {
		global $wpdb;

		$data_fm = $where_fm = array();

		foreach ( $data as $item ) {
			if ( isset( $this->format[ $item ] ) ) {
				$data_fm[] = $this->format[ $item ];
			}
		}

		foreach ( $where as $item ) {
			if ( isset( $this->format[ $item ] ) ) {
				$where_fm[] = $this->format[ $item ];
			}
		}

		return $wpdb->update( $this->cart_record_tb, $data, $where, $data_fm, $where_fm );
	}

	public function insert_abd_cart_record( $data = array() ) {
		global $wpdb;

		$data_fm = $where_fm = array();

		foreach ( $data as $item ) {
			if ( isset( $format[ $item ] ) ) {
				$data_fm[] = $this->format[ $item ];
			}
		}

		$wpdb->insert( $this->cart_record_tb, $data, $data_fm );

		return $wpdb->insert_id;
	}


	public function get_abd_cart_records( $user_id, $user_type, $recovered_cart = 0, $cart_ignored = '0' ) {
		global $wpdb;
		$check_id = '';

		$query = "SELECT * FROM {$this->cart_record_tb} WHERE user_id = %d AND cart_ignored = %s AND recovered_cart = %d AND user_id != 0 AND user_type = %s {$check_id}";

		return $wpdb->get_results( $wpdb->prepare( $query, $user_id, $cart_ignored, $recovered_cart, $user_type ) );
	}

	public function get_abdc_record( $abdc_id ) {
		global $wpdb;
		$query = "SELECT * FROM {$this->cart_record_tb} WHERE id = %s";

		return $wpdb->get_row( $wpdb->prepare( $query, $abdc_id ) );
	}

	public function get_abdc_record_by_recover_id( $abdc_id ) {
		global $wpdb;
		$query = "SELECT * FROM {$this->cart_record_tb} WHERE id = %s AND cart_ignored = %s";

		return $wpdb->get_row( $wpdb->prepare( $query, $abdc_id, '0' ) );
	}

	public function get_abd_guest_cart_record_like_session_id( $session_id, $recovered_cart = 0, $cart_ignored = 0 ) {
		global $wpdb;
		$query = "SELECT * FROM {$this->cart_record_tb} WHERE session_id LIKE %s AND cart_ignored = %s AND recovered_cart = %d ";

		return $wpdb->get_results( $wpdb->prepare( $query, $session_id, $cart_ignored, $recovered_cart ) );
	}

	public function get_guest_same_email( $id, $cart_ignored = 0, $recovered_cart = 0 ) {
		global $wpdb;
		$query = "SELECT * FROM {$this->cart_record_tb} WHERE user_id= %d AND cart_ignored = %s AND recovered_cart = %d ";

		return $wpdb->get_results( $wpdb->prepare( $query, $id, $cart_ignored, $recovered_cart ) );
	}

	// Query with guest info record table

	public function get_guest_info_rows( $billing_email ) {
		global $wpdb;
		$query = "SELECT id FROM {$this->guest_info_tb} WHERE billing_email = %s";

		return $wpdb->get_results( $wpdb->prepare( $query, $billing_email ) );
	}

	public function insert_guest_info( $guest_info = array() ) {
		global $wpdb;
		$wpdb->insert( $this->guest_info_tb,
			array(
				'input_date'          => current_time( 'U' ),
				'user_ref'            => isset( $guest_info['user_ref'] ) ? $guest_info['user_ref'] : '',
				'billing_first_name'  => isset( $guest_info['billing_first_name'] ) ? $guest_info['billing_first_name'] : '',
				'billing_last_name'   => isset( $guest_info['billing_last_name'] ) ? $guest_info['billing_last_name'] : '',
				'billing_email'       => isset( $guest_info['billing_email'] ) ? $guest_info['billing_email'] : '',
				'billing_postcode'    => isset( $guest_info['billing_postcode'] ) ? $guest_info['billing_postcode'] : '',
				'billing_company'     => isset( $guest_info['billing_company'] ) ? $guest_info['billing_company'] : '',
				'billing_address_1'   => isset( $guest_info['billing_address_1'] ) ? $guest_info['billing_address_1'] : '',
				'billing_address_2'   => isset( $guest_info['billing_address_2'] ) ? $guest_info['billing_address_2'] : '',
				'billing_city'        => isset( $guest_info['billing_city'] ) ? $guest_info['billing_city'] : '',
				'billing_country'     => isset( $guest_info['billing_country'] ) ? $guest_info['billing_country'] : '',
				'billing_phone'       => isset( $guest_info['billing_phone'] ) ? $guest_info['billing_phone'] : '',
				'ship_to_billing'     => '',
				'shipping_first_name' => isset( $guest_info['shipping_first_name'] ) ? $guest_info['shipping_first_name'] : '',
				'shipping_last_name'  => isset( $guest_info['shipping_last_name'] ) ? $guest_info['shipping_last_name'] : '',
				'shipping_company'    => isset( $guest_info['shipping_company'] ) ? $guest_info['shipping_company'] : '',
				'shipping_address_1'  => isset( $guest_info['shipping_address_1'] ) ? $guest_info['shipping_address_1'] : '',
				'shipping_address_2'  => isset( $guest_info['shipping_address_2'] ) ? $guest_info['shipping_address_2'] : '',
				'shipping_city'       => isset( $guest_info['shipping_city'] ) ? $guest_info['shipping_city'] : '',
				'shipping_country'    => isset( $guest_info['shipping_country'] ) ? $guest_info['shipping_country'] : '',
				'shipping_postcode'   => isset( $guest_info['shipping_postcode'] ) ? $guest_info['shipping_postcode'] : '',
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			)
		);

		return $wpdb->insert_id;
	}


	public function update_guest_info( $user_id, $guest_info = array() ) {
		global $wpdb;
		$input   = $format = array();
		$result  = '';
		$pattern = array(
			'user_ref'            => '%s',
			'billing_first_name'  => '%s',
			'billing_last_name'   => '%s',
			'billing_email'       => '%s',
			'billing_postcode'    => '%d',
			'billing_company'     => '%s',
			'billing_address_1'   => '%s',
			'billing_address_2'   => '%s',
			'billing_city'        => '%s',
			'billing_country'     => '%s',
			'billing_phone'       => '%d',
			'shipping_first_name' => '%s',
			'shipping_last_name'  => '%s',
			'shipping_company'    => '%s',
			'shipping_address_1'  => '%s',
			'shipping_address_2'  => '%s',
			'shipping_city'       => '%s',
			'shipping_country'    => '%s',
			'shipping_postcode'   => '%d',
			'status'              => '%s',
		);

		foreach ( $pattern as $key => $value ) {
			if ( ! empty( $guest_info[ $key ] ) ) {
				$input[ $key ] = $guest_info[ $key ];
				$format[]      = $value;
			} else {
				continue;
			}
		}

		if ( ! empty( $input ) ) {
			$result = $wpdb->update( $this->guest_info_tb,
				$input,
				array( 'id' => $user_id ),
				$format,
				array( '%d' )
			);
		}

		return $result;
	}


	//Compare cart info
	public function compare_guest_cart_info( $new_cart, $abandoned_cart ) {

		$new_cart_arr       = json_decode( $new_cart, true );
		$abandoned_cart_arr = json_decode( $abandoned_cart, true );

		return $this->compare_carts( $new_cart_arr, $abandoned_cart_arr );

	}

	public function compare_cart_info( $user_id, $abandoned_cart ) {

		$current_woo_cart   = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
		$abandoned_cart_arr = json_decode( $abandoned_cart, true );

		return $this->compare_carts( $current_woo_cart, $abandoned_cart_arr );
	}

	public function compare_carts( $first_cart, $second_cart ) {

		if ( isset( $first_cart['cart'] ) && isset( $second_cart['cart'] ) ) {

//			if ( count( $first_cart['cart'] ) < 0 || count( $second_cart['cart'] ) < 0 ) {
			if ( count( $first_cart['cart'] ) < count( $second_cart['cart'] ) ) {
				$temp        = $first_cart['cart'];
				$first_cart  = $second_cart['cart'];
				$second_cart = $temp;
			}

			if ( is_array( $first_cart ) && is_array( $second_cart ) ) {
				foreach ( $first_cart as $key => $items_info ) {
					foreach ( $items_info as $item_key => $value ) {

						$first_product_id   = $value['product_id'];
						$first_variation_id = $value['variation_id'];
						$first_quantity     = $value['quantity'];

						$second_product_id   = isset( $second_cart[ $key ][ $item_key ]['product_id'] ) ? $second_cart[ $key ][ $item_key ]['product_id'] : '';
						$second_variation_id = isset( $second_cart[ $key ][ $item_key ]['variation_id'] ) ? $second_cart[ $key ][ $item_key ]['variation_id'] : '';
						$second_quantity     = isset( $second_cart[ $key ][ $item_key ]['quantity'] ) ? $second_cart[ $key ][ $item_key ]['quantity'] : '';

						if ( $first_product_id != $second_product_id || $first_variation_id != $second_variation_id || $first_quantity != $second_quantity ) {
							return false;
						}
					}

				}
			}
//			} else {
//				return false;
//			}
		}

		return true;
	}

//Recover list

	public function get_recover_list( $option ) {
		global $wpdb;
//		$compare_time_member = Data::get_instance()->member_compare_cut_off_time();
//		$compare_time_guest  = Data::get_instance()->guest_compare_cut_off_time();
		$que = '';
		switch ( $option ) {
			case 'all_customer':
				$que = "";
				break;
			case 'member':
				$que = "AND user_type='member'";
				break;
			case 'guest':
				$que = "AND user_type='guest'";
				break;
		}

		$query = "SELECT acr.* , wpu.user_login, wpu.user_email FROM {$this->cart_record_tb} AS acr LEFT JOIN {$wpdb->users} AS wpu ON acr.user_id = wpu.id ";
		$query .= "WHERE acr.recovered_cart!='0' AND acr.cart_ignored='1' AND order_type = '1' ";
		$query .= " {$que} ORDER BY acr.abandoned_cart_time DESC";

		return $wpdb->get_results( $query );
	}

	//Email reminder
	public function get_abd_list( $option, $start, $end, $limit, $offset ) {
		global $wpdb;
		$compare_time_member = Data::get_instance()->member_compare_cut_off_time();
		$compare_time_guest  = Data::get_instance()->guest_compare_cut_off_time();
		$que                 = $sub_query = '';
		switch ( $option ) {
			case 'all_customer':
				$que = "AND (({$compare_time_member} > abandoned_cart_time AND user_type='member') OR ({$compare_time_guest} > abandoned_cart_time AND user_type='guest'))";
				break;
			case 'member':
				$que       = "AND (({$compare_time_member} > abandoned_cart_time AND user_type='member') OR ({$compare_time_guest} > abandoned_cart_time AND user_type='guest'))";
				$sub_query = "AND acr.user_id!=0";
				break;
			case 'guest':
				$que = "AND {$compare_time_guest} > abandoned_cart_time AND user_type='guest' AND acr.user_id=0";
				break;
		}

		$query = "SELECT acr.* , wpu.user_login, wpu.user_email, wpi.user_ref, wpi.billing_email, wpi.billing_first_name, wpi.billing_last_name , wpi.billing_phone , wpi.billing_country ";
		$query .= "FROM {$this->cart_record_tb} AS acr LEFT JOIN {$wpdb->users} AS wpu ON acr.user_id = wpu.id ";
		$query .= "LEFT JOIN  {$wpdb->prefix}wacv_guest_info_record AS wpi ON acr.user_id = wpi.id ";
		$query .= "WHERE ((acr.recovered_cart='0' AND acr.cart_ignored='0') OR (acr.recovered_cart_time!='0' AND acr.order_type='1')) ";
		$query .= "AND acr.abandoned_cart_info NOT LIKE '\"\"' AND acr.abandoned_cart_info NOT LIKE '[]' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":[]}' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":null}' ";
		$query .= "AND acr.abandoned_cart_time >{$start} AND acr.abandoned_cart_time <{$end} ";
		$query .= " {$que} {$sub_query}  ORDER BY acr.abandoned_cart_time DESC LIMIT {$limit} OFFSET {$offset}";

		return $wpdb->get_results( $query );
	}

	public function count_abd_items( $option, $start, $end ) {
		global $wpdb;
		$compare_time_member = Data::get_instance()->member_compare_cut_off_time();
		$compare_time_guest  = Data::get_instance()->guest_compare_cut_off_time();
		$que                 = $sub_query = '';
		switch ( $option ) {
			case 'all_customer':
				$que = "AND (({$compare_time_member} > abandoned_cart_time AND user_type='member') OR ({$compare_time_guest} > abandoned_cart_time AND user_type='guest'))";
				break;
			case 'member':
				$que       = "AND (({$compare_time_member} > abandoned_cart_time AND user_type='member') OR ({$compare_time_guest} > abandoned_cart_time AND user_type='guest'))";
				$sub_query = "AND acr.user_id!=0";
				break;
			case 'guest':
				$que = "AND {$compare_time_guest} > abandoned_cart_time AND user_type='guest' AND acr.user_id=0";
				break;
		}

		$query = "SELECT COUNT(acr.id)  ";
		$query .= "FROM {$this->cart_record_tb} AS acr LEFT JOIN {$wpdb->users} AS wpu ON acr.user_id = wpu.id ";
		$query .= "LEFT JOIN  {$wpdb->prefix}wacv_guest_info_record AS wpi ON acr.user_id = wpi.id ";
		$query .= "WHERE ((acr.recovered_cart='0' AND acr.cart_ignored='0') OR (acr.recovered_cart='0' AND acr.order_type='1')) ";
		$query .= "AND acr.abandoned_cart_info NOT LIKE '\"\"' AND acr.abandoned_cart_info NOT LIKE '[]' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":[]}' ";
		$query .= "AND acr.abandoned_cart_time >{$start} AND acr.abandoned_cart_time <{$end} ";
		$query .= " {$que} {$sub_query}  ORDER BY acr.abandoned_cart_time DESC";

		return $wpdb->get_var( $query );
	}


	public function get_abd_cart_by_id( $id ) {
		global $wpdb;
		$query = "SELECT acr.*, wpu.user_login, wpu.user_email FROM {$this->cart_record_tb} AS acr LEFT JOIN {$wpdb->users} AS wpu ON acr.user_id = wpu.id ";
		$query .= "WHERE acr.id={$id} AND acr.abandoned_cart_info NOT LIKE '\"\"' AND acr.abandoned_cart_info NOT LIKE '[]' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":[]}' ";
		$query .= "AND acr.recovered_cart='0' AND acr.cart_ignored='0' AND acr.user_id !=0 AND acr.unsubscribe_link='0' ";

		return $wpdb->get_row( $query, OBJECT );
	}

	public function get_list_email_to_send( $time, $number_of_mailing ) {
		if ( ! $this->params['send_email_to_member'] && ! $this->params['send_email_to_guest'] ) {
			return;
		}

		global $wpdb;
		$compare_member_time = $time - $this->params['member_cut_off_time'] * 60;
		$compare_guest_time  = $time - $this->params['guest_cut_off_time'] * 60;

		$user_query = '';
		switch ( true ) {
			case $this->params['send_email_to_member'] && ! $this->params['send_email_to_guest']:
				$user_query = " AND (abandoned_cart_time <$compare_member_time AND user_type = 'member')";
				break;
			case $this->params['send_email_to_guest'] && ! $this->params['send_email_to_member'] :
				$user_query = " AND (abandoned_cart_time <$compare_guest_time AND user_type = 'guest')";
				break;
			case $this->params['send_email_to_member'] && $this->params['send_email_to_guest']:
				$user_query = " AND ((abandoned_cart_time <$compare_member_time AND user_type = 'member') OR (abandoned_cart_time < $compare_guest_time AND user_type = 'guest'))";
				break;
		}
		$number_of_mailing = intval( $number_of_mailing ) - 1;
		$exclude_user      = ! empty( $this->params['tracking_user_exclude'] ) ? "AND user_id NOT IN (" . implode( ',', $this->params['tracking_user_exclude'] ) . ")" : '';

		$query = "SELECT acr.* , wpu.user_login, wpu.user_email FROM {$this->cart_record_tb} AS acr LEFT JOIN {$wpdb->users} AS wpu ON acr.user_id = wpu.id ";
		$query .= "WHERE acr.abandoned_cart_info NOT LIKE '\"\"' AND acr.abandoned_cart_info NOT LIKE '[]' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":[]}' ";
		$query .= "AND number_of_mailing = {$number_of_mailing} AND acr.recovered_cart='0' AND acr.cart_ignored='0' AND acr.user_id !=0 AND acr.unsubscribe_link='0' "; //
		$query .= "AND acr.email_complete is null {$user_query} {$exclude_user} ORDER BY acr.id DESC";

		return ( $wpdb->get_results( $query ) );
	}


	public function get_list_message_to_send( $time, $sent_time ) {
		global $wpdb;
		$compare_member_time = $time - $this->params['member_cut_off_time'] * 60;
		$compare_guest_time  = $time - $this->params['guest_cut_off_time'] * 60;

		$both_true    = " AND ((abandoned_cart_time <$compare_member_time AND user_type = 'member') OR (abandoned_cart_time <$compare_guest_time AND user_type = 'guest'))";
		$sent_time    = intval( $sent_time ) - 1;
		$exclude_user = ! empty( $this->params['tracking_user_exclude'] ) ? "AND user_id NOT IN (" . implode( ',', $this->params['tracking_user_exclude'] ) . ")" : '';

		$query = "SELECT acr.* , wpu.user_login, wpum.meta_value, wpi.user_ref FROM {$this->cart_record_tb} AS acr LEFT JOIN {$wpdb->prefix}usermeta AS wpum ON acr.user_id = wpum.user_id AND wpum.meta_key='wacv_user_ref' ";
		$query .= "LEFT JOIN {$wpdb->prefix}wacv_guest_info_record AS wpi ON acr.user_id = wpi.id ";
		$query .= "LEFT JOIN {$wpdb->users} AS wpu ON acr.user_id = wpu.id ";
		$query .= "WHERE acr.abandoned_cart_info NOT LIKE '\"\"' AND acr.abandoned_cart_info NOT LIKE '[]' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":[]}' ";
		$query .= "AND acr.messenger_sent = {$sent_time} AND acr.recovered_cart='0' AND acr.cart_ignored='0' AND acr.user_id !=0 AND acr.unsubscribe_link='0'";
		$query .= "AND acr.messenger_complete is null {$both_true} ORDER BY acr.id DESC";

		return ( $wpdb->get_results( $query ) );
	}

	public function get_list_sms_to_send( $time, $sent_time ) {
		global $wpdb;
		$compare_member_time = $time - $this->params['member_cut_off_time'] * 60;
		$compare_guest_time  = $time - $this->params['guest_cut_off_time'] * 60;

		$both_true    = " AND ((abandoned_cart_time <$compare_member_time AND user_type = 'member') OR (abandoned_cart_time <$compare_guest_time AND user_type = 'guest'))";
		$sent_time    = intval( $sent_time ) - 1;
		$exclude_user = ! empty( $this->params['tracking_user_exclude'] ) ? "AND user_id NOT IN (" . implode( ',', $this->params['tracking_user_exclude'] ) . ")" : '';

		$query = "SELECT acr.* , wpu.user_login, wpi.billing_phone , wpi.billing_country , wpi.billing_last_name , wpi.billing_first_name FROM {$this->cart_record_tb} AS acr ";
		$query .= "LEFT JOIN {$wpdb->prefix}wacv_guest_info_record AS wpi ON acr.user_id = wpi.id ";
		$query .= "LEFT JOIN {$wpdb->users} AS wpu ON acr.user_id = wpu.id ";
		$query .= "WHERE acr.abandoned_cart_info NOT LIKE '\"\"' AND acr.abandoned_cart_info NOT LIKE '[]' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":[]}' ";
		$query .= "AND acr.sms_sent = {$sent_time} AND acr.recovered_cart='0' AND acr.cart_ignored='0' AND acr.user_id !=0 AND acr.unsubscribe_link='0'  AND acr.valid_phone!=1 ";
		$query .= "AND sms_complete is null {$both_true} {$exclude_user} ORDER BY acr.id DESC";

		return ( $wpdb->get_results( $query ) );
	}


	public function get_abd_carts( $args = array() ) {
		global $wpdb;

		$arg_default = array(
			'acr_fields'    => 'id,user_id,user_type',
			'member_fields' => '',
			'guest_fields'  => 'billing_last_name, billing_first_name',
			'time'          => '',
			'times'         => '',
			'remind_type'   => '',
			'cart_ignored'  => ''
		);

		$args = wp_parse_args( $args, $arg_default );

		$join     = array();
		$where[]  = "acr.abandoned_cart_info NOT LIKE '\"\"' AND acr.abandoned_cart_info NOT LIKE '[]' AND acr.abandoned_cart_info NOT LIKE '{\"cart\":[]}'";
		$fields[] = $args['acr_fields'] ? 'acr.' . str_replace( ',', ', acr.', $args['acr_fields'] ) : 'acr.*';
		if ( $args['member_fields'] ) {
			$fields[] = 'member.' . str_replace( ',', ', member.', $args['member_fields'] );
			$join[]   = "LEFT JOIN {$wpdb->users} AS member ON acr.user_id = member.id LEFT JOIN {$wpdb->usermeta} AS u_meta ON acr.user_id = u_meta.user_id";
		}
		if ( $args['guest_fields'] ) {
			$fields[] = 'guest.' . str_replace( ',', ', guest.', $args['guest_fields'] );
			$join[]   = "LEFT JOIN {$wpdb->prefix}wacv_guest_info_record AS guest ON acr.user_id = guest.id ";
		}

		if ( $args['time'] ) {
			$compare_member_time = $args['time'] - $this->params['member_cut_off_time'] * 60;
			$compare_guest_time  = $args['time'] - $this->params['guest_cut_off_time'] * 60;
			if ( $args['member_fields'] && empty( $args['guest_fields'] ) ) {
				$where[] = "AND (acr.abandoned_cart_time <$compare_member_time AND acr.user_type = 'member')";
			} elseif ( $args['guest_fields'] && empty( $args['member_fields'] ) ) {
				$where[] = "AND (acr.abandoned_cart_time <$compare_guest_time AND acr.user_type = 'guest')";
			} elseif ( $args['member_fields'] && $args['guest_fields'] ) {
				$where[] = " AND ((acr.abandoned_cart_time <$compare_member_time AND acr.user_type = 'member') OR (acr.abandoned_cart_time <$compare_guest_time AND acr.user_type = 'guest'))";
			}
		}

		if ( $args['times'] && $args['remind_type'] ) {
			$times   = $args['times'] - 1;
			$where[] = "AND {$args['remind_type'] }={$times}";
			if ( in_array( $args['remind_type'], array( 'sms_sent' ) ) ) {
				$where[] = "AND acr.valid_phone!=1 AND (guest.billing_phone != '' OR (u_meta.meta_key='billing_phone' AND u_meta.meta_value!=''))";
			}
		}

		if ( in_array( $args['cart_ignored'], array( 0, 1 ) ) ) {
			$where[] = $args['cart_ignored'] == 0 ? "AND acr.cart_ignored='0'" : "AND acr.cart_ignored='1'";
		}

		$fields = ! empty( $fields ) ? implode( ',', $fields ) : '';
		$join   = ! empty( $join ) ? implode( ' ', $join ) : '';
		$where  = ! empty( $where ) ? implode( ' ', $where ) : '';

		$q = "select $fields from {$this->cart_record_tb} as acr $join where $where";

		return $wpdb->get_results( $q );
	}

	public function get_guest_info( $user_id ) {
		global $wpdb;
		$query_guest = "SELECT * FROM {$this->guest_info_tb} WHERE id = %d AND id != 0 ";

		return $wpdb->get_row( $wpdb->prepare( $query_guest, $user_id ) );
	}

	public function get_email_history( $acr_id ) {
		global $wpdb;
		$query = "SELECT * FROM {$this->email_history_tb} WHERE acr_id = %d ";

		return $wpdb->get_results( $wpdb->prepare( $query, $acr_id ) );
	}

	public function get_all_email_history() {
		global $wpdb;
		$query = "SELECT * FROM {$this->email_history_tb} WHERE id != %d";
		$arg   = '';

		return $wpdb->get_results( $wpdb->prepare( $query, 0 ) );
	}

	public function insert_email_history( $type, $acr_id, $sent_email_id, $template_id = '', $email = '', $coupon = '' ) {
		global $wpdb;

		$data    = array(
			'type'          => $type,
//				'billing_email' => $email,
			'template_id'   => $template_id,
			'acr_id'        => $acr_id,
			'sent_time'     => current_time( 'timestamp' ),
			'coupon'        => $coupon,
			'sent_email_id' => $sent_email_id
		);
		$data_fm = array( '%s', '%d', '%d', '%d', '%s', '%s' ); //'%s',

		$wpdb->insert( $this->email_history_tb, $data, $data_fm );

		return $wpdb->insert_id;
	}

	public function update_email_tracking( $sent_email_id, $type ) {
		global $wpdb;
		$type = 'clicked' || 'opened' ? $type : '';

		if ( ! $type ) {
			return;
		}

		$wpdb->update( $this->email_history_tb, array( $type => current_time( 'timestamp' ) ), array( 'sent_email_id' => $sent_email_id ), array( '%d' ), array( '%s' ) );
	}


	//Reports

	public function get_abd_cart_report( $from_time, $to_time ) {
		global $wpdb;

		$compare_time_member = Data::get_instance()->member_compare_cut_off_time();
		$compare_time_guest  = Data::get_instance()->guest_compare_cut_off_time();

		$query = "SELECT * FROM {$this->cart_record_tb} WHERE abandoned_cart_time >= %d AND abandoned_cart_time <= %d AND recovered_cart = 0 AND (cart_ignored = '0' OR order_type = '1' ) AND (({$compare_time_member} > abandoned_cart_time AND user_type='member') OR ({$compare_time_guest} > abandoned_cart_time AND user_type='guest')) ";

		return $wpdb->get_results( $wpdb->prepare( $query, $from_time, $to_time ) );
	}

	public function get_recovered_cart_report( $from_time, $to_time ) {
		global $wpdb;

		$compare_time_member = Data::get_instance()->member_compare_cut_off_time();
		$compare_time_guest  = Data::get_instance()->guest_compare_cut_off_time();

		$query = "SELECT * FROM {$this->cart_record_tb} WHERE abandoned_cart_time >= %d AND abandoned_cart_time <= %d AND recovered_cart != 0 AND order_type = '1'  AND (({$compare_time_member} > abandoned_cart_time AND user_type='member') OR ({$compare_time_guest} > abandoned_cart_time AND user_type='guest'))";

		return $wpdb->get_results( $wpdb->prepare( $query, $from_time, $to_time ) );
	}

	public function get_email_history_report( $from_time, $to_time, $type, $clicked = false ) {
		global $wpdb;

		$que_clicked = $clicked ? "AND clicked !=''" : "";

		$query = "SELECT COUNT(id) FROM {$this->email_history_tb} WHERE sent_time >= %d AND sent_time <= %d AND type =%s {$que_clicked} ";

		return $wpdb->get_var( $wpdb->prepare( $query, $from_time, $to_time, $type ) );
	}

	public function get_number_of_abd_product() {
		global $wpdb;

		$recovered_cart      = 0;
		$compare_time_member = Data::get_instance()->member_compare_cut_off_time();
		$compare_time_guest  = Data::get_instance()->guest_compare_cut_off_time();
		$query               = "SELECT abandoned_cart_info FROM {$this->cart_record_tb} WHERE recovered_cart = %d AND cart_ignored='0' AND ((user_type = 'member' AND {$compare_time_member}>= abandoned_cart_time) OR (user_type = 'guest' AND {$compare_time_guest}>= abandoned_cart_time)) AND abandoned_cart_info NOT LIKE '{\"cart\":[]}' AND abandoned_cart_info NOT LIKE '\"\"' AND abandoned_cart_info NOT LIKE '[]'";

		return $wpdb->get_results( $wpdb->prepare( $query, $recovered_cart ) );
	}

	public function get_number_of_rcv_product() {
		global $wpdb;
		$recovered_cart = 0;
		$query          = "SELECT recovered_cart FROM {$this->cart_record_tb} WHERE recovered_cart != %d AND order_type = '1' ";

		return $wpdb->get_results( $wpdb->prepare( $query, $recovered_cart ) );
	}

	public function count_template( $template_id ) {
		global $wpdb;
		$query = "SELECT COUNT(id) FROM {$this->email_history_tb} WHERE template_id = %d";

		return $wpdb->get_var( $wpdb->prepare( $query, $template_id ) );
	}


//Cart Log
	public function cart_log_record( $start = '', $end = '' ) {
		global $wpdb;

		$query = "SELECT clg.* , wpu.user_login, wpu.user_email, gi.billing_email, gi.billing_first_name, gi.billing_last_name FROM {$this->cart_log_tb} AS clg ";
		$query .= "LEFT JOIN {$wpdb->users} AS wpu ON clg.user_id = wpu.id LEFT JOIN {$this->guest_info_tb} AS gi ON clg.user_id = gi.id AND clg.user_id!=0 ";
		$query .= " WHERE clg.time_log > %d AND clg.time_log < %d ORDER BY id DESC";

		return $wpdb->get_results( $wpdb->prepare( $query, $start, $end ) );
	}

	public function insert_cart_log( $data ) {
		global $wpdb;
		$data_fm = array();

		$sample_fm = array(
			'user_id'     => '%s',
			'data'        => '%s',
			'time_log'    => '%d',
			'ip'          => '%s',
			'os_platform' => '%s',
//			'browser'     => '%s',
		);

		foreach ( $data as $key => $item ) {
			if ( isset( $sample_fm[ $key ] ) ) {
				$data_fm[] = $sample_fm[ $key ];
			}
		}

		$wpdb->insert( $this->cart_log_tb, $data, $data_fm );

		return $wpdb->insert_id;
	}

	public function update_cart_log( $data = array(), $where = array() ) {
		global $wpdb;
		$data_fm = $where_fm = array();
		$format  = array(
			'user_id'     => '%s',
			'data'        => '%s',
			'time_log'    => '%d',
			'ip'          => '%s',
			'os_platform' => '%s',
			'browser'     => '%s',
		);

		foreach ( $data as $item ) {
			if ( isset( $format[ $item ] ) ) {
				$data_fm[] = $format[ $item ];
			}
		}

		foreach ( $where as $item ) {
			if ( isset( $format[ $item ] ) ) {
				$where_fm[] = $format[ $item ];
			}
		}
		$wpdb->update( $this->cart_log_tb, $data, $where, $data_fm, $where_fm );
	}

	public function select_cart_log_record( $user_id, $time ) {
		global $wpdb;
		$query = "SELECT * FROM {$this->cart_log_tb} WHERE user_id = %s AND time_log > %d";

		return $wpdb->get_results( $wpdb->prepare( $query, $user_id, $time ) );
	}

	public function get_abd_cart_detail( $id ) {
		global $wpdb;
		$query = "SELECT * FROM {$this->cart_record_tb}  WHERE id = %d";

		return $wpdb->get_row( $wpdb->prepare( $query, $id ) );
	}

	public function get_user_ref( $g_id ) {
		global $wpdb;
		$query = "SELECT user_ref FROM {$this->guest_info_tb}  WHERE id = %d";

		return $wpdb->get_results( $wpdb->prepare( $query, $g_id ) );
	}

	public function remove_abd_record( $id, $force = false ) {
		global $wpdb;
		$where = array( 'id' => $id, 'recovered_cart' => 0 );

		if ( $force ) {
			unset( $where['recovered_cart'] );
		}

		return $wpdb->delete( $this->cart_record_tb, $where, array( '%d' ) );
	}

	public function remove_abd_record_by_user_id( $id ) {
		global $wpdb;
		$wpdb->delete( $this->cart_record_tb, array( 'user_id' => $id, 'recovered_cart' => 0 ), array( '%d', '%d' ) );
	}


	public function remove_mutlti_abd_record( $ids ) {
		global $wpdb;
		$ids   = implode( ',', array_map( 'absint', $ids ) );
		$query = "delete from {$this->cart_record_tb} where id in({$ids})  and recovered_cart = 0";
		$wpdb->query( $query );
	}

	public function remove_abd_record_via_ip( $ip ) {
		global $wpdb;
		$time  = WACVP_CURRENT_TIME - 3600;
		$query = "delete from {$this->cart_record_tb} where customer_ip = %s and abandoned_cart_time > {$time}  and recovered_cart = 0";
//		$wpdb->delete( $this->cart_record_tb, array( 'customer_ip' => $ip ), array( '%s' ) );
		$wpdb->query( $wpdb->prepare( $query, $ip ) );
	}

	public function remove_abd_record_by_time( $time ) {
		global $wpdb;
		$time  = WACVP_CURRENT_TIME - $time * DAY_IN_SECONDS;
		$query = "delete from {$this->cart_record_tb} where abandoned_cart_time < %d limit 500";
		$wpdb->query( $wpdb->prepare( $query, $time ) );
	}

	public function get_user_by_email( $email ) {
		global $wpdb;
		$query = "select id from {$this->guest_info_tb} where billing_email = %s ";
		$r     = $wpdb->get_row( $wpdb->prepare( $query, $email ) );

		return $r->id ?? '';
	}

	public function get_user_by_phone( $phone ) {
		global $wpdb;
		$query = "select id from {$this->guest_info_tb} where billing_phone = %d ";
		$r     = $wpdb->get_row( $wpdb->prepare( $query, $phone ) );

		return $r->id ?? '';
	}

	public function get_user_by_user_ref( $user_ref ) {
		global $wpdb;
		$query = "select id from {$this->guest_info_tb} where user_ref = %s ";
		$r     = $wpdb->get_row( $wpdb->prepare( $query, $user_ref ) );

		return $r->id ?? '';
	}

	public function get_customer_emails( $args ) {
		$args = wp_parse_args( $args, [
			'fields'       => '*',
			'where_clause' => '',
			'limit'        => PHP_INT_MAX,
			'offset'       => 0,
			'orderby'      => 'id',
			'order'        => 'DESC',
		] );

		if ( is_array( $args['fields'] ) ) {
			$args['fields'] = implode( ', ', $args['fields'] );
		}

		$where = $args['where_clause'] ? "WHERE {$args['where_clause']}" : '';

		$q = "SELECT {$args['fields']} FROM {$this->guest_info_tb} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d ;";
		$q = $this->wpdb->prepare( $q, $args['limit'], $args['offset'] );

		return $this->wpdb->get_results( $q, ARRAY_A );
	}

	public function count_records( $args ) {
		$args = wp_parse_args( $args, [ 'fields' => 'id', 'where_clause' => '' ] );

		if ( empty( $args['table'] ) ) {
			return 0;
		}

		$args['table'] = $this->wpdb->prefix . $args['table'];
		$where         = $args['where_clause'] ? "WHERE {$args['where_clause']}" : '';

		$q = "SELECT COUNT({$args['fields']}) FROM {$args['table']} {$where}";

		return $this->wpdb->get_var( $q );
	}


	public function get_abandoned_list( $args ) {
		$args = wp_parse_args( $args, [
			'fields'       => '*',
			'where_clause' => '',
			'limit'        => PHP_INT_MAX,
			'offset'       => 0,
			'orderby'      => 'id',
			'order'        => 'ASC',
		] );

		if ( is_array( $args['fields'] ) ) {
			$args['fields'] = implode( ', ', $args['fields'] );
		}

		$where = $args['where_clause'] ? "WHERE {$args['where_clause']}" : '';

		$q = "SELECT {$args['fields']} FROM {$this->cart_record_tb} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d ;";
		$q = $this->wpdb->prepare( $q, $args['limit'], $args['offset'] );

		return $this->wpdb->get_results( $q, ARRAY_A );
	}

	public function get_data_for_export_csv( $step, $from, $to, $limit = 50 ) {
		$offset = $step > 0 ? ( $step - 1 ) * $limit : 0;

		$where[] = "acr.user_id > 0 AND acr.cart_ignored = '0'";

		if ( $from ) {
			$where[] = "acr.abandoned_cart_time > {$from}";
		}

		if ( $to ) {
			$where[] = "acr.abandoned_cart_time < {$to}";
		}

		$where = implode( ' AND ', $where );

		$query = "select acr.*, gi.billing_first_name, gi.billing_last_name, gi.billing_email , gi.billing_phone from {$this->cart_record_tb} as acr ";
		$query .= "left join {$this->guest_info_tb} as gi ON acr.user_id = gi.id ";
		$query .= "where {$where} limit {$limit} offset {$offset}";

		return $this->wpdb->get_results( $query, ARRAY_A );
	}
}

