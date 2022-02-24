<?php
/**
 * Plugin Name: WooCommerce Abandoned Cart Recovery Premium
 * Plugin URI: https://villatheme.com/extensions/woo-abandoned-cart-recovery/
 * Description: Capture abandoned cart & send reminder emails to the customers.
 * Version: 1.0.6.3
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woo-abandoned-cart-recovery
 * Domain Path: /languages
 * Copyright 2019-2021 VillaTheme.com. All rights reserved.
 * Requires at least: 5.0
 * Tested up to: 5.8
 * WC requires at least: 4.0
 * WC tested up to: 5.5
 * Requires PHP: 7.0
 **/

use WACVP\Inc\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'WACVP_VERSION', '1.0.6.3' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
global $wp_version;

define( 'WACVP_SLUG', 'woocommerce-abandoned-cart-recovery' );
define( 'WACVP_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-abandoned-cart-recovery" . DIRECTORY_SEPARATOR );
define( 'WACVP_LANGUAGES', WACVP_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'WACVP_INCLUDES', WACVP_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'WACVP_VIEWS', WACVP_DIR . "views" . DIRECTORY_SEPARATOR );
define( 'WACVP_TEMPLATES', WACVP_INCLUDES . "templates" . DIRECTORY_SEPARATOR );
define( 'WACVP_GUEST_ID_LIMIT', 100000000 );

$wacv_err_message = '';
$require_wp_ver   = '4.0';
$require_php_ver  = '7.0';

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$wacv_err_message = __( 'Please install and activate WooCommerce to use WooCommerce Abandoned Cart Recovery.', 'woo-abandoned-cart-recovery' );
}
if ( ! version_compare( $wp_version, $require_wp_ver, '>' ) ) {
	$wacv_err_message = __( "Please update WordPress version {$require_wp_ver} to use WooCommerce Abandoned Cart Recovery.", 'woo-abandoned-cart-recovery' );
}

if ( ! version_compare( phpversion(), $require_php_ver, '>=' ) ) {
	$wacv_err_message = __( "Please update PHP version at least {$require_php_ver} to use WooCommerce Abandoned Cart Recovery.", 'woo-abandoned-cart-recovery' );
}

if ( $wacv_err_message ) {
	if ( ! function_exists( 'wacv_notification' ) ) {
		function wacv_notification() {
			global $wacv_err_message;
			?>
            <div id="message" class="error">
                <p><?php echo esc_html($wacv_err_message) ?></p>
            </div>
			<?php
		}
	}
	add_action( 'admin_notices', 'wacv_notification' );
	deactivate_plugins( plugin_basename( __FILE__ ) );
} else {
	require_once WACVP_INCLUDES . "define.php";
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wacvp_add_action_links' );
	register_activation_hook( __FILE__, 'wacvp_activate' );

	function wacvp_add_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=wacv_settings' ) . '">' . __( 'Settings', 'woo-abandoned-cart-recovery' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	function wacvp_activate( $network_wide ) {
		require_once WACVP_INCLUDES . "plugin.php";
		$wacv_plugin = \WACVP\Inc\Plugin::get_instance();
		$wacv_plugin->activate( $network_wide );
	}

	function wacvp_activate_new_blog( $blog_id ) {
		if ( is_plugin_active_for_network( 'woocommerce-abandoned-cart-recovery/woocommerce-abandoned-cart-recovery.php' ) ) {
			switch_to_blog( $blog_id );
			require_once WACVP_INCLUDES . "plugin.php";
			$wacv_plugin = \WACVP\Inc\Plugin::get_instance();
			$wacv_plugin->single_active();
			restore_current_blog();
		}
	}

	add_action( 'wpmu_new_blog', 'wacvp_activate_new_blog' );

	function wacv_delete_plugin_tables( $tables, $blog_id ) {
		if ( empty( $blog_id ) || 1 == $blog_id || $blog_id != $GLOBALS['blog_id'] ) {
			return $tables;
		}
		global $wpdb;
		$blog_prefix   = $wpdb->get_blog_prefix( $blog_id );
		$plugin_tables = array(
			'wacv_abandoned_cart_record',
			'wacv_guest_info_record',
			'wacv_email_history',
			'wacv_cart_log'
		);
		foreach ( $plugin_tables as $k => $table ) {
			$tables[ $table ] = $blog_prefix . $table;
		}

		return $tables;
	}

	add_filter( 'wpmu_drop_tables', 'wacv_delete_plugin_tables', 10, 2 );

	function WACV_Data() {
		return Data::get_instance();
	}
}


