<?php
namespace HTML5Player\Model;
use HTML5Player\Model\Block;
use HTML5Player\Helper\DefaultArgs;
use HTML5Player\Services\VideoTemplate;

class AdvanceSystem{

    public static function html($id){
        $blocks =  Block::getBlock($id);
        $output = '';
        if(is_array($blocks)){
            foreach($blocks as $block){
                if(isset($block['attrs'])){
                    $output .= render_block($block);
                }else {
                    $data = DefaultArgs::parseArgs(self::getData($block));
                    $output .= VideoTemplate::html($data);
                }
            }
        }
        return $output;
    }

    public static function getData($block){
        
        $options = [
            'controls' => self::parseControls(self::i($block, 'controls')),
            'tooltips' => [
                'controls' => true,
                'seek' => true,
            ],
            'loop' => [
                'active' => (boolean) self::i($block, 'repeat') 
            ],
            'autoplay' => (boolean) self::i($block, 'autoplay'),
            'muted' => (boolean) self::i($block, 'muted'),
            'hideControls' => self::i($block, 'autoHideControl', '', true),
            'resetOnEnd' => (boolean) self::i($block, 'resetOnEnd', '', true),
            'captions' => [
                'active' => true,
                'update' => true,
            ]
        ];

        $infos = [
            'id' => 0,
            'source' => self::i($block, 'source'),
            'poster' => self::i($block, 'poster'),
        ];

        $template = array(
            'class' => 'h5vp_player_initializer',
            'poster' => self::i($block, 'poster'),
            'width' => self::i($block, 'width', 'number', '', 100).self::i($block, 'width', 'unit', '', '%'),
            'round' => self::i($block, 'radius', 'number', '', 100).self::i($block, 'radius', 'unit', '', '%'),
            'controlsShadow' => false,
        );

        $result = [
            'options' => $options,
            'infos' => $infos,
            'template' => $template
        ];

        return $result;
    }

    public static function i($array, $key1, $key2 = '', $default = false){
        if(isset($array[$key1][$key2])){
            return $array[$key1][$key2];
        }else if (isset($array[$key1])){
            return $array[$key1];
        }
        return $default;
    }



    public static function parseControls($controls){
        $newControls = [];
        if(!is_array($controls)){
            return ['play-large','rewind', 'play', 'fast-forward', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'];
        }
        foreach($controls as $key => $value){
            if($value == 1){
                array_push($newControls, $key);
            }
        }
        return $newControls;
    }
}