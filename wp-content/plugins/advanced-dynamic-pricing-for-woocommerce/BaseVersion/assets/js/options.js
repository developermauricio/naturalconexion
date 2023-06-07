jQuery( document ).ready( function ( $ ) {


	$( '.section_choice' ).click( function () {

		$( '.section_choice' ).removeClass( 'active' );
		$( this ).addClass( 'active' );

		$( '.settings-section' ).removeClass( 'active' );
		$( '#' + $( this ).data( 'section' ) + '_section' ).addClass( 'active' );

		window.location.href = $( this ).attr( 'href' );
	} );

	setTimeout( function () {
		if ( window.location.hash.indexOf( 'section' ) !== - 1 ) {
			$( '.section_choice[href="' + window.location.hash + '"]' ).click()
		} else {
			$( '.section_choice' ).first().click()
		}
	}, 0 );

	setTimeout(function () {
		$('#update_price_with_qty').change(function() {
			if (this.checked) {
        $('#enable_quick_price_change_for_simple_products').closest('tr').show()
        $('#show_spinner_when_update_price').closest('tr').show()
				$('#replace_variable_price').closest('tr').show()
			} else {
				$('#enable_quick_price_change_for_simple_products').closest('tr').hide()
				$('#replace_variable_price').closest('tr').hide()
        $('#show_spinner_when_update_price').closest('tr').hide()
			}
		}).trigger('change');
    $('#update_price_with_qty').change(function() {
      if (this.checked) {
        $('#replace_variable_price').prop('checked', true);
      }
    });
	}, 0);

  setTimeout(function () {
    $('#process_product_strategy').change(function(e) {
      if (e.target.value === "after") {
        $('#process_product_strategy_after_use_price').closest('tr').show()
      } else {
        $('#process_product_strategy_after_use_price').closest('tr').hide()
      }
    }).trigger('change');
  }, 0);

} );
