jQuery(function ($) {
    "use strict";
    
    var IconicFBT = function() {
        this.init = function() {
            $(document).on("change", "#iconic-wsb-fbt-discount-type", this.selectChangeHandler );
            $(document).on("change", "#iconic-wsb-fbt-discount-value", this.valueChangeHandler );
            this.selectChangeHandler();
        };
        
        /**
         * Handles Discount Type dropdown. Adds validation for min, max and negetive values.  
         */
        this.selectChangeHandler = function() {
            var type  = $( "#iconic-wsb-fbt-discount-type" ).val();
            var val   = $( "#iconic-wsb-fbt-discount-value" ).val();
            if ( type === "percentage" ) {
                $( "#iconic-wsb-fbt-discount-value" ).prop( "min" , 0 ); 
                $( "#iconic-wsb-fbt-discount-value" ).prop( "max" , 100 ); 
                if (val > 100 ) {
                    $( "#iconic-wsb-fbt-discount-value" ).val( 100 );
                }
            }
            else {
                $( "#iconic-wsb-fbt-discount-value" ).prop( "min", "" );
                $( "#iconic-wsb-fbt-discount-value" ).prop( "max", "" ); 
            }
        };
        
        /**
         * Handles changes in discount value. Ensure that the percentage value is not more than 100.
         */
        this.valueChangeHandler = function() {
            var type = $("#iconic-wsb-fbt-discount-type").val();
            if ( type === "percentage" &&  $( this ).val() > 100 ) {
                $( this ).val( 100 );
                alert( "Percentage discount cannot be more than 100" );
            }
            if ( $(this).val() < 0 ) {
                $(this).val(0);
                alert( "Discount cannot be negetive" );
            }
            
        };
        
    };
    
    $( document ).ready( function() {
        var iconicfbt = new IconicFBT();
        iconicfbt.init();
    } );

});
jQuery( function( $ ) {

	"use strict";

	if ( typeof iconic_wsb_admin_vars === 'undefined' || typeof  iconic_wsb_admin_vars.postId === 'undefined' ) {
		return;
	}

	// Uploading files
	var file_frame;
	var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
	var set_to_post_id = iconic_wsb_admin_vars.postId;
	var imagePreview = $( '[data-iconic-wsb-image-preview]' );
	var imageAttachmentId = $( '[data-iconic-wsb-image-attachment-id]' );

	$( document ).on( 'click', '[data-iconic-wsb-upload-image]', function( event ) {
		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {

			// Set the post ID to what we want
			file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
			// Open frame
			file_frame.open();
			return;
		} else {

			// Set the wp.media post id so the uploader grabs the ID we want when initialised
			wp.media.model.settings.post.id = set_to_post_id;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media( {
			title: 'Select a image to upload',
			button: {
				text: 'Use this image',
			},
			multiple: false	// Set to true to allow multiple files to be selected
		} );

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			var attachment = file_frame.state().get( 'selection' ).first().toJSON();

			imagePreview.attr( 'src', attachment.url );
			imageAttachmentId.val( attachment.id );

			// Restore the main post ID
			wp.media.model.settings.post.id = wp_media_post_id;
		} );

		// Finally, open the modal
		file_frame.open();
	} );

	// Restore the main ID when the add media button is pressed
	$( 'a.add_media' ).on( 'click', function() {
		wp.media.model.settings.post.id = wp_media_post_id;
	} );
} );
jQuery( function( $ ) {
	"use strict";

	if ( typeof iconic_wsb_admin_vars === 'undefined' || typeof  iconic_wsb_admin_vars.postId === 'undefined' ) {
		return;
	}

	/**
	 * init color picker
	 */
	$( '[data-iconic-wsb-color-picker]' ).wpColorPicker( {
		change: function( event, ui ) {
			var color = ui.color.toString();

			$( this ).val( color ).trigger( 'change' );
		}
	} );

	/**
	 * Toggle step 1 Display for additional control visibility
	 */
	$( document ).on( 'change', '[data-iconic-wsb-display-for--select]', function() {

		var $this = $( this );
		var $scope = $this.closest( '[data-iconic-wsb-display-for--scope]' );
		var $spoiler = $scope.find( '[data-iconic-wsb-display-for--spoiler]' );
		var $controls = $scope.find( '[data-iconic-wsb-display-for--control]' );

		if ( $this.val() === 'all' ) {
			$spoiler.addClass( 'iconic-wsb-hidden' );
			$controls.removeAttr( 'required' );
		} else {
			$spoiler.removeClass( 'iconic-wsb-hidden' );
			$controls.attr( 'required', 'required' );
		}

	} );

	/**
	 * Offer exclude from search
	 */
	$( document ).on( 'change', '[data-iconic-wsb-specific-products]', function() {
		var excludeFromSearchIds = $( this ).val();
		var select = $( '[data-iconic-wsb-offer-product]' );

		if ( excludeFromSearchIds && excludeFromSearchIds.length ) {
			select.data( 'exclude', excludeFromSearchIds.join( ',' ) );
		}
	} );

	/**
	 * Ajax get product price and setup discount validation
	 */
	$( document ).on( 'change', '[data-iconic-wsb-offer-product]', function() {

		var productId = $( this ).val();
		var discountInput = $( '[data-iconic-wsb-discount-value]' );
		var discountTypeSelect = $( '[data-iconic-wsb-discount-type]' );
		var discountType = discountTypeSelect.val();
		var imagePreview = $( '[data-iconic-wsb-image-preview]' );
		var imageIdInput = $( '[data-iconic-wsb-image-attachment-id]' );
		var $this = $( this );

		$.get( ajaxurl, {
			action: 'iconic_wsb_checkout_order_bump_calculate_price',
			product_id: productId,
		}, function( data ) {

			var scope = $this.closest( '[data-iconic-wsb-offer-scope]' );
			var discountTypeSelect = scope.find( '[data-iconic-wsb-discount-type]' );
			var discountValueInput = scope.find( '[data-iconic-wsb-discount-value]' );

			discountTypeSelect.attr( 'data-simple-max', data.sale_price );

			if ( discountType === 'simple' ) {
				discountValueInput.attr( 'max', data.sale_price );
			}

			if ( discountType === 'percentage' ) {
				discountValueInput.attr( 'max', discountTypeSelect.attr( 'data-percentage-max' ) );
			}

			// if product has image
			if ( data.image_id ) {
				imagePreview.attr( 'src', data.image_url );
				imageIdInput.val( data.image_id );
			}

			discountInput.trigger( 'input' );
		} );

	} );

	/**
	 * Update discount validation
	 */
	$( document ).on( 'change', '[data-iconic-wsb-discount-type]', function() {
		var $this = $( this );

		var scope = $this.closest( '[data-iconic-wsb-offer-scope]' );
		var discountValueInput = scope.find( '[data-iconic-wsb-discount-value]' );

		if ( $this.val() === 'percentage' ) {
			discountValueInput.attr( 'max', $this.attr( 'data-percentage-max' ) );

			if ( discountValueInput.val() > 99 ) {
				discountValueInput.val( 1 );
			}
		} else if ( $this.val() === 'simple' ) {
			discountValueInput.attr( 'max', $this.attr( 'data-simple-max' ) );
		}

		discountValueInput.trigger( 'input' );
	} );

	/**
	 * Update customize preview
	 */
	$( document ).on( 'change', '[data-iconic-wsb-discount-value],[data-iconic-wsb-discount-type],[data-iconic-wsb-offer-product]', function() {

		var discount = $( '[data-iconic-wsb-discount-value]' ).val();
		var discountType = $( '[data-iconic-wsb-discount-type]' ).val();
		var prdouctId = $( '[data-iconic-wsb-offer-product]' ).val();
		var $priceBox = $( '[data-iconic-wsb-setting-show-price--element]' );
		var regularPrice = $( '[data-iconic-wsb-product-customize-price--regular]' );

		$priceBox.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );

		$.get( ajaxurl, {
			action: 'iconic_wsb_checkout_order_bump_calculate_price',
			product_id: prdouctId,
			discount_type: discountType,
			discount: discount
		}, function( data ) {

			regularPrice.html( data.regular_price_html );
			$( '[data-iconic-wsb-product-customize-price--discount]' ).html( data.sale_price_html );

			if ( parseFloat( data.regular_price ) === parseFloat( data.sale_price ) ) {
				regularPrice.addClass( 'hidden' );
			} else {
				regularPrice.removeClass( 'hidden' );
			}

			$priceBox.unblock();
		} );
	} );

	/**
	 * Toggle image visibility
	 */
	$( document ).on( 'change', '[data-iconic-wsb-setting-show-image]', function() {
		var $imageBox = $( '[data-iconic-wsb-setting-show-image--element]' );

		if ( $( this ).val() === 'yes' ) {
			$imageBox.removeClass( 'iconic-wsb-hidden' );
		} else {
			$imageBox.addClass( 'iconic-wsb-hidden' );
		}
	} );

	/**
	 * Toggle price visibility
	 */
	$( document ).on( 'change', '[data-iconic-wsb-setting-show-price]', function() {
		var $priceBox = $( '[data-iconic-wsb-setting-show-price--element]' );

		if ( $( this ).val() === 'yes' ) {
			$priceBox.removeClass( 'iconic-wsb-hidden' );
		} else {
			$priceBox.addClass( 'iconic-wsb-hidden' );
		}
	} );

	/**
	 * Toggle border style
	 */
	$( document ).on( 'change', '[data-iconic-wsb-setting-border-style]', function() {
		var style = $( this ).val();

		$( '[data-iconic-wsb-setting-border-style--element]' ).css( {
			'border-style': style
		} );
	} );

	/**
	 * Handle setting color change
	 */
	$( document ).on( 'change', '[data-iconic-wsb-setting-border-color]', function() {
		var color = $( this ).val();

		$( '[data-iconic-wsb-setting-border-style--element]' ).css( {
			'border-color': color
		} );
	} );

	/**
	 * Handle setting highlight color change
	 */
	$( document ).on( 'change', '[data-iconic-wsb-setting-highlight-color]', function() {
		var color = $( this ).val();

		$( '[data-iconic-wsb-setting-highlight-color--element]' ).css( {
			'color': color
		} );
	} );

	/**
	 * Add benefit
	 */
	$( document ).on( 'click', '[data-add-benefit-button]', function( e ) {
		e.preventDefault();

		var benefitTemplate = $( '[data-benefit-template]' ).clone();

		benefitTemplate.removeAttr( 'data-benefit-template' );

		benefitTemplate.removeClass( 'iconic-wsb-hidden' );

		$( '[data-benefits-container]' ).append( benefitTemplate );
	} );

	/**
	 * Remove benefit
	 */
	$( document ).on( 'click', '[data-remove-benefit]', function() {
		var benefit = $( this ).closest( '[data-benefit]' );

		benefit.remove();
	} );

	/**
	 * Switch tabs
	 */
	$( document ).on( 'click', '.iconic-wsb-edit-page-nav__item-link', function( e ) {
		e.preventDefault();

		var $link = $( this ),
			$links = $( '.iconic-wsb-edit-page-nav__item-link' ),
			tab_id = $link.attr( 'href' ),
			$tabs = $( '.iconic-wsb-edit-page__step' ),
			$tab = $( tab_id );

		$links.removeClass( 'iconic-wsb-edit-page-nav__item-link--active' );
		$link.addClass( 'iconic-wsb-edit-page-nav__item-link--active' );

		$tabs.removeClass( 'iconic-wsb-edit-page__step--active' );
		$tab.addClass( 'iconic-wsb-edit-page__step--active' );
	} );
} );


