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



function vc_utm() {

  global $wpdb;

  $utms  = '?utm_source=ChimpmaticLite';
  $utms .= '&utm_campaign=w' . get_bloginfo( 'version' ) . '-' . mce_difer_dateact_date() . 'c' . WPCF7_VERSION . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . '';
  $utms .= '&utm_medium=cme-' . SPARTAN_MCE_VERSION . '';
  $utms .= '&utm_term=P' . PHP_VERSION . 'Sq' . $wpdb->db_version() . '-';
  // $utms .= '&utm_content=';
  return $utms;
}



function mce_panel_gen ($apivalid,$listdata,$cf7_mch,$listatags,$mce_txcomodin) {

  ?>
   <div class="mystery">

    <input type="hidden" id="mce_txtcomodin" name="wpcf7-mailchimp[mce_txtcomodin]" value="<?php echo( isset( $mce_txcomodin ) ) ? esc_textarea( $mce_txcomodin ) : ''; ?>" style="width:0%;" />
    <div class="mce-custom-fields">
      <div class="mail-field">
        <div id="mce_panel_listamail" >

            <?php mce_html_panel_listmail( $apivalid, $listdata, $cf7_mch); // Get listas ?>

        </div>
        <small class="description">Hit the Connect button to load your lists <a href="https://chimpmatic.com/help/how-to-get-your-mailchimp-api-key<?php echo vc_utm() ?>MC-list-id" class="helping-field" target="_blank" title="get help with MailChimp List ID:"> Learn More</a></small>
      </div>
    </div>

    <div class="mce-custom-fields">
      <div class="mail-field md-half">
        <label for="wpcf7-mailchimp-email"><?php echo esc_html( __( 'Subscriber Email: *|EMAIL|* ', 'wpcf7' ) ); ?> <span class="mce-required" > Required</span></label><br />
         <?php mce_html_selected_tag ('email',$listatags,$cf7_mch,'email') ;  ?>
        <small class="description">MUST be an email tag <a href="<?php echo CHIMPHELP_URL ?>/mailchimp-required-email<?php echo vc_utm() ?>MC-email" class="helping-field" target="_blank" title="get help with Subscriber Email:"> Learn More</a></small>
      </div>

      <div class="mail-field md-half">
        <label for="wpcf7-mailchimp-name"><?php echo esc_html( __( 'Subscriber Name - *|FNAME|* ', 'wpcf7' ) ); ?></label><br />
         <?php mce_html_selected_tag ('name',$listatags,$cf7_mch,'text') ; ?>
        <small class="description"> This may be sent as Name <a href="<?php echo CHIMPHELP_URL ?>/mailchimp-subscriber-name<?php echo vc_utm() ?>MC-name" class="helping-field" target="_blank" title="get help with Subscriber name:"> Learn More</a></small>
      </div>
    </div>



    <div class="mce-custom-fields holder-img">
      <h3 class="title">Audience TAGs - <span class="audience-name"><a href="<?php echo CHIMPL_URL ?>?utm_source=ChimpMatic&utm_campaign=Tags-img" target="_blank" title="ChimpMatic Pro Options">PRO Feature</a></span></h3>
      <p>You can add these as your contacts tags:</p>
      <div class="col-6">
              <div id="chm_panel_camposformatags">

    <label class="pr10"><input type="checkbox" id="wpcf7-mailchimp-labeltags-1" name="wpcf7-mailchimp[labeltags][your-name]" value="1">
             [your-name] <span class="mce-type">text</span></label>



    <label class="pr10"><input type="checkbox" id="wpcf7-mailchimp-labeltags-3" name="wpcf7-mailchimp[labeltags][your-number]" value="1">
             [your-number] <span class="mce-type">number</span></label>



    <label class="pr10"><input type="checkbox" id="wpcf7-mailchimp-labeltags-4" name="wpcf7-mailchimp[labeltags][your-height]" value="1" checked="checked">
             [your-car] <span class="mce-type">text</span></label>

                    <label class="atags"><b>Arbitrary Tags Here:</b> <input type="text" id="wpcf7-mailchimp-labeltags_cm-tag" name="wpcf7-mailchimp[labeltags_cm-tag]" value="genre, [card-brand]" placeholder="comma, separated, texts, or [mail-tags]">
                  <p class="description">You can type in your tags here. Comma separated text or [mail-tags]</p>
                </label>
              </div>
          </div>

      <a class="lin-to-pro" href="<?php echo CHIMPL_URL ?>?utm_source=ChimpMatic&utm_campaign=TAGs-link" target="_blank" title="ChimpMatic Pro Options"><span>PRO Feature <span>Learn More...</span></span></a>
    </div>




    <div class="mce-custom-fields holder-img">
      <h3 class="title">Audience GROUPs - <span class="audience-name"><a href="<?php echo CHIMPL_URL ?>?utm_source=ChimpMatic&utm_campaign=Tags-img" target="_blank" title="ChimpMatic Pro Options">PRO Feature</a></span></h3>
      <p>Match them to your Contact Form checkboxes or radio buttons:</p>

      <div class="col-6">
              <div id="chm_panel_camposformagroup">
                            <div class="mcee-container">
            <label> <span class="gname">Car Brand</span> <span class="mce-type">id: 4ce1c8eff2 - type :checkboxes</span>               <input type="hidden" id="wpcf7-mailchimp-ggCustomKey1" value="4ce1c8eff2" style="width:0%" name="wpcf7-mailchimp[ggCustomKey1]">
            </label>
            <label class="glocks"><span class="dashicons dashicons-lock blue"></span>
                <input type="checkbox" class="chimp-gg-arbirary" tag="1" id="wpcf7-mailchimp-ggCheck1" name="wpcf7-mailchimp[ggCheck1]" value="1">
            </label>
            <div id="gg-select1">
                <select class="chm-select" id="wpcf7-mailchimp-ggCustomValue1" name="wpcf7-mailchimp[ggCustomValue1]" style="width:95%">
                  <option value=" ">Choose.. </option>
                  <option value="my-gdpr1" selected="selected">[car-brand]  - type :checkbox </option>
                  <option value="my-gdpr2">[car-color]  - type :checkbox </option>
                  <option value="my-gdpr3">[my-size]  - type :checkbox </option>
                </select>
              </div>
      </div>
                  </div>
      </div>
<a class="lin-to-pro" href="<?php echo CHIMPL_URL ?>?utm_source=ChimpMatic&utm_campaign=GROUPs-link" target="_blank" title="ChimpMatic Pro Options"><span>PRO Feature  <span>Learn More...</span></span></a>
    </div>

    <div class="mce-custom-fields holder-img">
      <h3 class="title">Audience GDPR Marketing Preferences - <span class="audience-name"><a href="<?php echo CHIMPL_URL ?>?utm_source=ChimpMatic&utm_campaign=Tags-img" target="_blank" title="ChimpMatic Pro Options">PRO Feature</a></span></h3>
      <p>Match them to your Contact Form checkboxes:</p>
      <div class="col-6">
              <div id="chm_panel_camposformaGDPR">
                            <div class="mcee-container">
            <label>Email <span class="mce-type">id: 74de728e79</span>               <input type="hidden" id="wpcf7-mailchimp-GDPRcustomKey1" value="74de728e79" style="width:0%" name="wpcf7-mailchimp[GDPRcustomKey1]">
            </label>
            <div id="GDPR-select1">
                    <select class="chm-select" id="wpcf7-mailchimp-GDPRCustomValue1" name="wpcf7-mailchimp[GDPRCustomValue1]" style="width:95%">
              <option value=" ">
          Choose.. </option>
                 <option value="my-gdpr1"  selected="selected">
              [my-gdpr1]  - type :checkbox </option>
                        <option value="my-gdpr2">
              [my-gdpr2]  - type :checkbox </option>
                        <option value="my-gdpr3">
              [my-gdpr3]  - type :checkbox </option>
                        <option value="my-gdpr4">
              [my-gdpr4]  - type :checkbox </option>
                        <option value="mi-radio">
              [mi-radio]  - type :radio </option>
                </select>
              </div>
      </div>
          <div class="mcee-container">
            <label>Direct Mail <span class="mce-type">id: 701c19a03b</span>               <input type="hidden" id="wpcf7-mailchimp-GDPRcustomKey2" value="701c19a03b" style="width:0%" name="wpcf7-mailchimp[GDPRcustomKey2]">
            </label>
            <div id="GDPR-select2">
                    <select class="chm-select" id="wpcf7-mailchimp-GDPRCustomValue2" name="wpcf7-mailchimp[GDPRCustomValue2]" style="width:95%">
              <option value=" ">
          Choose.. </option>
                 <option value="my-gdpr1">
              [my-gdpr1]  - type :checkbox </option>
                        <option value="my-gdpr2" selected="selected">
              [my-gdpr2]  - type :checkbox </option>
                        <option value="my-gdpr3">
              [my-gdpr3]  - type :checkbox </option>
                        <option value="my-gdpr4">
              [my-gdpr4]  - type :checkbox </option>
                        <option value="mi-radio">
              [mi-radio]  - type :radio </option>
                </select>
              </div>
      </div>

                  </div>
          </div>
<a class="lin-to-pro" href="<?php echo CHIMPL_URL ?>?utm_source=ChimpMatic&utm_campaign=GDPRs-link" target="_blank" title="ChimpMatic Pro Options"><span>PRO Feature <span>Learn More...</span></span></a>
    </div>


  </div>


<?php
}

