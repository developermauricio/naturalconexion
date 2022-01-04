jQuery(document).ready( function($) {

	jQuery( '.variations_form' ).each( function() {
        jQuery(this).on( 'found_variation', function( event, variation ) {
            //console.log(variation);//all details here
            var price = variation.display_price;//selectedprice
            //console.log(price);
			$("addi-widget").attr("price",price);
        });
    });
	
	setTimeout(function() {
		var radios = document.querySelectorAll('input[type=radio][name="payment_method"]');

		function changeHandler(event) {
		   if(event.target.value == 'addi' && event.target.checked) {
			   var constraint = document.querySelector('div.constraint-container');
	           if(constraint){
				  const place_order = document.querySelector('button#place_order');
			if(place_order) place_order.disabled =  true;
				  $('button#place_order').addClass('button-disabled');
			   }
		   }
			else {
				const place_order = document.querySelector('button#place_order');
			if(place_order) place_order.disabled =  false;
		        $('button#place_order').removeClass('button-disabled');
			}
		}

		Array.prototype.forEach.call(radios, function(radio) {
		   radio.addEventListener('change', changeHandler);
		});
	}, 3000);	

	var element = document.querySelector('ul.wc_payment_methods > li > input[checked]');
	
	if(element && element.value && element.value === 'addi') {
	var constraint = document.querySelector('div.constraint-container');
	   if(constraint){
		   setTimeout(function() {
			const place_order = document.querySelector('button#place_order');
			if(place_order) place_order.disabled =  true;
			   $('button#place_order').addClass('button-disabled');
		   }, 3000);
	   }
	}
	else {
		setTimeout(function() {
			const place_order = document.querySelector('button#place_order');
			if(place_order) place_order.disabled =  false;
			$('button#place_order').removeClass('button-disabled');
		}, 3000);
	}

});

