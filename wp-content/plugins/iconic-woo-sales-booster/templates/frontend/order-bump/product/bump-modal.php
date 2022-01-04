<?php defined( 'ABSPATH' ) || exit;
/**
 * @var WC_Product[] $offers
 * @var array        $settings
 */
?>

<!-- After Add to Cart Modal Bump -->
<div class="iconic-wsb-modal iconic-wsb-modal--narrow" data-iconic-wsb-acc-modal-bump>
	<div class="iconic-wsb-modal__header" style="background-color: <?php echo esc_attr( $settings['header_color'] ); ?>">
		<div class="iconic-wsb-modal__header-icon iconic-wsb-modal__header-icon--success">
			<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 426.667 426.667" style="enable-background:new 0 0 426.667 426.667;" xml:space="preserve"><g>
					<g>
						<polygon points="293.333,135.04 190.08,240.213 137.173,187.093 108.8,215.467 192.213,298.667 326.187,168.747 		" />
					</g>
				</g>
				<g>
					<g>
						<path d="M213.333,0C95.513,0,0,95.513,0,213.333s95.513,213.333,213.333,213.333s213.333-95.513,213.333-213.333S331.154,0,213.333,0z M213.333,388.053c-96.495,0-174.72-78.225-174.72-174.72s78.225-174.72,174.72-174.72c96.446,0.117,174.602,78.273,174.72,174.72C388.053,309.829,309.829,388.053,213.333,388.053z" />
					</g>
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
		</div>
		<div class="iconic-wsb-modal__header-title">
			<?php printf( esc_html__( '%s was added to your cart!', 'iconic-wsb' ), $product->get_title() ); ?>
		</div>
		<div class="iconic-wsb-modal__header-icon iconic-wsb-modal__header-icon--close" data-iconic-wsb-close-aac-modal>
			<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 212.982 212.982" style="enable-background:new 0 0 212.982 212.982;" xml:space="preserve"><g id="Close">
					<path style="fill-rule:evenodd;clip-rule:evenodd;" d="M131.804,106.491l75.936-75.936c6.99-6.99,6.99-18.323,0-25.312c-6.99-6.99-18.322-6.99-25.312,0l-75.937,75.937L30.554,5.242c-6.99-6.99-18.322-6.99-25.312,0c-6.989,6.99-6.989,18.323,0,25.312l75.937,75.936L5.242,182.427c-6.989,6.99-6.989,18.323,0,25.312c6.99,6.99,18.322,6.99,25.312,0l75.937-75.937l75.937,75.937c6.989,6.99,18.322,6.99,25.312,0c6.99-6.99,6.99-18.322,0-25.312L131.804,106.491z" />
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
		</div>
	</div>
	<div class="iconic-wsb-modal__content">
		<div class="iconic-wsb-modal__product iconic-wsb-modal__product--summary">
			<div class="iconic-wsb-modal-product-summary">
				<div class="iconic-wsb-modal-product-summary__main">
					<div class="iconic-wsb-modal-product-summary__product">
						<div class="iconic-wsb-modal-product-summary__product-image">
							<?php echo $product->get_image(); ?>
						</div>
						<div class="iconic-wsb-modal-product-summary__product-info">
							<h3 class="iconic-wsb-modal-product-summary__product-title">
								<?php echo esc_html( $product->get_title() ); ?>
							</h3>
							<div class="iconic-wsb-modal-product-summary__product-price">
								<?php echo $product->get_price_html(); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="iconic-wsb-modal-product-summary__aside">
					<div class="iconic-wsb-modal-product-summary__cart-subtotal">
						<?php printf( __( 'Cart subtotal: %s', 'iconic-wsb' ), WC()->cart->get_cart_subtotal() ); ?>
					</div>

					<div class="iconic-wsb-modal-product-summary__cart-items-count">
						<?php $cart_items_count = WC()->cart->get_cart_contents_count(); ?>
						(<?php printf( _n( '%d Item', '%d Items', $cart_items_count, 'iconic-wsb' ), $cart_items_count ); ?>)
					</div>

					<div class="iconic-wsb-modal-product-summary__checkout">
						<a href="<?php echo wc_get_checkout_url(); ?>">
							<button class="button">
								<?php esc_html_e( 'Checkout', 'iconic-wsb' ); ?>
							</button>
						</a>
					</div>

					<div class="iconic-wsb-modal-product-summary__view-cart">
						<a href="<?php echo wc_get_cart_url(); ?>">
							<?php esc_html_e( 'View Cart ', 'iconic-wsb' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="iconic-wsb-modal__offer">
			<div class="iconic-wsb-modal-products">
				<h2 class="iconic-wsb-modal-products__title">
					<?php echo wp_kses_post( $settings['title'] ); ?>
				</h2>

				<div class="iconic-wsb-modal-products__product-list">

					<?php foreach ( $offers as $offer ): ?>
						<div class="iconic-wsb-modal-products__product">
							<div class="iconic-wsb-modal-product" data-iconic-wsb-acc-modal-bump-offer-product>
								<div class="iconic-wsb-modal-product__image" data-iconic-wsb-acc-modal-bamp-offer-image>
									<a href="<?php echo esc_url( $offer->get_permalink() ); ?>">
										<?php echo $offer->get_image( $size = 'woocommerce_thumbnail', $attr = array( 'srcset' => '' ) ); ?>
									</a>
								</div>
								<h3 class="iconic-wsb-modal-product__title">
									<a class="iconic-wsb-modal-product__title-link" href="<?php echo esc_url( $offer->get_permalink() ); ?>">
										<?php echo esc_html( $offer->get_name() ); ?>
									</a>
								</h3>
								<div class="iconic-wsb-modal-product__price">
									<?php echo $offer->get_price_html(); ?>
								</div>
								<div class="iconic-wsb-modal-product__add-to-cart">
									<?php
									global $iconic_wsb_class;
									$iconic_wsb_class->template->include_template( 'frontend/order-bump/product/add-to-cart/all.php', array(
										'product' => $offer,
									) );
									?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>

				</div>
			</div>
		</div>

	</div>
</div>