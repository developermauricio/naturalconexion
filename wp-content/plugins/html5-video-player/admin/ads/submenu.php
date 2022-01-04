<?php

/**
 * Register and enqueue a custom stylesheet in the WordPress admin.
 */
function h5vp_enqueue_custom_admin_style() {
    wp_register_style( 'h5vp_admin_custom_css', plugin_dir_url(__FILE__) . 'style.css', false, H5VP_VER );
    wp_enqueue_style( 'h5vp_admin_custom_css' );
}
add_action( 'admin_enqueue_scripts', 'h5vp_enqueue_custom_admin_style' );


//wp_enqueue_style('h5vp_admin-style', plugin_dir_url(__FILE__) . 'admin/css/style.css');

//-----------------------------------------------
// Helps 
//-----------------------------------------------


add_action('admin_menu', 'h5vp_support_page');

function h5vp_support_page()
{
    add_submenu_page('edit.php?post_type=videoplayer', 'Help ', 'Help', 'manage_options', 'h5vp-support', 'h5vp_support_page_callback');
}

function h5vp_support_page_callback()
{
    ?>
    <div class="bplugins-container">
        <div class="row">
            <div class="bplugins-features">
                <div class="col col-12">
                    <div class="bplugins-feature center">
                        <h1>Helpful Links</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="bplugins-container">
    <div class="row">
        <div class="bplugins-features">
            <div class="col col-4">
                <div class="bplugins-feature center">
                    <i class="fa fa-life-ring"></i>
                    <h3>Need any Assistance?</h3>
                    <p>Our Expert Support Team is always ready to help you out promptly.</p>
                    <a href="https://bplugins.com/support/" target="_blank" class="button
                    button-primary">Contact Support</a>
                </div>
            </div>
            <div class="col col-4">
                <div class="bplugins-feature center">
                    <i class="fa fa-file-text"></i>
                    <h3>Looking for Documentation?</h3>
                    <p>We have detailed documentation on every aspects of the plugin.</p>
                    <a href="https://links.bplugins.com/h5vp-help-doc" target="_blank" class="button button-primary">Documentation</a>
                </div>
            </div>

            <div class="col col-4">
                <div class="bplugins-feature center">
                    <i class="fa fa-thumbs-up"></i>
                    <h3>Liked This Plugin?</h3>
                    <p>Glad to know that, you can support us by leaving a 5 &#11088; rating.</p>
                    <a href="https://wordpress.org/support/plugin/html5-video-player/reviews/#new-post" target="_blank" class="button
                    button-primary">Rate the Plugin</a>
                </div>
            </div>            
        </div>
    </div>
</div>

<div class="bplugins-container">
    <div class="row">
        <div class="bplugins-features">
            <div class="col col-12">
                <div class="bplugins-feature center">
                    <h1>Video Tutorials</h1><br/>
                    <div class="embed-container"><iframe width="100%" height="700px" src="https://www.youtube.com/embed/dLU67e708fg" frameborder="0"
                    allowfullscreen></iframe></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
}








//-------------------------------------------------------------------
// Pro Plugin List
//------------------------------------------------------------------
// add_action('admin_menu', 'h5vp_pro_plugin_page');

// function h5vp_pro_plugin_page() {
// 	add_submenu_page( 'edit.php?post_type=videoplayer', 'Our PRO Plugins', 'Our PRO Plugins', 'manage_options', 'h5vp-pro-plugins', 'h5vp_proplugin_page_cb' );
// }

// function h5vp_proplugin_page_cb() {
// 	 $plugins = wp_remote_get('https://office-viewer.bplugins.com/premium-plugins-of-bplugins-llc/');

//  echo $plugins['body']; 
	
// }


//--------------------------------------------------//
//  Free Plugin List
//-------------------------------------------------//

require_once ABSPATH . "wp-admin/includes/plugin-install.php";

function h5vp_free_plugin_loaded(){
    wp_enqueue_script('plugin-install');
    wp_enqueue_script('updates');
}
add_action('init', 'h5vp_free_plugin_loaded');
//$table->display();
if (!class_exists('BPlugins_Free_plugins')) {
    class BPlugins_Free_plugins
    {

        public function __construct()
        {
            add_action('admin_menu', array($this, 'bplugins_free_plugins_menu'));
        }
        
        public function bplugins_free_plugins_menu()
        {
            add_submenu_page(
                'edit.php?post_type=videoplayer',
                'bPlugins',
                'Our Free Plugins',
                'manage_options',
                'plugin-install.php?s=abuhayat&tab=search&type=author'
            );
        }
            
    }
}
new BPlugins_Free_plugins();




