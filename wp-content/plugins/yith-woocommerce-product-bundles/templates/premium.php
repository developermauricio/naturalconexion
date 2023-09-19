<?php
/**
 * Premium tab.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Views
 */

?>
<style>
	.landing {
		margin-right : 15px;
		border       : 1px solid #d8d8d8;
		border-top   : 0;
	}

	.section {
		font-family : -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
		background  : #fafafa;
	}

	.section h1 {
		text-align     : center;
		text-transform : uppercase;
		color          : #445674;
		font-size      : 35px;
		font-weight    : 700;
		line-height    : normal;
		display        : inline-block;
		width          : 100%;
		margin         : 50px 0 0;
	}

	.section .section-title h2 {
		vertical-align : middle;
		padding        : 0;
		line-height    : normal;
		font-size      : 24px;
		font-weight    : 700;
		color          : #445674;
		text-transform : uppercase;
		background     : none;
		border         : none;
		text-align     : center;
	}

	.section p {
		margin      : 15px 0;
		font-size   : 19px;
		line-height : 32px;
		font-weight : 300;
		text-align  : center;
	}

	.section ul li {
		margin-bottom : 4px;
	}

	.section.section-cta {
		background : #fff;
	}

	.cta-container,
	.landing-container {
		display      : flex;
		max-width    : 1400px;
		margin-left  : auto;
		margin-right : auto;
		padding      : 30px 0;
		align-items  : center;
	}

	.landing-container-wide {
		flex-direction : column;
	}

	.cta-container {
		display   : block;
		max-width : 860px;
	}

	.landing-container:after {
		display : block;
		clear   : both;
		content : '';
	}

	.landing-container .col-1,
	.landing-container .col-2 {
		float      : left;
		box-sizing : border-box;
		padding    : 0 15px;
	}

	.landing-container .col-1 {
		width : 58.33333333%;
	}

	.landing-container .col-2 {
		width : 41.66666667%;
	}

	.landing-container .col-1 img,
	.landing-container .col-2 img {
		max-width : 100%;
	}

	.landing-container .col-wide img {
		max-width : 60%;
		margin    : 0 auto;
		display   : block;
	}

	.the-cta {
		color           : #4b4b4b;
		border-radius   : 10px;
		padding         : 30px 25px;
		display         : flex;
		align-items     : center;
		justify-content : space-between;
		width           : 100%;
		box-sizing      : border-box;
	}

	.the-cta:after {
		content : '';
		display : block;
		clear   : both;
	}

	.the-cta p {
		margin      : 10px 10px 10px 0;
		line-height : 1.5em;
		display     : inline-block;
		text-align  : left;
	}

	.the-cta a.button {
		border-radius  : 25px;
		float          : right;
		background     : #e09004;
		box-shadow     : none;
		outline        : none;
		color          : #fff;
		position       : relative;
		padding        : 10px 50px;
		text-align     : center;
		text-transform : uppercase;
		font-weight    : 600;
		font-size      : 20px;
		line-height    : normal;
		border         : none;
	}

	.the-cta a.button:hover,
	.the-cta a.button:active,
	.wp-core-ui .yith-plugin-ui .the-cta a.button:focus {
		color      : #fff;
		background : #d28704;
		box-shadow : none;
		outline    : none;
	}

	.the-cta .highlight {
		text-transform : uppercase;
		background     : none;
		font-weight    : 500;
	}

	@media (max-width : 991px) {
		.landing-container {
			display : block;
			padding : 50px 0 30px;
		}

		.landing-container .col-1,
		.landing-container .col-2 {
			float : none;
			width : 100%;
		}

		.the-cta {
			display    : block;
			text-align : center;
		}

		.the-cta p {
			text-align    : center;
			display       : block;
			margin-bottom : 30px;
		}

		.the-cta a.button {
			float   : none;
			display : inline-block;
		}
	}
