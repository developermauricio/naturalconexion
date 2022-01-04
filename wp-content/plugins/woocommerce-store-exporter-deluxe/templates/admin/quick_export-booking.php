<?php if( $booking && $booking_fields ) { ?>
		<div id="export-booking" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Booking Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $booking ) { ?>
					<p class="description"><?php woo_ce_export_fields_summary_text( $export_type ); ?></p>
					<p>
						<a href="javascript:void(0)" id="booking-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="booking-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="booking-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'booking' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="booking-fields" class="ui-sortable striped">

		<?php foreach( $booking_fields as $field ) { ?>
						<tr id="booking-<?php echo $field['reset']; ?>" data-export-type="booking" data-field-name="<?php printf( '%s-%s', 'booking', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="booking_fields[<?php echo $field['name']; ?>]" class="booking_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'booking' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="booking_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_booking" class="button-primary" value="<?php _e( 'Export Bookings', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Booking field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Bookings were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-bookings-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Booking Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_booking_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_booking_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_booking_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-booking -->

<?php } ?>