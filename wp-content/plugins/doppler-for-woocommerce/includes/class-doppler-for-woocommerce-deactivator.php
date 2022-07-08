<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.fromdoppler.com/
 * @since      1.0.0
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 * @author     Doppler LLC <info@fromdoppler.com>
 */
class Doppler_For_Woocommerce_Deactivator {

	/**
	 * Deactivate plugin. (use period)
	 *
	 * Performs tasks on deactivate plugin.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		/**
		 * On deactivation delete integration with APP.
		 * If APP couldn't delete stops deactivation
		 * and shows message.
		 */
		$options = get_option('dplr_settings');
		$has_consumer_secret = get_option('dplrwoo_api_connected');

		if( empty($options['dplr_option_useraccount']) || empty($options['dplr_option_apikey']) ||
			empty($has_consumer_secret) ) return false;
		
		$doppler_app_connect = new Doppler_For_WooCommerce_App_Connect(
			$options['dplr_option_useraccount'], $options['dplr_option_apikey'],
			DOPPLER_WOO_API_URL, DOPPLER_FOR_WOOCOMMERCE_ORIGIN
		);

		//Disconnect integration from App.
		$doppler_app_connect->disconnect();

		//delete keys
		$doppler_app_connect->remove_keys();

		/**
		 * Remove cron schedule.
		 */
		$timestamp = wp_next_scheduled( 'dplrwoo_cron_job' );
		wp_unschedule_event( $timestamp, 'dplrwoo_cron_job' );
		   
		$timestamp = wp_next_scheduled( 'dplrwoo_cron_clean_views' );
		wp_unschedule_event( $timestamp, 'dplrwoo_cron_clean_views' );
		   
		$timestamp = wp_next_scheduled( 'dplrwoo_synch_cron' );
   		wp_unschedule_event( $timestamp, 'dplrwoo_synch_cron' );
	
	}

}