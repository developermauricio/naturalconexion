<?php
namespace HTML5Player\Model;
use HTML5Player\Helper\DefaultArgs;
use HTML5Player\Services\VideoTemplate;
class AnalogSystem{

    public static function html($id){
        $data = DefaultArgs::parseArgs(self::getData($id));
        return VideoTemplate::html($data);
    }

    public static function getData($id){
        $controls = get_post_meta($id, 'h5vp_controls', true);
       

        $options = [
            'controls' => $controls,
            'loop' => [
                'active' => get_post_meta($id, 'h5vp_repeat_playerio', true) == 'once' ? false : true
            ],
            'muted' => (boolean) get_post_meta($id, 'h5vp_muted_playerio', true),
            'hideControls' => (boolean) get_post_meta($id, 'h5vp_auto_hide_control_playerio', true),
            'autoplay' => (boolean) get_post_meta($id, 'h5vp_auto_play_playerio', true)
        ];

        $infos  = [
            'source' => get_post_meta($id,'h5vp_video_link', true),
            'poster' => get_post_meta($id,'h5vp_video_thumbnails', true),
        ];

        $template = [
            'class' => 'h5vp_player_initializer',
            'source' => '',
            'poster' => '',
            'width' => get_post_meta($id,'h5vp_player_width_playerio', true) == 0 ? '100%' : get_post_meta($id,'h5vp_player_width_playerio', true).'px',

        ];
        
         return [
             'options' => $options,
             'infos' => $infos,
             'template' => $template,
         ];
    }
}