</style>
<div class="landing">
	<div class="section section-cta section-odd">
		<div class="cta-container">
			<div class="the-cta">
				<p>
					<?php
					// translators: 1. the 'highlight' span HTML tag start; 2. the 'highlight' span HTML tag end; 3. 'br' HTML tag.
					echo sprintf( esc_html__( 'Upgrade to the %1$spremium version%2$s of%3$s%1$sYITH WooCommerce Product Bundles%2$s%3$sto benefit from all features!', 'yith-woocommerce-product-bundles' ), '<span class="highlight">', '</span>', '<br/>' );
					?>
				</p>
				<a href="<?php echo esc_url( YITH_WCPB_Admin()->get_premium_landing_uri() ); ?>" target="_blank" class="button btn">
					<?php esc_html_e( 'Upgrade', 'yith-woocommerce-product-bundles' ); ?>
				</a>
			</div>
		</div>
	</div>
	<div class="section section-odd clear">
		<h1><?php esc_html_e( 'Premium Features', 'yith-woocommerce-product-bundles' ); ?></h1>
		<div class="landing-container">
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/02.jpg" alt="<?php esc_html_e( 'Bundled items', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Add products to the bundle and customize the way to show them', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'You can add unlimited products to the bundle and choose whether to show or hide the product, its name, and the description. For every product, you can use the default information or add a custom description.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
		</div>
	</div>
	<div class="section section-even clear">
		<div class="landing-container">
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Add product variations and allow users to choose the color, size, etc. before adding the bundle to the cart', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'You can also add variable products to your bundles so users can select the right options before purchasing.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/03.jpg" alt="<?php esc_html_e( 'Variable products', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
		</div>
	</div>
	<div class="section section-odd clear">
		<div class="landing-container">
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/04.jpg" alt="<?php esc_html_e( 'Optional products', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Set products as "optional" and allow your users to choose to add them to the bundle', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Optional products will show in the bundle with a checkbox so users will be able to choose to buy or exclude them.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
		</div>
	</div>
	<div class="section section-even clear">
		<div class="landing-container">
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Set purchase rules for the bundle', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'For optional products, you can set rules to define the minimum/maximum number of products that must be selected by the user in order to add the bundle to the cart.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/05.jpg" alt="<?php esc_html_e( 'Purchase rules', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
		</div>
	</div>
	<div class="section section-odd clear">
		<div class="landing-container">
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/06.jpg" alt="<?php esc_html_e( 'Price settings', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Set a fixed price for the bundle or use the prices of the items included', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'When creating the bundle, you can define a fixed price or use the one resulting from the sum of the single products.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
		</div>
	</div>
	<div class="section section-even clear">
		<div class="landing-container landing-container-wide">
			<div class="col-wide">
				<div class="section-title">
					<h2><?php esc_html_e( 'Apply a discount to products added to the bundle', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Apply a discount on the price of the products added to the bundle and create offers to encourage users to purchase.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
			<div class="col-wide">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/07.jpg" alt="<?php esc_html_e( 'Discounts', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
		</div>
	</div>
	<div class="section section-odd clear">
		<div class="landing-container">
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/08.jpg" alt="<?php esc_html_e( 'Bundle shipping', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Choose how to manage the shipping of the bundle', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'You can decide to ship all the bundled items and charge the customer with a single shipping cost or ship the products separately.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
		</div>
	</div>
	<div class="section section-even clear">
		<div class="landing-container">
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Choose how to manage the bundle with out-of-stock products', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'If one of the added products is out-of-stock, choose whether to hide the bundle, set it to “Out of stock” or keep showing it without the possibility for the users to buy it.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/09.jpg" alt="<?php esc_html_e( 'Out of stock settings', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
		</div>
	</div>
	<div class="section section-odd clear">
		<div class="landing-container">
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/10.jpg" alt="<?php esc_html_e( 'Cart settings', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Choose how to show the bundle in the cart and on invoices', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Choose whether to show only the bundle in the cart and order invoices or the list of the products included in the bundle and the related prices.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
		</div>
	</div>
	<div class="section section-even clear">
		<div class="landing-container">
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Use the widget “Products bundles” to show a list with your bundles on any widget area of your shop', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Give value to your bundles by inserting a widget in the sidebars of your product pages, the home page, footer, and any widget area of your theme.', 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/11.jpg" alt="<?php esc_html_e( 'Bundle widget', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
		</div>
	</div>
	<div class="section section-odd clear">
		<div class="landing-container">
			<div class="col-1">
				<img src="<?php echo esc_url( YITH_WCPB_URL ); ?>assets/images/landing/12.jpg" alt="<?php esc_html_e( '"Quick View" integration', 'yith-woocommerce-product-bundles' ); ?>"/>
			</div>
			<div class="col-2">
				<div class="section-title">
					<h2><?php esc_html_e( 'Get the best out of the compatibility with YITH WooCommerce Quick View', 'yith-woocommerce-product-bundles' ); ?></h2>
				</div>
				<p><?php esc_html_e( "Use our plugin Quick View to show the details of the product included in the bundle in a modal window so users don't have to go through the different product pages.", 'yith-woocommerce-product-bundles' ); ?></p>
			</div>
		</div>
	</div>
	<div class="section section-cta section-odd">
		<div class="cta-container">
			<div class="the-cta">
				<p>
					<?php
					// translators: 1. the 'highlight' span HTML tag start; 2. the 'highlight' span HTML tag end; 3. 'br' HTML tag.
					echo sprintf( esc_html__( 'Upgrade to the %1$spremium version%2$s of%3$s%1$sYITH WooCommerce Product Bundles%2$s%3$sto benefit from all features!', 'yith-woocommerce-product-bundles' ), '<span class="highlight">', '</span>', '<br/>' );
					?>
				</p>
				<a href="<?php echo esc_url( YITH_WCPB_Admin()->get_premium_landing_uri() ); ?>" target="_blank" class="button btn">
					<?php esc_html_e( 'Upgrade', 'yith-woocommerce-product-bundles' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
