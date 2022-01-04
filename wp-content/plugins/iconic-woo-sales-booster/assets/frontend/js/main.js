jQuery( function( $ ) {
	"use strict";

	/**
	 * Trigger add offered product to order
	 */
	$( document ).on( 'change', '[data-iconic-wsb-checkout-bump-trigger]', function() {
		var actionTypeInput = $( '[name=iconic-wsb-checkout-bump-action]' );

		if ( ! $( this ).prop( 'checked' ) ) {
			actionTypeInput.val( 'remove' );
		} else {
			actionTypeInput.val( 'add' );
		}

		$( 'body' ).trigger( 'update_checkout' );
	} );

	/**
	 * After checkout bump
	 * @constructor
	 */
	var AfterCheckoutBump = function() {
		this.isBumpShowed = false;
		this.isFormSubmited = false;

		this.init = function() {
			this.$checkoutForm       = $( 'form.checkout' );
			this.container           = '[data-iconic-wsb-acb-modal]';
			this.$bumpModal          = $( this.container );
			this.$addToCartButton    = $( '[data-iconic-wsb-acb-add-to-cart-button]' );
			this.$closeButton        = $( '[data-iconic-wsb-acb-close-button]' );
			this.actionInputSelector = '[name=iconic-wsb-acb-action]';
			this.$form               = $( '.iconic-wsb-after-checkout-bump-form' );
			this.variation_dropdown  = ".iconic-wsb-checkout-modal__select";
			this.variationHandler    = new IconicVariationHandler(this.$form, this.container, this.onVariationFound.bind( this ) , this.onVariationNotFound.bind( this ) );
			var self                 = this;

			// If suitable order bump exists
			if ( this.$bumpModal.length > 0 ) {

				this.$checkoutForm.on( 'checkout_place_order.orderBump', this.openModal.bind( this ) );
				this.$closeButton.on( 'click', this.close.bind( this ) );

				this.$addToCartButton.on( 'click', (function( e ) {
					e.preventDefault();
					this.addToCartBump();
				}).bind( this ) );

				$(this.container).on("change", this.variation_dropdown, this.variationHandler.getVariation.bind( this.variationHandler ) );
				$( '.wsb_select_readonly option:not(:selected)' ).prop( 'disabled', true );

			}

			//to make compatible with WooCommerce Attribute Swatches plugin
			this.$form.addClass("variations_form");
			this.$form.data("product_variations", false);
		};

		this.close = function() {
			$.magnificPopup.close();
			this.$checkoutForm.submit();
		};

		this.addToCartBump = function() {

			$( this.actionInputSelector ).val( 'add' );

			this.$checkoutForm.trigger( 'update_checkout' );

			this.$checkoutForm.off( 'checkout_place_order.orderBump', this.openModal.bind( this ) );

			$( document ).ajaxComplete( ( function ( event, xhr, settings ) {
				
				// ensure this is the update_order_review AJAX request.
				if ( ! settings.url.includes( "update_order_review" ) ) {
					return;
				}

				if ( this.isFormSubmited === true ) {
					return;
				}

				this.isFormSubmited = true;

				this.$checkoutForm.submit();
			}).bind( this ) );

			$.magnificPopup.close();
		};

		this.openModal = function() {
			if ( this.isBumpShowed === true ) {
				return true;
			}

			this.variationHandler.getVariation();

			$.magnificPopup.open( {
				items: {
					src: this.$bumpModal,
					type: 'inline'
				},
				modal: true
			} );

			this.isBumpShowed = true;

			return false;
		};

		this.onVariationFound = function( variation ) {
			this.$addToCartButton.attr( "disabled", false );
			this.$form.find(".iconic-wsb-checkout-modal-unavailable_msg").hide();
			this.$form.find(".iconic-wsb-modal-product-offer__price").html( variation.price_html );
			$("[name='iconic-wsb-acb-variation-id']").val( variation.variation_id );
		};

		this.onVariationNotFound = function() {
			this.$addToCartButton.attr( "disabled", true );
			this.$form.find( ".iconic-wsb-checkout-modal-unavailable_msg" ).slideDown( 200 );
			this.$form.find(".iconic-wsb-modal-product-offer__price").html("");
			$("[name='iconic-wsb-acb-variation-id']").val( "" );
		};
	};

	var CheckoutBumpVariableProduct = function() {

		this.allVariationSelectedFlag = null;
		this.$attributeFields         = null;
		this.$form                    = $( "form.woocommerce-checkout" );
		this.container                = ".iconic-wsb-checkout-bump";
		this.variation_dropdown       = ".iconic-wsb-checkout-bump__select";

		/**
		 * Initialize
		 */
		this.init = function () {
			var self = this;
			self.variationHandler = new IconicVariationHandler(self.$form, this.container, this.onVariationFound.bind( self ), this.onVariationNotFound.bind( self ) );

			$(self.$form).on("change", this.variation_dropdown, this.variationHandler.getVariation.bind( this.variationHandler ) );
			$(self.$form).on("change", this.variation_dropdown, this.handleCheckbox.bind(this) );

			$( document.body ).on( 'updated_checkout', function() {

				self.product_type = self.$form.find( ".iconic-wsb-checkout-bump" ).data( "product_type" );

				if( self.product_type === "variable" ) {

					if ( self.$form.find( "[name=iconic-wsb-checkout-variation-id]" ).length && $( "[name=iconic-wsb-checkout-variation-id]" ).val() === '' ) {
						self.variationHandler.getVariation( false ); //need to run getVariation after updated_checkout has been triggered
					}

					if ( self.$form.find( ".iconic-wsb-checkout-bump__select" ).length ) {
						self.$form.find( ".iconic-wsb-checkout-bump__header-checkbox" ).attr( "disabled", !self.variationHandler.checkAllSelects.bind( self.variationHandler ) );
					}

					self.handleSwatchesLabel();
					self.handleOverlay();
					self.handleCheckbox();
				}
				
				$('.wsb_select_readonly option:not(:selected)').prop('disabled', true);


			});

			//to make compatible with WooCommerce Attribute Swatches plugin
			this.$form.addClass( "variations_form" );
			this.$form.data( "product_variations" , false );

		};


		/**
		 * 1)Hides unavailable variation message 2) Enables the 'Add to cart' checkbox
		 */
		this.onVariationFound = function (variation) {
			this.$form.find("[data-iconic-wsb-checkout-bump-trigger]").attr("disabled", false);
			this.$form.find(".iconic-wsb-checkou-bump_unavailable_msg").hide();
		};

		/**
		 * 1)Shows unavailable variation message 2) Disables the 'Add to cart' checkbox
		 */
		this.onVariationNotFound = function() {
			this.$form.find("[data-iconic-wsb-checkout-bump-trigger]").attr("disabled", true);
			this.$form.find(".iconic-wsb-checkou-bump_unavailable_msg").slideDown(200);
			
		};

		/**
		 * Show/Hide overlay to prevent user from changing the variation after the product has been added to cart.
		 */
		this.handleOverlay = function() {
			var isInCart = this.$form.find( "[data-iconic-wsb-checkout-bump-trigger]" ).prop( "checked" );
			if ( isInCart ) {
				this.$form.find( ".iconic-wsb-checkout-bump" ).addClass( "iconic-wsb-checkout-bump__overlay_active" );
				this.$form.find( ".iconic-wsb-checkout-bump__overlay" ).show();
			}
			else {
				this.$form.find( ".iconic-wsb-checkout-bump" ).removeClass("iconic-wsb-checkout-bump__overlay_active");
				this.$form.find( ".iconic-wsb-checkout-bump__overlay" ).hide();
			}
		};

		/**
		 * If Woocomerce Attributes Swatches plugin is enabled then makes sure that lables for attributes are visible
		 */
		this.handleSwatchesLabel = function() {
			if( this.$form.find(".iconic-was-swatches").length ) {
				this.$form.find(".iconic-wsb-variation__select").each(function () {
					var val = jQuery(this).val();
					if ( val ) {
						var $row = jQuery( this ).closest( "tr" );
						var html = $row.find( "td.label label .iconic-was-chosen-attribute" ).html();
						if ( html && html.trim() === "" ) {
							var new_html = $row.find( ".iconic-was-swatch--selected" ).data( "attribute-value-name" );
							$row.find( "td.label label .iconic-was-chosen-attribute" ).html( new_html );
						}
					}
				});
			}
		};

		/**
		 * Enable or disable 'data-iconic-wsb-checkout-bump-trigger' checkbox
		 */
		this.handleCheckbox = function() {
			var $checkbox = this.$form.find("[data-iconic-wsb-checkout-bump-trigger]");
			//1) If: $checkbox is checked then user should be able to uncheck it, hence always enable it. Else: if any of the attributes are not selected, then disable $checkox
			if ( $checkbox.prop("checked") ) {
				$checkbox.attr( "disabled" , false );
			}
			else {
				this.$form.find( "[data-iconic-wsb-checkout-bump-trigger]" ).attr( "disabled", ! this.variationHandler.checkAllSelects() );
			}
		};

	};

	var afterCheckoutBump = new AfterCheckoutBump();
	var checkoutBumpVariableProduct = new CheckoutBumpVariableProduct();

	$( document ).ready( function() {
		afterCheckoutBump.init();
		checkoutBumpVariableProduct.init();
	} );

} );

