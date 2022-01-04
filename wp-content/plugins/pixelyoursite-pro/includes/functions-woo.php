<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function isWooCommerceVersionGte( $version ) {

	if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
		return version_compare( WC_VERSION, $version, '>=' );
	} else if ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
		return version_compare( WOOCOMMERCE_VERSION, $version, '>=' );
	} else {
		return false;
	}

}

/**
 * @param \WC_Product|\WP_Post $product
 *
 * @return bool
 */
function wooProductIsType( $product, $type ) {

	if ( isWooCommerceVersionGte( '2.7' ) ) {
		return $type == $product->is_type( $type );
	} else {
		return $product->product_type == $type;
	}

}

function getWooPayPalEventName() {

    $woo_paypal_custom_event_type = PYS()->getOption( 'woo_paypal_custom_event_type' );

    if ( PYS()->getOption( 'woo_paypal_event_type' ) == 'custom' && ! empty( $woo_paypal_custom_event_type ) ) {
        return sanitizeKey( PYS()->getOption( 'woo_paypal_custom_event_type' ) );
    } else {
        return PYS()->getOption( 'woo_paypal_event_type' );
    }
}

function getWooProductPrice( $product_id, $qty = 1,$customPrice = -1 ) {

	$product = wc_get_product( $product_id );

	if( false == $product ) {
		return 0;
	}

    if($product->get_type() == "variable") {
        $prices = $product->get_variation_prices( true );
        if(empty( $prices['price'] )) {
            $productPrice = $product->get_price();
        } else {
            $productPrice = current( $prices['price'] );
        }

    } else {
        $productPrice = $product->get_price();
    }

	if($customPrice > -1 && $customPrice != "") {
        $productPrice = $customPrice;
    }

    // for Woo Discount Rules
    if(method_exists('\Wdr\App\Controllers\ManageDiscount','calculateInitialAndDiscountedPrice')) {
        $salePrice = \Wdr\App\Controllers\ManageDiscount::calculateInitialAndDiscountedPrice($product,$qty);
        if(is_array($salePrice) && isset($salePrice['discounted_price'])) {
            $productPrice = $salePrice['discounted_price'];
        }
    }

	$include_tax = PYS()->getOption( 'woo_tax_option' ) == 'included' ? true : false;

	if ( $product->is_taxable() && $include_tax ) {
        $value = wc_get_price_including_tax( $product, array(
            'price' => $productPrice,
            'qty' => $qty
        ) );
	} else {
        $value = wc_get_price_excluding_tax( $product, array(
            'price' => $productPrice,
            'qty' => $qty
        ) );
	}

	return $value;

}

function getWooProductPriceToDisplay( $product_id, $qty = 1,$customPrice = -1 ) {

	if ( ! $product = wc_get_product( $product_id ) ) {
		return 0;
	}
    if($product->get_type() == "variable") {
        $prices = $product->get_variation_prices( true );
        if(empty( $prices['price'] )) {
            $productPrice = $product->get_price();
        } else {
            $productPrice = current( $prices['price'] );
        }

    } else {
        $productPrice = $product->get_price();
    }
    if($customPrice > -1 && $customPrice != "") {
        $productPrice = $customPrice;
    }

    // for Woo Discount Rules
    if(method_exists('\Wdr\App\Controllers\ManageDiscount','calculateInitialAndDiscountedPrice')) {
        $salePrice = \Wdr\App\Controllers\ManageDiscount::calculateInitialAndDiscountedPrice($product,$qty);
        if(is_array($salePrice) && isset($salePrice['discounted_price'])) {
            $productPrice = $salePrice['discounted_price'];
        }
    }

    if($product->get_type() == "bundle") {
        $price = (float) getDefaultBundlePrice($product);//->get_bundle_price("min",true);
        return $price;
    }

    return (float) wc_get_price_to_display( $product, array( 'qty' => $qty,'price'=>$productPrice ) );
}

function getWooBundleProductCartPrice( $cart_item ) {

    $price = $cart_item['line_subtotal'];

    foreach ($cart_item['bundled_items'] as $has) {
        $bundled_cart_item = WC()->cart->get_cart_item($has);
        $price += $bundled_cart_item["line_subtotal"];
    }

    return $price;
}


