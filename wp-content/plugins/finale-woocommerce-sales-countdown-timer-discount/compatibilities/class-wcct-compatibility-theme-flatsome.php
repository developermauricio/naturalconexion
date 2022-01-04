<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Compatibility_Theme_Flatsome {

	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'maybe_flatsome_theme' ), 99 );
	}

	public function maybe_flatsome_theme() {
		if ( class_exists( 'Flatsome_Option' ) ) {
			add_action( 'woocommerce_before_template_part', array( $this, 'woocommerce_before_template_part' ), 99, 1 );
		}
	}

	public function woocommerce_before_template_part( $template_name ) {
		global $product;
		if ( 'content-single-product-lightbox.php' === $template_name && $product instanceof WC_Product ) {
			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product );

			WCCT_Core()->public->wcct_get_product_obj( $parent_id );
			WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );
		}
	}
}

new WCCT_Compatibility_Theme_Flatsome();