jQuery( function( $ ) {
	"use strict";

	/**
	 * After add to cart bump
	 * @constructor
	 */
	var AfterAddToCartBump = function() {
		this.init = function() {
			this.$bumpModal = $( '[data-iconic-wsb-acc-modal-bump]' );
			this.$closeButton = $( '[data-iconic-wsb-close-aac-modal]' );
			this.$variation_wrap = jQuery( this.$bumpModal.selector + ' .single_variation_wrap' );

			// If order bump modal exists
			if ( this.$bumpModal.length > 0 ) {

				this.$closeButton.on( 'click', this.close.bind( this ) );
				this.$variation_wrap.on( 'show_variation', this.changeVariationImage );

				this.openModal();
			}
		};

		this.changeVariationImage = function( event, variation ) {
			if ( variation.image.thumb_src !== undefined ) {
				var $image = $( this ).closest( '[data-iconic-wsb-acc-modal-bump-offer-product]' ).find( '[data-iconic-wsb-acc-modal-bamp-offer-image]' ).find( 'img' );
				$image.attr( 'srcset', '' );
				$image.attr( 'src', variation.image.thumb_src );
			}
		};

		this.close = function() {
			$.magnificPopup.close();
		};

		this.openModal = function() {
			$.magnificPopup.open( {
				items: {
					src: this.$bumpModal,
					type: 'inline',

				},
				modal: true
			} );
		};
	};

	/**
	 * Handles all the events and actions for FBT module
	 */
	var FrequentlyBoughTogether = function() {

		this.init = function() {
			$( '.variations_form' ).on( 'found_variation', this.variationChangeHandle.bind( this ) );

			$( '.iconic-wsb-bump-product__select' ).change( function( e ) {
				this.toggleButtonStatus.bind( this )();
				this.updateTotalPrice.bind( this )();
				this.fetchVariationPrice.bind( e.target )();
				this.updateAttributeData.bind( e.target )();
			}.bind( this ) );

			$( '.iconic-wsb-bump-product__checkbox' ).change( function() {
				this.updateTotalPrice();
				this.toggleButtonStatus();
			}.bind( this ) );

			this.toggleButtonStatus();
			this.addToCartAjax();
			this.toggleProductImages();
			this.disableFormSubmitForDisabledButton();
		};

		/**
		 * Saves the attribute's data as JSON in hidden fields. Is called when FBT dropdown is changed.
		 */
		this.updateAttributeData = function() {
			var product_id = $( this ).data( "product_id" ),
				attributes = $( this ).find( "option:selected" ).data( "attributes" ),
				attributes_str = typeof attributes === 'object' ? JSON.stringify( attributes ) : attributes;

			$( "[name='iconic-wsb-bump-product_attributes-" + product_id + "']" ).val( attributes_str );
		};

		/**
		 * Disable "Add Selected to Cart" button when 
		 * either variation is not selected for any variable products
		 * or no products are checked
		 */
		this.toggleButtonStatus = function() {
			// Check if variations are selected
			var variableSelectFlag = true;
			$( '.iconic-wsb-product-bumps__list-item' ).each( function() {
				if ( $( this ).find( ".iconic-wsb-bump-product__checkbox" ).prop( "checked" ) ) {
					var $select = $( this ).find( ".iconic-wsb-bump-product__select" );
					if ( $select.length && ! $select.val() ) {
						variableSelectFlag = false;
					}
				}
			} );

			// check if atleast one product is checked
			var selectedProductsCount = $( '.iconic-wsb-bump-product__checkbox:checked' ).length;

			if ( variableSelectFlag && selectedProductsCount ) {
				$( ".iconic-wsb-product-bumps__button" ).removeClass( "disabled" );
			} else {
				$( ".iconic-wsb-product-bumps__button" ).addClass( "disabled" );
			}
		};
		
		/**
		 * Disable form submit when the button is disabled ( variations needs to be selected )
		 */
		this.disableFormSubmitForDisabledButton = function() {
			
			/* As we have multiple submit buttons on the form. Here we are saving 
			   the button which was clicked. This data will be used in form submit event.
			*/
			$( 'form.cart' ).click( function( e ) {
				$(this).data( 'clicked', $( e.target ) );
			});
			
			$( 'form.cart' ).submit( function( e ) {
				// Only prevent form submit when clicked on "Add selected to cart" button.
				if( $(this).data('clicked').is( "[data-bump-product-form-submit]" ) ) {
					if( $(this).find( '.iconic-wsb-product-bumps__button' ).hasClass( "disabled" ) ) {
						e.preventDefault();
						return false;
					}
				}
			} );
			
			$( '.iconic-wsb-product-bumps__button' ).click( function() {
				if( $( this ).hasClass( "disabled" ) ) {
					alert( iconic_wsb_frontend_vars.i18n.disabled_add_to_cart );
				}
			} );
		};

		/**
		 * Fetch price for the selcted variation
		 */
		this.fetchVariationPrice = function() {

			var data = {
				action: 'iconic_wsb_get_variation_price',
				variation_id: jQuery( this ).val(),
				_ajax_nonce: iconic_wsb_frontend_vars.nonce
			};

			var self = this;

			$( '.variations_form' ).block( { message: null, overlayCSS: { background: '#fff', opacity: 0.6 } } );

			jQuery.post( iconic_wsb_frontend_vars.ajax_url, data, function( response ) {
				$( '.variations_form' ).unblock();

				if ( !response.success ) {
					return;
				}

				jQuery( self ).siblings( ".iconic-wsb-bump-product__price" ).html( response.data.variation_price_html );
			} );

		};

		/**
		 * Fetches and shows the total price of selected products
		 */
		this.updateTotalPrice = function() {
			var product_ids = [];

			//collect all product IDs in product_ids
			$( ".iconic-wsb-product-bumps__list-item" ).each( function() {
				if ( $( this ).find( ".iconic-wsb-bump-product__checkbox" ).is( ":checked" ) ) {
					if ( $( this ).data( "product_type" ) === "variable" ) {
						var parent_id = parseInt( $( this ).data( "product_id" ) ),
							variation_value = parseInt( $( this ).find( ".iconic-wsb-bump-product__select" ).val() ),
							id = variation_value > 0 ? variation_value : parent_id;

						product_ids.push( id );
					} else {
						product_ids.push( $( this ).data( "product_id" ) );
					}
				}
			} );

			var data = {
				action: 'iconic_wsb_fbt_get_products_price',
				product_ids: product_ids,
				offer_product_id: $( "[name='iconic-wsb-fbt-this-product']" ).val(),
				_ajax_nonce: iconic_wsb_frontend_vars.nonce
			};

			$( '.variations_form' ).block( { message: null, overlayCSS: { background: '#fff', opacity: 0.6 } } );

			$.post( iconic_wsb_frontend_vars.ajax_url, data, function( data ) {
				$( ".iconic-wsb-product-bumps__total-price-amount" ).html( data.html );
				$( '.variations_form' ).unblock();
			} );

		};

		/**
		 * Responsible for selecting the right option from the Dropdown under
		 * "Add Selected to Cart" when user changes attributes.
		 */
		this.variationChangeHandle = function( e, variation_data ) {
			this.variationId = variation_data.variation_id;
			this.currentProductId = $( "form.cart" ).data( "product_id" );
			if ( this.variationId && $( ".iconic-wsb-bump-product__select--" + this.currentProductId ).length ) {
				var $select = $( ".iconic-wsb-bump-product__select--" + this.currentProductId );

				//gather all the Single product attributes in `currentProductAttributes`
				var currentProductAttributes = {};
				$( "form.cart .variations select" ).each( function() {
					currentProductAttributes[ $( this ).data( "attribute_name" ) ] = $( this ).find( "option:selected" ).text();
				} );

				//find the matching 'option in the dropdown'
				var matchIndex = 0;
				$( ".iconic-wsb-bump-product__select--" + this.currentProductId + " option" ).each( function( optionIndex ) {
					var $option = $( this );
					var optionAttribute = $option.data( "attributes" );
					var matchFlag = true;
					var attribute;
					for ( attribute in currentProductAttributes ) {
						if ( optionAttribute && optionAttribute[ attribute ] === currentProductAttributes[ attribute ] ) {
							//ok
						} else {
							matchFlag = false;
						}
					}
					if ( matchFlag ) {
						matchIndex = optionIndex;
						$select.find( "option" ).eq( matchIndex ).prop( 'selected', true );
						$select.change();
					}
				} );
			}
		};

		/**
		 * Adds products to the cart with AJAX rather than page reload, when AJAX setting is enabled
		 */
		this.addToCartAjax = function() {
			$( '[data-bump-product-form-submit]' ).click( function( e ) {
				var $button = $( this );

				if ( $button.prop( 'disabled' ) || $button.hasClass( 'disabled' ) || '1' !== iconic_wsb_frontend_vars.fbt_use_ajax ) {
					return;
				}

				var $bumps = $button.closest( '.iconic-wsb-product-bumps' ),
					$form = $button.closest( 'form.cart' );

				e.preventDefault();

				$bumps.block( { message: null, overlayCSS: { background: '#fff', opacity: 0.6 } } );
				$button.prop( "disabled", true );

				$.ajax( {
					type: 'POST',
					url: wc_add_to_cart_params.ajax_url,
					data: $form.serialize() + "&iconic-wsb-add-selected=1&action=iconic_wsb_fbt_add_to_cart",
					success: function( result ) {
						$button.text( iconic_wsb_frontend_vars.i18n.success );
						$bumps.unblock();

						// Show notice.
						var notice_class = result.success ? 'woocommerce-message' : 'woocommerce-error';
						$( '.woocommerce-notices-wrapper' ).append( '<div class="' + notice_class + '">' + result.message + '</div>'  );
						jQuery.scroll_to_notices( $(".woocommerce-notices-wrapper") );

						// Redirect to cart if set in Woocommerce settings.
						if ( wc_add_to_cart_params.cart_redirect_after_add === "yes" ) {
							window.location = wc_add_to_cart_params.cart_url;
						} else {
							$( document.body ).trigger( 'wc_fragment_refresh' );
						}
					},
					error: function() {
						$button.text( iconic_wsb_frontend_vars.i18n.error );
						$bumps.unblock();
					},
					complete: function() {
						setTimeout( function() {
							$button.prop( "disabled", false ).text( iconic_wsb_frontend_vars.i18n.add_selected );
						}, 3000 );
					}
				} );
			} );	
		};
		
		/**
		 * Toggle the related thumbnail when checkbox for a product is deselected.
		 */
		this.toggleProductImages = function() {
			$( '.iconic-wsb-bump-product__checkbox' ).change( function() {
				var $checkbox = $( this ),
					$list_item = $checkbox.closest( '.iconic-wsb-product-bumps__list-item' ),
					$bumps = $checkbox.closest( '.iconic-wsb-product-bumps' ),
					id = $checkbox.val(),
					$images_wrapper = $( '.iconic-wsb-product-bumps__images' ),
					$images = $( '.iconic-wsb-product-bumps__image' );

				if ( !$checkbox.is( ':checked' ) ) {
					$list_item.addClass( 'iconic-wsb-product-bumps__list-item--faded' );
					$( '.iconic-wsb-product-bumps__image[data-product-id=' + id + ']' ).hide();
				} else {
					$list_item.removeClass( 'iconic-wsb-product-bumps__list-item--faded' );
					$( '.iconic-wsb-product-bumps__image[data-product-id=' + id + ']' ).show();
				}

				// Remove plus on last visible item.
				var $last_visible = $bumps.find( '.iconic-wsb-product-bumps__image:visible:last' );

				$images.removeClass( 'iconic-wsb-product-bumps__image--no-plus' );
				$last_visible.addClass( 'iconic-wsb-product-bumps__image--no-plus' );

				// Hide section if no images visible.
				var toggle_images = ! $images_wrapper.is( ':visible' ) || 0 < $last_visible.length; console.log( toggle_images );
				$images_wrapper.toggle( toggle_images );
			} );
		};
	};

	var afterAddToCartBump = new AfterAddToCartBump();
	var frequentlyBoughTogether = new FrequentlyBoughTogether();

	$( document ).ready( function() {
		afterAddToCartBump.init();
		frequentlyBoughTogether.init();
	} );
} );
"use strict";

