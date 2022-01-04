<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class WCCT_Compatibilities
 * Loads all the compatibilities files we have in finale against plugins
 */
class WCCT_Compatibilities {


	public static function load_all_compatibilities() {
		if ( isset( $_GET['wcct_disable'] ) && $_GET['wcct_disable'] == 'yes' && is_user_logged_in() && current_user_can( 'administrator' ) ) {
			return;
		}
		// load all the WCCT_Compatibilities files automatically
		foreach ( glob( plugin_dir_path( WCCT_PLUGIN_FILE ) . '/compatibilities/*.php' ) as $_field_filename ) {

			require_once( $_field_filename );
		}
	}
}

//hooked over 999 so that all the plugins got initiated by that time
add_action( 'plugins_loaded', array( 'WCCT_Compatibilities', 'load_all_compatibilities' ), 999 );
