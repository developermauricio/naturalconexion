<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'custom_thank_you_page_enabled' ) ) {
	
	if ( PixelYourSite\isWooCommerceActive() ) {
		
		add_filter( 'pys_superpack_meta_box_screens', 'PixelYourSite\SuperPack\addWooProductMetaBox' );
		add_action( 'pys_superpack_meta_box_product', 'PixelYourSite\SuperPack\renderCustomThankYouPageMetaBox' );
		add_action( 'pys_superpack_meta_box_save_product', 'PixelYourSite\SuperPack\saveCustomThankYouPageMetaBox', 10, 2 );
		
	}
	
	if ( PixelYourSite\isEddActive() ) {
		
		add_filter( 'pys_superpack_meta_box_screens', 'PixelYourSite\SuperPack\addEDDProductMetaBox' );
		add_action( 'pys_superpack_meta_box_download', 'PixelYourSite\SuperPack\renderCustomThankYouPageMetaBox' );
		add_action( 'pys_superpack_meta_box_save_download', 'PixelYourSite\SuperPack\saveCustomThankYouPageMetaBox', 10, 2 );
		
	}
	
}

if ( PixelYourSite\SuperPack()->configured() && PixelYourSite\SuperPack()->getOption( 'custom_thank_you_page_enabled' ) ) {
	
	if ( PixelYourSite\isWooCommerceActive() ) {

		add_action( 'template_redirect', 'PixelYourSite\SuperPack\maybeRedirectToWooCustomThankYouPage' );
		
		if ( isset( $_GET['pys_ctp'] ) && isset( $_GET['order_id'] ) ) {
			add_filter( 'woocommerce_is_order_received_page', '__return_true' );
			add_filter( 'the_content', 'PixelYourSite\SuperPack\renderWooCustomThankYouPage', 10, 1 );
		}
		
	}
	
	if ( PixelYourSite\isEddActive() ) {
		
		add_filter( 'template_redirect', 'PixelYourSite\SuperPack\maybeRedirectToEDDCustomThankYouPage' );
		
		if ( isset( $_GET['pys_ctp'] ) && isset( $_GET['payment_id'] ) ) {
			add_filter( 'edd_is_success_page', '__return_true' );
			add_filter( 'the_content', 'PixelYourSite\SuperPack\renderEDDCustomThankYouPage', 10, 1 );
		}
		
	}
	
}

function addWooProductMetaBox( $screens ) {
	$screens[] = 'product';
	return $screens;
}

function addEDDProductMetaBox( $screens ) {
	$screens[] = 'download';
	return $screens;
}

function renderCustomThankYouPageMetaBox() {
	include 'views/html-cpt-meta-box.php';
}

