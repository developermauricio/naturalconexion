(
	function ( wp, $ ) {

		$.each( wdp_customize_preview.css_controls, function ( control_id, control_data ) {
			wp.customize( control_id, function ( value_control ) {
				value_control.bind( function ( newval ) {
					if ( control_data.css_option_value ) {
						if ( newval ) {
							$( control_data.selector ).css( control_data.css_option_name,
								control_data.css_option_value );
						} else {
							$( control_data.selector ).css( control_data.css_option_name, "" );
						}
					} else {
						$( control_data.selector ).css( control_data.css_option_name, newval )
					}
				} );
			} )
		} );

	}
)( window.wp, jQuery );