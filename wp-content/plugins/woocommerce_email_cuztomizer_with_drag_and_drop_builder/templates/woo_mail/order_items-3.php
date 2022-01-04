<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/plugin-folder-name/woo_mail/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$obj = new stdClass();
$sent_to_admin = (isset($sent_to_admin) ? $sent_to_admin : false);
$email = (isset($email) ? $email : '');
$plain_text = (isset($plain_text) ? $plain_text : '');
$woo_mb_settings = get_option('woo_mb_settings', '');
if ($woo_mb_settings != ''){
	$woo_mb_settings = json_decode($woo_mb_settings);
}
$show_payment_instruction = isset($woo_mb_settings->show_payment_instruction)? $woo_mb_settings->show_payment_instruction: 1;
$show_product_sku = isset($woo_mb_settings->show_product_sku)? $woo_mb_settings->show_product_sku: 0;
if($show_product_sku == 0){
    $show_product_sku = $sent_to_admin;
}
if($show_payment_instruction == 1 || ($show_payment_instruction == 2 && !$sent_to_admin)){
	do_action( 'woocommerce_email_before_order_table', (isset($order) ? $order : $obj), $sent_to_admin, $plain_text, $email); 
} ?>
<?php if ( ! $sent_to_admin ) : ?>
	<h2><?php printf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ); ?></h2>
<?php else : ?>
	<h2><a class="link" href="<?php echo esc_url( admin_url( 'post.php?post=' . ($order->get_id() ? $order->get_id() : '') . '&action=edit' ) ); ?>"><?php printf( __( 'Order #%s', 'woocommerce'), $order->get_order_number() ); ?></a> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order->get_date_created()) ), date_i18n( wc_date_format(), strtotime( $order->get_date_created() ) ) ); ?>)</h2>
<?php endif; ?>

<table class="email_builder_table_items" cellspacing="0" cellpadding="6" style="width: 100% !important;" border="1" width="100%">
	<thead>
	<tr>
		<th class="td" scope="col" style="text-align:left;"><?php _e( 'Product', 'woocommerce' ); ?></th>
		<th class="td" scope="col" style="text-align:left;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
		<th class="td" scope="col" style="text-align:left;"><?php _e( 'Price', 'woocommerce' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
    // load order item table
    $this->getOrdetItemTables($order, array(
        'show_sku'      => $show_product_sku,
        'show_image'    => false,
        'image_size'    => array( 32, 32 ),
        'plain_text'    => $plain_text,
        'sent_to_admin' => $sent_to_admin
    ));
	?>
	</tbody>
	<tfoot>
	<?php
	if ( $totals = $order->get_order_item_totals() ) {
		$i = 0;
		foreach ( $totals as $total ) {
			$i++;
			?><tr>
			<th class="td" scope="row" colspan="2" style="text-align:left; <?php if ( $i === 1 ) echo 'border-top-width: 1px'; ?>"><?php echo $total['label']; ?></th>
			<td class="td" style="text-align:left; <?php if ( $i === 1 ) echo 'border-top-width: 1px;'; ?>"><?php echo $total['value']; ?></td>
			</tr><?php
		}
	}
	?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
