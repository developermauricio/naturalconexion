<?php

namespace PixelYourSite;


class EnrichUser {

    private static $_instance;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'show_user_profile', array($this,'render_user_info'), 10 );
        add_action( 'edit_user_profile', array($this,'render_user_info'), 10 );
    }

    function render_user_info( $user ) {
        include 'views/html-user-info.php';
    }
}

/**
 * @return EnrichUser
 */
function EnrichUser() {
    return EnrichUser::instance();
}

EnrichUser();