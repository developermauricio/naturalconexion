jQuery(document).ready(function ($) {
    "use strict";
    var now = new Date();
    const oneDay = 86400000;
    let today = getDateRange(now);

    if ($('.wacv-select-time-report').val() === 'custom') {
        $('.wacv-custom-time-range').show();
    }

    $('.wacv-select-time-report').on('change', function () {
        let thisVal = $(this).val();
        addDate(thisVal);
        if (thisVal !== 'custom') {
            // $('.wacv-custom-time-range').hide();
            $('form#wacv-abandoned-cart').submit();
        } else {
            $('.wacv-custom-time-range').show();
        }
    });

    function addDate(key) {
        let from_date = $('.wacv-date-from');
        let to_date = $('.wacv-date-to');
        switch (key) {
            case 'today':
                from_date.val(today);
                to_date.val(today);
                break;
            case 'yesterday':
                let yesterday = new Date(Date.now() - oneDay);
                yesterday = getDateRange(yesterday);
                from_date.val(yesterday);
                to_date.val(yesterday);
                break;
            case '30days':
                let _30days = new Date(Date.now() - 30 * oneDay);
                _30days = getDateRange(_30days);
                from_date.val(_30days);
                to_date.val(today);
                break;
            case '90days':
                let _90days = new Date(Date.now() - 90 * oneDay);
                _90days = getDateRange(_90days);
                from_date.val(_90days);
                to_date.val(today);
                break;
            case '365days':
                let _365days = new Date(Date.now() - 365 * oneDay);
                _365days = getDateRange(_365days);
                from_date.val(_365days);
                to_date.val(today);
                break;
        }
    }

    function getDateRange(obj) {
        return obj.getFullYear() + "-" + ("0" + (obj.getMonth() + 1)).slice(-2) + "-" + ("0" + obj.getDate()).slice(-2);
    }
});