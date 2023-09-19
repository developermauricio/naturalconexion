<?php
/**
 * Bundled item.
 *
 * @var int                  $metabox_id   The meta-box ID.
 * @var YITH_WC_Bundled_Item $bundled_item The bundled item.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Views
 */

defined( 'YITH_WCPB' ) || exit;

$product_id     = $bundled_item->get_product_id();
$product        = $bundled_item->get_product();
$bundle_product = $bundled_item->get_bundle();
$bundle_id      = $bundle_product->get_id();

// phpcs:disable WordPress.Security.NonceVerification

$open_closed    = ! empty( $_POST['open_closed'] ) ? wc_clean( wp_unslash( $_POST['open_closed'] ) ) : 'closed';
$content_hidden = 'closed' === $open_closed ? 'hidden' : '';

$the_title = $product ? $product->get_name() : get_the_title( $product_id );

$edit_link  = get_edit_post_link( $product_id );
$item_title = apply_filters( 'yith_wcpb_bundle_item_title', sprintf( '%s &ndash; #%s', $the_title, $product_id ), $the_title, $product_id );

if ( ! $product ) {
	$is_purchasable = false;
} elseif ( $product->is_type( 'variable' ) ) {
	$is_purchasable = true;
} else {
	$is_purchasable = $product->is_purchasable();
}
?>
<div class="yith-wcpb-bundled-item wc-metabox <?php echo esc_attr( $open_closed ); ?>" rel="<?php echo esc_attr( $metabox_id ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<h3>
		<div class="yith-wcpb-bundled-item__handlediv yith-icon yith-icon-arrow-down" title="<?php esc_html_e( 'Click to toggle', 'yith-woocommerce-product-bundles' ); ?>"></div>
		<span class="yith-wcpb-remove-bundled-product-item"><?php esc_html_e( 'Remove', 'yith-woocommerce-product-bundles' ); ?></span>
		<a class="yith-wcbep-edit-product-btn" target="_blank" href="<?php echo esc_url_raw( $edit_link ); ?>"><?php esc_html_e( 'Edit', 'yith-woocommerce-product-bundles' ); ?></a>
		<strong class="item-title"><?php echo esc_html( $item_title ); ?></strong>
		<?php if ( ! $is_purchasable ) : ?>
			<span class="yith-wcpb-bundled-items-info not-purchasable">
				<?php esc_html_e( 'Not Purchasable', 'yith-woocommerce-product-bundles' ); ?>
			</span>
		<?php endif; ?>
	</h3>
	<div class="yith-wcpb-bundled-item-data wc-metabox-content <?php echo esc_attr( $content_hidden ); ?>">
		<div class="yith-wcpb-bundled-item-data-content">
			<input type="hidden" name="_yith_wcpb_bundle_data[<?php echo esc_attr( $metabox_id ); ?>][bundle_order]" class="yith-wcpb-bundled-item-position" value="<?php echo esc_attr( $metabox_id ); ?>"/>
			<input type="hidden" name="_yith_wcpb_bundle_data[<?php echo esc_attr( $metabox_id ); ?>][product_id]" class="yith-wcpb-product-id" value="<?php echo esc_attr( $product_id ); ?>"/>

			<?php do_action( 'yith_wcpb_admin_bundled_item_options', $bundled_item, $metabox_id ); ?>
		</div>
	</div>
</div>
