<?php

namespace PixelYourSite\SuperPack;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function maybeMigrate() {
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$sp_version = get_option( 'pys_super_pack_version', false );
	
	// migrate from 1.x
	if ( ! $sp_version ) {
		
		migrate_v1_options();

		update_option( 'pys_super_pack_version', PYS_SUPER_PACK_VERSION );
	
	}

}

function migrate_v1_options() {

	$v1 = get_option( 'pys_super_pack', array() );
	
	$v2 = array(
		'license_key'     => isset( $v1['license_key'] ) ? $v1['license_key'] : null,
		'license_status'  => isset( $v1['license_status'] ) ? $v1['license_status'] : null,
		'license_expires' => isset( $v1['license_expires'] ) ? $v1['license_expires'] : null,
		
		'additional_ids_enabled'                   => isset( $v1['additional_ids_enabled'] ) ? $v1['additional_ids_enabled'] : null,
		'dynamic_params_enabled'                   => isset( $v1['dynamic_params_enabled'] ) ? $v1['dynamic_params_enabled'] : null,
		
		'custom_thank_you_page_enabled'            => isset( $v1['custom_thank_you_page_enabled'] ) ? $v1['custom_thank_you_page_enabled'] : null,
		'woo_custom_thank_you_page_global_enabled' => isset( $v1['custom_thank_you_page_global_enabled'] ) ? $v1['custom_thank_you_page_global_enabled'] : null,
		'woo_custom_thank_you_page_global_url'     => isset( $v1['custom_thank_you_page_global_url'] ) ? $v1['custom_thank_you_page_global_url'] : null,
		'woo_custom_thank_you_page_global_cart'    => isset( $v1['custom_thank_you_page_global_cart'] ) ? $v1['custom_thank_you_page_global_cart'] : null,
		'edd_custom_thank_you_page_global_enabled' => isset( $v1['edd_custom_thank_you_page_global_enabled'] ) ? $v1['edd_custom_thank_you_page_global_enabled'] : null,
		'edd_custom_thank_you_page_global_url'     => isset( $v1['edd_custom_thank_you_page_global_url'] ) ? $v1['edd_custom_thank_you_page_global_url'] : null,
		'edd_custom_thank_you_page_global_cart'    => isset( $v1['edd_custom_thank_you_page_global_cart'] ) ? $v1['edd_custom_thank_you_page_global_cart'] : null,
		'remove_pixel_enabled'                     => isset( $v1['remove_pixel_enabled'] ) ? $v1['remove_pixel_enabled'] : null,
		'amp_enabled'                              => isset( $v1['amp_enabled'] ) ? $v1['amp_enabled'] : null
	);
	
	// cleanup
	foreach ( $v2 as $key => $value ) {
		if ( $value === null ) {
			unset( $v2[ $key ] );
		}
	}
	
	// update settings
	PixelYourSite\SuperPack()->updateOptions( $v2 );
	PixelYourSite\SuperPack()->reloadOptions();
	
}