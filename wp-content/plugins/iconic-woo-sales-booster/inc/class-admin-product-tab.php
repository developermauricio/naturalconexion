<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Admin_Tab.
 *
 * @class    Iconic_WSB_Admin_Product_Tab
 * @version  1.0.0
 */
class Iconic_WSB_Admin_Product_Tab {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_fields' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_data_panels' ), 10 );
	}

	/**
	 * Outputs the Sales booster panel in Product edit screen.
	 */
	public static function product_data_panels() {
		global $iconic_wsb_class, $post_id;

		$fbt               = Iconic_WSB_Order_Bump_Product_Page_Manager::get_instance();
		$after_add_to_cart = Iconic_WSB_Order_Bump_Product_Page_Modal_Manager::get_instance();

		$data = array(
			'fbt_dropdown_options'               => $fbt->get_bump_products_tab_data(),
			'after_add_to_cart_dropdown_options' => $after_add_to_cart->get_bump_products_tab_data(),
			'fbt_fields'                         => $fbt->get_fields_data( $post_id ),
			'settings'                           => $fbt->get_settings(),
		);

		$iconic_wsb_class->template->include_template( 'admin/order-bump/product/product-data-panel.php', $data );
	}

	/**
	 * Save fields.
	 *
	 * @param int $product_id
	 *
	 * @return void
	 */
	public static function save_fields( $product_id ) {
		if ( isset( $_REQUEST['iconic-wsb-fbt-discount-value'], $_REQUEST['iconic-wsb-fbt-discount-type'], $_REQUEST['iconic-wsb-fbt-title'] ) ) {
			// security.
			$title         = filter_input( INPUT_POST, 'iconic-wsb-fbt-title' );
			$value         = wc_format_decimal( filter_input( INPUT_POST, 'iconic-wsb-fbt-discount-value' ) );
			$type          = filter_input( INPUT_POST, 'iconic-wsb-fbt-discount-type' );
			$sales_pitch   = filter_input( INPUT_POST, 'iconic-wsb-fbt-sales-pitch' );
			$set_unchecked = filter_input( INPUT_POST, 'iconic-wsb-fbt-set-unchecked' );

			$title = sanitize_text_field( $title );
			$type  = 'percentage' === $type ? 'percentage' : 'simple';

			update_post_meta( $product_id, '_iconic_wsb_fbt_discount_type', $type );
			update_post_meta( $product_id, '_iconic_wsb_fbt_title', $title );
			update_post_meta( $product_id, '_iconic_wsb_fbt_sales_pitch', $sales_pitch );
			update_post_meta( $product_id, '_iconic_wsb_fbt_set_unchecked', $set_unchecked );

			if ( is_numeric( $value ) ) {
				if ( 'percentage' === $type && $value < 0 ) {
					$value = 0;
				}
				if ( 'percentage' === $type && $value > 100 ) {
					$value = 100;
				}
				update_post_meta( $product_id, '_iconic_wsb_fbt_discount_value', $value );
			} else {
				update_post_meta( $product_id, '_iconic_wsb_fbt_discount_value', '' );
			}
		}
	}
}
