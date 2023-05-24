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



if ( ! function_exists ( 'mc_meta_links'  ) ) {

  function mc_meta_links( $links, $file ) {

      if ( $file === 'contact-form-7-mailchimp-extension/chimpmatic-lite.php' ) {

          $links[] = '<a href="'.MCE_URL.'" target="_blank" title="ChimpMatic Lite Documentation">ChimpMatic Documentation</a>';
          // $links[] = '<a href="'.MCE_URL.'" target="_blank" title="Starter Guide">Starter Guide</a>';
          $links[] = '<a href="//www.paypal.me/renzojohnson" target="_blank" title="Donate"><strong>Donate</strong></a>';
      }
      return $links;
  }

}
add_filter( 'plugin_row_meta', 'mc_meta_links', 10, 2 );



if ( ! function_exists ( 'mc_settings_link'  ) ) {


  function mc_settings_link( $links ) {

    $url = get_admin_url() . 'admin.php?page=wpcf7&post='.mc_get_latest_item().'&active-tab=4' ;
    $settings_link = '<a href="' . $url . '" title="ChimpMatic.com">' . __('ChimpMatic Settings', 'textdomain') . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
 }

}



if ( ! function_exists ( 'mc_after_setup_theme'  ) ) {

  function mc_after_setup_theme() {

       add_filter('plugin_action_links_' . SPARTAN_MCE_PLUGIN_BASENAME, 'mc_settings_link');
  }
  add_action ('after_setup_theme', 'mc_after_setup_theme');

}