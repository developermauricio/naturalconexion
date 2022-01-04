<?php // Silence is golden.
// Control core classes for avoid errors
if (class_exists('CSF')) {

    $prefix = '_h5vp_';


    // Create a settings page.
    CSF::createMetabox($prefix, array(
        'title' => 'Configure Your Video Player',
        'post_type' => 'videoplayer',
        'data_type' => 'unserialize',
    ));

    //
    // Create a section
    CSF::createSection($prefix, array(
        'title' => '',
        'fields' => array(
            array(
                'id' => 'h5vp_video_link',
                'type' => 'upload',
                'title' => 'Source URL',
                'placeholder' => 'https://',
                'library' => 'video',
                'button_title' => 'Add Video',
                'attributes' => array('class' => 'h5vp_video_link'),
                'desc' => 'select an mp4 or ogg video file. or paste a external video file link',
            ),
            array(
                'id' => 'h5vp_video_thumbnails',
                'type' => 'upload',
                'title' => 'Video Thumbnail',
                'library' => 'image',
                'button_title' => 'Add Image',
                'placeholder' => 'https://',
                'attributes' => array('class' => 'h5vp_video_thumbnails'),
                'desc' => 'specifies an image to be shown while the video is downloading or until the user hits the play button',
            ),
            array(
                'id' => 'h5vp_repeat_playerio',
                'type' => 'button_set',
                'title' => 'Repeat',
                'options' => array(
                    'once' => 'Once',
                    'loop' => 'Loop',
                ),
                'default' => 'once',
            ),
            array(
                'id' => 'h5vp_muted_playerio',
                'type' => 'switcher',
                'title' => 'Muted',
                'desc' => 'On if you want the video output should be muted',
                'default' => '0',
            ),
            array(
                'id' => 'h5vp_auto_play_playerio',
                'type' => 'switcher',
                'title' => 'Auto Play',
                'desc' => 'Turn On if you  want video will start playing as soon as it is ready. <a href="https://developers.google.com/web/updates/2017/09/autoplay-policy-changes">autoplay policy</a>',
                'default' => '',
            ),
            array(
                'id' => 'h5vp_player_width_playerio',
                'type' => 'spinner',
                'title' => 'Player Width',
                'unit' => 'px',
                'max' => '5000',
                'min' => '200',
                'step' => '50',
                'desc' => 'set the player width. Height will be calculate base on the value. Leave blank for Responsive player',
                'default' => '',
            ),
            array(
                'id' => 'h5vp_auto_hide_control_playerio',
                'type' => 'switcher',
                'title' => 'Auto Hide Control',
                'desc' => 'On if you want the controls (such as a play/pause button etc) hide automaticaly.',
                'default' => '1',
            ),
            
            array(
                'id' => 'h5vp_controls',
                'type' => 'button_set',
                'title' => 'Control buttons and Components',
                'multiple' => true,
                'options' => array(
                  'play-large' => 'Play Large',
                  'restart' => 'Restart',
                  'rewind' => 'Rewind',
                  'play' => 'Play',
                  'fast-forward' => 'Fast Forwards',
                  'progress' => 'Progressbar',
                  'duration' => 'Duration',
                  'current-time' => 'Current Time',
                  'mute' => 'Mute Button',
                  'volume' => 'Volume Control',
                  'settings' => 'Setting Button',
                  'pip' => 'PIP',
                  'airplay' => 'Airplay',
                  'download' => 'Download Button',
                  'fullscreen' => 'Full Screen'
                ),
                'default' => array( 'play-large', 'play','progress','current-time','mute','volume','settings', 'pip', 'download', 'fullscreen' ),
            )

        ),
    ));



    // $prefix = '_h5vp_side_';

    // //
    //     // Create a settings page.
    //     CSF::createMetabox($prefix, array(
    //         'title' => 'Controls',
    //         'post_type' => 'videoplayer',
    //         'data_type' => 'unserialize',
    //         'context' => 'advanced',
    //         'class' => 'h5vp_video_player'
    //     ));
    
    //     CSF::createSection($prefix, array(
    //         'title' => '',
    //         'fields' => array(

    //             array(
    //                 'id' => 'h5vp_hide_restart_btn',
    //                 'title' => 'Restart Button',
    //                 'type' => 'button_set',
    //                 'options' => array(
    //                     'show' => 'Show',
    //                     'hide' => 'Hide',
    //                   //  'mobile' => 'Hide On Mobile'
    //                 ),
    //                 'default' => 'hide',
    //             ),
    //             array(
    //                 'id' => 'h5vp_hide_rewind_btn',
    //                 'title' => 'Rewind Button',
    //                 'type' => 'button_set',
    //                 'options' => array(
    //                     'show' => 'Show',
    //                     'hide' => 'Hide',
    //                    // 'mobile' => 'Hide On Mobile'
    //                 ),
    //                 'default' => 'hide',
    //             ),
    //             array(
    //                 'id' => 'h5vp_hide_fast_forward_btn',
    //                 'title' => 'Fast Forward Button',
    //                 'type' => 'button_set',
    //                 'options' => array(
    //                     'show' => 'Show',
    //                     'hide' => 'Hide',
    //                    // 'mobile' => 'Hide On Mobile'
    //                 ),
    //                 'default' => 'hide',
    //             ),

    //         ),
    //     ));
    
    }    