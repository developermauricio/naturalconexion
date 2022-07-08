(function( $ ) {

function triggerError(input) {
	var container = input.closest(".dplr-input-section");
	container.addClass('input-error');
	container.removeClass('tooltip-hide');
	container.find(".tooltip-container span").html(input.attr("data-validation-fixed"));
}

function validateEmail(emailElement) {
	var email = emailElement.val();
	var container = emailElement.closest(".dplr-input-section");

	if (email.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)) {
		container.removeClass('input-error');
		container.addClass('tooltip-hide');
	} else {
		container.find(".tooltip-container span").html(emailElement.attr("data-validation-email"));
		container.addClass('input-error');
		container.removeClass('tooltip-hide');
	}
}

function validateRequired(requiredElement) {
	var value = requiredElement.val();
	var container = requiredElement.closest(".dplr-input-section");

	if (value) {
		container.removeClass('input-error');
		container.addClass('tooltip-hide');
	} else {
		container.find(".tooltip-container span").html(requiredElement.attr("data-validation-required"));
		container.addClass('input-error');
		container.removeClass('tooltip-hide');
	}
}

function hideUserApiError(){
	$('.tooltip--user_api_error').css('display','none');
}

$(document).ready(function(){

	var colorSelector = $('.color-selector');
	var default_page_size = '200';

	$("input[data-validation-fixed]").each(function() {
		hideUserApiError();
		triggerError($(this));
	});

	$("input[data-validation-email]").focusout(function() {
		hideUserApiError();
		validateEmail($(this));
	});

	$("input[data-validation-required]").focusout(function() {
		hideUserApiError();
		validateRequired($(this));
	});

	$(".dplr-input-section input[type='text']").focusin(function(e) {
		$(this).closest(".dplr-input-section").addClass("notempty").find('.tooltip-container span').html('');
		$(this).addClass("notempty");
	});
	
	$(".dplr-input-section input[type='text']").focusout(function(e) {
		if( $(this).val() == ""){
			$(this).closest(".dplr-input-section").removeClass("notempty");
			$(this).removeClass("notempty");
		}
	});

	$("#dplr-disconnect-form").submit(function(event) {

		event.preventDefault();
		hideUserApiError();

		var form = $(this);
		var button = $(this).find('button');
		button.attr('disabled','disabled').addClass("button--loading");
		
		var data = {
			action: 'dplr_ajax_disconnect'
		}

		$.post( ajaxurl, data, function( response ) {	
			var obj = JSON.parse(response);
			if(obj.response.code == '200'){
				window.location.reload();
			}else{
				var body = JSON.parse(obj.body);
				var msg = '';
				if(body.status!='401'){
					msg = generateErrorMsg(body);
				}else{
					msg = object_string.wrongCredentials;
				}
				var error = '<div class="tooltip tooltip-warning tooltip--user_api_error">';
					error+= '<div class="text-red text-left">';
					error+= '<span>' + msg + '</span>';
					error+= '</div>';
					error+= '</div>';
				form.after(error);
				button.removeAttr('disabled').removeClass('button--loading');
			}
		})

	});

	/**
	 * Check against api first, 
	 * then save credentials.
	 */
	$("#dplr-connect-form").submit(function(event) {
		event.preventDefault();
		hideUserApiError();

		var form = $(this);
		var button = $(this).find('button');
		var userfield = $('#user-account');
		var keyfield = $('#api-key');

		validateEmail($("input[data-validation-email]"));
		validateRequired($("input[data-validation-required]"));

		var inputErrors = $(this).find(".input-error");
		if(inputErrors.length > 0){
			button.removeClass("button--loading");
			return false;
		}

		button.attr('disabled','disabled').addClass("button--loading");
		
		var data = {
			action: 'dplr_ajax_connect',
			user: userfield.val(),
			key: keyfield.val()
		}

		$.post( ajaxurl, data, function( response ) {	
			var obj = JSON.parse(response);
			if(obj.response.code == '200'){				
				var fields = form.serialize();
				$.post( 'options.php', fields, function(){
					window.location.reload(false); 					
				});	
			}else{
				var body = JSON.parse(obj.body);
				var msg = '';
				if(body.status!='401'){
					msg = generateErrorMsg(body);
				}else{
					msg = object_string.wrongCredentials;
				}
				var error = '<div class="tooltip tooltip-warning tooltip--user_api_error">';
					error+= '<div class="text-red text-left">';
					error+= '<span>' + msg + '</span>';
					error+= '</div>';
					error+= '</div>';
				form.after(error);
				button.removeAttr('disabled').removeClass('button--loading');
			}
		})

	});

	$("#dplr-connect-form.error label input[type='text']").keyup(function(event) {
		$(".error").each(function(index, el) {
			$(this).removeClass('error');
		});
	});

	$(".multiple-selec").each(function(){
		var elem = $(this);
		var elemID = elem.attr('id');
		if(elemID != 'widget-dplr_subscription_widget-__i__-selected_lists'){
			elem.chosen({
				width: "100%",
			});
			elem.addClass('selecAdded');
		}
	});

	$( ".sortable" ).sortable({
		placeholder: "ui-state-mark"
	});

	$( ".sortable" ).disableSelection();

	/*
	var fields = {
		container: $("ul#formFields"),

		items: [],
		addItem: function(item) {
			var domElement = $(item.renderItem());
			var _this = this;
			this.items.push(item);
			domElement.find(".icon-close").on("click", function(){
				$(this).parent().remove();
			});
			this.container.append(domElement);
		},
		removeItem: function(element) {

		}
	};*/

	$("body").on('click', "li .alt-toggle", function(e) {
		$(this).closest('li').toggleClass('active');
	});

	$(".dplr-toggle-thankyou").change(function(){
		var o = $('.dplr-toggle-thankyou:checked').val();
		if(o === 'yes'){
			$('.dplr_thankyou_url input').attr('required','required');
			$('.dplr_thankyou_url').css('display','inline');
			$('.dplr_confirmation_message').val('').css('display','none');
		}else{
			$('.dplr_thankyou_url input').removeAttr('required');
			$('.dplr_thankyou_url').val('').css('display','none');
			$('.dplr_confirmation_message').css('display','inline');
		}
	});

	$(".dplr-toggle-consent").change(function(){
		var o = $('.dplr-toggle-consent:checked').val();
		if(o === 'yes'){
			$('#dplr_consent_section').fadeIn();
		}else{
			$('#dplr_consent_section').fadeOut();
		}
	});	

	if($('.dplr-toggle-selector').length>0){	
		colorSelector.iris({
			change: function (event,ui){
				$('.color-selector')[0].setCustomValidity('');
			}
		});
		showColorSelector();
	}

	$(".dplr-toggle-selector").change(function(){
		if(!colorSelector.val().match('^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$')) colorSelector.val('');
		showColorSelector();
	});

	if($('#dplr-dialog-confirm').length>0){
		$("#dplr-dialog-confirm").dialog({
			autoOpen: false,
			resizable: false,
			height: "auto",
			width: 400,
			modal: true
		});
	}

	$(".dplr-tab-content--list .dplr-remove").click(function(e) {
		
		e.preventDefault();
		clearResponseMessages();
		var a = $(this);
		var listId = a.attr('data-list-id');
		var row = a.closest('tr');
		if(!listId>0) return false;

		$("#dplr-dialog-confirm").dialog("option", "buttons", [{
		  text: object_string.Delete,
		  click: function() {
			var data = {action: 'dplr_delete_form', listId : listId}
			$(this).dialog("close");
			row.addClass('deleting');
			$.post(ajaxurl,data,function(resp){
				if(resp == '1'){
					row.remove();
				}
			});
		  }
		}, {
		  text: object_string.Cancel,
		  click: function() {
			$(this).dialog("close");
		  }
		}]);

		$("#dplr-dialog-confirm").dialog("open");
		
	});

	/* CRUD */

	$("#dplr-save-list").click(function(e){
		e.preventDefault();			
		clearResponseMessages();
		var listName = $(this).closest('form').find('input[type="text"]').val();
		if(listName!==''){
			var data = {
				action: 'dplr_save_list',
				listName: listName
			};
			listsLoading();
			$.post( ajaxurl, data, function( response ) {
				var body = 	JSON.parse(response);
				if(body.createdResourceId){		
					var html ='<tr>';
					html+='<td>'+body.createdResourceId+'</td><td><strong>'+listName+'</strong></td>';
					html+='<td>0</td>';
					html+='<td><a href="#" class="text-dark-red" data-list-id="'+body.createdResourceId+'">'+object_string.Delete+'</a></td>'
					html+='</tr>';
					$("#dplr-tbl-lists tbody").prepend(html);
				}else{
					if(body.status >= 400){
						//body.status,body.errorCode
						displayErrors(body);
					}
				}
				listsLoaded();
			});
		}
	});

	$("#dplr-tbl-lists tbody").on("click","tr a",deleteList);

	$(".dplr-extensions .dplr-boxes button.dp-install").click(function(){
		var button = $(this);
		var extension = button.attr('data-extension');
		button.addClass('button--loading').html(object_string.installing);
		button.closest('.dplr-extensions').find('button').css('pointer-events','none');
		var data = {
			action: 'install_extension',
			extensionName: extension
		}
		$.post(ajaxurl,data,function(resp){
			window.location.reload(false);
		});
	});
	
	/*
	$(".dplr-extensions .dplr-boxes button.dp-uninstall").click(function(){
		var button = $(this);
		var extension = button.attr('data-extension');
		button.addClass('button--loading').html(object_string.uninstalling);
		button.closest('.dplr-extensions').find('button').css('pointer-events','none');
		console.log('uninstall');
	});
	*/

	if($("#dplr-tbl-lists").length>0){
		loadLists(1,default_page_size);
	}

	function showColorSelector(){
		colorSelector.val() == '' ? btnColor = '#000000' : btnColor = colorSelector.val();
		$('.dplr-toggle-selector:checked').val() === 'yes' ?
		colorSelector.css('display', 'block').iris('color',btnColor).iris('show') : 
		colorSelector.css('display', 'none').iris('hide');
	}

});

function listsLoading(){
	$('form input, form button').prop('disabled', true);
	$('#dplr-crud').addClass('loading');
}

function listsLoaded(){
	$('form input, form button').prop('disabled', false);
	$('form input').val('');
	$('#dplr-crud').removeClass('loading');
	$('#dplr-tbl-lists').removeClass('d-none');
}

function loadLists( page, per_page ){

	var data = {
		action: 'dplr_get_lists',
		page: page,
		per_page : per_page
	};
	
	if(page==1){
		listsLoading();
		$("#dplr-tbl-lists tbody tr").remove();
	}else{
		$("#crud-show-more").addClass('button--loading');
	}

	$.post( ajaxurl, data, function( response ) {
		if(response.length>0){
			var obj = JSON.parse(response);
			var items = obj.items;
			var html = '';
			for (const key in items) {
				var value = items[key];
				html += '<tr>';
				html += '<td>'+value.listId+'</td>';
				html += '<td><strong>'+value.name+'</strong></td>';
				html += '<td>'+value.subscribersCount+'</td>';
				html += '<td><a href="#" class="text-dark-red" data-list-id="'+value.listId+'">'+object_string.Delete+'</a></td>'
				html += '</tr>';
			}
			$("#dplr-tbl-lists tbody").prepend(html);
			if(page==1){
				listsLoaded();
			}else{
				$("#crud-show-more").removeClass('button--loading');
			}
			
			if(page < parseInt(obj.pagesCount)){
				$("#crud-show-more").css('visibility','visible').attr('data-next-page', parseInt(page)+1);
			}else{
				$("#crud-show-more").css('visibility','hidden');
			}
		
		}
	})
}

function deleteList(e){

	e.preventDefault();

	var a = $(this);
	var tr = a.closest('tr');
	var listId = a.attr('data-list-id');
	var data = {
		action: 'dplr_delete_list',
		listId : listId
	};

	clearResponseMessages();
	
	$("#dplr-dialog-confirm").dialog("option", "buttons", [{
		text: object_string.Delete,
		click: function() {
			$(this).dialog("close");
			tr.addClass('deleting');
			$.post( ajaxurl, data, function( response ) {
				var obj = JSON.parse(response);
				if(obj.response.code == 200){
					tr.remove();
					return;
				}
				(obj.response.code == 0)?
					$('#showErrorResponse').css('display','flex').html('<p>'+obj.response.message+'</p>') : 
					displayErrors(JSON.parse(obj.body))
				tr.removeClass('deleting');
			});
		}
	  }, 
	  {
		text: object_string.Cancel,
		click: function() {
		  $(this).dialog("close");
		}
	  }]);

	  $("#dplr-dialog-confirm").dialog("open");

}

})( jQuery );

