<?php if( $attribute && $attribute_fields ) { ?>
		<div id="export-attribute" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Attribute Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $attribute ) { ?>
					<p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
					<p>
						<a href="javascript:void(0)" id="attribute-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="attribute-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="attribute-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'attribute' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="attribute-fields" class="ui-sortable striped">

		<?php foreach( $attribute_fields as $field ) { ?>
						<tr id="attribute-<?php echo $field['reset']; ?>" data-export-type="attribute" data-field-name="<?php printf( '%s-%s', 'attribute', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="attribute_fields[<?php echo $field['name']; ?>]" class="attribute_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'attribute' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="attribute_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_attribute" class="button-primary" value="<?php _e( 'Export Attributes', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Attribute field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Attributes were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->
		</div>
		<!-- #export-attributes -->
<?php } ?>