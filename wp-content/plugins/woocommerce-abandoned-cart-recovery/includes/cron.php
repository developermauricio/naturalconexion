<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 31/01/2019
 * Time: 4:07 CH
 */

namespace WACVP\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cron {

	protected static $instance = null;

	public function __construct() {

		add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );

		if ( ! wp_next_scheduled( 'wacv_execute_cron' ) ) {
			wp_schedule_event( time(), 'one_minute', 'wacv_execute_cron' );
		}

		add_action( 'wacv_execute_cron', array( $this, 'wacv_execute_cron' ) );

		if ( ! wp_next_scheduled( 'wacv_remove_abandoned_cart' ) ) {
			wp_schedule_event( time(), 'daily', 'wacv_remove_abandoned_cart' );
		}

		add_action( 'wacv_remove_abandoned_cart', array( $this, 'wacv_remove_abandoned_cart' ) );

	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	public function add_cron_schedule( $schedules ) {
		$schedules['one_minute'] = array(
			'interval' => 60,
			'display'  => __( 'One minute' ),
		);

		return $schedules;
	}

	public function wacv_execute_cron() {
		do_action( 'wacv_cron_send_email_abd_cart' );
		do_action( 'wacv_cron_send_email_abd_order' );
		do_action( 'wacv_cron_send_sms' );
		do_action( 'wacv_cron_send_messenger' );
	}

	public function receive_cron_request() {
		$data   = Data::get_params();
		$enable = $data['enable_cron_server'];
		if ( $enable ) {
			if ( isset( $_GET['crtk'] ) && $_GET['crtk'] == get_option( 'wacv_cron_key' ) ) {
				file_get_contents( site_url() . '/wp-cron.php' );
				exit;
			}
		}
	}

	public function wacv_remove_abandoned_cart() {
		$time = Data::get_param( 'delete_record_time' );
		Query_DB::get_instance()->remove_abd_record_by_time( $time );
	}
}
