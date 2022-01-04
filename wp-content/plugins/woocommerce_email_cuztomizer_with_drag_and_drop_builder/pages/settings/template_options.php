<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (function_exists('WC')) {
    $wooinst = WC();
    $mailer = $wooinst->mailer();
    $mails = $mailer->get_emails();
} else {
    $mailer = $woocommerce->mailer();
    $mails = $mailer->get_emails();
}
$avail_lang_list = array();
$avail_lang_list[] = 'en_US';
$avail_lang_list_new = get_available_languages();
if(!empty($avail_lang_list_new) && is_array($avail_lang_list_new)){
    if(!in_array('en_US', $avail_lang_list_new)){
        $avail_lang_list = array_merge($avail_lang_list, $avail_lang_list_new);
    } else {
        $avail_lang_list = $avail_lang_list_new;
    }
}

if ( empty( $translations ) ) {
    require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
    $translations = wp_get_available_translations();
}

$woo_mb_settings_lang = get_option('woo_mb_settings_lang', '');
if ($woo_mb_settings_lang != ''){
    $woo_mb_settings_lang = json_decode($woo_mb_settings_lang);
}
?>
<form name="settings" id="woo-mail-settings" action="#">
    <div class="email-builder-settings-option email-builder-settings-option_template_con" id="woo-mail-settings_toggle">
        <?php
        global $sitepress;
        ?>
        <ul class="nav nav-tabs">
        <?php
        $i = 0;
        foreach ($avail_lang_list as $avail_lang_code){
            $i++;
            if($avail_lang_code == 'en_US'){
                $avail_lang_string = "English (United States)";
            } else {
                if(isset($translations[ $avail_lang_code ])){
                    $translation = $translations[ $avail_lang_code ];
                    $avail_lang_string = $translation['english_name'];
                    //$avail_lang_string = $translation['native_name'];
                } else {
                    $avail_lang_string = $avail_lang_code;
                }
            }
            ?>
            <li class="<?php echo ($i == 1)? ' active': ''; ?>">
                <a data-toggle="tab" href="#<?php echo $avail_lang_code; ?>"><?php echo "For - ".$avail_lang_string; ?></a>
            </li>
             <?php
        }
        ?>
        </ul>
        <div class="tab-content">
         <?php
         $i = 0;
        foreach ($avail_lang_list as $avail_lang_code){
            $i++;
            ?>
            <div class="tab-pane fade<?php echo ($i == 1)? ' in active': ''; ?>" id="<?php echo $avail_lang_code; ?>">
                <div class="email-builder-settings_fields">
                    <?php $fieldName = 'dir_'.$avail_lang_code; ?>
                    <label class="settings-label" for="settings_lang_<?php echo $avail_lang_code; ?>">
                        <?php esc_html_e('Direction', 'woo-email-customizer-page-builder'); ?>
                    </label>
                    <?php
                    $radioValue = isset($woo_mb_settings_lang->$fieldName)? $woo_mb_settings_lang->$fieldName: 'ltr';
                    ?>
                    <label><input type="radio" name="settings_lang[<?php echo $fieldName; ?>]" value="ltr"<?php echo ($radioValue == 'ltr')? ' checked': ''; ?>><?php esc_html_e('ltr', 'woo-email-customizer-page-builder'); ?></label>&nbsp;
                    <label><input type="radio" name="settings_lang[<?php echo $fieldName; ?>]" value="rtl"<?php echo ($radioValue == 'rtl')? ' checked': ''; ?>><?php esc_html_e('rtl', 'woo-email-customizer-page-builder'); ?></label>
                </div>
            <?php
            foreach ($mails as $mail){
                $fieldName = $avail_lang_code.'_'.$mail->id;
                ?>
                <div class="email-builder-settings_fields">
                    <label class="settings-label" for="settings_lang_<?php echo $fieldName; ?>">
                        <?php esc_html_e($mail->title, 'woo-email-customizer-page-builder'); ?>
                    </label>
                    <?php
                    $radioValue = isset($woo_mb_settings_lang->$fieldName)? $woo_mb_settings_lang->$fieldName: 1;
                    ?>
                    <label><input type="radio" name="settings_lang[<?php echo $fieldName; ?>]" value="1"<?php echo ($radioValue == 1)? ' checked': ''; ?>><?php esc_html_e('Yes', 'woo-email-customizer-page-builder'); ?></label>&nbsp;
                    <label><input type="radio" name="settings_lang[<?php echo $fieldName; ?>]" value="0"<?php echo ($radioValue == 0)? ' checked': ''; ?>><?php esc_html_e('No', 'woo-email-customizer-page-builder'); ?></label>
                </div>
                <?php
            }
            ?>
            </div>
                <?php
        }
        ?>
        </div>
        <input type="hidden" name="settings_based_on_template_lang" value="1"/>
        <div class="email-builder-settings_fields">
            <a class="md-btn md-btn-default md-btn-mini" onclick="woo_email_customizer_saveWooEmailCustomizerSettings()" data-url="#" title="<?php esc_html_e('Save settings', 'woo-email-customizer-page-builder'); ?>" href="#">
                <i class="actions md-icon material-icons save md-color-green-600">save</i>
            </a>
        </div>
    </div>
</form>