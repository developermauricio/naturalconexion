function _mic_submit() {
	if(document.mic_form.mic_image.value == "") {
		alert(mic_adminscripts.mic_image);
		document.mic_form.mic_image.focus();
		return false;
	}
//	else if(document.mic_form.mic_width.value != "" && isNaN(document.mic_form.mic_width.value)) {
//		alert(mic_adminscripts.mic_width_num);
//		document.mic_form.mic_width.focus();
//		document.mic_form.mic_width.select();
//		return false;
//	}
	else if(document.mic_form.mic_group.value == "" && document.mic_form.mic_group_txt.value == "") {
		alert(mic_adminscripts.mic_group);
		document.mic_form.mic_group_txt.focus();
		return false;
	}
}

function _mic_delete(id) {
	if(confirm(mic_adminscripts.mic_delete)) {
		document.frm_mic_display.action="options-general.php?page=marquee-image-crawler&ac=del&did="+id;
		document.frm_mic_display.submit();
	}
}	

function _mic_redirect() {
	window.location = "options-general.php?page=marquee-image-crawler";
}

function _mic_help() {
	window.open("http://www.gopiplus.com/work/2020/12/18/marquee-image-crawler-wordpress-plugin/");
}

function _mic_numericandtext(inputtxt) {  
	var numbers = /^[0-9a-zA-Z]+$/;  
	document.getElementById('mic_group').value = "";
	if(inputtxt.value.match(numbers)) {  
		return true;  
	}  
	else {  
		alert(mic_adminscripts.mic_numletters); 
		newinputtxt = inputtxt.value.substring(0, inputtxt.value.length - 1);
		document.getElementById('mic_group_txt').value = newinputtxt;
		return false;  
	}  
}