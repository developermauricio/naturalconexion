<?php
// Quick Export

// HTML template for Filter Orders by Brand widget on Store Exporter screen
function woo_ce_orders_filter_by_product_brand() {

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() == false )
		return;

	$args = array(
		'hide_empty' => 1,
		'orderby' => 'term_group'
	);
	$product_brands = woo_ce_get_product_brands( $args );
	$types = woo_ce_get_option( 'order_brand', array() );

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-brand"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Product Brand', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-brand" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_brands ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Brand...', 'woocommerce-exporter' ); ?>" name="order_filter_brand[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_brands as $product_brand ) { ?>
				<option value="<?php echo $product_brand->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_brand->term_id, $types, false ), true ) : '' ); ?>><?php echo woo_ce_format_product_category_label( $product_brand->name, $product_brand->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_brand->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Brands were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Brands you want to filter exported Orders by. Product Brands not assigned to Products are hidden from view. Default is to include all Product Brands.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-brand -->
<?php
	ob_end_flush();

}

// HTML template for Filter Orders by Product Vendor widget on Store Exporter screen
function woo_ce_orders_filter_by_product_vendor() {

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) == false && woo_ce_detect_export_plugin( 'yith_vendor' ) == false )
		return;

	$args = array(
		'hide_empty' => 1
	);
	$product_vendors = woo_ce_get_product_vendors( $args, 'full' );
	$types = woo_ce_get_option( 'order_product_vendor', array() );

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-product_vendor"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Product Vendor', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-product_vendor" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_vendors ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Vendor...', 'woocommerce-exporter' ); ?>" id="order_filter_vendor" name="order_filter_product_vendor[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_vendors as $product_vendor ) { ?>
				<option value="<?php echo $product_vendor->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_vendor->term_id, $types, false ), true ) : '' ); ?><?php disabled( $product_vendor->count, 0 ); ?>><?php echo $product_vendor->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Product Vendors to be included in the export. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-product_vendor -->
<?php
	ob_end_flush();

}

// HTML template for Filter Orders by Delivery Date widget on Store Exporter screen
function woo_ce_orders_filter_by_delivery_date() {

	// YITH WooCommerce Delivery Date Premium - http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/
	// Order Delivery Date for WooCommerce - https://wordpress.org/plugins/order-delivery-date-for-woocommerce/
	// Order Delivery Date Pro for WooCommerce - https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/
	if(
	 woo_ce_detect_export_plugin( 'yith_delivery_pro' ) == false && 
	 woo_ce_detect_export_plugin( 'orddd_free' ) == false &&
	 woo_ce_detect_export_plugin( 'orddd' ) == false
	)
		return;

	$delivery_dates_from = woo_ce_get_order_first_date();
	$delivery_dates_to = woo_ce_get_order_date_filter( 'today', 'from', 'd/m/Y' );
	$types = woo_ce_get_option( 'order_delivery_dates_filter' );

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-delivery_date"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Delivery Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-delivery_date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $delivery_dates_from; ?> - <?php echo $delivery_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value="tomorrow"<?php checked( $types, 'tomorrow' ); ?> /> <?php _e( 'Tomorrow', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="delivery_dates_from" name="order_delivery_dates_from" value="<?php echo esc_attr( $delivery_dates_from ); ?>" class="text code datepicker order_delivery_dates_export" /> to <input type="text" size="10" maxlength="10" id="delivery_dates_to" name="order_delivery_dates_to" value="<?php echo esc_attr( $delivery_dates_to ); ?>" class="text code datepicker order_delivery_dates_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-delivery_date -->
<?php
	ob_end_flush();

}

