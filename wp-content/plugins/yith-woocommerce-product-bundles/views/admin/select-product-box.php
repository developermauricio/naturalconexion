<?php
/**
 * Select product box.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Views
 */

defined( 'YITH_WCPB' ) || exit;

?>
<div class="yith-wcpb-select-product-box">
	<h3 class="yith-wcpb-select-product-box__title"><?php esc_html_e( 'Add products to the bundle', 'yith-woocommerce-product-bundles' ); ?></h3>
	<div class="yith-wcpb-select-product-box__filters">
		<?php
		$minimum_characters = apply_filters( 'yith_wcpb_minimum_characters_ajax_search', 3 );
		// translators: %s is the number of characters.
		$placeholder = sprintf( __( 'Search for a product (min %s characters)', 'yith-woocommerce-product-bundles' ), $minimum_characters );
		?>
		<input type="text" class="yith-wcpb-select-product-box__filter__search" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
	</div>
	<div class="yith-wcpb-select-product-box__products">
		<?php yith_wcpb_get_view( '/admin/select-product-box-products.php' ); ?>
	</div>
</div>
