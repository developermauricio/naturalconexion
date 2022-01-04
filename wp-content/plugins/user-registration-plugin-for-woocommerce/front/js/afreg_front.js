jQuery(document).ready(function($){
	
	"user strict";
	jQuery(".color_sepctrum").spectrum({
		color: "#000",
		preferredFormat: "hex",
	});

});

jQuery(document).ready(function(){
	"user strict";
	jQuery('form#registerform').attr('enctype','multipart/form-data');
	jQuery('.woocommerce-form-register').attr('enctype','multipart/form-data');
	jQuery('.woocommerce-EditAccountForm').attr('enctype','multipart/form-data');
	
});


jQuery(document).ready(function(){
	"user strict";
	jQuery('.woocommerce-account-fields div.create-account').append(jQuery('div.afreg_extra_fields'));
});




