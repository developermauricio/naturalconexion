<?php
/*
	uninstall.php file
	this script will remove Addi plugin and tables related
	to it in database.
*/

// exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) die;

// delete database table
global $wpdb;
$table_name = $wpdb->prefix .'wc_addi_gateway';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

$table_config_name = $wpdb->prefix .'wc_addi_config';
$wpdb->query("DROP TABLE IF EXISTS {$table_config_name}");
?>
