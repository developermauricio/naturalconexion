<?php
// Quick Export

// HTML template for Booking Sorting widget on Store Exporter screen
function woo_ce_booking_sorting() {

	$booking_orderby = woo_ce_get_option( 'booking_orderby', 'ID' );
	$booking_order = woo_ce_get_option( 'booking_order', 'ASC' );

	ob_start(); ?>
<p><label><?php _e( 'Booking Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="booking_orderby">
		<option value="ID"<?php selected( 'ID', $booking_orderby ); ?>><?php _e( 'Booking Number', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $booking_orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $booking_orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $booking_orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="booking_order">
		<option value="ASC"<?php selected( 'ASC', $booking_order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $booking_order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Bookings within the exported file. By default this is set to export Bookings by Booking ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
	ob_end_flush();

}

// Export templates

function woo_ce_export_template_fields_booking( $post_ID = 0 ) {

	$export_type = 'booking';

	$fields = woo_ce_get_booking_fields( 'full', $post_ID );

	$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

	// Check if labels is empty
	if( $labels == false )
		$labels = array();

	ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Booking fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="<?php echo $export_type; ?>-fields" class="ui-sortable">
				<tbody>
	<?php foreach( $fields as $field ) { ?>
					<tr id="<?php echo $export_type; ?>-<?php echo $field['reset']; ?>">
						<td>
							<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
								<input type="checkbox" name="<?php echo $export_type; ?>_fields[<?php echo $field['name']; ?>]" class="<?php echo $export_type; ?>_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?>
							</label>
							<input type="text" name="<?php echo $export_type; ?>_fields_label[<?php echo $field['name']; ?>]" class="text" placeholder="<?php echo $field['label']; ?>" value="<?php echo ( array_key_exists( $field['name'], $labels ) ? $labels[$field['name']] : '' ); ?>" />
							<input type="hidden" name="<?php echo $export_type; ?>_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
						</td>
					</tr>
	<?php } ?>
				</tbody>
			</table>
			<!-- #<?php echo $export_type; ?>-fields -->
<?php } else { ?>
			<p><?php _e( 'No Booking fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->

</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

// Add Export to... to Booking screen
function woo_ce_extend_woocommerce_admin_booking_actions( $actions, $booking ) {

/*
	$actions['export_csv'] = array(
		'url' 		=> admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
		'name' 		=> __( 'Export to CSV', 'woocommerce-bookings' ),
		'action' 	=> "export_booking_csv"
	);
*/
	return $actions;

}
add_filter( 'woocommerce_admin_booking_actions', 'woo_ce_extend_woocommerce_admin_booking_actions', 10, 2 );
?>