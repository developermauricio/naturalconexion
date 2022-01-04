/* global jQuery */

jQuery(function ($) {
    'use strict';
    $('.wcct-cmb-tab-nav').on('click', 'a', function (e) {
        e.preventDefault();

        var $li = $(this).parent(),
            panel = $li.data('panel'),
            $wrapper = $li.parents(".cmb-tabs").find('.cmb2-wrap-tabs'),
            $panel = $wrapper.find('.cmb-tab-panel-' + panel);

        $li.addClass('cmb-tab-active').siblings().removeClass('cmb-tab-active');

        $panel.addClass('show').siblings().removeClass('show');
        $(document).trigger('wcct_cmb2_options_tabs_activated', [panel]);
    });


});
