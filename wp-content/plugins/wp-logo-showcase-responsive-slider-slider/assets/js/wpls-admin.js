( function( $ ) {

	"use strict";

	/* Vertical Tab */
	$( document ).on( "click", ".wpls-vtab-nav a", function() {

		$(".wpls-vtab-nav").removeClass('wpls-active-vtab');
		$(this).parent('.wpls-vtab-nav').addClass("wpls-active-vtab");

		var selected_tab = $(this).attr("href");
		$('.wpls-vtab-cnt').hide();

		/* Show the selected tab content */
		$(selected_tab).show();

		/* Pass selected tab */
		$('.wpls-selected-tab').val(selected_tab);
		return false;
	});

	/* Remain selected tab for user */
	if( $('.wpls-selected-tab').length > 0 ) {
		
		var sel_tab = $('.wpls-selected-tab').val();

		if( typeof(sel_tab) !== 'undefined' && sel_tab != '' && $(sel_tab).length > 0 ) {
			$('.wpls-vtab-nav [href="'+sel_tab+'"]').click();
		} else {
			$('.wpls-vtab-nav:first-child a').click();
		}
	}

	/* Click to Copy the Text */
	$(document).on('click', '.wpos-copy-clipboard', function() {
		var copyText = $(this);
		copyText.select();
		document.execCommand("copy");
	});

	/* Drag widget event to render layout for Beaver Builder */
	$('.fl-builder-content').on( 'fl-builder.preview-rendered', wpls_fl_render_preview );

	/* Save widget event to render layout for Beaver Builder */
	$('.fl-builder-content').on( 'fl-builder.layout-rendered', wpls_fl_render_preview );

	/* Publish button event to render layout for Beaver Builder */
	$('.fl-builder-content').on( 'fl-builder.didSaveNodeSettings', wpls_fl_render_preview );

})( jQuery );

/* Function to render shortcode preview for Beaver Builder */
function wpls_fl_render_preview() {
	wpls_logo_slider_init();
}