// HTML template for Filter Orders by Booking Date widget on Store Exporter screen
function woo_ce_orders_filter_by_booking_date() {

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( !woo_ce_detect_export_plugin( 'woocommerce_bookings' ) )
		return;

	$current_year = date( 'Y', current_time( 'timestamp' ) );
	$last_year = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
	$today = date( 'l', current_time( 'timestamp' ) );
	$yesterday = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
	$current_month = date( 'F', current_time( 'timestamp' ) );
	$last_month = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) )-1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
	$order_dates_from = woo_ce_get_order_first_date();
	$order_dates_to = woo_ce_get_order_date_filter( 'today', 'from', 'd/m/Y' );

	$types = woo_ce_get_option( 'order_booking_dates_filter' );

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-booking_date"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Booking Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-booking_date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $order_dates_from; ?> - <?php echo $order_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="current_week"<?php checked( $types, 'current_week' ); ?> /> <?php _e( 'Current week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="last_week"<?php checked( $types, 'last_week' ); ?> /> <?php _e( 'Last week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="current_month"<?php checked( $types, 'current_month' ); ?> /> <?php _e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="last_month"<?php checked( $types, 'last_month' ); ?> /> <?php _e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="current_year"<?php checked( $types, 'current_year' ); ?> /> <?php _e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo $current_year; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="last_year"<?php checked( $types, 'last_year' ); ?> /> <?php _e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo $last_year; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="booking_dates_from" name="order_booking_dates_from" value="<?php echo esc_attr( $order_dates_from ); ?>" class="text code datepicker order_booking_dates_export" /> to <input type="text" size="10" maxlength="10" id="booking_dates_to" name="order_booking_dates_to" value="<?php echo esc_attr( $order_dates_to ); ?>" class="text code datepicker order_booking_dates_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-booking_date -->
<?php
	ob_end_flush();

}

// HTML template for Filter Orders by Booking Start Date widget on Store Exporter screen
function woo_ce_orders_filter_by_booking_start_date() {

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( !woo_ce_detect_export_plugin( 'wc_easybooking' ) )
		return;

	$current_year = date( 'Y', current_time( 'timestamp' ) );
	$last_year = date( 'Y', strtotime( '-1 year', current_time( 'timestamp' ) ) );
	$today = date( 'l', current_time( 'timestamp' ) );
	$yesterday = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
	$current_month = date( 'F', current_time( 'timestamp' ) );
	$last_month = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) )-1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
	$order_dates_from = woo_ce_get_order_first_date();
	$order_dates_to = woo_ce_get_order_date_filter( 'today', 'from', 'd/m/Y' );

	$types = woo_ce_get_option( 'order_booking_start_dates_filter' );

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-booking_start_date"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Booking Start Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-booking_start_date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $order_dates_from; ?> - <?php echo $order_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="current_week"<?php checked( $types, 'current_week' ); ?> /> <?php _e( 'Current week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="last_week"<?php checked( $types, 'last_week' ); ?> /> <?php _e( 'Last week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="current_month"<?php checked( $types, 'current_month' ); ?> /> <?php _e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="last_month"<?php checked( $types, 'last_month' ); ?> /> <?php _e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="current_year"<?php checked( $types, 'current_year' ); ?> /> <?php _e( 'Current year', 'woocommerce-exporter' ); ?> (<?php echo $current_year; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="last_year"<?php checked( $types, 'last_year' ); ?> /> <?php _e( 'Last year', 'woocommerce-exporter' ); ?> (<?php echo $last_year; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_start_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="booking_start_dates_from" name="order_start_booking_dates_from" value="<?php echo esc_attr( $order_dates_from ); ?>" class="text code datepicker order_booking_dates_export" /> to <input type="text" size="10" maxlength="10" id="booking_start_dates_to" name="order_booking_start_dates_to" value="<?php echo esc_attr( $order_dates_to ); ?>" class="text code datepicker order_booking_dates_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-booking_start_date -->
<?php
	ob_end_flush();

}

