(function($) {
	$('#vc_ui-panel-edit-element').on('vcPanel.shown', function() {

		$('.wd-numbers').each(function() {
			let $wrapper = $(this);

			$wrapper.find('.wd-device').on( 'click', function() {
				let $this = $(this);

				updateActiveClass($this);
				updateActiveClass($wrapper.find('.wd-number[data-device="'+ $this.data('value') +'"]'));
			});

			$wrapper.find('.wd-number').each(function() {
				let $this = $(this);

				$this.on( 'change', function () {
					setMainValue();
				}).trigger('change');
			});

			function setMainValue() {
				let $valueInput = $wrapper.find('.wpb_vc_param_value');
				let sliderSettings = $valueInput.data('settings');

				if ( 'undefined' === typeof sliderSettings.selectors) {
					return;
				}

				let $results = {
					devices: {},
					selector_id: $('.woodmart-css-id').val(),
					shortcode: $('#vc_ui-panel-edit-element').attr('data-vc-shortcode'),
					selectors:sliderSettings.selectors,
				};

				$wrapper.find('.wd-number').each(function() {
					let $this = $(this);

					$results.devices[$this.attr('data-device')] = {
						value: $this.val(),
					};
				});

				$valueInput.val(window.btoa(JSON.stringify($results)));
			}
		});


		/**
		 * Update Active Class.
		 */
		function updateActiveClass($this) {
			$this.siblings().removeClass('xts-active');
			$this.addClass('xts-active');
		}
	});
})(jQuery);
