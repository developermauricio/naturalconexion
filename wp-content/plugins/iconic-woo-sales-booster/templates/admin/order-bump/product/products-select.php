<?php defined( 'ABSPATH' ) || exit;
/**
 * @var WC_Product[] $bump_products
 * @var WP_Post      $product
 * @var string       $name
 * @var string       $label
 * @var string       $description
 */
?>
<p class="form-field">
	<label for="<?php echo $name; ?>"><?php esc_html_e( $label ); ?></label>
	<select class="wc-product-search"
	        multiple="multiple"
	        style="width: 50%;"
	        id="<?php echo esc_attr( $name ); ?>"
	        name="<?php echo esc_attr( $name ); ?>[]"
	        data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
	        data-action="woocommerce_json_search_products_and_variations"
	        data-sortable="true"
	        data-exclude="<?php echo esc_attr( intval( $product->ID ) ); ?>">
		<?php foreach ( $bump_products as $bump_product ): ?>
			<option value="<?php echo esc_attr( $bump_product->get_id() ) ?>" selected>
				<?php echo wp_kses_post( $bump_product->get_formatted_name() ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<?php echo wc_help_tip( $description ); ?>
</p>