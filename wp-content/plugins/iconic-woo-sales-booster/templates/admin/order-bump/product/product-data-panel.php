<?php defined( 'ABSPATH' ) || exit; ?>

<div id="iconic_wsb" class="panel woocommerce_options_panel hidden iconic-wsb-product-panel">
	<div class="options_group hide_if_grouped hide_if_external">
		<div class="iconic-wsb-product-panel__header">
			<h3><?php esc_html_e( 'Frequently Bought Together', 'iconic-wsb' ); ?></h3>
			<p><?php echo esc_html( $fbt_dropdown_options['description'] ); ?></p>
		</div>

		<p class="form-field">
			<label for="iconic-wsb-fbt-title"><?php esc_html_e( 'Title', 'iconic-wsb' ); ?></label>
			<input type="text" name="iconic-wsb-fbt-title" id="iconic-wsb-fbt-title" value="<?php echo esc_attr( $fbt_fields['title'] ); ?>" placeholder="<?php echo esc_attr( $settings['order_bump_title'] ); ?>" >
		</p>

		<p class="form-field">
			<label for="iconic-wsb-fbt-sales-pitch"><?php esc_html_e( 'Description', 'iconic-wsb' ); ?></label>
			<input type="text" name="iconic-wsb-fbt-sales-pitch" id="iconic-wsb-fbt-sales-pitch" value="<?php echo esc_attr( $fbt_fields['sales_pitch'] ); ?>" >
			<?php echo wc_help_tip( __( 'Use this to "pitch" the bundle to your customers.', 'iconic-wsb' ) ); ?>
		</p>

		<p class="form-field">
			<label for="iconic_wsb_product_page_order_bump_ids"><?php esc_html_e( 'Products', 'iconic-wsb' ); ?></label>
			<select class="wc-product-search"
					multiple="multiple"
					style="width: 50%;"
					id="iconic_wsb_product_page_order_bump_ids"
					name="iconic_wsb_product_page_order_bump_ids[]"
					data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
					data-action="woocommerce_json_search_products_and_variations"
					data-sortable="true"
					data-exclude="<?php echo esc_attr( intval( $fbt_dropdown_options['product']->ID ) ); ?>">
				<?php foreach ( $fbt_dropdown_options['bump_products'] as $bump_product ) : ?>
					<option value="<?php echo esc_attr( $bump_product->get_id() ); ?>" selected>
						<?php echo wp_kses_post( $bump_product->get_formatted_name() ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p class="form-field">
			<label for="iconic-wsb-fbt-set-unchecked"><?php esc_html_e( 'Unchecked by Default', 'iconic-wsb' ); ?></label>
			<input type="checkbox" name="iconic-wsb-fbt-set-unchecked" id="iconic-wsb-fbt-set-unchecked" value="yes" <?php checked( 'yes', $fbt_fields['set_unchecked'] ); ?> >
			<?php echo wc_help_tip( __( 'When enabled, the products in the frequently bought together bundle will be unchecked by default.', 'iconic-wsb' ) ); ?>
		</p>

		<p class="form-field">
			<label for="iconic-wsb-fbt-discount-value"><?php esc_html_e( 'Discount (Optional)', 'iconic-wsb' ); ?></label>

			<input type="text" class="wc_input_price" id="iconic-wsb-fbt-discount-value" name="iconic-wsb-fbt-discount-value" value="<?php echo esc_attr( $fbt_fields['discount_value'] ); ?>">
			<select name="iconic-wsb-fbt-discount-type" id="iconic-wsb-fbt-discount-type">
				<option <?php selected( $fbt_fields['discount_type'], 'percentage' ); ?> value="percentage">Percent</option>
				<option <?php selected( $fbt_fields['discount_type'], 'simple' ); ?> value="simple"><?php echo esc_attr( get_woocommerce_currency_symbol() ); ?></option>
			</select>
		</p>

		<div class="iconic-wsb-product-panel__header">
			<h3><?php esc_html_e( 'After Add to Cart Popup', 'iconic-wsb' ); ?></h3>
			<p><?php echo esc_html( $after_add_to_cart_dropdown_options['description'] ); ?></p>
		</div>

		<p class="form-field">
			<label for="<?php echo esc_attr( $after_add_to_cart_dropdown_options['name'] ); ?>"><?php esc_html_e( 'Products', 'iconic-wsb' ); ?></label>
			<select class="wc-product-search"
					multiple="multiple"
					style="width: 50%;"
					id="<?php echo esc_attr( $after_add_to_cart_dropdown_options["name"] ); ?>"
					name="<?php echo esc_attr( $after_add_to_cart_dropdown_options["name"] ); ?>[]"
					data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
					data-action="woocommerce_json_search_products_and_variations"
					data-sortable="true"
					data-exclude="<?php echo esc_attr( intval( $after_add_to_cart_dropdown_options['product']->ID ) ); ?>">
				<?php foreach ( $after_add_to_cart_dropdown_options['bump_products'] as $bump_product ) : ?>
					<option value="<?php echo esc_attr( $bump_product->get_id() ); ?>" selected>
						<?php echo wp_kses_post( $bump_product->get_formatted_name() ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>
</div>