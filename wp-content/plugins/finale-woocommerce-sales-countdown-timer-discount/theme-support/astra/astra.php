<?php
defined( 'ABSPATH' ) || exit;

$settings = WCCT_Common::get_global_default_settings();
if ( 'old' == $settings['wcct_positions_approach'] ) {
	return;
}

/**
 * Remove all existing finale positions on single product page
 */
add_action( 'wp', 'wcct_theme_helper_astra', 99 );
if ( ! function_exists( 'wcct_theme_helper_astra' ) ) {
	function wcct_theme_helper_astra() {
		if ( ! function_exists( 'WCCT_Core' ) ) {
			return;
		}
		$wcct_core = WCCT_Core()->appearance;

		// removing actions
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.3 );
	}
}

add_action( 'woocommerce_before_template_part', 'wcct_theme_helper_astra_before_template_part', 99 );
if ( ! function_exists( 'wcct_theme_helper_astra_before_template_part' ) ) {
	function wcct_theme_helper_astra_before_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
		if ( ! function_exists( 'WCCT_Core' ) ) {
			return;
		}
		$wcct_core = WCCT_Core()->appearance;
		if ( empty( $template_name ) ) {
			return '';
		}
		if ( $template_name == 'single-product/title.php' ) {
			echo $wcct_core->wcct_position_above_title();
		}
	}
}
add_action( 'woocommerce_after_template_part', 'wcct_theme_helper_astra_after_template_part', 99 );
if ( ! function_exists( 'wcct_theme_helper_astra_after_template_part' ) ) {
	function wcct_theme_helper_astra_after_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
		if ( ! function_exists( 'WCCT_Core' ) ) {
			return;
		}
		$wcct_core = WCCT_Core()->appearance;
		if ( empty( $template_name ) ) {
			return '';
		}
		if ( $template_name == 'single-product/title.php' ) {
			echo $wcct_core->wcct_position_below_title();
		} elseif ( $template_name == 'single-product/rating.php' ) {
			echo $wcct_core->wcct_position_below_review();
		} elseif ( $template_name == 'single-product/price.php' ) {
			echo $wcct_core->wcct_position_below_price();
		} elseif ( $template_name == 'single-product/short-description.php' ) {
			echo $wcct_core->wcct_position_below_short_desc();
		}
	}
}
/**
 * Handling for below add to cart position Starts here
 */
add_action( 'woocommerce_after_add_to_cart_form', 'wcct_theme_helper_astra_after_add_to_cart', 99 );
if ( ! function_exists( 'wcct_theme_helper_astra_after_add_to_cart' ) ) {
	function wcct_theme_helper_astra_after_add_to_cart() {
		if ( ! function_exists( 'WCCT_Core' ) ) {
			return;
		}
		$wcct_core = WCCT_Core()->appearance;
		$output    = '';
		ob_start();
		echo $wcct_core->wcct_position_below_add_cart();
		$output = ob_get_clean();
		if ( $output !== '' ) {
			echo '<div class="wcct_clear" style="height: 15px;"></div>';
		}
		echo $output;
	}
}
