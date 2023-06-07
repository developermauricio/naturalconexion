(function ($) {
	'use strict';

	$( document ).ready(
		function ()
		{
				$( "[data-tooltip-title]" ).tooltip();
				$( 'body' ).on(
					'change',
					'input[name="payment_method"], #billing_country, #shipping_country, #shipping_state, #billing_state',
					function() {
						setTimeout(
							function(){
								$( 'body' ).trigger( 'update_checkout' );
							},
							2000
						);
					}
				);

				$( ".single_variation_wrap" ).on(
					"show_variation",
					function ( event, variation ) {
						// Fired when the user selects all the required dropdowns / attributes
						// and a final variation is selected / shown
						var variation_id = $( "input[name='variation_id']" ).val();
						if (variation_id) {
							$( ".wad-qty-pricing-table" ).hide();
							$( ".wad-qty-pricing-table[data-id='" + variation_id + "']" ).show();
						}
					}
				);
		}
	);

})( jQuery );
