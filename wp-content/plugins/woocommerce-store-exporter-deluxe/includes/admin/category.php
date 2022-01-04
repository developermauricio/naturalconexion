<?php
// Quick Export

// HTML template for Category Sorting widget on Store Exporter screen
function woo_ce_category_sorting() {

	$category_orderby = woo_ce_get_option( 'category_orderby', 'ID' );
	$category_order = woo_ce_get_option( 'category_order', 'ASC' );

	ob_start(); ?>
<p><label><?php _e( 'Category Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="category_orderby">
		<option value="id"<?php selected( 'id', $category_orderby ); ?>><?php _e( 'Term ID', 'woocommerce-exporter' ); ?></option>
		<option value="name"<?php selected( 'name', $category_orderby ); ?>><?php _e( 'Category Name', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="category_order">
		<option value="ASC"<?php selected( 'ASC', $category_order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $category_order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Categories within the exported file. By default this is set to export Categories by Term ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
	ob_end_flush();

}

// HTML template for Custom Categories widget on Store Exporter screen
function woo_ce_categories_custom_fields() {

	if( $custom_terms = woo_ce_get_option( 'custom_categories', '' ) )
		$custom_terms = implode( "\n", $custom_terms );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	ob_start(); ?>
<form method="post" id="export-categories-custom-fields" class="export-options category-options">
	<div id="poststuff">

		<div class="postbox" id="export-options category-options">
			<h3 class="hndle"><?php _e( 'Custom Category Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom Category meta in the list of available export fields above fill the meta text box then click Save Custom Fields. The saved custom fields will appear as export fields to be selected from the Category Fields list.', 'woocommerce-exporter' ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label for="custom_categories"><?php _e( 'Category meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea id="custom_categories" name="custom_categories" rows="5" cols="70"><?php echo esc_textarea( $custom_terms ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Category meta in your export file by adding each custom Category meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'woo_ce_categories_custom_fields' ); ?>

				</table>
				<p class="description"><?php printf( __( 'For more information on exporting custom Category meta and Attributes consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Custom Fields', 'woocommerce-exporter' ); ?>" class="button" />
				</p>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

	</div>
	<!-- #poststuff -->
	<input type="hidden" name="action" value="update" />
</form>
<!-- #export-categories-custom-fields -->
<?php
	ob_end_flush();

}

// Scheduled Export

function woo_ce_scheduled_export_filters_category( $post_ID = 0 ) {

	ob_start(); ?>
<div class="export-options category-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_category', $post_ID ); ?>

</div>
<!-- .category-options -->

<?php
	ob_end_flush();

}

// HTML template for Category Sorting filter on Edit Scheduled Export screen
function woo_ce_scheduled_export_category_filter_orderby( $post_ID ) {

	$orderby = get_post_meta( $post_ID, '_filter_category_orderby', true );
	// Default to Title
	if( $orderby == false ) {
		$orderby = 'name';
	}

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="category_filter_orderby"><?php _e( 'Category Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="category_filter_orderby" name="category_filter_orderby">
			<option value="id"<?php selected( 'id', $orderby ); ?>><?php _e( 'Term ID', 'woocommerce-exporter' ); ?></option>
			<option value="name"<?php selected( 'name', $orderby ); ?>><?php _e( 'Category Name', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
	ob_end_flush();

}

// Export templates

function woo_ce_export_template_fields_category( $post_ID = 0 ) {

	$export_type = 'category';

	$fields = woo_ce_get_category_fields( 'full', $post_ID );

	$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

	// Check if labels is empty
	if( $labels == false )
		$labels = array();

	ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Category fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No Category fields were found.', 'woocommerce-exporter' ); ?></p>
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