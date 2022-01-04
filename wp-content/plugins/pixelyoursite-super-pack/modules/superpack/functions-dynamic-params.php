<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'dynamic_params_enabled' ) ) {
	add_action( 'pys_superpack_dynamic_params_help', 'PixelYourSite\SuperPack\renderDynamicParamsHelp' );
}

if ( PixelYourSite\SuperPack()->configured() && PixelYourSite\SuperPack()->getOption( 'dynamic_params_enabled' ) ) {
	add_filter( 'pys_superpack_dynamic_params', 'PixelYourSite\SuperPack\replaceDynamicParamsPlaceholders', 10, 2 );
}

function renderDynamicParamsHelp() {
	/** @noinspection PhpIncludeInspection */
	require_once PYS_SUPER_PACK_PATH . '/modules/superpack/views/html-dynamic-params-help.php';
}

//@todo: +2.1+ cache values
function replaceDynamicParamsPlaceholders( $params, $context ) {
	
	foreach ( $params as $key => $value ) {
		
		if ( false !== strpos( $value, '[id]' ) ) {
			$params[ $key ] = replaceContentID( $value, $context );
		}
		
		if ( false !== strpos( $value, '[title]' ) ) {
			$params[ $key ] = replaceContentTitle( $value );
		}
		
		if ( false !== strpos( $value, '[content_type]' ) ) {
			$params[ $key ] = replaceContentType( $value );
		}
		
		if ( false !== strpos( $value, '[categories]' ) ) {
			$params[ $key ] = replaceContentCategories( $value );
		}
		
		if ( false !== strpos( $value, '[tags]' ) ) {
			$params[ $key ] = replaceContentTags( $value );
		}
		
		if ( false !== strpos( $value, '[total]' ) ) {
			$params[ $key ] = replaceTotalParam( $value );
		}
		
		if ( false !== strpos( $value, '[subtotal]' ) ) {
			$params[ $key ] = replaceSubtotalParam( $value );
		}
		
	}
	
	return $params;
	
}

function replaceContentID( $value, $context ) {
	global $post;
	
	$content_id = is_singular() ? $post->ID : '';
	
	if ( $context == 'facebook' ) {
		return str_replace( '[id]', "['" . $content_id . "']", $value );
	} else {
		return str_replace( '[id]', $content_id, $value );
	}
	
}

function replaceContentTitle( $value ) {
	global $post;
	
	if ( is_singular() && ! is_page() ) {
		
		$title = $post->post_title;
		
	} elseif ( is_page() || is_home() ) {
		
		$title = is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;
		
	} elseif ( PixelYourSite\isWooCommerceActive() && is_shop() ) {
		
		$title = get_the_title( wc_get_page_id( 'shop' ) );
		
	} elseif ( is_category() || is_tax() || is_tag() ) {
		
		if ( is_category() ) {
			
			$cat  = get_query_var( 'cat' );
			$term = get_category( $cat );
			
		} elseif ( is_tag() ) {
			
			$slug = get_query_var( 'tag' );
			$term = get_term_by( 'slug', $slug, 'post_tag' );
			
		} else {
			
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			
		}
		
		$title = $term->name;
		
	} else {
		
		$title = '';
		
	}
	
	return str_replace( '[title]', $title, $value );
	
}

function replaceContentType( $value ) {
	
	if ( is_singular() ) {
		$content_type = get_post_type();
	} else {
		$content_type = '';
	}
	
	return str_replace( '[content_type]', $content_type, $value );
	
}

function replaceContentCategories( $value ) {
	global $post;
	
	$content_categories = is_single() ? PixelYourSite\getObjectTerms( 'category', $post->ID ) : '';
    
	if(is_array($content_categories)) {
        $content_categories = implode(", ",$content_categories);
    }

	return str_replace( '[categories]', $content_categories, $value );
	
}

function replaceContentTags( $value ) {
	global $post;
	
	$content_tags = is_single() ? PixelYourSite\getObjectTerms( 'post_tag', $post->ID ) : '';

    if(is_array($content_tags)) {
        $content_tags = implode(", ",$content_tags);
    }
	return str_replace( '[tags]', $content_tags, $value );
	
}

function replaceTotalParam( $value ) {
	
	if ( PixelYourSite\isWooCommerceActive() && PixelYourSite\PYS()->getOption( 'woo_enabled' ) ) {
		if ( is_order_received_page() && isset( $_REQUEST['key'] ) ) {
            $order_key = sanitize_key($_REQUEST['key']);
			$order_id = (int) wc_get_order_id_by_order_key( $order_key );
			$order = new \WC_Order( $order_id );
			
			if ( $order ) {
				$total = $order->get_total( 'edit' );
				return str_replace( '[total]', $total, $value );
			}
			
		}
	}
	
	if ( PixelYourSite\isEddActive() && PixelYourSite\PYS()->getOption( 'edd_enabled' ) ) {
		if ( edd_is_success_page() ) {
			
			$payment_key = PixelYourSite\getEddPaymentKey();
			$payment_id = (int) edd_get_purchase_id_by_key( $payment_key );
			
			$total = PixelYourSite\getEddOrderTotal( $payment_id );
			
			return str_replace( '[total]', $total, $value );
			
		}
	}
	
	return str_replace( '[total]', null, $value );

}

function replaceSubtotalParam( $value ) {
	
	if ( PixelYourSite\isWooCommerceActive() && PixelYourSite\PYS()->getOption( 'woo_enabled' ) ) {
		if ( is_order_received_page() && isset( $_REQUEST['key'] ) ) {
            $order_key = sanitize_key($_REQUEST['key']);
			$order_id = (int) wc_get_order_id_by_order_key( $order_key );
			$order    = new \WC_Order( $order_id );
			
			if ( $order ) {
				$subtotal = $order->get_subtotal() + $order->get_total_tax( 'edit' );
				return str_replace( '[subtotal]', $subtotal, $value );
			}
			
		}
	}
	
	if ( PixelYourSite\isEddActive() && PixelYourSite\PYS()->getOption( 'edd_enabled' ) ) {
		if ( edd_is_success_page() ) {
			
			$payment_key = PixelYourSite\getEddPaymentKey();
			$payment_id  = (int) edd_get_purchase_id_by_key( $payment_key );
			
			$subtotal = edd_get_payment_subtotal( $payment_id );
			
			return str_replace( '[subtotal]', $subtotal, $value );
			
		}
	}
	
	return str_replace( '[subtotal]', null, $value );
	
}