<?php
/**
 * Select product box.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Views
 */

defined( 'YITH_WCPB' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended

$items_per_page = 10;
$the_page       = ! empty( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
$offset         = $the_page > 1 ? ( ( $the_page - 1 ) * $items_per_page ) : 0;
$product_types  = yith_wcpb_get_allowed_product_types();

$term_to_search = ! empty( $_REQUEST['s'] ) ? wc_clean( wp_unslash( $_REQUEST['s'] ) ) : '';

$args = array(
	'limit'            => $items_per_page,
	'offset'           => $offset,
	'type'             => array_keys( $product_types ),
	'status'           => 'publish',
	'paginate'         => true,
	'suppress_filters' => false,
);

if ( ! ! $term_to_search && 'sku:' === substr( $term_to_search, 0, 4 ) ) {
	$args['sku'] = substr( $term_to_search, 4 );
} else {
	$args['s'] = $term_to_search;
}

$args = apply_filters( 'yith_wcpb_select_product_box_args', $args );

$products_query = new WC_Product_Query( $args );
$results        = $products_query->get_products();
$products       = $results->products;
$total          = $results->total;
$total_pages    = $results->max_num_pages;
?>
<div class="yith-wcpb-select-product-box__products__table-container">
	<table class="yith-wcpb-select-product-box__products__table widefat">
		<thead>
		<tr>
			<td class="column-image"><?php esc_html_e( 'Image', 'yith-woocommerce-product-bundles' ); ?></td>
			<td class="column-name"><?php esc_html_e( 'Name', 'yith-woocommerce-product-bundles' ); ?></td>
			<td class="column-price"><?php esc_html_e( 'Price', 'yith-woocommerce-product-bundles' ); ?></td>
			<td class="column-type"><?php esc_html_e( 'Type', 'yith-woocommerce-product-bundles' ); ?></td>
			<td class="column-action"></td>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $products as $product ) : ?>
			<?php
			/**
			 * The product.
			 *
			 * @var WC_Product $product
			 */
			$product_type_raw = $product->get_type();
			$product_type     = $product_types[ $product_type_raw ] ?? ucfirst( str_replace( '_', ' ', $product->get_type() ) );
			$edit_link        = get_edit_post_link( $product->get_id() );
			?>
			<tr class="yith-wcpb-select-product-box__product" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
				<td class="column-image"><?php echo $product->get_image( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>

				<td class="column-name">
					<div class="product-name">
						<a href="<?php echo esc_url( $edit_link ); ?>" target="_blank"><?php echo esc_html( $product->get_formatted_name() ); ?></a>
					</div>
					<div class="product-info">
						<?php if ( ! $product->is_in_stock() ) : ?>
							<span class="product-single-info out-of-stock"><?php esc_html_e( 'Out of stock', 'yith-woocommerce-product-bundles' ); ?></span>
						<?php endif; ?>
					</div>
				</td>
				<td class="column-price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
				<td class="column-type"><?php echo esc_html( $product_type ); ?></td>
				<td class="column-action">
					<span class="yith-wcpb-add-product" data-id="<?php echo esc_attr( $product->get_id() ); ?>"><?php esc_html_e( 'Add', 'yith-woocommerce-product-bundles' ); ?></span>
					<span class="yith-wcpb-product-added">
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="24.676px" height="19.139px" viewBox="0 0 24.676 19.139" enable-background="new 0 0 24.676 19.139" xml:space="preserve">
						<g>
							<polygon fill="currentColor" points="4.705,4.608 10.178,9.985 20.163,0 24.676,4.449 10.146,19.139 0,9.249  "/>
						</g>
					</svg>
					<span class="yith-wcpb-product-added__text">
						<?php esc_html_e( 'Product added to the bundle', 'yith-woocommerce-product-bundles' ); ?>
					</span>
				</span>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<div class="yith-wcpb-select-product-box__products__pagination">
	<?php
	$prev_disabled = $the_page < 2 ? 'disabled' : '';
	$next_disabled = $the_page >= $total_pages ? 'disabled' : '';
	$prev_page     = max( 1, ( $the_page - 1 ) );
	$next_page     = min( $total_pages, ( $the_page + 1 ) );
	?>
	<span class="first <?php echo esc_attr( $prev_disabled ); ?>" data-page="1">&laquo;</span>
	<span class="prev <?php echo esc_attr( $prev_disabled ); ?>" data-page="<?php echo esc_attr( $prev_page ); ?>"><?php esc_html_e( 'prev', 'yith-woocommerce-product-bundles' ); ?></span>
	<span class="current"><?php echo sprintf( '%s/%s', absint( $the_page ), absint( $total_pages ) ); ?></span>
	<span class="next <?php echo esc_attr( $next_disabled ); ?>" data-page="<?php echo esc_attr( $next_page ); ?>"><?php esc_html_e( 'next', 'yith-woocommerce-product-bundles' ); ?></span>
	<span class="last <?php echo esc_attr( $next_disabled ); ?>" data-page="<?php echo esc_attr( $total_pages ); ?>">&raquo;</span>
</div>
