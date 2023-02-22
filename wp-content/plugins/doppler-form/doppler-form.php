<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Doppler
 *
 * @wordpress-plugin
 * Plugin Name:       Doppler Forms
 * Description:       Crea Formularios de Suscripción con la misma estética de tu sitio web o blog en minutos. Conéctalo con Doppler y envía a tus nuevos contactos automáticamente a una Lista de Suscriptores.
 * Version:           2.3.2
 * Author:            Doppler LLC
 * Author URI:        https://www.fromdoppler.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       doppler-form
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( !defined('DOPPLER_FORM_VERSION') ) define( 'DOPPLER_FORM_VERSION', '2.3.2' );
if( !defined('WP_DEBUG_LOG_DOPPLER_PLUGINS') ) define( 'WP_DEBUG_LOG_DOPPLER_PLUGINS', false );

if( !defined('DOPPLER_PLUGINS_PATH') ) define('DOPPLER_PLUGINS_PATH', plugin_dir_path(__DIR__));
if( !defined('DOPPLER_PLUGIN_URL')   ) define('DOPPLER_PLUGIN_URL', plugin_dir_url( __FILE__ ));

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if( is_plugin_active('Plugin/doppler-form.php') ) {
	
	deactivate_plugins( plugin_basename( __FILE__ ) );
	// Throw an error in the WordPress admin console.
	$error_message = '<p style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;font-size: 13px;line-height: 1.5;color:#444;">' . esc_html__( 'You have to uninstall version 1.1 of Doppler Form before installing version 2.0 ', 'doppler-form' ) . '</p>';
	die( $error_message ); // WPCS: XSS ok.

}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_doppler() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-doppler-form-activator.php';
	Doppler_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_doppler() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-doppler-form-deactivator.php';
	Doppler_Deactivator::deactivate();
}

 register_activation_hook( __FILE__, 'activate_doppler' );
 //register_deactivation_hook( __FILE__, 'deactivate_doppler' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-doppler-form.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_doppler() {

	$plugin = new DPLR_Doppler();
	$plugin->run();

}
run_doppler();
