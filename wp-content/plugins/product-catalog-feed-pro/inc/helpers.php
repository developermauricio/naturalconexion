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
    /*
    foreach ($fields as $fieldkey => $field) {
        $all_fields[$fieldkey] = $field;   
        if( $fieldkey == 'description' ) {
            $all_fields['description_short'] = array(
                'label'         => __('Short Description', 'woocommerce_wpwoof'),
                'desc'          => __( 'A short paragraph describing the product.', 'woocommerce_wpwoof' ),
                'value'         => false,
                'required'      => true,
                'feed_type'     => array('facebook','all','adsensecustom'),
                'facebook_len'  => 5000,
                'text'          => true,
                'woocommerce_default' =>array('label' => 'description_short', 'value' => 'description_short'),
            );
            $all_fields['variation_description'] = array(
                'label'         => __('Variation Description', 'woocommerce_wpwoof'),
                'desc'          => __( 'Descrioption for variation inside woocommerce.', 'woocommerce_wpwoof' ),
                'value'         => false,
                'required'      => true,
                'feed_type'     => array('facebook','all','adsensecustom'),
                'facebook_len'  => 5000,
                'text'          => true,
                'woocommerce_default' =>array('label' => 'variation_description', 'value' => 'variation_description'),
            );
        }
    }
    $all_fields['site_name'] = array(
        'label'         => __('Site Name', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['mpn']['label'] = 'SKU';
    $post_type = "product";
    $taxonomy_names = get_object_taxonomies( $post_type );
    $value_brand = "";
    foreach( $taxonomy_names as $taxonomy_name ) {
        if( ($taxonomy_name != 'product_cat') && ($taxonomy_name != 'product_tag') && ($taxonomy_name != 'product_type') 
        && ($taxonomy_name != 'product_shipping_class') && ($taxonomy_name != 'pa_color') ) {
            if( strpos($taxonomy_name, "brand") !== false ) {
                $value_brand = $taxonomy_name;
                break;
            }
        }
    }
    $all_fields['brand']['label'] = $all_fields['brand']['label'].' '.$value_brand;
    $all_fields['product_type']['label'] = 'Woo Prod Categories';
    $all_fields['use_custom_attribute'] = array(
        'label'         => __('Custom Attribute', 'woocommerce_wpwoof'),
        'desc'          => __( 'Use custom product attribute value.', 'woocommerce_wpwoof' ),
        'value'         => false,
        'required'      => true,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => 5000,
        'text'          => true,
    );
    $all_fields['stock_quantity'] = array(
        'label'         => __('Stock Quantity', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['average_rating'] = array(
        'label'         => __('Average Rating', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['total_rating'] = array(
        'label'         => __('Total Rating', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['sale_start_date'] = array(
        'label'         => __('Sale Start Date', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['sale_end_date'] = array(
        'label'         => __('Sale End Date', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['length'] = array(
        'label'         => __('Length', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['width'] = array(
        'label'         => __('Width', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['height'] = array(
        'label'         => __('Height', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['tags'] = array(
        'label'         => __('Tags', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['product_type_normal'] = array(
        'label'         => __('Product Type', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );

    $all_fields['yoast_seo_product_image'] = array(
        'label'         => __('Yoast SEO Product Image', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['mashshare_product_image'] = array(
        'label'         => __('MashShare Product Image', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    

    $all_fields['wpfoof-carusel-box-media-name'] = array(
        'label'         => __('Carousel ad image', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
    $all_fields['wpfoof-box-media-name'] = array(
        'label'         => __('Single product ad image', 'woocommerce_wpwoof'),
        'desc'          => '',
        'value'         => false,
        'required'      => false,
        'feed_type'     => array('facebook','all','google','adsensecustom'),
        'facebook_len'  => false,
        'text'          => true,
    );
*/
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

