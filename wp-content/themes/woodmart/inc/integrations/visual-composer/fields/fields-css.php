<?php
/**
 * This file generates fields css.
 *
 * @package Woodmart.
 */

if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Shortcodes css formatter
 */
if ( ! function_exists( 'woodmart_parse_shortcodes_css_data_new' ) ) {
	/**
	 * This function parse post content data and return fields params.
	 *
	 * @param mixed $content post content.
	 * @return string|void
	 * @throws Exception .
	 */
	function woodmart_parse_shortcodes_css_data_new( $content ) {
		$css_data = '';

		if ( ! class_exists( 'WPBMap' ) ) {
			return;
		}

		$woodmart_fields = array(
			'wd_slider',
			'wd_number',
			'woodmart_box_shadow',
		);

		WPBMap::addAllMappedShortcodes();
		preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes );

		foreach ( $shortcodes[2] as $index => $tag ) {
			$shortcode  = WPBMap::getShortCode( $tag );
			$attr_array = shortcode_parse_atts( trim( $shortcodes[3][ $index ] ) );

			if ( isset( $shortcode['params'] ) && ! empty( $shortcode['params'] ) ) {
				foreach ( $shortcode['params'] as $param ) {
					if ( isset( $param['type'] ) && in_array( $param['type'], $woodmart_fields, true ) && isset( $attr_array[ $param['param_name'] ] ) ) {
						$css_data .= $attr_array[ $param['param_name'] ] . '[|]';
					}
				}
			}
		}

		foreach ( $shortcodes[5] as $shortcode_content ) {
			$css_data .= woodmart_parse_shortcodes_css_data_new( $shortcode_content );
		}

		return $css_data;
	}
}

if ( ! function_exists( 'woodmart_get_fields_css' ) ) {
	/**
	 * This function return field css.
	 *
	 * @param int $post_id Post id.
	 * @throws Exception .
	 */
	function woodmart_get_fields_css( $post_id ) {
		$post       = get_post( $post_id );
		$css_data   = woodmart_parse_shortcodes_css_data_new( $post->post_content );
		$data_array = array_filter( explode( '[|]', $css_data ) );
		return woodmart_fields_css_data_to_css( $data_array );
	}
}

if ( ! function_exists( 'woodmart_save_fields_css' ) ) {
	/**
	 * This function save field css.
	 *
	 * @param int $post_id Post id.
	 * @throws Exception .
	 */
	function woodmart_save_fields_css( $post_id ) {
		$css  = woodmart_get_fields_css( $post_id );
		$css .= woodmart_get_fields_css_old( $post_id );

		if ( empty( $css ) ) {
			delete_post_meta( $post_id, 'woodmart_shortcodes_custom_css' );
		} else {
			update_post_meta( $post_id, 'woodmart_shortcodes_custom_css', $css );
		}
	}

	add_action( 'save_post', 'woodmart_save_fields_css' );
}

if ( ! function_exists( 'woodmart_fields_css_data_to_css' ) ) {
	/**
	 * This function prepares the css.
	 *
	 * @param array $css_data array with css data in base64.
	 * @return string $result finished css.
	 */
	function woodmart_fields_css_data_to_css( $css_data ) {
		$result          = '';
		$sorted_css_data = array();

		foreach ( $css_data as $value ) {
			$decompressed_data = function_exists( 'woodmart_decompress' ) ? json_decode( woodmart_decompress( $value ), true ) : '';

			if ( isset( $decompressed_data['selectors'] ) ) {
				$wrapper_prefix = '.wd-rs-';

				$wrapper = $wrapper_prefix . $decompressed_data['selector_id'];
				foreach ( $decompressed_data['devices'] as $device => $device_value ) {
					foreach ( $decompressed_data['selectors'] as $selector => $properties ) {
						$selector = str_replace( '{{WRAPPER}}', $wrapper, $selector );

						if ( is_array( $properties ) ) {
							$properties = implode( '', $properties );
						}

						if ( false !== stripos( $properties, 'box-shadow' ) ) {
							$properties = str_replace( '{{HORIZONTAL}}', $device_value['horizontal'], $properties );
							$properties = str_replace( '{{VERTICAL}}', $device_value['vertical'], $properties );
							$properties = str_replace( '{{BLUR}}', $device_value['blur'], $properties );
							$properties = str_replace( '{{SPREAD}}', $device_value['spread'], $properties );
							$properties = str_replace( '{{COLOR}}', $device_value['color'], $properties );
						} else {
							if ( empty( $device_value['value'] ) ) {
								continue;
							}

							if ( isset( $device_value['value'] ) ) {
								$properties = str_replace( '{{VALUE}}', $device_value['value'], $properties );
							}
							if ( isset( $device_value['unit'] ) ) {
								$properties = str_replace( '{{UNIT}}', $device_value['unit'], $properties );
							}
						}

						$sorted_css_data[ $device ][] = "$selector {\n\t $properties \n}\n\n";
					}
				}
			}
		}

		foreach ( $sorted_css_data as $device => $styles ) {
			$device_styles = '';

			if ( 'desktop' === $device ) {
				$device_styles .= implode( '', $styles );
				$result        .= $device_styles;
			}
			if ( 'tablet' === $device ) {
				$device_styles .= implode( '', $styles );
				$result        .= "@media (max-width: 1024px) {\n\t $device_styles \n}\n\n";
			}
			if ( 'mobile' === $device ) {
				$device_styles .= implode( '', $styles );
				$result        .= "@media (max-width: 767px) {\n\t $device_styles \n}\n\n";
			}
		}

		return $result;
	}
}
