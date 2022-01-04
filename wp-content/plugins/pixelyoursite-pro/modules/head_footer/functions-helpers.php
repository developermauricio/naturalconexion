<?php

namespace PixelYourSite\HeadFooter\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;
use function PixelYourSite\wooGetOrderIdFromRequest;
use function PixelYourSite\wooIsRequestContainOrderId;

function get_content_id() {
	global $post;
	return is_singular() ? $post->ID : '';
}

function get_content_title() {
	global $post;

	if ( is_singular() && ! is_page() ) {

		return $post->post_title;

	} elseif ( is_page() || is_home() ) {

		return is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;

	} elseif ( PixelYourSite\isWooCommerceActive() && is_shop() ) {

		return get_the_title( wc_get_page_id( 'shop' ) );

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
        if(!$term) return "";

		return $term->name;

	} else {

		return '';

	}

}

function get_content_categories() {
	global $post;

	return is_single() ? PixelYourSite\getObjectTerms( 'category', $post->ID ) : '';

}

function get_user_email() {

	$user = wp_get_current_user();

	if ( $user ) {
		return $user->user_email;
	} else {
		return '';
	}

}

function get_user_first_name() {

	$user = wp_get_current_user();

	if ( $user ) {
		return $user->user_firstname;
	} else {
		return '';
	}

}

function get_user_last_name() {

	$user = wp_get_current_user();

	if ( $user ) {
		return $user->user_lastname;
	} else {
		return '';
	}

}

function get_order_id() {

	if ( PixelYourSite\isWooCommerceActive() && is_order_received_page() &&
        wooIsRequestContainOrderId()
    ) {
		return wooGetOrderIdFromRequest();

	} elseif ( PixelYourSite\isEddActive() && edd_is_success_page() ) {

		return get_edd_order_meta( 'id' );

	} else {
		return '';
	}

}

function get_order_subtotal() {

	if ( PixelYourSite\isWooCommerceActive() && is_order_received_page() &&
        wooIsRequestContainOrderId()
    ) {
		$order_id = wooGetOrderIdFromRequest();
		$order    = new \WC_Order( $order_id );

		return $order->get_subtotal();

	} elseif ( PixelYourSite\isEddActive() && edd_is_success_page() ) {

		return get_edd_order_meta( 'subtotal' );

	} else {
		return '';
	}

}

function get_order_total() {

	if ( PixelYourSite\isWooCommerceActive() && is_order_received_page()
        && wooIsRequestContainOrderId()) {

		$order_id = wooGetOrderIdFromRequest();
		$order    = new \WC_Order( $order_id );

		return $order->get_total();

	} elseif ( PixelYourSite\isEddActive() && edd_is_success_page() ) {
		
		return get_edd_order_meta( 'total' );

	} else {
		return '';
	}

}

function get_order_currency() {

	if ( PixelYourSite\isWooCommerceActive() && is_order_received_page()
        && wooIsRequestContainOrderId()
    ) {
		return get_woocommerce_currency();

	} elseif ( PixelYourSite\isEddActive() && edd_is_success_page() ) {

		return edd_get_currency();

	} else {
		return '';
	}

}

function get_edd_order_meta( $metakey ) {
	global $edd_receipt_args;

	// skip payment confirmation page
	if ( isset( $_GET['payment-confirmation'] ) ) {
		return '';
	}

	$session = edd_get_purchase_session();
	if ( isset( $_GET['payment_key'] ) ) {
		$payment_key = urldecode( $_GET['payment_key'] );
	} else if ( $session ) {
		$payment_key = $session['purchase_key'];
	} elseif ( $edd_receipt_args['payment_key'] ) {
		$payment_key = $edd_receipt_args['payment_key'];
	}

	if ( ! isset( $payment_key ) ) {
		return '';
	}

	$payment_id    = edd_get_purchase_id_by_key( $payment_key );
	$user_can_view = edd_can_view_receipt( $payment_key );

	if ( ! $user_can_view && ! empty( $payment_key ) && ! is_user_logged_in() && ! edd_is_guest_payment( $payment_id ) ) {
		return '';
	}

	switch ( $metakey ) {
		case 'id':
			return $payment_id;
			break;

		case 'subtotal':
			return $session['subtotal'];
			break;

		case 'total':
			return $session['price'];
			break;

		default:
			return '';
	}

}