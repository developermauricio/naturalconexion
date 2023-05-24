<?php
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



require_once(ABSPATH . '/wp-load.php') ;
require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

/*
 * Hide the 'Activate Plugin' and other links when not using QuietSkin as these links will
 * fail when not called from /wp-admin
 */

echo '<style> p + p > a {display: none !important;} </style>';
// echo '<style> a {display: none !important;} </style>';
class QuietSkin extends \WP_Upgrader_Skin {
    public function feedback($string, ...$args) { /* no output */ }
}

/**
 * Download, install and activate a plugin
 *
 * If the plugin directory already exists, this will only try to activate the plugin
 *
 * @param string $slug The slug of the plugin (should be the same as the plugin's directory name
 */



function sswInstallActivatePlugin($slug) {

    $pluginDir = WP_PLUGIN_DIR . '/' . $slug;
    /*
     * Don't try installing plugins that already exist (wastes time downloading files that
     * won't be used
     */
    if (!is_dir($pluginDir)) {

        $api = plugins_api(
            'plugin_information',
            array(
                'slug' => $slug,
                'fields' => array(
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false,
                            ),
                  )
             );

        // Replace with new QuietSkin for no output
        $skin = new QuietSkin(array('api' => $api));
        // $skin = new QuietSkin(array('api' => $api));


        $upgrader = new Plugin_Upgrader($skin);
        //$upgrader = new QuietSkin($skin);

        $install = $upgrader->install($api->download_link);

        if ($install !== true) {

            echo 'Error: Install process failed (' . $slug . '). var_dump of result follows.<br>'
                . "\n";
            var_dump($install); // can be 'null' or WP_Error

        }
    }



    /*
     * The install results don't indicate what the main plugin file is, so we just try to
     * activate based on the slug. It may fail, in which case the plugin will have to be activated
     * manually from the admin screen.
     */

    $pluginPath = $pluginDir . '/' . 'wp-'.$slug . '.php';

    if (file_exists($pluginPath)) {

        activate_plugin($pluginPath);  //finish activated

    } else {
        echo 'Error: Plugin file not activated (' . $slug . '). This probably means the main '
            . 'file\'s name does not match the slug. Check the plugins listing in wp-admin.<br>'
            . "\n";

    }
}



$pluginSlugs = array(

    'contact-form-7',

);



foreach ($pluginSlugs as $pluginSlug) {

  sswInstallActivatePlugin($pluginSlug);

}