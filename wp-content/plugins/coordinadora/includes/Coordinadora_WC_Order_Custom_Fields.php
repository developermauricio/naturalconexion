<?php
class Coordinadora_WC_Order_Custom_Fields
{
    public static function init()
    {
        add_action('woocommerce_admin_order_data_after_order_details', __CLASS__ . '::add_tracking_number_field_in_order', 100, 1);
        add_action('woocommerce_process_shop_order_meta', __CLASS__ . '::save_tracking_number_field', 12, 2);
    }

    public static function add_tracking_number_field_in_order($order)
    {
        $cm_tracking_number = get_post_meta($order->get_id(), '_coordinadora_tracking_number', true);
        woocommerce_wp_text_input(array(
            'id' => '_coordinadora_tracking_number',
            'label' => __( 'Guía Coordinadora', 'coordinadora' ),
            'value' => $cm_tracking_number,
            'wrapper_class' => 'form-field-wide'
        ));

        $cm_tracking_url = get_post_meta($order->get_id(), '_coordinadora_tracking_url', true);
        woocommerce_wp_text_input(array(
            'id' => '_coordinadora_tracking_url',
            'label' => __( 'Url seguimiento', 'coordinadora' ),
            'value' => $cm_tracking_url,
            'wrapper_class' => 'form-field-wide'
        ));
    }

    public static function save_tracking_number_field($post_id, $post)
    {
        update_post_meta($post_id, '_coordinadora_tracking_number', sanitize_text_field( $_POST['_coordinadora_tracking_number'] ));
        update_post_meta($post_id, '_coordinadora_tracking_url', esc_url( $_POST['_coordinadora_tracking_url'] ));
    }
}