function woo_ce_orders_filter_by_voucher_redeemed() {

	// WooCommerce PDF Product Vouchers - http://www.woothemes.com/products/pdf-product-vouchers/
	if( woo_ce_detect_export_plugin( 'wc_pdf_product_vouchers' ) == false )
		return;

	$types = woo_ce_get_option( 'order_voucher_redeemed' );

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-voucher_redeemed"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Voucher Redeemed', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-voucher_redeemed" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_filter_voucher_redeemed" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All Orders', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_voucher_redeemed" value="redeemed"<?php checked( $types, 'redeemed' ); ?> /> <?php _e( 'Orders marked as redeemed', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_voucher_redeemed" value="unredeemed"<?php checked( $types, 'unredeemed' ); ?> /> <?php _e( 'Orders marked un-redeemed', 'woocommerce-exporter' ); ?></label>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-voucher_redeemed -->
<?php
	ob_end_flush();

}

function woo_ce_orders_filter_by_order_type() {

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) == false )
		return;

	$types = woo_ce_get_option( 'order_order_type' );

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-order_type"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order Type', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-order_type" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_filter_order_type" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All Orders', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="original"<?php checked( $types, 'original' ); ?> /> <?php _e( 'Original', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="parent"<?php checked( $types, 'parent' ); ?> /> <?php _e( 'Subscription Parent', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="renewal"<?php checked( $types, 'renewal' ); ?> /> <?php _e( 'Subscription Renewal', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="resubscribe"<?php checked( $types, 'resubscribe' ); ?> /> <?php _e( 'Subscription Resubscribe', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="switch"<?php checked( $types, 'switch' ); ?> /> <?php _e( 'Subscription Switch', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="regular"<?php checked( $types, 'regular' ); ?> /> <?php _e( 'Non-subscription', 'woocommerce-exporter' ); ?></label>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-order_type -->
<?php
	ob_end_flush();

}

function woo_ce_extend_order_sorting( $orderby = false ) {

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( !woo_ce_detect_export_plugin( 'wc_easybooking' ) )
		return;

	ob_start(); ?>
<option value="booking_start_date"<?php selected( $orderby, 'booking_start_date' ); ?>><?php _e( 'Booking Start Date', 'woocommerce-exporter' ); ?></option>
<option value="booking_end_date"<?php selected( $orderby, 'booking_end_date' ); ?>><?php _e( 'Booking End Date', 'woocommerce-exporter' ); ?></option>
<?php
	ob_end_flush();

}

function woo_ce_orders_custom_fields_extra_product_options() {

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) == false )
		return;

	if( $custom_extra_product_options = woo_ce_get_option( 'custom_extra_product_options', '' ) )
		$custom_extra_product_options = implode( "\n", $custom_extra_product_options );

	ob_start(); ?>
