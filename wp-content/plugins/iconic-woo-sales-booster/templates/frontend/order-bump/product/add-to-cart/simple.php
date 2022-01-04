<?php defined( 'ABSPATH' ) || exit;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product );

if ( $product->is_in_stock() ) : ?>
	<form class="iconic-wsb-modal-product__add-to-cart-form cart"
          action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
          method="post"
          enctype='multipart/form-data'>
		<button class="iconic-wsb-modal-product__add-to-cart single_add_to_cart_button button alt"
                type="submit"
                name="add-to-cart"
                value="<?php echo esc_attr( $product->get_id() ); ?>">
            <?php echo esc_html( $product->single_add_to_cart_text() ); ?>
        </button>
	</form>
<?php endif; ?>