jQuery( function( $ ) {

	"use strict";

	if ( typeof iconic_wsb_admin_vars === 'undefined' || typeof  iconic_wsb_admin_vars.posts === 'undefined' ) {
		return;
	}

	if( $( ".post_type_page" ).length === 0 ||  ( $( ".post_type_page" ).val() !== "at_checkout_ob" && $( ".post_type_page" ).val() !== "after_checkout_ob" ) ) {
		return;
	}
	
	$( '#the-list' ).sortable( {
		handle: ".iconic-wsb-sortable",
		axis: 'y',

		update: function( event, ui ) {
			var $table = $( '.wp-list-table' );

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					data: $( this ).sortable( 'serialize' ),
					posts: iconic_wsb_admin_vars.posts,
					action: 'iconic_wsb_handle_sorting_bump_checkout_product',
					post_type: iconic_wsb_admin_vars.post_type
				},
				success: function( data ) {
					iconic_wsb_admin_vars.posts = data.posts;
				},
				beforeSend: function() {
					$table.block( {
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					} );
				},
				complete: function() {
					$table.unblock();
				},
				dataType: 'json'
			} );
		},
	} );

} );
;(function( $, window, document, undefined ) {

	"use strict";

	// Create the defaults once
	var pluginName = "inputQuantity",
		defaults = {
			input: '[data-quantity-field]',
			checkNotNumberReg: /[\D]/g,
			checkNotNumberFloatReg: /[^\d\.]/g,
			decimalSeparatorReg: /[.]/g,
			tooltipTimeout: 3000
		};

	// The actual plugin constructor
	function Plugin( element, options ) {
		this.element = element;

		this.settings = $.extend( {}, defaults, options );

		this.settings.validationMessage = $( this.element ).data( 'quantity-validation' );

		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend( Plugin.prototype, {
		init: function() {

			this.input = $( this.element ).find( this.settings.input );

			$( this.input ).on( 'input', $.proxy( this.inputHandler, this ) );
			$( this.input ).on( 'keypress', $.proxy( this.keypressHandler, this ) );

			this.tooltip = $( '<div/>', {
				class: 'iconic-wsb-quantity__tooltip',
				text: ''
			} ).appendTo( $( this.element ) );

			var input = $( this.input );
			var step = input.attr( 'step' );
			var toFixed = String( step ).substring( String( step ).indexOf( '.' ) ).length - 1;
			this.settings.float = toFixed !== 0;

		},
		keypressHandler: function( e ) {
			e = e || event;
			var chr = this.getChar( e );
			var onlyNumber = this.settings.validationMessage.onlyNumber;
			var decimalSeparatorReg = this.settings.decimalSeparatorReg;
			var inputVal = $( this.input ).val();
			var checkNotNumberReg;

			if ( this.settings.float ) {
				checkNotNumberReg = this.settings.checkNotNumberFloatReg;
			} else {
				checkNotNumberReg = this.settings.checkNotNumberReg;
			}

			if ( e.ctrlKey || e.altKey || chr == null ) {
				this.showMessage( onlyNumber );
				return false;
			}

			if ( checkNotNumberReg.test( chr ) ) {
				this.showMessage( onlyNumber );
				return false;
			}

			if ( inputVal.match( decimalSeparatorReg ) && ! /[\d]/.test( chr ) ) {
				this.showMessage( onlyNumber );
				return false;
			}

		},
		inputHandler: function( e ) {
			var input = $( this.input );
			var value = input.val();

			return this.validateInput( value );

		},
		validateInput: function( nextValue ) {

			var input = $( this.input );
			var max = Number( input.attr( 'max' ) ) || Infinity;
			var min = Number( input.attr( 'min' ) ) || - Infinity;
			var maxMessage = this.settings.validationMessage.max;
			var minMessage = this.settings.validationMessage.min;
			var onlyNumber = this.settings.validationMessage.onlyNumber;
			var checkNotNumberReg;

			if ( this.settings.float ) {
				checkNotNumberReg = this.settings.checkNotNumberFloatReg;
			} else {
				checkNotNumberReg = this.settings.checkNotNumberReg;
			}

			if ( nextValue > max ) {
				this.showMessage( maxMessage + max );
				return this.setInputValue( max );
			}

			if ( nextValue < min ) {
				this.showMessage( minMessage + min );
				return this.setInputValue( min );
			}

			if ( isNaN( nextValue ) || checkNotNumberReg.test( nextValue ) ) {
				this.showMessage( onlyNumber );
				return this.setInputValue( min );
			}

			return this.setInputValue( nextValue );

		},
		setInputValue: function( newValue ) {
			var input = $( this.input );
			var step = input.attr( 'step' );
			var toFixed = String( step ).substring( String( step ).indexOf( '.' ) ).length - 1;

			// replace value if 1.0 === 1
			if ( Number( newValue ) !== + Number( newValue ).toFixed( 0 ) ) {
				//value = Number(value).toFixed(0);
				newValue = Number( newValue ).toFixed( toFixed );
			}

			input.val( newValue );

			return newValue;
		},
		showMessage: function( message ) {

			var self = this;
			var tooltipTimeout = this.settings.tooltipTimeout;

			$( this.tooltip ).html( message ).addClass( 'is-active' );

			clearTimeout( this.timerId );

			this.timerId = setTimeout( function() {
				self.removeMessage();
			}, tooltipTimeout );

		},
		removeMessage: function() {
			$( this.tooltip ).removeClass( 'is-active' );
		},
		getChar: function( event ) {
			if ( event.which === null ) {
				if ( event.keyCode < 32 ) {
					return null;
				}

				return String.fromCharCode( event.keyCode ); // IE
			}

			if ( event.which !== 0 && event.charCode !== 0 ) {
				if ( event.which < 32 ) {
					return null;
				}

				return String.fromCharCode( event.which ); // other
			}

			return null;
		}
	} );

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[ pluginName ] = function( options ) {
		return this.each( function() {
			if ( ! $.data( this, "plugin_" + pluginName ) ) {
				$.data( this, "plugin_" +
				              pluginName, new Plugin( this, options ) );
			}
		} );
	};

	function initQuantityInput() {

		$( '[data-quantity-validation]' ).inputQuantity();

	}

	$( document ).ready( initQuantityInput );

})( jQuery, window, document );