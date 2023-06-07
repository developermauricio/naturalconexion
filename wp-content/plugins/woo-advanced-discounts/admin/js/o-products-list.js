(function ($) {
    'use strict';

    $(document).ready(function () {
        var o_tax_query_selected_option = new Array();

        $("#o-list-check-query").click(function () {
            var form = $("#post").serializeJSON();//serializeArray();
            $("span#o-list-loading").css("display", "inline-block");
            $("#debug").html("");
            $.post(
                ajaxurl,
                {
                    action: "o-list-evaluate-query",
                    data: form
                },
                function (data) {
                    $("#o-list-loading").css("display", "none");
                    if (is_json(data)) {
                        var response = JSON.parse(data);
                        $("#debug").html(response.msg);
                    } else
                        $("#debug").html(data);
                }
            );
        });

        $(document).on("change", ".o-list-extraction-type", function (e) {
            var selected_value = $(this).val();
            if (selected_value == "by-id") {
                $(".extract-by-id-row").show();
                $(".extract-by-custom-request-row").hide();
            } else {
                $(".extract-by-id-row").hide();
                $(".extract-by-custom-request-row").show();
            }
        });

        $(document).on('focus', ".o-list-taxonomies-selector", function () {
            // Store the current value on focus and on change
            let previous = $(this).val();

            if (typeof o_tax_query_selected_option[$(this).attr('name')] === 'undefined') {
                let temp = new Array();
                temp[previous] = $(this).closest('tr').find('.o-list-terms-selector').val();
                o_tax_query_selected_option[$(this).attr('name')] = temp;
            } else {
                o_tax_query_selected_option[$(this).attr('name')][previous] = $(this).closest('tr').find('.o-list-terms-selector').val();
            }
        }).on("change", ".o-list-taxonomies-selector", function (e) {
            let param = $(this).val();
            let o_selected_option_key = o_tax_query_selected_option[$(this).attr('name')][$(this).val()];
            $(this).closest('tr').find('.o-list-terms-selector').html(o_tax_query_recap[param]);

            if (o_selected_option_key) {
                // mark elements previously selected.
                $(this).closest('tr').find('.o-list-terms-selector option').each(function (i) {
                    if (o_selected_option_key.indexOf($(this).val()) >= 0) {
                        $(this).attr('selected', 'selected');
                    }
                });
            }
        });

        $(document).on("click", ".add-rf-row", function () {
            if (typeof (o_tax_query_recap) !== 'undefined') {
                var val = $(".o-list-taxonomies-selector").last().val();
                $(".o-list-terms-selector").last().html(o_tax_query_recap[val]);
            }
        });

        if (typeof (o_tax_query_recap) !== 'undefined') {
            // Handle taxonomies field when updating product list
            $(".o-list-taxonomies-selector").each(function (index) {
                let selectedEle = [];
                // Retrieve selected elements.
                $('.o-list-terms-selector:eq( ' + index + ' ) :selected').each(function (i) {
                    selectedEle.push($(this).val());
                });

                // update taxonomies choices.
                let val = $(this).val();
                $('.o-list-terms-selector:eq( ' + index + ' )').html(o_tax_query_recap[val]);

                // mark elements previously selected.
                $('.o-list-terms-selector:eq( ' + index + ' ) option').each(function (i) {
                    if (selectedEle.indexOf($(this).val()) >= 0) {
                        $(this).attr('selected', 'selected');
                    }
                });
                let temp = new Array();
                temp[val] = $('.o-list-terms-selector:eq( ' + index + ' )').val();
                o_tax_query_selected_option[$(this).attr('name')] = temp;
            });
        }
    });
})(jQuery);
