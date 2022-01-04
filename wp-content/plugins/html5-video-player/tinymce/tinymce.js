jQuery(document).ready(function($) {
	
			sliderList = jQuery('#h5vptinymce_select_slider');

// END LOAD MEDIA

	jQuery("body").delegate("#h5vp_shortcode_button","click",function(){	
		
			mg_H = 300;
			mg_W = 550;
			sliderList.find('option').remove();
			jQuery("<option/>").val(0).text('Loading...').appendTo(sliderList);
			
		setTimeout(function() {
			tb_show( 'Insert Html5 Video Player', '#TB_inline?height='+mg_H+'&width='+mg_W+'&inlineId=h5vpmodal' );
			jQuery('#TB_window').css("height", mg_H);
			jQuery('#TB_window').css("width", mg_W);
			jQuery('#TB_window').css("top", ((jQuery(window).height() - mg_H) / 6) + 'px');
			jQuery('#TB_window').css("left", ((jQuery(window).width() - mg_W) / 4) + 'px');
			jQuery('#TB_window').css("margin-top", ((jQuery(window).height() - mg_H) / 6) + 'px');
			jQuery('#TB_window').css("margin-left", ((jQuery(window).width() - mg_W) / 4) + 'px');
			jQuery("#TB_window").css('height','auto');
			jQuery("#TB_ajaxContent").css('height','auto');
			jQuery("select#h5vptinymce_select_slider").val("select");
			
			//load ajax to grab slider list ( we need this methode to avoid conflict in media editor with another plugin )
			grabslider();
			

		}, 300);	
		
	});
	
	// add the shortcode to the post editor
	jQuery('#h5vp_insert_scrt').on("click", function () {

		if ( jQuery( "#h5vptinymce_select_slider" ).val() != 'select' ) {
		
			var sccode;
			sccode = "[video id="+jQuery( "#h5vptinymce_select_slider option:selected" ).val()+"]";
		
			if( jQuery('#wp-content-editor-container > textarea').is(':visible') ) {
				var val = jQuery('#wp-content-editor-container > textarea').val() + sccode;
				jQuery('#wp-content-editor-container > textarea').val(val);	
				}
				else {
				tinyMCE.activeEditor.execCommand('mceInsertContent', 0, sccode);
					}

			tb_remove();
			}
			else {
				alert('Please select slider first!');
				//tb_remove();
				}
		});	
		
		
function grabslider() {
	
			jQuery.ajax({
			url: ajaxurl,
			data:{
				'action': 'h5vp_grab_slider_list_ajax',
				'grabslider': 'yes'
			},
			dataType: 'JSON',
			type: 'POST',
			success:function(response){
				sliderList.find('option').remove();
				jQuery("<option/>").val('select').text('- Select Player -').appendTo(sliderList);
				jQuery.each(response, function(i, option)
				{
					jQuery("<option/>").val(option.val).text(option.title).appendTo(sliderList);
				});
			},
			error: function(errorThrown){
			   jQuery("<option/>").val('select').text('- Select Player -').appendTo(sliderList);
			}
			
		}); // End Grab	
		
}
		
		
});