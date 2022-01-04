(function($) {
	$.each([
		'frontend/element_ready/wd_accordion.default',
	], function(index, value) {
		woodmartThemeModule.wdElementorAddAction(value, function() {
			woodmartThemeModule.accordion();
		});
	});

	woodmartThemeModule.accordion = function () {
		$('.wd-accordion').each(function() {
			var $wrapper = $(this);
			var $tabTitles = $wrapper.find('.wd-accordion-title');
			var $tabContents = $wrapper.find('.wd-accordion-content');
			var activeClass = 'wd-active';
			var state = $wrapper.data('state');
			var time = 300;

			var isTabActive = function(tabIndex) {
				return $tabTitles.filter('[data-accordion-index="' + tabIndex + '"]').hasClass(activeClass);
			};

			var activateTab = function(tabIndex) {
				var $requestedTitle = $tabTitles.filter('[data-accordion-index="' + tabIndex + '"]');
				var $requestedContent = $tabContents.filter('[data-accordion-index="' + tabIndex + '"]');

				$requestedTitle.addClass(activeClass);
				$requestedContent.stop().slideDown(time).addClass(activeClass);

				if ('first' === state && !$wrapper.hasClass('wd-inited')) {
					$requestedContent.stop().show().css('display', 'block');
				}

				$wrapper.addClass('wd-inited');
			};

			var deactivateActiveTab = function() {
				var $activeTitle = $tabTitles.filter('.' + activeClass);
				var $activeContent = $tabContents.filter('.' + activeClass);

				$activeTitle.removeClass(activeClass);
				$activeContent.stop().slideUp(time).removeClass(activeClass);
			};

			var getFirstTabIndex = function() {
				return $tabTitles.first().data('accordion-index');
			};

			if ('first' === state) {
				activateTab(getFirstTabIndex());
			}

			$tabTitles.on('click', function() {
				var tabIndex = $(this).data('accordion-index');
				var isActiveTab = isTabActive(tabIndex);

				if (isActiveTab) {
					deactivateActiveTab();
				} else {
					deactivateActiveTab();
					activateTab(tabIndex);
				}

				setTimeout(function() {
					woodmartThemeModule.$window.resize();
				}, time);
			});
		});
	}

	$(document).ready(function() {
		woodmartThemeModule.accordion();
	});
})(jQuery);