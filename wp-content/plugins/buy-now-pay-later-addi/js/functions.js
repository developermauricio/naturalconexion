	jQuery(document).ready( function($) {

		const widgetPositionValue = $('select#woocommerce_addi_field_widget_position').value;
		
		$('label[for=woocommerce_addi_element_reference]').parent().parent().addClass('hidden');
		
		if (widgetPositionValue === 'custom') {        			
			$('label[for=woocommerce_addi_element_reference]').parent().parent().removeClass('hidden');
		}
		
		$('select#woocommerce_addi_field_widget_position').on('change', function() {
			//console.log('select changed!');
			//console.log(this.value);
			const option = this.value;
			
			if(option === 'custom') {
				//$('input#woocommerce_addi_element_reference').removeClass('hidden');
				$('label[for=woocommerce_addi_element_reference]').parent().parent().removeClass('hidden');
			}
			else {
				//$('input#woocommerce_addi_element_reference').addClass('hidden');
				$('label[for=woocommerce_addi_element_reference]').parent().parent().addClass('hidden');
			}
		});
		
		var billingField = getCookie("billingField");
		
		if(billingField && billingField !== null && billingField !== "null") {
			$('#woocommerce_addi_field_id').val(billingField);
			$('#woocommerce_addi_field_id').attr("disabled", "disabled");
			deleteCookie("billingField");
		} 

		var slug_value = $("#woocommerce_addi_widget_slug").val();

		if (slug_value === '' || slug_value === ' ') {
			$('#woocommerce_addi_widget_enabled').attr("disabled", "disabled");
			$('#woocommerce_addi_widget_enabled').removeAttr("checked");
		}
		else{
			$('#woocommerce_addi_widget_enabled').removeAttr("disabled");
		}

		$("#woocommerce_addi_widget_slug").keyup(function(e){

			if(e.target.value === '' || e.target.value === ' ') {
				$('#woocommerce_addi_widget_enabled').attr("disabled", "disabled");
				$('#woocommerce_addi_widget_enabled').removeAttr("checked");
			}
			else{
				$('#woocommerce_addi_widget_enabled').removeAttr("disabled");
			}
		});

		/* see more link*/
		$('label[for=woocommerce_addi_widget_section_widget_header]').
		after('<a target="popup" href="https://s3.amazonaws.com/statics.addi.com/assets/manuals/guide-widget.png">Ver Ejemplo</a>');
		
		$('label[for=woocommerce_addi_widget_section_modal_header]')
			.after("<a target='popup' href='https://s3.amazonaws.com/statics.addi.com/assets/manuals/guide-modal.png'>Ver Ejemplo</a>");
		
			$('label[for=woocommerce_addi_field_widget_type]')
			.after("<a target='popup' href='https://s3.amazonaws.com/statics.addi.com/addi-home-banner/addiHomeBannerOptions.png'>Ver Ejemplo</a>");
		
		/* js script for widget/modal styles */
		/* widget style*/
		$('#woocommerce_addi_widgetBorderColor').after("<span class='customCircle addiWidget step1'>1</span>");
		$('#woocommerce_addi_widgetBorderRadius').after("<span class='customCircle addiWidget step2'>2</span>");
		$('#woocommerce_addi_widgetFontColor').after("<span class='customCircle addiWidget step3'>A</span>");
		$('#woocommerce_addi_widgetFontFamily').after("<span class='customCircle addiWidget step4'>B</span>");
		$('#woocommerce_addi_widgetFontSize').after("<span class='customCircle addiWidget step5'>C</span>");
		$('#woocommerce_addi_widgetBadgeBackgroundColor').after("<span class='customCircle addiWidget step6'>4</span>");
		$('#woocommerce_addi_widgetInfoBackgroundColor').after("<span class='customCircle addiWidget step7'>5</span>");
		$('#woocommerce_addi_widgetMargin').after("<span class='customCircle addiWidget step8'>6</span>");
		$('#woocommerce_addi_modalBadgeLogoStyle').after("<span class='customCircle addiWidget step9'>7</span>");
		/* modal style*/
		$('#woocommerce_addi_modalBackgroundColor').after("<span class='customCircle addiWidgetModal step1'>1</span>");
		$('#woocommerce_addi_modalFontColor').after("<span class='customCircle addiWidgetModal step2'>A</span>");
		$('#woocommerce_addi_modalPriceColor').after("<span class='customCircle addiWidgetModal step3'>3</span>");
		$('#woocommerce_addi_modalBadgeBackgroundColor').after("<span class='customCircle addiWidgetModal step4'>A</span>");
		$('#woocommerce_addi_modalBadgeBorderRadius').after("<span class='customCircle addiWidgetModal step5'>B</span>");
		$('#woocommerce_addi_modalBadgeFontColor').after("<span class='customCircle addiWidgetModal step6'>C</span>");
		$('#woocommerce_addi_modalCardColor').after("<span class='customCircle addiWidgetModal step7'>5</span>");
		$('#woocommerce_addi_modalButtonBorderColor').after("<span class='customCircle addiWidgetModal step8'>A</span>");
		$('#woocommerce_addi_modalButtonBorderRadius').after("<span class='customCircle addiWidgetModal step9'>B</span>");
		$('#woocommerce_addi_modalButtonBackgroundColor').after("<span class='customCircle addiWidgetModal step10'>C</span>");
		$('#woocommerce_addi_modalButtonFontColor').after("<span class='customCircle addiWidgetModal step11'>D</span>");
		/* js script for widget/modal styles */
		
		function getCookie(cname) {
		let name = cname + "=";
		let decodedCookie = decodeURIComponent(document.cookie);
		let ca = decodedCookie.split(';');
		for(let i = 0; i <ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) == ' ') {
			c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
			}
		}
		return "";
		}
		
		function setCookie(cname, cvalue, exdays) {
		const d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		let expires = "expires="+ d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}
		
		function deleteCookie(name) {
		document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
		}
	});