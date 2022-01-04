<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'wpsf_register_settings_iconic-wsb', 'iconic_wsb_settings' );

/**
 * Starter for WooCommerce Settings
 *
 * @param array $settings
 *
 * @return array
 */
function iconic_wsb_settings( $settings ) {

	$settings = Iconic_WSB_Settings::init_tabs( $settings );

	return $settings;
}