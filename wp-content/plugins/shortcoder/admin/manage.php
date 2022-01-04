<?php
if( ! defined( 'ABSPATH' ) ) exit;

class SC_Admin_Manage{

    public static function init(){

        add_filter( 'manage_' . SC_POST_TYPE . '_posts_columns', array( __CLASS__, 'column_head' ) );

        add_action( 'manage_' . SC_POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );

        add_filter( 'edit_posts_per_page', array( __CLASS__, 'per_page_count' ), 10, 2 );

    }

    public static function column_head( $columns ){

        unset( $columns[ 'views' ] );

        $columns[ 'shortcode' ] = __( 'Shortcode', 'sc');

        return $columns;

    }

    public static function column_content( $column, $post_id ){

        if( $column == 'shortcode' ){
            $sc_tag = Shortcoder::get_sc_tag( $post_id );
            echo '<code>' . $sc_tag . '</code>';
        }

    }

    public static function per_page_count( $count, $post_type ){
        if( $post_type == SC_POST_TYPE && $count == 20 ){
            return 500;
        }
        return $count;
    }

}

SC_Admin_Manage::init();

?>