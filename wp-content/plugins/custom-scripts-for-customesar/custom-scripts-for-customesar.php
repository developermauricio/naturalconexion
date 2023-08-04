<?php

/**
 * @link              https://www.rupok.me
 * @since             1.1.0
 * @package           Custom_Scripts_For_Customiser
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Scripts for Customiser
 * Plugin URI:        https://wordpress.org/plugins/custom-script-for-customizer
 * Description:       Add custom scripts through WordPress Customizer and edit with CodeMirror editor.
 * Version:           1.1.0
 * Author:            Nazmul H. Rupok
 * Author URI:        https://www.rupok.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-script-for-customizer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CUSTOM_SCRIPT_CUSTOMIZER_VERSION', '1.1.0' );
define('CUSTOM_SCRIPT_DOMAIN', 'custom-scripts-for-customizer');
define('CUSTOM_SCRIPT_DIR', plugin_dir_path(__FILE__));

function activate_custom_script_for_customizer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-script-for-customizer-activator.php';
	Custom_Script_For_Customizer_Activator::activate();
}

function deactivate_custom_script_for_customizer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-script-for-customizer-deactivator.php';
	Custom_Script_For_Customizer_Deactivator::deactivate();
}


// Action menus

function csfc_add_settings_link( $links ) {
    $add_scripts_link = sprintf( '<a href="customize.php">' . __( 'Add Scripts' ) . '</a>' );
    array_push( $links, $add_scripts_link);
   return $links;
}
$plugin = plugin_basename( __FILE__ );

if(file_exists(plugin_dir_path( __FILE__ ) . 'includes/class-custom-script-for-customizer.php')) {
	
	
	require plugin_dir_path( __FILE__ ) . 'includes/class-custom-script-for-customizer.php';
	
}
	
	function run_custom_script_for_customizer() {
		if(file_exists(plugin_dir_path( __FILE__ ) . 'includes/class-custom-script-for-customizer.php')) {

			$plugin = new Custom_Script_For_Customizer();
		}

	}
	if(file_exists(plugin_dir_path( __FILE__ ) . 'includes/class-custom-script-for-customizer.php')) {
	run_custom_script_for_customizer();
	}
	