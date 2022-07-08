<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * This class handles all functionality related
 * to the tracking of product views.
 * 
 * @since      1.0.2
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 * @author     Doppler LLC <info@fromdoppler.com>
 */

class Doppler_For_WooCommerce_Visited_Products {

    protected $visited_products_table;

    public function __construct( $table ) {
        $this->visited_products_table = $table; 
    }

    private function get_visited_products_table() {
        return $this->visited_products_table;
    }

    /**
     * Save product view
     */
    public function save_visited_product() {
        global $wpdb;
        $table_name = $this->get_visited_products_table();
        
        if( is_product() && is_user_logged_in() ){
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $user_name = $user->first_name;
            $user_lastname = $user->last_name;
            $user_email = $user->user_email;
            $product_id = get_the_ID();
            $product = wc_get_product( $product_id );
            $product_name = $product->get_name();
            $product_slug = $product->get_slug();
            $product_description = $product->get_description();
            $product_image = wp_get_attachment_url($product->get_image_id());
            $product_link = $product->get_permalink();
            $regular_price = $product->get_regular_price();
            $product_price = $product->get_price();
            $currency = get_woocommerce_currency();
            $current_time = gmdate('Y-m-d H:i:s');
    
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO ". $table_name ."
                    ( user_id, user_email, user_name, user_lastname, product_id,
                     product_name, product_slug, product_description, product_image, product_link, product_price, product_regular_price,
                     currency, visited_time )
                    VALUES ( %d, %s, %s, %s, %d,
                     %s, %s, %s, %s, %s, %0.2f, %0.2f, 
                     %s, %s)",
                    array(
                        filter_var($user_id, FILTER_SANITIZE_NUMBER_INT),
                        sanitize_email( $user_email ),
                        sanitize_text_field( $user_name),
                        sanitize_text_field( $user_lastname ),
                        filter_var($product_id, FILTER_SANITIZE_NUMBER_INT),
                        sanitize_text_field( $product_name ),
                        sanitize_text_field( $product_slug ),
                        sanitize_text_field( $product_description ),
                        sanitize_text_field( $product_image ),
                        sanitize_text_field( $product_link ),
                        sanitize_text_field( $product_price ),
                        sanitize_text_field( $regular_price ),
                        sanitize_text_field( $currency ),
                        sanitize_text_field( $current_time )                    
                    ) 
                )
            );

            //print($wpdb->last_error); die();

        }

    }

}