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



$mch_tool_autoupdate = get_option( 'chimpmatic-update') ;

if ( $mch_tool_autoupdate === '0' or  $mch_tool_autoupdate ==='1' ) {

    update_option( 'chimpmatic-update', $mch_tool_autoupdate );
    //var_dump ( 'existe : ' . $mch_tool_autoupdate  ) ;
} else {

  $deprecated = null;
  $autoload = 'no';
  add_option( 'chimpmatic-update', '1', $deprecated, $autoload );
  $mch_tool_autoupdate = 1;

}

?>

  <table class="form-table mt0 description">
    <tbody>

      <tr>
        <th scope="row">Custom Fields</th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Custom Fields</span></legend><label for="wpcf7-mailchimp-cfactive">
          <input type="checkbox" id="wpcf7-mailchimp-cf-active" name="wpcf7-mailchimp[cfactive]" value="1"<?php echo ( isset($cf7_mch['cfactive']) ) ? ' checked="checked"' : ''; ?> />
          <?php echo esc_html( __( 'Send more fields to Mailchimp.com', 'wpcf7' ) ); ?>  <a href="<?php echo MC_URL ?>/help<?php echo vc_utm() ?>MC-custom-fields" class="helping-field" target="_blank" title="get help with Custom Fields"> Learn More </a></label>
          </fieldset>
        </td>
      </tr>


      <tr>
        <th scope="row">Double Opt-in</th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Double Opt-in</span></legend><label for="wpcf7-mailchimp-cfactive">
          <input type="checkbox" id="wpcf7-mailchimp-conf-subs" name="wpcf7-mailchimp[confsubs]" value="1"<?php echo ( isset($cf7_mch['confsubs']) ) ? ' checked="checked"' : ''; ?> />
          <?php echo esc_html( __( 'Enable', 'wpcf7' ) ); ?>  <a href="<?php echo MC_URL ?>/help/mailchimp-opt-in-checkbox<?php echo vc_utm() ?>MC-double-opt-in" class="helping-field" target="_blank" title="Get help with Double Opt-in"> Learn More </a></label>
          </fieldset>
        </td>
      </tr>



      <tr>
        <th scope="row">Required Acceptance</th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Required Acceptance</span></legend><label for="wpcf7-mailchimp-cfactive">
          <input type="text" id="wpcf7-mailchimp-accept" name="wpcf7-mailchimp[accept]" class="regular-text ltr" placeholder="[my-optin] <= Leave Empty if you are NOT using the checkbox or read the link above" value="<?php echo (isset($cf7_mch['accept'])) ? $cf7_mch['accept'] : '';?>" />
           <small class="description"><?php echo mce_mail_tags(); ?>  <a href="<?php echo MC_URL ?>/help/mailchimp-opt-in-checkbox<?php echo vc_utm() ?>MC-opt-in-checkbox" class="helping-field" target="_blank" title="Get help with Subscriber name:"> Learn More </a></small></label>
          </fieldset>
        </td>
      </tr>

       <tr class="">
        <th scope="row"></th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Add As Unsubscribed</span></legend><label for="wpcf7-mailchimp-cfactive">
          <input type="checkbox" id="wpcf7-mailchimp-addunsubscr" name="wpcf7-mailchimp[addunsubscr]" value="1"<?php echo ( isset($cf7_mch['addunsubscr']) ) ? ' checked="checked"' : ''; ?> />
          <?php echo esc_html( __( 'Add As Unsubscribed ', 'wpcf7' ) ); ?>  <a href="<?php echo MCE_URL ?>/mailchimp-opt-in-addunsubscr<?php echo vc_utm() ?>MC-double-addunsubscr" class="helping-field" target="_blank" title="get help with Custom Fields"> Learn More </a></label>
          </fieldset>
        </td>
      </tr>




      <tr>
        <th scope="row">Debug Logger</th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Debug Logger</span></legend><label for="wpcf7-mailchimp-cfactive">
          <input type="checkbox"
                 id="wpcf7-mailchimp-logfileEnabled"
                 name="wpcf7-mailchimp[logfileEnabled]"
                 value="1" <?php echo ( isset( $cf7_mch['logfileEnabled'] ) ) ? ' checked="checked"' : ''; ?>
          />
          Enable to troubleshoot issues with the extension.</label>
          </fieldset>
        </td>
      </tr>

      <tr>
        <th scope="row">Developer</th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Developer</span></legend><label for="wpcf7-mailchimp-cfactive">
          <input type="checkbox" id="wpcf7-mailchimp-cf-support" name="wpcf7-mailchimp[cf-supp]" value="1"<?php echo ( isset($cf7_mch['cf-supp']) ) ? ' checked="checked"' : ''; ?> />
          A backlink to my site, not compulsory, but appreciated</label>
          </fieldset>
        </td>
      </tr>

      <tr>
        <th scope="row">Auto Update</th>
        <td>
          <fieldset><legend class="screen-reader-text"><span>Auto Update</span></legend><label for="wpcf7-mailchimp-updates">
          <input type="checkbox" id="chimpmatic-update" name="chimpmatic-update" value="1"<?php echo ( $mch_tool_autoupdate == '1'  ) ? ' checked="checked"' : ''; ?> />
          Auto Update Chimpmatic Lite</label>
          </fieldset>
        </td>
      </tr>

    </tbody>
  </table>