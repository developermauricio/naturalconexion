<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Assets.
 *
 * Register/enqueue frontend and backend scripts.
 *
 * @class    Iconic_WSB_Assets
 * @version  1.0.0
 */
class Iconic_WSB_Assets {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
	}

	/**
	 * Frontend assets.
	 */
	public static function frontend_assets() {
		if ( is_checkout() || is_product() || is_cart() ) {
			wp_enqueue_script( 'magnific-popup', ICONIC_WSB_URL . 'assets/vendor/magnific/jquery.magnific-popup.min.js', [ 'jquery' ], ICONIC_WSB_VERSION, true );
			wp_enqueue_style( 'magnific-popup-style', ICONIC_WSB_URL . 'assets/vendor/magnific/magnific-popup.css', [], ICONIC_WSB_VERSION );

			wp_enqueue_script( 'iconic_wsb_frontend_scripts', ICONIC_WSB_URL . 'assets/frontend/js/main.js', [ 'jquery', 'wc-add-to-cart' ], ICONIC_WSB_VERSION, true );

			$settings = Iconic_WSB_Order_Bump_Product_Page_Manager::get_instance()->get_settings();

			$args = [
				'ajax_url'     => WC()->ajax_url(),
				'nonce'        => wp_create_nonce( 'iconic_wsb_nonce' ),
				'fbt_use_ajax' => $settings['use_ajax'],
				'i18n'         => array(
					'error'   => __( 'Please Try Again', 'iconic-wsb' ),
					'success' => __( 'Added to Cart', 'iconic-wsb' ),
					'add_selected' => __( 'Add Selected to Cart', 'iconic-wsb' ),
					'disabled_add_to_cart' => __( 'Please select a variation before adding the selected products to your cart.', 'iconic-wsb' ),
				),
			];

			wp_localize_script( 'iconic_wsb_frontend_scripts', 'iconic_wsb_frontend_vars', $args );
		}

		wp_enqueue_style( 'iconic_wsb_frontend_style', ICONIC_WSB_URL . 'assets/frontend/css/main.css', array(), ICONIC_WSB_VERSION );
	}

	/**
	 * Admin assets.
	 */
	public static function admin_assets() {
		global $wp_query;

		if ( in_array( get_current_screen()->post_type, array(
			Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance()->get_post_type(),
			Iconic_WSB_Order_Bump_After_Checkout_Manager::get_instance()->get_post_type(),
			'product',
		) ) ) {
			// WooCommerce
			wp_enqueue_script( 'jquery-blockui',
				WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . '.min' . '.js', array( 'jquery' ),
				'2.70', true );
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(),
				WC_VERSION );
			wp_enqueue_script( 'jquery-ui-sortable' );

			// color picker
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_style( 'iconic_wsb_admin_bump_edit_style', ICONIC_WSB_URL . 'assets/admin/css/main.css',
				array(), WC_VERSION );

			$args = [];

			if ( get_current_screen()->base == 'edit' ) {
				$args['posts']     = array_map( function ( $post ) {
					return $post->ID;
				}, $wp_query->posts );
				$args['post_type'] = get_current_screen()->post_type;
			}

			if ( get_current_screen()->base == 'post' ) {
				$args['postId'] = get_the_ID();
			}

			wp_enqueue_script( 'iconic_wsb_admin_bump_edit_script', ICONIC_WSB_URL . '/assets/admin/js/main.js',
				array( 'jquery', 'jquery-ui-sortable', 'jquery-blockui', 'wp-color-picker' ), ICONIC_WSB_VERSION );
			wp_localize_script( 'iconic_wsb_admin_bump_edit_script', 'iconic_wsb_admin_vars', $args );
		}
	}
}