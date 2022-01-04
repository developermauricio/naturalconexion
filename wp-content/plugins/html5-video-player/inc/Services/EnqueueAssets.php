<?php
namespace HTML5Player\Services;
class EnqueueAssets{
    protected static $_instance = null;

    public function __construct(){
        add_action("wp_enqueue_scripts", [$this, 'publicAssets']);
        add_action( 'admin_enqueue_scripts', [$this, 'html5_enqueue_custom_admin_style'] );
    }

    /**
     * Create Instance
     */
    public static function instance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Enqueue public assets
     */
    public function publicAssets(){
        wp_register_script( 'bplugins-plyrio', H5VP_PLUGIN_DIR . 'js/plyr.js', array(), H5VP_VER , false );
        wp_register_script( 'h5vp-public', H5VP_PLUGIN_DIR . 'dist/public.js', array('bplugins-plyrio'), H5VP_VER , false );

        wp_register_style( 'bplugins-plyrio', H5VP_PLUGIN_DIR . 'css/player-style.css', array(), H5VP_VER , 'all' );
        wp_register_style( 'h5vp-public', H5VP_PLUGIN_DIR . 'dist/public.css', array('bplugins-plyrio'), H5VP_VER , 'all' );

    }

    /**
     * enqueue admin assets
     **/    
    function html5_enqueue_custom_admin_style($hook_suffix) {
        wp_enqueue_style( 'h5vp-admin', H5VP_PLUGIN_DIR . 'admin/css/admin.css', false, H5VP_VER );
        wp_enqueue_script( 'h5vp-admin', H5VP_PLUGIN_DIR . 'dist/admin.js', false, H5VP_VER );
    }
}

EnqueueAssets::instance();