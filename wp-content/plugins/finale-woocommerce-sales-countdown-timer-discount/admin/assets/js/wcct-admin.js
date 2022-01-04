var wcct_admin_change_content = null;
var current_pro_slug = null;
var xl_tab_clicked_old = jQuery('.cmb-tab-active').data('panel');
jQuery(document).ready(function ($) {
    'use strict';
    /**
     * Set up the functionality for CMB2 conditionals.
     */
    window.WCCT_CMB2ConditionalsInit = function (changeContext, conditionContext) {
        var loopI, requiredElms, uniqueFormElms, formElms;

        if ('undefined' === typeof changeContext) {
            changeContext = 'body';
        }
        changeContext = $(changeContext);

        if ('undefined' === typeof conditionContext) {
            conditionContext = 'body';
        }
        conditionContext = $(conditionContext);
        window.wcct_admin_change_content = conditionContext;
        changeContext.on('change', 'input, textarea, select', function (evt) {
            var elm = $(this),
                fieldName = $(this).attr('name'),
                dependants,
                dependantsSeen = [],
                checkedValues,
                elmValue;

            var dependants = $('[data-wcct-conditional-id="' + fieldName + '"]', conditionContext);
            if (!elm.is(":visible")) {
                return;
            }

            // Only continue if we actually have dependants.
            if (dependants.length > 0) {

                // Figure out the value for the current element.
                if ('checkbox' === elm.attr('type')) {
                    checkedValues = $('[name="' + fieldName + '"]:checked').map(function () {
                        return this.value;
                    }).get();
                } else if ('radio' === elm.attr('type')) {
                    if ($('[name="' + fieldName + '"]').is(':checked')) {
                        elmValue = elm.val();
                    }
                } else {
                    elmValue = evt.currentTarget.value;
                }

                dependants.each(function (i, e) {
                    var loopIndex = 0,
                        current = $(e),
                        currentFieldName = current.attr('name'),
                        requiredValue = current.data('wcct-conditional-value'),
                        currentParent = current.parents('.cmb-row:first'),
                        shouldShow = false;


                    // Only check this dependant if we haven't done so before for this parent.
                    // We don't need to check ten times for one radio field with ten options,
                    // the conditionals are for the field, not the option.
                    if ('undefined' !== typeof currentFieldName && '' !== currentFieldName && $.inArray(currentFieldName, dependantsSeen) < 0) {
                        dependantsSeen.push = currentFieldName;

                        if ('checkbox' === elm.attr('type')) {
                            if ('undefined' === typeof requiredValue) {
                                shouldShow = (checkedValues.length > 0);
                            } else if ('off' === requiredValue) {
                                shouldShow = (0 === checkedValues.length);
                            } else if (checkedValues.length > 0) {
                                if ('string' === typeof requiredValue) {
                                    shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                                } else if (Array.isArray(requiredValue)) {
                                    for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                        if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                            shouldShow = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        } else if ('undefined' === typeof requiredValue) {
                            shouldShow = (elm.val() ? true : false);
                        } else {
                            if ('string' === typeof requiredValue) {
                                shouldShow = (elmValue === requiredValue);
                            }
                            if ('number' === typeof requiredValue) {
                                shouldShow = (elmValue == requiredValue);
                            } else if (Array.isArray(requiredValue)) {
                                shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                            }
                        }

                        // Handle any actions necessary.
                        currentParent.toggle(shouldShow);

                        window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);

                        if (current.data('conditional-required')) {
                            current.prop('required', shouldShow);
                        }

                        // If we're hiding the row, hide all dependants (and their dependants).
                        if (false === shouldShow) {
                            // CMB2ConditionalsRecursivelyHideDependants(currentFieldName, current, conditionContext);
                        }

                        // If we're showing the row, check if any dependants need to become visible.
                        else {
                            if (1 === current.length) {
                                current.trigger('change');
                            } else {
                                current.filter(':checked').trigger('change');
                            }
                        }
                    } else {
                        /** Handling for */
                        if (current.hasClass("xl-cmb2-tabs") || current.hasClass("cmb2-wcct_html")) {


                            if ('checkbox' === elm.attr('type')) {
                                if ('undefined' === typeof requiredValue) {
                                    shouldShow = (checkedValues.length > 0);
                                } else if ('off' === requiredValue) {
                                    shouldShow = (0 === checkedValues.length);
                                } else if (checkedValues.length > 0) {
                                    if ('string' === typeof requiredValue) {
                                        shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                                    } else if (Array.isArray(requiredValue)) {
                                        for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                            if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                                shouldShow = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else if ('undefined' === typeof requiredValue) {
                                shouldShow = (elm.val() ? true : false);
                            } else {
                                if ('string' === typeof requiredValue) {
                                    shouldShow = (elmValue === requiredValue);
                                }
                                if ('number' === typeof requiredValue) {
                                    shouldShow = (elmValue == requiredValue);
                                } else if (Array.isArray(requiredValue)) {
                                    shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                                }
                            }

                            currentParent.toggle(shouldShow);
                            window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);


                        } else if (current.hasClass("wcct_custom_wrapper_group") || current.hasClass("wcct_custom_wrapper_wysiwyg")) {
                            if ('checkbox' === elm.attr('type')) {
                                if ('undefined' === typeof requiredValue) {
                                    shouldShow = (checkedValues.length > 0);
                                } else if ('off' === requiredValue) {
                                    shouldShow = (0 === checkedValues.length);
                                } else if (checkedValues.length > 0) {
                                    if ('string' === typeof requiredValue) {
                                        shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                                    } else if (Array.isArray(requiredValue)) {
                                        for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                            if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                                shouldShow = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else if ('undefined' === typeof requiredValue) {
                                shouldShow = (elm.val() ? true : false);
                            } else {
                                if ('string' === typeof requiredValue) {
                                    shouldShow = (elmValue === requiredValue);
                                }
                                if ('number' === typeof requiredValue) {
                                    shouldShow = (elmValue == requiredValue);
                                } else if (Array.isArray(requiredValue)) {
                                    shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                                }
                            }

                            current.toggle(shouldShow);
                            window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);


                        }
                    }
                });
            }

            if (elm.hasClass('wcct_icon_select')) {
                var ecomm_font_icon_val = $(this).val();
                if (ecomm_font_icon_val > 0) {
                    elm.next('.wcct_icon_preview').html('<i class="wcct_custom_icon wcct-ecommerce' + wcct_return_font_val(ecomm_font_icon_val) + '"></i>');
                }
            }
        });

        window.wcct_admin_change_content.on("wcct_conditional_runs", function (e, current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue) {

            var loopIndex = 0;
            var checkedValues;
            var shouldShow = false;
            if (typeof current.attr('data-wcct-conditional-value') == "undefined") {
                return;
            }


            elm = $("[name='" + current.attr('data-wcct-conditional-id') + "']", changeContext).eq(0);


            if (!elm.is(":visible")) {

                return;
            }
            // Figure out the value for the current element.
            if ('checkbox' === elm.attr('type')) {
                checkedValues = $('[name="' + current.attr('data-wcct-conditional-id') + '"]:checked').map(function () {
                    return this.value;
                }).get();
            } else if ('radio' === elm.attr('type')) {
                elmValue = $('[name="' + current.attr('data-wcct-conditional-id') + '"]:checked').val();

            }

            requiredValue = current.data('wcct-conditional-value');

            // Only check this dependant if we haven't done so before for this parent.
            // We don't need to check ten times for one radio field with ten options,
            // the conditionals are for the field, not the option.
            if ('undefined' !== typeof currentFieldName && '' !== currentFieldName) {


                if ('checkbox' === elm.attr('type')) {
                    if ('undefined' === typeof requiredValue) {
                        shouldShow = (checkedValues.length > 0);
                    } else if ('off' === requiredValue) {
                        shouldShow = (0 === checkedValues.length);
                    } else if (checkedValues.length > 0) {
                        if ('string' === typeof requiredValue) {
                            shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                        } else if (Array.isArray(requiredValue)) {
                            for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                    shouldShow = true;
                                    break;
                                }
                            }
                        }
                    }
                } else if ('undefined' === typeof requiredValue) {
                    shouldShow = (elm.val() ? true : false);
                } else {

                    if ('string' === typeof requiredValue) {
                        shouldShow = (elmValue === requiredValue);
                    }
                    if ('number' === typeof requiredValue) {
                        shouldShow = (elmValue == requiredValue);
                    } else if (Array.isArray(requiredValue)) {

                        shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                    }
                }

                // Handle any actions necessary.
                currentParent.toggle(shouldShow);


                window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);

                if (current.data('conditional-required')) {
                    current.prop('required', shouldShow);
                }

                // If we're hiding the row, hide all dependants (and their dependants).
                if (false === shouldShow) {
                    // CMB2ConditionalsRecursivelyHideDependants(currentFieldName, current, conditionContext);
                }

                // If we're showing the row, check if any dependants need to become visible.
                else {
                    if (1 === current.length) {
                        current.trigger('change');
                    } else {
                        current.filter(':checked').trigger('change');
                    }
                }
            } else {


                if (current.hasClass("xl-cmb2-tabs") || current.hasClass("cmb2-wcct_html")) {


                    if ('checkbox' === elm.attr('type')) {
                        if ('undefined' === typeof requiredValue) {
                            shouldShow = (checkedValues.length > 0);
                        } else if ('off' === requiredValue) {
                            shouldShow = (0 === checkedValues.length);
                        } else if (checkedValues.length > 0) {
                            if ('string' === typeof requiredValue) {
                                shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                            } else if (Array.isArray(requiredValue)) {
                                for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                    if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                        shouldShow = true;
                                        break;
                                    }
                                }
                            }
                        }
                    } else if ('undefined' === typeof requiredValue) {
                        shouldShow = (elm.val() ? true : false);
                    } else {
                        if ('string' === typeof requiredValue) {
                            shouldShow = (elmValue === requiredValue);
                        }
                        if ('number' === typeof requiredValue) {
                            shouldShow = (elmValue == requiredValue);
                        } else if (Array.isArray(requiredValue)) {
                            shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                        }
                    }

                    currentParent.toggle(shouldShow);
                    window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);


                } else if (current.hasClass("wcct_custom_wrapper_group") || current.hasClass("wcct_custom_wrapper_wysiwyg")) {
                    if ('checkbox' === elm.attr('type')) {
                        if ('undefined' === typeof requiredValue) {
                            shouldShow = (checkedValues.length > 0);
                        } else if ('off' === requiredValue) {
                            shouldShow = (0 === checkedValues.length);
                        } else if (checkedValues.length > 0) {
                            if ('string' === typeof requiredValue) {
                                shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                            } else if (Array.isArray(requiredValue)) {
                                for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                    if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                        shouldShow = true;
                                        break;
                                    }
                                }
                            }
                        }
                    } else if ('undefined' === typeof requiredValue) {
                        shouldShow = (elm.val() ? true : false);
                    } else {
                        if ('string' === typeof requiredValue) {
                            shouldShow = (elmValue === requiredValue);
                        }
                        if ('number' === typeof requiredValue) {
                            shouldShow = (elmValue == requiredValue);
                        } else if (Array.isArray(requiredValue)) {
                            shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                        }
                    }

                    current.toggle(shouldShow);
                    window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);

                }
            }
        });

        $('[data-wcct-conditional-id]', conditionContext).not(".wcct_custom_wrapper_group").parents('.cmb-row:first').hide({
            "complete": function () {
                $("body").trigger("wcct_w_trigger_conditional_on_load");

                uniqueFormElms = [];
                $(':input', changeContext).each(function (i, e) {
                    var elmName = $(e).attr('name');
                    if ('undefined' !== typeof elmName && '' !== elmName && -1 === $.inArray(elmName, uniqueFormElms)) {
                        uniqueFormElms.push(elmName);
                    }
                });

                for (loopI = 0; loopI < uniqueFormElms.length; loopI++) {
                    formElms = $('[name="' + uniqueFormElms[loopI] + '"]');
                    if (1 === formElms.length || !formElms.is(':checked')) {
                        formElms.trigger('change');
                    } else {
                        formElms.filter(':checked').trigger('change');
                    }
                }

            }
        });
        $(document).on('wcct_cmb2_options_tabs_activated', function (e, panel) {
            var uniqueFormElms = [];
            $(':input', ".cmb-tab-panel").each(function (i, e) {
                var elmName = $(e).attr('name');
                if ('undefined' !== typeof elmName && '' !== elmName && -1 === $.inArray(elmName, uniqueFormElms) && $(e).is(":visible")) {
                    uniqueFormElms.push(elmName);
                }
            });
            for (loopI = 0; loopI < uniqueFormElms.length; loopI++) {
                formElms = $('[name="' + uniqueFormElms[loopI] + '"]');
                if (1 === formElms.length || !formElms.is(':checked')) {
                    formElms.trigger('change');
                } else {
                    formElms.filter(':checked').trigger('change');
                }
            }
        });

        $(document).on('wcct_acc_toggled', function (e, elem) {
            var uniqueFormElms = [];
            $(':input', ".cmb-tab-panel").each(function (i, e) {
                var elmName = $(e).attr('name');
                if ('undefined' !== typeof elmName && '' !== elmName && -1 === $.inArray(elmName, uniqueFormElms) && $(e).is(":visible")) {
                    uniqueFormElms.push(elmName);
                }
            });
            for (loopI = 0; loopI < uniqueFormElms.length; loopI++) {
                formElms = $('[name="' + uniqueFormElms[loopI] + '"]');
                if (1 === formElms.length || !formElms.is(':checked')) {
                    formElms.trigger('change');
                } else {
                    formElms.filter(':checked').trigger('change');
                }
            }
        });
    }

    if (typeof pagenow !== "undefined" && "wcct_countdown" == pagenow) {
        WCCTCMB2ConditionalsInit('#post .cmb2-wrap.wcct_options_common', '#post .cmb2-wrap.wcct_options_common');
        WCCT_CMB2ConditionalsInit('#post .cmb2-wrap.wcct_options_common', '#post  .cmb2-wrap.wcct_options_common');
    }

    $('.wcct_global_option .wcct_options_page_left_wrap').removeClass('dispnone');

    $(window).on("load", function () {
        $("body").on("click", ".cmb2_wcct_acc_head", function () {


            var currentOpened = $(this).parent(".cmb2_wcct_wrapper_ac").attr('data-slug');


            if (buy_pro_helper.proacc.indexOf(currentOpened) !== -1) {
                show_modal_pro(currentOpened);
                return false;
            }


            if ($(this).hasClass("active")) {
                $(this).next(".cmb2_wcct_wrapper_ac_data").toggle(false);
                $(this).parents(".cmb2_wcct_wrapper_ac").removeClass('opened');
            } else {
                $(this).next(".cmb2_wcct_wrapper_ac_data").toggle(true);
                $(this).parents(".cmb2_wcct_wrapper_ac").addClass('opened');
            }
            $(this).toggleClass("active");
            $(document).trigger("wcct_acc_toggled", [this]);
        });

        if ($("select.wcct_icon_select").length > 0) {
            $("select.wcct_icon_select").each(function () {
                $(this).trigger("change");
            });
        }

        $("body").on("click", ".wcct_detect_checkbox_change input[type='checkbox']", function () {
            var $this = $(this);
            var $wrap = $(this).parents(".wcct_detect_checkbox_change");
            if ($wrap.hasClass("wcct_gif_location")) {
                $(".wcct_load_spin.wcct_load_tab_location").addClass("wcct_load_active");
                setTimeout(function () {
                    $(".wcct_load_spin.wcct_load_tab_location").removeClass("wcct_load_active");
                }, 2000);
            }
            if ($wrap.hasClass("wcct_gif_appearance")) {
                $(".wcct_load_spin.wcct_load_tab_appearance").addClass("wcct_load_active");
                setTimeout(function () {
                    $(".wcct_load_spin.wcct_load_tab_appearance").removeClass("wcct_load_active");
                }, 2000);
            }
        });
        $("body").on("click", ".wcct_detect_radio_change input[type='radio']", function () {
            var $this = $(this);
            var $wrap = $(this).parents(".wcct_detect_radio_change");
            if ($wrap.hasClass("wcct_gif_appearance")) {
                $(".wcct_load_spin.wcct_load_tab_appearance").addClass("wcct_load_active");
                setTimeout(function () {
                    $(".wcct_load_spin.wcct_load_tab_appearance").removeClass("wcct_load_active");
                }, 2000);
            }
        });
        $("body").on("click", ".wcct_thickbox", function () {
            var $this = $(this), screenW = $(window).width(), screenH = $(window).height(), modalW = 1000, modalH = 350;
            var $container_id = $(this).attr("data-id");
            var $thickbox_title = $(this).attr("data-title");

            if (screenW < 1000) {
                modalW = parseInt(screenW * 0.8);
            }
            if (screenH < 350) {
                modalH = parseInt(screenH * 0.8);
            }
            if ($("#" + $container_id).length > 0) {
                tb_show($thickbox_title, '#TB_inline?width=' + modalW + '&height=' + modalH + '&inlineId=' + $container_id, false);
                return false;
            }
        });

        setTimeout(function () {
            $(".cmb2-id--wcct-wrap-tabs").remove();
        }, 2000);
        // $("#post").on("wcct_conditional_runs", function (e, current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue) {
        //     console.log(current.id == "_wcct_data_wcct_sales_count_from_date");
        //
        // });
        // $("#wcct_global_option_metaboox").on("wcct_conditional_runs", function (e, current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue) {
        //
        //
        //     if (currentParent.hasClass("wcct_field_date_range")) {
        //
        //         currentParent.parents(".order_date_outer_wrapper").find(".order_label").eq(0).toggle(shouldShow);
        //
        //
        //         currentParent.parents(".order_date_outer_wrapper").find(".order_label").eq(0).toggle(shouldShow);
        //     }
        //
        //
        // });
    });

    $(".cmb-row.wcct_radio_btn").find("input[type='radio']").on("change", function () {
        var $this = $(this);
        $this.parents("ul").find("li.radio-active").removeClass("radio-active");
        $this.parent("li").addClass("radio-active");
    });

    /** FUNCTIONS DECLARATION STARTS **/
    /**
     * Function to return font value
     * @param $icon_num
     * @returns {*}
     */
    function wcct_return_font_val($icon_num) {
        if ($icon_num.length === 3) {
            return $icon_num;
        } else if ($icon_num.length === 2) {
            return '0' + $icon_num;
        } else if ($icon_num.length === 1) {
            return '00' + $icon_num;
        } else {
            return '001';
        }
    }

    $("body").on("change", ".wcct_select_change select", function () {
        var $this = $(this);
        var groupParent = $this.parents(".postbox.cmb-row");
        var changeType = $this.attr("data-change")
        var actionElem = ".wcct_option_" + changeType;
        var finalHtml = '';
        if (changeType == "event_value") {
            // finalHtml = " <=";
            // if ($this.val() == "units_sold") {
            //     finalHtml = " >=";
            // }
        } else if (changeType == "entity") {
            if ($this.val() == "+") {
                finalHtml = "Adjust";
            } else if ($this.val() == "-") {
                finalHtml = "Adjust";
            } else if ($this.val() == "=") {
                finalHtml = "Assign";
            }
        }
        groupParent.find(actionElem).html(finalHtml);
    });

    $(document).on('wcct_cmb2_options_tabs_activated', function (e, tab) {
        var clicked_tab = '#' + tab;
        if (typeof buy_pro_helper != 'undefined') {
            var pro_tabs = buy_pro_helper.protabs;
            var is_pro_tab = jQuery.inArray(clicked_tab, pro_tabs);
            if (is_pro_tab != -1) {
                show_modal_pro(clicked_tab);
                jQuery('.cmb-tab-' + xl_tab_clicked_old).find("a").trigger("click");
                return false;
            } else {
                xl_tab_clicked_old = tab;
            }
        }
    });

    $(document).on('xl_cmb2_options_tabs_activated', function (e, event, ui) {


        if (buy_pro_helper.protabs.indexOf(jQuery(ui.newTab[0]).find("a").eq(0).attr("href")) !== -1) {


            e.preventDefault();
            e.stopPropagation();
            event.preventDefault();
            event.stopPropagation();


            show_modal_pro(jQuery(ui.newTab[0]).find("a").eq(0).attr("href"));
            jQuery(ui.oldTab[0]).find("a").trigger("click");
            return false;
        } else {
            if (jQuery(ui.newTab[0]).find("a").eq(0).attr("id")) {

                var d = new Date();
                d.setTime(d.getTime() + (5 * 60 * 1000));
                var expires = "expires=" + d.toUTCString();
                var postid = (jQuery("input#post_ID").val());
                document.cookie = "wcct_cook_post_tab_open_" + postid + "" + "=" + jQuery(ui.newTab[0]).find("a").eq(0).attr("id") + ";" + expires + ";path=/";

            }
        }


    });


    $(window).on('load', function (e) {

        var val;
        var postid = (jQuery("input#post_ID").val());
        var name = "wcct_cook_post_tab_open_" + postid + "";

        val = wcct_getCookie(name);

        if (val !== "") {
            jQuery('a#' + val).trigger('click');
        }

    });

    if ($("input[name='post_ID']").length > 0) {
        $.post(ajaxurl, {'ID': $("input[name='post_ID']").val(), 'action': 'wcct_quick_view_html'}, function (res) {
            $("#_wcct_qv_html").html(res);
        });
    }

    $(".wcct-features-note .notice-dismiss").click(function () {

        var get_cookie_name = $(".wcct-features-note").attr("data-cookie");
        var d = new Date(new Date().setFullYear(new Date().getFullYear() + 1));

        var expires = "expires=" + d.toUTCString();

        document.cookie = get_cookie_name + "" + "=yes;" + expires + ";path=/";

    });

});


