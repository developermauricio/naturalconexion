<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Input_Select {

	public function __construct() {
		// vars
		$this->type = 'Select';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'class'         => '',
		);
	}

	public function render( $field, $value = null ) {

		$field          = array_merge( $this->defaults, $field );
		$field['value'] = $value;
		$optgroup       = false;

		// determin  if choices are grouped (2 levels of array)
		if ( is_array( $field['choices'] ) ) {
			foreach ( $field['choices'] as $k => $v ) {
				if ( is_array( $v ) ) {
					$optgroup = true;
				}
			}
		}

		// value must be array
		if ( ! is_array( $field['value'] ) ) {
			// perhaps this is a default value with new lines in it?
			if ( strpos( $field['value'], "\n" ) !== false ) {
				// found multiple lines, explode it
				$field['value'] = explode( "\n", $field['value'] );
			} else {
				$field['value'] = array( $field['value'] );
			}
		}

		// trim value
		$field['value'] = array_map( 'trim', $field['value'] );

		$multiple = '';
		if ( $field['multiple'] ) {
			$multiple      = ' multiple="multiple" size="5" ';
			$field['name'] .= '[]';
		}

		echo '<select id="' . $field['id'] . '" class="' . $field['class'] . '" name="' . $field['name'] . '" ' . $multiple . ' >';

		// null
		if ( $field['allow_null'] ) {
			echo '<option value="null"> - Select - </option>';
		}

		// loop through values and add them as options
		if ( is_array( $field['choices'] ) ) {
			foreach ( $field['choices'] as $key => $value ) {
				if ( $optgroup ) {
					// this select is grouped with optgroup
					if ( $key != '' ) {
						echo '<optgroup label="' . $key . '">';
					}

					if ( is_array( $value ) ) {
						foreach ( $value as $id => $label ) {
							$selected    = in_array( $id, $field['value'] ) ? 'selected="selected"' : '';
							$rule_object = WCCT_Common::woocommerce_wcct_rule_get_rule_object( $id );
							$disabled    = '';
							if ( $field['class'] == 'rule_type' && empty( $rule_object ) ) {
								$disabled = 'disabled';
							}
							echo '<option value="' . $id . '" ' . $selected . ' ' . $disabled . '>' . $label . '</option>';
						}
					}

					if ( $key != '' ) {
						echo '</optgroup>';
					}
				} else {
					$selected = in_array( $key, $field['value'] ) ? 'selected="selected"' : '';
					echo '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
				}
			}
		}

		echo '</select>';
	}

}


