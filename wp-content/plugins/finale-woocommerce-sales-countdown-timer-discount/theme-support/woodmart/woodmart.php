<?php

/**
 * Remove Woodmart theme styles on Finale single campaign page
 */
add_action( 'admin_print_styles', 'wcct_theme_woodmart_removing_styles_finale_campaign_load', - 1 );

function wcct_theme_woodmart_removing_styles_finale_campaign_load() {
	global $wp_styles;

	if ( false === WCCT_Common::wcct_valid_admin_pages( 'single' ) ) {
		return;
	}

	$mod_wp_scripts = $wp_styles;
	$assets         = $wp_styles;

	if ( is_object( $assets ) && isset( $assets->registered ) && count( $assets->registered ) > 0 ) {
		foreach ( $assets->registered as $handle => $script_obj ) {
			if ( ! isset( $script_obj->src ) || empty( $script_obj->src ) ) {
				continue;
			}
			$src = $script_obj->src;

			/** If no cmb2 in src continue */
			if ( strpos( $src, 'themes/woodmart' ) === false ) {
				continue;
			}

			/** Unset cmb2 script */
			unset( $mod_wp_scripts->registered[ $handle ] );

		}
	}

	$wp_styles = $mod_wp_scripts;

}
