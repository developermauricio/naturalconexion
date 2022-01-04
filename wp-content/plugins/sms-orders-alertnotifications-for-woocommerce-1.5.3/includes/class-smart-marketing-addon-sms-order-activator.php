<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Activator {

	/**
	 * Register hooks on plugin activation
	 *
	 * Create a schedule event to process SMS order reminder
	 * Create table egoi_sms_order_reminders
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if (! wp_next_scheduled ( 'egoi_sms_order_event' )) {
			wp_schedule_event(time(), 'every_fifteen_minutes', 'egoi_sms_order_event');
		}

        self::create_sms_follow_price_table();
		self::create_sms_abandoned_cart_table();
		self::create_email_order_reminders_table();
		self::create_sms_order_reminders_table();

        self::checkApiKey();
	}

	public static function create_email_order_reminders_table() {
		global $wpdb;

		$table_name = $wpdb->prefix. 'egoi_email_order_reminders';
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


		$sqla = "CREATE TABLE IF NOT EXISTS $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  order_id int NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";


		dbDelta( $sqla );

        $table_name = $wpdb->prefix. 'egoi_email_order_billets';

        $sqlb = "CREATE TABLE IF NOT EXISTS $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  order_id int NOT NULL,
		  link VARCHAR(255) NOT NULL,
		  code VARCHAR(16) NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";


		dbDelta( $sqlb );
	}

	public static function create_sms_order_reminders_table() {
		global $wpdb;

		$table_name = $wpdb->prefix. 'egoi_sms_order_reminders';
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


		$sqla = "CREATE TABLE IF NOT EXISTS $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  order_id int NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";


		dbDelta( $sqla );

        $table_name = $wpdb->prefix. 'egoi_sms_order_billets';

        $sqlb = "CREATE TABLE IF NOT EXISTS $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  order_id int NOT NULL,
		  link VARCHAR(255) NOT NULL,
		  code VARCHAR(16) NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";


		dbDelta( $sqlb );

        update_option('egoi_activation_data', time());
	}

	public static function create_sms_follow_price_table(){
        global $wpdb;

        $table_name = $wpdb->prefix. 'egoi_sms_follow_price';
        $charset_collate = $wpdb->get_charset_collate();

        $sqla = "CREATE TABLE IF NOT EXISTS $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  product_id int NOT NULL,
		  mobile VARCHAR(100) NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sqla );
    }

    public static function create_sms_abandoned_cart_table() {
        global $wpdb;

        $table_name = $wpdb->prefix. 'egoi_sms_abandoned_carts';
        $charset_collate = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


        $sqla = "CREATE TABLE IF NOT EXISTS $table_name (
		  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  woo_session_key VARCHAR(255) NOT NULL,
		  php_session_key VARCHAR(255) NOT NULL,
		  cellphone VARCHAR(255) NOT NULL,
		  order_id bigint(20) UNSIGNED NOT NULL,
          status VARCHAR(255) NOT NULL DEFAULT 'standby',
		  PRIMARY KEY  (id)
		) $charset_collate;";


        dbDelta( $sqla );

    }

	/**
	 * @throws SoapFault
	 */
	public static function checkApiKey() {
        $apikey = get_option('egoi_api_key');
        $params = [
            'plugin_key' => '2f711c62b1eda65bfed5665fbd2cdfc9',
            'apikey' 		=> $apikey['api_key']
        ];
        $client = new SoapClient('http://api.e-goi.com/v2/soap.php?wsdl');
        $client->checklogin($params);
        self::ping($apikey['api_key']);
        self::activateTransactional($apikey['api_key']);
    }

    public static function activateTransactional($apikey){
        $response = wp_remote_post( 'https://www51.e-goi.com/api/public/client', array(
            'body'    => json_encode(['apikey' => $apikey]),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ) );
    }

	/**
	 * @param $apikey
	 *
	 * @return array|mixed
	 */
	public function ping ($apikey)
	{
		$egoiV3    = 'https://api.egoiapp.com';
		$pluginkey = '2f711c62b1eda65bfed5665fbd2cdfc9';

		try {
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL            => $egoiV3 . "/ping",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_HTTPHEADER     => array(
					"cache-control: no-cache",
					"Apikey: " . $apikey,
					"Pluginkey: " . $pluginkey
				),
			));

			curl_exec($curl);
			curl_close($curl);
			return true;

		} catch (Exception $e) {
			return true;
		}
	}
}
