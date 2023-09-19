<?php
/**
 * Bundle add-to-cart template
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Templates
 */

defined( 'YITH_WCPB' ) || exit;

// Late style and scripts loading.
wp_enqueue_style( 'yith_wcpb_bundle_frontend_style' );
wp_enqueue_script( 'yith_wcpb_bundle_frontend_add_to_cart' );

/**
 * The bundle product.
 *
 * @var WC_Product_Yith_Bundle $product
 */
global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

?>

<?php echo wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" method="post" enctype='multipart/form-data' data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">

		<?php
		$bundled_items = $product->get_bundled_items();
		?>
		<?php if ( $bundled_items ) : ?>
			<div class="yith-wcpb-product-bundled-items">
				<?php foreach ( $bundled_items as $bundled_item ) : ?>
					<?php
					$bundled_product = $bundled_item->get_product();
					$bundled_post    = get_post( yit_get_base_product_id( $bundled_product ) );
					$quantity        = $bundled_item->get_quantity();
					$description     = $bundled_post->post_excerpt;
					$the_title       = $bundled_product->get_title();

					if ( $quantity > 1 ) {
						$the_title = $quantity . ' x ' . $the_title;
					}

					$bundled_item_classes = apply_filters( 'yith_wcpb_bundled_item_classes', array( 'product', 'yith-wcpb-product-bundled-item' ), $bundled_item, $product );
					$bundled_item_classes = implode( ' ', $bundled_item_classes );
					?>
					<div class="<?php echo esc_attr( $bundled_item_classes ); ?>"
							data-is-purchasable="<?php echo esc_attr( $bundled_product->is_purchasable() ? '1' : '0' ); ?>">
						<div class="yith-wcpb-product-bundled-item-image">
							<?php
							$post_thumbnail_id = $bundled_product->get_image_id();
							if ( $post_thumbnail_id ) {
								echo wc_get_gallery_image_html( $post_thumbnail_id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								echo '<div class="woocommerce-product-gallery__image--placeholder">';
								echo sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
								echo '</div>';
							}
							?>
						</div>
						<div class="yith-wcpb-product-bundled-item-data">
							<h3 class="yith-wcpb-product-bundled-item-data__title">
								<a href="<?php echo esc_url( $bundled_product->get_permalink() ); ?>">
									<?php echo esc_html( $the_title ); ?>
								</a>
							</h3>

							<?php do_action( 'yith_wcpb_after_bundled_item_title', $bundled_item ); ?>

							<div class="yith-wcpb-product-bundled-item-data__description"><?php echo wp_kses_post( do_shortcode( $description ) ); ?></div>

							<div class="yith-wcpb-product-bundled-item-data__availability yith-wcpb-product-bundled-item-availability not-variation">
								<?php
								if ( $bundled_product->has_enough_stock( $quantity ) && $bundled_product->is_in_stock() ) {
									echo '<div class="yith-wcpb-product-bundled-item-instock"></div>';
								} else {
									echo '<div class="yith-wcpb-product-bundled-item-outofstock">' . esc_html__( 'Out of stock', 'woocommerce' ) . '</div>';
								}
								?>
							</div>

						</div>
					</div>

				<?php endforeach; ?>

			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
		<?php
		if ( ! $product->is_sold_individually() ) {
			woocommerce_quantity_input(
				array(
					'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
					'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product ),
				)
			);
		}
		?>

		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"/>

		<button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
