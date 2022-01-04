<?php

add_action( 'admin_print_scripts', 'wcct_deque_traveler_theme_scripts', - 1 );

if ( ! function_exists( 'wcct_deque_traveler_theme_scripts' ) ) {

	function wcct_deque_traveler_theme_scripts() {
		if ( WCCT_Common::wcct_valid_admin_pages() ) {
			wp_dequeue_script( 'custom-iconpicker' );
		}
	}
}
