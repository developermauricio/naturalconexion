<?php defined( 'ABSPATH' ) || exit;
/**
 * @var Iconic_WSB_Order_Bump_After_Checkout $bump
 */
?>
<div class="iconic-wsb-edit-step">
	<h2 class="iconic-wsb-edit-step__header">
		<?php esc_html_e( 'Customize Design', 'iconic-wsb' ); ?>
	</h2>

	<div class="iconic-wsb-edit-step__body">

		<div class="iconic-wsb-edit-step__customize-table">
			<table class="iconic-wsb-customize-table iconic-wsb-form" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<label for="iconic_wsb_show_progress_bar"><?php esc_html_e( 'Progress Bar', 'iconic-wsb' ); ?>:</label>
					</td>
					<td>
						<select class="iconic-wsb-form__control"
						        required
						        name="iconic_wsb_show_progress_bar"
						        id="iconic_wsb_show_progress_bar">
							<option value="yes" <?php selected( true, $bump->need_show_progress_bar() ); ?> >
								<?php esc_html_e( 'Yes', 'iconic-wsb' ); ?>
							</option>
							<option value="no" <?php selected( false, $bump->need_show_progress_bar() ); ?> >
								<?php esc_html_e( 'No', 'iconic-wsb' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<label for="iconic_wsb_bump_title"><?php esc_html_e( 'Title', 'iconic-wsb' ); ?>:</label>
					</td>
					<td>
						<input class="iconic-wsb-form__control"
						       type="text"
						       required
						       id="iconic_wsb_bump_title"
						       name="iconic_wsb_bump_title"
						       value="<?php echo esc_attr( $bump->get_bump_title( __( 'Wait! Your order is almost complete...', 'iconic-wsb' ) ) ); ?>"
						>
					</td>
				</tr>
				<tr>
					<td>
						<label for="iconic_wsb_bump_subtitle"><?php esc_html_e( 'Subtitle', 'iconic-wsb' ); ?>:</label>
					</td>
					<td>
						<textarea class="iconic-wsb-form__control" required id="iconic_wsb_bump_subtitle" name="iconic_wsb_bump_subtitle"><?php echo $bump->get_bump_subtitle( __( 'Add this offer to your order for only $1.99 - that\'s 50% off!', 'iconic-wsb' ) ); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label for="iconic_wsb_bump_product_intro"><?php esc_html_e( 'Product Intro', 'iconic-wsb' ); ?>:</label>
					</td>
					<td>
                        <textarea class="iconic-wsb-form__control"
                                  required
                                  type="text"
                                  id="iconic_wsb_bump_product_intro"
                                  name="iconic_wsb_bump_product_intro"><?php echo $bump->get_product_intro( __( 'Before we complete your order, here\'s a special one-time offer that you will only see on this page.', 'iconic-wsb' ) ); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<?php esc_html_e( 'Product Benefits', 'iconic-wsb' ); ?>:
					</td>
					<td>
						<div class="iconic-wsb-benefit iconic-wsb-hidden" data-benefit-template data-benefit>
							<input type="text" name="iconic_wsb_product_benefits[]" class="iconic-wsb-benefit__input">
							<span class="iconic-wsb-benefit__remove" data-remove-benefit></span>
						</div>
						<div class="iconic-wsb-customize-table__benefits" data-benefits-container>
							<?php foreach ( $bump->get_product_benefits( [] ) as $benefit ): ?>
								<div class="iconic-wsb-benefit" data-benefit>
									<input class="iconic-wsb-form__control iconic-wsb-benefit__input"
									       type="text"
									       name="iconic_wsb_product_benefits[]"
									       value="<?php echo esc_attr( $benefit ); ?>">
									<span class="iconic-wsb-benefit__remove" data-remove-benefit></span>
								</div>
							<?php endforeach; ?>
						</div>

						<button class="iconic-wsb-add-benefit-button button" data-add-benefit-button>
							<?php esc_html_e( 'Add benefit', 'iconic-wsb' ); ?>
						</button>
					</td>
				</tr>
				<tr>
					<td>
						<label for="iconic_wsb_bump_skip_text"><?php esc_html_e( 'Skip Text', 'iconic-wsb' ); ?>:</label>
					</td>
					<td>
						<input class="iconic-wsb-form__control"
						       type="text"
						       required
						       id="iconic_wsb_bump_skip_text"
						       name="iconic_wsb_bump_skip_text"
						       value="<?php echo esc_attr( $bump->get_skip_text( __( 'No thanks, I\'ll pass.', 'iconic-wsb' ) ) ); ?>">
					</td>
				</tr>
				<tr>
					<td>
						<label for="iconic_wsb_bump_button_text"><?php esc_html_e( 'Button Text', 'iconic-wsb' ); ?>:</label>
					</td>
					<td>
						<input class="iconic-wsb-form__control"
						       type="text"
						       required
						       id="iconic_wsb_bump_button_text"
						       name="iconic_wsb_bump_button_text"
						       value="<?php echo esc_attr( $bump->get_button_text( __( 'Add to my order', 'iconic-wsb' ) ) ); ?>">
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>