<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Iconic_WSB_Settings.
 *
 * Any methods related to the plugin settings.
 *
 * @class    Iconic_WSB_Settings
 * @version  1.0.0
 */
class Iconic_WSB_Settings {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'init' ) );
	}

	/**
	 * Init.
	 */
	public static function init() {
		global $iconic_wsb_class;

		if ( ! $iconic_wsb_class ) {
			return;
		}

		$iconic_wsb_class->set_settings();
	}

	/**
	 * Add all plugin settings tab
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function init_tabs( $settings ) {
		$settings['tabs']['order-bump'] = array(
			'id'    => 'order_bump',
			'title' => __( 'Design', 'iconic-wsb' ),
		);

		return $settings;
	}
}
