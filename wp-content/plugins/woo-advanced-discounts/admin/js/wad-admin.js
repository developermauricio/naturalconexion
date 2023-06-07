(function ($) {
	'use strict';

	$( document ).ready(
		function () {
			var wad_tax_query_selected_option = new Array();
			if (typeof lang_wordpress != "undefined") {
				$.datetimepicker.setLocale( lang_wordpress );
			}
			$( ".o-date" ).each(
				function ()
				{
						var element = $( this );
						element.datetimepicker(
							{
							}
						);
				}
			);

			function display_proper_rules_tab()
			{
				var rules_type = $( 'input[type=radio][name="o-discount[rules-type]"]:checked' ).attr( 'value' );

				if (rules_type == 'intervals') {
					$( "#steps_rules" ).parent().parent().hide();
					$( "#intervals_rules" ).parent().parent().show();

				} else {
					$( "#intervals_rules" ).parent().parent().hide();
					$( "#steps_rules" ).parent().parent().show();

				}
			}

			display_proper_rules_tab();

			$( document ).on(
				"change",
				"input[type=radio][name='o-discount[rules-type]']",
				function (e)
				{
						display_proper_rules_tab();
				}
			);

			$( ".TabbedPanels" ).each(
				function ()
				{
						var defaultTab = 0;
						new Spry.Widget.TabbedPanels( $( this ).attr( "id" ), {defaultTab: defaultTab} );
				}
			);

			$( document ).on(
				"click",
				".wad-add-rule",
				function (e)
				{
						var new_rule_index = $( ".wad-rules-table tr" ).length;
						var group_index    = $( this ).data( "group" );
						var raw_tpl        = $( "#wad-rule-tpl" ).val();
						var tpl1           = raw_tpl.replace( /{rule-group}/g, group_index );
						var tpl2           = tpl1.replace( /{rule-index}/g, new_rule_index );
						$( this ).parents( ".wad-rules-table" ).find( "tbody" ).append( tpl2 );
				}
			);

			$( document ).on(
				"click",
				".wad-add-group",
				function (e)
				{
						var new_rule_index = 0;
						var group_index    = $( ".wad-rules-table" ).length;
						var raw_tpl        = $( "#wad-first-rule-tpl" ).val();
						var tpl1           = raw_tpl.replace( /{rule-group}/g, group_index );
						var tpl2           = tpl1.replace( /{rule-index}/g, new_rule_index );
						var html           = '<table class="wad-rules-table widefat"><tbody>' + tpl2 + '</tbody></table>';
						$( ".wad-rules-table-container" ).append( html );
				}
			);

			$( document ).on(
				"click",
				".wad-remove-rule",
				function (e)
				{
						// If this is the last rule in the group, we remove the entire group
					if ($( this ).parent().parent().parent().find( "tr" ).length == 1) {
						$( this ).parent().parent().parent().parent().remove();
					} else {
						$( this ).parent().parent().remove();
					}

				}
			);

			$( document ).on(
				"change",
				".wad-pricing-group-param",
				function (e)
				{
						var selected_value = $( this ).val();
						var raw_tpl        = wad_values_matches[selected_value];
						var group_index    = $( this ).data( "group" );
						var new_rule_index = $( this ).data( "rule" );

						var tpl1 = raw_tpl.replace( /{rule-group}/g, group_index );
						var tpl2 = tpl1.replace( /{rule-index}/g, new_rule_index );
						$( this ).parent().parent().find( "td.value" ).html( tpl2 );

						var raw_tpl_op = wad_operators_matches[selected_value];

						tpl1 = raw_tpl_op.replace( /{rule-group}/g, group_index );
						tpl2 = tpl1.replace( /{rule-index}/g, new_rule_index );
						$( this ).parent().parent().find( "td.operator" ).html( tpl2 );
				}
			);


			// We make sure the products list is required when it's visible when the page is loaded
			if ($( "#products-list" ).is( ':visible' )) {
				$( "#products-list" ).prop( 'required', true );
			}
			if ($( "#percentage-amount" ).is( ':visible' )) {
				$( "#percentage-amount" ).prop( 'required', true );
			}

			$( document ).on(
				"change",
				".discount-action",
				function (e)
				{
						var selected_value = $( this ).val();
					if (selected_value == "free-gift") {
						$( ".percentage-row, .product-action-row" ).hide();
						$( ".free-gift-row" ).show();
						$( "#products-list" ).prop( 'required', false );
					} else if (selected_value == "percentage-off-pprice" || selected_value == "fixed-amount-off-pprice" || selected_value == "fixed-pprice") {// Product based actions
						$( ".free-gift-row" ).hide();
						$( ".percentage-row, .product-action-row" ).show();
						$( "#products-list" ).prop( 'required', true );
					} else // Order based actions
						{
						$( ".free-gift-row, .product-action-row" ).hide();
						$( ".percentage-row" ).show();
						$( "#products-list" ).prop( 'required', false );
					}
				}
			);

			$( ".discount-action" ).trigger( "change" );

			$( document ).on(
				"change",
				".o-list-extraction-type",
				function (e)
				{
						var selected_value = $( this ).val();
					if (selected_value == "by-id") {
						$( ".extract-by-id-row" ).show();
						$( ".extract-by-custom-request-row" ).hide();
					} else {
						$( ".extract-by-id-row" ).hide();
						$( ".extract-by-custom-request-row" ).show();
					}
				}
			);


			var labels  = $( 'td:eq(2)','#intervals_rules' );
			var labels2 = $( 'td:eq(1)','#steps_rules' );
			labels.text( 'Percentage' );
			$( 'input:radio[name="o-discount[type]"]' ).change(
				function(){
					if ($( this ).is( ':checked' )) {
						var parenti = $( this ).parent().text();
						var parents = $( 'input:radio[name="o-discount[type]"]:checked' ).parent().text();
						labels.text( parenti );
						labels2.text( parents );
					}
				}
			);
			labels.text( $( 'input:radio[name="o-discount[type]"]:checked' ).parent().text() );
			var labels2 = $( 'td:eq(1)','#steps_rules' );
			$( 'input:radio[name="o-discount[rules-type]"]' ).change(
				function(){
					if ($( this ).is( ':checked' )) {
						var parents2 = $( 'input:radio[name="o-discount[type]"]:checked' ).parent().text();
						labels2.text( parents2 );
					}
				}
			);

		}
	);

		   /*
		 *
		 * Newsletter
		 */
		$( document ).on(
			"click",
			".wad-dismiss-newsletters",
			function () {
				$.post(
					ajaxurl,
					{
						action: "wad_hide_notice",
					},
					function (data) {
						if (data === "ok") {
							$( '#subscription-notice' ).hide();
						}
					}
				)
					.fail(
						function (xhr, status, error) {
							alert( error );
						}
					);
			}
		);

		$( document ).on(
			"click",
			".wad-dismiss-notice",
			function () {
				$.post(
					ajaxurl,
					{
						action: "wad_hide_notice",
					},
					function (data) {
						if (data === "ok") {
							$( '.wad-review' ).hide();
						}
					}
				)
					.fail(
						function (xhr, status, error) {
							alert( error );
						}
					);
			}
		);

		$( document ).on(
			"click",
			"#wad-subscribe",
			function () {
				$( "#wad-subscribe-loader" ).show();
				$( "#wad-subscribe" ).attr( "disabled", true );
				$( "#wad-subscribe" ).addClass( 'disabled' );
				if ( ! $( '#o_user_email' ).val()) {
					alert( 'No email found. Please add an email address to subscribe.' );
					$( "#wad-subscribe-loader" ).hide();
					$( "#wad-subscribe" ).attr( "disabled", false );
					$( "#wad-subscribe" ).removeClass( 'disabled' );
				} else {
					var email = $( '#o_user_email' ).val();
					$.post(
						ajaxurl,
						{
							action: "wad_subscribe",
							email: email
							},
						function (data) {
							if (data == "true") {
								$( "#wad-subscribe-loader" ).hide();
								$( '#subscription-notice' ).hide();
								$( '#subscription-success-notice' ).show();
							} else {
								$( "#wad-subscribe-loader" ).hide();
								$( "#wad-subscribe" ).attr( "disabled", false );
								$( "#wad-subscribe" ).removeClass( 'disabled' );
								alert( data );
							}
						}
					)
						.fail(
							function (xhr, status, error) {
								$( "#wad-subscribe-loader" ).hide();
								$( "#wad-subscribe" ).attr( "disabled", false );
								$( "#wad-subscribe" ).removeClass( 'disabled' );
								alert( error );
							}
						);
				}

			}
		);

		$( document ).on(
			'click',
			"#submit-a-review",
			function(e){
				$.post(
					ajaxurl,
					{
						action : "wad_submit_a_review"
					},
					function(data){
						if (data === "ok") {
							$( 'div.wad-review' ).hide();
						}
					}
				);
			}
		);

})( jQuery );

function is_json(data)
{
	if (/^[\],:{}\s]*$/.test(
		data.replace( /\\["\\\/bfnrtu] /g, '@' ).
			replace( /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']' ).
			replace( /(?:^|:|,)(?:\s*\[)+/g, '' )
	)) {
		return true;
	} else {
		return false;
	}
}
