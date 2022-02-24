jQuery(document).ready(function ($) {
    'use strict';

    const App = {
        init() {
            $('input#billing_email, input#email').on('change', function () {
                var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (pattern.test($(this).val())) {
                    wacv_send_get_guest_info();
                }
            });

            $('input#billing_phone').on('change', function () {
                wacv_send_get_guest_info();
            });
            // $('input#billing_first_name,input#billing_last_name,input#billing_last_name,input#billing_country,input#billing_address_1,input#billing_city,input#billing_phone').change(function () {
            // });
            function wacv_send_get_guest_info() {
                var data = $('form.woocommerce-checkout').serialize() + '&action=wacv_get_info';

                $.ajax({
                    url: wacv_localize.ajax_url,
                    data: data,
                    type: 'POST',
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (res) {
                    }
                });
            }
        }
    };

    App.init();

    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.url === "/?wc-ajax=viwcaio_get_checkout_form") {
            App.init();
        }
    });

});