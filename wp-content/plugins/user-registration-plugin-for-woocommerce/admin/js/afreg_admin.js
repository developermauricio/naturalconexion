jQuery(document).ready(function($) {

	"use strict";

	var form_enc = jQuery('form').attr("enctype");
	if ( form_enc != 'multipart/form-data' ) {
		jQuery('form').attr("enctype" , "multipart/form-data");
	}

	var value = $("#afreg_field_type option:selected").val();
	if (value == 'select' || value == 'multiselect' || value == 'radio' || value == 'multi_checkbox') {

		jQuery('#afreg_field_options').show();
		jQuery('.afreg_recaptchahide').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();
	} else if (value == 'googlecaptcha') {

		jQuery('#afreg_field_options').hide();
		jQuery('.afreg_recaptchahide').hide();
		jQuery('#afreg_recaptcha').show();
		jQuery('.afreg_fileupload').hide();
		jQuery('.gshow').show();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();

	} else if (value == 'fileupload') {

		jQuery('#afreg_field_options').hide();
		jQuery('.afreg_recaptchahide').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').show();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();

	} else if (value == 'heading') {

		jQuery('#afreg_field_options').hide();
		jQuery('.heading_hide').hide();
		jQuery('.heading_show').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.gshow').hide();
		jQuery('.heading_type_show').show();
		jQuery('.description_show').hide();

	} else if (value == 'description') {

		jQuery('#afreg_field_options').hide();
		jQuery('.heading_hide').hide();
		jQuery('.heading_show').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.gshow').hide();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').show();

	} else {

		jQuery('#afreg_field_options').hide();
		jQuery('.afreg_recaptchahide').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();
	}
});

function wpf_downloadFile(post_id) {

	"use strict";

	var ajaxurl = wpf_php_vars.admin_url;

	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: {"action": "wpf_download_file", "post_id":post_id},
		success: function(data) {

			window.open(data, '_blank');

		}
	});

	var form_enc = jQuery('form').attr("enctype");
	if ( form_enc != 'multipart/form-data' ) {
		jQuery('form').attr("enctype" , "multipart/form-data");
	}

}


var maxField = 10000; //Input fields increment limitation

function afreg_add_option() {

	"use strict";
	var fieldHTML  = '';
	fieldHTML     += '<tr id="maxrow'+maxField+'">';
		fieldHTML += '<td><input type="text" name="afreg_field_option['+maxField+'][field_value]" id="afreg_field_option_value'+maxField+'" class="option_field" /></td>';
		fieldHTML += '<td><input type="text" name="afreg_field_option['+maxField+'][field_text]" id="afreg_field_option_text'+maxField+'" class="option_field" /></td>';
		fieldHTML += '<td><button type="button" class="button button-danger" onclick="jQuery(\'#maxrow' + maxField + '\').remove();">Remove Option</button></td>';
	fieldHTML     += '</tr>'; //New input field html 
	jQuery('#NewField').before(fieldHTML);
	maxField++;
}

function afreg_show_options(value) {

	"use strict";

	if (value == 'select' || value == 'multiselect' || value == 'radio' || value == 'multi_checkbox') {

		jQuery('#afreg_field_options').show();
		jQuery('.afreg_recaptchahide').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();
	} else if (value == 'googlecaptcha') {

		jQuery('#afreg_field_options').hide();
		jQuery('.afreg_recaptchahide').hide();
		jQuery('#afreg_recaptcha').show();
		jQuery('.afreg_fileupload').hide();
		jQuery('.gshow').show();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();

	} else if (value == 'fileupload') {

		jQuery('#afreg_field_options').hide();
		jQuery('.afreg_recaptchahide').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').show();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();

	} else if (value == 'heading') {

		jQuery('#afreg_field_options').hide();
		jQuery('.heading_hide').hide();
		jQuery('.heading_show').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.gshow').hide();
		jQuery('.heading_type_show').show();
		jQuery('.description_show').hide();

	} else if (value == 'description') {

		jQuery('#afreg_field_options').hide();
		jQuery('.heading_hide').hide();
		jQuery('.heading_show').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.gshow').hide();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').show();

	} else {

		jQuery('#afreg_field_options').hide();
		jQuery('.afreg_recaptchahide').show();
		jQuery('#afreg_recaptcha').hide();
		jQuery('.afreg_fileupload').hide();
		jQuery('.heading_type_show').hide();
		jQuery('.description_show').hide();
	}


}




function afregsaveFields() {

	var ajaxurl = afreg_php_vars.admin_url;
	var nonce   = afreg_php_vars.nonce;
	var url     = afreg_php_vars.url;

	jQuery('#df_form').find(':checkbox:not(:checked)').attr('value', '0').prop('checked', true);
	var data2 = jQuery('#df_form').serialize();
	

	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data2 + '&action=afreg_save_df_form&nonce='+nonce,
		success: function(res) {
			
			window.location.reload(true);

		}
	});
}


