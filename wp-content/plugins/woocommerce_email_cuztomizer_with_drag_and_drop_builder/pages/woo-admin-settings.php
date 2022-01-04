<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$settings_page = $_REQUEST['settings'];
$woo_mb_settings = get_option('woo_mb_settings', '');
if ($woo_mb_settings != ''){
    $woo_mb_settings = json_decode($woo_mb_settings);
}
$woo_mb_settings_container_width = isset($woo_mb_settings->container_width)? $woo_mb_settings->container_width: '';
?>
<div class="wrap woo_email_customizer_settings_con">
    <!-- Prevent Alertifyjs AutoInject css -->
    <div id="alertifyCSS"></div>
    <span class="alertify-logs"></span>
    <h1 class="wp-heading-inline">
        <?php esc_html_e('WooCommerce Email Customizer - Settings', 'woo-email-customizer-page-builder'); ?>
    </h1>
    <div class="email-builder-header-banner-settings-con">
        <?php
        include WOO_ECPB_DIR.'/pages/notices/html-notice-emc-dashboard.php';
        ?>
    </div>
    <br>
    <a class="md-btn md-btn-default md-btn-mini" title="<?php esc_html_e('Return back to home', 'woo-email-customizer-page-builder'); ?>" href="<?php echo admin_url('admin.php?page=woo_email_customizer_page_builder')?>">
        <i class="material-icons"><?php esc_html_e('arrow_back', 'woo-email-customizer-page-builder'); ?></i>
    </a>
    <div class="woo_email_customizer_settings_nav_con" id="email-builder">
        <ul class="nav nav-tabs">
            <li<?php echo ($settings_page == 'default')? ' class="active"': ''; ?>><a href="<?php echo admin_url("admin.php?page=woo_email_customizer_page_builder&settings=default"); ?>"><?php esc_html_e('General', 'woo-email-customizer-page-builder'); ?></a></li>
            <li<?php echo ($settings_page == 'reset_template')? ' class="active"': ''; ?>><a href="<?php echo admin_url("admin.php?page=woo_email_customizer_page_builder&settings=reset_template"); ?>"><?php esc_html_e('Reset templates', 'woo-email-customizer-page-builder'); ?></a></li>
            <li<?php echo ($settings_page == 'import_export_template')? ' class="active"': ''; ?>><a href="<?php echo admin_url("admin.php?page=woo_email_customizer_page_builder&settings=import_export_template"); ?>"><?php esc_html_e('Import/Export templates', 'woo-email-customizer-page-builder'); ?></a></li>
            <li<?php echo ($settings_page == 'template_options')? ' class="active"': ''; ?>><a href="<?php echo admin_url("admin.php?page=woo_email_customizer_page_builder&settings=template_options"); ?>"><?php esc_html_e('Enable/Disable template', 'woo-email-customizer-page-builder'); ?></a></li>
            <?php $retainful_settings_page = WooEmailCustomizerIntegrationCouponAnalyticsRabbit::getSettingsPageURL(); ?>
            <li<?php echo ($settings_page == 'integrations')? ' class="active"': ''; ?>><a href="<?php echo $retainful_settings_page; ?>"><?php esc_html_e('Retainful - Next order coupon', 'woo-email-customizer-page-builder'); ?></a></li>
        </ul>
        <div class="clear"></div>
        <div class="nav-container ">
            <div id="alertifyCSS"></div>
            <span class="alertify-logs"></span>
            <div class="woo_emc_loader_outer">
                <?php require_once __DIR__ . '/settings/'.$settings_page.'.php'; ?>
                <div class="woo_emc_loader">
                    <div class="lds-ripple"><div></div><div></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
<div class="clear"></div>
<?php
$js = 'let order_info = [];';
$js .= 'let lang = "'.WOO_ECPB_LANG.'";
    let woo_email_customizer_user_mail = "'.wp_get_current_user()->user_email.'";
    let woo_email_customizer_ajax_url = "'.admin_url('admin-ajax.php').'";
    let woo_email_customizer_containerWidth = "'.$woo_mb_settings_container_width.'";';
wp_add_inline_script('woo-email', $js, 'after');
wp_enqueue_script('woo-email'); ?>