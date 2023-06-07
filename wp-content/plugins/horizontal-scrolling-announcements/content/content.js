function _hsas_delete(guid) {
	if(confirm(hsas_content.hsas_delete_record)) {
		document.frm_hsas_display.action="admin.php?page=hsas-content&ac=del&guid="+guid;
		document.frm_hsas_display.submit();
	}
}

function _hsas_insert() {
	if(document.hsas_form.hsas_text.value=="") {
		alert(hsas_content.hsas_add_text);
		document.hsas_form.hsas_text.focus();
		return false;
	} else if( document.hsas_form.hsas_order.value=="" || isNaN(document.hsas_form.hsas_order.value) ) {
		alert(hsas_content.hsas_add_order);
		document.hsas_form.hsas_order.focus();
		return false;
	} else if( document.hsas_form.hsas_group_txt.value=="" && document.hsas_form.hsas_group.value=="" ) {
		alert(hsas_content.hsas_add_group);
		document.hsas_form.hsas_group_txt.focus();
		return false;
	} else if( document.hsas_form.hsas_datestart.value=="") {
		alert(hsas_content.hsas_add_datestart);
		document.hsas_form.hsas_datestart.focus();
		return false;
	} else if( document.hsas_form.hsas_dateend.value=="") {
		alert(hsas_content.hsas_add_dateend);
		document.hsas_form.hsas_dateend.focus();
		return false;
	}
}

function _hsas_redirect() {
		window.location = "admin.php?page=hsas-content";
}

function _hsas_help() {
		window.open("http://www.gopiplus.com/work/2010/07/18/horizontal-scrolling-announcement/");
}

function _owlc_numericandtext(inputtxt) {  
	document.getElementById('hsas_group').value = "";
	var numbers = /^[0-9a-zA-Z]+$/;  
	if(inputtxt.value.match(numbers)) {  
		return true;  
	}  
	else {  
		alert(hsas_content.hsas_numericandtext); 
		newinputtxt = inputtxt.value.substring(0, inputtxt.value.length - 1);
		document.getElementById('hsas_group_txt').value = newinputtxt;
		return false;  
	}  
}