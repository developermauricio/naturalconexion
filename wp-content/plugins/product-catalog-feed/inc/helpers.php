<?php
/* Helper functions */
function wpwoof_get_all_attributes(){
    $taxonomy_objects = get_object_taxonomies( 'product', 'objects');
    foreach ($taxonomy_objects as $taxonomy_key => $taxonomy_object) {
        $cat = substr( $taxonomy_key, 0, 3 ) === "pa_" ? 'pa' : 'global';
        if( $taxonomy_key == 'product_type' ) {
            $attributes[$cat][$taxonomy_key]= 'Product Type ('.$taxonomy_key.')';
        } else {
            $attributes[$cat][$taxonomy_key]= $taxonomy_object->label.' ('.$taxonomy_key.')';
        }
    }
    $attributes['meta'] = get_products_meta_keys();
    return $attributes;
}

function generate_products_meta_keys(){
    global $wpdb;
    $post_type = 'product';
    $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        WHERE $wpdb->posts.post_type = '%s' 
        AND $wpdb->postmeta.meta_key != '' 
        AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' 
        AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
    ";
    $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
    set_transient('products_meta_keys', $meta_keys, 60*60*24); # create 1 Day Expiration
    return $meta_keys;
}
function get_products_meta_keys(){
    $cache = get_transient('products_meta_keys');
    $meta_keys = $cache ? $cache : generate_products_meta_keys();
    return $meta_keys;
}
function wpwoof_get_product_fields_sort(){
    global $woocommerce_wpwoof_common;
    $sort = $woocommerce_wpwoof_common->fields_organize;
    $name = $woocommerce_wpwoof_common->fields_organize_name;
    /*
    $sort['general'][] = 'description_short';
    $sort['general'][] = 'variation_description';
    $sort['general'][] = 'stock_quantity';
    $sort['general'][] = 'product_type_normal';
    $sort['price'][] = 'sale_start_date';
    $sort['price'][] = 'sale_end_date';
    $sort['additional_data'][] = 'average_rating';
    $sort['additional_data'][] = 'total_rating';
    $sort['additional_data'][] = 'tags';

    $sort['shipping'][] = 'shipping';
    $sort['shipping'][] = 'shipping_weight';
    $sort['shipping'][] = 'length';
    $sort['shipping'][] = 'width';
    $sort['shipping'][] = 'height';
*/
    return array('sort' => $sort, 'name' => $name);
}
function wpwoof_get_product_fields(){
    global $woocommerce_wpwoof_common;

    $fields = $woocommerce_wpwoof_common->product_fields;
    $all_fields = array();
    $all_fields['id'] = array(
        'label'         => __('ID', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google', 'pinterest','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['_sku'] = array(
        'label'         => __('SKU', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google', 'pinterest','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );

    return $all_fields;
}
function wpwoof_get_all_fields(){
    global $woocommerce_wpwoof_common;

    $all_fields = $woocommerce_wpwoof_common->product_fields;
    $required_fields = array();
    $extra_fields    = array();
    $setting_fields = array();
    $field_group = array();

    foreach ($all_fields as $key => $value) {
        if (isset($value['type']) && is_array($value['type'])) {
            foreach ($value['type'] as $valueType) {
                $tpKey = !empty($valueType) ? $valueType : 'extra';
                if (!isset($field_group[$tpKey]))
                $field_group[$tpKey] = array();
                $field_group[$tpKey][$key] = $value;
            }
        } else {
            $tpKey = !empty($value['type']) ? $value['type'] : 'extra';
            if (!isset($field_group[$tpKey]))
                $field_group[$tpKey] = array();
            $field_group[$tpKey][$key] = $value;
        }
    }
   return $field_group;
}

