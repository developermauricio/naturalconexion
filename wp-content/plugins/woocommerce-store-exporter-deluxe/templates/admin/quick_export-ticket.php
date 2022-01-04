<?php if( $ticket && $ticket_fields ) { ?>
		<div id="export-ticket" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Ticket Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $ticket ) { ?>
					<p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
					<p>
						<a href="javascript:void(0)" id="ticket-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="ticket-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="ticket-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'ticket' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="ticket-fields" class="ui-sortable striped">

		<?php foreach( $ticket_fields as $field ) { ?>
						<tr id="ticket-<?php echo $field['reset']; ?>" data-export-type="ticket" data-field-name="<?php printf( '%s-%s', 'ticket', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="ticket_fields[<?php echo $field['name']; ?>]" class="ticket_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'ticket' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="ticket_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_ticket" class="button-primary" value="<?php _e( 'Export Tickets', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Ticket field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Tickets were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-ticket-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Ticket Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_ticket_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_ticket_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_ticket_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-ticket -->

<?php } ?>