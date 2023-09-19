<?php
/**
 * Admin bundle options tab
 *
 * @var WC_Product_YITH_Bundle|false $bundle_product The bundle product or false (if it's not a bundle product)
 * @var WC_Product                   $product_object The product object
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Views
 */

defined( 'YITH_WCPB' ) || exit;

$bundle_id = $product_object->get_id();

$bundle_data = $product_object->get_meta( '_yith_wcpb_bundle_data', true, 'edit' );
$bundle_data = ! ! $bundle_data ? $bundle_data : array();

$bundled_items = $bundle_product ? $bundle_product->get_bundled_items() : array();

$items_with_qty = array();
foreach ( $bundled_items as $bundled_item ) {
	$_product_id = $bundled_item->get_product_id();
	if ( isset( $items_with_qty[ $_product_id ] ) ) {
		$items_with_qty[ $_product_id ] ++;
	} else {
		$items_with_qty[ $_product_id ] = 1;
	}
}

?>
<div id="yith_bundle_product_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper yith-plugin-ui" data-items-with-qty="<?php echo esc_attr( wp_json_encode( $items_with_qty ) ); ?>">

	<div class="yith-wcpb-bundle-options-section">

		<div class="yith-wcpb-bundle-options-section__title">
			<h3><?php esc_attr_e( 'Bundled items', 'yith-woocommerce-product-bundles' ); ?></h3>
			<span id="yith-wcpb-bundled-items-expand-collapse" class="yith-wcpb-expand-collapse">
				<a href="#" class="close_all"><?php esc_attr_e( 'Close all', 'yith-woocommerce-product-bundles' ); ?></a>
				<a href="#" class="expand_all"><?php esc_attr_e( 'Expand all', 'yith-woocommerce-product-bundles' ); ?></a>
			</span>
		</div>


		<div class="yith-wcpb-bundle-options-section__content">

			<div id="yith-wcpb-bundled-items__actions" class="yith-wcpb-bundled-items__actions">
				<div id="yith-wcpb-bundled-items__actions__hero-icon" class="yith-wcpb-bundled-items__actions__show-if-hero">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 82.5 80.23">
						<defs>
							<style>
								.cls-1, .cls-2 {
									fill            : none;
									stroke          : currentColor;
									stroke-linecap  : round;
									stroke-linejoin : round;
									stroke-width    : 2.5px;
								}

								.cls-2 {
									stroke-dasharray : 0.51 6.14;
								}
							</style>
						</defs>
						<g>
							<g>
								<polyline class="cls-1" points="72.78 46.51 72.78 63.92 42.66 78.98 10.66 63.92 10.66 46.68"/>
								<polyline class="cls-1" points="10.66 30.04 42.66 43.21 72.78 30.04"/>
								<line class="cls-1" x1="42.66" y1="43.21" x2="42.66" y2="78.98"/>
								<polyline class="cls-1" points="42.66 43.21 33.25 57.33 1.25 42.27 10.66 30.04"/>
								<line class="cls-1" x1="64.69" y1="27.34" x2="72.78" y2="30.04"/>
								<line class="cls-1" x1="10.66" y1="30.04" x2="18.75" y2="27.34"/>
								<polyline class="cls-1" points="72.78 30.04 81.25 42.27 53.02 56.39 42.66 43.21"/>
								<line class="cls-1" x1="31.24" y1="30.58" x2="31.24" y2="18.24"/>
								<line class="cls-1" x1="52.2" y1="7.7" x2="52.2" y2="26.56"/>
								<line class="cls-1" x1="45.22" y1="14.56" x2="45.22" y2="34.28"/>
								<line class="cls-1" x1="38.23" y1="11.71" x2="38.23" y2="24.23"/>
								<line class="cls-2" x1="45.22" y1="1.25" x2="45.22" y2="11.38"/>
								<line class="cls-2" x1="31.24" y1="11.94" x2="31.24" y2="3.48"/>
								<line class="cls-1" x1="59.19" y1="28.44" x2="59.19" y2="19.09"/>
								<line class="cls-2" x1="59.19" y1="12.79" x2="59.19" y2="11.14"/>
								<line class="cls-2" x1="24.25" y1="13.64" x2="24.25" y2="11.99"/>
								<line class="cls-1" x1="24.25" y1="27.59" x2="24.25" y2="20.01"/>
							</g>
						</g>
					</svg>
				</div>
				<div id="yith-wcpb-bundled-items__actions__hero-description" class="yith-wcpb-bundled-items__actions__show-if-hero">
					<?php esc_html_e( 'You are creating a bundle product!', 'yith-woocommerce-product-bundles' ); ?>
					<br/>
					<?php esc_html_e( 'Now, the first step is adding some items to this bundle: after that, you should simply set the bundle options below.', 'yith-woocommerce-product-bundles' ); ?>
				</div>
				<button type="button" id="yith-wcpb-add-bundled-product" class="button button-primary"><?php esc_html_e( 'Add product to the bundle', 'yith-woocommerce-product-bundles' ); ?></button>
			</div>

			<div class="yith-wcpb-bundled-items wc-metaboxes">
				<?php
				$metabox_id = 1;
				foreach ( $bundled_items as $bundled_item ) {
					$open_closed = 'closed';
					yith_wcpb_get_view( '/admin/bundled-item.php', compact( 'metabox_id', 'bundled_item', 'open_closed' ) );
					$metabox_id ++;
				}
				?>
			</div>
		</div>
	</div>

	<div class="yith-wcpb-bundle-options-section">

		<div class="yith-wcpb-bundle-options-section__title">
			<h3><?php esc_attr_e( 'Bundle Options', 'yith-woocommerce-product-bundles' ); ?></h3>
		</div>

		<div class="yith-wcpb-bundle-options-section__content">

			<?php do_action( 'yith_wcpb_before_product_bundle_options_tab' ); ?>

			<div class="yith-wcpb-form-field">
				<label class="yith-wcpb-form-field__label">
					<?php
					// translators: %s is the currency symbol.
					echo esc_html( sprintf( __( 'Bundle Regular Price (%s)', 'yith-woocommerce-product-bundles' ), get_woocommerce_currency_symbol() ) );
					?>
				</label>
				<div class="yith-wcpb-form-field__content">
					<?php
					yith_plugin_fw_get_field(
						array(
							'type'  => 'text',
							'name'  => '_regular_price',
							'id'    => '_yith_wcpb_bundle_regular_price',
							'class' => 'short wc_input_price yith-wcpb-short-price-field',
							'value' => wc_format_localized_price( $product_object->get_regular_price( 'edit' ) ),
						),
						true
					);
					?>
				</div>
				<div class='yith-wcpb-form-field__description'>
					<?php esc_html_e( 'Enter the price of this bundle.', 'yith-woocommerce-product-bundles' ); ?>
				</div>
			</div>

			<div class="yith-wcpb-form-field">
				<label class="yith-wcpb-form-field__label">
					<?php
					// translators: %s is the currency symbol.
					echo esc_html( sprintf( __( 'Bundle Sale Price (%s)', 'yith-woocommerce-product-bundles' ), get_woocommerce_currency_symbol() ) );
					?>
				</label>
				<div class="yith-wcpb-form-field__content">
					<?php
					yith_plugin_fw_get_field(
						array(
							'type'  => 'text',
							'name'  => '_sale_price',
							'id'    => '_yith_wcpb_bundle_sale_price',
							'class' => 'short wc_input_price yith-wcpb-short-price-field',
							'value' => wc_format_localized_price( $product_object->get_sale_price( 'edit' ) ),
						),
						true
					);
					?>
				</div>
				<div class='yith-wcpb-form-field__description'>
					<?php esc_html_e( 'Enter an optional sale price to show a discount for this bundle.', 'yith-woocommerce-product-bundles' ); ?>
				</div>
			</div>

			<?php do_action( 'yith_wcpb_after_product_bundle_options_tab' ); ?>
		</div>
	</div>

</div>
