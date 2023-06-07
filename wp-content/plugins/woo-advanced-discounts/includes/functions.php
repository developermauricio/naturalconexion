<?php

/**
 * Builds a select dropdpown
 *
 * @param string       $name Name
 * @param string       $id ID
 * @param string       $class Class
 * @param array        $options Options
 * @param string|array $selected Selected value
 * @param bool         $multiple Can select multiple values
 * @return string HTML code
 */
function get_wad_html_select( $name, $id, $class, $options, $selected = '', $multiple = false, $required = false ) {
	ob_start();
	if ( $multiple && ! is_array( $selected ) ) {
		$selected = array();
	}
	?>
	<select 
	name="<?php echo esc_attr( $name ); ?>" 
	<?php echo esc_attr( "id='$id'" ); ?> 
	<?php echo esc_attr( "class='$class'" ); ?> 
	<?php echo esc_attr( "multiple='$multiple'" ); ?> 
	<?php echo esc_attr( "required='$required'" ); ?> 
	>
		<?php
		if ( is_array( $options ) && ! empty( $options ) ) {
			foreach ( $options as $value => $label ) {
				if ( ! $multiple && $value == $selected ) {
					?>
					 <option value="<?php echo esc_attr( $value ); ?>"  selected="selected" > <?php echo esc_attr( $label ); ?></option> 
					<?php
				} elseif ( $multiple && in_array( $value, $selected ) ) {
					?>
					 <option value="<?php echo esc_attr( $value ); ?>"  selected="selected" > <?php echo esc_attr( $label ); ?></option> 
					<?php
				} else {
					?>
					 <option value="<?php echo esc_attr( $value ); ?>"> <?php echo esc_attr( $label ); ?></option> 
					<?php
				}
			}
		}
		?>
	</select>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


/**
 * Checks if the current page is the checkout page
 *
 * @return boolean
 */
function wad_is_checkout() {
	$is_checkout = false;
	if ( ! is_admin() && function_exists( 'is_checkout' ) && is_checkout() ) {
		$is_checkout = true;
	}

	return $is_checkout;
}

/**
 * Returns the logged user role
 *
 * @global object $wpdb
 * @return string
 */
function wad_get_user_role() {
	if ( ! is_user_logged_in() ) {
		return 'not-logged-in';
	}
	$uid = get_current_user_id();
	global $wpdb;
	$role = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$wpdb->prefix}capabilities' AND user_id = {$uid}" );
	if ( ! $role ) {
		return 'non-user';
	}
	$rarr  = unserialize( $role );
	$roles = is_array( $rarr ) ? array_keys( $rarr ) : array( 'non-user' );
	return $roles; // $roles[0];
}

/**
 * Returns the number of products in the cart grouped by product or not
 *
 * @global type $woocommerce
 * @param bool $by_products whether or not the return the result by product or the sum of the quantities in the cart
 * @param bool $product_id Product ID to limit the search to
 * @return int|array
 */
function wad_get_cart_products_count( $by_products = false, $product_id = false ) {
	global $woocommerce;
	$count = array();
	if ( ! empty( $woocommerce->cart->cart_contents ) ) {
		foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $cart_item_data ) {
			if ( $product_id && $cart_item_data['variation_id'] != $product_id && $cart_item_data['product_id'] != $product_id ) {
				continue;
			}
			// We add the variations too in case the customer is checking against a variation
			if ( isset( $cart_item_data['variation_id'] ) && ! empty( $cart_item_data['variation_id'] ) ) {
				if ( ! isset( $count[ $cart_item_data['variation_id'] ] ) ) {
					$count[ $cart_item_data['variation_id'] ] = 0;
				}
				$count[ $cart_item_data['variation_id'] ] += $cart_item_data['quantity'];
			} else {
				if ( ! isset( $count[ $cart_item_data['product_id'] ] ) ) {
					$count[ $cart_item_data['product_id'] ] = 0;
				}
				$count[ $cart_item_data['product_id'] ] += $cart_item_data['quantity'];
			}
		}
	}

	if ( $by_products ) {
		return $count;
	} else {
		return array_sum( $count );
	}
}

/**
 * Returns the list of user roles in the current installation
 *
 * @global type $wp_roles
 * @return array
 */
function wad_get_existing_user_roles() {
	global $wp_roles;
	$roles_arr                  = array();
	$all_roles                  = $wp_roles->roles;
	$roles_arr['not-logged-in'] = esc_html__( 'Not logged in', 'woo-advanced-discounts' );
	foreach ( $all_roles as $role_key => $role_data ) {
		$roles_arr[ $role_key ] = $role_data['name'];
	}
	return $roles_arr;
}