function displayErrors(body){
	jQuery('#showErrorResponse').css('display','flex').html('<p>'+generateErrorMsg(body)+'</p>');
}

function displaySuccess(successMsg){
	if(successMsg == '') return false;
	jQuery('#showSuccessResponse').css('display','flex').html('<p>'+successMsg+'</p>');
}

function clearResponseMessages(){
	jQuery('#showSuccessResponse,#showErrorResponse').html('').css('display','none');
	jQuery('#displaySuccessMessage,#displayErrorMessage').remove();
}

function generateErrorMsg(body){
	let status = body.status,
		code = body.errorCode, 
		title = body.title, 
		detail = body.detail;
	let err = '';
	let errors = {	
		400 : { 1: object_string.validationError,
				2: object_string.duplicatedName,
				3: object_string.maxListsReached,
				8: (typeof body.blockingReasonCode !== 'undefined')? object_string[body.blockingReasonCode] : ''
			},
		401 : {},
		404 : {},
		429 : { 0: object_string.tooManyConn},
	}	
	if(status === 528){
		err = object_string.cURL28Error;
		return err;
	}
	if(typeof errors[status] === 'undefined')
		err = object_string.APIConnectionErr;
	else 
		typeof errors[status][code] === 'undefined'? err= '<strong>'+title+'</strong> '+detail : err = errors[status][code];
	return err;
}