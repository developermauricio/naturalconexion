jQuery(document).ready(function($) {
	
	// Add New Row 
	$('.add.rule.button').on( 'click', function( event ){
		add_new_cost_row( event );
	});

	//Function to add new row into the shipping rate table
	function add_new_cost_row( event ) {
	
		var html_options = '';
		var json_shipping_classes = jQuery.parseJSON( wcss_object.shipping_classes );
		var calculation_type = $( '#woocommerce_super_shipping_calculation_type' ).val();
		$( json_shipping_classes ).each( function( key, value ){
			html_options += '<option value="' + value.slug + '" >'+ value.name +'</option>';
		});
		var size = $('table.wcss-form-table table#shipping_rules tbody .flat_rate').size();

		if ( $('table.wcss-form-table table#shipping_rules tbody .flat_rate').last().hasClass( 'alternate' ) ) {
			var html_row = '<tr class="flat_rate">';
		} else {

			var html_row = '<tr class="flat_rate alternate">';
		};
		
		html_row += '<th class="check-column"><input type="checkbox" name="select" /></th>\
			<td><select name="' + wcss_object.id + '[shipping_class][' + size + ']" class="shipping_class_list">\
				<option value="no-class">' + wcss_object.no_class_string + '</option>' + html_options + '</select>\
			</td>\
			<td><select name="' + wcss_object.id + '[conditional][' + size + ']">\
				<option value="1" selected="selected">' + wcss_object.weight_string + '</option>\
				<option value="2">' + wcss_object.price_string + '</option>\
				<option value="3">' + wcss_object.item_count_string + '</option>\
				<option value="4">' + wcss_object.volume_string + '</option>\
			</select></td>\
			<td>\
				<div class="horizontal-inputs-wrap">\
					<input type="text" name="' + wcss_object.id + '[range][min][' + size + ']" class="text wc_ss_input_decimal required" placeholder="' + wcss_object.min_string + '">\
					<input type="text" name="' + wcss_object.id + '[range][max][' + size + ']" class="text wc_ss_input_decimal required" placeholder="' + wcss_object.max_string + '">\
				</div>\
			</td>\
			<td><input type="text" class="wc_ss_input_decimal required" value="" name="' + wcss_object.id + '[cost][' + size + ']" placeholder="0.00" size="6" /></td>\
			<td><input type="text" class="wc_ss_input_decimal" value="" name="' + wcss_object.id + '[cost_per_additional_unit][' + size + ']" placeholder="0.00" size="6"></td>\
		</tr>';
	
		$( html_row ).appendTo('table.wcss-form-table table#shipping_rules tbody');
		
		event.preventDefault();
		return false;
	}

	// Duplicate Selected Rows
	$('.duplicate.rule.button').on( 'click', function( event ){
		duplicate_cost_row( event );
	});

	//Function to duplicate selected rows into the shipping rate table
	function duplicate_cost_row( event ){
		var size = jQuery('table.wcss-form-table table#shipping_rules tbody .flat_rate').size();
		var checked_rows = jQuery('table.wcss-form-table table#shipping_rules tbody tr th.check-column input:checked').size();
		if( checked_rows > 0 ){
	
			jQuery( 'table.wcss-form-table table#shipping_rules tbody tr th.check-column input:checked' ).each( function(){
				var row_to_clone = jQuery( this ).closest( 'tr' ).clone( true );
				row_to_clone.find( 'th.check-column input:checked' ).prop( 'checked', false );
				row_to_clone.appendTo( 'table.wcss-form-table table#shipping_rules tbody' );
				// Update attibute name of each row
				update_name_attribute( row_to_clone, size );
				size++;
			});
		}
	
		event.preventDefault();
		return false;
	}

	// Update the name of attribute for each field
	function update_name_attribute( row_to_clone, size ){
		jQuery( row_to_clone ).find( 'td:eq(0) select' ).attr( 'name', '' + wcss_object.id + '[shipping_class][' + size + ']' );
		jQuery( row_to_clone ).find( 'td:eq(1) select' ).attr( 'name', '' + wcss_object.id + '[conditional][' + size + ']' );
		jQuery( row_to_clone ).find( 'td:eq(2) input:eq(0)' ).attr( 'name', '' + wcss_object.id + '[range][min][' + size + ']' );
		jQuery( row_to_clone ).find( 'td:eq(2) input:eq(1)' ).attr( 'name', '' + wcss_object.id + '[range][max][' + size + ']' );
		jQuery( row_to_clone ).find( 'td:eq(3) input' ).attr( 'name', '' + wcss_object.id + '[cost][' + size + ']' );
		jQuery( row_to_clone ).find( 'td:eq(4) input' ).attr( 'name', '' + wcss_object.id + '[cost_per_additional_unit][' + size + ']' );
	}

	// Remove Selected Rows 
	$('.delete.rule.button').on( 'click', function( event ){
		remove_cost_row( event );
	});
	
	//Function to delete selected rows into the shipping rate table
	function remove_cost_row( event ) {
		var size = jQuery('table.wcss-form-table table#shipping_rules tbody tr th.check-column input:checked').size();
		if( size > 0 ){
			var answer = confirm( wcss_object.delete_selected_rates_confirm_string )
			if (answer) {
				jQuery('table.wcss-form-table table#shipping_rules tbody tr th.check-column input:checked').each(function(i, el){
					jQuery(el).closest('tr').remove();
				});
			}
		}
		event.preventDefault();
		return false;
	}

	// Show or hide the volumetric weight factor field
	$( '#woocommerce_super_shipping_volumetric_weight_measure' ).change( function(){
		if ( $( this ).is( ':checked' ) ) {
			$( '#woocommerce_super_shipping_volumetric_weight_factor' ).closest( 'tr' ).show();
		} else {
			$( '#woocommerce_super_shipping_volumetric_weight_factor' ).closest( 'tr' ).hide();
		}
	}).change();

	// Field validation error tips
	$( document.body )
	.on( 'keyup change', '.wc_ss_input_decimal[type=text]', function() {
		var value    = $( this ).val();
		var regex    = new RegExp( '[^\-0-9\%\\\*' + woocommerce_admin.decimal_point + ']+', 'gi' );
		var newvalue = value.replace( regex, '' );
		if ( value !== newvalue ) {
			$( this ).val( newvalue );
			$( document.body ).triggerHandler( 'wc_add_error_tip', [ $( this ), 'i18n_decimal_error' ] );
		} else {
			$( document.body ).triggerHandler( 'wc_remove_error_tip', [ $( this ), 'i18n_decimal_error' ] );
		}
	});

	// Check if there is required fields empty
	$( '#woocommerce_super_shipping_special_rate' ).closest( 'form' ).submit( function( event ) {
		
		$( 'input[type=text].required' ).each( function( i, el ){
			var value = $.trim( $( el ).val() );

			if ( $.isEmptyObject( value )  ) {

				jQuery( '#message.notice' ).remove();
				$( 'h2' ).before( '<div id="message" class="notice notice-error"><p><strong>'+ wcss_object.error_empty_fields_string +'</strong></p></div>' );
				$( 'html, body' ).animate( { scrollTop: 0 }, "slow" );

				event.preventDefault();
				return false;
			};
		});
	});
});