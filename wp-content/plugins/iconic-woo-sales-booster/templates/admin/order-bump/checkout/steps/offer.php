<?php defined( 'ABSPATH' ) || exit;
/**
 * @var Iconic_WSB_Order_Bump_Checkout_Abstract $bump
 */
?>
<div class="iconic-wsb-edit-step" data-iconic-wsb-offer-scope>
	<h2 class="iconic-wsb-edit-step__header">
		<?php _e( 'Create Offer', 'iconic-wsb' ); ?>
	</h2>
	<p class="iconic-wsb-edit-step__description">
		<?php _e( 'Select a product to offer during checkout if the conditions are met.', 'iconic-wsb' ); ?>
	</p>
	<div class="iconic-wsb-edit-step__body">
		<div class="iconic-wsb-form">
			<div class="iconic-wsb-form__row">
				<div class="iconic-wsb-form__inner">
					<select class="iconic-wsb-form__product-search wc-product-search"
					        id="data-iconic-wsb-offer-product-search"
					        data-iconic-wsb-offer-product
					        data-exclude="<?php echo esc_attr( join( ',', $bump->get_specific_products( [] ) ) ); ?>"
					        data-minimum_input_length="1"
					        name="iconic_wsb_product_offer"
					        required
					        data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
					        data-action="woocommerce_json_search_products_and_variations">
						<?php if ( $bump->get_product_offer() ): $product = $bump->get_product_offer(); ?>
							<option selected value="<?php echo esc_attr( $product->get_id() ); ?>">
								<?php echo esc_html( $product->get_formatted_name() ); ?>
							</option>
						<?php endif; ?>
					</select>
				</div>
			</div>
			<div class="iconic-wsb-form__row">
				<label class="iconic-wsb-form__label"
				       for="iconic_wsb_discount"
				>
					<?php _e( 'Discount (optional):', 'iconic-wsb' ); ?>
				</label>

				<div class="iconic-wsb-form__inner">
					<?php $i18nValidation = array(
						'max'        => __( 'Max quantity: ', 'iconic-wsb' ),
						'min'        => __( 'Min quantity: ', 'iconic-wsb' ),
						'onlyNumber' => __( 'Numbers only', 'iconic-wsb' ),
					); ?>
					<span class="iconic-wsb-quantity" data-quantity-validation="<?php echo esc_attr( htmlspecialchars( json_encode( $i18nValidation ) ) ); ?>">
                        <input class="iconic-wsb-form__control"
                               type="number"
                               data-quantity-field
                               id="iconic_wsb_discount"
                               min="0"
	                        <?php if ( $product = $bump->get_product_offer() ):; ?>
		                        max="<?php echo esc_attr( $bump->get_discount_type() === 'percentage' ? '100' : $product->get_price() ); ?>"
	                        <?php endif; ?>
	                           step="0.01"
	                           name="iconic_wsb_discount"
	                           data-iconic-wsb-discount-value
	                           value="<?php echo esc_attr( $bump->get_discount() ); ?>"
                        >
                    </span>
					<select class="iconic-wsb-form__control"
					        name="iconic_wsb_discount_type"
					        required
					        data-iconic-wsb-discount-type
					        data-percentage-max="100"
						<?php if ( $product = $bump->get_product_offer() ):; ?>
							data-simple-max="<?php echo esc_attr( $product->get_price() ); ?>"
						<?php endif; ?>
					>
						<option value="percentage" <?php selected( 'percentage', $bump->get_discount_type() ); ?> ><?php esc_html_e( 'Percent', 'iconic_wsb' ); ?></option>
						<option value="simple" <?php selected( 'simple', $bump->get_discount_type() ); ?> ><?php echo get_woocommerce_currency_symbol(); ?></option>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