/**
 * @param \WC_Product_Bundle $product
 */
function getDefaultBundlePrice($product) {
    $qty = 1;
    $price_prop = "price";
    $price_calc = 'display';
    $strict = false;
    $min_or_max = "min";
    if($product->contains( 'priced_individually' )) {

        $price_fn = 'get_' . $price_prop;
        $price    = wc_format_decimal( \WC_PB_Product_Prices::get_product_price( $product, array(
            'price' => $product->$price_fn(),
            'qty'   => $qty,
            'calc'  => $price_calc,
        ) ), wc_get_price_decimals() );

        $bundled_items = $product->get_bundled_items();

        if ( ! empty( $bundled_items ) ) {
            foreach ( $bundled_items as $bundled_item ) {

                if ( false === $bundled_item->is_purchasable() ) {
                    continue;
                }

                if ( false === $bundled_item->is_priced_individually() ) {
                    continue;
                }

                if($bundled_item->is_optional()) {
                    continue;
                }

                $bundled_item_qty = $qty * $bundled_item->get_quantity( "default", array( 'context' => 'price', 'check_optional' => $min_or_max === 'min' ) );

                if ( $bundled_item_qty ) {

                    $price += wc_format_decimal( $bundled_item->calculate_price( array(
                        'min_or_max' => $min_or_max,
                        'qty'        => $bundled_item_qty,
                        'strict'     => $strict,
                        'calc'       => $price_calc,
                        'prop'       => $price_prop
                    ) ), wc_get_price_decimals() );
                }
            }
        }

    } else {
        $price_fn = 'get_' . $price_prop;
        $price    = \WC_PB_Product_Prices::get_product_price( $product, array(
            'price' => $product->$price_fn(),
            'qty'   => $qty,
            'calc'  => $price_calc,
        ) );


    }

    return $price;
}

/**
 * @param SingleEvent $event
 */
function getWooEventCartSubtotal($event) {
    $subTotal = 0;
    $include_tax = get_option( 'woocommerce_tax_display_cart' ) == 'incl';

    foreach ($event->args['products'] as $product) {
        $subTotal += $product['subtotal'];
        if($include_tax) {
            $subTotal += $product['subtotal_tax'];
        }
    }
    return pys_round($subTotal);
}

/**
 * @param SingleEvent $event
 */
function getWooEventCartTotal($event) {

    return getWooEventCartSubtotal($event);
}
/**
 * @param SingleEvent $event
 */
function getWooEventOrderTotal( $event ) {

    if(PYS()->getOption( 'woo_event_value' ) != 'custom') {
        $total = 0;
        // $include_tax = get_option( 'woocommerce_tax_display_cart' ) == 'incl';
        foreach ($event->args['products'] as $product) {
            $total += $product['total'] + $product['total_tax'];
        }
        $total+=$event->args['shipping_cost'] + $event->args['shipping_tax'];
        return pys_round($total);
    }

    $include_tax = PYS()->getOption( 'woo_tax_option' ) == 'included' ? true : false;
    $include_shipping = PYS()->getOption( 'woo_shipping_option' ) == 'included' ? true : false;


    $total = 0;
    foreach ($event->args['products'] as $product) {
        $total += $product['total'];
        if($include_tax) {
            $total += $product['total_tax'];
        }
    }

    if($include_shipping) {
        $total += $event->args['shipping_cost'];
    }
    if($include_tax) {
        $total += $product['shipping_cost_tax'];
    }

    return pys_round($total );

}

function getWooCartSubtotal() {

	// subtotal is always same value on front-end and depends on PYS options
	$include_tax = get_option( 'woocommerce_tax_display_cart' ) == 'incl';

	if ( $include_tax ) {

		if ( isWooCommerceVersionGte( '3.2.0' ) ) {
			$subtotal = (float) WC()->cart->get_subtotal() + (float) WC()->cart->get_subtotal_tax();
		} else {
			$subtotal = WC()->cart->subtotal;
		}

	} else {

		if ( isWooCommerceVersionGte( '3.2.0' ) ) {
			$subtotal = (float) WC()->cart->get_subtotal();
		} else {
			$subtotal = WC()->cart->subtotal_ex_tax;
		}

	}

	return $subtotal;
}