function saveCustomThankYouPageMetaBox( $post_id, $data ) {

	if ( isset( $data['pys_super_pack_cpt_enabled'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_cpt_enabled', $data['pys_super_pack_cpt_enabled'] );
	} else {
		delete_post_meta( $post_id, '_pys_super_pack_cpt_enabled' );
	}

	if ( isset( $data['pys_super_pack_cpt_url'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_cpt_url', $data['pys_super_pack_cpt_url'] );
	}

	if ( isset( $data['pys_super_pack_cpt_condition'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_cpt_condition', $data['pys_super_pack_cpt_condition'] );
	}

	if ( isset( $data['pys_super_pack_cpt_cart'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_cpt_cart', $data['pys_super_pack_cpt_cart'] );
	}

}

function maybeRedirectToWooCustomThankYouPage() {

	// perform redirect only from default thank you page
	if ( is_page( wc_get_page_id( 'checkout' ) ) && $redirect_to = getWooCustomThankYouPageURI() ) {
		wp_redirect( $redirect_to );
		exit;
	}

}

function maybeRedirectToEDDCustomThankYouPage() {

	// perform redirect only from default success page
	if ( edd_is_success_page() && ! isset( $_GET['pys_ctp'] ) && $redirect_to = getEDDCustomThankYouPageURI() ) {
		wp_redirect( $redirect_to );
		exit;
	}

}

function getWooCustomThankYouPageURI() {
	global $wp;

	$order_id  = isset( $wp->query_vars['order-received'] ) ? absint( $wp->query_vars['order-received'] ) : null;
	$order_key = isset( $_GET['key'] ) ? wc_clean( $_GET['key'] ) : null;

	// it is Checkout page, not Order Received
	if ( ! $order_id || ! $order_key ) {
		return false;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return false;
	}

	if ( PixelYourSite\isWooCommerceVersionGte( '3.0.0' ) ) {

		if ( $order->get_order_key() !== $order_key ) {
			return false;
		}

	} else {

		if ( $order->order_key !== $order_key ) {
			return false;
		}

	}

	$order_items = $order->get_items();

	$redirect_to = '';
	$show_cart   = 'hidden';

	// get per item custom thank you page
	foreach ( $order_items as $order_item ) {

		if ( PixelYourSite\isWooCommerceVersionGte( '3.0.0' ) ) {
			/** @var \WC_Order_Item_Product $order_item */
			$product_id = $order_item->get_product_id( 'edit' );
		} else {
			$product_id = absint( $order_item['product_id'] );
		}

		$enabled = (bool) get_post_meta( $product_id, '_pys_super_pack_cpt_enabled', true );

		// custom page disabled for product
		if ( ! $enabled ) {
			continue;
		}

		$page_url = get_post_meta( $product_id, '_pys_super_pack_cpt_url', true );

		// custom page not specified
		if ( ! $page_url ) {
			continue;
		}

		$condition = get_post_meta( $product_id, '_pys_super_pack_cpt_condition', true );

		// condition not match
		if ( $condition == 'only' && count( $order_items ) > 1 ) {
			continue; // only this product allowed in cart
		}

		$redirect_to = $page_url;
		$show_cart   = get_post_meta( $product_id, '_pys_super_pack_cpt_cart', true );

		break;

	}

	// may be redirect to global custom page
	if ( empty( $redirect_to )
	     && PixelYourSite\SuperPack()->getOption( 'woo_custom_thank_you_page_global_enabled' )
	     && PixelYourSite\SuperPack()->getOption( 'woo_custom_thank_you_page_global_url' )
	) {
		$redirect_to = PixelYourSite\SuperPack()->getOption( 'woo_custom_thank_you_page_global_url' );
		$show_cart   = PixelYourSite\SuperPack()->getOption( 'woo_custom_thank_you_page_global_cart', 'hidden' );
	}

	if ( ! empty( $redirect_to ) ) {

		// Empty awaiting payment session
		unset( WC()->session->order_awaiting_payment );

		// Empty current cart
		wc_empty_cart();

		return add_query_arg( array(
			'order_id'     => $order_id,
			'key'          => $order_key,
			'pys_ctp'      => true,
			'pys_ctp_cart' => $show_cart == 'hidden' ? null : $show_cart,
		), $redirect_to );

	} else {
		return false;
	}

}

function getEDDCustomThankYouPageURI() {

	$session = edd_get_purchase_session();
	if ( isset( $_GET['payment_key'] ) ) {
		$payment_key = urldecode( $_GET['payment_key'] );
	} else if ( $session ) {
		$payment_key = $session['purchase_key'];
	} else {
		$payment_key = null;
	}

	// no payment key found
	if ( ! isset( $payment_key ) ) {
		return false;
	}

	$payment_id    = edd_get_purchase_id_by_key( $payment_key );
	$user_can_view = edd_can_view_receipt( $payment_key );

	if ( ! $payment_id || ! $user_can_view ) {
		return false;
	}

	$order_items = edd_get_payment_meta_cart_details( $payment_id );

	$redirect_to = '';
	$show_cart   = 'hidden';

	// get per item custom thank you page
	foreach ( $order_items as $order_item ) {

		$download_id = $order_item['id'];
		$cpt_enabled = get_post_meta( $download_id, '_pys_super_pack_cpt_enabled', true );

		// custom page is not enabled for download
		if ( ! $cpt_enabled ) {
			continue;
		}

		$page_url = get_post_meta( $download_id, '_pys_super_pack_cpt_url', true );

		// custom page is not specified
		if ( ! $page_url ) {
			continue;
		}

		$condition = get_post_meta( $download_id, '_pys_super_pack_cpt_condition', true );

		// condition not match
		if ( $condition == 'only' && count( $order_items ) > 1 ) {
			continue;
		}

		$redirect_to = $page_url;
		$show_cart   = get_post_meta( $download_id, '_pys_super_pack_cpt_cart', true );

		break;

	}

	// may be redirect to global custom page
	if ( empty( $redirect_to )
	     && PixelYourSite\SuperPack()->getOption( 'edd_custom_thank_you_page_global_enabled' )
	     && PixelYourSite\SuperPack()->getOption( 'edd_custom_thank_you_page_global_url' )
	) {
		$redirect_to = PixelYourSite\SuperPack()->getOption( 'edd_custom_thank_you_page_global_url' );
		$show_cart   = PixelYourSite\SuperPack()->getOption( 'edd_custom_thank_you_page_global_cart', 'hidden' );
	}

	if ( ! empty( $redirect_to ) ) {

		return add_query_arg( array(
			'payment_id'   => $payment_id,
			'payment_key'  => $payment_key,
			'pys_ctp'      => true,
			'pys_ctp_cart' => $show_cart == 'hidden' ? null : $show_cart,
		), $redirect_to );

	} else {
		return false;
	}

}

function renderWooCustomThankYouPage( $content ) {

	$order_id  = isset( $_GET['order_id'] ) ? wc_clean( $_GET['order_id'] ) : null;
	$order_key = isset( $_GET['key'] ) ? wc_clean( $_GET['key'] ) : null;
	
	// no order key and id found
	if ( ! isset( $order_id ) || ! isset( $order_key ) ) {
		return getAccessDeniedMessageHTML();
	}

	$order = wc_get_order( $order_id );

	// order not found
	if ( ! $order ) {
		return getAccessDeniedMessageHTML();
	}

	// order keys do not match
	if ( $order_key !== $order->get_order_key() ) {
		return getAccessDeniedMessageHTML();
	}

	$show_cart = isset( $_GET['pys_ctp_cart'] ) ? wc_clean( $_GET['pys_ctp_cart'] ) : false;

	if ( $show_cart ) {

		ob_start();

		wc_get_template( 'checkout/thankyou.php', array(
			'order' => $order
		) );

		$order_details = '<div class="woocommerce">' . ob_get_contents() . '</div>';
		ob_end_clean();

		if ( $show_cart == 'after' ) {
			$content = $content . $order_details;
		} else {
			$content = $order_details . $content;
		}

	}

	return $content;

}

function renderEDDCustomThankYouPage( $content ) {

	$payment_id  = isset( $_GET['payment_id'] ) ? urldecode( $_GET['payment_id'] ) : null;
	$payment_key = isset( $_GET['payment_key'] ) ? urldecode( $_GET['payment_key'] ) : null;

	// no payment key and id found
	if ( ! isset( $payment_key ) || ! isset( $payment_id ) ) {
		return getAccessDeniedMessageHTML();
	}

	$user_can_view = edd_can_view_receipt( $payment_key );

	// user not allowed to see this page
	if ( ! $user_can_view ) {
		return getAccessDeniedMessageHTML();
	}

	$payment_id_from_key = edd_get_purchase_id_by_key( $payment_key );

	// payment ids do not match
	if ( $payment_id !== $payment_id_from_key ) {
		return getAccessDeniedMessageHTML();
	}

	$show_cart = isset( $_GET['pys_ctp_cart'] ) ? urldecode( $_GET['pys_ctp_cart'] ) : false;

	if ( $show_cart ) {

		ob_start();

		echo do_shortcode( '[edd_receipt]' );

		$order_details = ob_get_clean();

		if ( $show_cart == 'after' ) {
			$content = $content . $order_details;
		} else {
			$content = $order_details . $content;
		}

	}

	return $content;

}

function getAccessDeniedMessageHTML() {
	return '<p class="pys-alert pys-alert-warn">You do not allowed to see this page.</p>';
}