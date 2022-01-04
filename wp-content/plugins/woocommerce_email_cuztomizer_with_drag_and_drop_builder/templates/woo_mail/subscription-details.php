<?php
/**
 * Load subscription details
 *
 * This template can be overridden by copying it to yourtheme/plugin-folder-name/woo_mail/subscription-details.php.
 * @var $order WooCommerce order
 * @var $subscription WooCommerce Subscription order
 * @var $sent_to_admin WooCommerce email send to admin
 * @var $plain_text WooCommerce email format
 * @var $email WooCommerce email object
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="td email_builder_table_items" cellspacing="0" cellpadding="6" style="width: 100%;" border="1">
    <thead>
    <tr>
        <th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Subscription', 'woo-email-customizer-page-builder' ); ?></th>
        <th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Price', 'table headings in notification email', 'woo-email-customizer-page-builder' ); ?></th>
        <th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Last Order Date', 'table heading', 'woo-email-customizer-page-builder' ); ?></th>
        <th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'End of Prepaid Term', 'table headings in notification email', 'woo-email-customizer-page-builder' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td class="td" width="1%" style="text-align:left; vertical-align:middle;">
            <a href="<?php echo esc_url( wcs_get_edit_post_link( $subscription->get_id() ) ); ?>">#<?php echo esc_html( $subscription->get_order_number() ); ?></a>
        </td>
        <td class="td" style="text-align:left; vertical-align:middle;">
            <?php echo wp_kses_post( $subscription->get_formatted_order_total() ); ?>
        </td>
        <td class="td" style="text-align:left; vertical-align:middle;">
            <?php
            $last_order_time_created = $subscription->get_time( 'last_order_date_created', 'site' );
            if ( ! empty( $last_order_time_created ) ) {
                echo esc_html( date_i18n( wc_date_format(), $last_order_time_created ) );
            } else {
                esc_html_e( '-', 'woocommerce-subscriptions' );
            }
            ?>
        </td>
        <td class="td" style="text-align:left; vertical-align:middle;">
            <?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) ); ?>
        </td>
    </tr>
    </tbody>
</table>
