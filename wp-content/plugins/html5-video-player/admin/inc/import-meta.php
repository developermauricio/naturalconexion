<?php

function h5vp_import_meta(){
    $meta_keys = [
        '_ahp_video-file' => 'h5vp_video_link',
        '_ahp_video-poster' => 'h5vp_video_thumbnails',
        '_ahp_video-repeat' => 'h5vp_repeat_playerio',
        '_ahp_video-muted' => 'h5vp_muted_playerio',
        '_ahp_video-autoplay' => 'h5vp_auto_play_playerio',
        '_ahp_video-control' => 'h5vp_auto_hide_control_playerio',
        '_ahp_video-size' => 'h5vp_player_width_playerio',
    ];
    $videos = new WP_Query(array(
        'post_type' => 'videoplayer',
        'post_status' => 'any',
        'posts_per_page' => -1
    ));
    while ($videos->have_posts()): $videos->the_post();
        $id = get_the_ID();
        foreach ($meta_keys as $old_meta => $new_meta) {
            if (metadata_exists('post', $id, $old_meta) && metadata_exists('post', $id, $new_meta) == false) {
                if (get_post_meta($id, $old_meta, true) == 'on') {
                    update_post_meta($id, $new_meta, '1');
                } else {
                    update_post_meta($id, $new_meta, get_post_meta($id, $old_meta, true));
                }
            }
        }
    endwhile;

}