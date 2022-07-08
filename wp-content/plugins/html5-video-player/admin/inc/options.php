<?php
  /** Option page */
  $prefix = 'h5vp_option';
  
  CSF::createOptions( $prefix, array(
    'framework_title' => esc_html__('Video Player Settings', 'h5vp'),
    'menu_title'  => esc_html__('Settings', 'h5vp'),
    'menu_slug'   => 'settings',
    'menu_type'   => 'submenu',
    'menu_parent' => 'edit.php?post_type=videoplayer',
    'theme' => 'light',
    'show_bar_menu' => false,
  ) );

    CSF::createSection($prefix, array(
        'title' => 'Shortcode',
        'fields' => array(
            array(
                'id' => 'h5vp_gutenberg_enable',
                'type' => 'switcher',
                'title' => esc_html__('Enable Gutenberg shortcode generator', 'h5vp'),
                'default' => true
            ),
            array(
                'id' => 'h5vp_disable_video_shortcode',
                'type' => 'switcher',
                'title' => "Disable `[video id='id']` shortcode for this plugin",
                'default' => false
            ),
            array(
              'id' => 'h5vp_pause_other_player',
              'type' => 'switcher',
              'title' => esc_html__('Play one player at a time', 'h5vp'),
              'default' => false,
            ),
        )
    ));
