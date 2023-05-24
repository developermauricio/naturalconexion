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



function mce_updts ( $update, $item ) {

    $plugins = array (
        'blocks',
        'chimpmatic',
        'quick-maps',
        'contact-form-7-campaign-monitor-extension',
        'contact-form-7-mailchimp-extension',
        'integrate-contact-form-7-and-aweber',
        'cf7-getresponse',
        'cf7-icontact-extension',
    );
    if ( in_array( $item->slug, $plugins ) ) {

        return true;

    } else {

        return $update;

    }
}

$autoupdate = get_option( 'chimpmatic-update', '1' ) ;

if ( $autoupdate  )
  add_filter( 'auto_update_plugin', 'mce_updts', 10, 2 );



// Disable auto-update email notifications for plugins.
add_filter( 'auto_plugin_update_send_email', '__return_false' );

// Disable auto-update email notifications for themes.
add_filter( 'auto_theme_update_send_email', '__return_false' );
