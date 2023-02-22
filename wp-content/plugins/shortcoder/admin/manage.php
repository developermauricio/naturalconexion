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

        $new_columns = array();

        foreach( $columns as $id => $val ){
            if( $id == 'taxonomy-sc_tag' ){
                $new_columns[ 'shortcode' ] = __( 'Shortcode', 'sc');
                $new_columns[ 'desc' ] = __( 'Description', 'sc');
            }
            $new_columns[$id] = $val;
        }

        return $new_columns;

    }

    public static function column_content( $column, $post_id ){

        if( $column == 'shortcode' ){
            $sc_tag = Shortcoder::get_sc_tag( $post_id );
            echo '<span class="sc_copy_list_wrap"><input type="text" class="widefat sc_copy_text" readonly value="' . esc_attr( $sc_tag ) . '" /><a href="#" class="sc_copy_list" title="' . esc_attr__( 'Copy', 'shortcoder' ) . '"><span class="dashicons dashicons-clipboard"></span></a></span>';
        }

        if( $column == 'desc' ){
            $sc_settings = Shortcoder::get_sc_settings( $post_id );
            echo esc_html( $sc_settings[ '_sc_description' ] );
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