(function( $ ) {
	'use strict';

    $( document ).ready(function() {

    	var languages = ['en', 'es', 'pt', 'pt_BR'];
    	var methods = ['multibanco', 'payshop', 'billet'];
		var form_id = $("#form_info").data('form-id');
		var form_lang = $("#form_info").data('form-lang');
        var form_method = $("#form_info").data('form-method');

		if ($( "#form_info" ).length && form_id != 'form-sms-order-senders' && form_id != 'form-sms-order-tests' && form_id != '') {
            var form_type = form_id.split("-");
            var element = 'tab-sms-'+form_type[3];
            if (typeof form_type[4] !== "undefined") {
                element = element+'-'+form_type[4];
            }

            activeConfigTab("#nav-"+element);
            showConfigWrap("#"+element);

			$("#sms_text_language").val(form_lang);
            $("#sms_payment_methode").val(form_method);
            $("#email_payment_method").val(form_method);
            disableAllSmsOrderTexts(languages, methods);
            enableSmsOrderText(form_lang, form_method);
            disableAllEmailOrderTexts(methods);
            enableEmailOrderText(form_method);
		} else {
            showConfigWrap('#tab-sms-senders');
            disableAllSmsOrderTexts(languages, methods);
            disableAllEmailOrderTexts(methods);
		}

        $(".nav-tab-addon").on("click", function () {
            activeConfigTab(this);

            var tab = $(".nav-tab-active").attr("id");
            var wrap = "#"+tab.substring(4);

            showConfigWrap(wrap);
        });

        $("#sms_text_language").on("change", function () {
            var lang = $(this).val();
            var method = $('#sms_payment_method').val();

            disableAllSmsOrderTexts(languages, methods);
            enableSmsOrderText(lang, method);
        });

        $("#sms_payment_method").on("change", function () {
            var method = $(this).val();
            var lang = $("#sms_text_language").val();

            disableAllSmsOrderTexts(languages, methods);
            enableSmsOrderText(lang, method);
        });

        $("#email_payment_method").on("change", function () {
            var method_email = $(this).val();

            disableAllEmailOrderTexts(methods);
            enableEmailOrderText(method_email);
        });

        $("#button-test-sms").on("click", function () {
        	$("#test-sms").show();
		});

        var text_el = '';
        var position = 0;
        $(".sms_texts_tags_button").on("click", function () {
			var cod = $(this).data('text-cod');
			if (text_el !== '') {
				var text = text_el.val();
				var new_text = text.substring(0, position) + cod + text.substring(position);
				text_el.val(new_text).focus();
			}
        });
        $("#form-sms-order-texts textarea").focusout( function () {
			text_el = $(this);
            position = $(this).getCursorPosition();
        });
        $("#form-sms-order-payment-texts textarea").focusout( function () {
            text_el = $(this);
            position = $(this).getCursorPosition();
        });
        $("#form-email-order-payment-texts textarea").focusout( function () {
            text_el = $(this);
            position = $(this).getCursorPosition();
        });

        $( "#form-email-order-payment-texts textarea" ).keydown(function(event) {
            if(event.keyCode == 13){
                var text = $(this).val();
                var position = $(this).getCursorPosition();
                var new_text = text.substring(0, position) + "\n" + text.substring(position);
                $(this).val(new_text);
            }            
          });

        var sender = $("#sender_hash");
        if (sender.val() == null) {
            $("#form-sms-order-senders :input").prop("disabled", true);
            sender.prop("disabled", false);
        }
        sender.on("change", function () {
            $("#form-sms-order-senders :input").prop("disabled", false);
            toggleAdminOrders();
        });

        if ($("#admin_phone").length) {
            toggleAdminOrders();
            $("#admin_phone").on("input", function () {
                toggleAdminOrders();
            });
        }

        $("#button_view_help").on("click", function () {
            activeConfigTab("#nav-tab-sms-help");
            showConfigWrap("#tab-sms-help");
        });

        //CUSTOM CARRIER
        $("#add_egoi_sms_order_tracking_button").on("click", function () {
            if($('#add_egoi_sms_order_tracking_button').attr('disabled') == 'disabled')
                return;
            addCustomCarrier()
        });

        $('#add_egoi_sms_order_tracking_button').attr('disabled',true);

        $("#custom_carriers_rows").keydown(function (event) {
            if(event.keyCode == 13) {
                event.preventDefault();
                if(!isValidFormCustom())
                    return false;
                addCustomCarrier();
            }
        });

        $("#add_egoi_sms_order_tracking_url").bind('input', function(){
            if(isValidFormCustom())
                $('#add_egoi_sms_order_tracking_button').attr('disabled', false);
            else
                $('#add_egoi_sms_order_tracking_button').attr('disabled',true);
        });

        $("#add_egoi_sms_order_tracking_name").bind('input', function(){
            if(isValidFormCustom())
                $('#add_egoi_sms_order_tracking_button').attr('disabled', false);
            else
                $('#add_egoi_sms_order_tracking_button').attr('disabled',true);
        });

        $('#custom_carriers_rows').on('click', '.remove_carrier', function (event) {
            removeCustomCarrier($(event.target).attr('id'));
        });

        $("#help_carrier_url").click(function(e) {
            e.preventDefault();
            activeConfigTab("#nav-tab-sms-help");
            showConfigWrap("#tab-sms-help");
            return false;
        });

    });

    function addCustomCarrier(){
        var carrier = {
            name:       $("#add_egoi_sms_order_tracking_name").val(),
            url:        $("#add_egoi_sms_order_tracking_url").val(),
            security:   smsonw_config_ajax_object.ajax_nonce,
            action:     'smsonw_add_custom_carrier'
        };
        block({type:'input'});
        $.post(smsonw_meta_box_ajax_object.ajax_url, carrier, function(response) {
            unblock({type:'input'});
            if(response.includes('ERROR')) {
                response = JSON.parse(response);
                propErrorMessage(response.ERROR);
            }else{
                cleanFields();
                addRowCustomCarrier(carrier);
            }
        });
    }

    function addRowCustomCarrier(carrier){
        $("#custom_carriers_rows").append('<tr id = "custom_carrier_'+ carrier.name +'">\n' +
            '<td>\n' +
            '<span style="width: 100%;" value="">'+ carrier.name +'</span>\n' +
            '</td>\n' +
            '<td>\n' +
            '<span style="min-width: 400px;width: 100%;" value="">'+carrier.url+'</span>\n' +
            '</td>\n' +
            '<td>\n' +
            '<div class="button remove_carrier remove-button" id="remove_custom_carrier_'+ carrier.name +'">x</div>\n' +
            '</td>\n' +
            '</tr>');
    }

    function cleanFields(){
        $("#add_egoi_sms_order_tracking_name").val('');
        $("#add_egoi_sms_order_tracking_url").val('');
    }

    function propErrorMessage(error){
        $("#tracking_texts_message").append('<div class="notice notice-error is-dismissible">\n' +
            '        <p>'+ error +'</p>\n' +
            '        </div>');
    }

    function removeCustomCarrier(id){

        var carrier = {
            security: smsonw_config_ajax_object.ajax_nonce,
            action: 'smsonw_remove_custom_carrier',
            name: id.replace('remove_custom_carrier_','')
        };
        block({type: 'remove', id:id});
        $.post(smsonw_meta_box_ajax_object.ajax_url, carrier, function(response) {
            unblock({type: 'remove', id:id});
            if(response.includes('ERROR')) {
                response = JSON.parse(response);
                propErrorMessage(response.ERROR);
            }else{
                removeCustomFromTable(id);
            }
        });
    }

    function removeCustomFromTable(id) {
        $('#'+id.replace('remove_',''))
            .children('td, th')
            .animate({
                padding: 0
            })
            .wrapInner('<div />')
            .children()
            .slideUp(function () {
                $(this).closest('tr').remove();
            });
    }

    function isValidFormCustom(){
        return (($("#add_egoi_sms_order_tracking_url").val().length !=0) && ($("#add_egoi_sms_order_tracking_name").val().length != 0));
    }

    function block(area) {
        switch (area.type) {
            case 'input':
                $( "#add_egoi_sms_order_tracking_button" ).addClass("loading");
                $("#add_egoi_sms_order_tracking_name").attr('disabled',true);
                $("#add_egoi_sms_order_tracking_url").attr('disabled',true);
                break;
            case 'remove':
                $( "#"+area.id ).addClass("loading");
                $( "#"+area.id ).html("&nbsp;&nbsp;");
                break;
        }
    };

    /**
     * Unblock meta boxes.
     */
    function unblock(area) {
        switch (area.type) {
            case 'input':
                $( "#add_egoi_sms_order_tracking_button" ).removeClass("loading");
                $("#add_egoi_sms_order_tracking_name").attr('disabled',false);
                $("#add_egoi_sms_order_tracking_url").attr('disabled',false);
                break;
            case 'remove':
                $( "#"+area.id ).removeClass("loading");
                $( "#"+area.id ).html("x");
                break;
        }
    };

    function activeConfigTab(tag) {
        $(".nav-tab-addon").each(function () {
            $(this).attr("class", "nav-tab nav-tab-addon");
        });
        $(tag).attr("class", "nav-tab nav-tab-addon nav-tab-active");
    }

    function showConfigWrap(wrap) {
        $(".wrap-addon").each(function () {
            $(this).hide();
        });
        $(wrap).show();
    }

    function disableAllSmsOrderTexts(languages, methods) {
        languages.forEach(function (lang) {
            $("#sms_order_texts_"+lang).hide();
            $("#sms_order_texts_"+lang+" :input").attr("disabled", true);
        });

        methods.forEach(function (method) {
            $("#sms_order_payment_texts_"+method).hide();
            $("#sms_order_payment_texts_"+method+" :input").attr("disabled", true);
        });

		$("#sms_texts_tags").hide();
        $("#sms_payment_texts_tags").hide();
    }

    function enableSmsOrderText(language, method) {
        if (language) {
            $("#sms_order_texts_" + language).show();
            $("#sms_order_texts_" + language + " :input").attr("disabled", false);
            $("#sms_texts_tags").show();
        }
        if (method) {
            $("#sms_order_payment_texts_" + method).show();
            if (method !== 'billet') {
                $("#sms_order_payment_texts_" + method + " :input").attr("disabled", false);
            } else {
                $("#sms_order_payment_texts_" + method + " :input[name='egoi_sms_order_payment_text_pt_BR']").attr('disabled', false);
                $("#sms_order_payment_texts_" + method + " :input[name='egoi_sms_order_reminder_text_pt_BR']").attr('disabled', false);
                $("#sms_order_payment_texts_" + method + " :input[type='submit']").attr('disabled', false);
            }
            $("#sms_payment_texts_tags").show();
        }
    }

    function disableAllEmailOrderTexts(methods) {

        methods.forEach(function (method) {
            $("#email_order_payment_texts_"+method).hide();
            $("#email_order_payment_texts_"+method+" :input").attr("disabled", true);
        });

        $("#email_payment_texts_tags").hide();
    }

    function enableEmailOrderText(method) {

        if (method) {
            $("#email_order_payment_texts_" + method).show();
            if (method !== 'billet') {
                $("#email_order_payment_texts_" + method + " :input").attr("disabled", false);
            } else {
                $("#email_order_payment_texts_" + method + " :input[name='egoi_sms_order_reminder_email_text_pt_BR']").attr('disabled', false);
                $("#email_order_payment_texts_" + method + " :input[type='submit']").attr('disabled', false);
            }
            $("#email_payment_texts_tags").show();
        }
    }

    function toggleAdminOrders() {
        var admin_phone = $("#admin_phone").val();
        if (admin_phone.length >= 6) {
            $(".admin-order-status").prop("disabled", false);
        } else {
            $(".admin-order-status").prop("disabled", true);
        }
    }


})( jQuery );

(function ($) {
    $.fn.getCursorPosition = function() {
        var el = $(this).get(0);
        var pos = 0;
        if('selectionStart' in el) {
            pos = el.selectionStart;
        } else if('selection' in document) {
            el.focus();
            var Sel = document.selection.createRange();
            var SelLength = document.selection.createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }
        return pos;
    }
})(jQuery);