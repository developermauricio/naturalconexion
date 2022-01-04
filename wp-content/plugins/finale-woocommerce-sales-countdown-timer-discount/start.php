<?php
/**
 * This file is to initiate XL core and to run some common methods and decide which XL core should run
 */

defined( 'ABSPATH' ) || exit;

$this_current_version = '5.9';
if ( ! class_exists( 'XL_Common' ) ) {

	class XL_Common {

		public static $is_xl_loaded = false;

		public static $current_version = '5.9';

		public static function include_xl_core() {

			global $xl_ultimate_latest_core_all, $xl_ultimate_latest_core;

			if ( isset( $xl_ultimate_latest_core_all['latest']['plugin_path'] ) && self::$is_xl_loaded === false ) {

				$get_global_path = $xl_ultimate_latest_core_all['latest']['plugin_path'] . '/xl/';

				if ( false === @file_exists( $get_global_path . 'includes/class-xl-api.php' ) ) {
					_doing_it_wrong( __FUNCTION__, __( 'XL Core should be present in folder \'xl\' in order to run this properly. ' ), self::$current_version );
					die( 0 );
				}

				/**
				 * Loading Core XL Files
				 */
				require_once $get_global_path . 'includes/class-xl-api.php';
				/**
				 * @todo holding back this functionality until we do it complete
				 */
				// require_once $get_global_path . 'includes/class-xl-promotions.php';
				require_once $get_global_path . 'includes/class-xl-admin-notifications.php';
				require_once $get_global_path . 'includes/class-xl-opt-in-manager.php';
				require_once $get_global_path . 'includes/class-xl-addons.php';
				require_once $get_global_path . 'includes/class-xl-plugin-states.php';
				require_once $get_global_path . 'includes/class-xl-addon.php';
				require_once $get_global_path . 'includes/class-xl-licenses.php';
				require_once $get_global_path . 'includes/class-xl-support.php';
				require_once $get_global_path . 'includes/class-xl-process.php';
				require_once $get_global_path . 'includes/class-xl-deactivation.php';
				require_once $get_global_path . 'includes/class-xl-dashboard-loader.php';
				require_once $get_global_path . 'includes/class-xl-cache.php';
				require_once $get_global_path . 'includes/class-xl-transients.php';
				require_once $get_global_path . 'includes/class-xl-file-api.php';

				$xl_ultimate_latest_core = $xl_ultimate_latest_core_all['latest'];

				do_action( 'xl_loaded', $get_global_path );
				self::$is_xl_loaded = true;
			}
		}

		/**
		 * Deleting option key where add-ons are set up
		 * @depreciated
		 */
		public static function reset_latest_installed() {

		}

		/**
		 * Deleting option key where add-ons are set up
		 */
		public static function load_text_domain( $get_global_path ) {
			global $xl_ultimate_latest_core_all;
			if ( ! is_admin() ) {
				return;
			}
			load_plugin_textdomain( 'xlplugins', false, $get_global_path . 'languages/' );

			wp_enqueue_script( 'wp-util' );
			wp_localize_script( 'wp-util', 'xlplugins', array(
				'core'    => $get_global_path,
				'version' => $xl_ultimate_latest_core_all['latest']['version'],
			) );
		}

	}

}
//add_action( 'xl_loaded', array( 'XL_Common', 'load_text_domain' ), 10, 1 );

global $xl_ultimate_latest_core_all;


if ( ! $xl_ultimate_latest_core_all ) {
	$xl_ultimate_latest_core_all                                                   = array();
	$xl_ultimate_latest_core_all['plugins']                                        = array();
	$xl_ultimate_latest_core_all['plugins'][ plugin_basename( WCCT_PLUGIN_FILE ) ] = array(
		'version'     => $this_current_version,
		'plugin_path' => dirname( WCCT_PLUGIN_FILE ),
	);
} else {
	//if not registered yet
	if ( ! isset( $xl_ultimate_latest_core_all['plugins'][ plugin_basename( WCCT_PLUGIN_FILE ) ] ) ) {
		$xl_ultimate_latest_core_all['plugins'][ plugin_basename( WCCT_PLUGIN_FILE ) ] = array(
			'version'     => $this_current_version,
			'plugin_path' => dirname( WCCT_PLUGIN_FILE ),
		);
	}
}

//if not registered yet
if ( ! isset( $xl_ultimate_latest_core_all['latest'] ) ) {
	$xl_ultimate_latest_core_all['latest'] = array(
		'basename'    => plugin_basename( WCCT_PLUGIN_FILE ),
		'version'     => $this_current_version,
		'plugin_path' => dirname( WCCT_PLUGIN_FILE ),
	);

} else {
	//if latest framework exists and have for the same plugin
	//if current version is same as the saved one
	if ( version_compare( $this_current_version, $xl_ultimate_latest_core_all['latest']['version'], '<' ) ) {
		//do nothing
	} else {


		$xl_ultimate_latest_core_all['latest'] = array(
			'basename'    => plugin_basename( WCCT_PLUGIN_FILE ),
			'version'     => $this_current_version,
			'plugin_path' => dirname( WCCT_PLUGIN_FILE ),
		);

	}
}
