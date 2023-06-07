<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function hsas_delete_plugin() {
	global $wpdb;

	delete_option( 'horizontal-scrolling-announcements' );

	$wpdb->query( sprintf( "DROP TABLE IF EXISTS %s",
		$wpdb->prefix . 'horizontal_scrolling_hsas' ) );
}

hsas_delete_plugin();