<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_nitro', 99 );

function wcct_theme_helper_nitro() {
	if ( is_singular( 'product' ) ) {
		$wcct_core     = WCCT_Core()->appearance;
		$nitro_options = WR_Nitro_Customize::get_options();
		if ( is_array( $nitro_options ) && isset( $nitro_options['wc_single_style'] ) ) {
			$single_style = $nitro_options['wc_single_style'];

			if ( $single_style == '1' ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.2 );

				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
				add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 14 );
				add_action( 'woocommerce_after_single_product_summary', function () {
					echo '<div class="p-single-middle clear">';
				}, 1 );
				add_action( 'woocommerce_after_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 2 );
				add_action( 'woocommerce_after_single_product_summary', function () {
					echo '</div>';
				}, 9 );
			} elseif ( $single_style == '2' ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.2 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.2 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );

				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
				add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 1 );
				add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 4 );
				add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 14 );
			} elseif ( $single_style == '3' ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );

				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
				add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 14 );

			} elseif ( $single_style == '4' ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );

				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
				add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 14 );

			} elseif ( $single_style == '5' ) {
				if ( ! wp_is_mobile() ) {
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				}
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.2 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.2 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.2 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.2 );
				remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.2 );

				add_action( 'woocommerce_before_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 8 );
				add_action( 'woocommerce_before_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9 );
				add_action( 'wcct_nitro_theme_below_price', array( $wcct_core, 'wcct_position_below_price' ), 10 );
				add_action( 'woocommerce_before_add_to_cart_form', array( $wcct_core, 'wcct_position_below_short_desc' ), 10 );
				add_action( 'woocommerce_after_add_to_cart_form', function () {
					echo '<div class="wcct_clear wcct_clear_10"></div>';
				}, 9 );
				add_action( 'woocommerce_after_add_to_cart_form', array( $wcct_core, 'wcct_position_below_add_cart' ), 10 );
			}
		}
	}
}


add_action( 'woocommerce_after_template_part', 'wcct_theme_helper_nitro_after_template_part', 99 );

function wcct_theme_helper_nitro_after_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
	if ( empty( $template_name ) ) {
		return '';
	}
	if ( $template_name == 'woorockets/single-product/builder.php' ) {
		do_action( 'wcct_nitro_theme_below_related_products' );
	} elseif ( $template_name == 'single-product/price.php' ) {
		do_action( 'wcct_nitro_theme_below_price' );
	}
}
