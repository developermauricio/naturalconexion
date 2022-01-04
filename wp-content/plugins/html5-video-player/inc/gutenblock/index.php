<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



function h5vp_ter_block_type($myBlockName, $h5vp_BlockOption = array()) {
	register_block_type(
		'h5vp-kit/' . $myBlockName,
		array_merge(
			array(
				'editor_script' => 'h5vp-kit-editor-script',
				'editor_style' => 'h5vp-kit-editor-style',
				'script' => 'h5vp-kit-front-script',
				'style' => 'h5vp-kit-front-style'
			),
			$h5vp_BlockOption
		)
	);
}

function h5vp_blocks_script() {
	wp_register_script(
		'h5vp-kit-editor-script',
		plugins_url('dist/js/editor-script.js', __FILE__),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-editor',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-autop',
		)
	);	
	h5vp_ter_block_type('kahf-banner-k27f', array(
		'render_callback' => 'h5vp_block_custom_post_fun',
		'attributes' => array(
			'postName' => array(	
				'type' => 'string',
				'source' => 'html',
			),
		)
	));
}
add_action('init', 'h5vp_blocks_script');


function h5vp_block_custom_post_fun ( $attributes, $content ) {
	
	return wpautop( $content );
}