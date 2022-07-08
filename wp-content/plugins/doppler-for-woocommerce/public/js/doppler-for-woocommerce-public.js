(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */	
	
	jQuery(document).ready(function($){

		var timer;

		$("#billing_email, #billing_phone, input.input-text, input.input-checkbox, textarea.input-text").on("keyup keypress change", getCheckoutData ); //All action happens on or after changing Email or Phone fields or any other fields in the Checkout form. All Checkout form input fields are now triggering plugin action. Data saved to Database only after Email or Phone fields have been entered.
		$(window).on("load", getCheckoutData ); //Automatically collect and save input field data if input fields already filled on page load
	
		function getCheckoutData(){
			if($("#billing_email").length > 0){ //If email address exists

				var dplrwoo_email = $("#billing_email").val();

				var atposition = dplrwoo_email.indexOf("@");
				var dotposition = dplrwoo_email.lastIndexOf(".");
				
				clearTimeout(timer);

				if (!(atposition < 1 || dotposition < atposition + 2 || dotposition + 2 >= dplrwoo_email.length)){
					//If email is valid
					var dplrwoo_name 	  = $("#billing_first_name").val();
					var dplrwoo_lastname  = $("#billing_last_name").val();
					var dplrwoo_country   = $("#billing_country").val();
					var dplrwoo_city      = $("#billing_city").val();
					var dplrwoo_phone	  = $("#billing_phone").val();
					
					//Other fields used for "Remember user input" function
					var dplrwoo_billing_company = $("#billing_company").val();
					var dplrwoo_billing_address_1 = $("#billing_address_1").val();
					var dplrwoo_billing_address_2 = $("#billing_address_2").val();
					var dplrwoo_billing_state = $("#billing_state").val();
					var dplrwoo_billing_postcode = $("#billing_postcode").val();
					var dplrwoo_shipping_first_name = $("#shipping_first_name").val();
					var dplrwoo_shipping_last_name = $("#shipping_last_name").val();
					var dplrwoo_shipping_company = $("#shipping_company").val();
					var dplrwoo_shipping_country = $("#shipping_country").val();
					var dplrwoo_shipping_address_1 = $("#shipping_address_1").val();
					var dplrwoo_shipping_address_2 = $("#shipping_address_2").val();
					var dplrwoo_shipping_city = $("#shipping_city").val();
					var dplrwoo_shipping_state = $("#shipping_state").val();
					var dplrwoo_shipping_postcode = $("#shipping_postcode").val();
					var dplrwoo_order_comments = $("#order_comments").val();
					var dplrwoo_create_account = $("#createaccount");
					var dplrwoo_ship_elsewhere = $("#ship-to-different-address-checkbox");

					if(dplrwoo_create_account.is(':checked')){
						dplrwoo_create_account = 1;
					}else{
						dplrwoo_create_account = 0;
					}

					if(dplrwoo_ship_elsewhere.is(':checked')){
						dplrwoo_ship_elsewhere = 1;
					}else{
						dplrwoo_ship_elsewhere = 0;
					}
					
					var data = {
						action:							"save_data",
						dplrwoo_email:					dplrwoo_email,
						dplrwoo_name:					dplrwoo_name,
						dplrwoo_lastname:				dplrwoo_lastname,
						dplrwoo_phone:					dplrwoo_phone,
						dplrwoo_country:				dplrwoo_country,
						dplrwoo_city:					dplrwoo_city,
						dplrwoo_billing_company:		dplrwoo_billing_company,
						dplrwoo_billing_address_1:		dplrwoo_billing_address_1,
						dplrwoo_billing_address_2: 		dplrwoo_billing_address_2,
						dplrwoo_billing_state:			dplrwoo_billing_state,
						dplrwoo_billing_postcode: 		dplrwoo_billing_postcode,
						dplrwoo_shipping_first_name: 	dplrwoo_shipping_first_name,
						dplrwoo_shipping_last_name: 	dplrwoo_shipping_last_name,
						dplrwoo_shipping_company: 		dplrwoo_shipping_company,
						dplrwoo_shipping_country: 		dplrwoo_shipping_country,
						dplrwoo_shipping_address_1: 	dplrwoo_shipping_address_1,
						dplrwoo_shipping_address_2: 	dplrwoo_shipping_address_2,
						dplrwoo_shipping_city: 			dplrwoo_shipping_city,
						dplrwoo_shipping_state: 		dplrwoo_shipping_state,
						dplrwoo_shipping_postcode: 		dplrwoo_shipping_postcode,
						dplrwoo_order_comments: 		dplrwoo_order_comments,
						dplrwoo_create_account: 		dplrwoo_create_account,
						dplrwoo_ship_elsewhere: 		dplrwoo_ship_elsewhere
					}

					timer = setTimeout(function(){
						$.post(dplrWooAjaxObj.ajaxurl, data,
						function(response) {
							console.log(response);
						});
						
					}, 800);
				}else{
					console.log("Not a valid e-mail or phone address");
				}
			}
		}

	});


})( jQuery );
