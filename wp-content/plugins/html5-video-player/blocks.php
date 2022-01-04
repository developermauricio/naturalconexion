<?php
namespace HTML5Player\Block;
if(!defined('ABSPATH')) {
    return;
}
use HTML5Player\Helper\DefaultArgs;
use HTML5Player\Model\AdvanceSystem;
use HTML5Player\Services\VideoTemplate;


if(!class_exists('H5VP_Block')){
    class H5VP_Block{
        function __construct(){
            // add_action('init', [$this, 'enqueue_block_css_js']);
            add_action('init', [$this, 'enqueue_script']);
        }

        function enqueue_script(){
            wp_register_script(	'html5-player-blocks', plugin_dir_url( __FILE__ ).'dist/editor.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'h5vp-public'),H5VP_VER, true );
            wp_register_script( 'bplugins-plyrio', plugin_dir_url( __FILE__ ). 'js/plyr.js' , array(), H5VP_VER, false );
            wp_register_script( 'h5vp-public', plugin_dir_url( __FILE__ ). 'dist/public.js' , array('jquery', 'bplugins-plyrio'), H5VP_VER, true );
            
            wp_register_style( 'bplugins-plyrio', plugin_dir_url( __FILE__ ) . 'css/player-style.css', array(), H5VP_VER, 'all' );
            wp_register_style( 'h5vp-editor', plugin_dir_url( __FILE__ ) . 'dist/editor.css', array(), H5VP_VER, 'all' );
            wp_register_style( 'h5vp-public', plugin_dir_url( __FILE__ ). 'dist/public.css' , array('bplugins-plyrio', 'h5vp-editor'), H5VP_VER );

            register_block_type('html5-player/parent', array(
                'editor_script' => 'html5-player-blocks',
                'editor_style' => 'h5vp-public',
                // 'script' => 'h5vp-public',
                // 'style' => 'h5vp-public',
                // 'render_callback' => [$this, 'render_callback_parent']
            ));

            register_block_type('html5-player/video', array(
                'editor_script' => 'h5vp-public',
                'editor_style' => 'h5vp-public',
                // 'script' => 'h5vp-public',
                // 'style' => 'h5vp-public',
                'render_callback' => [$this, 'render_callback_video']
            ));
        }

        public function render_callback_video($atts, $content){
            $atts['provider'] = 'library';
            $data = DefaultArgs::parseArgs(AdvanceSystem::getData($atts));
            return VideoTemplate::html($data);
        }

    }

    new H5VP_Block();
}

