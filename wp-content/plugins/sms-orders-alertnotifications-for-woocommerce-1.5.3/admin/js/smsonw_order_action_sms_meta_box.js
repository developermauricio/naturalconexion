(function( $ ) {
    'use strict';

    $( document ).ready(function() {

        $("#egoi_send_order_sms_button").on("click", function () {
            if ($("#egoi_send_order_sms_message").val() == '') {
                $("#egoi_send_order_sms_notice").hide();
                $("#egoi_send_order_sms_error").show();
                return false;
            }
            $("#egoi_send_order_sms_error").hide();
            $("#egoi_send_order_sms_notice").show();
            var data = {
                'action': 'smsonw_order_action_sms_meta_box',
                'order_id': $("#egoi_send_order_sms_order_id").val(),
                'country': $("#egoi_send_order_sms_order_country").val(),
                'recipient': $("#egoi_send_order_sms_recipient").val(),
                'message': $("#egoi_send_order_sms_message").val(),
                'security': smsonw_meta_box_ajax_object.ajax_nonce
            };

            $.post(smsonw_meta_box_ajax_object.ajax_url, data, function(response) {
                var note = jQuery.parseJSON(response);
                if (note.message) {
                    $(".order_notes").prepend(
                        "<li class='note system-note'>" +
                        "<div class='note_content'><p>" + note.message + "</p></div>" +
                        "<p class='meta'>" +
                        "<abbr class='exact-date'>" + note.date + "</abbr>" +
                        "</li>");
                    $("#egoi_send_order_sms_notice").hide();
                } else if (note.errorCode) {
                    $("#egoi_send_order_sms_notice").hide();
                    $("#egoi_send_order_sms_error").show().text(note.errors[0]);
                }
            });

        });

        $('#egoi_add_tracking').attr('disabled',true);

        $('#egoi-add-tracking-code').keyup(function(){
            if($(this).val().length !=0 && $("#egoi_add_tracking_carrier").val().length != 0)
                $('#egoi_add_tracking').attr('disabled', false);
            else
                $('#egoi_add_tracking').attr('disabled',true);
        });

        $('#egoi_add_tracking_carrier').change(function(){
            if($(this).val().length !=0 && $("#egoi-add-tracking-code").val().length != 0)
                $('#egoi_add_tracking').attr('disabled', false);
            else
                $('#egoi_add_tracking').attr('disabled',true);
        });

        $('#egoi_add_tracking').click(function () {
            if($('#egoi_add_tracking').attr('disabled') == 'disabled')
                return;
            var tracking = {
                carrier: $("#egoi_add_tracking_carrier").val(),
                code: $("#egoi-add-tracking-code").val()
            }
            $("#egoi-add-tracking-code").val('');
            $('#egoi_add_tracking').attr('disabled', true);
            addTracking(tracking);
        });

        $('#egoi_tracking_for_sms').on('click', '.egoi_close_x', function (event) {
            event.preventDefault();
            removeTraking($(event.target).attr('id'));
        });

        $("#egoi_tracking_for_sms").keydown(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        var addTracking = function (track) {
            track.security  = smsonw_meta_box_ajax_object.ajax_nonce;
            track.action    = 'smsonw_order_add_tracking_number';
            track.order_id  = $("#egoi_send_order_sms_order_id").val();
            block('#egoi_tracking_for_sms');
            $.post(smsonw_meta_box_ajax_object.ajax_url, track, function(response) {
                unblock('#egoi_tracking_for_sms');
                if(response.includes('SUCCESS')){
                    $('#egoi_tracking_for_sms_insert').attr('disabled',true);
                    $('#egoi_tracking_for_sms_insert').hide();
                    $(".smsonw-tracking-code__list ul").append('' +
                        '<li id="'+track.code+'">' +
                        '<span class="tracking-code-link">'+$("#egoi_add_tracking_carrier option:selected").text()+': </span>' +
                        '<a href="#" class="tracking-code-link" title="'+ $("#egoi_add_tracking_carrier option:selected").text() +'">'+ track.code +'</a> ' +
                        '<a class="egoi_close_x select2-selection__clear" id="tracking-'+track.code+'"  href="#" >×</a>' +
                        '</li>'
                    );
                }
                else
                    return trigger_error(response);
            });
            return true
        }

        var removeTraking = function(id){
            id = id.replace("tracking-","");
            var obj = $("#"+id);
            if(obj.length==0)
                return false;
            var track = {
                code:       id,
                security:   smsonw_meta_box_ajax_object.ajax_nonce,
                action:     'smsonw_order_delete_tracking_number',
                order_id:   $("#egoi_send_order_sms_order_id").val(),
            };
            block('#egoi_tracking_for_sms');
            $.post(smsonw_meta_box_ajax_object.ajax_url, track, function(response) {
                unblock('#egoi_tracking_for_sms');
                if(response.includes('SUCCESS')){
                    obj.remove();
                    $('#egoi_tracking_for_sms_insert').attr('disabled',false);
                    $('#egoi_tracking_for_sms_insert').show();
                }
                else
                    return trigger_error(response);
            });
            return true;
        };
        
        var trigger_error = function (err) {
            console.log(err);
            return false;
        };

        var block = function(id) {
            $( id ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        };

        /**
         * Unblock meta boxes.
         */
        var unblock = function(id) {
            $( id ).unblock();
        };


    });

})( jQuery );