/* global wp_mail_smtp_logs */
'use strict';

var WPMailSMTP = window.WPMailSMTP || {};
WPMailSMTP.Admin = WPMailSMTP.Admin || {};

/**
 * WP Mail SMTP Admin area Logs module.
 *
 * @since 1.5.0
 */
WPMailSMTP.Admin.Logs = WPMailSMTP.Admin.Logs || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.5.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine. DOM is not ready yet, use only to init something.
		 *
		 * @since 1.5.0
		 */
		init: function() {

			// Do that when DOM is ready.
			$( document ).ready( app.ready );
		},

		/**
		 * DOM is fully loaded.
		 *
		 * @since 1.5.0
		 */
		ready: function() {

			app.pageHolder = $( '.wp-mail-smtp-page-logs' );

			app.bindActions();

			app.pageHolder.trigger( 'WPMailSMTP.Admin.Logs.ready' );
		},

		/**
		 * Process all generic actions/events, mostly custom that were fired by our API.
		 *
		 * @since 1.5.0
		 */
		bindActions: function() {
			jQuery( '.wp-mail-smtp-page-logs-single' )
				.on( 'click', '.js-wp-mail-smtp-pro-logs-email-delete', app.single.processDelete )
				.on( 'click', '.js-wp-mail-smtp-pro-logs-toggle-extra-details', app.single.processExtraDetailsToggle )
				.on( 'click', '.js-wp-mail-smtp-pro-logs-close-extra-details', app.single.processExtraDetailsClose );
		},

		/**
		 * All the methods associated with the Single Email view.
		 *
		 * @since 1.5.0
		 */
		single: {

			/**
			 * Process single email deletion.
			 *
			 * @since 1.5.0
			 *
			 * @returns {boolean} Whether to remove or not.
			 */
			processDelete: function() {
				return confirm( wp_mail_smtp_logs.text_email_delete_sure );
			},

			/**
			 * Process the click on extra details header to open/close.
			 *
			 * @since 1.5.0
			 *
			 * @param {object} event jQuery event.
			 */
			processExtraDetailsToggle: function( event ) {
				var $btn = jQuery( event.target );

				if ( $btn.hasClass( 'open' ) ) {
					$btn.siblings( '.email-header-details' ).slideUp( 'fast', function() {
						$btn.removeClass( 'open' );
						$btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-up' ).addClass( 'dashicons-arrow-down' );
					} );
				} else {
					$btn.siblings( '.email-header-details' ).slideDown( 'fast', function() {
						$btn.addClass( 'open' );
						$btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-down' ).addClass( 'dashicons-arrow-up' );
					} );
				}
			},

			/**
			 * Process the click on close details button.
			 *
			 * @since 1.5.0
			 *
			 * @param {object} event jQuery event.
			 */
			processExtraDetailsClose: function( event ) {
				jQuery( event.target ).parents( '.email-extra-details' ).find( 'h2.open' ).click();
			}
		},

		/**
		 * All the methods associated with the Archive view (list of email log entries).
		 *
		 * @since 1.5.0
		 */
		archive: {}
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPMailSMTP.Admin.Logs.init();
