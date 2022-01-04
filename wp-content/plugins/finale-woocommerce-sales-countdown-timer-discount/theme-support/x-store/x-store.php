<?php
add_action( 'wp_loaded', 'wcct_theme_helper_x_store', 100 );

function wcct_theme_helper_x_store() {
	$wcct_appearance_instance = WCCT_Core()->appearance;
	remove_action( 'wp_loaded', array( $wcct_appearance_instance, 'wcct_modify_positions' ), 9999 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_above_title' ), 2.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_below_title' ), 9.3 );
	remove_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_below_price' ), 17.3 );

	add_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_below_price' ), 25.1 );

	add_action( 'wp_loaded', function () {
		$settings = WCCT_Common::get_global_default_settings();
		if ( 'new' === $settings['wcct_positions_approach'] ) {
			return;
		}

		$wcct_appearance_instance = WCCT_Core()->appearance;
		// removing wcct positions action hooks
		remove_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_below_review' ), 11.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_below_price' ), 25.1 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_below_short_desc' ), 21.3 );
		remove_action( 'woocommerce_single_product_summary', array( $wcct_appearance_instance, 'wcct_position_below_add_cart' ), 39.3 );

		/** Hooking 'price, review & short description' position */
		add_action( 'woocommerce_after_template_part', function ( $template_name = '' ) {
			if ( empty( $template_name ) ) {
				return;
			}

			$wcct_appearance_instance = WCCT_Core()->appearance;

			if ( 'single-product/short-description.php' === $template_name ) {
				$wcct_appearance_instance->wcct_position_below_short_desc();
			} elseif ( 'single-product/rating.php' === $template_name ) {
				$wcct_appearance_instance->wcct_position_below_review();
			} elseif ( 'single-product/price.php' === $template_name ) {
				$wcct_appearance_instance->wcct_position_below_price();
			}
		}, 49 );

		/** Hooking 'below add to cart' position */
		add_action( 'woocommerce_after_add_to_cart_form', array( $wcct_appearance_instance, 'wcct_add_to_cart_template' ), 49 );
	}, 9999 );

	add_filter( 'woocommerce_show_page_title', function ( $bool ) {
		$wcct_appearance_instance = WCCT_Core()->appearance;
		$wcct_appearance_instance->wcct_position_above_title();

		if ( etheme_get_option( 'product_name_signle' ) && is_single() && ! is_attachment() ) {
			echo '<h1 class="title">';
			echo WCCT_Common::get_the_title();
			echo '</h1>';
		} elseif ( ! is_single() ) {
			echo '<h1 class="title">';
			echo woocommerce_page_title();
			echo '</h1>';
		}

		$wcct_appearance_instance->wcct_position_below_title();

		return false;
	}, 999 );

	// shop loop
	remove_action( 'woocommerce_after_shop_loop_item', array( $wcct_appearance_instance, 'wcct_bar_timer_show_on_grid' ), 9 );

	// hooking after shop loop function
	add_action( 'woocommerce_after_shop_loop_item_title', array( $wcct_appearance_instance, 'wcct_bar_timer_show_on_grid' ), 20 );
}
