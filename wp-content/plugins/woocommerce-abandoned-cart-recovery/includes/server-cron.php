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

class Server_Cron {

	protected static $instance = null;

	private function __construct() {
		add_action( 'init', array( $this, 'receive_cron_request' ) );
	}


	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
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
}
