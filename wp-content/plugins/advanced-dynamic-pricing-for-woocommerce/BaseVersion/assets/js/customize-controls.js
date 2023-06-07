/* global customizer_info */

(function () {
	"use strict";
	(function ( wp, $ ) {
		$(function() {
			wp.customize.bind( 'ready', function() {
				$( '.customize-control label.wdp-control-icon input' ).on( 'change', function ( e ) {
					var $btn, toggle;
					$btn = $( e.target ).closest( '.font-style-button' );
					if ( 'radio' === e.target.type ) {
						$btn.siblings().removeClass( 'active' );
					}
					toggle = $( e.target ).is( ':checked' ) ? 'addClass' : 'removeClass';
					return $btn[toggle]( 'active' );
				} ).on( 'click', function ( e ) {
					var $btn;
					if ( 'radio' === e.target.type ) {
						$btn = $( e.target ).closest( '.font-style-button' );
						if ( $btn.hasClass( 'active' ) ) {
							$( e.target ).removeAttr( 'checked' );
							return $btn.removeClass( 'active' ).siblings( '.font-style-text-align-empty' ).prop(
								'checked', true ).change();
						}
					}
				} );
			});
		});


	})( window.wp, jQuery );
}).call( this );