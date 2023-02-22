( function($) {

	'use strict';

	jQuery(window).on('elementor/frontend/init', function() {

		elementorFrontend.hooks.addAction( 'frontend/element_ready/wp-widget-text.default', wpls_elementor_init );
		elementorFrontend.hooks.addAction( 'frontend/element_ready/shortcode.default', wpls_elementor_init );
		elementorFrontend.hooks.addAction( 'frontend/element_ready/text-editor.default', wpls_elementor_init );

		/* Tabs Element */
		elementorFrontend.hooks.addAction( 'frontend/element_ready/tabs.default', function( $scope ) {

			if( $scope.find('.wpls-logo-showcase').length >= 1 ) {
				$scope.find('.elementor-tabs-content-wrapper').addClass('wpls-elementor-tab-wrap');
			} else {
				$scope.find('.elementor-tabs-content-wrapper').removeClass('wpls-elementor-tab-wrap');
			}

			$scope.find('.wpls-logo-showcase').each(function( index ) {
				var slider_id = $(this).attr('id');
				$('#'+slider_id).css({'visibility': 'hidden', 'opacity': 0});

				wpls_elementor_init();

				setTimeout(function() {

					/* Tweak for slick slider */
					if( typeof(slider_id) !== 'undefined' && slider_id != '' ) {
						$('#'+slider_id).slick( 'setPosition' );
						$('#'+slider_id).css({'visibility': 'visible', 'opacity': 1});
					}
				}, 300);
			});
		});

		/* Accordion Element */
		elementorFrontend.hooks.addAction( 'frontend/element_ready/accordion.default', function( $scope ) {

			$scope.find('.wpls-logo-showcase').each(function( index ) {
				var slider_id = $(this).attr('id');
				$('#'+slider_id).css({'visibility': 'hidden', 'opacity': 0});

				wpls_elementor_init();

				setTimeout(function() {

					/* Tweak for slick slider */
					if( typeof(slider_id) !== 'undefined' && slider_id != '' ) {
						$('#'+slider_id).slick( 'setPosition' );
						$('#'+slider_id).css({'visibility': 'visible', 'opacity': 1});
					}
				}, 300);
			});
		});

		/* Toggle Element */
		elementorFrontend.hooks.addAction( 'frontend/element_ready/toggle.default', function( $scope ) {

			$scope.find('.wpls-logo-showcase').each(function( index ) {
				var slider_id = $(this).attr('id');
				$('#'+slider_id).css({'visibility': 'hidden', 'opacity': 0});

				wpls_elementor_init();

				setTimeout(function() {

					/* Tweak for slick slider */
					if( typeof(slider_id) !== 'undefined' && slider_id != '' ) {
						$('#'+slider_id).slick( 'setPosition' );
						$('#'+slider_id).css({'visibility': 'visible', 'opacity': 1});
					}
				}, 300);
			});
		});
	});

	/**
	 * Initialize Plugin Functionality
	 */
	function wpls_elementor_init() {
		wpls_logo_slider_init();
	}
})(jQuery);