/**
 * Returns the list of users in the current installation
 *
 * @return array
 */
function wad_get_existing_users() {
	$users     = get_users( array( 'fields' => array( 'ID', 'display_name', 'user_email' ) ) );
	$users_arr = array();
	foreach ( $users as $user ) {
		$users_arr[ $user->ID ] = "$user->display_name($user->user_email)";
	}

	return $users_arr;
}

/**
 * Returns the statuses to consider as completed when retrieving the orders
 *
 * @global array $wad_settings
 * @return array
 */
function wad_get_completed_orders_statuses() {
	global $wad_settings;
	$statuses = O_Utils::get_proper_value( $wad_settings, 'completed-order-statuses', array() );
	if ( empty( $wad_settings['completed-order-statuses'] ) ) {
		$statuses = array( 'wc-processing', 'wc-completed', 'wc-on-hold' );
	}
	return $statuses;
}

/**
 * Returns the list of products currently in the cart
 *
 * @global type $woocommerce
 * @return array
 */
function wad_get_cart_products() {
	global $woocommerce;
	$products = array();
	if ( empty( $woocommerce->cart->cart_contents ) ) {
		return $products;
	}

	foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $cart_item_data ) {
		array_push( $products, $cart_item_data['product_id'] );
		// We add the variations too in case the customer is checking against a variation
		if ( isset( $cart_item_data['variation_id'] ) && ! empty( $cart_item_data['variation_id'] ) ) {
			array_push( $products, $cart_item_data['variation_id'] );
		}
	}
	return $products;
}

function wad_get_all_discounts() {
	global $wpdb;
	$sql     = "select ID, post_title from $wpdb->posts where post_type='o-discount' and post_status='publish'";
	$results = $wpdb->get_results( $sql );
	return $results;
}

/**
 * Returns the list of active discounts
 *
 * @global object $wpdb
 * @param bool $group_by_types to group the list by discount types (order | product) or not
 * @return array
 */
function wad_get_active_discounts( $group_by_types = false ) {
	global $wpdb;
	if ( $group_by_types ) {
		$valid_discounts = array(
			'product'  => array(),
			'order'    => array(),
			'shipping' => array(),
		);
	} else {
		$valid_discounts = array();
	}

	$date_format           = 'Y-m-d H:i';
	$raw_today             = current_time( $date_format, false );
	$today                 = date( $date_format, strtotime( $raw_today ) );
	$product_based_actions = wad_get_product_based_actions();

	$all_discounts = wad_get_all_discounts();
	foreach ( $all_discounts as $discount ) {
		$metas = get_post_meta( $discount->ID, 'o-discount', true );
		if ( ! isset( $metas['start-date'] ) ) {
			$metas['start-date'] = '';
		}
		if ( ! isset( $metas['end-date'] ) ) {
			$metas['end-date'] = '';
		}
		if ( ! isset( $metas['period'] ) ) {
			$metas['period'] = '';
		}
		if ( ! isset( $metas['period-type'] ) ) {
			$metas['period-type'] = 'd';
		}

		// We make sure empty dates are marked as active
		if ( empty( $metas['start-date'] ) ) {
			$start_date = $today;
		} else {
			$start_date = date( $date_format, strtotime( $metas['start-date'] ) );
		}

		if ( empty( $metas['end-date'] ) ) {
			$end_date = $today;
		} else {
			$end_date = date( $date_format, strtotime( $metas['end-date'] ) );
		}

		// We check the limit if needed
		$limit = O_Utils::get_proper_value( $metas, 'users-limit' );
		if ( $limit ) {
			// How many times has this discount been used?
			$sql     = "SELECT count(*) FROM $wpdb->postmeta where meta_key='wad_used_discount' and meta_value=$discount->ID";
			$nb_used = $wpdb->get_var( $sql );
			if ( $nb_used >= $limit ) {
				continue;
			}
		}
		if (
					( ( $today >= $start_date ) && ( $today <= $end_date ) ) ||
					wad_is_discount_in_valid_period( $metas['start-date'], $metas['end-date'], $metas['period'], $metas['period-type'] )
			) {
			if ( $group_by_types ) {
				if ( in_array( $metas['action'], $product_based_actions ) ) {
					array_push( $valid_discounts['product'], $discount->ID );
				} else {
					array_push( $valid_discounts['order'], $discount->ID );
				}
			} else {
				array_push( $valid_discounts, $discount->ID );
			}
		}
	}
	return $valid_discounts;
}

