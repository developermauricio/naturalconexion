<?php
    /*
    Plugin Name: Profile Builder - Campaign Monitor Add-On
    Plugin URI: http://www.cozmoslabs.com/wordpress-profile-builder/
    Description: Easily associate Campaign Monitor client list fields with Profile Builder fields. Also, make use of the Profile Builder Campaign Monitor Widget to add more subscribers to your lists.
    Version: 1.1.0
    Author: Cozmoslabs, Mihai Iova
    Author URI: http://www.cozmoslabs.com/
    License: GPL2

    == Copyright ==
    Copyright 2014 Cozmoslabs (www.cozmoslabs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    */


    /*
     * Definitions and dependencies
     *
     */
    define('WPPBCMI_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ));
    define('WPPBCMI_PLUGIN_URL', plugin_dir_url(__FILE__));

    // Increase the timeout to 15 seconds
    if (false === defined('WPPB_CS_REST_CALL_TIMEOUT')) {
        define('WPPB_CS_REST_CALL_TIMEOUT', 15);
    }

    // Include Campaign Monitor API files
    if( file_exists( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_general.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_general.php' );

    if( file_exists( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_clients.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_clients.php' );

    if( file_exists( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_lists.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_lists.php' );

    if( file_exists( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_subscribers.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/cmonitor/csrest_subscribers.php' );


    // Include the file for general functions
    if( file_exists( WPPBCMI_PLUGIN_DIR . '/admin/functions.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/admin/functions.php' );

    // Include the file for the Campaign Monitor Manage Fields
    if( file_exists( WPPBCMI_PLUGIN_DIR . '/admin/manage-fields.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/admin/manage-fields.php' );

    // Include the file for the Campaign Monitor sub-page
    if( file_exists( WPPBCMI_PLUGIN_DIR . '/admin/cmonitor-page.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/admin/cmonitor-page.php' );

    // Include the file for the Widget
    if( file_exists( WPPBCMI_PLUGIN_DIR . '/admin/widget.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/admin/widget.php' );

    // Include the file for the Campaign Monitor field
    if( file_exists( WPPBCMI_PLUGIN_DIR . '/front-end/cmonitor-field.php' ) )
        include_once( WPPBCMI_PLUGIN_DIR . '/front-end/cmonitor-field.php' );


    /*
     * Check for updates
     *
     */
    function wppb_cmi_update_checker(){
        if( class_exists( 'wppb_PluginUpdateChecker' ) ) {
            //we don't know what version we have installed so we need to check both
            $localSerial = get_option('wppb_profile_builder_pro_serial');
            if (empty($localSerial))
                $localSerial = get_option('wppb_profile_builder_hobbyist_serial');

            $wppb_cmi_update = new wppb_PluginUpdateChecker('http://updatemetadata.cozmoslabs.com/?localSerialNumber=' . $localSerial . '&uniqueproduct=CLPBCM', __FILE__, 'wppb-cmi-add-on');
        }
    }
    add_action( 'plugins_loaded', 'wppb_cmi_update_checker', 100 );


    /*
     * Function that fires up on add-on activation
     *
     * @since v.1.0.0
     *
     */
    function wppb_cmi_activation() {
        if( get_option( 'wppb_cmi_api_key_validated', 'not_found' ) == 'not_found' )
            add_option( 'wppb_cmi_api_key_validated', false );
    }
    register_activation_hook( __FILE__, 'wppb_cmi_activation' );


    /*
     * Function that enqueues the necessary scripts in the admin area
     *
     * @since v.1.0.0
     *
     */
    function wppb_cmi_scripts_and_styles_admin() {
        wp_register_script( 'wppb-cmonitor-integration', plugin_dir_url(__FILE__) . 'assets/js/main.js', array( 'jquery' ) );
        wp_enqueue_script( 'wppb-cmonitor-integration' );
        wp_enqueue_style( 'wppb-cmonitor-integration', plugin_dir_url(__FILE__) . 'assets/css/style-back-end.css' );
    }
    add_action( 'admin_enqueue_scripts', 'wppb_cmi_scripts_and_styles_admin' );


    /*
     * Function that enqueues the necessary scripts in the front end area
     *
     * @since v.1.0.0
     *
     */
    function wppb_cmi_scripts_and_styles_front_end() {
        wp_enqueue_style( 'wppb-cmonitor-integration', plugin_dir_url(__FILE__) . 'assets/css/style-front-end.css' );
    }
    add_action( 'wp_enqueue_scripts', 'wppb_cmi_scripts_and_styles_front_end' );


    /*
     * Function that registers the settings for the Campaign Monitor options page
     *
     * @since v1.0.0
     *
     */
    function wppb_cmi_register_settings() {
        register_setting( 'wppb_cmi_settings', 'wppb_cmi_settings', 'wppb_cmi_settings_sanitize' );
    }
    if ( is_admin() ) {
        add_action('admin_init', 'wppb_cmi_register_settings');
    }
