var wcct_timeOut = false;
var wcctAllUniqueTimers = [];
(function ($) {
    'use strict';
    var wcctArrBar = [];
    $(".variations_form").on("woocommerce_variation_select_change", function () {
        // Fires whenever variation's select is changed
    });
    $(".variations_form").on("show_variation", function (event, variation) {
        // Fired when the user selects all the required dropdowns/ attributes and a final variation is selected/ shown
    });

    $(document).ready(function () {
        wcct_reset_all_timer_data();

        wcct_expiry_timer_init();
        wcct_counter_bar();

        wcct_populate_header_info();
    });

    $(window).scroll(function () {
        wcct_counter_bar();
    });

    $(document).bind("wc_fragments_refreshed", function () {
        wcct_expiry_timer_init();
    });

    function wcct_reset_all_timer_data() {
        if (!wcct_data.hasOwnProperty('refresh_timings')) {
            return;
        }
        if (wcct_data.refresh_timings === "no") {
            return;
        }
        $(".wcct_countdown_timer .wcct_timer_wrap").each(function () {
            var currentEleme = this;
            var campID = $(this).parents(".wcct_countdown_timer").attr('data-campaign-id');
            if (wcctAllUniqueTimers.indexOf(campID) > -1) {
                return;
            }
            wcctAllUniqueTimers.push(campID);
            wcct_expiry_timer_init();
            $.ajax({
                url: wcct_data.admin_ajax,
                type: "GET",
                dataType: 'json',
                data: {
                    'wcct_action': 'wcct_refreshed_times',
                    'location': document.location.href,
                    'endDate': $(this).attr('data-date'),
                    'campID': $(this).parents(".wcct_countdown_timer").attr('data-campaign-id')
                },
                beforeSend: function () {
                },
                success: function (result) {
                    $(".wcct_countdown_timer[data-campaign-id='" + result.id + "']").each(function () {
                        var curDataLeft = jQuery(this).children(".wcct_timer_wrap").attr("data-left");

                        if (result.diff === 0) {
                            switch (jQuery(this).attr('data-type')) {
                                case "counter_bar":
                                    jQuery(this).parents(".wcct_counter_bar").eq(0).fadeOut().remove();
                                    break;
                                case "single":
                                    jQuery(this).eq(0).fadeOut().remove();
                                    break;
                            }
                        } else {

                            var campDelay = jQuery(this).attr('data-delay');
                            if (typeof campDelay != 'undefined' && result.diff > parseInt(campDelay)) {
                                jQuery(this).remove();
                            } else {
                                //$timerElem.css("display", "inline-block");
                            }
                            if ((parseInt(curDataLeft) - parseInt(result.diff)) > 10) {

                                jQuery(this).removeAttr("data-wctimer-load");
                                jQuery(this).children(".wcct_timer_wrap").attr("data-left", result.diff);
                            }
                        }
                    });
                    wcct_expiry_timer_init();
                }
            });
        });

    }

    function wcct_populate_header_info() {
        if ($("#wp-admin-bar-wcct_admin_page_node-default").length > 0) {
            $("#wp-admin-bar-wcct_admin_page_node-default").html($(".wcct_header_passed").html());
        }
    }

    function wcct_expiry_timer_init(aH) {
        if (aH === undefined) {
            aH = true;
        }
        if ($(".wcct_timer").length > 0) {
            $(".wcct_timer").each(function () {
                var $this = $(this);

                // checking data-wctimer-load attr
                var dAl = $this.attr("data-wctimer-load");
                if (dAl == 'yes') {
                    return true;
                }

                var childSpan = $this.find(".wcct_timer_wrap");
                var toTimestamp = parseInt(childSpan.attr("data-date"));
                var displayFormat, valSecs, valMins, valHrs, classMins, classHrs, classDays, classSecWrap, classMinsWrap, classHrsWrap, classDaysWrap;

                var timerSkin = childSpan.attr("data-timer-skin");
                var label_day = $(this).attr("data-days") != "" ? $(this).attr("data-days") : 'day';
                var label_hrs = $(this).attr("data-hrs") != "" ? $(this).attr("data-hrs") : 'hr';
                var label_min = $(this).attr("data-mins") != "" ? $(this).attr("data-mins") : 'min';
                var label_sec = $(this).attr("data-secs") != "" ? $(this).attr("data-secs") : 'sec';
                var is_show_days = $(this).attr("data-is_days") != "" ? $(this).attr("data-is_days") : 'yes';
                var is_show_hrs = $(this).attr("data-is-hrs") != "" ? $(this).attr("data-is-hrs") : 'yes';
                var modifiedDate = new Date().getTime() + parseInt(childSpan.attr("data-left")) * 1000;

                childSpan.wcctCountdown(modifiedDate, {elapse: true}).on('update.countdown', function (event) {
                    valSecs = event.offset.seconds;
                    valMins = event.offset.minutes;
                    valHrs = event.offset.hours;
                    classMins = classHrs = classDays = classSecWrap = classMinsWrap = classHrsWrap = classDaysWrap = '';
                    if (valSecs == '0') {
                        classMins = ' wcct_pulse wcct_animated';
                        classMinsWrap = ' wcct_border_none';
                    }
                    if (valSecs == '0' && classMins == '0') {
                        classHrs = ' wcct_pulse wcct_animated';
                        classHrsWrap = ' wcct_border_none';
                    }
                    if (valSecs == '0' && classMins == '0' && classHrs == '0') {
                        classDays = ' wcct_pulse wcct_animated';
                        classDaysWrap = ' wcct_border_none';
                    }
                    displayFormat = '';
                    if (event.elapsed && aH == true) {
                        var headerParent = $this.parents('.wcct_header_area');
                        if (headerParent.length > 0) {
                            headerParent.find(".wcct_close").trigger("click");
                            setTimeout(function () {
                                headerParent.remove();
                            }, 1000);
                        }
                        var footerParent = $this.parents('.wcct_footer_area');
                        if (footerParent.length > 0) {
                            footerParent.find(".wcct_close").trigger("click");
                            setTimeout(function () {
                                footerParent.remove();
                            }, 1000);
                        }
                        setTimeout(function () {
                            $this.remove();
                        }, 1000);
                        /**
                         * Making sure we only register reload event only once per load so that there would be no chance for further reload.
                         */
                        if (wcct_timeOut === false) {
                            $.ajax({
                                url: wcct_data.admin_ajax,
                                type: "POST",
                                dataType: 'json',
                                data: {
                                    'action': 'wcct_clear_cache',
                                },
                                success: function (result) {
                                    //
                                },
                                timeout: 10
                            });
                            if ('yes' == wcct_data.reload_page_on_timer_ends) {
                                var timeOut = setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                            }
                        }
                    } else {
                        var WDays = '%D';
                        var WHrs = '%H';
                        var WMins = '%M';
                        var WSecs = '%S';

                        if (aH == false) {
                            WDays = '00';
                            WHrs = '00';
                            WMins = '00';
                            WSecs = '00';
                        }

                        if (timerSkin == 'round_fill') {
                            if (event.offset.totalDays > 0 || is_show_days == "yes") {
                                displayFormat = '<div class="wcct_round_wrap ' + classDaysWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div><div class="wcct_wrap_border ' + classDays + '"></div></div>';
                            }
                            if (event.offset.totalHours > 0 || is_show_hrs == "yes") {
                                displayFormat += '<div class="wcct_round_wrap ' + classHrsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div><div class="wcct_wrap_border ' + classHrs + '"></div></div>';
                            }
                            displayFormat += '<div class="wcct_round_wrap ' + classMinsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div><div class="wcct_wrap_border ' + classMins + '"></div></div>' + '<div class="wcct_round_wrap wcct_border_none"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div><div class="wcct_wrap_border wcct_pulse wcct_animated"></div></div>';
                        } else if (timerSkin == 'round_ghost') {
                            if (event.offset.totalDays > 0 || is_show_days == "yes") {
                                displayFormat = '<div class="wcct_round_wrap ' + classDaysWrap + '"><div class="wcct_wrap_border ' + classDays + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div></div>';
                            }
                            if (event.offset.totalHours > 0 || is_show_hrs == "yes") {
                                displayFormat += '<div class="wcct_round_wrap ' + classHrsWrap + '"><div class="wcct_wrap_border ' + classHrs + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div></div>';
                            }
                            displayFormat += '<div class="wcct_round_wrap ' + classMinsWrap + '"><div class="wcct_wrap_border ' + classMins + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div></div>' + '<div class="wcct_round_wrap wcct_border_none"><div class="wcct_wrap_border wcct_pulse wcct_animated"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div></div>';
                        } else if (timerSkin == 'square_fill') {
                            if (event.offset.totalDays > 0 || is_show_days == "yes") {
                                displayFormat = '<div class="wcct_square_wrap ' + classDaysWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div><div class="wcct_wrap_border ' + classDays + '"></div></div>';
                            }
                            if (event.offset.totalHours > 0 || is_show_hrs == "yes") {
                                displayFormat += '<div class="wcct_square_wrap ' + classHrsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div><div class="wcct_wrap_border ' + classHrs + '"></div></div>';
                            }
                            displayFormat += '<div class="wcct_square_wrap ' + classMinsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div><div class="wcct_wrap_border ' + classMins + '"></div></div>' + '<div class="wcct_square_wrap wcct_border_none"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div><div class="wcct_wrap_border wcct_pulse wcct_animated"></div></div>';
                        } else if (timerSkin == 'square_ghost') {
                            if (event.offset.totalDays > 0 || is_show_days == "yes") {
                                displayFormat = '<div class="wcct_square_wrap ' + classDaysWrap + '"><div class="wcct_wrap_border ' + classDays + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div></div>';
                            }
                            if (event.offset.totalHours > 0 || is_show_hrs == "yes") {
                                displayFormat += '<div class="wcct_square_wrap ' + classHrsWrap + '"><div class="wcct_wrap_border ' + classHrs + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div></div>';
                            }
                            displayFormat += '<div class="wcct_square_wrap ' + classMinsWrap + '"><div class="wcct_wrap_border ' + classMins + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div></div>' + '<div class="wcct_square_wrap wcct_border_none"><div class="wcct_wrap_border wcct_pulse wcct_animated"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div></div>';
                        } else if (timerSkin == 'highlight_1') {
                            if (event.offset.totalDays > 0 || is_show_days == "yes") {
                                displayFormat = '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WDays + '</span> ' + label_day + '<span class="wcct_colon_sep">:</span></div>';
                            }
                            if (event.offset.totalHours > 0 || is_show_hrs == "yes") {
                                displayFormat += '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WHrs + '</span> ' + label_hrs + '<span class="wcct_colon_sep">:</span></div>';
                            }
                            displayFormat += '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WMins + '</span> ' + label_min + '<span class="wcct_colon_sep">:</span></div>' + '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WSecs + '</span> ' + label_sec + '</div>';
                        } else {
                            if (event.offset.totalDays > 0 || is_show_days == "yes") {
                                displayFormat = WDays + label_day;
                            }
                            if (event.offset.totalHours > 0 || is_show_hrs == "yes") {
                                displayFormat += ' ' + WHrs + label_hrs;
                            }
                            displayFormat += ' ' + WMins + label_min + ' ' + WSecs + label_sec;
                        }
                        $(this).html(event.strftime(displayFormat));
                    }
                });
                $this.attr("data-wctimer-load", "yes");
            });
        }
    }

    function wcct_counter_bar() {
        if ($('.wcct_counter_bar').length > 0) {
            $(".wcct_counter_bar").each(function () {
                var elem = $(this);
                elem.css("display", "block");
                if (elem.find(".wcct_progress_aria").length > 0) {
                    var $this = elem.find(".wcct_progress_aria");
                    if ($this.visible(true)) {
                        if (!$this.hasClass("wcct_bar_active")) {
                            $this.addClass("wcct_bar_active");
                            var $ProgressBarVal = $this.find('.wcct_progress_bar').attr('aria-valuenow');
                            setTimeout(function () {
                                $this.find('.wcct_progress_bar').css('width', $ProgressBarVal + '%');
                            }, 200);
                        }
                    }
                }
            });
        }
    }

    function wcct_ajax_call($this, expireTime) {
        var instanceIDVal = $this.attr("data-id");
        var typeVal = $this.find(".wcct_close").attr("data-ref");
        $.ajax({
            url: wcct_data.admin_ajax,
            type: "POST",
            data: {
                action: 'wcct_close_sticky_bar',
                type: typeVal,
                expire_time: expireTime,
                instance_id: instanceIDVal,
            },
            success: function (result) {
            }
        });
    }

    function wcct_timestamp_converter(UNIX_timestamp) {
        var newDate = new Date(UNIX_timestamp * 1000);
        var year = newDate.getFullYear();
        var month = newDate.getMonth();
        var date = newDate.getDate();
        var hour = newDate.getHours();
        var min = "0" + newDate.getMinutes();
        var sec = "0" + newDate.getSeconds();
        var time = year + "/" + (month + 1) + "/" + date + " " + hour + ":" + min.substr(-2) + ":" + sec.substr(-2);
        return time;
    }


})(jQuery);
