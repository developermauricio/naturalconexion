!function (a) {
    "use strict";
    a(function () {
        a( document.body ).on( 'updated_shipping_method', function() {
            a( '.woocommerce-cart-form :input[name="update_cart"]' ).prop( 'disabled', false );
            a( 'button[name="update_cart"]').click();
        } );
    })
}(jQuery);