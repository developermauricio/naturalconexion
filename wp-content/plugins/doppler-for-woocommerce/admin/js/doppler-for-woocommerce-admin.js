(function($) {
    $.fn.onEnter = function(func) {
        this.bind('keypress', function(e) {
            if (e.keyCode == 13) func.apply(this, [e]);    
        });               
        return this; 
     };
})(jQuery);

(function( $ ) {
	'use strict';

	$(function() {

		var mappingFieldsSelects = $(".dplrwoo-mapping-fields");
		var listsSelect = $(".dplrwoo-lists-sel");
		var contactListSelect = $("#contacts-list");
		var buyersListSelect = $("#buyers-list");
		var syncListsButton = $("#dplrwoo-lists-btn");
		var listsForm = $("#dplrwoo-form-list");

		mappingFieldsSelects.focus(function(){
			$(this).data('fieldData', {'val':$(this).val(),
				'type':$('option:selected', this).attr('data-type'),
				'name':$(this).attr('name')
			});
		}).change(function(){
			var prevData = $(this).data('fieldData');
			var current = $(this).val();
			$(this).data('val', current);
			if(prevData.val!==''){
				mappingFieldsSelects.each(function(){
					if( checkFieldType(prevData.type, $(this).attr('data-type')) && (prevData.name !== $(this).attr('name')) ){
						$(this).append('<option value="'+prevData.val+'">'+prevData.val+'</option>');
					}
				});
			}
			if(current!==''){
				var s = mappingFieldsSelects.not(this);
				s.find('option[value="'+current+'"]').remove();
			}
		});

		listsSelect.focus(function(){
			$(this).data('fieldData', {'val':$(this).val(),
				'name':$(this).attr('name'),
				'selectedName':$(this).children("option:selected").text()
			});
		}).change(function(){
			var prevData = $(this).data('fieldData');
			var current = $(this).val();
			if(prevData.val!==''&&prevData.val!=='0'){
				listsSelect.each(function(){
					if( prevData.name !== $(this).attr('name') ){
						$(this).find('option:first-child').after('<option value="'+prevData.val+'">'+prevData.selectedName+'</option>');
					}
				});
			}
			if(current!==''&&current!='0'){
				var s = listsSelect.not(this);
				s.find('option[value="'+current+'"]').remove();
			}
			$(this).closest('tr').find('td span').html(
				$('option:selected', this).attr('data-subscriptors')
			);
		});

		var synchBuyers = function(buyersList){
			if(buyersList==='') return false;
			$.post( ajaxurl, {action:'dplrwoo_ajax_synch',list_type: 'buyers', list_id: buyersList});
		}
		
		var synchContacts = function(contactsList){
			if(contactsList==='') return false;
			$.post(ajaxurl, {action: 'dplrwoo_ajax_synch', list_type: 'contacts', list_id: contactsList});
		}

		var synchLists = function(buyersList,contactsList){
			console.log(buyersList);
			console.log(contactsList);
			if(contactsList==='' || buyersList==='') return false;
			$.post(ajaxurl, {action: 'dplrwoo_ajax_synch', buyers_list: buyersList, contacts_list : contactsList});
		}

		syncListsButton.click(function(e){
			e.preventDefault();
			verifyKeys().then(syncrhonizeLists);
		});

		function verifyKeys(){
			syncListsButton.attr('disabled','disabled').addClass("button--loading");
			$("#dplr-settings-text").html(ObjWCStr.Synchronizing);
			return $.post(ajaxurl, {action: 'dplrwoo_ajax_verify_keys'});
		}

		function syncrhonizeLists(resp){
			var buyersList = buyersListSelect.val();
			var contactsList = contactListSelect.val();
			$.when(createDefaultList(buyersList, 'buyers'), createDefaultList(contactsList, 'contacts')).done(function(bl,cl){
				$.when(synchLists(bl,cl)).done(function(){
					listsForm.submit();
				});
			});
		}

		$("#dplrwoo-form-list-new input[type=text]").keyup(function(){
			var button = $(this).closest('form').find('button');
			if($(this).val().length>0){
				button.removeAttr('disabled');
				return false;
			}
			button.attr('disabled',true);
		});

		$("#dplrwoo-save-list").click(function(e){
			e.preventDefault();
			clearResponseMessages();
			var button = $(this);
			var listInput = $(this).closest('form').find('input[type="text"]');
			var listName = listInput.val();
			if(listName=='') return false;
			button.attr('disabled',true).addClass("button--loading");
			var data = {
				action: 'dplrwoo_ajax_save_list',
				listName: listName
			}
			$.post( ajaxurl, data, function( response ){
				var body = 	JSON.parse(response);
				if(body.createdResourceId){		
					var html ='<option value="'+body.createdResourceId+'">'+listName+'</option>';
					listsForm.find('select option:first-child').after(html);
					listInput.val('');
					button.attr('disabled',true);
					displaySuccess(ObjWCStr.listSavedOk);
				}else if(body.status >= 400){
					displayErrors(body);
				}
				button.removeAttr('disabled').removeClass("button--loading");
			})
		});

		$('.deactivate a').click(function (e) {
			if($(this).closest('tr').attr('data-plugin') == 'doppler-for-woocommerce/doppler-for-woocommerce.php'){
				var href = $(this).attr('href');
				e.preventDefault();
				$( "#dplrwoo-dialog" ).dialog({
					resizable: false,
					height: "auto",
					width: 400,
					modal: true,
					buttons: {
					  "Confirm": function() {
						  window.location = href;
					  },
					  Cancel: function() {
						$( this ).dialog( "close" );
					  }
					}
				});
			}
		});
		
	});

	function checkFieldType(dplrType, wcType){
		var types = {
			'string': ['string','state','country'],
			'gender': ['radio'],
			'email' : ['email'],
			'country':['country','string'],
			'phone' : ['tel'],
			'number': ['number'],
			'date'  : ['date','datetime','datetime-local'],
			'boolean':['checkbox'],
		}

		if( $.inArray(wcType,types[dplrType]) !== -1 || (dplrType === 'string' && wcType === '') ) {
			return true;
		}

		return false;
	}

	function createDefaultList(list_id, list_type){
		var deferred = new $.Deferred();
		if(list_id!='0'){
			deferred.resolve(list_id);
		}else{
			var listName = '';
			var selectElement = {}
			if(list_type!='buyers' && list_type!='contacts')deferred.resolve(false);
			if(list_type=='buyers'){
				listName = ObjWCStr.default_buyers_list;
				selectElement = $('#buyers-list option:first');
			}
			if(list_type=='contacts'){
				listName = ObjWCStr.default_contacts_list;
				selectElement = $('#contacts-list option:first');
			}
			$.post(ajaxurl, {action: 'dplrwoo_ajax_save_list', listName},function(response){
				var body = 	JSON.parse(response);
				if(body.createdResourceId){	
					selectElement.attr('value',body.createdResourceId);
					deferred.resolve(body.createdResourceId);
				}else{
					deferred.resolve(body.status);
				}
			})
		}
		return deferred.promise();
	}

	
})( jQuery );