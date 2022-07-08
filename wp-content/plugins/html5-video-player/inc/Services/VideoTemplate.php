<?php
namespace HTML5Player\Services;
use HTML5Player\Helper\Functions;
class VideoTemplate{
    protected static $uniqid = null;
    protected static $styles = [];

    public static function html($data){
        self::createId();
        self::style($data['template']);
        self::enqueueAssets();

        $settings = $data;
        unset($settings['template']);
         ob_start(); 
         ?>
         <style>
             <?php echo esc_html(Functions::trim(self::renderStyle())); ?>
        </style>
        <div id="h5vp_player">
            <div data-unique-id="<?php echo esc_attr(uniqid()) ?>" id="<?php echo esc_attr(self::$uniqid); ?>" class="h5vp_player <?php echo esc_html($data['template']['class']) ?>" data-settings="<?php echo esc_attr(wp_json_encode($settings)); ?>">
                <video playsinline poster="<?php echo esc_url($data['infos']['poster']);?>" <?php echo esc_html(self::getAttrs($data['options'])); ?> >
                    <source src="<?php echo esc_html($data['infos']['source']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
        <?php $output = ob_get_contents(); ob_get_clean();
        return $output; //print $output; // debug 
    }

    public static function style($template){
        $id = "#".self::$uniqid;
        self::addStyle("$id.h5vp_player", [
            'width' => $template['width'],
            'border-radius' => $template['round'],
            'overflow' => 'hidden'
        ]);
    }

    public static function addStyle($selector, $styles){
        if(array_key_exists($selector, self::$styles)){
           self::$styles[$selector] = wp_parse_args(self::$styles[$selector], $styles);
        }else {
            self::$styles[$selector] = $styles;
        }
    }

    public static function renderStyle(){
        $output = '';
        foreach(self::$styles as $selector => $style){
            $new = '';
            foreach($style as $property => $value){
                // if($value == ''){
                //     $new .= $property.";";
                // }else {
                    $new .= " $property: $value;";
                // }
            }
            $output .= "$selector { $new }";
        }

        return $output;
    }

    public static function createId(){
        self::$uniqid = esc_html('player'.uniqid());
    }

    /**
     * enqueue essential assets
     */
    public static function enqueueAssets(){
        wp_enqueue_script('h5vp-public');
        wp_enqueue_style('h5vp-public');
    }

    /**
     * get attr ( autoplay, loop, muted)
     */
    public static function getAttrs($options){
        $attr = $options['muted'] == true ? ' muted' : '';
        $attr .= $options['autoplay'] == true ? ' autoplay' : '';
        return $attr;
    }
}