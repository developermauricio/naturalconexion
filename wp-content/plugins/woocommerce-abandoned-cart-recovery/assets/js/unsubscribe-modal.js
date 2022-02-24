jQuery(document).ready(function ($) {
    $('#wacv-unsubscribe-modal').on('click', function (e) {
        if ($(e.target).hasClass('wacv-modal-relative-layer') || $(e.target).hasClass('wacv-modal-close')) {
            $(this).closest('#wacv-unsubscribe-modal').hide();
        }
    });
});