<?php

if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
}

if ( ! function_exists( 'woodmart_vc_map_html_block' ) ) {
	function woodmart_vc_map_html_block() {
		if ( ! shortcode_exists( 'html_block' ) ) {
			return;
		}

		vc_map(
			array(
				'name'        => esc_html__( 'HTML Block', 'woodmart' ),
				'base'        => 'html_block',
				'category'    => esc_html__( 'Theme elements', 'woodmart' ),
				'description' => esc_html__( 'Display pre-built HTML block', 'woodmart' ),
				'icon'        => WOODMART_ASSETS . '/images/vc-icon/html-block.svg',
				'params'      => array(
					array(
						'type'       => 'dropdown',
						'heading'    => esc_html__( 'Select block', 'woodmart' ),
						'param_name' => 'id',
						'value'      => array( esc_html__( 'Select', 'woodmart' ) => '' ) + woodmart_get_static_blocks_array(),
					),
				),
			)
		);
	}
	add_action( 'vc_before_init', 'woodmart_vc_map_html_block' );
}
