jQuery(document).ready(function(c){function n(e){var t=c("#"+e.data("target"));e.val()===e.data("value")?t.removeClass("form-control-hidden"):t.addClass("form-control-hidden")}function e(){"price"===c('input[name="pys[core][woo_event_value]"]:checked').val()?c(".woo-event-value-option").hide():c(".woo-event-value-option").show()}function t(){"price"===c('input[name="pys[core][edd_event_value]"]:checked').val()?c(".edd-event-value-option").hide():c(".edd-event-value-option").show()}function a(){var e=c("#pys_event_trigger_type").val(),t="#"+e+"_panel";c(".event_triggers_panel").hide(),c(t).show(),"page_visit"===e?c("#url_filter_panel").hide():c("#url_filter_panel").show();var n=c(t),a=n.data("trigger_type");0==c(".event_trigger",n).length-1&&s(n,a)}function s(e,t){var n=c(".event_trigger",e),a=c(n[0]).clone(!0),s=c(n[n.length-1]).data("trigger_id")+1,i="pys[event]["+t+"_triggers]["+s+"]";a.data("trigger_id",s),c("select",a).attr("name",i+"[rule]"),c("input",a).attr("name",i+"[value]"),a.css("display","block"),a.insertBefore(c(".insert-marker",e))}function i(){"page_visit"===c("#pys_event_trigger_type").val()?c(".event-delay").css("visibility","visible"):c(".event-delay").css("visibility","hidden")}function o(){c("#pys_event_facebook_enabled").is(":checked")?c("#facebook_panel").show():c("#facebook_panel").hide()}function _(){"CustomEvent"===c("#pys_event_facebook_event_type").val()?c(".facebook-custom-event-type").css("visibility","visible"):c(".facebook-custom-event-type").css("visibility","hidden")}function r(){c("#pys_event_facebook_params_enabled").is(":checked")?c("#facebook_params_panel").show():c("#facebook_params_panel").hide()}function p(){var e=c("#pys_event_facebook_event_type").val();c("#facebook_params_panel").removeClass().addClass(e)}function l(){"custom"===c("#pys_event_facebook_params_currency").val()?c(".facebook-custom-currency").css("visibility","visible"):c(".facebook-custom-currency").css("visibility","hidden")}function v(){c("#pys_event_pinterest_enabled").is(":checked")?c("#pinterest_panel").show():c("#pinterest_panel").hide()}function d(){"CustomEvent"===c("#pys_event_pinterest_event_type").val()?c(".pinterest-custom-event-type").css("visibility","visible"):c(".pinterest-custom-event-type").css("visibility","hidden")}function u(){c("#pys_event_pinterest_params_enabled").is(":checked")?c("#pinterest_params_panel").show():c("#pinterest_params_panel").hide()}function m(){var e=c("#pys_event_pinterest_event_type").val();c("#pinterest_params_panel").removeClass().addClass(e)}function y(){"custom"===c("#pys_event_pinterest_params_currency").val()?c(".pinterest-custom-currency").css("visibility","visible"):c(".pinterest-custom-currency").css("visibility","hidden")}function g(){c("#pys_event_ga_enabled").is(":checked")?c("#analytics_panel").show():c("#analytics_panel").hide()}function h(){c("#pys_event_google_ads_enabled").is(":checked")?c("#google_ads_panel").show():c("#google_ads_panel").hide()}function b(){"_custom"===c("#pys_event_google_ads_event_action").val()?c("#pys_event_google_ads_custom_event_action").css("visibility","visible"):c("#pys_event_google_ads_custom_event_action").css("visibility","hidden")}function k(){c("#pys_event_bing_enabled").is(":checked")?c("#bing_panel").show():c("#bing_panel").hide()}c(function(){c('[data-toggle="pys-popover"]').popover({container:"#pys",html:!0,content:function(){return c("#pys-"+c(this).data("popover_id")).html()}})}),c(".pys-select2").select2(),c(".pys-tags-select2").select2({tags:!0,tokenSeparators:[","," "]}),c("select.controls-visibility").on("change",function(e){n(c(this))}).each(function(e,t){n(c(t))}),c(".card-collapse").on('click',function(){var e=c(this).closest(".card").find(".card-body");e.hasClass("show")?e.hide().removeClass("show"):e.show().addClass("show")}),c(".collapse-control .custom-switch-input").on('change',function(){var e=c(this),t=c("."+e.data("target"));0<t.length&&(e.prop("checked")?t.show():t.hide())}).trigger("change"),e(),c('input[name="pys[core][woo_event_value]"]').on('change',function(){e()}),t(),c('input[name="pys[core][edd_event_value]"]').on('change',function(){t()}),c("#pys_select_all_events").on('change',function(){c(this).prop("checked")?c(".pys-select-event").prop("checked","checked"):c(".pys-select-event").prop("checked",!1)}),i(),a(),c("#pys_event_trigger_type").on('change',function(){i(),a()}),c(".add-event-trigger").on('click',function(){var e=c(this).closest(".event_triggers_panel"),t=e.data("trigger_type");s(e,t)}),c(".add-url-filter").on('click',function(){s(c(this).closest(".event_triggers_panel"),"url_filter")}),c(".remove-row").on('click',function(e){c(this).closest(".row.event_trigger, .row.facebook-custom-param, .row.pinterest-custom-param, .row.google_ads-custom-param").remove()}),o(),_(),r(),p(),l(),c("#pys_event_facebook_enabled").on('click',function(){o()}),c("#pys_event_facebook_event_type").on('change',function(){_(),p()}),c("#pys_event_facebook_params_enabled").on('click',function(){r()}),c("#pys_event_facebook_params_currency").on('change',function(){l()}),c(".add-facebook-parameter").on('click',function(){var e=c("#facebook_params_panel"),t=c(".facebook-custom-param",e),n=c(t[0]).clone(!0),a=c(t[t.length-1]).data("param_id")+1,s="pys[event][facebook_custom_params]["+a+"]";n.data("param_id",a),c("input.custom-param-name",n).attr("name",s+"[name]"),c("input.custom-param-value",n).attr("name",s+"[value]"),n.css("display","flex"),n.insertBefore(c(".insert-marker",e))}),v(),d(),u(),m(),y(),c("#pys_event_pinterest_enabled").on('click',function(){v()}),c("#pys_event_pinterest_event_type").on('change',function(){d(),m()}),c("#pys_event_pinterest_params_enabled").on('click',function(){u()}),c("#pys_event_pinterest_params_currency").on('change',function(){y()}),c(".add-pinterest-parameter").on('click',function(){var e=c("#pinterest_params_panel"),t=c(".pinterest-custom-param",e),n=c(t[0]).clone(!0),a=c(t[t.length-1]).data("param_id")+1,s="pys[event][pinterest_custom_params]["+a+"]";n.data("param_id",a),c("input.custom-param-name",n).attr("name",s+"[name]"),c("input.custom-param-value",n).attr("name",s+"[value]"),n.css("display","flex"),n.insertBefore(c(".insert-marker",e))}),g(),c("#pys_event_ga_enabled").on('click',function(){g()}),h(),b(),c("#pys_event_google_ads_enabled").on('click',function(){h()}),c("#pys_event_google_ads_event_action").on('change',function(){b()}),c(".add-google_ads-parameter").on('click',function(){var e=c("#google_ads_params_panel"),t=c(".google_ads-custom-param",e),n=c(t[0]).clone(!0),a=c(t[t.length-1]).data("param_id")+1,s="pys[event][google_ads_custom_params]["+a+"]";n.data("param_id",a),c("input.custom-param-name",n).attr("name",s+"[name]"),c("input.custom-param-value",n).attr("name",s+"[value]"),n.css("display","flex"),n.insertBefore(c(".insert-marker",e))}),k(),c("#pys_event_bing_enabled").on('click',function(){k()})});


