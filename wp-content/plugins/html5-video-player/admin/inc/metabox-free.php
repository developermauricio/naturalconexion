<?php // Silence is golden.
// Control core classes for avoid errors
if (class_exists('CSF')) {

    $prefix = '_h5vp_';


    // Create a settings page.
    CSF::createMetabox($prefix, array(
        'title' => esc_html__('Configure Your Video Player', 'h5vp'),
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
                'title' => esc_html__('Source URL', 'h5vp'),
                'placeholder' => 'https://',
                'library' => 'video',
                'button_title' => esc_html__('Add Video', 'h5vp'),
                'attributes' => array('class' => 'h5vp_video_link'),
                'desc' => esc_html__('select an mp4 or ogg video file. or paste a external video file link', 'h5vp'),
            ),
            array(
                'id' => 'h5vp_video_thumbnails',
                'type' => 'upload',
                'title' => esc_html__('Video Thumbnail', 'h5vp'),
                'library' => 'image',
                'button_title' => esc_html__('Add Image', 'h5vp'),
                'placeholder' => 'https://',
                'attributes' => array('class' => 'h5vp_video_thumbnails'),
                'desc' => esc_html__('specifies an image to be shown while the video is downloading or until the user hits the play button', 'h5vp'),
            ),
            array(
                'id' => 'h5vp_repeat_playerio',
                'type' => 'button_set',
                'title' => esc_html__('Repeat', 'h5vp'),
                'options' => array(
                    'once' => esc_html__('Once', 'h5vp'),
                    'loop' => esc_html__('Loop', 'h5vp'),
                ),
                'default' => 'once',
            ),
            array(
                'id' => 'h5vp_muted_playerio',
                'type' => 'switcher',
                'title' => esc_html__('Muted', 'h5vp'),
                'desc' => esc_html__('On if you want the video output should be muted', 'h5vp'),
                'default' => '0',
            ),
            array(
                'id' => 'h5vp_auto_play_playerio',
                'type' => 'switcher',
                'title' => esc_html__('Auto Play', 'h5vp'),
                'desc' => 'Turn On if you  want video will start playing as soon as it is ready. <a href="https://developers.google.com/web/updates/2017/09/autoplay-policy-changes">autoplay policy</a>',
                'default' => '',
            ),
            array(
                'id' => 'h5vp_player_width_playerio',
                'type' => 'spinner',
                'title' => esc_html__('Player Width', 'h5vp'),
                'unit' => 'px',
                'max' => '5000',
                'min' => '200',
                'step' => '50',
                'desc' => esc_html__('set the player width. Height will be calculate base on the value. Leave blank for Responsive player', 'h5vp'),
                'default' => '',
            ),
            array(
                'id' => 'h5vp_auto_hide_control_playerio',
                'type' => 'switcher',
                'title' => esc_html__('Auto Hide Control', 'h5vp'),
                'desc' => esc_html__('On if you want the controls (such as a play/pause button etc) hide automaticaly.', 'h5vp'),
                'default' => '1',
            ),
            
            
            array(
                'id' => 'h5vp_controls',
                'type' => 'button_set',
                'title' => esc_html__('Control buttons and Components', 'h5vp'),
                'multiple' => true,
                'options' => array(
                  'play-large' => esc_html__('Play Large', 'h5vp'),
                  'restart' => esc_html__('Restart', 'h5vp'),
                  'rewind' => esc_html__('Rewind', 'h5vp'),
                  'play' => esc_html__('Play', 'h5vp'),
                  'fast-forward' => esc_html__('Fast Forwards', 'h5vp'),
                  'progress' => esc_html__('Progressbar', 'h5vp'),
                  'duration' => esc_html__('Duration', 'h5vp'),
                  'current-time' => esc_html__('Current Time', 'h5vp'),
                  'mute' => esc_html__('Mute Button', 'h5vp'),
                  'volume' => esc_html__('Volume Control', 'h5vp'),
                  'settings' => esc_html__('Setting Button', 'h5vp'),
                  'pip' => esc_html__('PIP', 'h5vp'),
                  'airplay' => esc_html__('Airplay', 'h5vp'),
                  'download' => esc_html__('Download Button', 'h5vp'),
                  'fullscreen' => esc_html__('Full Screen', 'h5vp')
                ),
                'default' => array( 'play-large', 'play','progress','current-time','mute','volume','settings', 'pip', 'download', 'fullscreen' ),
            )

        ),
    ));

    }    