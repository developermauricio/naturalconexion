<?php
/**
 * Plugin Name: Horizontal scrolling announcements
 * Plugin URI: http://www.gopiplus.com/work/2010/07/18/horizontal-scrolling-announcement/
 * Description: This horizontal scrolling announcement wordpress plugin lets scroll the content from one end to another end like reel. This plugin is using JQuery Marquee script for scrolling.
 * Version: 1.5
 * Author: Gopi Ramasamy
 * Author URI: http://www.gopiplus.com/work/about/
 * Requires at least: 3.4
 * Tested up to: 5.3
 * Text Domain: horizontal-scrolling-announcements
 * Domain Path: /languages/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright (c) 2019 www.gopiplus.com
 */

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
	die('You are not allowed to call this page directly.');
}

$hsas_current_folder = dirname(__FILE__);
if(!defined('HSAS_DIR')) define('HSAS_DIR', $hsas_current_folder.DIRECTORY_SEPARATOR);
if(!defined('HSAS_ADMINURL')) define( 'HSAS_ADMINURL', site_url( '/wp-admin/admin.php' ) );
if(!defined('HSAS_URL')) define('HSAS_URL',plugins_url().'/'.strtolower('horizontal-scrolling-announcements').'/');

require_once($hsas_current_folder.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'hsas-defined.php');
require_once($hsas_current_folder.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'hsas-register.php');
require_once($hsas_current_folder.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'hsas-intermediate.php');
require_once($hsas_current_folder.DIRECTORY_SEPARATOR.'query'.DIRECTORY_SEPARATOR.'db_content.php');

add_action( 'widgets_init', array( 'hsas_cls_registerhook', 'hsas_widget_loading' ));
add_action( 'admin_menu', array( 'hsas_cls_registerhook', 'hsas_adminmenu' ), 9);
add_action( 'admin_init', array( 'hsas_cls_registerhook', 'hsas_welcome' ) );
add_action( 'admin_enqueue_scripts', array( 'hsas_cls_registerhook', 'hsas_load_scripts' ) );

add_shortcode( 'hsas-shortcode', 'hsas_shortcode' );

add_action( 'plugins_loaded', 'hsas_textdomain' );
function hsas_textdomain() {
	load_plugin_textdomain( 'horizontal-scrolling-announcements' , false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

register_activation_hook( $hsas_current_folder.DIRECTORY_SEPARATOR.'horizontal-scrolling-announcements.php', array( 'hsas_cls_registerhook', 'hsas_activation' ) );
register_deactivation_hook( $hsas_current_folder.DIRECTORY_SEPARATOR.'horizontal-scrolling-announcements.php', array( 'hsas_cls_registerhook', 'hsas_deactivation' ) );