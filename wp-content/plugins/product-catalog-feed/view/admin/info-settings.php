<?php
/**
 * Created by PhpStorm.
 * User: v0id
 * Date: 04.06.19
 * Time: 13:35
 */

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