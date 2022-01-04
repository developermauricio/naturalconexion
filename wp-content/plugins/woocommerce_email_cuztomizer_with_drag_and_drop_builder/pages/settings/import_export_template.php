<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
if(isset($_POST['import']) && sanitize_text_field($_POST['import']) == 1){
    WooEmailCustomizerCommon::importEmailTemplates();
}
?>
<h4>
    <?php esc_html_e("Export", "woo-email-customizer-page-builder") ?>
</h4>
<div class="email-builder-settings-option">
    <a class="md-btn md-btn-default md-btn-mini" onclick="woo_email_customizer_export_templates()" data-url="#" title="<?php esc_html_e('Export', 'woo-email-customizer-page-builder'); ?>" href="#">
        <i class="actions material-icons">import_export</i><?php esc_html_e('Export all templates', 'woo-email-customizer-page-builder'); ?>
    </a>
</div>
<br/>
<h4>
    <?php esc_html_e('Import templates', 'woo-email-customizer-page-builder'); ?>
</h4>
<div class="email-builder-settings-option">
    <form action="<?php echo admin_url("admin.php?page=woo_email_customizer_page_builder&settings=import_export_template"); ?>" method="post" enctype="multipart/form-data">
        <input type="file" name="import_file" id="import_file">
        <input type="hidden" name="import" value="1"/>
        <button type="submit" class="md-btn md-btn-default md-btn-mini" name="submit"><i class='actions material-icons'>import_export</i> <?php esc_html_e('Import', 'woo-email-customizer-page-builder'); ?></button>
    </form>
    </div>
