(function($) {
	$('#vc_ui-panel-edit-element').on('vcPanel.shown', function() {
		$('.wd-box-shadow').each(function() {
			let $this = $(this);
			let $valueInput = $this.find('.wpb_vc_param_value');
			let settings = JSON.parse( $valueInput.attr('data-settings') );

			if ( 'undefined' === typeof settings.selectors ) {
				return;
			}

			let $results = {
				devices: {
					desktop: {
						horizontal: 0,
						vertical: 0,
						blur: 0,
						spread: 0,
					},
				},
				selector_id: $('.woodmart-css-id').val(),
				shortcode: $('#vc_ui-panel-edit-element').attr('data-vc-shortcode'),
				selectors: settings.selectors,
			};

			initColorPicker($this, $results);
			initTextField($this, $results);
		});

		function initTextField($wrapper, data) {
			$wrapper.find('.wd-text-input').each(function() {
				let $this = $(this);
				let id = $this.attr('id');

				$this.on('change', function() {
					data.devices.desktop[id] = $this.val();
					setMainValue($wrapper, data);
				}).trigger('change');
			});
		}

		function setMainValue($this, $results) {
			let $valueInput = $this.find('.wpb_vc_param_value');

			$valueInput.attr('value', window.btoa(JSON.stringify($results)));
		}

		function initColorPicker($this, data) {
			let $pickerInput = $this.find('.wd-color-input');

			$pickerInput.wpColorPicker({
				change: function(event, ui) {
					data.devices.desktop['color'] = ui.color.toString();
					setMainValue($this, data);
				},
				clear : function() {
					data.devices.desktop['color'] = '';
					$opacityRange.val(100);
					$opacityOutput.val('100%');
					setMainValue($this, data);
				}
			});

			if (data.devices.desktop['color']) {
				$pickerInput.wpColorPicker('color', data.devices.desktop['color']);
			}

			// Opacity range add.
			let opacityVal = 100;
			let value = $pickerInput.val().replace(/\s+/g, '');

			if (value.match(/rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/)) {
				opacityVal = 100 * parseFloat(value.match(/rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/)[1]);
			}

			$('<div class="woodmart-opacity-container"><label>Opacity: <output class="rangevalue">' + opacityVal + '%</output></label><input type="range" min="1" max="100" value="' + opacityVal + '" name="opacity" class="woodmart-opacity-field"></div>').appendTo($this.addClass('woodmart-opacity-picker').find('.iris-picker'));

			let $opacityRange = $this.find('.woodmart-opacity-field');
			let $opacityOutput = $this.find('.woodmart-opacity-container output');

			$opacityRange.on('change', function() {
				opacityVal = parseFloat($opacityRange.val());
				$opacityOutput.val($opacityRange.val() + '%');

				let iris = $pickerInput.data('a8c-iris');
				let colorPicker = $pickerInput.data('wp-wpColorPicker');

				iris._color._alpha = opacityVal / 100;
				$pickerInput.val(iris._color.toString());

				colorPicker.toggler.css({
					backgroundColor: $pickerInput.val()
				});
				$pickerInput.wpColorPicker('color', $pickerInput.val());

			}).val(opacityVal).trigger('change');
		}
	});
})(jQuery);
