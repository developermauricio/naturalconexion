(function($) {
	var $panel = $('#vc_ui-panel-edit-element');
	// Dependency.
	$panel.on('vcPanel.shown', function() {
		var $fieldValue = $('.vc_shortcode-param .wpb_vc_param_value');

		$fieldValue.on('change', function() {
			checkDependency($(this));
		});

		$fieldValue.each(function() {
			checkDependency($(this));
		});

		function checkDependency($this) {
			var name = $this.attr('name');
			var value = $this.val();

			$('.vc_shortcode-param').each(function() {
				var param = $(this).data('param_settings');

				if (param.wd_dependency) {
					if (param.wd_dependency.element === name) {
						if (param.wd_dependency.value[0] === value) {
							$(this).show();
						} else {
							$(this).hide();
						}
					}
				}
			});
		}
	});

	//Tooltips
	$panel.on('vcPanel.shown', function() {
		var $tooltips = $('.woodmart-css-tooltip');

		$tooltips.each(function() {
				var $label = $(this).find('.woodmart-tooltip-label');

				$label.remove();
				$(this).addClass('woodmart-tltp').prepend('<span class="woodmart-tooltip-label">' + $(this).data('text') + '</span>');
				$label.trigger('mouseover');
			})
			.off('mouseover.tooltips')
			.on('mouseover.tooltips', function() {
				var $label = $(this).find('.woodmart-tooltip-label'),
				    width  = $label.outerWidth();

				if ($('body').hasClass('rtl')) {
					$label.css({
						marginRight: -parseInt(width / 2)
					});
				} else {
					$label.css({
						marginLeft: -parseInt(width / 2)
					});
				}
			});
	});

	//Hint
	$panel.on('vcPanel.shown', function() {
		var $panel = $(this);

		$panel.find('.vc_shortcode-param').each(function() {
			var $this = $(this);
			var settings = $this.data('param_settings');

			if (typeof settings != 'undefined' && typeof settings.hint != 'undefined') {
				$this.find('.wpb_element_label').addClass('woodmart-with-hint').append('<div class="woodmart-hint">?<div class="woodmart-hint-content">' + settings.hint + '</div></div>');
			}
		});

		$panel.find('.woodmart-hint').on('hover mouseover', function() {
			var $hint = $(this);

			$hint.removeClass('woodmart-hint-right woodmart-hint-left');

			var hintPos = $hint.offset().left + $hint.find('.woodmart-hint-content').outerWidth();
			var panelPos = $panel.offset().left + $panel.find('.vc_edit_form_elements').width();

			if (hintPos > panelPos) {
				$hint.addClass('woodmart-hint-right');
			} else {
				$hint.addClass('woodmart-hint-left');
			}
		});

	});

})(jQuery);