/**
 * Checks if a discount is in the validity period
 *
 * @param string $start Start date
 * @param string $end End date
 * @param int    $period
 * @param string $period_type
 * @return boolean
 */
function wad_is_discount_in_valid_period( $start, $end, $period, $period_type ) {
	if ( empty( $period ) ) {
		return false;
	}

	$begin_date = new DateTime( $start );
	$end_date   = new DateTime( $end );

	$today = new DateTime();
	// We make sure the today does not includes the time otherwise it may interfere with the comparison
	$today->setTime( 0, 0, 0 );

	$nb_elapsed = $today->diff( $begin_date );

	$nb_days_elapsed = $nb_elapsed->format( "%$period_type" );

	$nb_periods_elapsed = intval( $nb_days_elapsed / $period );

	if ( $period_type == 'd' ) {
		$period_type_str = 'day';
	} elseif ( $period_type == 'm' ) {
		$period_type_str = 'month';
	} elseif ( $period_type == 'y' ) {
		$period_type_str = 'year';
	}

	$last_period_begin_date = $begin_date->modify( '+' . ( $nb_periods_elapsed * $period ) . " $period_type_str" );
	$last_period_end_date   = $end_date->modify( '+' . ( $nb_periods_elapsed * $period ) . " $period_type_str" );

	return ( ( $today >= $last_period_begin_date ) && ( $today <= $last_period_end_date ) );
}

/**
 * Returns the product id to use in order to apply the discounts
 *
 * @param type $product Product to check
 * @return int
 */
function wad_get_product_id_to_use( $product ) {
	$product_class = get_class( $product );

	if ( $product_class == 'WC_Product_Variation' ) {
		$pid = $product->get_id();
	} else {
		$pid = $product->get_id();
	}

	return $pid;
}

/**
 * Returns the list of products based actions
 *
 * @return array
 */
function wad_get_product_based_actions() {
	return array( 'percentage-off-pprice', 'fixed-amount-off-pprice', 'fixed-pprice' );
}

/**
 * Return the cart sub total
 *
 * @global type $woocommerce
 * @param Bool $inc_taxes Including taxes
 * @return type
 */
function wad_get_cart_total( $inc_taxes = false ) {
	global $woocommerce;
	global $wad_cart_total_inc_taxes;
	global $wad_settings;
	$inc_shipping_in_taxes = O_Utils::get_proper_value( $wad_settings, 'inc-shipping-in-taxes', 'Yes' );
	$cart_total            = 0;
	$wc_version            = WC()->version;
	// if(!did_action('template_redirect'))
	// return 0;
	if ( ! $inc_taxes ) {
		$cart_total = $woocommerce->cart->subtotal_ex_tax;
	} else {
		// Optimization: We don't need to recalculate everytime if it has already been calculated once
		if ( $wad_cart_total_inc_taxes !== false && $wad_cart_total_inc_taxes !== 0 && ! is_null( $wad_cart_total_inc_taxes ) ) {
			$cart_total = $wad_cart_total_inc_taxes;
		} else {

			if ( version_compare( $wc_version, '3.2.1', '<' ) ) {
				$taxes = $woocommerce->cart->taxes;
			} else {
				$taxes = $woocommerce->cart->get_cart_contents_taxes();
			}

			$cart_total = $woocommerce->cart->subtotal_ex_tax + array_sum( $taxes );
			if ( isset( $woocommerce->cart->tax_total ) && $woocommerce->cart->tax_total > 0 && empty( $taxes ) ) {
				$cart_total += $woocommerce->cart->tax_total;
			}
			if ( $inc_shipping_in_taxes == 'Yes' ) {
				$cart_total += $woocommerce->cart->shipping_total;
			}
		}
	}
	return $cart_total;
}

function wad_evaluate_conditions( $condition, $operator, $value ) {
	switch ( $operator ) {
		case '<':
			return $condition < $value;
			break;
		case '>':
			return $condition > $value;
			break;
		case '==':
			return $condition == $value;
			break;
		case '>=':
			return $condition >= $value;
			break;
		case '<=':
			return $condition <= $value;
			break;
		default:
			return false;
			break;
	};
}

function wad_filter_on_sale_products( $product_list, $discount_object ) {

	if ( empty( $product_list ) || ! is_array( $product_list ) || ! is_object( $discount_object ) ) {
		return array();
	}

	$wad_on_sale_products = array();

	foreach ( $product_list as $pid ) {

		if ( $discount_object->is_applicable( $pid ) ) {
			array_push( $wad_on_sale_products, $pid );
		}
	}

	return $wad_on_sale_products;
}
