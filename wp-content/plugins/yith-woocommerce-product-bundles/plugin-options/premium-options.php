<?php
/**
 * Premium tab options.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Options
 */

defined( 'YITH_WCPB' ) || exit;

return array(
	'premium' => array(
		'landing' => array(
			'type'         => 'custom_tab',
			'action'       => 'yith_wcpb_premium_tab',
			'hide_sidebar' => true,
		),
	),
);
