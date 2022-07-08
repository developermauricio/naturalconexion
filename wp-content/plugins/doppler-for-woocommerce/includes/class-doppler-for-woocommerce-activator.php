<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Fired during plugin activation
 *
 * @link       https://www.fromdoppler.com/
 * @since      1.0.0
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 * @author     Doppler LLC <info@fromdoppler.com>
 */
class Doppler_For_Woocommerce_Activator {

	/**
	 * Deactivate plugin (use period)
	 *
	 * Creates all the neccesary options, fields
	 * or database tables. Perhaps check if WooCommerce exists?
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		//Creates abandoned cart table
		//Creates visited products table
		//If account has changed, connects.
		
		global $wpdb;
		$table_name = $wpdb->prefix . DOPPLER_ABANDONED_CART_TABLE;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			    id BIGINT(20) NOT NULL AUTO_INCREMENT,
			    name VARCHAR(60),
			    lastname VARCHAR(60),
			    email VARCHAR(100),
			    phone VARCHAR(20),
			    location VARCHAR(100),
			    cart_contents LONGTEXT,
			    cart_total DECIMAL(10,2),
			    currency VARCHAR(10),
			    time DATETIME DEFAULT NULL,
			    session_id VARCHAR(60),
			    other_fields LONGTEXT,
				cart_url TEXT,
				token TEXT,
				restored SMALLINT DEFAULT 0,
			    PRIMARY KEY  (id)
		) $charset_collate;";
		  
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$sql ="ALTER TABLE $table_name AUTO_INCREMENT = 1";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . DOPPLER_VISITED_PRODUCTS_TABLE;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			    id BIGINT(20) NOT NULL AUTO_INCREMENT,
				user_id BIGINT(20),
				user_name VARCHAR(60),
				user_lastname VARCHAR(60),
				user_email VARCHAR(200),
			    product_id BIGINT(20),
			    product_name VARCHAR(200),
			    product_slug VARCHAR(200),
				product_description TEXT,
				product_image TEXT,
				product_link VARCHAR(200),
			    product_price DECIMAL(10,2),
				product_regular_price DECIMAL(10,2),
			    currency VARCHAR(10),
			    visited_time DATETIME DEFAULT NULL,
			    PRIMARY KEY  (id)
		) $charset_collate;";
		  
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		//Saves plugin version.
		update_option('dplrwoo_version', DOPPLER_FOR_WOOCOMMERCE_VERSION);

		/**
		 * Doppler App Integration.
		 * 
		 * If plugin in installed for the 1st time (dplrwoo_api_connected is empty)
		 * we ignore app integration becouse it will be performed on first sync.
		 * 
		 * If for some reason dplrwoo_api_connected has a value, it means plugin was
		 * deactivated and re-activated. On deactivation Integration was DELETED, so we
		 * are goint to re-activate and regenerate the keys.
		 * 
		 */
		$options = get_option('dplr_settings');
		if( !empty(get_option('dplrwoo_api_connected')) && !empty($options['dplr_option_useraccount']) && !empty($options['dplr_option_apikey']) ){
			$DopplerAppConnect = new Doppler_For_WooCommerce_App_Connect(
				$options['dplr_option_useraccount'], $options['dplr_option_apikey'],
				DOPPLER_WOO_API_URL, DOPPLER_FOR_WOOCOMMERCE_ORIGIN
			);
			//TODO: On fail through warning, but goes on with the activation.
			//TODO: Display onscreen status somewhere. 
			//TODO: should disconnect?
			$response = $DopplerAppConnect->connect();
			//check $response['response']['code']==200
		}
	}

}
