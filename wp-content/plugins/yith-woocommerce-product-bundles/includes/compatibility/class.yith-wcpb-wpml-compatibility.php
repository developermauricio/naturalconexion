<?php
/**
 * WPML integration class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles
 */

defined( 'YITH_WCPB' ) || exit;

/**
 * WPML Compatibility Class
 *
 * @class   YITH_WCPB_Wpml_Compatibility
 * @since   1.1.2 Free
 */
class YITH_WCPB_Wpml_Compatibility {

	/**
	 * Single instance of the class
	 *
	 * @var YITH_WCPB_Wpml_Compatibility
	 */
	protected static $instance;

	/**
	 * Meta to copy.
	 *
	 * @var string[]
	 */
	public $bundle_meta_to_copy = array( '_yith_wcpb_bundle_data' );

	/**
	 * Singleton implementation
	 *
	 * @return YITH_WCPB_Wpml_Compatibility|YITH_WCPB_Wpml_Compatibility_Premium
	 */
	public static function get_instance() {
		/**
		 * The class.
		 *
		 * @var YITH_WCPB_Wpml_Compatibility|YITH_WCPB_Wpml_Compatibility_Premium $self
		 */
		$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

		return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		add_action( 'wcml_after_duplicate_product_post_meta', array( $this, 'bundles_sync' ), 10, 2 );
	}

	/**
	 * Bundle sync
	 *
	 * @param int $original_product_id   Original product id.
	 * @param int $translated_product_id Translated product id.
	 */
	public function bundles_sync( $original_product_id, $translated_product_id ) {
		foreach ( $this->bundle_meta_to_copy as $bundle_meta ) {
			$data = get_post_meta( $translated_product_id, $bundle_meta, true );
			if ( is_array( $data ) && '_yith_wcpb_bundle_data' === $bundle_meta ) {
				$language = apply_filters( 'wpml_post_language_details', null, $translated_product_id );
				foreach ( $data as &$product ) {
					$product['product_id'] = apply_filters(
						'wpml_object_id',
						$product['product_id'],
						'product',
						true,
						$language['language_code']
					);
				}
				update_post_meta( $translated_product_id, $bundle_meta, $data );
			}
		}
	}

	/**
	 * Retrieve the WPML parent product id
	 *
	 * @param int $id The ID.
	 *
	 * @return int
	 */
	public function get_parent_id( $id ) {
		/**
		 * WPML Post Translations instance
		 *
		 * @var WPML_Post_Translation $wpml_post_translations
		 */
		global $wpml_post_translations;
		if ( $wpml_post_translations ) {
			$parent_id = $wpml_post_translations->get_original_element( $id );
			if ( $parent_id ) {
				$id = $parent_id;
			}
		}

		return absint( $id );
	}

	/**
	 * Get id of post translation in current language
	 *
	 * @param int         $element_id                 Element ID.
	 * @param string      $element_type               Element type.
	 * @param bool        $return_original_if_missing Set true to return original if missing.
	 * @param null|string $language_code              Language code.
	 *
	 * @return int the translation id
	 */
	public function wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null ) {
		if ( function_exists( 'wpml_object_id_filter' ) ) {
			return wpml_object_id_filter( $element_id, $element_type, $return_original_if_missing, $language_code );
		} elseif ( function_exists( 'icl_object_id' ) ) {
			return icl_object_id( $element_id, $element_type, $return_original_if_missing, $language_code );
		} else {
			return $element_id;
		}
	}
}
