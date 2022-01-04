<?php
namespace HTML5Player\Model;
class ImportData{
    
    public static function importMeta(){
        $meta_keys = [
            '_ahp_video-file' => 'h5vp_video_link',
            '_ahp_video-poster' => 'h5vp_video_thumbnails',
            '_ahp_video-repeat' => 'h5vp_repeat_playerio',
            '_ahp_video-muted' => 'h5vp_muted_playerio',
            '_ahp_video-autoplay' => 'h5vp_auto_play_playerio',
            '_ahp_video-control' => 'h5vp_auto_hide_control_playerio',
            '_ahp_video-size' => 'h5vp_player_width_playerio',
        ];
        $videos = new \WP_Query(array(
            'post_type' => 'videoplayer',
            'post_status' => 'any',
            'posts_per_page' => -1
        ));

        while ($videos->have_posts()): $videos->the_post();
            $id = \get_the_ID();
            foreach ($meta_keys as $old_meta => $new_meta) {
                if (\metadata_exists('post', $id, $old_meta) && \metadata_exists('post', $id, $new_meta) == false) {
                    if (\get_post_meta($id, $old_meta, true) == 'on') {
                        \update_post_meta($id, $new_meta, '1');
                    } else {
                        \update_post_meta($id, $new_meta, get_post_meta($id, $old_meta, true));
                    }
                }
            }
        endwhile;
    }

    public static function importControls(){
        $videos = new \WP_Query(array(
            'post_type' => 'videoplayer',
            'post_status' => 'any',
            'posts_per_page' => -1
        ));

        while ($videos->have_posts()): $videos->the_post();
            $id = \get_the_ID();
            $controls = ['play-large', 'play','progress','current-time','mute','volume','settings','pip', 'download', 'fullscreen'];
            $restart = get_post_meta($id, 'h5vp_hide_restart_btn', true);
            $rewind = get_post_meta($id, 'h5vp_hide_rewind_btn', true);
            $fast_forward = get_post_meta($id, 'h5vp_hide_fast_forward_btn', true);
    
            if($fast_forward == 'show'){
                array_splice($controls, 2, 0, 'fast-forward');
            }
            if($rewind == 'show'){
                array_splice($controls, 1, 0, 'rewind');
            }
            if($restart == 'show'){
                array_splice($controls, 1, 0, 'restart');
            }

            if (\metadata_exists('post', $id, 'h5vp_controls') == false){
                \update_post_meta($id, 'h5vp_controls', $controls);
            }

        endwhile;

    }
}