<?php
/**
 * Created by PhpStorm.
 * User: v0id
 * Date: 04.06.19
 * Time: 13:35
 */
 $license_data = get_option('pcbpys_license_info',false);
 $txt_status = "";
 $case_status = "";
 $txt_until = "";
if(is_user_logged_in()  && current_user_can('administrator')) {
    ?>
    <div class="wpwoof-content wpwoof-box" style="overflow:unset;">
    <h2>Permissions:</h2>
    <form method="post" action="<?php echo admin_url() ?>?page=wpwoof-settings">
        <div> <?php /*class="wpwoof-aligncenter"*/ ?>
            <?php wp_nonce_field('pcbpys_nonce', 'pcbpys_nonce'); ?>
            <select id="id-permissions" multiple name="roles[]"><?php
                $editable_roles = wp_roles()->roles;
                $selected = get_option('wpwoof_permissions_role', array('administrator'));
                $p = "";
                foreach ($editable_roles as $role => $details) {
                    $name = translate_user_role($details['name']);
                    if (in_array($role, $selected)) // preselect specified role
                        $p .= "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
                    else
                        $p .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
                }
                echo $p;
                ?>
            </select><br/><br/>
            <input type="submit" class="wpwoof-button wpwoof-button-blue" name="pcbpys_add_permissions_role"
                   value="<?php _e('Save'); ?>"/>
            <script>jQuery('#id-permissions').fastselect();</script>
        </div>
    </form>
    </div><?php
}
?>


    <div class="wpwoof-content wpwoof-box">
    <h2>License:</h2>
<?php
 if($license_data) {
     $txt_status  =  $license_data->license=="valid" ? "Active" : ( $license_data->license=="expired" ? "<span style='color:red;'>EXPIRED</span>" : ucfirst(strtolower($license_data->license)) );
     $case_status =  $license_data->license;
     $txt_until   = ( $license_data->license=="disabled") ? "" : $license_data->expires;
 } else {
     $case_status =  trim(get_option( 'pcbpys_license_status',''));
     $txt_status  =  $case_status=="valid" ? "Active" : "Inactive";
     $txt_until   = $case_status=="valid" ? "lifetime" : "";

 }

     switch( $case_status ) {
         case "disabled":
         case "expired":
         case "valid":
             ?><p>Status: <b><?php echo $txt_status;?></b></p><?php
             if( !empty($txt_until) && $case_status!='invalid') {
                 ?><p>Your license key valid unti: <?php echo $txt_until;?></p><?php
             } ?>
             <form method="post" action="<?php echo admin_url() ?>?page=wpwoof-settings">
            <div > <?php /*class="wpwoof-aligncenter"*/ ?>
                <?php wp_nonce_field( 'pcbpys_nonce', 'pcbpys_nonce' );

                if($case_status=="expired"){
                    ?><br><br><br><p style="font-weight: bold;"><a target="_blank" href="<?php echo WPWOOF_SL_STORE_URL; ?>/checkout/?edd_license_key=<?php echo trim(get_option( 'pcbpys_license_key' ));?>&utm_campaign=admin&utm_source=licenses&utm_medium=renew">Click here to renew your license now</a></p><?php
                }
                ?>
                <input type="submit" class="wpwoof-button wpwoof-button-blue" name="pcbpys_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
            </div>
            </form>
            <?php

             break;
         default :
             ?>
             <form method="post" action="options.php">
             <?php settings_fields('pcbpys_license'); ?>

                 <div class="wpwoof-container">
                     <div>
                         <input id="pcbpys_license_key" name="pcbpys_license_key" type="text" class="regular-text"
                                value="<?php esc_attr_e($license); ?>"
                                placeholder="<?php _e('Enter your license key'); ?>"/>
                     </div><?php
                     if($case_status=="no activation left key"){
                         ?>
                         <p style="color: red">No activations left</p>
                         <?php
                         $case_status="";
                     }else if ($case_status=="invalid"){
                         ?>
                         <p style="color: red">License is invalid</p>
                         <?php
                         $case_status="";
                     }

                     ?>
                     <br/>
                     <div>
                         <?php wp_nonce_field('pcbpys_nonce', 'pcbpys_nonce'); ?>
                         <input type="submit" class="wpwoof-button wpwoof-button-blue" name="pcbpys_license_activate"
                                value="<?php _e('Activate License'); ?>"/>
                     </div><?php

                     if (empty($case_status)) {
                         ?>
                         <p>We sent you license key by email right after you bought the plugin, and you can also
                             find it inside <a target="_blank"
                                               href="<?php echo WPWOOF_SL_STORE_URL; ?>/my-account">your
                                 account.</a></p>
                         <h4>If you bought a bundle, use the plugin specific license.</h4><?php
                     }
                     ?>

             </form><?php
             break;
     }  ?>
</div>