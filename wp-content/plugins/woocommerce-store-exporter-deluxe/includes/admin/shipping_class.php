<?php
// Scheduled Exports

function woo_ce_scheduled_export_filters_shipping_class( $post_ID = 0 ) {

	ob_start(); ?>
<div class="export-options shipping_class-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_shipping_class', $post_ID ); ?>

</div>
<!-- .shipping_class-options -->

<?php
	ob_end_flush();

}


// HTML template for Shipping Class Sorting filter on Edit Scheduled Export screen
function woo_ce_scheduled_export_shipping_class_filter_orderby( $post_ID ) {

	$orderby = get_post_meta( $post_ID, '_filter_shipping_class_orderby', true );
	// Default to Title
	if( $orderby == false ) {
		$orderby = 'name';
	}

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="shipping_class_filter_orderby"><?php _e( 'Shipping Class Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="shipping_class_filter_orderby" name="shipping_class_filter_orderby">
			<option value="id"<?php selected( 'id', $orderby ); ?>><?php _e( 'Term ID', 'woocommerce-exporter' ); ?></option>
			<option value="name"<?php selected( 'name', $orderby ); ?>><?php _e( 'Shipping Class Name', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
	ob_end_flush();

}

// Quick Export

// HTML template for Shipping Class Sorting widget on Store Exporter screen
function woo_ce_shipping_class_sorting() {

	$orderby = woo_ce_get_option( 'shipping_class_orderby', 'ID' );
	$order = woo_ce_get_option( 'shipping_class_order', 'ASC' );

	ob_start(); ?>
<p><label><?php _e( 'Shipping Class Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="shipping_class_orderby">
		<option value="id"<?php selected( 'id', $orderby ); ?>><?php _e( 'Term ID', 'woocommerce-exporter' ); ?></option>
		<option value="name"<?php selected( 'name', $orderby ); ?>><?php _e( 'Shipping Class Name', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="shipping_class_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Shipping Classes within the exported file. By default this is set to export Shipping Classes by Term ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
	ob_end_flush();

}

// Export templates

function woo_ce_export_template_fields_shipping_class( $post_ID = 0 ) {

	$export_type = 'shipping_class';

	$fields = woo_ce_get_shipping_class_fields( 'full', $post_ID );

	$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

	// Check if labels is empty
	if( $labels == false )
		$labels = array();

	ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Shipping Class fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No Shipping Class fields were found.', 'woocommerce-exporter' ); ?></p>
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
?>