/**
 * Reusable utility to handles the dropdown changes for the variation dropdowns.
 * 1. Fetches the variation price when all dropdowns are selected
 * 2. Saves attributes data in hidden fields 'iconic-wsb-acb-variation-data' or 'iconic-wsb-checkout-variation-data'
 * 3. Shows and Hides spinner while AJAX
 *
 * @param {Object}   form The jQuery instance of the form where variation dropdowns reside
 * @param {str}      container_selector selector string for the containing div
 * @param {Function} onVariationFound callback function to be called when the variation is purchasable
 * @param {Function} onVariationNotFound callback function to be called when the variation is not purchasable
 */
var IconicVariationHandler = function (form, container_selector, onVariationFound, onVariationNotFound ) {
	this.$form              = form;
	this.container_selector = container_selector;

	this.getVariation = function() {
		var self = this,
			$variationIdField = self.$form.find( ".iconic-wsb__variation_id" );

		if ( $variationIdField.length <= 0 ) {
			return;
		}

		if ( self.checkAllSelects() ) {
			
			self.$attributeFields = jQuery( self.container_selector ).find( '.iconic-wsb-variation__select' );
			var attributes = self.getChosenAttributes();
			var currentAttributes = attributes.data;
			//TODO: Make this more generic. 
			if ( self.$form.hasClass("iconic-wsb-after-checkout-bump-form") ) {
				jQuery("[name='iconic-wsb-acb-variation-data']").val(JSON.stringify(currentAttributes));
			}
			else {
				jQuery("[name='iconic-wsb-checkout-variation-data']").val(JSON.stringify(currentAttributes));
			}
			currentAttributes.product_id = parseInt( jQuery( self.container_selector ).find( ".iconic-wsb__product_id" ).val() );
			currentAttributes.action = "iconic_wsb_checkout_get_variation";
			currentAttributes.bump_id = jQuery( self.container_selector ).find( ".iconic-wsb__bump_id" ).val();
			currentAttributes._ajax_nonce = iconic_wsb_frontend_vars.nonce;
			self.showLoader();
			self.xhr = jQuery.ajax( {
				url: iconic_wsb_frontend_vars.ajax_url,
				type: 'POST',
				data: currentAttributes,
				success: function( variation ) {
					if ( variation ) {
						self.$form.find( "#iconic-wsb-checkout-bump-variation" ).val( variation.variation_id );
						self.$form.find( ".iconic-wsb-checkout-bump__price_span" ).html( variation.price_html );
						if ( !variation.is_purchasable || !variation.is_in_stock || !variation.variation_is_visible ) {
							onVariationNotFound();
							$variationIdField.val( "" );
						} else {
							onVariationFound( variation );
							$variationIdField.val( variation.variation_id );
						}
					} else {
						onVariationNotFound();
						$variationIdField.val( "" );
					}
					self.hideLoader();
				},
				complete: function() {
					self.hideLoader();
				}
			} );

			self.$form.find( "[data-iconic-wsb-acb-add-to-cart-button] , [data-iconic-wsb-checkout-bump-trigger]" ).attr( "disabled", false );
		} else {
			self.$form.find( "[data-iconic-wsb-acb-add-to-cart-button], [data-iconic-wsb-checkout-bump-trigger]" ).attr( "disabled", true );
		}
	};

	/**
	 * Checks all the variation dropdowns.
	 * @returns boolean. Returns true iff all attribute dropdowns have a selected value. Returns false even is a single attribute dropdown doesnt has a value.
	 */
	this.checkAllSelects = function() {
		var self = this;
		self.allVariationSelectedFlag = true;
		jQuery( self.container_selector ).find( ".iconic-wsb-variation__select" ).each( function() {
			if ( jQuery( this ).val() ) {
				jQuery( this ).closest( "tr" ).removeClass( "iconic-wsb-variation-tr-valdation-error" );
			} else {
				self.allVariationSelectedFlag = false;
				jQuery( this ).closest( "tr" ).addClass( "iconic-wsb-variation-tr-valdation-error" );
			}
		} );
		return self.allVariationSelectedFlag;
	};

	/**
	 * Get chosen attributes from form.
	 * @return array
	 */
	this.getChosenAttributes = function() {
		var data = {};
		var count = 0;
		var chosen = 0;

		this.$attributeFields.each( function() {
			var attribute_name = jQuery( this ).data( 'attribute_name' ) || jQuery( this ).attr( 'name' );
			var value = jQuery( this ).val() || '';

			if ( value.length > 0 ) {
				chosen ++;
			}

			count ++;
			data[ attribute_name ] = value;
		} );

		return {
			'count': count,
			'chosenCount': chosen,
			'data': data
		};
	};

	/**
	 * Show spinner/loader for ajax.
	 */
	this.showLoader = function() {
		this.$form.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );
	};

	/**
	 * used in conjection to showLoader() to hide the spinner/loader
	 */
	this.hideLoader = function() {
		this.$form.unblock();
	};

};