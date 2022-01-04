<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Deactivator {

	/**
	 * Unregister hooks on deactivation
	 *
	 * Remove SMS order reminder schedule event
	 * Drop egoi_sms_order_reminders table
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook('egoi_sms_order_event');
	}

}
