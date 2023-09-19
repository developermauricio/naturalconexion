/* global jQuery, ajaxurl, woocommerce_admin_meta_boxes, yith_bundle_opts */
jQuery( function ( $ ) {
    "use strict";

    var product_type = $( 'select#product-type' ),
        post_id      = woocommerce_admin_meta_boxes.post_id,
        bundle_regular_price      = $( '#_yith_wcpb_bundle_regular_price' ),
        bundle_sale_price         = $( '#_yith_wcpb_bundle_sale_price' ),
        block_params = {
            message   : null,
            overlayCSS: {
                background: '#fff',
                opacity   : 0.7
            }
        },
        tiptip_args  = {
            'attribute': 'data-tip',
            'fadeIn'   : 50,
            'fadeOut'  : 50,
            'delay'    : 200
        },
        isBundle     = function () {
            return 'yith_bundle' === product_type.val();
        };


    var yith_wcbp_metabox = {
        el      : {
            root                : $( '#yith_bundle_product_data' ),
            add_item_btn        : $( '#yith-wcpb-add-bundled-product' ),
            bundled_items       : $( '#yith_bundle_product_data .yith-wcpb-bundled-items' ),
            items_count         : $( '#yith_bundle_product_data .yith-wcpb-bundled-items .yith-wcpb-bundled-item' ).size() + 1,
            ajax_filter_products: null,
            expandCollapse      : $( '#yith-wcpb-bundled-items-expand-collapse' ),
            actions             : $( '#yith-wcpb-bundled-items__actions' )
        },
        init    : function () {
            this.el.add_item_btn.on( 'click', this.select_products );
            $( document ).on( 'click', '.yith-wcpb-add-product', this.add_item );
            $( document ).on( 'keyup', 'input.yith-wcpb-select-product-box__filter__search', this.search_filter );

            $( document ).on( 'click', '.yith-wcpb-remove-bundled-product-item', this.remove_current_item );
            $( document ).on( 'click', '.yith-wcpb-bundled-item h3 a', this.stop_event_propagation );

            $( document ).on( 'click', '.yith-wcpb-select-product-box__products__pagination .first:not(.disabled)', this.paginate );
            $( document ).on( 'click', '.yith-wcpb-select-product-box__products__pagination .prev:not(.disabled)', this.paginate );
            $( document ).on( 'click', '.yith-wcpb-select-product-box__products__pagination .next:not(.disabled)', this.paginate );
            $( document ).on( 'click', '.yith-wcpb-select-product-box__products__pagination .last:not(.disabled)', this.paginate );

            this.sorting();

            this.bundledItemsChangeHandler();
        },
        add_item: function () {
            var product_id = $( this ).data( 'id' ),
                products   = $( this ).closest( '.yith-wcpb-select-product-box__products' );

            if ( product_id ) {
                products.block( block_params );

                var data = {
                    action     : 'yith_wcpb_add_product_in_bundle',
                    open_closed: 'open',
                    bundle_id  : post_id,
                    id         : yith_wcbp_metabox.el.items_count,
                    product_id : product_id,
                    security: yith_bundle_opts.nonces.addProductToBundle
                };

                $.ajax( {
                            type    : 'POST',
                            url     : ajaxurl,
                            data    : data,
                            success : function ( response ) {
                                if ( response.error ) {
                                    alert( response.error );
                                } else if ( response.html ) {
                                    yith_wcbp_metabox.el.bundled_items.append( response.html );
                                    $( document.body ).trigger( 'wc-enhanced-select-init' );
                                    $( document.body ).trigger( 'yith-plugin-fw-init-radio' );
                                    $( document ).trigger( 'yith-wcpb-show-conditional-init' );
                                    yith_wcbp_metabox.el.items_count++;

                                    yith_wcbp_metabox.addItemToItemsWithQty( product_id );
                                    yith_wcbp_metabox.bundledItemsChangeHandler();
                                }
                            },
                            complete: function () {
                                products.unblock();
                            }
                        } );
            }
        },

        remove_current_item: function () {
            var _container  = $( this ).closest( '.yith-wcpb-bundled-item' ),
                _product_id = _container.data( 'product-id' );
            _container.remove();

            yith_wcbp_metabox.removeItemToItemsWithQty( _product_id );
            yith_wcbp_metabox.bundledItemsChangeHandler();
        },

        filter_products: function ( data ) {
            if ( data.s !== undefined && data.s.length < yith_bundle_opts.minimum_characters ) {
                data.s = '';
            }

            data = $.extend( data, { action: 'yith_wcpb_select_product_box_filtered' } );

            var products = $( '.yith-wcpb-select-product-box__products' );
            products.block( block_params );

            if ( yith_wcbp_metabox.el.ajax_filter_products ) {
                yith_wcbp_metabox.el.ajax_filter_products.abort();
            }

            yith_wcbp_metabox.el.ajax_filter_products = $.ajax( {
                                                                    type    : 'POST',
                                                                    url     : ajaxurl,
                                                                    data    : data,
                                                                    success : function ( response ) {
                                                                        products.html( response );
                                                                        yith_wcbp_metabox.updateAddedQuantities();
                                                                    },
                                                                    complete: function ( jqXHR, textStatus ) {
                                                                        if ( textStatus !== 'abort' ) {
                                                                            products.unblock( block_params );
                                                                        }
                                                                    }
                                                                } );
        },

        paginate: function () {
            var page = $( this ).data( 'page' );
            if ( page !== undefined ) {
                var search_filter_value = $( 'input.yith-wcpb-select-product-box__filter__search' ).val();
                yith_wcbp_metabox.filter_products( { s: search_filter_value, page: page } );
            }
        },

        search_filter: function () {
            var value = $( this ).val();
            if ( !value || value.length >= yith_bundle_opts.minimum_characters ) {
                yith_wcbp_metabox.filter_products( { s: value } );
            }
        },

        select_products          : function () {
            $.fn.yith_wcpb_popup( {
                                      ajax        : true,
                                      url         : ajaxurl,
                                      ajax_data   : {
                                          action: 'yith_wcpb_select_product_box'
                                      },
                                      ajax_success: function () {
                                          $( '.yith-wcpb-select-product-box__filter__search' ).focus();
                                          yith_wcbp_metabox.updateAddedQuantities();
                                      }
                                  } );
        },
        sorting                  : function () {
            var bundled_items = this.el.bundled_items.find( '.yith-wcpb-bundled-item' ).get();

            bundled_items.sort( function ( a, b ) {
                var compA = parseInt( $( a ).attr( 'rel' ) );
                var compB = parseInt( $( b ).attr( 'rel' ) );
                return compA < compB ? -1 : compA > compB ? 1 : 0;
            } );

            $( bundled_items ).each( function ( idx, itm ) {
                yith_wcbp_metabox.el.bundled_items.append( itm );
            } );

            this.el.bundled_items.sortable( {
                                                items               : '.yith-wcpb-bundled-item',
                                                cursor              : 'move',
                                                axis                : 'y',
                                                handle              : 'h3',
                                                scrollSensitivity   : 40,
                                                forcePlaceholderSize: true,
                                                opacity             : 0.65,
                                                placeholder         : 'wc-metabox-sortable-placeholder',
                                                start               : function ( event, ui ) {
                                                    ui.item.css( 'background-color', '#f6f6f6' );
                                                },
                                                stop                : function ( event, ui ) {
                                                    ui.item.removeAttr( 'style' );
                                                }
                                            } );
        },
        stop_event_propagation   : function ( event ) {
            event.stopPropagation();
        },
        bundledItemsChangeHandler: function () {
            if ( yith_wcbp_metabox.el.bundled_items.find( '.yith-wcpb-bundled-item' ).length ) {
                yith_wcbp_metabox.el.expandCollapse.show();
                yith_wcbp_metabox.el.actions.removeClass( 'yith-wcpb-bundled-items__actions--hero' );
            } else {
                yith_wcbp_metabox.el.expandCollapse.hide();
                yith_wcbp_metabox.el.actions.addClass( 'yith-wcpb-bundled-items__actions--hero' );
            }
            yith_wcbp_metabox.updateAddedQuantities();
        },
        getItemsWithQty          : function () {
            return yith_wcbp_metabox.el.root.data( 'items-with-qty' );
        },
        setItemsWithQty          : function ( items ) {
            yith_wcbp_metabox.el.root.data( 'items-with-qty', items );
        },
        addItemToItemsWithQty    : function ( _id ) {
            var items = yith_wcbp_metabox.getItemsWithQty();
            if ( items[ _id ] ) {
                items[ _id ]++;
            } else {
                items[ _id ] = 1;
            }

            yith_wcbp_metabox.setItemsWithQty( items );
        },
        removeItemToItemsWithQty : function ( _id ) {
            var items = yith_wcbp_metabox.getItemsWithQty();
            if ( items[ _id ] ) {
                items[ _id ]--;
                if ( !items[ _id ] ) {
                    delete items[ _id ];
                }
            } else {
                items[ _id ] = 1;
            }

            yith_wcbp_metabox.setItemsWithQty( items );
        },
        updateAddedQuantities    : function () {
            var _rows  = $( '.yith-wcpb-select-product-box__products .yith-wcpb-select-product-box__product' ),
                _items = yith_wcbp_metabox.getItemsWithQty();

            _rows.each( function ( _idx, _row ) {
                _row           = $( _row );
                var _productID = _row.data( 'product-id' ),
                    _qty       = _items[ _productID ],
                    _added     = _row.find( '.yith-wcpb-product-added' ),
                    _addedText = _added.find( '.yith-wcpb-product-added__text' );
                if ( 1 === _qty ) {
                    _addedText.html( yith_bundle_opts.i18n.addedLabelSingular );
                    _added.show();
                } else if ( _qty > 1 ) {
                    _addedText.html( yith_bundle_opts.i18n.addedLabelPlural.replace( '%s', _qty ) );
                    _added.show();
                } else {
                    _added.hide();
                }

            } );

        }
    };

    yith_wcbp_metabox.init();

    $( '.pricing' ).addClass( 'hide_if_yith_bundle' );
    $( '._manage_stock_field' ).addClass( 'show_if_yith_bundle' );
    $( '._tax_status_field' ).closest( 'div' ).addClass( 'show_if_yith_bundle' );
    $( '._sold_individually_field' ).addClass( 'show_if_yith_bundle' ).closest( 'div' ).addClass( 'show_if_yith_bundle' );

    $( '.shipping_tab' ).addClass( 'yith_bundle_hide_if_non_bundled_shipping' ).addClass( 'yith_bundle_show_if_bundled_shipping' );


    $( 'body' ).on( 'woocommerce-product-type-change', function ( event, select_val, select ) {
        if ( select_val === 'yith_bundle' ) {
            bundle_regular_price.removeAttr( 'disabled' );
            bundle_sale_price.removeAttr( 'disabled' );

            $( 'input#_downloadable' ).prop( 'checked', false );
            $( 'input#_virtual' ).removeAttr( 'checked' );
        } else {
            bundle_regular_price.attr( 'disabled', 'disabled' );
            bundle_sale_price.attr( 'disabled', 'disabled' );
        }
    } );

    product_type.change();

} );