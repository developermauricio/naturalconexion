<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp', 'wcct_theme_helper_savoy', 99 );
if ( ! function_exists( 'wcct_theme_helper_savoy' ) ) {

	function wcct_theme_helper_savoy() {
		$wcct_core = WCCT_Core()->appearance;

		// removing wcct action hooks on theme
		remove_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 11.2 );
		add_action( 'woocommerce_single_product_summary', array( $wcct_core, 'wcct_position_below_review' ), 22 );

		add_action( 'woocommerce_after_single_product_summary', function () {
			echo '<div class="wcct_clear_20"></div><div class="nm-row"><div class="col-xs-12">';
		}, 8.8 );
		add_action( 'woocommerce_after_single_product_summary', function () {
			echo '<div class="nm-row"><div class="col-xs-12">';
		}, 20.2 );
		add_action( 'woocommerce_after_single_product_summary', function () {
			echo '</div></div>';
		}, 10.8 );
		add_action( 'woocommerce_after_single_product_summary', function () {
			echo '</div></div>';
		}, 22.2 );
	}
}