function getWooCartTotal() {

	$include_tax = PYS()->getOption( 'woo_tax_option' ) == 'included' ? true : false;

	if ( $include_tax ) {
		$total = WC()->cart->cart_contents_total + WC()->cart->tax_total;
	} else {
		$total = WC()->cart->cart_contents_total;
	}

	return $total;

}

/**
 * @param \WC_Order $order
 *
 * @return string
 */
function getWooOrderTotal( $order ) {

	$include_tax = PYS()->getOption( 'woo_tax_option' ) == 'included' ? true : false;
	$include_shipping = PYS()->getOption( 'woo_shipping_option' ) == 'included' ? true : false;

	if ( $include_shipping && $include_tax ) {

		$total = $order->get_total();   // full order price

	} elseif ( ! $include_shipping && ! $include_tax ) {

		$cart_subtotal  = $order->get_subtotal();

		if ( isWooCommerceVersionGte( '2.7' ) ) {
			$discount_total = (float) $order->get_discount_total( 'edit' );
		} else {
			$discount_total = $order->get_total_discount();
		}

		$total = $cart_subtotal - $discount_total;

	} elseif ( ! $include_shipping && $include_tax ) {

		if ( isWooCommerceVersionGte( '2.7' ) ) {
			$cart_total     = (float) $order->get_total( 'edit' );
			$shipping_total = (float) $order->get_shipping_total( 'edit' );
			$shipping_tax   = (float) $order->get_shipping_tax( 'edit' );
		} else {
			$cart_total     = $order->get_total();
			$shipping_total = $order->get_total_shipping();
			$shipping_tax   = $order->get_shipping_tax();
		}

		$total = $cart_total - $shipping_total - $shipping_tax;

	} else {
		// $include_shipping && !$include_tax

		$cart_subtotal  = $order->get_subtotal();

		if ( isWooCommerceVersionGte( '2.7' ) ) {
			$discount_total = (float) $order->get_discount_total( 'edit' );
			$shipping_total = (float) $order->get_shipping_total( 'edit' );
		} else {
			$discount_total = $order->get_total_discount();
			$shipping_total = $order->get_total_shipping();
		}

		$total = $cart_subtotal - $discount_total + $shipping_total;

	}

	//wc_get_price_thousand_separator is ignored
	return number_format( $total, wc_get_price_decimals(), '.', '' );

}

/**
 * @deprecated use getWooProductValue
 * @param String $valueOption
 * @param float $global
 * @param float $percent
 * @param int $product_id
 * @param int $qty
 * @return false|float|int
 */

function getWooEventValue( $valueOption, $global, $percent, $product_id,$qty ) {
	return getWooProductValue([
	    'valueOption' => $valueOption,
        'global' => $global,
        'percent' => $percent,
        'product_id' => $product_id,
        'qty' => $qty
    ]);
}

function getWooProductValue($args) {
    $valueOption = $args['valueOption'];
    $global = $args['global'];
    $percent = $args['percent'];
    $product_id = $args['product_id'];
    $qty = $args['qty'];


    $product = wc_get_product($product_id);
    if(!$product) return 0;

    $productPrice = "";

    if(!empty($args['price'])) {
        $productPrice = $args['price'];
    }
    // for cartflow product sale
    $salePrice = getWfcProductSalePrice($product,$args);
    if($salePrice > -1 ) {
        $productPrice = $salePrice;
    }



    if($valueOption == 'cog' && isPixelCogActive()) {
        return pys_woo_get_cog_product_value($product,$qty,$productPrice);
    }

    if ( PYS()->getOption( 'woo_event_value' ) == 'custom' ) {
        $amount = getWooProductPrice( $product_id, $qty,$productPrice );
    } else {
        $amount = getWooProductPriceToDisplay( $product_id, $qty,$productPrice );
    }

    switch ( $valueOption ) {
        case 'global': $value = $global; break;
        case 'percent':
            $percents = (float) $percent;
            $percents = str_replace( '%', null, $percents );
            $percents = (float) $percents / 100;
            $value    = (float) $amount * $percents;
            break;
        default:$value = (float)$amount;
    }
    return $value;
}

