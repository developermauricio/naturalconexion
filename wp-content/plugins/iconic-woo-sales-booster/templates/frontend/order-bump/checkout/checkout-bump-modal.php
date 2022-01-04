<?php defined( 'ABSPATH' ) || exit;

/**
 * @var Iconic_WSB_Order_Bump_After_Checkout $bump
 */

$product_id         = $product ? $product->get_id() : false;
$is_variable        = $product->is_type( 'variable' ) || $product->is_type( 'variation' );
$bump_title         = $bump->get_bump_title();
$bump_subtitle      = $bump->get_bump_subtitle();
$bump_product_intro = $bump->get_product_intro();
?>

<?php do_action( 'iconic-wsb-before-checkout-bump-modal', $bump ); ?>

<div class="iconic-wsb-modal mfp-hide" data-iconic-wsb-acb-modal>
	<div class="iconic-wsb-modal__content">
		<form class='iconic-wsb-after-checkout-bump-form variations_form' data-is_variable="<?php echo $is_variable ? 'yes' : 'no'; ?>">
			<?php if ( $bump->need_show_progress_bar() ) { ?>
				<div class="iconic-wsb-modal__progress">
					<div class="iconic-wsb-checkout-progress">
						<div class="iconic-wsb-checkout-progress__step is-active">
							<div class="iconic-wsb-checkout-progress__step-title">
								<?php esc_html_e( 'Order Submitted', 'iconic-wsb' ); ?>
							</div>
							<div class="iconic-wsb-checkout-progress__step-line">
								<div class="iconic-wsb-checkout-progress__step-status"></div>
							</div>
						</div>
						<div class="iconic-wsb-checkout-progress__step is-active">
							<div class="iconic-wsb-checkout-progress__step-title">
								<?php esc_html_e( 'Special Offer', 'iconic-wsb' ); ?>
							</div>
							<div class="iconic-wsb-checkout-progress__step-line">
								<div class="iconic-wsb-checkout-progress__step-status"></div>
							</div>
						</div>
						<div class="iconic-wsb-checkout-progress__step">
							<div class="iconic-wsb-checkout-progress__step-title">
								<?php esc_html_e( 'Order Receipt', 'iconic-wsb' ); ?>
							</div>
							<div class="iconic-wsb-checkout-progress__step-line">
								<div class="iconic-wsb-checkout-progress__step-status"></div>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>

			<div class="iconic-wsb-modal__titles">
				<?php if ( $bump_title ) { ?>
					<h1 class="iconic-wsb-modal__title">
						<?php echo $bump_title; ?>
					</h1>
				<?php } ?>

				<?php if ( $bump_subtitle ) { ?>
					<p class="iconic-wsb-modal__subtitle">
						<?php echo $bump_subtitle; ?>
					</p>
				<?php } ?>
			</div>

			<div class="iconic-wsb-modal__product iconic-wsb-modal-product-offer">
				<?php $product = $bump->get_product_offer(); ?>
				<div class="iconic-wsb-modal-product-offer__row">
					<div class="iconic-wsb-modal-product-offer__column iconic-wsb-modal-product-offer__column--image">
						<div class="iconic-wsb-modal-product-offer__image">
							<?php

							$post_thumbnail_id = $product->get_image_id();

							if ( $post_thumbnail_id ) {
								echo wp_get_attachment_image( $post_thumbnail_id, 'woocommerce_single' );
							} else {
								echo wc_placeholder_img( 'woocommerce_single' );
							}

							?>
						</div>
					</div>
					<div class="iconic-wsb-modal-product-offer__column iconic-wsb-modal-product-offer__column--intro">
						<?php if ( $product->get_title() ) { ?>
							<h2 class="iconic-wsb-modal-product-offer__title">
								<?php echo $product->get_title(); ?>
							</h2>
						<?php } ?>

						<?php if ( $product->get_price() ) { ?>
							<div class="iconic-wsb-modal-product-offer__price">
								<?php echo $bump->get_price_html(); ?>
							</div>
						<?php } ?>

						<?php if ( $bump_product_intro ) { ?>
							<p class="iconic-wsb-modal-product-offer__offer-description">
								<?php echo $bump_product_intro; ?>
							</p>
						<?php } ?>

						<?php if ( count( $bump->get_product_benefits() ) > 0 ) { ?>
							<div class="iconic-wsb-modal-product-offer__benefits">
								<ul class="iconic-wsb-modal-product-offer__benefits-list">
									<?php foreach ( $bump->get_product_benefits( [] ) as $benefit ) { ?>
										<li class="iconic-wsb-modal-product-offer__benefits-item">
											<?php echo $benefit; ?>
										</li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>

						<?php if ( $is_variable ) { ?>
							<div class="iconic-wsb-modal-product-offer___variable-product">
								<table class="variations iconic-wsb-variation-table" cellspacing="0">
									<tbody>
									<?php 
									$variable_product = $product->is_type( 'variable' ) ? $product : wc_get_product( $product->get_parent_id() );
									foreach ( $variable_product->get_variation_attributes() as $attribute_name => $options ) { ?>
										<tr>
											<td class="label">
												<label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label>
											</td>
											<td class="value">
												<?php
												$selected = false;

												if ( $variation_data && $product->is_type( "variation" )) {
													$selected = isset( $variation_data[$attribute_name] ) ? $variation_data[$attribute_name] : false;
												}

												Iconic_WSB_Helpers::wc_dropdown_variation_attribute_options(
													array(
														'options'   => $options,
														'attribute' => $attribute_name,
														'product'   => $product,
														'class'     => 'iconic-wsb-variation__select iconic-wsb-checkout-modal__select',
														'selected'  => $selected,
													)
												);
												?>
											</td>
										</tr>
									<?php } ?>
									</tbody>
								</table>
								<p class="wc-no-matching-variations woocommerce-info iconic-wsb-checkout-modal-unavailable_msg" style="display:none;"><?php _e( 'Sorry, this product is unavailable. Please choose a different combination.', 'iconic-wsb' ); ?></p>

								<input type="hidden" name='iconic-wsb-modal__variation_id' class="iconic-wsb__variation_id">

							</div>
						<?php } ?>

						<div class="iconic-wsb-modal-product-offer__actions">
							<div class="iconic-wsb-modal-product-offer__action">
								<button class="iconic-wsb-btn iconic-wsb-btn--buy button" type="button" data-iconic-wsb-acb-add-to-cart-button>
									<?php echo esc_html( $bump->get_button_text() ); ?>
								</button>
							</div>
							<div class="iconic-wsb-modal-product-offer__action">
								<a class="iconic-wsb-modal-product-offer__btn-skip" data-iconic-wsb-acb-close-button href="#">
									<?php echo esc_html( $bump->get_skip_text() ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name='iconic-wsb-modal__product_id' class="iconic-wsb__product_id" value="<?php echo esc_attr( $product_id ); ?>">
			<input type="hidden" name='iconic-wsb-modal__bump_id' class="iconic-wsb__bump_id" value="<?php echo esc_attr( $bump->get_id() ); ?>">
		</form>
	</div> <!-- iconic-wsb-modal__content -->
</div> <!-- iconic-wsb-modal -->

<?php do_action( 'iconic-wsb-after-checkout-bump-modal', $bump ); ?>