<tr>
	<th>
		<label for="custom_extra_product_options"><?php _e( 'Custom Extra Product Options', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<textarea id="custom_extra_product_options" name="custom_extra_product_options" rows="5" cols="70"><?php echo esc_textarea( $custom_extra_product_options ); ?></textarea>
		<p class="description"><?php _e( 'Include custom Extra Product Options linked to Order Items within in your export file by adding the Name of each Extra Product Option to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
	ob_end_flush();

}

function woo_ce_orders_custom_fields_product_addons() {

	if( ( woo_ce_detect_export_plugin( 'product_addons' ) ) == false )
		return;

	if( $custom_product_addons = woo_ce_get_option( 'custom_product_addons', '' ) )
		$custom_product_addons = implode( "\n", $custom_product_addons );

	ob_start(); ?>
<tr>
	<th>
		<label for="custom_product_addons"><?php _e( 'Custom Product Add-ons', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<textarea id="custom_product_addons" name="custom_product_addons" rows="5" cols="70"><?php echo esc_textarea( $custom_product_addons ); ?></textarea>
		<p class="description"><?php _e( 'Include custom Product Add-ons (not Global Add-ons) linked to individual Products within in your export file by adding the Group Name of each Product Addon to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
	ob_end_flush();

}

// Scheduled Exports

function woo_ce_scheduled_export_order_filter_by_product_brand( $post_ID = 0 ) {

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() == false )
		return;

	$args = array(
		'hide_empty' => 1,
		'orderby' => 'term_group'
	);
	$product_brands = woo_ce_get_product_brands( $args );
	$types = get_post_meta( $post_ID, '_filter_order_brand', true );

	ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_brand"><?php _e( 'Product brand', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $product_brands ) ) { ?>
	<select id="order_filter_brand" data-placeholder="<?php _e( 'Choose a Product Brand...', 'woocommerce-exporter' ); ?>" name="order_filter_brand[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_brands as $product_brand ) { ?>
		<option value="<?php echo $product_brand->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $product_brand->term_id, $types ) : false ), true ); ?><?php disabled( $product_brand->count, 0 ); ?>><?php echo woo_ce_format_product_category_label( $product_brand->name, $product_brand->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_brand->term_id ); ?>)</option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product Brands you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Product Brands were found linked to Products.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>

<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_order_filter_by_order_type( $post_ID = 0 ) {

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) == false )
		return;

	$types = get_post_meta( $post_ID, '_filter_order_type', true );

	ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_order_type"><?php _e( 'Order type', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="order_filter_order_type" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'All Orders', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="original"<?php checked( $types, 'original' ); ?> />&nbsp;<?php _e( 'Original', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="parent"<?php checked( $types, 'parent' ); ?> />&nbsp;<?php _e( 'Subscription Parent', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="renewal"<?php checked( $types, 'renewal' ); ?> />&nbsp;<?php _e( 'Subscription Renewal', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="resubscribe"<?php checked( $types, 'resubscribe' ); ?> />&nbsp;<?php _e( 'Subscription Resubscribe', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="switch"<?php checked( $types, 'switch' ); ?> />&nbsp;<?php _e( 'Subscription Switch', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="regular"<?php checked( $types, 'regular' ); ?> />&nbsp;<?php _e( 'Non-subscription', 'woocommerce-exporter' ); ?>
</p>

<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_order_filter_by_booking_start_date( $post_ID = 0 ) {

	// WooCommerce Easy Booking - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) == false )
		return;

	$types = get_post_meta( $post_ID, '_filter_order_booking_start_date_filter', true );
	$booking_start_date_from = get_post_meta( $post_ID, '_filter_order_booking_start_date_from', true );
	$booking_start_date_to = get_post_meta( $post_ID, '_filter_order_booking_start_date_to', true );

	ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_booking_start_date"><?php _e( 'Booking start date', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="order_booking_start_dates_filter" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'All', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="today"<?php checked( $types, 'today' ); ?> />&nbsp;<?php _e( 'Today', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php _e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="current_week"<?php checked( $types, 'current_week' ); ?> />&nbsp;<?php _e( 'Current week', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="last_week"<?php checked( $types, 'last_week' ); ?> />&nbsp;<?php _e( 'Last week', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="current_month"<?php checked( $types, 'current_month' ); ?> />&nbsp;<?php _e( 'Current month', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="last_month"<?php checked( $types, 'last_month' ); ?> />&nbsp;<?php _e( 'Last month', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="current_year"<?php checked( $types, 'current_year' ); ?> />&nbsp;<?php _e( 'Current year', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="last_year"<?php checked( $types, 'last_year' ); ?> />&nbsp;<?php _e( 'Last year', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_booking_start_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> />&nbsp;<?php _e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
	<input type="text" name="order_booking_start_dates_from" value="<?php echo $booking_start_date_from; ?>" size="10" maxlength="10" class="sized datepicker order_export" /> <span style="float:left; margin-right:6px;"><?php _e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="order_booking_start_dates_to" value="<?php echo $booking_start_date_to; ?>" size="10" maxlength="10" class="sized datepicker order_export" /><br class="clear" />

</p>
<?php
	ob_end_flush();
}