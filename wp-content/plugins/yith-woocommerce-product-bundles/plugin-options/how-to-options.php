<?php
/**
 * How-to tab options.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles\Options
 */

defined( 'YITH_WCPB' ) || exit;

return array(
	'how-to' => array(
		'how-to' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcpb_how_to_tab',
		),
	),
);
