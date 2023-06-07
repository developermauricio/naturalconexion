/**
 * Contains the wpc_postboxes logic, opening and closing wpc_postboxes, reordering and saving
 * the state and ordering to the database.
 *
 * @summary Contains wpc_postboxes logic
 *
 * @requires jQuery
 */

/* global ajaxurl, postBoxL10n */

/**
 * This object contains all function to handle the behaviour of the post boxes. The post boxes are the boxes you see
 * around the content on the edit page.
 *
 *
 * @namespace wpc_postboxes
 *
 * @type {Object}
 */
var wpc_postboxes;

(function($) {
	var $document = $( document );

	wpc_postboxes = {

		/**
		 * @summary Handles a click on either the postbox heading or the postbox open/close icon.
		 *
		 * Opens or closes the postbox. Expects `this` to equal the clicked element.
		 *
		 * @since 4.4.0
		 * @memberof wpc_postboxes
		 * @fires wpc_postboxes#postbox-toggled
		 *
		 * @returns {void}
		 */
		handle_click : function () {
			var $el = $( this ),
				p = $el.parent( '.postbox' ),
				id = p.attr( 'id' ),
				ariaExpandedValue;

			if ( 'dashboard_browser_nag' === id ) {
				return;
			}

			p.toggleClass( 'closed' );

			ariaExpandedValue = ! p.hasClass( 'closed' );

			if (ariaExpandedValue) {
				wpc_postboxes._on_expand($el.closest('.postbox'));
			} else {
				wpc_postboxes._on_close($el.closest('.postbox'));
			}

			if ( $el.hasClass( 'handlediv' ) ) {
				// The handle button was clicked.
				$el.attr( 'aria-expanded', ariaExpandedValue );
			} else {
				// The handle heading was clicked.
				$el.closest( '.postbox' ).find( 'button.handlediv' )
					.attr( 'aria-expanded', ariaExpandedValue );
			}
		},

		/**
		 * Adds event handlers to all wpc_postboxes and screen option on the current page.
		 *
		 * @since 2.7.0
		 * @memberof wpc_postboxes
		 *
		 * @param {string} page The page we are currently on.
		 * @param {Object} [args]
		 * @returns {void}
		 */
		add_postbox_toggles : function ($container) {
			this.init();

			$container.on( 'click.wpc_postboxes', '.postbox .hndle, .postbox .handlediv', this.handle_click );

			/**
			 * @since 2.7.0
			 */
			$container.on('click', '.postbox .hndle a', function(e) {
				e.stopPropagation();
			});

			/**
			 * @summary Hides a postbox.
			 *
			 * Event handler for the postbox dismiss button. After clicking the button
			 * the postbox will be hidden.
			 *
			 * @since 3.2.0
			 *
			 * @returns {void}
			 */
			$container.on( 'click.wpc_postboxes', '.postbox a.dismiss', function( e ) {
				var hide_id = $(this).parents('.postbox').attr('id') + '-hide';
				e.preventDefault();
				$( '#' + hide_id ).prop('checked', false).triggerHandler('click');
			});

			/**
			 * @summary Hides the postbox element
			 *
			 * Event handler for the screen options checkboxes. When a checkbox is
			 * clicked this function will hide or show the relevant wpc_postboxes.
			 *
			 * @since 2.7.0
			 * @fires wpc_postboxes#postbox-toggled
			 *
			 * @returns {void}
			 */
			$('.hide-postbox-tog').bind('click.wpc_postboxes', function() {
				var $el = $(this),
					boxId = $el.val(),
					$postbox = $( '#' + boxId );

				if ( $el.prop( 'checked' ) ) {
					$postbox.show();
				} else {
					$postbox.hide();
				}

				wpc_postboxes._mark_area();
			});
		},

		/**
		 * @summary Initializes all the wpc_postboxes, mainly their sortable behaviour.
		 *
		 * @since 2.7.0
		 * @memberof wpc_postboxes
		 *
		 * @param {string} page The page we are currently on.
		 * @param {Object} [args={}] The arguments for the postbox initializer.
		 *
		 * @returns {void}
		 */
		init : function() {
			var isMobile = $( document.body ).hasClass( 'mobile' ),
				$handleButtons = $( '.postbox .handlediv' );

			$('#wpbody-content').css('overflow','hidden');
			$('.meta-box-sortables .sortables-container').sortable({
				containment: 'parent',
				items: '.postbox',
				cursor: 'move',
				axis:   'y',
				opacity: 0.65,
				placeholder: 'sortable-placeholder',
				handle: '.hndle',
				delay: ( isMobile ? 200 : 0 ),
				distance: 2,
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				update: function (event, ui) { wpc_postboxes._on_reorder(event, ui) }
			});

			if ( isMobile ) {
				$(document.body).bind('orientationchange.wpc_postboxes', function(){ wpc_postboxes._pb_change(); });
				this._pb_change();
			}

			this._mark_area();

			// Set the handle buttons `aria-expanded` attribute initial value on page load.
			$handleButtons.each( function () {
				var $el = $( this );
				var expandedValue = ! $el.parent( '.postbox' ).hasClass( 'closed' );

				if (expandedValue) {
					var $expand_el = $el.parent('.postbox');
					wpc_postboxes._on_expand($expand_el);
				} else {
					wpc_postboxes._on_close($expand_el);
				}

				$el.attr( 'aria-expanded', expandedValue );
			});
		},

		/**
		 * @summary Marks empty postbox areas.
		 *
		 * Adds a message to empty sortable areas on the dashboard page. Also adds a
		 * border around the side area on the post edit screen if there are no wpc_postboxes
		 * present.
		 *
		 * @since 3.3.0
		 * @memberof wpc_postboxes
		 * @access private
		 *
		 * @returns {void}
		 */
		_mark_area : function() {
			var visible = $('div.postbox:visible').length, side = $('#post-body #side-sortables');

			$( '#dashboard-widgets .meta-box-sortables:visible' ).each( function() {
				var t = $(this);

				if ( visible == 1 || t.children('.postbox:visible').length ) {
					t.removeClass('empty-container');
				}
				else {
					t.addClass('empty-container');
					t.attr('data-emptyString', postBoxL10n.postBoxEmptyString);
				}
			});

			if ( side.length ) {
				if ( side.children('.postbox:visible').length )
					side.removeClass('empty-container');
				else if ( $('#postbox-container-1').css('width') == '280px' )
					side.addClass('empty-container');
			}
		},

		/**
		 * @summary Changes the amount of columns on the post edit page.
		 *
		 * @since 3.3.0
		 * @memberof wpc_postboxes
		 * @fires wpc_postboxes#wpc_postboxes-columnchange
		 * @access private
		 *
		 * @param {number} n The amount of columns to divide the post edit page in.
		 * @returns {void}
		 */
		_pb_edit : function(n) {
			var el = $('.metabox-holder').get(0);

			if ( el ) {
				el.className = el.className.replace(/columns-\d+/, 'columns-' + n);
			}

			/**
			 * Fires when the amount of columns on the post edit page has been changed.
			 *
			 * @since 4.0.0
			 * @event wpc_postboxes#wpc_postboxes-columnchange
			 */
			$( document ).trigger( 'wpc_postboxes-columnchange' );
		},

		/**
		 * @summary Changes the amount of columns the wpc_postboxes are in based on the
		 *          current orientation of the browser.
		 *
		 * @since 3.3.0
		 * @memberof wpc_postboxes
		 * @access private
		 *
		 * @returns {void}
		 */
		_pb_change : function() {
			var check = $( 'label.columns-prefs-1 input[type="radio"]' );

			switch ( window.orientation ) {
				case 90:
				case -90:
					if ( !check.length || !check.is(':checked') )
						this._pb_edit(2);
					break;
				case 0:
				case 180:
					if ( $('#poststuff').length ) {
						this._pb_edit(1);
					} else {
						if ( !check.length || !check.is(':checked') )
							this._pb_edit(2);
					}
					break;
			}
		},

		_on_reorder: function() {},
		_on_expand: function($el) {},
		_on_close: function($el) {},
	};

}(jQuery));
