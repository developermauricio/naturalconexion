<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'remove_pixel_enabled' ) ) {
	add_filter( 'pys_superpack_meta_box_screens', 'PixelYourSite\SuperPack\addRemovePixelMetaBox' );
	add_action( 'pys_superpack_meta_box', 'PixelYourSite\SuperPack\renderRemovePixelMetaBox' );
	add_action( 'pys_superpack_meta_box_save', 'PixelYourSite\SuperPack\saveRemovePixelMetaBox', 10, 2 );
}

if ( PixelYourSite\SuperPack()->configured() && PixelYourSite\SuperPack()->getOption( 'remove_pixel_enabled' ) ) {
	add_filter( 'pys_pixel_disabled', 'PixelYourSite\SuperPack\maybeRemovePixel', 10, 2 );
}

function addRemovePixelMetaBox( $screens ) {
	
	$screens[] = 'post';
	$screens[] = 'page';
	
	// add custom post types
	foreach ( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) as $post_type ) {
		$screens[] = $post_type->name;
	}
	
	return $screens;
	
}

function renderRemovePixelMetaBox() {
	include 'views/html-remove-pixel-meta-box.php';
}

function saveRemovePixelMetaBox( $post_id, $data ) {

	// Facebook
	if ( isset( $data['pys_super_pack_remove_pixel'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_remove_pixel', true );
	} else {
		delete_post_meta( $post_id, '_pys_super_pack_remove_pixel' );
	}
	
	// GA
	if ( isset( $data['pys_super_pack_remove_ga_pixel'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_remove_ga_pixel', true );
	} else {
		delete_post_meta( $post_id, '_pys_super_pack_remove_ga_pixel' );
	}
	
	// Pinterest
	if ( isset( $data['pys_super_pack_remove_pinterest_pixel'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_remove_pinterest_pixel', true );
	} else {
		delete_post_meta( $post_id, '_pys_super_pack_remove_pinterest_pixel' );
	}

}

function maybeRemovePixel( $remove, $context ) {
	global $post;
	
	switch ( $context ) {
		case 'facebook':
			return $post && get_post_meta( $post->ID, '_pys_super_pack_remove_pixel', true );
		
		case 'ga':
			return $post && get_post_meta( $post->ID, '_pys_super_pack_remove_ga_pixel', true );
		
		case 'pinterest':
			return $post && get_post_meta( $post->ID, '_pys_super_pack_remove_pinterest_pixel', true );
			
		default:
			return $remove;
	}

}
