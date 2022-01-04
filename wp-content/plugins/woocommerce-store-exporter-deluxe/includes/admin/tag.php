<?php
// Quick Export

// HTML template for Filter Tags by Language widget on Store Exporter screen
function woo_ce_tags_filter_by_language() {

	if( !woo_ce_detect_wpml() )
		return;

	$languages = ( function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=N' ) : array() );

	ob_start(); ?>
<p><label><input type="checkbox" id="tags-filters-language" /> <?php _e( 'Filter Tags by Language', 'woocommerce-exporter' ); ?></label></p>
<div id="export-tags-filters-language" class="separator">
	<ul>
		<li>
<?php if( !empty( $languages ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Language...', 'woocommerce-exporter' ); ?>" name="tag_filter_language[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $languages as $key => $language ) { ?>
				<option value="<?php echo $key; ?>"><?php echo $language['native_name']; ?> (<?php echo $language['translated_name']; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Languages were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Language\'s you want to filter exported Tags by. Default is to include all Language\'s.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-tags-filters-language -->

<?php
	ob_end_flush();

}

// HTML template for Tag Sorting widget on Store Exporter screen
function woo_ce_tag_sorting() {

	$tag_orderby = woo_ce_get_option( 'tag_orderby', 'ID' );
	$tag_order = woo_ce_get_option( 'tag_order', 'ASC' );

	ob_start(); ?>
<p><label><?php _e( 'Product Tag Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="tag_orderby">
		<option value="id"<?php selected( 'id', $tag_orderby ); ?>><?php _e( 'Term ID', 'woocommerce-exporter' ); ?></option>
		<option value="name"<?php selected( 'name', $tag_orderby ); ?>><?php _e( 'Tag Name', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="tag_order">
		<option value="ASC"<?php selected( 'ASC', $tag_order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $tag_order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Product Tags within the exported file. By default this is set to export Product Tags by Term ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
	ob_end_flush();

}

// HTML template for Custom Tags widget on Store Exporter screen
function woo_ce_tags_custom_fields() {

	if( $custom_terms = woo_ce_get_option( 'custom_tags', '' ) )
		$custom_terms = implode( "\n", $custom_terms );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	ob_start(); ?>
<form method="post" id="export-tags-custom-fields" class="export-options tag-options">
	<div id="poststuff">

		<div class="postbox" id="export-options tag-options">
			<h3 class="hndle"><?php _e( 'Custom Tag Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom Tag meta in the list of available export fields above fill the meta text box then click Save Custom Fields. The saved custom fields will appear as export fields to be selected from the Tag Fields list.', 'woocommerce-exporter' ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label for="custom_tags"><?php _e( 'Tag meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea id="custom_tags" name="custom_tags" rows="5" cols="70"><?php echo esc_textarea( $custom_terms ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Tag meta in your export file by adding each custom Tag meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'woo_ce_tags_custom_fields' ); ?>

				</table>
				<p class="description"><?php printf( __( 'For more information on exporting custom Tag meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
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
<!-- #export-tags-custom-fields -->
<?php
	ob_end_flush();

}

// Scheduled Exports

function woo_ce_scheduled_export_filters_tag( $post_ID = 0 ) {

	ob_start(); ?>
<div class="export-options tag-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_tag', $post_ID ); ?>

</div>
	<!-- .tag-options -->

<?php
	ob_end_flush();

}

// HTML template for Tag Sorting filter on Edit Scheduled Export screen
function woo_ce_scheduled_export_tag_filter_orderby( $post_ID ) {

	$orderby = get_post_meta( $post_ID, '_filter_tag_orderby', true );
	// Default to Title
	if( $orderby == false ) {
		$orderby = 'name';
	}

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="tag_filter_orderby"><?php _e( 'Tag Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="tag_filter_orderby" name="tag_filter_orderby">
			<option value="id"<?php selected( 'id', $orderby ); ?>><?php _e( 'Term ID', 'woocommerce-exporter' ); ?></option>
			<option value="name"<?php selected( 'name', $orderby ); ?>><?php _e( 'Tag Name', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
	ob_end_flush();

}

// Export templates

function woo_ce_export_template_fields_tag( $post_ID = 0 ) {

	$export_type = 'tag';

	$fields = woo_ce_get_tag_fields( 'full', $post_ID );

	$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

	// Check if labels is empty
	if( $labels == false )
		$labels = array();

	ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Tag fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No Tag fields were found.', 'woocommerce-exporter' ); ?></p>
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