jQuery( document ).ready(function($) {


    checkStepActive();
    calculateStepsNums();

    $('.woo_initiate_checkout_enabled input[type="checkbox"]').on('change',function() {
        checkStepActive()
    });
    $('.checkout_progress input[type="checkbox"]').on('change',function () {
        calculateStepsNums();
    });

    function calculateStepsNums() {
        var step = 2;
        $('.checkout_progress').each(function (index,value) {
            if($(value).find("input:checked").length > 0) {
                $(value).find(".step").text("STEP "+step+": ");
                step++;
            } else {
                $(value).find(".step").text("");
            }
        });
    }

    function checkStepActive() {
        if($('.woo_initiate_checkout_enabled input[type="checkbox"]').is(':checked')) {
            $('.checkout_progress .custom-switch').removeClass("disabled");
            $('.checkout_progress input[type="checkbox"]').removeAttr("disabled");
            $('.woo_initiate_checkout_enabled .step').text("STEP 1:");
        } else {
            $('.checkout_progress input').prop('checked',false);
            $('.checkout_progress .custom-switch').addClass("disabled");
            $('.checkout_progress input[type="checkbox"]').attr("disabled","disabled");
            $('.woo_initiate_checkout_enabled .step').text("");
        }
        calculateStepsNums();
    }
    updatePurchaseFDPValue($("#pys_facebook_fdp_purchase_event_fire"));
    $("#pys_facebook_fdp_purchase_event_fire").on('change',function () {

        updatePurchaseFDPValue(this);
    });

    updateAddToCartFDPValue($("#pys_facebook_fdp_add_to_cart_event_fire"));
    $("#pys_facebook_fdp_add_to_cart_event_fire").on('change',function () {

        updateAddToCartFDPValue(this);
    });
    updatePostEventFields();
    $("#pys_event_trigger_type").on('change',function(){
        updatePostEventFields();
    });

    $(".action_old,.action_g4").on('change',function () {
        var value = $(this).val();
        $(".ga-custom-param-list").html("");
        $(".ga-param-list").html("");

        for(i=0;i<ga_fields.length;i++){
            if(ga_fields[i].name == value) {
                ga_fields[i].fields.forEach(function(el){
                    $(".ga-param-list").append('<div class="row mb-3 ga_param">\n' +
                            '<label class="col-5 control-label">'+el+'</label>' +
                            '<div class="col-4">' +
                                '<input type="text" name="pys[event][ga_params]['+el+']" class="form-control">' +
                            '</div>' +
                        ' </div>');
                });
                break;
            }
        }

        if($('option:selected', this).attr('group') == "Retail/Ecommerce") {
            $(".ga_woo_info").attr('style',"display: block");
        } else {
            $(".ga_woo_info").attr('style',"display: none");
        }
        updateGAActionSelector();
    })
    updateGAActionSelector();

    function updateGAActionSelector() {
        if($('.action_g4').length > 0) {
            if($('.action_old').val() === "_custom" || $('.action_old').val() === "CustomEvent") {
                $('#ga-custom-action_old').css('display','block')
            } else {
                $('#ga-custom-action_old').css('display','none')
            }
            if($('.action_g4').val() === "_custom" || $('.action_g4').val() === "CustomEvent") {
                $('#ga-custom-action_g4').css('display','block');
            } else {
                $('#ga-custom-action_g4').css('display','none')
            }
        }

    }

    $('.ga-custom-param-list').on("click",'.ga-custom-param .remove-row',function(){
       $(this).parents('.ga-custom-param').remove();
    });

    $('.add-ga-custom-parameter').on('click',function(){
        var index = $(".ga-custom-param-list .ga-custom-param").length + 1;
        $(".ga-custom-param-list").append('<div class="row mt-3 ga-custom-param" data-param_id="'+index+'">' +
            '<div class="col">' +
                '<div class="row">' +
                    '<div class="col-1"></div>' +
                        '<div class="col-4">' +
                            '<input type="text" placeholder="Enter name" class="form-control custom-param-name"' +
                                ' name="pys[event][ga_custom_params]['+index+'][name]"' +
                                ' value="">' +
                        '</div>' +
                        '<div class="col-4">' +
                            '<input type="text" placeholder="Enter value" class="form-control custom-param-value"' +
                                ' name="pys[event][ga_custom_params]['+index+'][value]"' +
                                ' value="">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<button type="button" class="btn btn-sm remove-row">' +
                                '<i class="fa fa-trash-o" aria-hidden="true"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>');
    });

    $("#import_events_file").on('change',function(){
        var fd = new FormData();
        fd.append("action","pys_import_events");
        fd.append($(this).attr("name"), $(this).prop('files')[0]);

        $.ajax({
            url: ajaxurl,
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (data) {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.data)
                }

            },error:function (data) {
                console.log(data);
            }
        });
    });

    function updatePostEventFields() {
        if($("#pys_event_trigger_type").val() == "post_type") {
            $(".event-delay").css("visibility","visible");
            $(".triger_post_type").show();
            $("#url_filter_panel").hide();
            $(".post_type_error").show();
        } else {
            $(".post_type_error").hide();
            $(".triger_post_type").hide();
        }
    }
    function updateAddToCartFDPValue(input) {
        if($(input).val() == "scroll_pos") {
            $("#fdp_add_to_cart_event_fire_scroll_block").show();
            $("#pys_facebook_fdp_add_to_cart_event_fire_css").hide()
        } else  if($(input).val() == "css_click") {
            $("#fdp_add_to_cart_event_fire_scroll_block").hide();
            $("#pys_facebook_fdp_add_to_cart_event_fire_css").show()
        } else {
            $("#fdp_add_to_cart_event_fire_scroll_block").hide();
            $("#pys_facebook_fdp_add_to_cart_event_fire_css").hide()
        }
    }
    function updatePurchaseFDPValue(input) {
        if($(input).val() == "scroll_pos") {
            $("#fdp_purchase_event_fire_scroll_block").show();
            $("#pys_facebook_fdp_purchase_event_fire_css").hide()
        } else  if($(input).val() == "css_click") {
            $("#fdp_purchase_event_fire_scroll_block").hide();
            $("#pys_facebook_fdp_purchase_event_fire_css").show()
        } else {
            $("#fdp_purchase_event_fire_scroll_block").hide();
            $("#pys_facebook_fdp_purchase_event_fire_css").hide()
        }
    }
    function updateGAFields() {
        if($("#pys_event_ga_pixel_id").val().indexOf('G') === 0) {
            $('.col.g4').css('display','block');
            $('.col.old_g').css('display','none');
            $('.action_old').attr('name','')
            $('.action_g4').attr('name','pys[event][ga_event_action]')
            $('#ga-custom-action_old input').attr('name','')
            $('#ga-custom-action_g4 input').attr('name','pys[event][ga_custom_event_action]')
        } else {
            $('.col.g4').css('display','none');
            $('.col.old_g').css('display','block');
            $('.action_g4').attr('name','')
            $('.action_old').attr('name','pys[event][ga_event_action]')
            $('#ga-custom-action_old input').attr('name','pys[event][ga_custom_event_action]')
            $('#ga-custom-action_g4 input').attr('name','')
        }

    }
    if($("#pys_event_ga_pixel_id").length >0) {
        $("#pys_event_ga_pixel_id").on('change',function () {
            updateGAFields()
        })
        updateGAFields();
    }


    $(document).on('change','.ga_tracking_id,#pys_ga_tracking_id_0',function(){
        let text = 'We identified this tag as a Google Analytics Universal property.'
        if($(this).val().indexOf('G') === 0) {
            text = 'We identified this tag as a GA4 property.';
        }
        $(this).next().text(text);
    });

    function renderField(data,wrapClass="row mb-3",labelClass="col-5 control-label") {
        if(data.type === "input") {
            return '<div class="'+wrapClass+'">' +
                '<label class="'+labelClass+'">'+data.label+'</label>' +
                '<div class="col-4">' +
                '<input type="text" name="'+data.name+'" value="" placeholder="" class="form-control">' +
                '</div></div>';
        }
    }

    /**
     * TikTok Edit Event
     */
    if($('#pys_event_tiktok_event_type').length > 0) {
        $('#pys_event_tiktok_event_type').on('change',function(){
            updateTikTokEventParamsFrom()
        })

        $('#pys_event_tiktok_params_enabled').on('change',function(){
            updateTiktokParamFormVisibility()
        })

        if($('#pys_event_tiktok_event_type').val() === 'CustomEvent') {
            $('.tiktok-custom-event-type').css('display','block')
        } else {
            $('.tiktok-custom-event-type').css('display','none')
        }
        updateTiktokParamFormVisibility();

        $('.tiktok-custom-param-list').on("click",'.tiktok-custom-param .remove-row',function(){
            $(this).parents('.tiktok-custom-param').remove();
        });

        $('.add-tiktok-custom-parameter').on('click',function(){
            var index = $(".tiktok-custom-param-list .tiktok-custom-param").length + 1;
            $(".tiktok-custom-param-list").append('<div class="row mt-3 tiktok-custom-param" data-param_id="'+index+'">' +
                '<div class="col">' +
                '<div class="row">' +
                '<div class="col-1"></div>' +
                '<div class="col-4">' +
                '<input type="text" placeholder="Enter name" class="form-control custom-param-name"' +
                ' name="pys[event][tiktok_custom_params]['+index+'][name]"' +
                ' value="">' +
                '</div>' +
                '<div class="col-4">' +
                '<input type="text" placeholder="Enter value" class="form-control custom-param-value"' +
                ' name="pys[event][tiktok_custom_params]['+index+'][value]"' +
                ' value="">' +
                '</div>' +
                '<div class="col-2">' +
                '<button type="button" class="btn btn-sm remove-row">' +
                '<i class="fa fa-trash-o" aria-hidden="true"></i>' +
                '</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>');
        });
    }

    function updateTikTokEventParamsFrom() {
        let $select = $('#pys_event_tiktok_event_type');
        if($select.length === 0) return;
        let $panel = $('#tiktok_params_panel .standard');
        let $custom = $('.tiktok-custom-event-type');

        $panel.html('')
        if($select.val() === 'CustomEvent') {
            $custom.css('display','block')
        } else {
            $custom.css('display','none')
            let fields = $select.find(":selected").data('fields')
            fields.forEach(function (item) {
                $panel.append(renderField(item))
            })
        }
    }

    function updateTiktokParamFormVisibility() {
        if($('#pys_event_tiktok_params_enabled:checked').length > 0) {
            $('#tiktok_params_panel').css('display','block')
        } else {
            $('#tiktok_params_panel').css('display','none')
        }
    }
    
    function updateTikTokPanelVisibility() {
        if($("#pys_event_tiktok_enabled").is(":checked")) {
            $("#tiktok_panel").show()
        }
        else {
            $("#tiktok_panel").hide()
        }
    }
    updateTikTokPanelVisibility()
    $("#pys_event_tiktok_enabled").on('click',function(){updateTikTokPanelVisibility()})





});


