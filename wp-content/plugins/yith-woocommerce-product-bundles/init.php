<?php
/**
 * Plugin Name: YITH WooCommerce Product Bundles
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-product-bundles
 * Description: <code><strong>YITH WooCommerce Product Bundles</strong></code> allows you to bundle WooCommerce products and sell them at a unique price. You can also set the quantity for each bundled item! <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>
 * Version: 1.26.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-product-bundles
 * Domain Path: /languages/
 * WC requires at least: 7.8
 * WC tested up to: 8.0.x
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Product Bundles
 * @version 1.26.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * WooCommerce admin notice.
 */
function yith_wcpb_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'YITH WooCommerce Product Bundles is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-product-bundles' ); ?></p>
	</div>
	<?php
}

/**
 * Premium admin notice.
 */
function yith_wcpb_install_free_admin_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'You can\'t activate the free version of YITH WooCommerce Product Bundles while you are using the premium one.', 'yith-woocommerce-product-bundles' ); ?></p>
	</div>
	<?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


if ( ! defined( 'YITH_WCPB_VERSION' ) ) {
	define( 'YITH_WCPB_VERSION', '1.26.0' );
}

if ( ! defined( 'YITH_WCPB_FREE_INIT' ) ) {
	define( 'YITH_WCPB_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCPB' ) ) {
	define( 'YITH_WCPB', true );
}

if ( ! defined( 'YITH_WCPB_FILE' ) ) {
	define( 'YITH_WCPB_FILE', __FILE__ );
}

if ( ! defined( 'YITH_WCPB_URL' ) ) {
	define( 'YITH_WCPB_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_WCPB_DIR' ) ) {
	define( 'YITH_WCPB_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_WCPB_TEMPLATE_PATH' ) ) {
	define( 'YITH_WCPB_TEMPLATE_PATH', YITH_WCPB_DIR . 'templates' );
}

if ( ! defined( 'YITH_WCPB_VIEWS_PATH' ) ) {
	define( 'YITH_WCPB_VIEWS_PATH', YITH_WCPB_DIR . 'views' );
}

if ( ! defined( 'YITH_WCPB_ASSETS_URL' ) ) {
	define( 'YITH_WCPB_ASSETS_URL', YITH_WCPB_URL . 'assets' );
}

if ( ! defined( 'YITH_WCPB_ASSETS_PATH' ) ) {
	define( 'YITH_WCPB_ASSETS_PATH', YITH_WCPB_DIR . 'assets' );
}

if ( ! defined( 'YITH_WCPB_INCLUDES_PATH' ) ) {
	define( 'YITH_WCPB_INCLUDES_PATH', YITH_WCPB_DIR . 'includes' );
}

if ( ! defined( 'YITH_WCPB_SLUG' ) ) {
	define( 'YITH_WCPB_SLUG', 'yith-woocommerce-product-bundles' );
}

/**
 * Init.
 */
function yith_wcpb_init() {

	load_plugin_textdomain( 'yith-woocommerce-product-bundles', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	require_once 'includes/objects/class.yith-wc-product-bundle.php';
	require_once 'includes/objects/class.yith-wc-bundled-item.php';
	require_once 'includes/compatibility/class.yith-wcpb-compatibility.php';
	require_once 'includes/class.yith-wcpb-admin.php';
	require_once 'includes/class.yith-wcpb-frontend.php';
	require_once 'includes/class.yith-wcpb.php';
	require_once 'includes/functions.yith-wcpb.php';

	// Let's start the game!
	YITH_WCPB();
}

add_action( 'yith_wcpb_init', 'yith_wcpb_init' );

/**
 * Install
 */
function yith_wcpb_install() {

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'yith_wcpb_install_woocommerce_admin_notice' );
	} elseif ( defined( 'YITH_WCPB_PREMIUM' ) ) {
		add_action( 'admin_notices', 'yith_wcpb_install_free_admin_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {
		do_action( 'yith_wcpb_init' );
	}
}

add_action( 'plugins_loaded', 'yith_wcpb_install', 11 );

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( plugin_dir_path( __FILE__ ) );
