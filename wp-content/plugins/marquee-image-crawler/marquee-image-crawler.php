<?php
/*
Plugin Name: Marquee image crawler
Plugin URI: http://www.gopiplus.com/work/2020/12/18/marquee-image-crawler-wordpress-plugin/
Description: Marquee image crawler is a continuous scrolling image plugin. This plugin crawls the images left or right.
Author: Gopi Ramasamy
Version: 1.3
Author URI: http://www.gopiplus.com/work/about/
Donate link: http://www.gopiplus.com/
Tags: plugin, widget, marquee, image, crawler
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: marquee-image-crawler
Domain Path: /languages
*/

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
	die('You are not allowed to call this page directly.');
}

if(!defined('MICR_DIR')) 
	define('MICR_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

if ( ! defined( 'MICR_ADMIN_URL' ) )
	define( 'MICR_ADMIN_URL', admin_url() . 'options-general.php?page=marquee-image-crawler' );

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'mic-register.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'mic-query.php');

function mic_textdomain() {
	  load_plugin_textdomain( 'marquee-image-crawler', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_shortcode( 'marquee-image-crawler', array( 'mic_cls_shortcode', 'mic_shortcode' ) );

add_action('wp_enqueue_scripts', array('mic_cls_registerhook', 'mic_frontscripts'));
add_action('plugins_loaded', 'mic_textdomain');
add_action('widgets_init', array('mic_cls_registerhook', 'mic_widgetloading'));
add_action('admin_enqueue_scripts', array('mic_cls_registerhook', 'mic_adminscripts'));
add_action('admin_menu', array('mic_cls_registerhook', 'mic_addtomenu'));

register_activation_hook(MICR_DIR . 'marquee-image-crawler.php', array('mic_cls_registerhook', 'mic_activation'));
register_deactivation_hook(MICR_DIR . 'marquee-image-crawler.php', array('mic_cls_registerhook', 'mic_deactivation'));
?>