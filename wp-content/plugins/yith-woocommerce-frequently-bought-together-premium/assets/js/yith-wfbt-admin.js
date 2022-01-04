/**
 * admin.js
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    "use strict";

    $( '.yith-wfbt_options a' ).on( 'click', function(){

        var select      = $('#yith_wfbt_default_variation'),
            select_wrap = select.parents('p');

        select_wrap.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
        }});


        $.ajax({
            type: 'POST',
            url: yith_wfbt.ajaxurl,
            data: {
                action   : 'yith_update_variation_list',
                productID: yith_wfbt.postID
            },
            dataType: 'html',
            success: function( res ){

                // add new content
                select.html( res );
                select_wrap.unblock();
            }
        })
    });

    $( '#yith_wfbt_data_option' ).find( '[data-deps]' ).each( function() {

        var t           = $(this),
            deps        = t.attr('data-deps').split(','),
            values      = t.attr('data-value').split(','),
            conditions  = [];

        $.each( deps, function( i, dep ) {
            $( '[name="' + dep + '"]').on( 'change', function(){

                var value           = this.value,
                    check_values    = '';

                // exclude radio if not checked
                if( this.type == 'radio' && ! $(this).is(':checked') ){
                    return;
                }

                if( this.type == 'checkbox' ){
                    value = $(this).is(':checked') ? 'yes' : 'no';
                }

                check_values = values[i] + ''; // force to string
                check_values = check_values.split('|');
                conditions[i] = $.inArray( value, check_values ) !== -1;

                if( $.inArray( false, conditions ) === -1 ){
                    t.show();
                } else {
                    t.hide();
                }

            }).change();
        });
    });
});