<?php 
namespace HTML5Player\Helper;
class DefaultArgs{

    public static function parseArgs($data){
        $default = self::getDefaultData();
        $data = wp_parse_args( $data, $default );
        $data['options'] = wp_parse_args( $data['options'], $default['options'] );
        $data['infos'] = wp_parse_args( $data['infos'], $default['infos'] );
        $data['template'] = wp_parse_args( $data['template'], $default['template'] );

        return $data;
    }

    private static function getDefaultData(){
        $options = [
            'controls' => [''],
            'loop' => [
                'active' => false
            ],
            'muted' => false,
            'hideControls' => true,
            'autoplay' => false
        ];

        $infos  = [
            'source' => '',
            'poster' => '',
        ];

        $template = [
            'source' => '',
            'poster' => '',
            'width' => '100%',
            'round' => '0px',
        ];
         return [
             'options' => $options,
             'infos' => $infos,
             'template' => $template,
         ];
    }   
}
