<?php
defined( 'ABSPATH' ) || exit;

$settings = WCCT_Common::get_global_default_settings();
if ( 'old' === $settings['wcct_positions_approach'] ) {
	return;
}

add_action( 'wp', 'wcct_theme_helper_oceanwp', 99 );
if ( ! function_exists( 'wcct_theme_helper_oceanwp' ) ) {

	function wcct_theme_helper_oceanwp() {
		$wcct_core = WCCT_Core()->appearance;
		if ( ! $wcct_core instanceof WCCT_Appearance ) {
			return;
		}

		// removing wcct action hooks on theme
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_price' ), 17.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_short_desc' ), 21.2 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_add_cart' ), 39.2 );
	}
}

add_action( 'woocommerce_before_template_part', 'wcct_theme_helper_oceanwp_before_template_part', 99 );

if ( ! function_exists( 'wcct_theme_helper_oceanwp_before_template_part' ) ) {
	function wcct_theme_helper_oceanwp_before_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
		if ( empty( $template_name ) ) {
			return;
		}

		$wcct_core = WCCT_Core()->appearance;
		if ( ! $wcct_core instanceof WCCT_Appearance ) {
			return;
		}
		if ( 'single-product/title.php' === $template_name ) {
			$wcct_core->wcct_position_above_title();
		}
	}
}

add_action( 'woocommerce_after_template_part', 'wcct_theme_helper_oceanwp_after_template_part', 99 );

if ( ! function_exists( 'wcct_theme_helper_oceanwp_after_template_part' ) ) {
	function wcct_theme_helper_oceanwp_after_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {

		if ( empty( $template_name ) ) {
			return;
		}

		$wcct_core = WCCT_Core()->appearance;
		if ( ! $wcct_core instanceof WCCT_Appearance ) {
			return;
		}

		if ( 'single-product/title.php' === $template_name ) {
			$wcct_core->wcct_position_below_title();
		} elseif ( 'single-product/short-description.php' === $template_name ) {
			$wcct_core->wcct_position_below_short_desc();
		} elseif ( 'single-product/rating.php' === $template_name ) {
			$wcct_core->wcct_position_below_review();
		} elseif ( 'single-product/price.php' === $template_name ) {
			$wcct_core->wcct_position_below_price();
		}
	}
}

/**
 * Handling for below add to cart position Starts here
 */
add_action( 'woocommerce_after_add_to_cart_form', 'wcct_theme_helper_oceanwp_after_add_to_cart_template' );
if ( ! function_exists( 'wcct_theme_helper_oceanwp_after_add_to_cart_template' ) ) {
	function wcct_theme_helper_oceanwp_after_add_to_cart_template() {

		$wcct_core = WCCT_Core()->appearance;
		$output    = '';

		if ( $wcct_core instanceof WCCT_Appearance ) {
			ob_start();
			$wcct_core->wcct_position_below_add_cart();
			$output = ob_get_clean();

			if ( '' !== $output ) {
				echo '<div class="wcct_clear" style="height: 15px;"></div>';
			}
		}

		echo $output;
	}
}
