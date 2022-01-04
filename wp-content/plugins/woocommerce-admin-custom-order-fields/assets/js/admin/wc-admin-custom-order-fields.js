jQuery( function( $ ) {
	'use strict';

	/* global ajaxurl, alert, wc_admin_custom_order_fields_params */

	var	$editor = $( 'table.wc-admin-custom-order-fields-editor' );

	$editor
		// add new field
		.on( 'click', '.js-wc-admin-custom-order-fields-add-field', function () {

			// clone the blank row
			$editor.append( wc_admin_custom_order_fields_params.new_row );

			// re-index all rows
			indexRows();

			// trigger select2
			$( document.body ).trigger( 'wc-enhanced-select-init' );

			// force change
			$editor.find( 'select' ).change();

			// Tooltips
			$editor.find( '.help_tip, .woocommerce-help-tip' ).tipTip( {
				'attribute' : 'data-tip',
				'fadeIn'    : 50,
				'fadeOut'   : 50,
				'delay'     : 200
			} );

			return false;
		} )

		// remove selected fields
		.on( 'click', '.js-wc-admin-custom-order-fields-remove', function() {

			$editor.find( 'tbody .check-column input:checked' ).each( function() {

				$( this ).closest( 'tr' ).remove();
			} );

			// re-index all rows
			indexRows();

			return false;
		} )

		// handle field type changes
		.on( 'change', 'select.js-wc-custom-order-field-type', function() {

			var $defaultValues = $( this ).closest( 'tr' ).find( 'input.js-wc-custom-order-field-default-values' ),
				$attributes = $( this ).closest( 'tr' ).find( 'select.js-wc-custom-order-field-attributes' ),
				fieldType = $( this ).val();


			// add/remove datepicker
			if ( 'date' === fieldType ) {

				$defaultValues.datepicker( {
					dateFormat     : 'yy-mm-dd',
					numberOfMonths : 1,
					showOn         : 'focus'
				} );

			} else {

				$defaultValues.datepicker( 'destroy' );
			}

			// blank placeholder for text
			if ( 'text' === fieldType || 'textarea' === fieldType || 'date' === fieldType ) {

				$defaultValues.prop( 'placeholder', '' );

			} else {

				$defaultValues.prop( 'placeholder', wc_admin_custom_order_fields_params.default_placeholder_text );
			}

			// don't allow the filterable attribute for textareas
			if ( 'textarea' === fieldType ) {

				$attributes.find( 'option[value=filterable]' ).prop( 'disabled', true );

			} else {

				$attributes.find( 'option[value=filterable]' ).prop( 'disabled', false );
			}

			// don't allow the sortable attribute for multiselects/checkboxes
			if ( 'multiselect' === fieldType || 'checkbox' === fieldType ) {

				$attributes.find( 'option[value=sortable]' ).prop( 'disabled', true );

			} else {

				$attributes.find( 'option[value=sortable]' ).prop( 'disabled', false );
			}

		} )

		// make fields sortable
		.find( 'tbody' ).sortable( {
			items  : 'tr',
			cursor : 'move',
			axis   : 'y',
			handle : 'td.js-wc-custom-order-field-draggable',
			scrollSensitivity : 40,
			helper: function( e, ui ) {
				return ui;
			},
			start: function( event, ui ) {
				ui.item.css( 'background-color','#f6f6f6' );
			},
			stop: function( event, ui ) {
				ui.item.removeAttr( 'style' );
				indexRows();
			}
		} )

		// force field types to be handled on page load (e.g. adding datepicker to date fields)
		.find( 'select' ).change();

	// ensure required field properties are set
	$( 'form.wc-admin-custom-order-fields' ).submit( function() {

		var valid = true;

		// labels are required for every field
		$editor.find( 'input.js-wc-custom-order-field-label' ).each( function() {

			if ( ! $( this ).val() ) {

				alert( wc_admin_custom_order_fields_params.label_required_text );

				return valid = false;
			}
		} );

		return valid;
	} );

	// index rows
	function indexRows() {

		$editor.find( 'tbody tr' ).each( function( index ) {
			$( this ).find( 'input, select' ).each( function() {

				var name = $( this ).attr( 'name' );

				if ( typeof name !== 'undefined' && name ) {
					name = name.replace( /\[\d*\]/, '[' +  index + ']' );
					$( this ).attr( 'name', name );
				}
			} );
		} );
	}

	// hide welcome notice
	$( 'div.wc-connect a.skip' ).click( function() {

		$.get( ajaxurl, { action: 'wc_admin_custom_order_fields_dismiss_welcome_notice' } );

		$(this ).parents( 'div.wc-connect' ).fadeOut();
	} );

} );
