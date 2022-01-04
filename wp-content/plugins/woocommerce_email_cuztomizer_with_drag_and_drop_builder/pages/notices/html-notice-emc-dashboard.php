<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$display_notice = WooEmailCustomizerNotices::display_notice_in_customizer_page();
$has_enabled_next_order_coupon = WooEmailCustomizerIntegrationCouponAnalyticsRabbit::isAPIIntegrationEnabled();
if(!$has_enabled_next_order_coupon && $display_notice){
    ?>
    <div class="email-builder-header-banner">
        <a class="woo_email_customizer-message-close notice-dismiss" href="<?php echo esc_url_raw( wp_nonce_url( add_query_arg( 'wemc-hide-notice', 'customizer_page' ), 'woo_email_customizer_hide_notices_nonce', '_wemc_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'woo-email-customizer-page-builder' ); ?></a>

        <h2>Send <strong>single-use, unique coupon codes</strong> automatically to customers within order emails for their next purchases. Drag and drop the coupon box in your email template. We integrated with our new product Retainful.</h2>
		<div class="woo_email_customizer_action">
			<a class="button button-primary" href="<?php echo WooEmailCustomizerIntegrationCouponAnalyticsRabbit::get_dashboard_url(); ?>" target="_blank">Start using it free</a>
		</div>
        <div class="retainful-footer-logo">
            <p>powered by  <a href="https://www.retainful.com/" target="_blank"><img src="<?php echo WOO_ECPB_URI . '/assets/images/retainful-logo.png'; ?>" alt="retainful-logo" /></a>
                <br/> a product from <a href="https://www.flycart.org" target="_blank">Flycart</a></p></div>
    </div>
    <?php
}
?>
