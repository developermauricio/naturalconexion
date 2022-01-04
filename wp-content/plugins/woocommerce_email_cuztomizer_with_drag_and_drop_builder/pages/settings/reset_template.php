<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Load mailer
if (function_exists('WC')) {
    $wooinst = WC();
    $mailer = $wooinst->mailer();
    $mails = $mailer->get_emails();
} else {
    $mailer = $woocommerce->mailer();
    $mails = $mailer->get_emails();
}
?>
<h4>
    <?php esc_html_e("Reset all templates", "woo-email-customizer-page-builder") ?>
</h4>
<div class="email-builder-settings-option">
    <a class="md-btn md-btn-default md-btn-mini" onclick="woo_email_customizer_resetDefaultTemplate()" data-url="#" title="<?php esc_html_e('Reset all template to default', 'woo-email-customizer-page-builder'); ?>" href="#">
        <i class="actions material-icons">cached</i>
    </a>
    <span class="label"><?php esc_html_e('Reset all template to default', 'woo-email-customizer-page-builder'); ?></span>
</div>
<h4>
    <?php esc_html_e("Reset selected template", "woo-email-customizer-page-builder") ?>
</h4>

<div class="email-builder-settings-option">
    <div class="email-builder-settings_fields">
        <div class="reset_single_template_container">
            <?php
            $avail_lang_list = get_available_languages();
            $lang_select = wp_dropdown_languages( array(
                'id' => 'woo_mb_email_lang_reset',
                'name' => 'woo_mb_email_lang_reset',
                'languages' => $avail_lang_list,
                'selected' => get_locale(),
                'echo'      => 0,
                'show_available_translations' => false
            )  ); ?>

            <?php echo $lang_select;  ?>

            <select title="<?php _e('Choose which email status to reset.', 'woo-email-customizer-page-builder'); ?>" name="woo_mb_email_type_reset" id="woo_mb_email_type_reset">
                <option value="">
                    <?php _e("Template to reset", 'woo-email-customizer-page-builder'); ?>
                </option>
                <?php
                //Customer_Invoice
                if (!empty($mails)) {
                    foreach ($mails as $mail) { ?>
                        <option value="<?php echo $mail->id ?>">
                            <?php echo ucwords($mail->title); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
            <a class="md-btn md-btn-default md-btn-mini" onclick="woo_email_customizer_resetSingleTemplate()" data-url="#" title="<?php esc_html_e('Reset single template to empty', 'woo-email-customizer-page-builder'); ?>" href="#">
                <i class="actions material-icons">cached</i>
            </a>
        </div>
    </div>
</div>