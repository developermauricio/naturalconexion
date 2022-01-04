<?php defined( 'ABSPATH' ) || exit;
/**
 * @var WC_Product   $bump_product
 * @var WC_Product[] $bump_products
 * @var array        $settings
 * @var WC_Product   $product
 * @var string       $total_price
 * @var string       $discount_message
 */
?>
<div class="iconic-wsb-product-bumps <?php echo $sales_pitch ? 'iconic-wsb-product-bumps--has-sales-pitch' : ''; ?>">
	<?php if ( ! empty( $title ) ) { ?>
		<div class="iconic-wsb-product-bumps__header">
			<h3 class="iconic-wsb-product-bumps__title">
				<?php echo wp_kses_post( $title ); ?>
			</h3>
		</div>
	<?php } ?>

	<div class="iconic-wsb-product-bumps__body">
		<?php if ( $settings['show_product_thumbnail'] == 1 ): ?>
			<ul class="iconic-wsb-product-bumps__images">
				<?php foreach ( $bump_products as $bump_product ) { ?>
					<li class="iconic-wsb-product-bumps__image" data-product-id="<?php echo esc_attr( $bump_product->get_ID() ); ?>">
						<?php echo wp_kses_post( $bump_product->get_image( [ 60, 60 ] ) ); ?>
					</li>
				<?php } ?>
			</ul>
		<?php endif; ?>

		<?php if ( $sales_pitch ) { ?>
			<div class="iconic-wsb-product-bumps__sales_pitch">
				<p><?php echo esc_html( $sales_pitch ); ?></p>
			</div>
		<?php } ?>

		<ul class="iconic-wsb-product-bumps__list">
			<?php foreach ( $bump_products as $bump_product ) { ?>
				<?php
					$this_item = $bump_product->get_id() === $product->get_id();
					$checked   = in_array( $bump_product->get_id(), $checked_products );
				?>
				<li class="iconic-wsb-product-bumps__list-item" data-product_id="<?php echo esc_attr( $bump_product->get_ID() ); ?>" data-product_type="<?php echo esc_attr( $bump_product->get_type() ); ?>">
					<div class="iconic-wsb-bump-product">
						<div class="iconic-wsb-bump-product__body">
							<label>
								<input 
									type="checkbox" 
									class="iconic-wsb-bump-product__checkbox" 
									name="iconic-wsb-products-add-to-cart[]" 
									value="<?php echo esc_attr( $bump_product->get_id() ); ?>" 
									<?php checked( $checked, true ); ?>
								/>

								<?php if ( $this_item ) { ?>
									<strong class="iconic-wsb-bump-product__title iconic-wsb-bump-product__title--this-item"><?php esc_html_e( 'This item', 'iconic-wsb' ); ?>: <?php echo esc_html( $bump_product->get_title() ); ?></strong>
								<?php } elseif ( '1' === $settings['link_product_titles'] ) { ?>
									<a class="iconic-wsb-bump-product__title iconic-wsb-bump-product__title--link"
										href="<?php echo esc_url( $bump_product->get_permalink() ); ?>">
										<?php echo esc_html( $bump_product->get_title() ); ?>
									</a>
								<?php } else { ?>
									<span class="iconic-wsb-bump-product__title"><?php echo $bump_product->get_title(); ?></span>
								<?php } ?>

								<span class="iconic-wsb-bump-product__price"><?php echo Iconic_WSB_Order_Bump_Product_Page_Manager::get_price_html( $bump_product ); ?></span>

								<?php if ( $bump_product->is_type( 'variable' ) ) {
									$variations = Iconic_WSB_Order_Bump_Product_Page_Manager::get_variations( $bump_product );
									?>
									<select
										class='iconic-wsb-bump-product__select iconic-wsb-bump-product__select--<?php echo esc_attr( $bump_product->get_id() ); ?>'
										name='iconic-wsb-products-add-to-cart-variation-<?php echo esc_attr( $bump_product->get_id() ); ?>'
										data-product_id="<?php echo esc_attr( $bump_product->get_id() ); ?>">

										<option disabled selected value><?php echo Iconic_WSB_Order_Bump_Product_Page_Manager::get_variable_dropdown_placeholder( $bump_product ); ?></option>

										<?php

										foreach ( $variations as $variation ) {
											$option_attributes = array();
											foreach ( $variation['attributes'] as $attribute_key => $attribute_value ) {
												$option_attributes[] = $attribute_value;
											}
											$option_string = implode( " - ", $option_attributes );
											?>
											<option
												value='<?php echo $variation['variation_id']; ?>'
												data-attributes="<?php echo esc_attr( json_encode( $variation['attributes'] ) ); ?>">
												<?php echo $option_string; ?>
											</option>
											<?php
										}
										?>
									</select>
									<input type="hidden" name="iconic-wsb-bump-product_attributes-<?php echo esc_attr( $bump_product->get_id() ); ?>" value="">
									<?php
								} else if ( $bump_product->is_type( 'variation' ) ) {
									$attributes = Iconic_WSB_Order_Bump_Product_Page_Manager::get_variation_any_attributes( $bump_product );
									?>
									<select
										class='iconic-wsb-bump-product__select iconic-wsb-bump-product__select--<?php echo esc_attr( $bump_product->get_id() ); ?>'
										name='iconic-wsb-products-add-to-cart-variation-<?php echo esc_attr( $bump_product->get_id() ); ?>'
										data-product_id="<?php echo esc_attr( $bump_product->get_id() ); ?>">

										<option disabled selected value><?php echo Iconic_WSB_Order_Bump_Product_Page_Manager::get_variation_dropdown_placeholder( $bump_product ); ?></option>
										<?php foreach ( $attributes as $attribute ) {
											$option_string     = implode( " - ", $attribute );
											$option_attributes = Iconic_WSB_Order_Bump_Product_Page_Manager::get_variation_dropdown_option_attributes( $bump_product, $attribute );
											?>
											<option
												value='<?php echo esc_attr( $bump_product->get_id() ); ?>'
												data-attributes="<?php echo esc_attr( json_encode( $option_attributes ) ); ?>">
												<?php echo $option_string; ?>
											</option>
											<?php
										}
										?>
									</select>
									<input type="hidden" name="iconic-wsb-bump-product_attributes-<?php echo esc_attr( $bump_product->get_id() ); ?>" value="">
								<?php } ?>
							</label>
						</div>
					</div>
				</li>
			<?php } ?>
		</ul>
		<div class="iconic-wsb-product-bumps__actions">
			<div class="iconic-wsb-product-bumps__total-price">
				<span class="iconic-wsb-product-bumps__total-price-label">
					<?php esc_html_e( 'Total Price:', 'iconic-wsb' ); ?>
				</span>
				<span class="iconic-wsb-product-bumps__total-price-amount">
					<?php echo wp_kses_post( $total_price ); ?>
				</span>
			</div>
			<?php if ( $discount_message ) { ?>
				<div class="iconic-wsb-product-bumps__discount-message">
					<?php echo wp_kses_post( $discount_message ); ?>
				</div>
			<?php } ?>
			<div class="iconic-wsb-product-bumps__button-wrap">
				<button type="submit" class="button iconic-wsb-product-bumps__button" name="iconic-wsb-add-selected" data-bump-product-form-submit>
					<?php esc_html_e( 'Add Selected to Cart', 'iconic-wsb' ); ?>
				</button>
			</div>
			<input type="hidden" name="iconic-wsb-fbt-this-product" value="<?php echo esc_attr( $product->get_ID() ); ?>">
		</div>
	</div>
</div>
