<?php

/**
 * Plugin Name: PixelYourSite Super Pack
 * Plugin URI: http://www.pixelyoursite.com/
 * Description: Extend the power of PixelYourSite Pro with these extra add-ons.
 * Version: 2.1.4
 * Author: PixelYourSite
 * Author URI: http://www.pixelyoursite.com
 * License URI: http://www.pixelyoursite.com/pixel-your-site-pro-license
 *
 * Requires at least: 4.4
 * Tested up to: 5.0.2
 *
 * WC requires at least: 2.6.0
 * WC tested up to: 4.2
 *
 * Text Domain: pys
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use PixelYourSite\SuperPack;

define( 'PYS_SUPER_PACK_VERSION', '2.1.4' );
define( 'PYS_SUPER_PACK_PRO_MIN_VERSION', '7.0.6' );
define( 'PYS_SUPER_PACK_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PYS_SUPER_PACK_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'PYS_SUPER_PACK_PLUGIN_FILE', __FILE__ );

require_once 'modules/superpack/functions-common.php';
require_once 'modules/superpack/functions-admin.php';
require_once 'modules/superpack/functions-migrate.php';

register_activation_hook( __FILE__, 'pysSuperPackActivation' );
function pysSuperPackActivation() {

	if ( ! SuperPack\isPysProActive() || ! SuperPack\pysProVersionIsCompatible() ) {
		wp_die( 'PixelYourSite Super Pack Add-on requires PixelYourSite PRO version ' .
                PYS_SUPER_PACK_PRO_MIN_VERSION .' or newer.',
            'Plugin Activation',
            array( 'back_link' => true, )
        );
	}

}

if ( SuperPack\isPysProActive() ) {

    add_action( 'init', function() {
        require_once 'modules/superpack/superpack.php';
    }, 8 );

    if ( ! SuperPack\pysProVersionIsCompatible() ) {
        add_action( 'admin_notices', 'PixelYourSite\SuperPack\adminNoticePysProOutdated' );
    }

} else {
    add_action( 'admin_notices', 'PixelYourSite\SuperPack\adminNoticePysProNotActive' );
}
