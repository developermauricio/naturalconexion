<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function getEddPaymentKey() {
	global $edd_receipt_args;

	$session = edd_get_purchase_session();

	if ( isset( $_GET['payment_key'] ) ) {
		return urldecode( $_GET['payment_key'] );
	} else if ( $session ) {
		return $session['purchase_key'];
	} elseif ( $edd_receipt_args && isset($edd_receipt_args['payment_key']) && $edd_receipt_args['payment_key'] ) {
		return $edd_receipt_args['payment_key'];
	} else {
		return false;
	}

}

function getEddCustomerTotals($user_id = 0) {
	global $wpdb;

	$totals = array(
		'orders_count' => 0,
		'avg_order_value' => 0,
		'ltv' => 0,
	);
    if($user_id == 0)
	    $user_id = get_current_user_id();

	if ( $user_id ) {

		// get customer orders
		$order_ids = $wpdb->get_col( $wpdb->prepare( "
            SELECT post_id 
            FROM $wpdb->postmeta 
            WHERE   meta_key = '_edd_payment_user_id' 
              AND   meta_value = '%d'
            ", $user_id ) );

	} else {

		$payment_key = getEddPaymentKey();

		if ( ! isset( $payment_key ) ) {
			return $totals;
		}

		// get last order for guests
		$order_ids[] = (int) edd_get_purchase_id_by_key( $payment_key );

	}

	if( empty( $order_ids ) ) {
		return $totals;
	}

	$order_statues = PYS()->getOption( 'edd_ltv_order_statuses' );
	$order_statues = array_filter( $order_statues );

	if ( empty( $order_statues ) ) {
		$order_statues = array_keys( edd_get_payment_statuses() );
	}

	$post_ids_placeholder = implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) );
	$post_statuses_placeholder = implode( ', ', array_fill( 0, count( $order_statues ), '%s' ) );

	// calculate totals
	$query = $wpdb->prepare( "
        SELECT  SUM(meta_value) AS ltv, AVG(meta_value) as avg_order_value, COUNT(meta_value) AS orders_count 
        FROM    $wpdb->postmeta AS pm
        JOIN    $wpdb->posts AS p ON pm.post_id = p.ID
        WHERE   p.ID IN ({$post_ids_placeholder})
                AND p.post_status IN ({$post_statuses_placeholder})
                AND pm.meta_key = '_edd_payment_total'
        GROUP BY meta_key
    ", array_merge( $order_ids, $order_statues ) );

	$results = $wpdb->get_results( $query );

	if ( null === $results || empty( $results ) ) {
		return $totals;
	} else {
		return array(
			'orders_count'    => (int) $results[0]->orders_count,
			'avg_order_value' => round( (float) $results[0]->avg_order_value, 2),
			'ltv'             => round( (float) $results[0]->ltv, 2),
		);
	}

}

function getEddCustomerTotalsByEmail($email) {
    global $wpdb;

    $totals = array(
        'orders_count' => 0,
        'avg_order_value' => 0,
        'ltv' => 0,
    );


        // get customer orders
        $order_ids = $wpdb->get_col( $wpdb->prepare( "
            SELECT post_id 
            FROM $wpdb->postmeta 
            WHERE   meta_key = '_edd_payment_user_email' 
              AND   meta_value = '%s'
            ", $email ) );


    if( empty( $order_ids ) ) {
        return $totals;
    }

    $order_statues = PYS()->getOption( 'edd_ltv_order_statuses' );
    $order_statues = array_filter( $order_statues );

    if ( empty( $order_statues ) ) {
        $order_statues = array_keys( edd_get_payment_statuses() );
    }

    $post_ids_placeholder = implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) );
    $post_statuses_placeholder = implode( ', ', array_fill( 0, count( $order_statues ), '%s' ) );

    // calculate totals
    $query = $wpdb->prepare( "
        SELECT  SUM(meta_value) AS ltv, AVG(meta_value) as avg_order_value, COUNT(meta_value) AS orders_count 
        FROM    $wpdb->postmeta AS pm
        JOIN    $wpdb->posts AS p ON pm.post_id = p.ID
        WHERE   p.ID IN ({$post_ids_placeholder})
                AND p.post_status IN ({$post_statuses_placeholder})
                AND pm.meta_key = '_edd_payment_total'
        GROUP BY meta_key
    ", array_merge( $order_ids, $order_statues ) );

    $results = $wpdb->get_results( $query );

    if ( null === $results || empty( $results ) ) {
        return $totals;
    } else {
        return array(
            'orders_count'    => (int) $results[0]->orders_count,
            'avg_order_value' => round( (float) $results[0]->avg_order_value, 2),
            'ltv'             => round( (float) $results[0]->ltv, 2),
        );
    }

}

function getEddDownloadPrice( $download_id, $price_index = null ) {

	$price = edd_get_download_price( $download_id );

	if ( edd_has_variable_prices( $download_id ) ) {

		$prices = edd_get_variable_prices( $download_id );

		if ( $price_index !== null ) {

			// get selected price option
			$price = isset( $prices[ $price_index ] ) ? $prices[ $price_index ]['amount'] : 0;

		} else {

			// get default price option
			$default_option = edd_get_default_variable_price( $download_id );
			$price          = $prices[ $default_option ]['amount'];

		}

	}

	$price = (float) $price;
	$tax   = edd_get_cart_item_tax( $download_id, array(), $price );

	$include_tax = PYS()->getOption( 'edd_tax_option' ) == 'included' ? true : false;

	if ( $include_tax == false && edd_prices_include_tax() ) {
		$price -= $tax;
	} elseif ( $include_tax == true && edd_prices_include_tax() == false ) {
		$price += $tax;
	}

	return (float) $price;

}

function getEddDownloadPriceToDisplay( $download_id, $price_index = null  ) {

	if ( edd_has_variable_prices( $download_id ) ) {

		$prices = edd_get_variable_prices( $download_id );

		if ( $price_index !== null ) {

			// get selected price option
			$price = isset( $prices[ $price_index ] ) ? $prices[ $price_index ]['amount'] : 0;

		} else {

			// get default price option
			$default_option = edd_get_default_variable_price( $download_id );
			$price = $prices[ $default_option ]['amount'];

		}

	} else {

		$price = edd_get_download_price( $download_id );

	}

	return (float) $price;

}

function getEddEventValue( $option, $amount, $global, $percent ) {

	switch ( $option ) {
		case 'global':
			$value = (float) $global;
			break;

		case 'percent':
			$percents = (float) $percent;
			$percents = str_replace( '%', null, $percents );
			$percents = (float) $percents / 100;
			$value    = (float) $amount * $percents;
			break;

		default:    // "price" option
			$value = (float) $amount;
	}

	return $value;

}

function getEddDownloadLicenseData( $download_id ) {

	// license management disabled for product
	if ( false == get_post_meta( $download_id, '_edd_sl_enabled', true ) ) {
		return array();
	}

	$params = array();

	$limit      = get_post_meta( $download_id, '_edd_sl_limit', true );
	$exp_unit   = get_post_meta( $download_id, '_edd_sl_exp_unit', true );
	$exp_length = get_post_meta( $download_id, '_edd_sl_exp_length', true );
	$version    = get_post_meta( $download_id, '_edd_sl_version', true );

	$is_limited = get_post_meta( $download_id, 'edd_sl_download_lifetime', true );
	$is_limited = empty( $is_limited );

	$params['transaction_type']   = getEddDownloadPrice( $download_id ) <= 0 ? 'free' : 'paid';
	$params['license_site_limit'] = $limit;
	$params['license_time_limit'] = $is_limited ? "{$exp_length} {$exp_unit}" : 'lifetime';
	$params['license_version']    = $version;

	return $params;

}

function getEddOrderTotal( $payment_id ) {

	$include_tax = PYS()->getOption( 'edd_tax_option' ) == 'included' ? true : false;

	if ( edd_use_taxes() && $include_tax ) {
		return edd_get_payment_amount( $payment_id );
	} elseif ( edd_use_taxes() && ! $include_tax ) {
		return edd_get_payment_amount( $payment_id ) - edd_get_payment_tax( $payment_id );
	} else {
		return edd_get_payment_amount( $payment_id );
	}

}

function eddExportCustomAudiences() {
	global $wpdb;
	
	ob_clean();
	
	$csv_data = array();
	
	$order_statues = PYS()->getOption( 'edd_ltv_order_statuses', array() );
	
	if ( empty( $order_statues ) ) {
		$order_statues = array_keys( edd_get_payment_statuses() );
	}
	
	$order_statues_placeholders = implode( ', ', array_fill( 0, count( $order_statues ), '%s' ) );
	
	// collect all unique customers by email
	$query = $wpdb->prepare( "
        SELECT  postmeta.meta_value AS email, postmeta.post_id
        FROM    $wpdb->postmeta AS postmeta
        JOIN    $wpdb->posts AS posts ON postmeta.post_id = posts.ID
        WHERE   posts.post_type = 'edd_payment'
                AND posts.post_status IN ({$order_statues_placeholders})
                AND postmeta.meta_key = '_edd_payment_user_email'
    ", $order_statues );
	
	$results = $wpdb->get_results( $query );
	
	$customers = array();
	
	// format data as email => [ order_ids ]
	foreach ( $results as $row ) {
		
		$order_ids   = isset( $customers[ $row->email ] ) ? $customers[ $row->email ] : array();
		$order_ids[] = (int) $row->post_id;
		
		$customers[ $row->email ] = $order_ids;
		
	}
	
	@ini_set( 'max_execution_time', 180 );
	
	// collect data per each customer
	foreach ( $customers as $email => $order_ids ) {
		
		$order_ids_placeholders = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
		
		// calculate customer LTV
		$query = $wpdb->prepare( "
            SELECT  SUM( meta_value )
            FROM    $wpdb->postmeta
            WHERE   post_id IN ( {$order_ids_placeholders} )
                    AND meta_key = '_edd_payment_total'
        ", $order_ids );
		
		$customer_ltv = $wpdb->get_col( $query );
		
		// query customer data from last order
		$query = $wpdb->prepare( "
            SELECT  meta_value
            FROM    $wpdb->postmeta
            WHERE   post_id = %d
                    AND meta_key = '_edd_payment_meta'
        ", end( $order_ids ) );
		
		$customer_meta        = $wpdb->get_col( $query );
		$customer_meta        = maybe_unserialize( $customer_meta[0] );
		$customer_meta['ltv'] = (float) $customer_ltv[0];
		
		$csv_data[] = $customer_meta;
		
	}
	
	// generate file name
	$site_name = site_url();
	$site_name = str_replace( array( 'http://', 'https://' ), '', $site_name );
	$site_name = strtolower( preg_replace( "/[^A-Za-z]/", '_', $site_name ) );
	$file_name = strftime( '%Y%m%d' ) . '_' . $site_name . '_edd_customers.csv';
	
	// output CSV
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $file_name );
	
	$output = fopen( 'php://output', 'w' );
	
	// headings
	fputcsv( $output, array( 'email', 'phone', 'fn', 'ln', 'ct', 'st', 'country', 'zip', 'value' ) );
	
	// rows
	foreach ( $csv_data as $row ) {
		
		fputcsv( $output, array(
			$row['email'],
			'',
			isset( $row['user_info']['first_name'] ) ? $row['user_info']['first_name'] : '',
			isset( $row['user_info']['last_name'] ) ? $row['user_info']['last_name'] : '',
			isset( $row['user_info']['address']['city'] ) ? $row['user_info']['address']['city'] : '',
			isset( $row['user_info']['address']['state'] ) ? $row['user_info']['address']['state'] : '',
			isset( $row['user_info']['address']['country'] ) ? $row['user_info']['address']['country'] : '',
			isset( $row['user_info']['address']['zip'] ) ? $row['user_info']['address']['zip'] : '',
			$row['ltv']
		) );
		
	}
	
	exit;
	
}