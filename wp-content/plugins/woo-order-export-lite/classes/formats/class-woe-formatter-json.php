<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Formatter_Json extends WOE_Formatter {
	var $prev_added = false;
	var $formatting_flags = NULL;

	public function __construct(
		$mode,
		$filename,
		$settings,
		$format,
		$labels,
		$field_formats,
		$date_format,
		$offset
	) {
		parent::__construct( $mode, $filename, $settings, $format, $labels, $field_formats, $date_format, $offset );
		$this->prev_added = ( $offset > 0 );
		if($this->mode == 'preview') {
			$this->formatting_flags |= JSON_PRETTY_PRINT;
		}
		if($settings['unescaped_slashes']) {
			$this->formatting_flags |= JSON_UNESCAPED_SLASHES;
		}
		if($settings['numeric_check']) {
			$this->formatting_flags |= JSON_NUMERIC_CHECK;
		}
        if($settings['encode_unicode']) {
            $this->formatting_flags |= JSON_UNESCAPED_UNICODE;
        }
	}

	public function start( $data = '' ) {
		parent::start( $data );

		$start_text = $this->convert_literals( $this->settings['start_tag'] );

		fwrite( $this->handle, apply_filters( "woe_json_start_text", $start_text ) );
	}

	public function output( $rec ) {
		$rec = parent::output( $rec );
		if ( $this->prev_added ) {
			fwrite( $this->handle, "," );
		}
		if ( $this->mode == 'preview' ) {
			fwrite( $this->handle, "\n" );
		}

		//rename fields in array
		$rec_out = array();
		$labels  = $this->labels['order'];


		foreach ( $labels->get_labels() as $label_data ) {
			$original_key = $label_data['key'];
			$label        = $label_data['label'];
			$key          = $label_data['parent_key'] ? $label_data['parent_key'] : $original_key;

			$field_value = $rec[ $key ];
			if ( is_array( $field_value ) ) {
				if ( $original_key == "products" ) {
					$child_labels = $this->labels['products'];
				} elseif ( $original_key == "coupons" ) {
					$child_labels = $this->labels['coupons'];
				} else {
					$rec_out[ $label ] = $field_value;
					continue;
				}


				if ( $child_labels->is_not_empty() == false ) // can't export!
				{
					continue;
				}

				$rec_out[ $label ] = array();
				foreach ( $field_value as $child_element ) {
					$child = array();
					foreach ( $child_labels->get_labels() as $child_label_data ) {
						$child_original_key = $child_label_data['key'];
						$child_label        = $child_label_data['label'];
						$child_key          = $child_label_data['parent_key'] ? $child_label_data['parent_key'] : $child_original_key;
						if ( isset( $child_element[ $child_key ] ) ) {
							$child[ $child_label ] = $child_element[ $child_key ];
						}
					}
					$rec_out[ $label ][] = $child;
				}

			} else {
				$rec_out[ $label ] = $field_value;
			}
		}

		$json = json_encode($rec_out, $this->formatting_flags);

		if ( $this->has_output_filter ) {
			$json = apply_filters( "woe_json_output_filter", $json, $rec_out, $this );
		}

		fwrite( $this->handle, $json );

		// first record added!
		if ( ! $this->prev_added ) {
			$this->prev_added = true;
		}
	}

	public function finish( $data = '' ) {
		if ( $this->mode == 'preview' ) {
			fwrite( $this->handle, "\n" );
		}

		$end_text = $this->convert_literals( $this->settings['end_tag'] );
		fwrite( $this->handle, apply_filters( "woe_json_end_text", $end_text ) );
		parent::finish();
	}
}