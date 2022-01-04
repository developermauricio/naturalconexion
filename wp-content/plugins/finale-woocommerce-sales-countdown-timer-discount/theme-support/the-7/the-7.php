<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_the7' );

function wcct_theme_helper_the7() {
	$wcct_core = WCCT_Core()->appearance;
	//handling above and below title
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_above_title' ), 2.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_title' ), 9.3 );

	add_filter( 'presscore_page_title', 'wcct_presscore_location_below_and_above_title', 10, 1 );

	if ( ! function_exists( 'wcct_presscore_location_below_and_above_title' ) ) {
		function wcct_presscore_location_below_and_above_title( $content ) {
			$wcct_core = WCCT_Core()->appearance;
			ob_start();
			echo '<div class="wf-td">';
			$wcct_core->wcct_position_above_title();
			echo '</div>';
			echo $content;
			echo '<div class="wf-td">';
			$wcct_core->wcct_position_below_title();
			echo '</div>';

			return ob_get_clean();
		}
	}

	//handling price and review hooks
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 12 );

	remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.3 );
	add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 12.2 );

	add_action( 'woocommerce_after_single_product_summary', function () {
		echo '<div class="wcct_clear wcct_clear_20"></div>';
	}, 20 );
}
