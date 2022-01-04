<?php defined( 'ABSPATH' ) || exit;

/**
 * @var Iconic_WSB_Order_Bump_Checkout $bump
 */

$settings       = $bump->get_render_settings();
$product        = $bump->get_product_offer();
$product_id     = $product ? $product->get_id() : false;
$cart_item      = Iconic_WSB_Cart::get_cart_item( "iconic_wsb_at_checkout" );
?>

<?php do_action( 'iconic-wsb-before-checkout-bump', $bump ); ?>

<div class="iconic-wsb-checkout-bump <?php echo $cart_item? "iconic-wsb-checkout-bump__overlay_active" : "";  ?>" style="border: 1px <?php echo esc_attr( $settings['border_style'] . ' ' . $settings['border_color'] ); ?>" data-product_type="<?php echo esc_attr($product->get_type()) ?>" >
	<div class="iconic-wsb-checkout-bump__header" style="border-bottom: 1px <?php echo esc_attr( $settings['border_style'] . ' ' . $settings['border_color'] ); ?>">
		<input class="iconic-wsb-checkout-bump__header-checkbox"
		       type="checkbox"
		       data-iconic-wsb-checkout-bump-trigger
		       id="iconic-wsb-checkout-bump-trigger"
			<?php checked( true, (bool) $cart_item_id ) ?> >
		<input type="hidden" name="iconic-wsb-bump-id" class="iconic-wsb__bump_id" value="<?php echo esc_attr( $bump->get_id() ); ?>">
		<input type="hidden" name="iconic-wsb-checkout-bump-action" value="">
		<input type="hidden" name='iconic-wsb-checkout-product-id' class="iconic-wsb__product_id" value='<?php echo esc_attr( $product_id ); ?>'>
		<input type="hidden" name="iconic-wsb-checkout-variation-id" class="iconic-wsb__variation_id" value="<?php echo $cart_item_id; ?>">
		<input type="hidden" name="iconic-wsb-checkout-variation-data" value="<?php echo esc_attr( json_encode( $variation_data ) ); ?>">
		<label class="iconic-wsb-checkout-bump__header-bump-title" for="iconic-wsb-checkout-bump-trigger" style="color: <?php echo esc_attr( $settings['highlight_color'] ); ?>;">
			<?php echo esc_html( $bump->get_checkbox_text( __( 'Customize the design and content of your order bump.', 'iconic-wsb' ) ) ); ?>
		</label>
	</div>
	<div class="iconic-wsb-checkout-bump__body">
		<?php if ( $settings['show_image'] === 'yes' ): ?>
			<div class="iconic-wsb-checkout-bump__product-aside">
				<div class="iconic-wsb-checkout-bump__product-image">
					<img class="iconic-wsb-checkout-bump__product-img"
					     src="<?php echo esc_url( $bump->get_offer_image_src() ); ?>"
					>
				</div>
			</div>
		<?php endif; ?>
		<div class="iconic-wsb-checkout-bump__main">
			<div class="iconic-wsb-checkout-bump__product"><?php echo wp_kses_post( $bump->get_bump_description() ); ?></div>
			<?php if ( $settings['show_price'] === 'yes' ): ?>
				<div class="iconic-wsb-checkout-bump__price">
					<?php if ( $bump->get_product_offer() ): ?>
						<span style="color: <?php echo esc_attr( $settings['highlight_color'] ); ?>;" class='iconic-wsb-checkout-bump__price_span'>
							<?php echo $price; ?>
						</span>
					<?php endif; ?>
				</div>
				<?php if ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ): ?>
					<div class="iconic-wsb-checkout-bump__variable">
						<table class="variations iconic-wsb-variation-table" cellspacing="0">
							<tbody>
								<?php
								$variable_product = $product->is_type( 'variable' ) ? $product : wc_get_product( $product->get_parent_id() );
								foreach ( $variable_product->get_variation_attributes() as $attribute_name => $options ) : ?>
									<?php $attribute_name_sanitized = sanitize_title( $attribute_name ); ?>
									<tr>
										<td class="label"><label for="<?php echo esc_attr( $attribute_name_sanitized ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></td>
										<td class="value">
											<?php
												$selected = false;

												if( $variation_data && ( $product->is_type( "variation" ) || $product->is_type( "variable" )) ) {
													$selected = isset( $variation_data[ $attribute_name_sanitized ] ) ? $variation_data[ $attribute_name_sanitized ] : false ;
												}

												Iconic_WSB_Helpers::wc_dropdown_variation_attribute_options( array(
													'options'   => $options,
													'attribute' => $attribute_name,
													'product'   => $product,
													'class'     => "iconic-wsb-variation__select iconic-wsb-checkout-bump__select",
													'selected'  => $selected,
													'readonly'  => 'readonly',
												) );
											?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<div class="iconic-wsb-checkout-bump__overlay" ></div>
					</div>
					<p class="wc-no-matching-variations woocommerce-info iconic-wsb-checkou-bump_unavailable_msg" style="display:none;"><?php _e('Sorry, this product is unavailable. Please choose a different combination.' , 'iconic-wsb' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php do_action( 'iconic-wsb-after-checkout-bump', $bump ); ?>

<?php if ( $settings['position'] === 'woocommerce_review_order_before_submit' ): ?>
	<style>
		#payment .place-order {
			background: none;
			padding: 0;
		}
	</style>
<?php endif; ?>