function getWcfEventValueOrder( $valueOption, $wcf_offer_step_id, $global, $percent ,$quantity) {

    $wcf_product = getWcfOfferProduct($wcf_offer_step_id);
    $product = wc_get_product($wcf_product['product']);
    $price = getWfcProductSalePrice($product,$wcf_product);
    $offer_shipping_fee = wcf_get_offer_shipping($wcf_offer_step_id);

    if($price < 0) {
        $price = $product->get_price();
    }


    if ( PYS()->getOption( 'woo_event_value' ) == 'custom' ) {
        $include_tax = PYS()->getOption( 'woo_tax_option' ) == 'included' ? true : false;
        $include_shipping = PYS()->getOption( 'woo_shipping_option' ) == 'included' ? true : false;

        if($include_shipping) {
            $amount = $price * $quantity + $offer_shipping_fee;
        } else {
            $amount = $price * $quantity;
        }
    } else {
        $amount = $price * $quantity;
    }

    switch ( $valueOption ) {
        case 'global':
            $value = (float) $global;
            break;

        case 'cog':
            $cog_value = pys_woo_get_cog_product_value($product,$quantity,$price);
            ($cog_value !== '') ? $value = (float) round($cog_value, 2) : $value = (float) $amount;
            if ( !isPixelCogActive() ) $value = (float) $amount;
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

/**
 * @param string $valueOption  // 'global' or 'cog' or 'percent' other is default
 * @param float $global // global value
 * @param string $percent // percent from value from 0 to 100%
 * @param $args // other args
 */
function getWooEventValueProducts( $valueOption, $global, $percent, $total, $args ) {

    if($valueOption == 'global') return $global;

    if($valueOption == 'percent') {
        $percents = (float) $percent;
        $percents = str_replace( '%', null, $percents );
        $percents = (float) $percents / 100;
        return (float) $total * $percents;
    }

    if($valueOption == 'cog' && isPixelCogActive()) {
        $cog_value = getAvailableProductCogOrder($args);
        if($cog_value !== '') {
            return (float) round($cog_value, 2);
        }
    }

    return (float) $total;
}
/**
 * @deprecated
 * @param  $valueOption
 * @param \WC_Order $order
 * @param $global
 * @param $order_id
 * @param $content_ids
 * @param int $percent
 * @return float|int
 */
function getWooEventValueOrder( $valueOption, $order, $global, $percent = 100 ) {

    if ( PYS()->getOption( 'woo_event_value' ) == 'custom' ) {
        $amount = getWooOrderTotal( $order );
    } else {
        $amount = $order->get_total();
    }
	switch ( $valueOption ) {
		case 'global':
			$value = (float) $global;
			break;

		case 'cog':
			$cog_value = getAvailableProductCogOrder($order->get_id());
			($cog_value !== '') ? $value = (float) round($cog_value, 2) : $value = (float) $amount;
			if ( !isPixelCogActive() ) $value = (float) $amount;
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


/**
 * @deprecated
 * @param $valueOption
 * @param $global
 * @param int $percent
 * @return bool|float|int|mixed|string|\WC_Tax
 */
function getWooEventValueCart( $valueOption, $global, $percent = 100 ) {


    if($valueOption == 'cog' && isPixelCogActive()) {
        $cog_value = getAvailableProductCogCart();
        if($cog_value !== '')
            return (float) round($cog_value, 2) ;

        if ( get_option( '_pixel_cog_tax_calculating')  == 'no' ) {
            return WC()->cart->cart_contents_total;
        }

        return WC()->cart->cart_contents_total + WC()->cart->tax_total;
    }


    if ( PYS()->getOption( 'woo_event_value' ) == 'custom' ) {
        $amount = getWooCartTotal();
    } else {
        $amount = $params['value'] = WC()->cart->subtotal;
    }

	switch ( $valueOption ) {
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

function getWooUserStat($orderId = 0) {
    global $wpdb;
    $orderStatTable = $wpdb->base_prefix."wc_order_stats" ;
    $totals = array(
        'orders_count' => 0,
        'avg_order_value' => 0,
        'ltv' => 0,
    );
    // get order customer id
    $customerId = $wpdb->get_var( $wpdb->prepare( "
            SELECT customer_id 
            FROM $orderStatTable
            WHERE   order_id = '%d'  ", $orderId ) );

    if ( null === $customerId ) {
        return $totals;
    }
    // calculate totals for customer
    $query = $wpdb->prepare( "
        SELECT  SUM(net_total) AS ltv, AVG(net_total) as avg_order_value, COUNT(net_total) AS orders_count 
        FROM    $orderStatTable
        WHERE   customer_id = %d
        GROUP BY customer_id
    ", intval($customerId) );

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

function getWooCustomerTotals($user_id = 0,$order_id = null) {
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
            WHERE   meta_key = '_customer_user' 
              AND   meta_value = '%d'
            ", $user_id ) );

	} else {
		// get last order for guests
		$order_ids = array( $order_id == null ? wooGetOrderIdFromRequest() : $order_id );
	}

	if( empty( $order_ids ) ) {
		return $totals;
	}

	$order_statues = PYS()->getOption( 'woo_ltv_order_statuses' );
	$order_statues = array_filter( $order_statues );

	if ( empty( $order_statues ) ) {
		$order_statues = array_keys( wc_get_order_statuses() );
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
                AND pm.meta_key = '_order_total'
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

function getWooCustomerTotalsByEmail($email) {
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
        WHERE   meta_key = '_billing_email' 
          AND   meta_value = '%s'
        ", $email ) );


    if( empty( $order_ids ) ) {
        return $totals;
    }

    $order_statues = PYS()->getOption( 'woo_ltv_order_statuses' );
    $order_statues = array_filter( $order_statues );

    if ( empty( $order_statues ) ) {
        $order_statues = array_keys( wc_get_order_statuses() );
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
                AND pm.meta_key = '_order_total'
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

function wooExportCustomAudiences() {
	global $wpdb;
	
	ob_clean();
	
	$csv_data = array();
	
	$order_statues = PYS()->getOption( 'woo_ltv_order_statuses', array() );
	
	if ( empty( $order_statues ) ) {
		$order_statues = array_keys( wc_get_order_statuses() );
	}
	
	$order_statues_placeholders = implode( ', ', array_fill( 0, count( $order_statues ), '%s' ) );
	
	// collect all unique customers by email
	$query = $wpdb->prepare( "
        SELECT  postmeta.meta_value AS email, postmeta.post_id
        FROM    $wpdb->postmeta AS postmeta
        JOIN    $wpdb->posts AS posts ON postmeta.post_id = posts.ID
        WHERE   posts.post_type = 'shop_order'
                AND posts.post_status IN ({$order_statues_placeholders})
                AND postmeta.meta_key = '_billing_email'
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
                    AND meta_key = '_order_total'
        ", $order_ids );
		
		$customer_ltv = $wpdb->get_col( $query );
		
		// query customer data from last order
		$query = $wpdb->prepare( "
            SELECT  meta_key, meta_value
            FROM    $wpdb->postmeta
            WHERE   post_id = %d
                    AND meta_key IN ( '_billing_first_name', '_billing_last_name', '_billing_city', '_billing_state',
                    '_billing_postcode', '_billing_country', '_billing_phone' )
        ", end( $order_ids ) );
		
		$results = $wpdb->get_results( $query );
		
		$customer_meta          = wp_list_pluck( $results, 'meta_value', 'meta_key' );
		$customer_meta['ltv']   = (float) $customer_ltv[0];
		$customer_meta['email'] = $email;
		
		$csv_data[] = $customer_meta;
		
	}
	
	// generate file name
	$site_name = site_url();
	$site_name = str_replace( array( 'http://', 'https://' ), '', $site_name );
	$site_name = strtolower( preg_replace( "/[^A-Za-z]/", '_', $site_name ) );
	$file_name = strftime( '%Y%m%d' ) . '_' . $site_name . '_woo_customers.csv';
	
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
			isset( $row['_billing_phone'] ) ? $row['_billing_phone'] : '',
			isset( $row['_billing_first_name'] ) ? $row['_billing_first_name'] : '',
			isset( $row['_billing_last_name'] ) ? $row['_billing_last_name'] : '',
			isset( $row['_billing_city'] ) ? $row['_billing_city'] : '',
			isset( $row['_billing_state'] ) ? $row['_billing_state'] : '',
			isset( $row['_billing_country'] ) ? $row['_billing_country'] : '',
			isset( $row['_billing_postcode'] ) ? $row['_billing_postcode'] : '',
			$row['ltv']
		) );
		
	}
	
	exit;
	
}

function wooGetOrderIdFromRequest() {
    if(isset( $_REQUEST['key'] )  && $_REQUEST['key'] != "") {
        $order_key = sanitize_key($_REQUEST['key']);
        $order_id = (int) wc_get_order_id_by_order_key( $order_key );
        return $order_id;
    }
    if(isset( $_REQUEST['referenceCode'] )  && $_REQUEST['referenceCode'] != "") {
        return (int)$_REQUEST['referenceCode'];
    }
    if(isset( $_REQUEST['ref_venta'] )  && $_REQUEST['ref_venta'] != "") {
        return (int)$_REQUEST['ref_venta'];
    }
    if(!empty($_REQUEST['wcf-order'])) {
        return (int)$_REQUEST['wcf-order'];
    }
     return -1;
}
function wooIsRequestContainOrderId() {
    return  isset( $_REQUEST['key'] )  && $_REQUEST['key'] != ""
        || isset( $_REQUEST['referenceCode'] )  && $_REQUEST['referenceCode'] != ""
        || isset( $_REQUEST['ref_venta'] )  && $_REQUEST['ref_venta'] != ""
        || !empty( $_REQUEST['wcf-order'] );
}

/**
 * @param \WC_Product $product
 * @return array
 */
function pys_woo_get_product_data($product,$args) {

    if ( $product->get_type() == 'variation' ) {
        $parent_id = $product->get_parent_id(); // get terms from parent
        $tags = getObjectTerms( 'product_tag', $parent_id );
        $categories = getObjectTermsWithId( 'product_cat', $parent_id );
    } else {
        $tags = getObjectTerms( 'product_tag', $product->get_id() );
        $categories = getObjectTermsWithId( 'product_cat', $product->get_id() );
    }



    $product_price = $product->get_price();
    // for cartflow product sale
    $sale_price = getWfcProductSalePrice($product,$args);
    if($sale_price > -1) {
        $product_price = $sale_price;
    }

    $product_type = $product->get_type();

    $isGrouped = $product_type == "grouped";
    $child = [];
    if($isGrouped) {
        $product_ids = $product->get_children();
        foreach ($product_ids as $id) {
            $child = wc_get_product($id);
            if(!$child) continue;
            if($child->get_type() == "variable") { // skip  variable products in grouped product
                continue;
            }
            $child[] = [
                'id' => $id,
                'quantity' => 1
            ];
        }
    }
    $variation_attr = null;
    if($product_type == 'variation') {
        $variation_attr = $product->get_variation_attributes();
    }

    return [
        'id'            => $product->get_id(),
        'name'          => $product->get_name(),
        'tags'          => $tags,
        'categories'    => $categories,
        'price'         => $product_price,
        'type'          => $product_type,
        'child'         => $child,
        'is_external'   => false,
        'quantity'      => $args['quantity'],
        'variation_attr'=> $variation_attr
    ];
}

function pys_woo_get_cog_product_value($product,$quantity,$price) {
    $args = array( 'qty'   => $quantity, 'price' => $price);
    if(get_option( '_pixel_cog_tax_calculating')  == 'no') {
        $amount = wc_get_price_excluding_tax($product, $args);
    } else {
        $amount = wc_get_price_including_tax($product,$args);
    }

    $cog = getAvailableProductCog($product);

    if ($cog['val']) {
        if ($cog['type'] == 'fix') {
            $value = round((float)$amount - (float)$cog['val'], 2);
        } else {
            $value = round((float)$amount - ((float)$amount * (float)$cog['val'] / 100), 2);
        }
    } else {
        $value = (float)$amount;
    }
    return $value;
}