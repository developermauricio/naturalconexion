(function($) {
	$('#vc_ui-panel-edit-element').on('vcPanel.shown', function() {

		$('.wd-sliders').each(function() {
			let $wrapper = $(this);
			let $valueInput = $wrapper.find('.wpb_vc_param_value')
			let sliderSettings = $valueInput.data('settings');

			$wrapper.find('.wd-device').on( 'click', function() {
				let $this = $(this);
				let device = $this.data('value');

				updateActiveClass($this);
				updateActiveClass($wrapper.find('.wd-slider[data-device="'+ device +'"]'));
			});

			$wrapper.find('.wd-slider').each(function() {
				let $this = $(this);
				let $slider = $this.find('.wd-slider-field');
				let $valuePreview = $this.find('.wd-slider-value-preview');
				let device = $this.data('device');
				let unit = sliderSettings.devices[device].unit;

				initSlider(device, unit);
				setMainValue();

				$this.find('.wd-slider-unit-control').on( 'click', function() {
					let count_unit = [];

					$.each( sliderSettings.range, function(key, value) {
						count_unit.push(key)
					});

					if ( 1 === count_unit.length ) {
						return;
					}

					let $this = $(this);
					let device = $this.parents('.wd-slider').data('device');

					updateActiveClass($this);
					initSlider( device, $this.data('unit') );
					$this.parents('.wd-slider').attr('data-unit', $this.data('unit'));
				});

				/**
				 * Change Unit.
				 */
				function initSlider( device, unit ) {
					if ( 'undefined' !== typeof $slider.slider() ) {
						$slider.slider('destroy');
					}

					let deviceData = sliderSettings.devices[device];
					let value = 0;

					if ( deviceData.unit === unit ) {
						value = deviceData.value;
					}

					$valuePreview.text(value);

					$slider.slider({
						range: 'min',
						value: value,
						min  : sliderSettings.range[unit].min,
						max  : sliderSettings.range[unit].max,
						step : sliderSettings.range[unit].step,
						slide: function(event, ui) {
							$this.attr('data-value', ui.value);
							$valuePreview.text(ui.value);
							setMainValue();
						}
					});
				}

				function setMainValue() {
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

					$wrapper.find('.wd-slider').each(function() {
						let $this = $(this);

						$results.devices[$this.attr('data-device')] = {
							unit: $this.attr('data-unit'),
							value: $this.attr('data-value'),
						};
					});

					$valueInput.attr('value', window.btoa(JSON.stringify($results)));
				}
			});
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
