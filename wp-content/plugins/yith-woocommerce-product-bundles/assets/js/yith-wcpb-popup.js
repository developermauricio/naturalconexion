( function ( $, window, document ) {
	$.fn.yith_wcpb_popup = function ( options ) {
		var self = {};

		self.overlay = null;
		self.popup   = $( this );

		self.opts = {};

		var defaults = {
			popup_class    : 'yith-wcpb-popup',
			overlay_class  : 'yith-wcpb-overlay',
			close_btn_class: 'yith-wcpb-popup-close',
			ajax_container : 'yith-wcpb-popup-ajax-container',
			url            : '',
			ajax_data      : {},
			ajax_complete  : function () {
			},
			ajax_success   : function () {
			},
			block_params   : {
				message        : '',
				blockMsgClass  : 'yith-wcpb-popup-loader',
				css            : {
					border    : 'none',
					background: 'transparent'
				},
				overlayCSS     : {
					background: '#fff',
					opacity   : 0.7
				},
				ignoreIfBlocked: true
			},
			loader         : '<span class="yith-wcpb-popup-loader"></span>'
		};

		self.init = function () {

			self.opts = $.extend( {}, defaults, options );
			if ( options === 'close' ) {
				_close();
				return;
			}

			_createOverlay();
			_getAjaxContent();

			_initEvents();
		};

		var _createOverlay  = function () {
				// add_overlay if not exist
				if ( $( document ).find( '.' + self.opts.overlay_class ).length > 0 ) {
					self.overlay = $( document ).find( '.' + self.opts.overlay_class ).first();
				} else {
					self.overlay = $( '<div />' ).addClass( self.opts.overlay_class );
					$( document.body ).append( self.overlay );
				}
			},
			_getAjaxContent = function () {
				self.popup         = $( '<div />' ).addClass( self.opts.popup_class );
				var closeBtn       = $( '<span />' ).addClass( self.opts.close_btn_class + ' dashicons dashicons-no-alt' ),
					popupContainer = $( '<div />' ).addClass( self.opts.ajax_container );

				self.popup.append( popupContainer );

				self.overlay.html( self.opts.loader );

				$.ajax( {
							data    : self.opts.ajax_data,
							url     : self.opts.url,
							success : function ( data ) {
								$( self.overlay ).append( self.popup );
								popupContainer.html( data );

								self.opts.ajax_success();
							},
							complete: function () {
								self.popup.append( closeBtn );

								self.opts.ajax_complete();
							}
						} );
			},
			_initEvents     = function () {
				$( document ).on( 'click', '.' + self.opts.overlay_class, _close );
				$( document ).on( 'click', '.' + self.opts.close_btn_class, _close );
				$( document ).on( 'click', '.' + self.opts.popup_class, _preventClosing );

				$( document ).on( 'keydown', function ( event ) {
					if ( event.keyCode === 27 ) {
						_close();
					}
				} );
			},
			_preventClosing = function ( e ) {
				e.stopPropagation();
			},
			_close          = function () {
				self.overlay.remove();
			};


		self.init();
		return self.popup;
	};

} )( jQuery, window, document );