?>


<div class="mce-main-fields pos-rel" data-info="6283ef9bdef6755f8fe686ce53bdf75a-us4">

  <a href="http://bit.ly/latte4renzo" class="dops-button is-primary donate-2019" target="_blank">DONATE</a>

  <div id="mce_apivalid">
    <h2>ChimpMatic <span class="cm-lite">Lite</span>  <?php echo isset( $apivalid ) && '1' == $apivalid ? $chm_valid : $chm_invalid ; ?> <span class="mc-code"><?php global $wpdb; $mce_sents = get_option( 'mce_sent'); echo SPARTAN_MCE_VERSION . 'CF7:' . WPCF7_VERSION . 'WP' . get_bloginfo( 'version' ) . 'P' . PHP_VERSION . 'S' . $wpdb->db_version() .' - ' . $mce_sents .  ' sent in ' .  mce_difer_dateact_date(); ?></span></h2>

  </div>

  <div class="mce-custom-fields">

    <label for="wpcf7-mailchimp-api"><?php echo esc_html( __( 'MailChimp API Key:', 'wpcf7' ) ); ?> </label><br />
    <input type="text" id="wpcf7-mailchimp-api" name="wpcf7-mailchimp[api]" class="wide" size="50" data-chimp="1ed6d4f4abc3e9" data-matic="deecf086af7f04b3a3-us4" placeholder="Enter Your Mailchimp API key Here" value="<?php echo (isset($cf7_mch['api']) ) ? esc_attr( $cf7_mch['api'] ) : ''; ?>" />

    <span><input id="mce_activalist" type="button" value="Connect and Fetch Your Audiences" class="button button-primary" style="width:35%;" /><span class="spinner"></span></span>

    <small class="description need-api"><a href="<?php echo CHIMPHELP_URL ?>/help/how-to-get-your-mailchimp-api-key<?php echo vc_utm() ?>MC-api" class="helping-field" target="_blank" title="get help with MailChimp API Key:"> Find your Mailchimp API here <span class="red-icon dashicons dashicons-arrow-right"></span><span class="red-icon dashicons dashicons-arrow-right"></span></a></small>


    <div id="chmp-new-user" class="new-user <?php echo ( ( $apivalid == 1  ) ? 'chmp-inactive' : 'chmp-active' ) ;  ?>">

      <?php  chmp_new_usr(); ?>

    </div>

    <div id="mce_panel_ajagen" class="<?php echo ( ( $apivalid == 1  ) ? 'chmp-active' : 'chmp-inactive' ) ;  ?>">
        <?php  mce_panel_gen ($apivalid,$listdata,$cf7_mch,$listatags,$mce_txcomodin) ;    ?>
    </div>


    <div id="cme-container" class="cme-container mce-support only-one-toogles" style="display:none">

        <div class="mailchimp-custom-fields">
          <p>In the following fields, you can use these mail-tags: <?php echo mce_mail_tags(); ?>.</p>

          <?php for($i=1;$i<=10;$i++){ ?>
          <div>

            <div class="col-6">
              <label for="wpcf7-mailchimp-CustomValue<?php echo $i; ?>"><?php echo esc_html( __( 'Contact Form [mail-tag] '.$i.':', 'wpcf7' ) ); ?></label><br />
              <input type="text" id="wpcf7-mailchimp-CustomValue<?php echo $i; ?>" name="wpcf7-mailchimp[CustomValue<?php echo $i; ?>]" class="wide" size="70" placeholder="[your-mail-tag]" value="<?php echo (isset( $cf7_mch['CustomValue'.$i]) ) ?  esc_attr( $cf7_mch['CustomValue'.$i] ) : '' ;  ?>" />
            </div>


            <div class="col-6">
              <label for="wpcf7-mailchimp-CustomKey<?php echo $i; ?>"><?php echo esc_html( __( 'MailChimp field name or *|MERGE|* tag '.$i.':', 'wpcf7' ) ); ?> <a href="<?php echo MCE_URL ?>/mailchimp/manage-mailchimp-audience-fields-and-merge-tags<?php echo vc_utm() ?>MC-custom-fields" class="helping-field" target="_blank" title="get help with Custom Fields"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label><br />
              <input type="text" id="wpcf7-mailchimp-CustomKey<?php echo $i; ?>" name="wpcf7-mailchimp[CustomKey<?php echo $i; ?>]" class="wide" size="70" placeholder="MERGE<?php echo $i+2;?>" value="<?php echo (isset( $cf7_mch['CustomKey'.$i]) ) ?  esc_attr( $cf7_mch['CustomKey'.$i] ) : '' ;  ?>" />
            </div>

          </div>
          <?php } ?>
        </div><!-- /.mailchimp-custom-field -->


        <?php include SPARTAN_MCE_PLUGIN_DIR . '/lib/parts/tanu.php'; ?>


   </div>


  <p class="p-author"><a type="button" aria-expanded="false" class="mce-trigger a-support ">Show Advanced Settings</a> &nbsp; <a class="cme-trigger-sys a-support ">Server Environment</a> &nbsp; <a class="cme-trigger-log a-support ">View Debug Logs</a> &nbsp; <a class="cme-trigger-php a-support ">View PHP Logs</a></p>

  <?php include SPARTAN_MCE_PLUGIN_DIR . '/lib/system.php'; ?>

  <?php  echo chimp_html_log_view() ; ?>
  <?php  echo chimp_html_php_log_view() ; ?>

  <div class="dev-cta mce-cta welcome-panel" style="display: none;">

    <div class="welcome-panel-content">
        <?php echo mce_set_welcomebanner() ; ?>
    </div>

  </div>



</div>


</div>
<?php echo mce_lateral_banner () ?>