<?php

class WCCT_Compatibility_With_Polylang {

	public function __construct() {

		add_filter( 'pll_get_post_types', array( $this, 'wcct_add_countdown_post_type_to_polylang' ), 10, 2 );

	}

	/**
	 * @param $post_types
	 * @param $is_settings
	 *
	 * @return mixed
	 */
	public function wcct_add_countdown_post_type_to_polylang( $post_types, $is_settings ) {
		if ( $is_settings ) {
			if ( isset( $post_types['wcct_countdown'] ) ) {
				unset( $post_types['wcct_countdown'] );
			}
		} else {
			$post_types['wcct_countdown'] = 'wcct_countdown';
		}

		return $post_types;
	}

}

new WCCT_Compatibility_With_Polylang();
