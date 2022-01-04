/**
 * Created by v0id on 20.12.16.
 */

jQuery( function ( $ ) {

    var wpfoofDEBUG=true;
    var file_frame = [];
        //  $button = $( '.wpfoof-box-upload-button' ),
        //$removebutton = $( '.wpfoof-box-upload-button-remove' );

    /*$button.on( 'click',$.fn.clickWPfoofClickUpload);*/
    
    $.fn.clickWPfoofClickUpload = function (elm) {

        var $this = $( elm ),
            id = $this.attr( 'id' );       

        // If the media frame already exists, reopen it.
        if ( file_frame[ id ] ) {
            file_frame[ id ].open();

            return;
        }

        // Create the media frame.
        file_frame[ id ] = wp.media.frames.file_frame = wp.media( {
            title    : $this.data( 'uploader_title' ),
            button   : {
                text : $this.data( 'uploader_button_text' )
            },
            multiple : false  // Set to true to allow multiple files to be selected
        } );

        // When an image is selected, run a callback.
        file_frame[ id ].on( 'select', function() {

            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame[ id ].state().get( 'selection' ).first().toJSON();
            // set input
            var elm_changed = $('#_value-' + id);
            elm_changed.val(attachment.id);
            setChangesWPFOOF(elm_changed);
            // set preview
            var img = '<img src="' + attachment.url + '" style="display: block; margin-left: 0px; max-width: 30%; height: auto; margin-bottom: 10px; margin-top: 10px;" />';
            $('#IDprev-'+id ).html( img );
            $('#'+id+'-remove').show();
            var s =  $('#'+id+'-alert').data('size').split('x');
            //for the general settings
            if(id == "Maine-Img"){
                $.fn.saveWPWoofParam({'action':'set_wpwoof_image','wpwoof_image':attachment.id});
            }
            if(attachment.width!=s[0] || attachment.height!=s[1]){
                /*  $('#'+id+'-alert').html('image size:'+attachment.width+'x'+attachment.height+' but we recommend '+$('#'+id+'-alert').data('size'));*/

            }else{
                $('#'+id+'-alert').html('');
            }
        } );
        // Finally, open the modal
        file_frame[ id ].open();

    };

   


     $.fn.clickWPfoofClickRemove = function( elm ) {

        console.log("clickWPfoofClickRemove",id);

        var obj = $( elm );
        var id = obj.prev( 'input' ).attr( 'id' );

        $('#IDprev-'+id ).html( '' );
        $('#'+id+'-remove').hide();
        $('#'+id+'-alert').html('');
        var elm_changed =  $( '#_value-' + id  );
        elm_changed.val( 0 );
        setChangesWPFOOF(elm_changed);
         //for the general settings
        if(id == "Maine-Img"){
             $.fn.saveWPWoofParam({'action':'set_wpwoof_image','wpwoof_image':''});
        }
        
    };

    setChangesWPFOOF = function( elm_changed ){
        if($('#variable_product_options').length && ! $('#variable_product_options').is(':hidden')) {
            elm_changed
                .closest('.woocommerce_variation')
                .addClass('variation-needs-update');
            $('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
            $('#variable_product_options').trigger('woocommerce_variations_input_changed');
        }
    };

} );
