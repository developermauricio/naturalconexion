<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$has_enabled_next_order_coupon = WooEmailCustomizerIntegrationCouponAnalyticsRabbit::isAPIIntegrationEnabled();
if(!$has_enabled_next_order_coupon){
    ?>
    <div id="message" class="woo_email_customizer-message updated woo_email_customizer-message--success">
        <a class="woo_email_customizer-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wemc-hide-notice', 'dashboard' ), 'woo_email_customizer_hide_notices_nonce', '_wemc_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'woo-email-customizer-page-builder' ); ?></a>

        <div class="woo_email_customizerwoo_email_customizer-content">
            <div>
                <div class="woo_customizer-content">
                    <h2>Drive repeat purchases by sending a unique <strong>‘Next purchase coupon’</strong> in the order email notifications. Email Customizer is now integrated with Retainful by Flycart. It’s free.  Add the short code now <span class="highlight"><strong>[wec_next_order_coupon]</strong></span></h2>
                </div>
                <div class="woo_email_customizerwoo_email_customizer-action">
                    <a class="button button-primary" href="admin.php?page=woo_email_customizer_page_builder">Open Email Customizer  & Add the Shortcode</a>
                </div>
            </div>
            <div class="retainful-footer-logo">
                <p>powered by  <a href="https://www.retainful.com/" target="_blank"><img src="<?php echo WOO_ECPB_URI . '/assets/images/retainful-logo.png'; ?>" alt="retainful-logo" /></a>
                <br/> a product from <a href="https://www.flycart.org" target="_blank">Flycart</a></p></div>
        </div>
    </div>
    <?php
}
?>
