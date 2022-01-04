<?php

class WCCT_Compatibility_With_Woo_Picker_Location {

	public function __construct() {

		/**
		 * Checking If WooCommerce Picker Location Plugin is installed or not
		 */
		if ( ! defined( 'WPPKPO_VERSION' ) ) {
			return;
		}

		add_action( 'admin_footer', array( $this, 'wcct_deque_woo_picker_location_scripts' ), 99 );

	}

	public function wcct_deque_woo_picker_location_scripts() {
		if ( WCCT_Common::wcct_valid_admin_pages() ) {
			wp_dequeue_script( 'wp_pkpo_widgetpicker' );
		}
	}

}

new WCCT_Compatibility_With_Woo_Picker_Location();
