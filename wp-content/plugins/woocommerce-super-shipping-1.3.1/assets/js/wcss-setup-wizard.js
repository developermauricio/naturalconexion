jQuery(document).ready(function($) {

	$( "#start-migration" ).click( function ( e ) {

		e.preventDefault();
		if ( $( '#create_backup' ).prop( 'checked' ) == false ) {
			
			$( '#create_backup' ).val( 'no' );
		}
		// Customize blockUI style
		$.blockUI.defaults.css = { 
            padding: 0,
            margin: 0,
            width: '30%',
            top: '40%',
            left: '35%',
            textAlign: 'center',
            cursor: 'wait'
		};
		$( '.wc-setup-content' ).block( {message:""} );

		$.ajax({
			type: "POST",
			url: wss_setup_param.url,
			cache: false,
			data: {
				action: wss_setup_param.action,
				security: wss_setup_param.start_migration_wizard_nonce,
				create_backup: $( '#create_backup' ).val(),
			},
	    	success: function (output) {
				$( '.wc-setup-content' ).unblock()
				$( '.migration-content' ).hide();
				$( '.wc-setup-content' ).append( '<div class="success-message">\
					<p>'+ wss_setup_param.success_message +'</p>\
					<p style="text-align:center;"><a href="'+ wss_setup_param.shipping_admin_url +'" class="button-primary button button-large button-next">'+ wss_setup_param.check_results_button +'</a></p>\
				</div>' );
			},
			error: function () {
				$( '.wc-setup-content' ).unblock()
				$( '.migration-content' ).hide();
				$( '.wc-setup-content' ).append( '<div class="error-message"><p>'+ wss_setup_param.error_message +'</p><p style="text-align:center;"><a href="'+ wss_setup_param.shipping_admin_url +'" class="button-primary button button-large button-next">'+ wss_setup_param.exit_button +'</a></p></div>' )
			}
		});
	});

	$( '.migration-content .button' ).click( function (){

		pause_video( '.migration-content' );
	});

	$( '.installation-content-1 .button-next' ).click( function (){

		pause_video( '.installation-content-1' );
		$( '.installation-content-1' ).hide();
		$( '.installation-content-2' ).show();
		$( 'ol.wc-setup-steps li:nth-child(1)' ).removeClass( 'active' );
		$( 'ol.wc-setup-steps li:nth-child(2)' ).addClass( 'active' );
	});

	$( '.installation-content-2 .button-prev' ).click( function (){

		pause_video( '.installation-content-2' );
		$( '.installation-content-2' ).hide();
		$( '.installation-content-1' ).show();
		$( 'ol.wc-setup-steps li:nth-child(2)' ).removeClass( 'active' );
		$( 'ol.wc-setup-steps li:nth-child(1)' ).addClass( 'active' );
	});

	$( '.installation-content-2 .button-next' ).click( function (){

		pause_video( '.installation-content-2' );
		$( '.installation-content-2' ).hide();
		$( '.installation-content-3' ).show();
		$( 'ol.wc-setup-steps li:nth-child(2)' ).removeClass( 'active' );
		$( 'ol.wc-setup-steps li:nth-child(3)' ).addClass( 'active' );
	});

	$( '.installation-content-3 .button-prev' ).click( function (){

		$( '.installation-content-3' ).hide();
		$( '.installation-content-2' ).show();
		$( 'ol.wc-setup-steps li:nth-child(3)' ).removeClass( 'active' );
		$( 'ol.wc-setup-steps li:nth-child(2)' ).addClass( 'active' );
	});

	// Pause the Vimeo video-tutorial when the user click on the next or prev button
	function pause_video( container_class ){

		var iframe = document.querySelector( container_class + ' iframe' );
		var player = new Vimeo.Player( iframe );
		player.pause();
	}
});