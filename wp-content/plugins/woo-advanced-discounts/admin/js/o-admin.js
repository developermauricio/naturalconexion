(function ($) {
	'use strict';

	$( document ).ready(
		function () {
			var o_uids = {};
			$( ".TabbedPanels" ).each(
				function ()
				{
						var defaultTab = 0;
						new Spry.Widget.TabbedPanels( $( this ).attr( "id" ), {defaultTab: defaultTab} );
				}
			);

			window.check_the_max_input_vars = function () {
				var all_champs_form      = $( "form#post input[type=text], form#post input[type=hidden], form#post input[type=button], form#post input[type=checkbox]:checked, form#post input[type=radio]:checked, form#post select, form#post input[type=date], form#post input[type=url], form#post input[type=time], form#post input[type=email], form#post input[type=number], form#post input[type=tel], form#post input[type=search], form#post input[type=image], form#post input[type=reset], form#post input[type=mot-cle], form#post textarea, form#post input[type=password] " ).length;
				var msg                  = o_max_input_msg.replace( "{nb}", all_champs_form );
				var message_admin_notice = "<div class='vpc-error error'><p>" + msg + "</p></div>";
				if (all_champs_form >= o_max_input_vars) {
					if ($( ".vpc-error" ).length) {
						$( ".vpc-error" ).html( "<p>" + msg + "</p>" );
					} else {
						$( '#wpbody-content, #publishing-action' ).prepend( message_admin_notice );
					}
					$( ".vpc-error" ).show();
					$( "#publishing-action #publish, #save-action #save-post" ).prop( 'disabled', true );

					// $('#normal-sortables').html(message_admin_notice);
					// $('#normal-sortables').show();
					// $('#publishing-action').find('div.vpc-error').remove();
					// $('#publishing-action').prepend(message_admin_notice);
					// $('#publishing-action').find('div.error').show();
				} else {

					$( ".vpc-error" ).hide();
					$( "#publishing-action #publish, #save-action #save-post" ).prop( 'disabled', false );

				}
			}

			function get_tables_hierarchy(raw_tpl, element)
			{
				var raw_tpl_tmp = raw_tpl;
				// console.log(raw_tpl_tmp);
				var regExp  = /{(.*?)}/g;
				var matches = raw_tpl_tmp.match( regExp );// regExp.exec(raw_tpl_tmp);
				// console.log(matches);
				// Attention on doit trouver un moyen d'identifier tous les éléments de la même ligne afin de remplacer leurs index correctement
				var count = (raw_tpl.match( regExp ) || []).length;

				// Loop through all parents repeatable fields rows
				if (count > 0) {
					var table_hierarchy = element.parents( ".o-rf-row" );
					$.each(
						table_hierarchy,
						function (i, e) {
							// console.log(matches[0]);
							var re        = new RegExp( matches[0], 'g' );
							var row_index = $( e ).index();
							raw_tpl_tmp   = raw_tpl_tmp.replace( re, row_index );
							matches.shift();
							// raw_tpl_tmp=raw_tpl_tmp.replace(regExp, row_index);
						}
					);

				}
				// The last or unique index in the template is the number of rows in the table
				var table_body    = element.siblings( "table.repeatable-fields-table" ).children( "tbody" ).first();
				var new_key_index = table_body.children( "tr" ).length;
				var re            = new RegExp( matches[0], 'g' );
				raw_tpl_tmp       = raw_tpl_tmp.replace( re, new_key_index );
				return raw_tpl_tmp;
			}

			$( document ).on(
				"click",
				".add-rf-row",
				function (e)
				{
						setTimeout( check_the_max_input_vars, 200 );
						var table_body = $( this ).siblings( "table" ).find( "tbody" ).first();
						var tpl_id     = $( this ).data( "tpl" );
						var raw_tpl    = o_rows_tpl[tpl_id];
						var tpl1       = get_tables_hierarchy( raw_tpl, $( this ) );
						table_body.append( tpl1 );

						// Makes sure the newly added rows uses unique modals popups
						// otherwise the click on two different options buttons may open the same popup
						var modal_ids = table_body.children( ".o-rf-row" ).last().find( "a.o-modal-trigger" );
					if (modal_ids.length) {
						$.each(
							modal_ids,
							function (i, e) {
								var modal_id     = $( this ).data( "modalid" );
								var new_modal_id = o_uniqid( "o-modal-" );
								$( this ).attr( "data-target", "#" + new_modal_id );
								$( "#" + modal_id ).attr( "id", new_modal_id );
							}
						);
					}
				}
			);

			$( document ).on(
				"click",
				".remove-rf-row",
				function (e)
				{
						setTimeout( check_the_max_input_vars, 200 );
						$( this ).parent().parent().remove();
				}
			);

			if ($( ".add-rf-row" ).length) {
				setTimeout( check_the_max_input_vars, 200 );
			}

			$( document ).on(
				"click",
				".o-add-media",
				function (e) {
					e.preventDefault();
					var trigger  = $( this );
					var uploader = wp.media(
						{
							title: 'Please set the picture',
							button: {
								text: "Select picture(s)"
							},
							multiple: false
						}
					)
						.on(
							'select',
							function () {
								var selection = uploader.state().get( 'selection' );
								selection.map(
									function (attachment) {
										attachment           = attachment.toJSON();
										var url_without_root = attachment.url.replace( home_url, "" );
										trigger.parent().find( "input[type=hidden]" ).val( url_without_root );
										trigger.parent().find( ".media-preview" ).html( "<img src='" + attachment.url + "'>" );
										trigger.parent().find( ".media-name" ).html( attachment.filename );
										if (trigger.parent().hasClass( "trigger-change" )) {
											trigger.parent().find( "input[type=hidden]" ).trigger( "propertychange" );
										}
									}
								);
							}
						)
						.open();
				}
			);

			$( document ).on(
				"click",
				".o-remove-media",
				function (e) {
					e.preventDefault();
					$( this ).parent().find( ".media-preview" ).html( "" );
					$( this ).parent().find( "input[type=hidden]" ).val( "" );
					$( this ).parent().find( ".media-name" ).html( "" );
					if ($( this ).parent().hasClass( "trigger-change" )) {
						$( this ).parent().find( "input[type=hidden]" ).trigger( "propertychange" );
					}
				}
			);

			// Modal resize
			// $('.o-modal-trigger').click(function() {
			// console.log("yes");
			// var TB_WIDTH = 100,
			// TB_HEIGHT = 100; // set the new width and height dimensions here..
			// $("#TB_window").animate({
			// marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
			// width: TB_WIDTH + 'px',
			// height: TB_HEIGHT + 'px',
			// marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px'
			// });
			//
			// $("#TB_window").css("background-color", "red");
			// });

			load_color_picker();
			function load_color_picker(){
				$( '.o-color' ).each(
					function(index,element)
					{
							var e             = $( this );
							var initial_color = e.val();
							e.css( "border-left", "35px solid " + initial_color );
							$( this ).parent().find( '.o-color-btn' ).ColorPicker(
								{
									color: initial_color,
									onShow: function (colpkr) {
										$( colpkr ).fadeIn( 500 );
										return false;
									},
									onChange: function (hsb, hex, rgb) {
										e.css( "border-left", "35px solid #" + hex );
										e.val( "#" + hex );
										e.trigger( "input" );
									}
								}
							);
					}
				);
			}

			$( ".o-google-font-selector" ).each(
				function ()
				{
						$( this ).select2( {allowClear: true} );
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



function o_uniqid(prefix, more_entropy) {
	// discuss at: http://phpjs.org/functions/uniqid/
	// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// revised by: Kankrelune (http://www.webfaktory.info/)
	// note: Uses an internal counter (in php_js global) to avoid collision
	// test: skip
	// example 1: uniqid();
	// returns 1: 'a30285b160c14'
	// example 2: uniqid('foo');
	// returns 2: 'fooa30285b1cd361'
	// example 3: uniqid('bar', true);
	// returns 3: 'bara20285b23dfd1.31879087'

	if (typeof prefix === 'undefined') {
		prefix = '';
	}

	var retId;
	var formatSeed = function (seed, reqWidth) {
		seed = parseInt( seed, 10 )
				.toString( 16 ); // to hex str
		if (reqWidth < seed.length) {
			// so long we split
			return seed.slice( seed.length - reqWidth );
		}
		if (reqWidth > seed.length) {
			// so short we pad
			return Array( 1 + (reqWidth - seed.length) )
					.join( '0' ) + seed;
		}
		return seed;
	};

	// BEGIN REDUNDANT
	if ( ! o_uids) {
		var o_uids = {};
	}
	// END REDUNDANT
	if ( ! o_uids.uniqidSeed) {
		// init seed with big random int
		o_uids.uniqidSeed = Math.floor( Math.random() * 0x75bcd15 );
	}
	o_uids.uniqidSeed++;

	// start with prefix, add current milliseconds hex string
	retId  = prefix;
	retId += formatSeed(
		parseInt(
			new Date()
			.getTime() / 1000,
			10
		),
		8
	);
	// add seed hex string
	retId += formatSeed( o_uids.uniqidSeed, 5 );
	if (more_entropy) {
		// for more entropy we add a float lower to 10
		retId += (Math.random() * 10)
				.toFixed( 8 )
				.toString();
	}

	return retId;
}
