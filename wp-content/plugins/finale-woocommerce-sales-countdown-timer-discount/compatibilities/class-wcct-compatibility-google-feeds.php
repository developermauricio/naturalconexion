<?php
defined( 'ABSPATH' ) || exit;

/**
 * Author: XLPlugins
 * Class WCCT_Compatibility_Google_Feeds
 */
class WCCT_Compatibility_Google_Feeds {


	public function __construct() {

		if ( defined( 'WOOCOMMERCE_GPF_VERSION' ) ) {
			add_filter( 'woocommerce_gpf_exclude_product', array( $this, 'maybe_setup_finale_data' ), 10, 2 );

		}
	}

	public function maybe_setup_finale_data( $excluded, $id ) {

		if ( true === $excluded ) {
			return $excluded;
		}

		$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $id );

		WCCT_Core()->public->wcct_get_product_obj( $parent_id );
		WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );

		return $excluded;
	}
}

new WCCT_Compatibility_Google_Feeds();
