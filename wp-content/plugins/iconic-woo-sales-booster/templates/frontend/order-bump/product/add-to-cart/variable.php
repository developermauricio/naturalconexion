<?php defined( 'ABSPATH' ) || exit;
/**
 * @var WC_Product_Variable $product
 */
$available_variations = $product->get_available_variations();
$attributes           = $product->get_variation_attributes();
$selected_attributes  = $product->get_default_attributes();
$attribute_keys       = array_keys( $attributes );
?>

<form class="iconic-wsb-modal-product__add-to-cart-form variations_form cart"
      action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>"
      data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ); ?>">
	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock">
			<?php esc_html_e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?>
		</p>
	<?php else : ?>
		<table class="iconic-wsb-modal-product__variations-table variations" cellspacing="0">
			<tbody>
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<tr>
					<td class="iconic-wsb-modal-product__variations-table-cell value">
						<?php
						wc_dropdown_variation_attribute_options( array(
							'options'          => $options,
							'attribute'        => $attribute_name,
							'product'          => $product,
							'show_option_none' => strip_tags( wc_attribute_label( $attribute_name ) ),
							'class'            => 'iconic-wsb-modal-product__variations-select',
						) );
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div class="single_variation_wrap">
			<div class="woocommerce-variation single_variation"></div>
			<div class="woocommerce-variation-add-to-cart variations_button">
				<button type="submit" class="iconic-wsb-modal-product__add-to-cart single_add_to_cart_button button alt">
					<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
				</button>
				<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
				<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
				<input type="hidden" name="variation_id" class="variation_id" value="0" />
			</div>
		</div>
	<?php endif; ?>
</form>

