<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$woo_mb_settings_analytics_rabbit = get_option('woo_mb_settings_analytics_rabbit', '');
if ($woo_mb_settings_analytics_rabbit != ''){
    $woo_mb_settings_analytics_rabbit = json_decode($woo_mb_settings_analytics_rabbit);
}
$app_id = isset($woo_mb_settings_analytics_rabbit->app_id)? $woo_mb_settings_analytics_rabbit->app_id: '';
if(trim($app_id) != '' && false){ //This have been disabled from v1.5.12
    ?>
    <h3><?php esc_html_e('Next order coupon settings', 'woo-email-customizer-page-builder'); ?></h3>

    <form name="settings" id="woo-mail-settings" action="#">
        <div class="email-builder-settings-option">
            <div style="float: right;background: #fff;border: 1px solid #eee;color:#333;padding: 20px;display:inline-block;max-width: 300px;text-align:center;border-radius: 4px;box-shadow: 0 0 5px 0 #ddd;margin: 0 5px;">
                <p style="font-family:'helvetica',sans-serif;margin: 0 0 15px;"><img src="<?php echo WOO_ECPB_URI . '/assets/images/retainful-logo.png'; ?>" style="max-width: 150px;height: auto;" alt=""></p>
                <h3 style="flex: 1;font-family:'helvetica',sans-serif;margin: 0;font-weight: 600;font-size:23px;color: #333;line-height:1.3;">Get your API Key for free</h3>
                <p style="font-family:'helvetica',sans-serif;margin: 10px 0;color:#777;font-size: 16px;line-height:1.5;">
                    Increase sales & get more money per customer. Drive repeat purchases by automatically sending a single-use coupon for next purchase.
                </p>
                <p style="font-family:'helvetica',sans-serif;margin: 15px 0 0;"><a href="<?php echo WooEmailCustomizerIntegrationCouponAnalyticsRabbit::get_dashboard_url(); ?>" target="_blank" style="font-family:'helvetica',sans-serif;display: inline-block;padding: 10px 20px;text-decoration: none;color:#fff;background:#F27052;border-radius: 4px;font-weight: 500;line-height:1.6;font-size: 16px;">Get your API Key</a></p>
                <p style="font-family:'helvetica',sans-serif;margin: 0 0 20px;display: flex;align-items: center;color: #777777;margin: 15px 0 0;justify-content: flex-end;font-size: 13px;">a product from &nbsp;<a href="https://www.flycart.org" target="_blank" style="color: #F27052;text-decoration: none;">Flycart</a></p>
            </div>
            <div class="email-builder-settings_fields show_product_image_option">
                <label class="settings-label" for="settings_product_image_height">
                    <?php esc_html_e('App id', 'woo-email-customizer-page-builder'); ?>
                </label>
                <?php
                $app_id = isset($woo_mb_settings_analytics_rabbit->app_id)? $woo_mb_settings_analytics_rabbit->app_id: '';
                $validated_app_id = isset($woo_mb_settings_analytics_rabbit->validated_app_id)? $woo_mb_settings_analytics_rabbit->validated_app_id: 0;
                ?>
                <input type="text" name="settings_integration[app_id]" id="settings_product_image_height" value="<?php echo $app_id; ?>"/>
                <a class="md-btn md-btn-default md-btn-success md-btn-micro md-btn-connect" onclick="woo_email_customizer_saveWooEmailCustomizerSettings(this)" ref="saveWooEmailCustomizerSettings" data-btn_type="next_order_coupon" data-url="#" title="Save settings" href="#">
                    Connect
                </a>
                <?php if(!empty($app_id)){
                    ?>
                    <span class="validated_next_order_coupon_text validated <?php echo ($validated_app_id)? 'show': 'hide'?>"><?php esc_html_e('Connected', 'woo-email-customizer-page-builder'); ?></span>
                    <span class="validated_next_order_coupon_text invalid <?php echo ($validated_app_id)? 'hide': 'show'?>"><?php esc_html_e('Not connected', 'woo-email-customizer-page-builder'); ?></span></span>
                    <?php
                } ?>
            </div>
            <div class="email-builder-settings_fields show_product_image_option">
                <label class="settings-label" for="settings_product_image_height">
                    <?php esc_html_e('Discount type', 'woo-email-customizer-page-builder'); ?>
                </label>
                <?php
                $discount_type = isset($woo_mb_settings_analytics_rabbit->discount_type)? $woo_mb_settings_analytics_rabbit->discount_type: 'percent';
                ?>
                <select name="settings_integration[discount_type]" id="settings_product_image_size">
                    <option <?php echo ($discount_type == 'percent')? 'selected="selected"': ''; ?> value="percent"><?php esc_html_e('Percentage', 'woo-email-customizer-page-builder'); ?></option>
                    <option <?php echo ($discount_type == 'fixed')? 'selected="selected"': ''; ?> value="fixed"><?php esc_html_e('Fixed', 'woo-email-customizer-page-builder'); ?></option>
                </select>
            </div>
            <div class="email-builder-settings_fields show_product_image_option">
                <label class="settings-label" for="settings_product_image_height">
                    <?php esc_html_e('Discount value', 'woo-email-customizer-page-builder'); ?>
                </label>
                <?php
                $discount_value = isset($woo_mb_settings_analytics_rabbit->discount_value)? $woo_mb_settings_analytics_rabbit->discount_value: '';
                ?>
                <input type="text" name="settings_integration[discount_value]" id="settings_product_image_height" value="<?php echo $discount_value; ?>"/>
            </div>
            <div class="email-builder-settings_fields">
                <label class="settings-label" for="settings_use_coupon_by">
                    <?php esc_html_e('Retainful coupon flow', 'woo-email-customizer-page-builder'); ?>
                </label>
                <?php
                $use_coupon_by = isset($woo_mb_settings_analytics_rabbit->use_coupon_by)? $woo_mb_settings_analytics_rabbit->use_coupon_by: 'all';
                ?>
                <select name="settings_integration[use_coupon_by]" id="settings_use_coupon_by">
                    <option <?php echo ($use_coupon_by == 'all')? 'selected="selected"': ''; ?> value="all"><?php esc_html_e('Allow any one to apply coupon', 'woo-email-customizer-page-builder'); ?></option>
                    <option <?php echo ($use_coupon_by == 'validate_on_checkout')? 'selected="selected"': ''; ?> value="validate_on_checkout"><?php esc_html_e('Allow the customer to apply coupon, but validate at checkout', 'woo-email-customizer-page-builder'); ?></option>
                    <option <?php echo ($use_coupon_by == 'login_users')? 'selected="selected"': ''; ?> value="login_users"><?php esc_html_e('Allow customer to apply coupon only after login', 'woo-email-customizer-page-builder'); ?></option>
                </select>
            </div>
            <input type="hidden" name="settings_for_api_integration" value="1"/>
            <div class="email-builder-settings_fields">
                <a class="md-btn md-btn-default md-btn-mini" onclick="woo_email_customizer_saveWooEmailCustomizerSettings()" ref="saveWooEmailCustomizerSettings" data-url="#" title="<?php esc_html_e('Save settings', 'woo-email-customizer-page-builder'); ?>" href="#">
                    <i class="actions md-icon material-icons save md-color-green-600">save</i>
                </a>
            </div>
        </div>
    </form>
<?php
} else {
    ?>
    <div style="background: #fff;border: 1px solid #eee;color:#333;padding: 20px;display:inline-block;max-width: 100%;text-align:center;border-radius: 4px;box-shadow: 0 0 5px 0 #ddd;margin: 0 5px;">
        <p style="font-family:'helvetica',sans-serif;margin: 0 0 15px;"><img src="<?php echo WOO_ECPB_URI . '/assets/images/retainful-logo.png'; ?>" style="max-width: 150px;height: auto;" alt=""></p>
        <h3 style="flex: 1;font-family:'helvetica',sans-serif;margin: 0;font-weight: 600;font-size:23px;color: #333;line-height:1.3;">Enable the Next Order Coupon by installing the Retainful Plugin</h3>
        <p style="font-family:'helvetica',sans-serif;margin: 15px 0 0;"><a href="<?php echo WooEmailCustomizerIntegrationCouponAnalyticsRabbit::get_download_url(); ?>" target="_blank" style="font-family:'helvetica',sans-serif;display: inline-block;padding: 10px 20px;text-decoration: none;color:#fff;background:#F27052;border-radius: 4px;font-weight: 500;line-height:1.6;font-size: 16px;">Download Retainful Free</a></p>
        <p style="font-family:'helvetica',sans-serif;margin: 10px 0;color:#777;font-size: 16px;line-height:1.5;">
            <b>IMPORTANT:</b> If you are already using Next Order Coupon with Retainful, please install the latest version of Retainful plugin as its API has undergone a number of improvements and more options are available for Next Order Coupon. So these options can be effectively managed after installing the Retainful plugin
        </p>
        <p style="font-family:'helvetica',sans-serif;margin: 0 0 20px;display: flex;align-items: center;color: #777777;margin: 15px 0 0;justify-content: flex-end;font-size: 13px;">a product from &nbsp;<a href="https://www.flycart.org" target="_blank" style="color: #F27052;text-decoration: none;">Flycart</a></p>
    </div>
<?php
}
?>
