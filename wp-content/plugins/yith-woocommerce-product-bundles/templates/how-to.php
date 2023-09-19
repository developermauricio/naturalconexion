<?php
/**
 * How-to tab.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Views
 */

?><style>
	.yith-wcbep-how-to-content {
		text-align  : center;
		margin      : 0 auto;
		max-width   : 1000px;
		font-size   : 18px;
		font-weight : 300;
	}

	.yith-wcbep-how-to-content p {
		font-size   : 1em;
		line-height : 1.5em;
		margin      : 3em 0;
	}

	.yith-wcbep-how-to-content img {
		width      : 100%;
		box-shadow : 2px 4px 20px 0 #bdeae966;
	}

	.yith-wcbep-how-to-content p.yith-wcbep-how-to__message {
		display       : block;
		margin-left   : auto;
		margin-right  : auto;
		margin-top    : 2em;
		border-radius : 7px;
		padding       : 10px;
		color         : #438cb1;
		background    : #f2fbff;
		border        : 1px solid #d0f0f7;
	}

	.yith-wcbep-how-to-content .cta {
		border-radius   : 50px;
		background      : #e09004;
		box-shadow      : none;
		outline         : none;
		color           : #fff;
		position        : relative;
		padding         : 10px 30px;
		text-align      : center;
		text-transform  : uppercase;
		font-weight     : 600;
		border          : none;
		text-decoration : none;
	}

</style>

<div class="yith-wcbep-how-to yith-plugin-fw-panel-custom-tab-container">
	<div class="yith-wcbep-how-to-content">
		<p>
			<?php
			// translators: %s is the plugin name.
			echo sprintf( esc_html__( 'This page of the documentation is a guide for the usage of the free version of the %s plugin.', 'yith-woocommerce-product-bundles' ), '<strong>YITH WooCommerce Product Bundles</strong>' );
			?>
			<br/>
			<?php esc_html_e( 'Now we analyze the necessary steps for the cretion of a product bundle, and its possible options.', 'yith-woocommerce-product-bundles' ); ?>
			<br/>
			<br/>
			<?php
			// translators: 1. the 'strong' HTML tag start; 2. the 'strong' HTML tag end.
			echo sprintf( esc_html__( 'Firstly, create a new product on "WooCommerce" and select the %1$sProduct Bundle%2$s type in the product detail page.', 'yith-woocommerce-product-bundles' ), '<strong>', '</strong>' );
			?>
		</p>
		<img src="<?php echo esc_url( YITH_WCPB_ASSETS_URL ); ?>/images/how-to/01.png" alt="bundle-01"/>

		<p>
			<?php
			// translators: 1. the 'strong' HTML tag start; 2. the 'strong' HTML tag end.
			echo sprintf( esc_html__( 'Now configure the options clickin on the new tab "%1$sBundled Options%2$s" that has been added to the "%1$sProduct Data%2$s" section of the product.', 'yith-woocommerce-product-bundles' ), '<strong>', '</strong>' );
			?>
		</p>
		<img src="<?php echo esc_url( YITH_WCPB_ASSETS_URL ); ?>/images/how-to/02.png" alt="bundle-02"/>

		<p>
			<?php esc_html_e( 'Add the products you want to add in the bundle using the related popup.', 'yith-woocommerce-product-bundles' ); ?>
		</p>
		<img src="<?php echo esc_url( YITH_WCPB_ASSETS_URL ); ?>/images/how-to/03.png" alt="bundle-03"/>

		<p class="yith-wcbep-how-to__message"><?php esc_html_e( 'The plugin lets you add only simple products.', 'yith-woocommerce-product-bundles' ); ?></p>
		<p>
			<?php esc_html_e( 'Select the quantity of the products to be added in the bundle.', 'yith-woocommerce-product-bundles' ); ?>
		</p>
		<img src="<?php echo esc_url( YITH_WCPB_ASSETS_URL ); ?>/images/how-to/04.png" alt="bundle-04"/>

		<p>
			<?php echo sprintf( esc_html__( 'Now you just have to set the price, the description and the image of your bundle product and you will be done.', 'yith-woocommerce-product-bundles' ), '<strong>', '</strong>' ); ?>
		</p>
		<img src="<?php echo esc_url( YITH_WCPB_ASSETS_URL ); ?>/images/how-to/05.png" alt="bundle-05"/>

		<p>
			<?php esc_html_e( 'Here you are an example of a "Bundle" product.', 'yith-woocommerce-product-bundles' ); ?>
		</p>
		<img src="<?php echo esc_url( YITH_WCPB_ASSETS_URL ); ?>/images/how-to/06.png" alt="bundle-06"/>

		<p>
			<?php esc_html_e( 'As you can see, the available products of the bundle have been added in the selected quantity.', 'yith-woocommerce-product-bundles' ); ?>
		</p>

		<p>
			<a class="cta" href="//yithemes.com/docs-plugins/yith-woocommerce-product-bundles/"><?php esc_html_e( 'View plugin documentation', 'yith-woocommerce-product-bundles' ); ?></a>
		</p>
	</div>
</div>
