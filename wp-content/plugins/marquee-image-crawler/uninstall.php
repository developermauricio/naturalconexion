<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function mic_delete_plugin() {
	global $wpdb;

	delete_option( 'marquee-image-crawler' );

	$wpdb->query( sprintf( "DROP TABLE IF EXISTS %s",
		$wpdb->prefix . 'marquee_img_crawler' ) );
}

mic_delete_plugin();