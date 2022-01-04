<?php

/**
 * Class WCCT_Compatibility_Product_CSV_Import_Export_For_Woocommerce
 * This Class is responsive for controlling the behaviour of saving product using Product CSV Import Export For Woocommerce plugin.
 */

if ( class_exists( 'WF_Product_Import_Export_CSV' ) ) {
	class WCCT_Product_CSV_Import_Export_For_Woocommerce {
		private static $ins = null;

		public function __construct() {
			add_action( 'save_post_product', array( __CLASS__, 'delete_product_taxonomy_ids_meta_product_csv_when_post_update' ), 99 );
			add_action( 'wf_refresh_after_product_import', array( $this, 'delete_product_taxonomy_ids_meta_product_csv_imp' ), 99, 3 );
		}

		public static function get_instance() {
			if ( null === self::$ins ) {
				self::$ins = new self;
			}

			return self::$ins;
		}

		public function delete_product_taxonomy_ids_meta_product_csv_when_post_update( $post_id ) {
			delete_post_meta( $post_id, '_wcct_product_taxonomy_term_ids' );
		}

		public function delete_product_taxonomy_ids_meta_product_csv_imp( $processing_product_object, $post, $parsed_item ) {
			delete_post_meta( $post['post_id'], '_wcct_product_taxonomy_term_ids' );
		}
	}

	WCCT_Product_CSV_Import_Export_For_Woocommerce::get_instance();
}
