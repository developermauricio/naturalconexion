<?php defined( 'ABSPATH' ) || exit;
/**
 * @var Iconic_WSB_Order_Bump_Checkout $bump
 */

$settings = $bump->get_render_settings();

?>

<div class="iconic-wsb-edit-step">
	<h2 class="iconic-wsb-edit-step__header">
		<?php esc_html_e( 'Customize Design', 'iconic-wsb' ); ?>
	</h2>
	<div class="iconic-wsb-edit-step__body">
		<div class="iconic-wsb-checkout-bump"
		     data-iconic-wsb-setting-border-style--element
		     style="border: 1px <?php echo esc_attr( $settings['border_style'] . ' ' . $settings['border_color'] ); ?>">
			<div class="iconic-wsb-checkout-bump__header"
			     data-iconic-wsb-setting-border-style--element
			     style="border-bottom: 1px <?php echo esc_attr( $settings['border_style'] . ' ' . $settings['border_color'] ); ?>">
				<input class="iconic-wsb-checkout-bump__header-checkbox" type="checkbox">
				<input type="text"
				       class="iconic-wsb-checkout-bump__header-bump-title"
				       name="iconic_wsb_checkbox_text"
				       data-iconic-wsb-setting-highlight-color--element
				       style="color: <?php echo esc_attr( $settings['highlight_color'] ); ?>;"
				       value="<?php echo esc_attr__( $bump->get_checkbox_text( __( 'Yes! I want to add this offer to my order', 'iconic-wsb' ) ) ); ?>"
				       required
				>
			</div>
			<div class="iconic-wsb-checkout-bump__body">
				<div class="iconic-wsb-checkout-bump__product">
					<div class="iconic-wsb-checkout-bump__product-aside <?php echo esc_attr( $settings['show_image'] == 'yes' ? '' : 'iconic-wsb-hidden' ); ?>"
					     data-iconic-wsb-setting-show-image--element>
						<div class='iconic-wsb-checkout-bump__product-image' data-iconic-wsb-upload-image>
							<img class="iconic-wsb-checkout-bump__product-img"
							     id='image-preview'
							     data-iconic-wsb-image-preview
							     src="<?php echo esc_url( $bump->get_offer_image_src() ); ?>"
							>
							<button class="iconic-wsb-checkout-bump__edit-image-btn"
							        type="button"
							        title="<?php esc_attr_e( 'Upload image', 'iconic-wsb' ); ?>"
							>
								<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 511.995 511.995" style="enable-background:new 0 0 511.995 511.995;" xml:space="preserve"><g>
										<path d="M497.941,14.057c18.75,18.75,18.719,49.141,0,67.891l-22.625,22.625L407.41,36.682l22.625-22.625C448.784-4.677,479.191-4.693,497.941,14.057z M158.534,285.588l-22.609,90.5l90.5-22.625l226.266-226.266l-67.906-67.891L158.534,285.588z M384.003,241.15v206.844h-320v-320h206.859l63.983-64H0.003v448h448v-334.86L384.003,241.15z" />
									</g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g>
									<g></g></svg>
							</button>
							<input type='hidden'
							       name='iconic_wsb_image_attachment_id'
							       data-iconic-wsb-image-attachment-id
							       value="<?php echo esc_attr( $bump->get_custom_image_id() ); ?>">
						</div>
					</div>
					<div class="iconic-wsb-checkout-bump__main">
                        <textarea required name="iconic_wsb_bump_description"
                                  cols="30"
                                  rows="7"
                        ><?php echo $bump->get_bump_description( __( 'One time offer! Get this product with HUGE discount right now! Click the checkbox above to add this product to your order. Get it now, because you won\'t have this chance again.', 'iconic-wsb' ) ); ?></textarea>
						<div class="iconic-wsb-checkout-bump__price <?php echo esc_attr( $settings['show_price'] == 'yes' ? '' : 'iconic-wsb-hidden' ); ?>"
						     data-iconic-wsb-setting-show-price--element>

							<del class="<?php echo $bump->get_product_offer() && $bump->get_product_offer()
							                                                          ->get_price() == $bump->get_discount_price() ? 'hidden' : ''; ?>" data-iconic-wsb-product-customize-price--regular>
								<?php if ( $bump->get_product_offer() ) {
									echo wc_price( $bump->get_product_offer()->get_price() );
								} ?>
							</del>
							<span
								data-iconic-wsb-product-customize-price--discount
								data-iconic-wsb-setting-highlight-color--element
								style="color: <?php echo esc_attr( $settings['highlight_color'] ); ?>;"
							>
								<?php if ( $bump->get_product_offer() ) {
									echo wc_price( $bump->get_discount_price() );
								} ?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="iconic-wsb-edit-step__customize-table">
			<table class="iconic-wsb-customize-table iconic-wsb-form">
				<tr>
					<td>
						<?php esc_html_e( 'Highlight color', 'iconic-wsb' ); ?>:
					</td>
					<td>
						<input type="color"
						       class="iconic-wsb-form__control"
						       data-iconic-wsb-color-picker
						       data-iconic-wsb-setting-highlight-color
						       name="iconic_wsb_render_settings[highlight_color]"
						       value="<?php echo esc_attr( $settings['highlight_color'] ); ?>"
						       required
						>
					</td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Border color', 'iconic-wsb' ); ?>:
					</td>
					<td>
						<input type="color"
						       class="iconic-wsb-form__control"
						       data-iconic-wsb-color-picker
						       data-iconic-wsb-setting-border-color
						       name="iconic_wsb_render_settings[border_color]"
						       value="<?php echo esc_attr( $settings['border_color'] ); ?>"
						       required
						>
					</td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Border style', 'iconic-wsb' ); ?>:
					</td>
					<td>
						<select name="iconic_wsb_render_settings[border_style]"
						        class="iconic-wsb-form__control"
						        data-iconic-wsb-setting-border-style
						        required>
							<option value="solid" <?php selected( 'solid', $settings['border_style'] ); ?> >
								<?php esc_html_e( 'Solid', 'iconic-wsb' ); ?>
							</option>
							<option value="dashed" <?php selected( 'dashed', $settings['border_style'] ); ?> >
								<?php esc_html_e( 'Dashed', 'iconic-wsb' ); ?>
							</option>
							<option value="dotted" <?php selected( 'dotted', $settings['border_style'] ); ?> >
								<?php esc_html_e( 'Dotted', 'iconic-wsb' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Show image', 'iconic-wsb' ); ?>:
					</td>
					<td>
						<select class="iconic-wsb-form__control"
						        name="iconic_wsb_render_settings[show_image]"
						        required
						        data-iconic-wsb-setting-show-image
						>
							<option value="yes" <?php selected( 'yes', $settings['show_image'] ); ?> >
								<?php esc_html_e( 'Yes', 'iconic-wsb' ); ?>
							</option>
							<option value="no" <?php selected( 'no', $settings['show_image'] ); ?> >
								<?php esc_html_e( 'No', 'iconic-wsb' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Show price', 'iconic-wsb' ); ?>:
					</td>
					<td>
						<select class="iconic-wsb-form__control"
						        name="iconic_wsb_render_settings[show_price]"
						        data-iconic-wsb-setting-show-price
						        required>
							<option value="yes" <?php selected( 'yes', $settings['show_price'] ); ?> >
								<?php esc_html_e( 'Yes', 'iconic-wsb' ); ?>
							</option>
							<option value="no" <?php selected( 'no', $settings['show_price'] ); ?> >
								<?php esc_html_e( 'No', 'iconic-wsb' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Show shadow', 'iconic-wsb' ); ?>:
					</td>
					<td>
						<select class="iconic-wsb-form__control"
						        name="iconic_wsb_render_settings[show_shadow]"
						        data-iconic-wsb-setting-show-shadow
						        required>
							<option value="yes" <?php selected( 'yes', $settings['show_shadow'] ); ?> >
								<?php esc_html_e( 'Yes', 'iconic-wsb' ); ?>
							</option>
							<option value="no" <?php selected( 'no', $settings['show_shadow'] ); ?> >
								<?php esc_html_e( 'No', 'iconic-wsb' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Position', 'iconic-wsb' ) ?>:
					</td>
					<td>
						<select class="iconic-wsb-form__control"
						        name="iconic_wsb_render_settings[position]"
						        required>
							<option value="woocommerce_review_order_before_submit" <?php selected( 'woocommerce_review_order_before_submit', $settings['position'] ); ?>>
								<?php esc_html_e( 'Before "Place Order" button', 'iconic-wsb' ) ?>
							</option>
						</select>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
