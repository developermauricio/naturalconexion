<?php

/**
 * Plugin Name: PixelYourSite PRO
 * Plugin URI: http://www.pixelyoursite.com/
 * Description: Implement the Facebook Pixel, Facebook Conversion API, Google Analytics, the Google Ads Tag and the TikTok Tag. Track key actions with automatic events, or create your own events. WooCommerce and EDD fully supported, with Facebook Dynamic Ads Pixel set-up,  Google Analytics Enhanced Ecommerce, and Dynamic Remarketing. Full support for <a href="https://www.consentmagic.com/?utm_source=PixelYourSite&utm_medium=Professional&utm_campaign=Description-link" target="_blank">ConsentMagic.com</a>.
 * Version: 8.6.4
 * Author: PixelYourSite
 * Author URI: http://www.pixelyoursite.com
 * License URI: http://www.pixelyoursite.com/pixel-your-site-pro-license
 *
 * Requires at least: 4.4
 * Tested up to: 5.8
 *
 * WC requires at least: 2.6.0
 * WC tested up to: 6.0
 *
 * Text Domain: pys
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'PYS_VERSION', '8.6.4' );
define( 'PYS_PINTEREST_MIN_VERSION', '3.0.0' );
define( 'PYS_SUPER_PACK_MIN_VERSION', '3.0.2' );
define( 'PYS_BING_MIN_VERSION', '2.0.0' );
define( 'PYS_PLUGIN_NAME', 'PixelYourSite Professional' );
define( 'PYS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PYS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'PYS_PLUGIN_FILE', __FILE__ );
define( 'PYS_PLUGIN_BASENAME', plugin_basename( PYS_PLUGIN_FILE ) );

function isPysFreeActive() {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    return is_plugin_active( 'pixelyoursite/facebook-pixel-master.php' );

}

register_activation_hook( __FILE__, 'pysProActivation' );
function pysProActivation() {

    if ( isPysFreeActive() ) {
       deactivate_plugins('pixelyoursite/facebook-pixel-master.php');
    }

	\PixelYourSite\manageAdminPermissions();
}

if ( isPysFreeActive() ) {
    return; // exit early when PYS Free is active
}
require_once PYS_PATH.'/vendor/autoload.php';
require_once PYS_PATH.'/includes/logger/class-pys-logger.php';
require_once PYS_PATH.'/includes/class-event-id-generator.php';
require_once PYS_PATH.'/includes/functions-common.php';
require_once PYS_PATH.'/includes/functions-cartflows.php';
require_once PYS_PATH.'/includes/functions-admin.php';
require_once PYS_PATH.'/includes/events/class-event.php';
require_once PYS_PATH.'/includes/events/interface-events.php';
require_once PYS_PATH.'/includes/events/class-event-single.php';
require_once PYS_PATH.'/includes/events/class-event-grouped.php';
require_once PYS_PATH.'/includes/events/class-events-signal.php';
require_once PYS_PATH.'/includes/events/class-events-woo.php';
require_once PYS_PATH.'/includes/events/class-events-edd.php';
require_once PYS_PATH.'/includes/events/class-events-fdp.php';
require_once PYS_PATH.'/includes/events/class-events-wcf.php';
require_once PYS_PATH.'/includes/events/class-events-custom.php';
require_once PYS_PATH.'/includes/functions-custom-event.php';
require_once PYS_PATH.'/includes/functions-woo.php';
require_once PYS_PATH.'/includes/functions-edd.php';
require_once PYS_PATH.'/includes/functions-system-report.php';
require_once PYS_PATH.'/includes/functions-license.php';
require_once PYS_PATH.'/includes/functions-update-plugin.php';
require_once PYS_PATH.'/includes/functions-gdpr.php';
require_once PYS_PATH.'/includes/functions-migrate.php';
require_once PYS_PATH.'/includes/class-pixel.php';
require_once PYS_PATH.'/includes/class-settings.php';
require_once PYS_PATH.'/includes/class-plugin.php';

require_once PYS_PATH.'/includes/class-pys.php';
require_once PYS_PATH.'/includes/class-events-manager.php';
require_once PYS_PATH.'/includes/class-custom-event.php';
require_once PYS_PATH.'/includes/class-custom-event-factory.php';
require_once PYS_PATH.'/modules/tiktok/tiktok.php';
require_once PYS_PATH.'/modules/facebook/facebook.php';
require_once PYS_PATH.'/modules/facebook/facebook-server.php';
require_once PYS_PATH.'/modules/google_analytics/ga.php';
require_once PYS_PATH.'/modules/google_ads/google_ads.php';
require_once PYS_PATH.'/modules/head_footer/head_footer.php';
require_once PYS_PATH.'/includes/enrich/class_enrich_order.php';
require_once PYS_PATH.'/includes/enrich/class-enrich_user.php';
require_once PYS_PATH.'/includes/class-events-manager-ajax_hook.php';
require_once PYS_PATH.'/includes/class-fixed-notices.php';
// here we go...
PixelYourSite\PYS();
