<?php
/**
 * Plugin Name: Contact Form 7 Extension For Mailchimp
 * Plugin URI: https://renzojohnson.com/contributions/contact-form-7-mailchimp-extension
 * Description: Integrate Contact Form 7 with Mailchimp. Automatically add form submissions to predetermined lists in Mailchimp, using its latest API.
 * Version: 0.5.68
 * Author: Renzo Johnson
 * Author URI: https://renzojohnson.com
 * Text Domain: chimpmatic-lite
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 5.6
 *
*/

/*  Copyright 2010-2023 Renzo Johnson (email: renzo.johnson at gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'SPARTAN_MCE_VERSION' ) ) {

    define( 'SPARTAN_MCE_VERSION', '0.5.68' );
    define( 'SPARTAN_MCE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    define( 'SPARTAN_MCE_PLUGIN_NAME', trim( dirname( SPARTAN_MCE_PLUGIN_BASENAME ), '/' ) );
    define( 'SPARTAN_MCE_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
    define( 'SPARTAN_MCE_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

}



require_once( SPARTAN_MCE_PLUGIN_DIR . '/lib/mailchimp.php' );



if (!function_exists('write_log')) {

    function write_log($log) {

        if (true === WP_DEBUG) {

            if (is_array($log) || is_object($log)) {

            } else {

            }
        }
    }

}