function show_purchase_pop_on_change(ev, identifier, defaultVal) {
    if (defaultVal == 'campaign_type') {
        if (identifier.value == 'recurring' || identifier.value == 'evergreen') {
            ev.preventDefault();
            ev.stopPropagation();
            show_modal_pro(identifier.value);

            return false;
        }
    } else if (identifier.value == defaultVal) {
        ev.preventDefault();
        ev.stopPropagation();

        show_modal_pro(defaultVal);

        return false;
    }

}

function show_modal_pro(defaultVal) {

    current_pro_slug = defaultVal;
    current_pro_slug = current_pro_slug.replace("#", '');
    var defaults = {
        title: "",
        icon: 'dashicons dashicons-lock',
        content: "",
        confirmButton: buy_pro_helper.call_to_action_text,
        columnClass: 'modal-wide',
        closeIcon: true,
        confirm: function () {
            var replaced = buy_pro_helper.buy_now_link.replace("{current_slug}", current_pro_slug);
            window.open(replaced, '_blank');
        }
    };

    if (buy_pro_helper.popups[defaultVal] !== "undefined") {
        var data = buy_pro_helper.popups[defaultVal];

        data = jQuery.extend(true, {}, defaults, data);

    } else {
        var data = {};
        data = jQuery.extend(true, {}, defaults, data);
    }


    jQuery.xlAlert(data);
}


function wcct_show_tb(title, id) {
    wcct_modal_show(title, "#WCCT_MB_inline?height=500&amp;width=1000&amp;inlineId=" + id + "");
}

function wcct_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function wcct_manage_radio_active($) {
    if ($(".cmb-row.wcct_radio_btn").length > 0) {
        $(".cmb-row.wcct_radio_btn").each(function () {
            var $this = $(this);
            $this.find("li.radio-active").removeClass("radio-active");
            $this.find("input[type='radio']:checked").parent("li").addClass("radio-active");
        });
    }
}

(function ($) {
    wcct_manage_radio_active($);
})(jQuery);
