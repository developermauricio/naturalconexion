<?php
  /** Option page */
  $prefix = 'h5vp_option';
  
  CSF::createOptions( $prefix, array(
    'framework_title' => 'Video Player Settings',
    'menu_title'  => 'Settings',
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
                'title' => 'Enable Gutenberg shortcode generator',
                'default' => true
            )
        )
    ));
