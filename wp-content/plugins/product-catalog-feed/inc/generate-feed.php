<?php
$wpwoofeed_wpml_langs='';
$wpwoofeed_wpml_debug='';
$wpwoofeed_wpml_debug='';
require_once dirname(__FILE__).'/common.php';
require_once "Encode.php";

class WPwoofGenerator{
    private  $_woocommerce;                 /* woocommerce core */
    private  $_aTerms;                      /* array with products categories */
    private  $_aTermsT;                     /* array with products tags */
    private  $_aIncludedTags = array();     /* array with IDs of included categories */
    private  $_wpwoofeed_wpml_debug;        /* file descriptor for debug log */
    private  $_woocommerce_wpwoof_common;   /* class WoocommerceWpwoofCommon */
    private  $_store_info;                  /* global setting woocommerce */
    private  $_wpwoofeed_settings;          /* feed settings */
    private  $_wpwoofeed_type;              /* feed type */
    private  $_wp_query;                    /* wp query */
    private  $_wpdb;                        /* wp db connector */
    private  $_post;                        /* wp post */
    private  $_wp_using_ext_object_cache;   /* ??? */
    private  $_field_rules;                 /* wpwoof_get_product_fields() */
    private  $_mainMetaData;                /* parent post meta */
    private  $_metaData;                    /* current post meta */
    private  $_aTags;                       /* product tags */

    private  $top_30           = null;

    private  $_aVariationsType = array('variation','subscription_variation');

    private $_ticketDir   =  "";            /* path for items tickets */
    private $_aCSVcolumns = array();        /* array with csv columns name */
    private $_aCurrentlyFields = array();   /* it is fields for compile current feed */
    private $mainImage = ""; /* parent product image for childs */

    function __construct($wpwoofeed_settings, $wpwoofeed_type)
    {
        global $woocommerce, $wpwoofeed_wpml_langs, $wpwoofeed_wpml_debug, $wpWoofProdCatalog,
               $woocommerce_wpwoof_common, $store_info, $wp_query, $wpdb, $post, $_wp_using_ext_object_cache;

        /*===========================    INIT BLOCK  ===========================*/
        $this->_woocommerce = $woocommerce;
        $this->_wpwoofeed_wpml_debug = $wpwoofeed_wpml_debug;
        $this->_woocommerce_wpwoof_common = $woocommerce_wpwoof_common;
        $this->_store_info = $store_info;
        $this->_wpwoofeed_settings = $wpwoofeed_settings;
        $this->_wpwoofeed_type = $wpwoofeed_type;
        $this->_wp_query = $wp_query;
        $this->_wpdb = $wpdb;
        $this->_post = $post;
        $this->_wp_using_ext_object_cache = $_wp_using_ext_object_cache;

        if(!isset($this->_wpwoofeed_settings['feed_name']) || !isset($this->_wpwoofeed_settings['feed_type']) || !isset($this->_wpwoofeed_settings['field_mapping']) ) exit("Error format");

        /*=========================== END INIT BLOCK ===========================*/
    } //function __construct($feed_data, $type ) {
    private function _storedebug($obj){
        if(WPWOOF_DEBUG) wpwoofStoreDebug($this->_wpwoofeed_wpml_debug,$obj);
    }
    private function _xml_has_error($message) {
        global $xml_has_some_error;
        if( ! $xml_has_some_error && !empty($message) ) {
            //add_action( 'admin_notices', create_function( '', 'echo "'.$message.'";' ), 9999 );
            $xml_has_some_error = true;
        }
    }
    private function getCategoryMetaData($id){
         return get_term_meta($id);

    }

    private function getTerms($isTag=false) {
        $args = array(
                'taxonomy'      => $isTag ? array('product_tag') : array('product_cat'),
                'hide_empty'    => false,
                'orderby'       => 'name',
                'order'         => 'ASC'
            );
        $array_terms = get_terms($args);

        if(count($array_terms)>0) foreach($array_terms as $idx => $term){
            if(isset($term->term_id)) $array_terms[$idx]->{'meta_data'} = $this->getCategoryMetaData( $term->term_id );
        }

        return $array_terms;
    }
    private function _getCategorySlufByID($termID){

        foreach($this->_aTerms as $term){
            if($term->term_id == $termID) return $term->slug;
        }
        foreach($this->_aTermsT as $term){
            if($term->term_id == $termID) return $term->slug;
        }
    }
    private function _searchValue($termID,$metaKey){
        $result = "";
            foreach($this->_aTerms as $_term){
                if($_term->term_id==$termID  ){
                   if( isset($_term->meta_data) && !empty($_term->meta_data[$metaKey][0]) ) $result  = $_term->meta_data[$metaKey][0];
                   if( empty($result) && $_term->parent>0 ){
                        $result = $this->_searchValue($_term->parent,$metaKey);
                    }
                    return $result;
                }
            }
       return $result;
    }
    private function _getTagMeta($product,$metaKey){
        $terms = get_the_terms( $this->_get_id($product), 'product_tag' );
        $result = "";
        if(is_array($terms)) foreach ($terms as $term) {
            $result = $this->_searchValue($term->term_id,$metaKey);
            if(!empty($result)) return $result;
        }
        return $result ;
    }
    private function _getCategoryMeta($product,$metaKey){
        $prId =  $this->_getParentID($product);
        if(!$prId) $prId  = $this->_get_id($product);
        $terms_tag = get_the_terms( $prId, 'product_tag' );
        $terms_cat = get_the_terms( $prId, 'product_cat' );
        if (is_array($terms_cat) and is_array($terms_tag)) {
            $terms = array_merge($terms_cat,$terms_tag);
        }
            elseif(is_array($terms_cat)) {
                $terms = $terms_cat;
            }
            else {
                $terms = $terms_tag;
            }
        $result = "";
        if(is_array($terms)) foreach ($terms as $term) {
           $result = $this->_searchValue($term->term_id,$metaKey);
           if(!empty($result)) return $result;
        }
        return $result ;
    }

    private function _enforce_length($text, $length, $full_words = false){
        if ( empty($length) ||  !is_string( $text ) ||  !empty($text)  && strlen( $text ) <= $length ) {
            return $text;
        }

        if ( $full_words === true ) {
            $text = substr( $text, 0, $length );
            $pos = strrpos($text, ' ');
            $text = substr( $text, 0, $pos );
        } else {
            $text = substr( $text, 0, $length );
        }

        return $text;
    }
    private function _terminate_text($text, $use_cdata = true){

        if( !empty($text) ) {
            if( is_string($text) ) {
                $text = str_replace("><","> <",$text);
                $text = preg_replace("/\r\n|\r|\n/", ' ', $text);
                $text = (strpos($text, "<![CDATA[") !== false) ? str_replace("<![CDATA[", "", str_replace(']]>', '', $text)) : $text;
                $text = WPWOOF_Encoding::toUTF8(html_entity_decode($text));
                $text = strip_tags($text);
                $text = strip_shortcodes(do_shortcode($text));
                $text = preg_replace('#\[[^\]]+\]#', '', $text);
                if ('xml' == $this->_wpwoofeed_type) {
                    $text = wp_kses_decode_entities($text);
                    if ($use_cdata) {
                        $text = "<![CDATA[" . $text . "]]>";
                    } else {
                        $text = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', htmlspecialchars($text, ENT_QUOTES));
                    }
                }
            }
            //$text = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ',  $text);
            return preg_replace('/[\x{0a}-\x{1f}]/u', '*', $text);
        }
        return "";
    }
    private function _sentence_case($string) {

        $sentences = preg_split('/((?:^|[.?!]+)\s*)/', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
        $new_string = '';
        foreach ($sentences as $key => $sentence) {
            $new_string .= ($key & 1) == 0 ? ucfirst(strtolower($sentence)) : $sentence;
        }

        return $new_string;
    }
    private function _getParentID($product){
        if( isset($product) && in_array($this->_product_get_type($product), $this->_aVariationsType ) ){
            // isset($product->variation_id)     
            $parId = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_product_get_property('parent_id',$product) :  $product->parent_id;
            if(!$parId) {
                $parId = $product->parent->id;
            }
            return  $parId;
        }
        return null;
    }
    private function get_maim_product(){
        return (!empty($this->_wpwoofeed_settings["product_tmp_data"]["product"]) )  ? $this->_wpwoofeed_settings["product_tmp_data"]["product"] : null;
    }
    private function _thumbnail_src( $post_id = null, $size = 'full'/*'post-thumbnail'*/, $product ) {
        if(empty($post_id)) $post_id = $this->_get_id($product);
        $size = !empty($this->_wpwoofeed_settings["field_mapping"]['image-size']) ? $this->_wpwoofeed_settings["field_mapping"]['image-size'] : "full";


        /*  Custom images. When you edit your products you can add custom images. */
        if (!empty($this->_wpwoofeed_settings["wpwoofeed_images"]['custom']) && !empty($this->_wpwoofeed_settings["wpwoofeed_images"]['0'])) {
            $value = $this->get_tagvalue($this->_wpwoofeed_settings["wpwoofeed_images"]['custom'], $product, $this->_wpwoofeed_settings["wpwoofeed_images"]['custom']);

            if (!empty($value)) {
                return $value;
            }
        }
        /*  Your product feature image */
        if (   !empty($this->_wpwoofeed_settings["wpwoofeed_images"]['product_image'])
            ||
            !empty($this->_wpwoofeed_settings["field_mapping"]["image_link"]["value"])
            && (
                $this->_wpwoofeed_settings["field_mapping"]["image_link"]["value"]=='wpwoofdefa_product_image'
                ||
                $this->_wpwoofeed_settings["field_mapping"]["image_link"]["value"]=='wpwoofdefa_image_link'
            )  ) {



            $post_thumbnail_id = get_post_thumbnail_id($post_id);


            if(  empty($post_thumbnail_id) && in_array( $this->_product_get_type($product), $this->_aVariationsType )  ) {
                    $mproduct = $this->get_maim_product();
                    $post_thumbnail_id = get_post_thumbnail_id( $this->_get_id($mproduct));
            }



            if (empty($post_thumbnail_id)) {
                $attributes = $product->get_attributes();
                $first_key = false;
                if(is_array($attributes))  foreach ($attributes as $attr) {
                    if(!empty($this->_wpwoofeed_settings["prod_images"]) && is_array($this->_wpwoofeed_settings["prod_images"])) foreach ($this->_wpwoofeed_settings["prod_images"] as $key => $aimg) {
                        if ($first_key === false) $first_key = $key;
                        if (!empty($aimg[1]) && $aimg[1] == $attr) {
                            $post_thumbnail_id = $aimg[0];
                            break;
                        }
                    }
                    if ($post_thumbnail_id) break;
                }
                if (!$post_thumbnail_id && isset($this->_wpwoofeed_settings["prod_images"][$first_key][0])) $post_thumbnail_id = $this->_wpwoofeed_settings["prod_images"][$first_key][0];
            }




            if (!empty($post_thumbnail_id)) {
                $image = wp_get_attachment_image_src($post_thumbnail_id, $size, false);
                $image = apply_filters('wp_get_attachment_image_src', $image, $post_thumbnail_id, $size, false);

                $src = "";
                if (!empty($image[0])) $src = $image[0];
                if (!empty($src)) {
                    $value =  $this->_check_url($src);
                    return  $value;
                }
            }
        }




        /*  The category image . */
        if ( !empty($this->_wpwoofeed_settings["wpwoofeed_images"]['category'])) {
            $value = $this->_getCategoryMeta($product,'thumbnail_id');
            if(!empty($value)) {
                $image = wp_get_attachment_image_src($value, $size, false);
                $image = apply_filters('wp_get_attachment_image_src', $image, $value, 'full'/*$size*/, false);
                $src = "";
                if (!empty($image[0])) $src = $image[0];
                if (!empty($src)) return $this->_check_url($src);
            }

        }
        return "";
    }
    private function _check_url($src){
        $src=trim($src);
        if(empty($src)) return '';
        return (!preg_match("~^(?:f|ht)tps?://~i", $src)) ?  home_url($src) : $src;
    }
    private function _load_product( $post ) {
        if ( function_exists( 'wc_get_product' ) ) {
            // 2.2 compat.
            return wc_get_product( $post );
        } else if ( function_exists( 'get_product' ) ) {
            // 2.0 compat.
            return get_product( $post );
        } else {
            return new WC_Product( $this->_get_id($post) );
        }
    }    
    private function _get_id($product){
        if(empty($product)) return null;
        if( method_exists(  $product, 'get_id') ) return $product->get_id();
        if( property_exists($product, 'ID'    ) ) return $product->ID;
        if( property_exists($product, 'id'    ) ) return $product->id;
        return null;
    }
    private function _get_child( $product,$child_id ) {
        return version_compare( WC_VERSION, '3.0', '>=' ) ?  wc_get_product($child_id) : $product->get_child($child_id);
    }
    private function _loadMetaExtraData($product){

        $this->_metaData = null;
        $prodID = $this->_get_id($product);

        $data = get_post_meta($prodID);
        $meta = array();
//        if( in_array( $this->_product_get_type($product) , $this->_aVariationsType ) ){
//           $meta = $this->_mainMetaData;
//        } else 
            $meta = $data;

        foreach(array('facebook','google','adsensecustom') as $key) {
            if (   isset($data['wpfoof-'.$key][0])
                && isset($data['wpwoof'.$key][0])
                && !empty($data['wpfoof-'.$key][0])) {
                $d = unserialize($data['wpwoofgoogle'][0]);
                foreach ($d as $k => $elm) {
                    $meta['wpfoof_' . $k] = array(0 => $elm['value']);
                }
            }
//            if(isset($data['wpwoofextra'])){
//                foreach (unserialize($data['wpwoofextra'][0]) as $k => $elm) {
//                    $meta['wpfoof_' . (isset($elm['custom_tag_name'])?$elm['custom_tag_name']:$k)] = array(0 => $elm['value']);
//                }
//            }
        }
        $this->_metaData = empty($meta)?$data:$meta;
        if( !in_array( $this->_product_get_type($product) , $this->_aVariationsType ) ) $this->_mainMetaData = $meta;
       // if($prodID==41) trace($this->_metaData,1);
    }   
    private function _product_get_property($proper,$product){
        if(empty($product)) return null;
        return version_compare( WC_VERSION, '3.0', '>=' ) ? $product->{"get_".$proper}() : $product->$proper;
    }
    private function searchBrandValue(){
        foreach($this->_metaData as $prop => $vals){
            if(stripos($prop,'brand')!==false && !empty($vals[0])  ){
                $a = (is_numeric($vals[0])) ? get_the_category_by_ID($vals[0]) : $vals[0];
                return is_string($a) ? $a : null;
            }
        }
        return null;
    }
    private function _getMeda($key,$product) {
         $meta = (isset($this->_metaData[$key][0])) ? $this->_metaData[$key][0] : '';
         if($meta==='' and  in_array( $this->_product_get_type($product) , $this->_aVariationsType ) ){
             $meta = (isset($this->_mainMetaData[$key][0])) ? $this->_mainMetaData[$key][0] : '';
         }
         return $meta;
    }
    private function _product_get_excerpt($product){
        if( version_compare( WC_VERSION, '3.0', '>=' ) ){
            $post = get_post($this->_get_id($product));
            return $post->post_excerpt;
        } else {
            return $product->post->post_excerpt;
        }
    }
    private function _product_get_short_description($product){
        return   apply_filters('the_content',version_compare( WC_VERSION, '3.0', '>=' ) ? $product->get_short_description() : $product->short_description );
    }
    private function _product_get_description($product){
        return  apply_filters('the_content', version_compare( WC_VERSION, '3.0', '>=' ) ? $product->get_description() : $product->post->post_content );
    }
    private function _wpwoofeed_product_get_title($product){
        return version_compare( WC_VERSION, '3.0', '>=' ) ? $product->get_title() : $product->post->post_title;
    }
    private function _product_get_type($product){
        return version_compare( WC_VERSION, '3.0', '>=' ) ? $product->get_type() : $product->product_type;
    }
    function get_tagvalue($tag, $product, $tagid, $tag_option = array()){

        $tag = str_replace('wpwoofdefa_','',$tag);


        if( strpos($tagid, 'additional_image_link') !== false ) {
            $image_position = str_replace('additional_image_link_', '', $tagid);
            $image_position = (int) $image_position - 1;
        } else if( strpos($tag, 'additional_image_link') !== false ) {
            $image_position = str_replace('additional_image_link_', '', $tag);
            $image_position = (int) $image_position - 1;
        }
        if(  strpos($tag, 'additional_image_link') !== false ){
            $tag = 'additional_image_link';
        }

        switch ($tag) {

            case 'id':
                $return = $this->_get_id( $product );
                if( in_array( $this->_product_get_type($product) ,$this->_aVariationsType ) ) {
                    $return =  version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                }

                return  $return;

            case 'description_short':
                $description = '';
                if( in_array( $this->_product_get_type($product) , $this->_aVariationsType ) ){
                    $product_id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                    $description = get_post_meta($product_id, '_variation_description', true) ;
                }
                if( empty($description) ) {
                    $mainpr      = $this->get_maim_product();
                    $description = $this->_product_get_excerpt($mainpr);
                    if( empty($description) ) $description = $this->_product_get_short_description( $mainpr );
                }

                return  apply_filters('the_content',$description);
                
            case 'short_description':
                if (isset($this->_wpwoofeed_settings["field_mapping"]['add_short_description']) && empty($this->_wpwoofeed_settings["field_mapping"]['add_short_description'])) return "";
                $shdedc = $this->_product_get_short_description( $product );
                return strlen($shdedc)>1000?substr($shdedc, 0,1000):$shdedc;

                  

            case 'description':
                $description = '';
                if( in_array( $this->_product_get_type($product) , $this->_aVariationsType ) ){
                    $product_id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                    $description = get_post_meta($product_id, '_variation_description', true);

                    if( empty($description) ) {
                        $mainpr      = $this->get_maim_product();
                        $description = ($mainpr) ? $this->_product_get_description( $mainpr ) : '';
                    }
                }
                if( empty($description) ) {
                    $description = $this->_product_get_description( $product );
                }
                return apply_filters('the_content', $description);

            case 'product_image':
            case 'image_link':
                    $size = !empty($this->_wpwoofeed_settings["field_mapping"]['image-size']) ? $this->_wpwoofeed_settings["field_mapping"]['image-size'] : "full";
                    if( in_array( $this->_product_get_type($product),$this->_aVariationsType ) ){

                        $link_var = $this->_thumbnail_src(version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id,  $size,$product);


                        if(!empty( $link_var)) return $link_var;
                    }
                //if($this->_get_id($product)=="2144") echo "2144:".$tag.":".$this->_thumbnail_src( $this->_getParentID($product), 'shop_single', $product )."\n";
                return $this->_thumbnail_src( $this->_get_id($product), $size, $product );

            case 'title':
                        $mainpr = $this->get_maim_product();
                        $title  = ($mainpr) ?  $this->_wpwoofeed_product_get_title($mainpr) : '';
               return $title;

            case 'link':
                    $lnk_post_id = $this->_get_id( $product );
                    $url = get_permalink( $lnk_post_id );
                    if( in_array( $this->_product_get_type($product), $this->_aVariationsType )  ) {
                        $lnk_post_id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                        $wc_product = new WC_Product_Variation($lnk_post_id);
                        $url = $wc_product->get_permalink();
                        unset($wc_product);
                    }
                    return $url;

            case 'item_group_id':
                return $this->_store_info->item_group_id;

            case 'availability':
                if( $product->is_in_stock() ) {
                    $stock = 'in stock';
                } else {
                    $stock = 'out of stock';
                }
                return $stock;
            
            case 'variation_description':
                $variation_description = '';
                if( in_array( $this->_product_get_type($product),$this->_aVariationsType ) ){
                    $product_id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                    $variation_description = get_post_meta($product_id, '_variation_description', true);
                    if(empty($variation_description)){
                        $mainpr                = $this->get_maim_product();
                        $variation_description = ($mainpr) ? $this->_product_get_description ($mainpr) : '';
                    }
                }
                return apply_filters('the_content', $variation_description);

            case 'use_custom_attribute':
                $attribute_value = '';
                if( isset( $this->_aCurrentlyFields[$tagid]['custom_attribute'] ) ) {
                    $custom_attribute = $this->_aCurrentlyFields[$tagid]['custom_attribute'];
                    $taxonomy = strtolower($custom_attribute);
                    if( !empty($taxonomy) && in_array( $this->_product_get_type($product), $this->_aVariationsType )) {
                        $attributes = $product->get_variation_attributes();
                        foreach ($attributes as $attribute => $attribute_value) {
                            $attribute = strtolower($attribute);
                            if( strpos($attribute, $taxonomy) !== false ) {
                                return $attribute_value;
                            }
                        }
                    }
                }
                return $attribute_value;

            case 'wpfoof-mpn-name':
            case 'wpfoof-gtin-name':
                $data =  "";
                if( in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $data =  get_post_meta( version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id, $tag, true );
                } else {
                    $data = get_post_meta( $this->_get_id( $product ), $tag, true );
                }
                return $data;

            case 'mashshare_product_image':
                $size = ( 1==0 && !empty($this->_wpwoofeed_settings["field_mapping"]['image-size']) ) ? $this->_wpwoofeed_settings["field_mapping"]['image-size'] : "full";
                $data = '';
                if( in_array($this->_product_get_type($product), $this->_aVariationsType ) ){
                    $data = get_post_meta( ( version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id) ,'mashsb_og_image',true);
                }
                if(!$data) {
                    $mainpr      = get_maim_product();
                    $data = get_post_meta( $this->_get_id($mainpr ),'mashsb_og_image',true);
                }


                if($data ){
                    $data=wp_get_attachment_image_src($data, $size, false );
                    $src = $this->_check_url( $data[0] );
                    return  $this->_check_url( ($src && !empty($src)) ? $src : '' ) ;
                }


                if( in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $data = get_post_meta( version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id,'mashsb_pinterest_image',true);
                }
                if(!$data) {
                    $mainpr      = $this->get_maim_product();
                    $data = get_post_meta( $this->_get_id($mainpr ),'mashsb_pinterest_image',true);
                }

                if($data){
                    $data = wp_get_attachment_image_src($data, $size, false );
                    $src  = $this->_check_url( $data[0] );
                    return  $this->_check_url( ($src && !empty($src)) ? $src : '' );
                }

                return '';

            case 'mpn':
                $data = $product->get_sku();
                if( $this->_product_get_type($product)=='subscription_variation' ) {
                    $data = get_post_meta( version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id,'_sku');
                }
                return $data;

            case '_sku':
                $data = $product->get_sku();
                if( $this->_product_get_type($product)=='subscription_variation' ) {
                    $data = get_post_meta( version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id,'_sku', true);
                }
                return $data;

            case 'additional_image_link':
                $tagvalue = '';
                $imgIds = version_compare( WC_VERSION, '3.0', '>=' ) ?   $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
                $size = ( 1==0 && !empty($this->_wpwoofeed_settings["field_mapping"]['image-size']) ) ? $this->_wpwoofeed_settings["field_mapping"]['image-size'] : "full";
                if(!count($imgIds)){
                    $product = $this->get_maim_product();
                    if($product) $imgIds = version_compare( WC_VERSION, '3.0', '>=' ) ?   $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
                }
                $images = array();
                if (count($imgIds)) {
                    foreach ($imgIds as $key => $value) {
                        if ($key < 9) {
                            $images[$key] = $this->_check_url(wp_get_attachment_image_src($value,$size,false));
                        }
                    }
                }
                if ($images && is_array($images) ) {
                    if( isset( $images[$image_position] ) )
                        $tagvalue.= $images[$image_position] . ',';
                }
                $tagvalue = rtrim($tagvalue, ',');
                return $tagvalue;

            case 'product_type':

                $prId =  $this->_getParentID($product);
                if(!$prId) $prId = $this->_get_id( $product );
                $categories = wp_get_object_terms( $prId, 'product_cat');

                $categories_string = array();
                if( ! is_wp_error($categories) ) {
                    foreach($categories as $cat) {
                        $categories_string[] = $cat->name;
                    }
                }
                $categories_string = implode(' > ', $categories_string);
                return $categories_string;

            case 'product_type_normal':
                $product_type = $this->_product_get_type($product);
                return $product_type;

            case 'sale_price_effective_date':
                if( in_array( $this->_product_get_type($product), $this->_aVariationsType ) )
                    $product_id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                else
                    $product_id = $this->_get_id( $product );
                $from = get_post_meta($product_id, '_sale_price_dates_from', true);
                $to = get_post_meta($product_id, '_sale_price_dates_to', true);

                if (!empty($from) && !empty($to)) {
                    $from = date_i18n('Y-m-d\TH:iO', $from);
                    $to = date_i18n('Y-m-d\TH:iO', $to);
                    $date = "$from" . "/" . "$to";
                } else {
                    $date = "";
                }
                $tagvalue = $date;
                return $tagvalue;

            case 'shipping_class':
                $shipping = $product->get_shipping_class();
                if( empty($shipping) ) {
                    $mainpr = $this->get_maim_product();
                    $shipping  = ($mainpr) ?  $mainpr->get_shipping_class() : '';
                }
                return $shipping;

            case 'shipping_weight':

                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_weight();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_weight() : '';
                }

                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_weight_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    //exit("VALUE:".$tagvalue);
                    return $tagvalue;
                } else {
                    return '';
                }
                break;

            case 'shipping_weight_value':

                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_weight();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_weight() : '';
                }
                return $tagvalue;
                break;
                
            case 'shipping_weight_unit':

                $unit = get_option( 'woocommerce_weight_unit' );
                return esc_attr($unit);

                break;

            case 'shipping_length':
                if( in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_length();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_length() : '';
                }

                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_dimension_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    return $tagvalue;
                }
                return '';

            case 'shipping_width':
                if(  in_array( $this->_product_get_type($product),$this->_aVariationsType ) ){
                    $tagvalue = $product->get_width();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_width() : '';
                }

                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_dimension_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    return $tagvalue;
                } else {
                    return '';
                }
                return $tagvalue;

            case 'shipping_height':
                if(   in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_height();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_height() : '';
                }

                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_dimension_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    return $tagvalue;
                } else {
                    return '';
                }
                return $tagvalue;
                
            case 'length':
                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_length();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_length() : '';
                }
                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_dimension_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    return $tagvalue;
                }
                return '';

            case 'width':
                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_width();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_width() : '';
                }


                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_dimension_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    return $tagvalue;
                }
                return $tagvalue;

            case 'height':
                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_height();
                }

                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_height() : '';
                }
                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_dimension_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    return $tagvalue;
                }
                return $tagvalue;

            case 'weight':
                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tagvalue = $product->get_weight();
                }
                if( empty($tagvalue) ) {
                    $mainpr = $this->get_maim_product();
                    $tagvalue  = ($mainpr) ?  $mainpr->get_weight() : '';
                }
                if( !empty($tagvalue) ) {
                    $unit = get_option( 'woocommerce_weight_unit' );
                    $tagvalue = $tagvalue . ' ' . esc_attr($unit);
                    return $tagvalue;
                }
                return '';

            case 'tags':

                $tags_string = array();
                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $tags = wp_get_object_terms( version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id , 'product_tag');

                    if( ! is_wp_error($tags) ) {
                        foreach($tags as $tag) {
                            $tags_string[] = $tag->name;
                        }
                    }
                }
                if( count($tags_string)==0 ) {
                    $mainpr = $this->get_maim_product();
                    if ($mainpr) {
                        $tags = wp_get_object_terms( $this->_get_id( $mainpr ), 'product_tag');
                        if( ! is_wp_error($tags) ) {
                            foreach($tags as $tag) {
                                $tags_string[] = $tag->name;
                            }
                        }
                    }
                }

                $tags_string = implode(', ', $tags_string);
                return $tags_string;

            case 'stock_quantity':
                $qty = $product->get_stock_quantity();
                return ($qty>0) ? $qty : "0";

            case 'average_rating':
                $average_rating = '';

                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType )  ){
                    $average_rating = $product->get_average_rating();
                }
                if( empty($average_rating) ) {
                    $mainpr = $this->get_maim_product();
                    $average_rating  = ($mainpr) ?  $mainpr->get_average_rating() : '';
                }

                return $average_rating;

            case 'total_rating':
                $total_rating = '';
                if(  in_array( $this->_product_get_type($product), $this->_aVariationsType ) ){
                    $total_rating = $product->get_rating_count();
                }

                if( empty($total_rating) ) {
                    $mainpr = $this->get_maim_product();
                    $total_rating  = ($mainpr) ?  $mainpr->get_rating_count() : '';
                }
                return $total_rating;

            case 'sale_start_date':

                if( in_array( $this->_product_get_type($product), $this->_aVariationsType ) )
                    $product_id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                else
                    $product_id = $this->_get_id( $product );

                $from = get_post_meta($product_id, '_sale_price_dates_from', true);
                if (!empty($from)) {
                    $tagvalue = date_i18n('Y-m-d\TH:iO', $from);
                } else {
                    $tagvalue = "";
                }
                return $tagvalue;

            case 'sale_end_date':
                if( in_array( $this->_product_get_type($product), $this->_aVariationsType ) )
                    $product_id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($product) : $product->variation_id;
                else
                    $product_id = $this->_get_id( $product );
                $to = get_post_meta($product_id, '_sale_price_dates_to', true);

                if (!empty($to)) {
                    $tagvalue = date_i18n('Y-m-d\TH:iO', $to);
                } else {
                    $tagvalue = "";
                }
                return $tagvalue;

            case 'price':
                return $this->_wpwoofeed_price($product, null, null, $tagid);

            case 'sale_price':

            default:
                return $this->_getMeda($tag,$product);

        }
        return '';
    }
    private function _custom_user_function( $product, $item, $field, $id, $data = array() ) {
        //if($id=='image_link' && $this->_get_id($product)=="41") trace("_custom_user_function: ITEM:".print_r($item,true)."|FIELD:".print_r($field,true)."|ID:".print_r($id,true),true);
        /*  if($this->_get_id($product)=="41" && $element=="image_link") exit($func);

         * <pre>_custom_user_function:|FIELD:Array
            (
               [value] => product_image
            )
           |ID:image_link</pre>
         * */
        $value = isset($field['value']) ? $field['value'] : '' ;


        if(empty($value) && isset($field['rules']['woocommerce_default']['value']) && isset($field['rules']['woocommerce_default']['automap'])){
            $value = $field['rules']['woocommerce_default']['value'];
        }



        $tagvalue = '';
        if( empty($value) ) {
            return '';
        } 

        $data = array_merge(array(
            'wpwoofdefa' => true,
            'wpwoofmeta' => true,
            'wpwoofattr' => true,
        ), $data);


        //if($id=='shipping_weight')  trace("_custom_user_function:".print_r($value,true),1);


        if( !empty($value) && $value=='custom_value' && isset($field['custom_value']) ){
            return $field['custom_value'];
        }elseif( !empty($value) && is_string($value) && strpos($value, 'wpwoofattr_') !== false && $data['wpwoofattr'] ){

            return $this->_wpwoofeed_attr($product, $item, $field, $id);
        }else {

            //if($id=='id') trace("_custom_user_function::get_tagvalue:VALUE=".print_r($value,true)."|ID:".print_r($id,true)."|FIELD:".print_r($field,true)."|RESULT=>".$this->get_tagvalue($value, $product, $id, $field),true);
            return $this->get_tagvalue($value, $product, $id, $field);
        }

        return false;
    }
    private function _wpwoofeed_attr( $product, $item, $field, $id ) {
        //trace("ID:".$id."|ITEM:".print_r($item,true)."|FIELD:".print_r($field,true),true);
        $taxonomy = ( isset( $field['define'] ) && isset($field['define']['option']) ) ?  $field['define']['option'] : $field['value'];
        $taxonomy = str_replace('wpwoofattr_', '', $taxonomy);
        
        $value = get_post_meta($this->_get_id($product), $taxonomy, true);
        
        if ($value!=="") return $value;


        if( !empty($taxonomy) && is_string($taxonomy) && strpos($taxonomy, 'pa_') !== false && $this->_product_get_type($product) == 'variation' ) {
            $txnmy = str_replace('pa_', '', $taxonomy);
            $attributes = $product->get_variation_attributes();


            foreach ($attributes as $attribute => $attribute_value) {
                if( is_string($attribute) && strpos($attribute, $txnmy) !== false ) {
                    $term = get_term_by('slug',$attribute_value, $taxonomy);
                    $attribute_value = ($term && isset($term->name)) ? $term->name : null;
                    return $attribute_value;
                }
            }
        }


        $product = $this->get_maim_product();
        $the_terms = wp_get_post_terms( $this->_get_id($product), $taxonomy, array( 'fields' => 'names' ));


        $tagvalue = '';
        if( !is_wp_error($the_terms) && !empty($the_terms) ) {
            $sep = "";
            $tagvalue =  implode(" > ", $the_terms);

        }

        return $tagvalue;
    }
    private function _wpwoofeed_description($product, $item, $field, $id){
        $result = "";

        if( !empty( $this->_wpwoofeed_settings['field_mapping']['description'])
              &&
            is_string( $this->_wpwoofeed_settings['field_mapping']['description'])
            &&
            strpos($this->_wpwoofeed_settings['field_mapping']['description'],'wpwoofdefa_') !== false ) {

            $selOld = str_replace('wpwoofdefa_',"",$this->_wpwoofeed_settings['field_mapping']['description']);
            $result = $this->_custom_user_function($product, $item,  array('value' => $selOld ) , $selOld);

            if(!empty($result)) return $result;
        }


        if(count($field)>0)
            foreach($field as $elmID){
            $result = $this->_custom_user_function($product, $item,  array('value' => $elmID) , $elmID);
            if(!empty($result)) return $result;
        }
        $this->_xml_has_error('Description missing in some products');
        return  '';
    }
    function wpwoofeed_old_condition($product, $item, $field, $id){
        $value = isset($field['define'])?$field['define']:null;
        $tagvalue = $this->_custom_user_function($product, $item, $field, $id);
        if( isset($value['global']) && isset($value['global'])  == 1 ){
            $tagvalue =  $value['globalvalue'];
        } else {
            if( empty($tagvalue) ){
                $tagvalue =  $value['missingvalue'];
            }
        }
        if( empty($tagvalue) ) {
            $tagvalue = 'new';
        } else {
            $tagvalue = str_replace(',', ' , ', $tagvalue);
            $tagvalue = ' '.$tagvalue.' ';
            $tagvalue = strtolower($tagvalue);
            if( strpos($tagvalue, ' new ') !== false ) {
                $tagvalue = 'new';
            } elseif( strpos($tagvalue, ' used ') !== false ) {
                $tagvalue = 'used';
            } elseif( strpos($tagvalue, ' refurbished ') !== false ) {
                $tagvalue = 'refurbished';
            } else {
                $tagvalue = 'new';
            }
        }

        return $tagvalue;
    }
    private function _wpwoofeed_condition($product, $item, $field, $id){
        $value = $field['define'];

       if(isset($field['define']['missingvalue'])) return $this->wpwoofeed_old_condition($product, $item, $field, $id);
        $result = '';
        if(count($field)>0) foreach($field as $elmID){
            $result = $this->_getMeda($elmID,$product);
            if(!empty($result)) return $result;
        }
        $result = empty($result) ? $value : $result;
        if( empty($result) ) {
            $this->_xml_has_error('Condition missing in some products');
        }
        return $result;
    }
    private function _wpwoofeed_availability($product, $item, $field, $id){
		$stock = 'out of stock';
		if($this->_store_info->woocommerce_manage_stock && $product->get_manage_stock()) {
			if ($product->has_child()) {
				$children = $product->get_children();

				foreach ($children as $child) {
					$child = $this->_get_child($product, $child);
					if (!$child) continue;
					if ($child->is_in_stock()) {
						$stock = 'in stock';
					}
				}
			} else {
				if ($product->is_in_stock()) {
					$stock = 'in stock';
				}
			}
		} else {
			if ($product->is_in_stock())
				$stock = 'in stock';
		}
		
		return $stock;
	}
    private function _wpwoofeed_inventory($product, $item, $field, $id){

        $result = null;
        $field = isset($this->_wpwoofeed_settings['field_mapping']['inventory']) ? $this->_wpwoofeed_settings['field_mapping']['inventory'] : array("value"=>1,"default"=>5);
        $this->_storedebug("TAG INVENTORY FIELD:");
        $this->_storedebug($field);
        $this->_storedebug("woocommerce_manage_stock:".$this->_store_info->woocommerce_manage_stock);
        $this->_storedebug("get_manage_stock():".$product->get_manage_stock());
        $this->_storedebug("is_in_stock():".$product->is_in_stock());
        $this->_storedebug("[product_tmp_data][manage_stock]:".$this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"]);
        $this->_storedebug("[product_tmp_data][_stock_main]:".$this->_wpwoofeed_settings["product_tmp_data"]["_stock_main"]);

        $field['default'] = !isset( $field['default'] ) ? 5 : (int)$field['default'];

        if(!empty($field['value'])  ) {
            if ($this->_store_info->woocommerce_manage_stock && $this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"]) {

                if($product->has_child()){
                    $this->_storedebug("[product_tmp_data][_stock_childs]:".$this->_wpwoofeed_settings["product_tmp_data"]["_stock_childs"]);
					$childStock = $this->_wpwoofeed_settings["product_tmp_data"]["_stock_childs"];
					$mainStock = $this->_wpwoofeed_settings["product_tmp_data"]["_stock_main"];
					$useStock = ($childStock) ? $childStock : $mainStock;
					$result = $this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"] ? (int)$useStock : (int)$field['default'];
                    $this->_storedebug("HAS CHILD RESULT:".$result);
                } else {
                    if ($product->get_manage_stock()) { 
                        $result = $product->get_stock_quantity(); 
                        
                    } else {
                        $result = $product->is_in_stock() ?  ( $this->_wpwoofeed_settings["product_tmp_data"]["_stock_main"]>0 ? (int)$this->_wpwoofeed_settings["product_tmp_data"]["_stock_main"] : (int)$field['default'] ) : 0;
                    }
                        
                    $this->_storedebug("HAS NOT CHILD RESULT:".$result);

                }

            } else {
				$result = ($product->is_in_stock()) ? (int)$field['default'] : 0;
                $this->_storedebug("NOT MANAGE STOCK:".$result);
            }
            return ($result<0) ? (($product->is_in_stock()) ? (int)$field['default'] : 0) : $result;
        }


        return null;
    }
    private function _wpwoofeed_brand($product, $item, $field, $id){

        $wpwoof_values    = $this->_woocommerce_wpwoof_common->getGlobalData();


        if(isset($wpwoof_values['brand'])) $field = $wpwoof_values['brand'];

        if(!isset($field['define']))$field['define']='';
        $value    = $field['define'];
        $result = '';

        /* old version complete */
        if(isset($field['define']['missingvalue'])){
            $value = $field['define'];
            if( !empty($value['globalvalue']) && isset($value['global']) && isset($value['global'])  == 1 ){
               return $value['globalvalue'];
            } else {
                if( empty($tagvalue) && ! empty($value['missingvalue']) ){
                    return $value['missingvalue'];
                }
            }
        }
        /* old version complete */
        if(!empty($field['value'])){
            $result = $this->_custom_user_function($product, $item, $field, $id);
            if(!empty($result)) return $result;
        }

        if(count($field)>0){
            if ( is_plugin_active( WPWOOF_BRAND_YWBA ) && isset($field["WPWOOF_BRAND_YWBA"])){
                  $result = $this->_getMeda('_yoast_wpseo_primary_yith_product_brand',$product);
                  if(!empty($result)) return $result;
            }
            if( is_plugin_active( WPWOOF_BRAND_PEWB) && isset($field["WPWOOF_BRAND_PEWB"])){
                $result = $this->_getMeda('_yoast_wpseo_primary_pwb-brand',$product);
                if(!empty($result)) return $result;
            }
            if( is_plugin_active( WPWOOF_BRAND_PBFW) && isset($field["WPWOOF_BRAND_PBFW"])){
                $term = get_the_terms($this->_get_id($product->ID), "product_brand");
                if (!empty($term) && !is_wp_error($term) && !empty($term[0]) && is_object($term[0])  && property_exists($term[0],'name')   ) {
                    $result = $term[0]->name;
                    if (!empty($result)) return $result;
                }
            }
            if(!empty($field["autodetect"])) {
                $result = $this->searchBrandValue();
                if ($result) return $result;
            }
        }
        $result = empty($result) ? $value : $result;
        if( empty($result) ) {   $this->_xml_has_error('Brand missing in some products');    }
        return $result;
    }
    private function _wpwoofeed_gtin($product, $item, $field, $id){
        $result = $this->_getMeda('wpfoof-gtin-name',$product);
        if(empty($result) && isset($field['value'])) {
            $result = $this->_custom_user_function($product, $item, $field, $id);
        }
        return  $result;
    }
    private function _wpwoofeed_mpn($product, $item, $field, $id){
       
        $result = $this->_getMeda('wpfoof-mpn-name',$product);
        if(empty($result) && isset($field['value'])) {
            $result = $this->_custom_user_function($product, $item, $field, $id);
        }
        return  $result;
    }
    private function _wpwoofeed_title($product, $item, $field, $id){
        $title = $this->_custom_user_function($product, $item, $field, $id);
        return  $title;
    }
    private function _wpwoofeed_item_group_id($product,$item, $field, $tag){


        return !empty($field['define']) ? $field['define'] :  '';
        /*
        if( ( in_array(   $this->_product_get_type($product) , $this->_aVariationsType )
                || $product->has_child() )
            && !empty($field['define'])  ) {
            return $field['define'];
        }else {
            return '';
        }
        */
    }

    private function _wpwoofeed_google_product_category($product,$item, $field, $tag){
        //1 product value
        $result = trim($this->_getMeda('feed_google_category',$product));
        if(!empty($result)) return $result;


        if( in_array( $this->_product_get_type($product), $this->_aVariationsType ) ) {
            $product = $this->get_maim_product();
            $value = trim(get_post_meta($this->_get_id($product), 'feed_google_category', true));
            if(!empty($value)) return $value;
        }

        /*  2.The category image . */
        $value = trim($this->_getCategoryMeta($product,'feed_google_category'));
        if(!empty($value)) return $value;


        //3 feed value [feed_google_category]
        if (!empty(trim($this->_wpwoofeed_settings['feed_google_category']))){
            return $this->_wpwoofeed_settings['feed_google_category'];
        }

        //4 global feed value [global_taxonomy][name]
        if (!empty(trim($this->_wpwoofeed_settings['global_taxonomy']['name']))){
            return $this->_wpwoofeed_settings['global_taxonomy']['name'];
        }

        //feed_google_category

        return "";
    }
    private function _wpwoofeed_taxlabel($product,$item, $field, $tag){
        $value = $this->_getCategoryMeta($product,'wpfoof-tax-category');
        return $value;
    }
    private function _wpwoofeed_shipping_label($product,$item, $field, $tag){
        /*  1.The category image . */
        $value = $this->_getCategoryMeta($product,'wpfoof-shipping-label');
        if(!empty($value)) return $value;
        $result = $this->_custom_user_function($product, $item, $field,  $tag);
        return  $result;
    }
    private function _wpwoofeed_adult($product,$item, $field, $tag){
        //wpfoof-adult
         //1 product value
        $result = $this->_getMeda('wpfoof_adult',$product);
        if(!empty($result)) return $result;

        /*  2.The category image . */
        $value = $this->_getCategoryMeta($product,'wpfoof-adult');
        if(!empty($value)) return $value;

        $result = $this->_custom_user_function($product, $item, $field, $tag);
        return  $result;


    }
    private function _wpwoofeed_contextualkeywords($product,$item, $field, $tag){
        if(!empty($field) ) {
            $tagstr="";
            $zp="";
            if(count($this->_aTags)) foreach ($this->_aTags as $tag){
                if(isset($tag->name)) { $tagstr.=$zp.$tag->name; $zp=", "; }
            }
        }
        return $tagstr;
    }
    private function _wpwoofeed_itemaddress($product,$item, $field, $tag){
        return (!empty($field['value']) ) ?  $field['value'] : "";
    }
    private function _wpwoofeed_itemtitle($product,$item, $field, $tag){
        return $this->_wpwoofeed_title($product, $item, $field, 'title');
    }
    private function _wpwoofeed_itemdescription($product,$item, $field, $tag){
        return $this->_wpwoofeed_description($product, $item, $field, 'description');
    }
    private function _wpwoofeed_itemcategory($product,$item, $field, $tag){
        return  $this->_custom_user_function($product, $item, $field, 'product_type');
    }
    private function _generate_prices_for_product( $woocommerce_product ) {


        $prices = new stdClass();
        $prices->sale_price    = null;
        $prices->regular_price  = null;


        $this->_storedebug( "this->_generate_prices_for_product". $this->_store_info->currency." != ".$this->_store_info->default_currency  );
        $this->_storedebug("PROD ID:".$this->_get_id($woocommerce_product));
        $rate =  ( $this->_store_info->currencyRate>0 ) ?  $this->_store_info->currencyRate*1.0 : 1.0;



        if( $woocommerce_product->is_type("subscription") || $woocommerce_product->is_type("subscription_variation")){

                $id = version_compare( WC_VERSION, '3.0', '>=' ) ? $this->_get_id($woocommerce_product) : $woocommerce_product->variation_id;



                $isTrial = get_post_meta( $id,"_subscription_trial_length",true);

                $sale_price  = get_post_meta( $id,"_sale_price",true);
                $price       = get_post_meta( $id,"_subscription_price",true);
                $rerular = get_post_meta( $id,'_subscription_sign_up_fee',true);
               if($isTrial){
                   /* trial */
                   $calc = isset($this->_wpwoofeed_settings['feed_subscriptions']["trial"]) ? $this->_wpwoofeed_settings['feed_subscriptions']["trial"] : "fee";

                   switch ($calc) {
                       case  "price":
                           if( $sale_price || !$sale_price && $sale_price=="0" ) {
                               $prices->sale_price =  $sale_price;
                           }
                           $prices->regular_price  = $price;
                           break;

                       case "zerro":
                           $prices->regular_price  = "0.0";
                           $prices->sale_price     = null;
                           break;

                       case "feeplusprice":
                           $rerular = get_post_meta( $id,'_subscription_sign_up_fee',true);
                           if( $sale_price ||  $sale_price=="0" ) {
                               $prices->sale_price =  $sale_price*1.0 + ($rerular ? $rerular*1.0 : 0.0);
                           }
                           $prices->regular_price  = $price*1.0 + ($rerular ? $rerular*1.0 : 0.0);
                           break;

                       default :// "fee"
                           $prices->regular_price  = $rerular;
                           $prices->sale_price     = null;
                           break;
                   }
               }else{
                   /* fee */

                   $calc = isset($this->_wpwoofeed_settings['feed_subscriptions']["fee"]) ? $this->_wpwoofeed_settings['feed_subscriptions']["fee"] : "feeplusprice";
                   switch ($calc) {
                       case  "price":
                           if( $sale_price || !$sale_price && $sale_price=="0" ) {
                               $prices->sale_price =  $sale_price;
                           }
                           $prices->regular_price  = $price;
                           break;
                       case  "fee":
                           $prices->regular_price  = $rerular;
                           $prices->sale_price     = null;
                           break;
                       default:

                           if( $sale_price ||  $sale_price=="0" ) {
                               $prices->sale_price =  $sale_price*1.0 + ($rerular ? $rerular*1.0 : 0.0);
                           }
                           $prices->regular_price  = $price*1.0 + ($rerular ? $rerular*1.0 : 0.0);



                           break;
                   }

               }

            return $prices;
        }

        $regular_price  = $woocommerce_product->get_regular_price();
        if ( '' != $regular_price ) {
            $prices->regular_price = $regular_price;
        }

        // Grab the sale price of the base product.
        $sale_price                    = $woocommerce_product->get_sale_price();
        if ( $sale_price != '' ) {
            $prices->sale_price    = $sale_price ;
        }





        $this->_storedebug( 'GENERAL PRICE');
        $this->_storedebug($prices);
        $this->_storedebug("================================================");


        return $prices;
    }
    private function _wpwoofeed_calc_tax($price,$product,$isPriceConverted = false){
        if(!$price) $price=0.00;

        if(!$isPriceConverted){
            $price = $price *  $this->_store_info->currencyRate;
        }

        if(!isset($this->_wpwoofeed_settings['tax_rate_tmp_data']) || empty($this->_wpwoofeed_settings['tax_rate_tmp_data'])) {
            return $this->_price_format($price);
        }

        $cproduct = ( $product->has_child() && isset($this->_wpwoofeed_settings["product_tmp_data"]["taxproduct"]) ) ? $this->_wpwoofeed_settings["product_tmp_data"]["taxproduct"] :  $product;

        $BaseTax = 0.0;

        $tax_staus    = $this->_product_get_property('tax_status',$cproduct);
        $prodTaxClass = $this->_product_get_property('tax_class',$cproduct);


        $defCountry = get_option('woocommerce_default_country','');
        if($defCountry){
            $a = explode(":",$defCountry);
            $defCountry = $a[0];
        }
        $atax = WC_Tax::find_rates(
            array(
                'country' => $defCountry,
                'tax_class'=>$prodTaxClass
            )
        );

        $aTax = array(0=>$BaseTax);
        if(count($atax))
            foreach($atax as $tax) {
                if(isset($tax['rate']) ){
                    if( !empty($tax['compound']) &&  $tax['compound']=='yes'){
                        $aTax[0]+=$tax['rate']*1.0;
                        //$i++;                
                    }else{
                        $BaseTax+= $tax['rate']*1.0;
                        $aTax[0]=$BaseTax;
                    }
                }
            }

        $this->_wpwoofeed_settings['aBaseTax_tem_val']=$atax;

        $BaseTax = $this->_wpwoofeed_settings['aBaseTax_tem_val'];

        if( $tax_staus!="taxable" || get_option('woocommerce_calc_taxes',null) != 'yes' ) {
            $BaseTax=0.0;
            $aTax = array(0=>$BaseTax);
        }

        if($this->_wpwoofeed_settings['tax_rate_tmp_data']=='addbase')  {
            foreach($aTax as $t){
                $price+=$price*$t/100.0;
            }
            return $this->_price_format($price);
        }else if($this->_wpwoofeed_settings['tax_rate_tmp_data']=='whithout') {
            $i=count($aTax)-1;
            $oprice = $price;
            while($i>-1){
                $price=$price*1.0/(1.0 + $aTax[$i]*1.0/100.0);
                $i--;
            }
            return $this->_price_format($price);

        }else if(strpos($this->_wpwoofeed_settings['tax_rate_tmp_data'],'addtarget' )!==false){
            $sep = explode(":",$this->_wpwoofeed_settings['tax_rate_tmp_data']);
            if(isset($sep[1]) && !empty($sep[1]) ){
                $taxRate=0.0;
                $sValTMP=$sep[1];
                $cl = $prodTaxClass;
                $country = $sValTMP;
                if(strpos($sValTMP,"-")){
                    $sValTMP = explode("-",$sep[1]);
                    $this->_wpwoofeed_settings['atax_tem_val'] = $this->_woocommerce_wpwoof_common->getTaxRateCountries($sValTMP[1]);
                    if(count($this->_wpwoofeed_settings['atax_tem_val'])>0) $cl =$this->_wpwoofeed_settings['atax_tem_val'][0]['class'];
                    $country=$sValTMP[0];

                }

                if($country!='*') {
                    $this->_wpwoofeed_settings['atax_tem_val'] = WC_Tax::find_rates(
                        array("country"=>$country,
                            "tax_class"=>$prodTaxClass
                        ));
                }else{
                    $this->_wpwoofeed_settings['atax_tem_val'] = $this->_wpwoofeed_settings['aBaseTax_tem_val'];
                }

                $tax_rate=0.0;
                $aTax = array(0=>$tax_rate);
                if(count($this->_wpwoofeed_settings['atax_tem_val'])) {
                    $i=1;
                    foreach($this->_wpwoofeed_settings['atax_tem_val'] as $tax) {
                        if(isset($tax['rate'])) {
                            if( !empty($tax['compound']) &&  $tax['compound']=='yes'){
                                $aTax[$i]+=$tax['rate']*1.0;
                                // $i++;                                
                            }else{
                                $tax_rate+=$tax['rate']*1.0;
                                $aTax[0]=$tax_rate;
                            }
                        }
                    }
                }

                foreach($aTax as $t){
                    $price+=$price*$t/100.0;
                }
                return $this->_price_format($price);
            }
            return  $this->_price_format($price);
        }else if(strpos($this->_wpwoofeed_settings['tax_rate_tmp_data'],'target' )!==false){
            $i=count($aTax)-1;
            while($i>-1){
                $price=$price*1.0/(1.0 + $aTax[$i]*1.0/100.0);
                $i--;
            }
            $sep = explode(":",$this->_wpwoofeed_settings['tax_rate_tmp_data']);
            if(isset($sep[1]) && !empty($sep[1]) ){
                $sValTMP=$sep[1];
                $cl = $prodTaxClass;
                $country = $sValTMP;
                if(strpos($sValTMP,"-")){
                    $sValTMP = explode("-",$sep[1]);
                    $atax = $this->_woocommerce_wpwoof_common->getTaxRateCountries($sValTMP[1]);
                    if(count($atax)>0) $cl = $atax[0]['class'];
                    $country=$sValTMP[0];
                }
                if($country!='*') $atax = WC_Tax::find_rates(
                    array("country"=>$country,
                        "tax_class"=>$cl));
                $tax_rate=0.0;
                $aTax = array(0=>$tax_rate);
                if(count($atax)) {
                    $i=1;
                    foreach($atax  as $tax) {
                        if(isset($tax['rate'])) {
                            if( !empty($tax['compound']) &&  $tax['compound']=='yes'){
                                $aTax[$i]+=$tax['rate']*1.0;
                                //$i++;  
                            }else{
                                $tax_rate+=$tax['rate']*1.0;
                                $aTax[0]=$tax_rate;
                            }
                        }
                    }
                    foreach($aTax as $t){
                        $price+=$price*$t/100.0;
                    }
                    return $this->_price_format($price);
                }
            }
            return  $this->_price_format($price);
        }
        return  $this->_price_format($price);
    }
    private function _wpwoofeed_price($product, $item, $field, $id){
        $price = $this->_custom_user_function( $product, $item, $field, $id );
        if($price>0) return $price;
        $price = $this->_wpwoofeed_settings['product_tmp_data']['prices']->regular_price;
        $isConverted = isset($this->_wpwoofeed_settings['product_tmp_data']['prices']->regular_price_isConverted)?$this->_wpwoofeed_settings['product_tmp_data']['prices']->regular_price_isConverted:false;

        if( $product->is_type('subscription_variation')){
            $price =  $this->_generate_prices_for_product( $product );
            $price =  $price->regular_price;
        }

        return  $this->_wpwoofeed_calc_tax($price,$product,$isConverted);
    }
        
    
    // WooCommerce Dynamic Pricing & Discounts Plugin integration 
    private function check_sale_price_by_WCDPD($product) {
        //Check that all need classes loaded
        if (!class_exists('RP_WCDPD_Settings') || !class_exists('RP_WCDPD_Product_Pricing') || !class_exists('RP_WCDPD_Controller_Methods_Product_Pricing')) {
            return false;
        }

        if (RP_WCDPD_Settings::get('product_pricing_sale_price_handling') === 'exclude' && RP_WCDPD_Product_Pricing::product_is_on_sale($product)) {
            return false;
        }
        $price = false;
        $controller = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();
        $applicable_rules = RP_WCDPD_Product_Pricing::get_applicable_rules_for_product($product, array('simple'));
        if (is_array($applicable_rules) && !empty($applicable_rules)) {
            $price = $this->_wpwoofeed_settings['product_tmp_data']['prices']->regular_price;
            if (RP_WCDPD_Settings::get('product_pricing_sale_price_handling') !== 'regular' && !empty($this->_wpwoofeed_settings['product_tmp_data']['prices']->sale_price)) {
                $price = $this->_wpwoofeed_settings['product_tmp_data']['prices']->sale_price;
            }
            foreach ($applicable_rules as $applicable_rule) {

                // Load method from rule
                if ($method = $controller->get_method_from_rule($applicable_rule)) {

                    // Generate prices array
                    $prices = RightPress_Product_Price_Breakdown::generate_prices_array($price, 1, $product);
                    // Apply adjustments to prices array
                    $prices = $method->apply_adjustment_to_prices($prices, array('rule' => $applicable_rule));
//                    $this->_storedebug("RightPress prices");
//                    $this->_storedebug($prices);
                    // Get price from prices array
                    $price = RightPress_Product_Price_Breakdown::get_price_from_prices_array($prices, $price, $product, null, true);
                }
            }
            if ($price==$this->_wpwoofeed_settings['product_tmp_data']['prices']->regular_price)                return false;
            $this->_storedebug("price_WCDPD ".$price);
        }

        return $price;
    }

    private function _wpwoofeed_sale_price($product, $item, $field, $id){
        $price_WCDPD = $this->check_sale_price_by_WCDPD($product);
        $prices = $this->_wpwoofeed_settings['product_tmp_data']['prices'];
        $prices->sale_price = trim($prices->sale_price);
        $isConverted = isset($this->_wpwoofeed_settings['product_tmp_data']['prices']->sale_price_isConverted)?$this->_wpwoofeed_settings['product_tmp_data']['prices']->sale_price_isConverted:false;
        if(empty($price_WCDPD) && ($prices->sale_price=="" || $prices->sale_price==$prices->regular_price )) return '';
        return  $this->_wpwoofeed_calc_tax($price_WCDPD>0?$price_WCDPD:$prices->sale_price,$product,$isConverted);
    }
    private function _price_format($price){

        return number_format(  $price  ,
                (int)$this->_store_info->woocommerce_price_num_decimals,
                ".",     // $this->_store_info->woocommerce_price_decimal_sep  #Bug #2738
                ""//","  // $this->_store_info->woocommerce_price_thousand_sep   #Bug #2738
                ).  ' ' . $this->_store_info->currency;
        }
    private function _get_ExtraData($product, $item, $field, $id){
        //check product meta
        $result = $this->_getMeda('wpfoof_'.$id,$product);
        if(!empty($result)) return $result;
        //check global data
        $result = $this->_custom_user_function($product, $item, $field, $id);
        return  $result;
    }
    private function _id_format($product, $item, $field, $id){
        $result = $this->_custom_user_function($product, $item, $field, $id);
        if (empty($result))  return $result;
        if ( !empty( $this->_aCurrentlyFields['id_prefix'] ) ) {
            $result = $this->_aCurrentlyFields['id_prefix'] . $result;
        }
        if ( !empty ( $this->_aCurrentlyFields['id_postfix'] ) ) {
            $result = $result . $this->_aCurrentlyFields['id_postfix'];
        }
        return  $result;
    }
    private function _wpwoofeed_identifier_exists($product, $item, $field, $id){

        // check by priority

        //1.product true:output
        $result = $this->_getMeda('wpfoof_identifier_exists', $product);
        if (!empty($result) && $result != "true") {
            $this->_storedebug("identifier_exists: product level");
            if ($result == "output") {$result = "no";}
            return $result;
        }

        //2.check category
        $result = $this->_getCategoryMeta($product,'wpfoof-identifier_exists');
        if(!empty($result) && $result != "true") {
            $this->_storedebug("identifier_exists: category level");
            if ($result == "output") {$result = "no";}
            return $result;
        }
        
        //3.feed
        if(isset($this->_wpwoofeed_settings['field_mapping']['identifier_exists'])) {
            $result = !empty($this->_wpwoofeed_settings['field_mapping']['identifier_exists']['value']) ? $this->_wpwoofeed_settings['field_mapping']['identifier_exists']['value'] : "";
            if (!empty($result) && $result != "true") {
                    $this->_storedebug("identifier_exists: feed level");
                if ($result == "output") {$result = "no";}
                return $result;
            }
        }
        //4.global
        $this->_storedebug("identifier_exists: global level");
        $result = $this->_custom_user_function($product, $item, $field, $id);
        return $result=='true'?'':$result;

    }
    function storeTicket($id,$buf,$ttDir=""){
        $this->_ticketDir = !empty($ttDir) ? $ttDir : $this->_ticketDir;
        if(empty($this->_ticketDir)) {
            throw new Exception("Tmp DIR is empty.");
            exit;
        }
        if (!file_exists($this->_ticketDir)) {
            if( mkdir($this->_ticketDir,0755,true) )   return $this->storeTicket($id,$buf);
            else throw new Exception("Can not create '".$this->_ticketDir."' folder.");
        } else if( file_exists($this->_ticketDir."/csvheader.item")) {
            $this->_aCSVcolumns = json_decode(file_get_contents($this->_ticketDir."/csvheader.item"),true);
        }
        if(!empty($id)) {
            $fileName = $this->_ticketDir."/".$id.".item";
            $a = file_put_contents($fileName,$buf);
            return ($a!==false);
        }
        return false;
    }
    function compileXMLItem( $aItem ){
        /* XML Item Builder */
        $item = "";
        foreach($this->_aCSVcolumns as $element){
            if( isset( $this->_field_rules[$element] ) ) {
                $length = false;
                $value_type = false;
                if (isset($this->_field_rules[$element]['length']) && $this->_field_rules[$element]['length'] != false) {
                    $length = $this->_field_rules[$element]['length'];
                }
                if (isset($this->_field_rules[$element]['value_type'])) {
                    $value_type = $this->_field_rules[$element]['value_type'];
                }
                if (isset($this->_field_rules[$element]) && isset($this->_field_rules[$element]['xml'])) {
                     if ($element == "expand_more_images" ) {
                        if( !empty($aItem[$element]) ) {
                            $aImages = explode(",", $aItem[$element]);
                            $i = 0;
                            foreach ($aImages as $img) {
                                $i++;
                                $tagval = $this->_terminate_text($img, !empty($this->_field_rules[$element]['CDATA']) ? true : false);
                                if (!empty($tagval)) $item .= "        <" . $this->_field_rules[$element]['xml'] . ">" . $tagval . "</" . $this->_field_rules[$element]['xml'] . ">" . "\n";
                            }
                        }
                    } else {
						if ($value_type == 'int') {
							$tagvalue = (isset($aItem[$element])) ? $aItem[$element] : 0;
							$item .= "        <" . $this->_field_rules[$element]['xml'] . ">" . $tagvalue . "</" . $this->_field_rules[$element]['xml'] . ">" . "\n";
						} else {
							$cdata = !empty($this->_field_rules[$element]['CDATA']) ? true : false;
							$tagvalue = (isset($aItem[$element])) ? $this->_terminate_text($this->_enforce_length($aItem[$element], $length, true), $cdata) : "";
							//if (!$cdata && is_string($tagvalue)) $tagvalue = htmlspecialchars($tagvalue); #3056 
							if (!empty($tagvalue) && is_string($tagvalue)) $item .= "        <" . $this->_field_rules[$element]['xml'] . ">" . $tagvalue . "</" . $this->_field_rules[$element]['xml'] . ">" . "\n";
						}
                    }
                }
            }//if( isset($this->_field_rules[$element]) {
        }//foreach($this->_aCSVcolumns as $field)
        if( !empty($item) ){
           return "    <item>"  . "\n" . $item . "    </item>" . "\n";
        }
        return "";
    }
    function compileCSVItem( $aItem, $aFields ){
        /* CSV Item Builder */
        $item = array();
        foreach($aFields as $element){
            if( isset( $this->_field_rules[$element] ) ) {
                $length = false;
                if (isset($this->_field_rules[$element]['length']) && $this->_field_rules[$element]['length'] != false) {
                    $length = $this->_field_rules[$element]['length'];
                }
                if (isset($this->_field_rules[$element]) && isset($this->_field_rules[$element]['csv'])) {
                    $tagvalue="";
                    $tagvalue = empty($aItem[$element]) ? "" : ( is_string($aItem[$element]) ? $this->_enforce_length(strip_tags($aItem[$element]) , $length, true) : $aItem[$element] );

                    $item[$this->_field_rules[$element]['csv']] = $tagvalue;
                }
            }//if( isset($this->_field_rules[$element]) {
        }//foreach($this->_aCSVcolumns as $field)
       return $item;
    }
    function compileFeed($filepath){
        if(!empty($this->_ticketDir)){
            if( file_exists ( $this->_ticketDir."/csvheader.item" ) ) {
                $this->_aCSVcolumns = json_decode(file_get_contents($this->_ticketDir."/csvheader.item"),true);
            }
            $aCSVHeader    = $this->_aCSVcolumns;
            $filep         = false;

            $upload_dir    = wpwoof_feed_dir($this->_wpwoofeed_settings['feed_name'], 'xml' );
            $filepathXML   = $upload_dir['path'];
            $upload_dir    = wpwoof_feed_dir($this->_wpwoofeed_settings['feed_name'], 'csv' );
            $filepathCSV   = $upload_dir['path'];

            $header = array();
            $delimiter = "\t";
            $enclosure = '"';

            if( file_exists ( $this->_ticketDir."/header.item" ) ) {

                file_put_contents($filepathXML, file_get_contents($this->_ticketDir."/header.item"));



                foreach( $aCSVHeader as $i => $elm ){
                       if($this->_store_info->feed_type == "adsensecustom" &&  in_array($aCSVHeader[$i],array("item_group_id","tax","tax_countries","mpn","gtin") ) ) {
                             unset($aCSVHeader[$i]);
                       }else if (isset($this->_field_rules[$elm]) && isset($this->_field_rules[$elm]['csv'])) {
                           $header[]=$this->_field_rules[$elm]['csv'];
                       }
                }

                $csvDIR = dirname($filepathCSV);
                if(!is_dir($csvDIR)){
                    //Directory does not exist, so lets create it.
                    mkdir($csvDIR, 0755);
                }
                if(file_exists($filepathCSV)) unlink($filepathCSV);
                $filep = fopen($filepathCSV,"a+");
                if($this->_store_info->feed_type == "facebook") fwrite($filep, "# ref_application_id 451257172939091\n");
                fputcsv( $filep, $header, $delimiter, $enclosure );
                @unlink($this->_ticketDir."/header.item" );
                fclose($filep);
            }
            //trace($aCSVHeader,1);

            $aItems = scandir($this->_ticketDir);
            $filep = fopen($filepathCSV,"a+");
            foreach ($aItems as $i){
                if (!in_array($i, array(".","..","footer.item","csvheader.item") ) && strpos($i,".item")){
                    $aItem = json_decode(file_get_contents($this->_ticketDir."/".$i),true);

                    $item = $this->compileXMLItem($aItem);
                    if($item) { file_put_contents($filepathXML, $item,FILE_APPEND); }

                    $item =  $this->compileCSVItem($aItem,$aCSVHeader);
                    if( count($item) ) {
                        $this->_storedebug("================================================");
                        $this->_storedebug( 'fputcsv: filep');
                        $this->_storedebug( $filep);
                        $this->_storedebug( 'fputcsv: item');
                        $this->_storedebug( $item);
                        $this->_storedebug( 'fputcsv: delimiter');
                        $this->_storedebug( $delimiter);
                        $this->_storedebug( 'fputcsv: enclosure');
                        $this->_storedebug( $enclosure);
                        $this->_storedebug("================================================");
                        fputcsv($filep, $item, $delimiter, $enclosure);
                    }

                    @unlink($this->_ticketDir."/".$i);
                }

            }// foreach ($aItems as $i)

            if( file_exists ( $this->_ticketDir."/footer.item" ) ) {
                file_put_contents($filepathXML, file_get_contents($this->_ticketDir."/footer.item"),FILE_APPEND);
                 @unlink($this->_ticketDir."/footer.item");
                 @unlink($this->_ticketDir."/csvheader.item");
            }
            @rmdir($this->_ticketDir);
            fclose($filep);
            return true;
        }else{
            throw new Exception("Can not found the feed temp  dir.");
            exit;
        }



        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////



    }
    function checkAndAddCsvColumn( $aFields ) {
        $isAdded = false;
        if(!is_array($this->_aCSVcolumns )) $this->_aCSVcolumns = array();
        if( count( $aFields ) ) foreach( $aFields as $fld ){
            if( !in_array( $fld, $this->_aCSVcolumns ) && !empty($fld) ){
                array_push($this->_aCSVcolumns , $fld);
                $isAdded = true;
            }
        }
        if( $isAdded ) $this->storeTicket("csvheader", json_encode($this->_aCSVcolumns));
    }
    function generate(){
            // check if feed created in pro version
            if ($this->_woocommerce_wpwoof_common->isPro($this->_wpwoofeed_settings['edit_feed'])) {
                $this->_woocommerce_wpwoof_common->delete_feed_status($this->_wpwoofeed_settings['edit_feed']);
                wp_clear_scheduled_hook( 'wpwoof_generate_feed',  array( (int)$this->_wpwoofeed_settings['edit_feed'] ));
                return;
            }
            /* Get Global Feed Fields Mapping Data from feed list page */
            $glData = $this->_woocommerce_wpwoof_common->getAllGlobals();

            /* checking current status **/
            //trace($this->_wpwoofeed_settings,1);

            $feedStatus = $this->_woocommerce_wpwoof_common->get_feed_status($this->_wpwoofeed_settings['edit_feed']);
            if( empty( $feedStatus["type"] ) ) $feedStatus["type"] = $this->_wpwoofeed_type ;

            /* set path for feed and log file's */
            $upload_dir = wpwoof_feed_dir($this->_wpwoofeed_settings['feed_name'], $this->_wpwoofeed_type );
            $filepath   = $upload_dir['path'];

            $ticketPath = str_replace("/".$this->_wpwoofeed_type ,"",$upload_dir['pathtofile']) ;

            /* create folder for tickets */
            $this->storeTicket('','',$ticketPath."/".$this->_wpwoofeed_settings['edit_feed']);

            $this->_wpwoofeed_wpml_debug = $ticketPath . "/" . $this->_wpwoofeed_settings['edit_feed'].".log";

            if(WPWOOF_DEBUG) {
                echo "LOG FILE:".$this->_wpwoofeed_wpml_debug."\n";
                if (empty($feedStatus['products_left'])) {
                    @unlink($this->_wpwoofeed_wpml_debug);
                }

            }
            /* end set path for feed and log file's */
            /* set cron task if generator stoped by server timeout */
            
            wp_schedule_single_event( time() + 60, 'wpwoof_generate_feed', array( (int)$this->_wpwoofeed_settings['edit_feed'] ) );
            if ($feedStatus['parsed_products']<=0) {
                foreach ($this->_woocommerce_wpwoof_common->getScheduledFeeds() as $fid) {
                    if ($fid == $this->_wpwoofeed_settings['edit_feed']) continue;
                    $tmpfeedStatus = $this->_woocommerce_wpwoof_common->get_feed_status($fid);
                    if ($tmpfeedStatus['parsed_products']>0) {
                        $this->_storedebug( 'FEED '.$fid.' IN PROGRESS:');
                        return true;
                    }
                }
            }

            if ( time() - $feedStatus['time'] < 60 ) {
                $this->_storedebug( 'FEED IN PROGRESS:');
                return true;
            }

            $this->_storedebug("START GENERATE:");
            $this->_storedebug("feedStatus:");
            $this->_storedebug($feedStatus);
            $this->_storedebug("Global Data:");
            $this->_storedebug( $glData);


            /* added maping fields from global data */

            $this->_aCurrentlyFields = $this->_wpwoofeed_settings['field_mapping'];

            $tmp_feed_type = $this->_wpwoofeed_settings['feed_type'] == 'facebook' ? "google" : $this->_wpwoofeed_settings['feed_type'];


            if( !empty( $glData['data'] ) && isset( $glData['data']['enable_'.$tmp_feed_type] ) && isset( $glData['data'][ $tmp_feed_type ] )   ){
                $this->_aCurrentlyFields = array_merge( $this->_aCurrentlyFields, $glData['data'][ $tmp_feed_type ] );
            }

            /* need add all automapfields if not exists */

           // $this->_storedebug("FIELDS:");
            $this->_field_rules = $this->_woocommerce_wpwoof_common->product_fields; /*wpwoof_get_product_fields();*/

            foreach($this->_field_rules as $fld => $val){

                if(  is_array($val['feed_type']) && in_array($this->_wpwoofeed_settings['feed_type'], $val['feed_type'])
                    &&  isset($val['woocommerce_default']['automap']) &&  isset( $val['woocommerce_default']['value'] )
                ){
                   //$this->_wpwoofeed_settings['field_mapping'][$fld] = array("value" => $val['woocommerce_default']['value']);
                    $this->_aCurrentlyFields[$fld] = array("value" => $val['woocommerce_default']['value']);
                }
            }

            /* set global img and taxonomy */
            $this->_wpwoofeed_settings['brand']           = isset($glData['brand']) ? $glData['brand'] : '';
            $this->_wpwoofeed_settings['global_img']      = isset($glData['img']) ? $glData['img'] : '';
            $this->_wpwoofeed_settings['global_taxonomy'] = isset($glData['google']) ? $glData['google'] : '';
            if (isset($glData['data']['extra']) && count($glData['data']['extra'])) {
                $this->_wpwoofeed_settings['extra'] = array();
                foreach ($glData['data']['extra'] as $key => $value) {
                    if (isset($value['custom_tag_name']) && !isset($value['feed_type'][$this->_wpwoofeed_settings['feed_type']])) continue;
                    if (!isset($value['custom_tag_name']) && (!isset($this->_woocommerce_wpwoof_common->product_fields[$key]) || !in_array($this->_wpwoofeed_settings['feed_type'], $this->_woocommerce_wpwoof_common->product_fields[$key]['feed_type']))) continue;
                    $nkey = strpos($key, "custom-extra-field-") === 0? $value['custom_tag_name'] : $key;
                    $this->_wpwoofeed_settings['extra'][$nkey] = $value;
                    $this->_field_rules[$nkey]['xml'] = 'g:'.$nkey;
                    $this->_field_rules[$nkey]['csv'] = $nkey;
                }
            }

            /* erase global data */
            unset($glData);

            $this->_storedebug("Feed Data:");
            $this->_storedebug( $this->_wpwoofeed_settings );
            $this->_storedebug("\n----------------------------------------------------------------------------------------------\n" );

            try {

                if (!empty($this->_wpwoofeed_settings['edit_feed'])) {
                    $this->_wpwoofeed_settings['status_feed'] = 'starting';
                    wpwoof_update_feed( $this->_wpwoofeed_settings, $this->_wpwoofeed_settings['edit_feed'],true );
                }


                $general_lang = null;
                $currencyRate=1.0;
                $current_currency =  get_woocommerce_currency();

               
                $this->_wpwoofeed_settings = array_merge(array(
                    'feed_variable_price' => 'small',
                ), $this->_wpwoofeed_settings);

                $remove_variations = !empty($this->_wpwoofeed_settings['feed_remove_variations']);

                $feedStatus['file'] = $filepath;
                $filePresent        = file_exists( $filepath ) ? true : false;

                $fields = array();

                /* Filter fields only for feed type */
                $this->_storedebug("\n------------------------------------FIELDS-----------------------------------------------------\n");
                $this->_storedebug( $this->_aCurrentlyFields );

                if ( is_array($this->_aCurrentlyFields) && count( $this->_aCurrentlyFields ) ) {
                    foreach ( $this->_aCurrentlyFields as $fld => $val) {
                        if ( $fld!='image-size'
                             && isset($this->_field_rules[$fld])
                             && isset($this->_field_rules[$fld]['feed_type'])
                             && is_array($this->_field_rules[$fld])
                             && in_array( $this->_wpwoofeed_settings['feed_type'], $this->_field_rules[$fld]['feed_type'] ) ) {
                            $fields[$fld] = $val;
                        }
                    }
                }

                if (isset($this->_wpwoofeed_settings['extra'])) {
                    foreach ($this->_wpwoofeed_settings['extra'] as $key => $value) {
                        if (!isset($fields[$key])){
                                if(isset($value['feed_type'])) {
                                    if (isset($value['feed_type'][$this->_wpwoofeed_settings['feed_type']]) && $value['feed_type'][$this->_wpwoofeed_settings['feed_type']]!='on') {
                                        continue;
                                    }
                                }
                        }
                        $fields[$key] = $value;
                    }
                }
                $this->_storedebug("\n------------------------------------COMPILED FIELDS-----------------------------------------------------\n");
                $this->_storedebug($fields);
                $this->_storedebug("\n------------------------------------END FIELDS----------------------------------------------------------\n");



                $this->_store_info                     = new stdClass();
                $this->_store_info->feed_type          = (!empty($this->_wpwoofeed_settings['feed_type'])) ? $this->_wpwoofeed_settings['feed_type'] : 'all'; /*'facebook'*/
                $this->_store_info->site_url           = home_url('/');
                $this->_store_info->feed_url_base      = home_url('/');
                $this->_store_info->blog_name          = get_option('blogname');
                $this->_store_info->charset            = get_option('blog_charset');
                $this->_store_info->currency           = $current_currency;

                $this->_store_info->default_currency   = get_woocommerce_currency();
                $this->_store_info->currencyRate       = $currencyRate;
                $this->_store_info->weight_units       = get_option('woocommerce_weight_unit');
                $this->_store_info->base_country       = $this->_woocommerce->countries->get_base_country();
                $this->_store_info->taxes              = $this->_wpdb->get_results( "SELECT tax_rate_country as shcode, `tax_rate_class` as `class`, `tax_rate_id` as `id`,`tax_rate` as `rate`, `tax_rate_name` as `name` FROM {$this->_wpdb->prefix}woocommerce_tax_rates  Order By tax_rate_class, tax_rate_country ",ARRAY_A );
                $this->_store_info                     = apply_filters('wpwoof_store_info', $this->_store_info);
                $this->_store_info->item_group_id      =  null;

                /* GET PRICE FORMAT DATA FROM WC CONFIG */
                $this->_store_info->woocommerce_price_decimal_sep        =  get_option('woocommerce_price_decimal_sep',',');
                $this->_store_info->woocommerce_price_display_suffix     =  get_option('woocommerce_price_display_suffix','');
                $this->_store_info->woocommerce_price_num_decimals       =  get_option('woocommerce_price_num_decimals','2');
                $this->_store_info->woocommerce_price_thousand_sep       =  get_option('woocommerce_price_thousand_sep','.');
                $this->_store_info->woocommerce_price_round              = "disabled";//"down"
                $this->_store_info->woocommerce_price_rounding_increment = 1;

                $this->_store_info->woocommerce_manage_stock = get_option('woocommerce_manage_stock',false);

                $this->_storedebug("SETS PRICE ROUND FOR:".$this->_store_info->currency);


                $this->_storedebug('\n----------------------------------TAX ---------------------------------\n');
                $this->_storedebug( $this->_store_info->taxes);

                if ( isset( $this->_wpwoofeed_settings['feed_use_lang'] ) ) {
                    $this->_store_info->feed_language = $this->_wpwoofeed_settings['feed_use_lang'];
                }

                $this->_store_info->feed_url = $this->_store_info->feed_url_base;
                $this->_store_info->US_feed = (!empty($this->_store_info->base_country) && substr('US' == $this->_store_info->base_country, 0, 2)) ? true : false;

                if($feedStatus['products_left'] && count($feedStatus['products_left'])>0 ){
                    $args = array(
                        'post__in' => $feedStatus['products_left']
                    );
                }


                $args['post_type']      = 'product';
                $args['post_status']    = 'publish';
                $args['fields']         = 'ids';
                $args['order']          = 'DESC';
                $args['orderby']        = 'ID';
                $args['posts_per_page'] = -1;//110;


                    $header  = "<?xml version=\"1.0\" encoding=\"" . $this->_store_info->charset . "\" ?>\n";
                    $header .= "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n";
                    $header .= "  <channel>\n";
                    $header .= "    <title><![CDATA[" . $this->_store_info->blog_name . " Products]]></title>\n";
                    $header .= "    <link><![CDATA[" .  $this->_store_info->site_url . "]]></link>\n";
                    $header .= "    <description>WooCommerce Product List RSS feed</description>\n";
                    if ($this->_wpwoofeed_settings['feed_type'] == 'facebook') $header .= "    <metadata><ref_application_id>451257172939091</ref_application_id></metadata>\n";
                    $this->storeTicket("header",$header);
                    $this->storeTicket("footer","  </channel>\n</rss>");
                    unset($header);

                $this->_storedebug('\n----------------------------------  SQL   ---------------------------------\n');
                $this->_storedebug( "WP_QUERY: args:".print_r($args,true));

                $products = new WP_Query($args);
                unset($args);

                $this->_storedebug("SQL:".$products->request);

                if(empty($feedStatus['total_products'])) {
                    $feedStatus['total_products']=$products->post_count;
                }
                $this->_storedebug("WP_QUERY: products:".print_r($products,true));
                $this->_storedebug('\n----------------------------------  END SQL   ---------------------------------\n');

                /* before render fields need check tax value include tax or not to price*/
                $this->_wpwoofeed_settings["tax_rate_tmp_data"] = '';


                if (isset($fields['tax']['value'])) {
                    $prices_include_tax = get_option("woocommerce_prices_include_tax");
                    $tax_based_on       = get_option("woocommerce_tax_based_on");

                    switch ($fields['tax']['value']) {
                        case "false" : /* Exclude */
                            if ($prices_include_tax == "yes") {
                                $this->_wpwoofeed_settings["tax_rate_tmp_data"] = "whithout";
                            }
                            break;
                        case 'true':/* Include */
                            if ($prices_include_tax == "yes") {
                                if ($tax_based_on == 'shipping' || $tax_based_on == 'billing') {
                                    //то сначала отнимаем от цены величину налога BaseLocation, а затем прибавляем к базовой цене величину налога TargetCountry - и выводим в фид.
                                    $this->_wpwoofeed_settings["tax_rate_tmp_data"] = "target:" . $fields['tax_countries']['value'];
                                } else { /* base */
                                    //Еcли установлено "Shop base address" - то ничего не добавляем,
                                    // берем цену как есть и выводим в фид (потому что цена
                                    // уже введена с учетом локального налоша).

                                }
                            } else {
                                if ($tax_based_on == 'shipping' || $tax_based_on == 'billing') {
                                    //Если установлено "Customer shipping/billing address"
                                    //- то прибавляем к цене величину налога TargetCountry - и выводим в фид.
                                    $this->_wpwoofeed_settings["tax_rate_tmp_data"] = "addtarget:" . $fields['tax_countries']['value'];
                                } else {
                                    // Ели установлено "Shop base address" - то прибавляем к цене
                                    // величину налога BaseLocation (вчера вроде видел в твоем коде
                                    // $this->_store_info->base_country)
                                    // - и выводим в фид. Т.е. настройки вукоммерса не оверрайдим,
                                    // если нужно выводить с налогом - то выводим с тем налогом,
                                    // который настроен в вукоммерсе.
                                    $this->_wpwoofeed_settings["tax_rate_tmp_data"] = "addbase";
                                    /** Если налога нет в цене */
                                    if(get_option("woocommerce_prices_include_tax")=="no" ) {
                                        $this->_wpwoofeed_settings["tax_rate_tmp_data"] = "addtarget:" . $fields['tax_countries']['value'];
                                    }


                                }
                            }
                            break;
                    }
                }



                $feedStatus['products_left'] = $products->posts;
                $this->_storedebug("-------------------------------- feedStatus['products_left'] --------------------------------- ");
                $this->_storedebug($feedStatus['products_left']);

                $products = ( isset($products->posts) && is_array($products->posts) ) ? $products->posts : $products->get_posts();
                $this->_storedebug("POSTS:".print_r($products,true) );

                //$aExistsProds = isset($feedStatus["parsed_product_ids"]) ? $feedStatus["parsed_product_ids"] : array();


                foreach ($products as  $post) {
                    $data = "";
                    $fields['item_group_id']['define'] = null;
                    $product = $this->_load_product($post);
                    $this->_loadMetaExtraData($product);


                    $this->_aTags = wp_get_post_terms( $post, 'product_tag' );


                    $this->_storedebug("PRODUCT:".$this->_get_id($product));
                    if(file_exists($this->_ticketDir."/".$this->_get_id($product).".item")){
                        continue;
                    }

                    $this->_wpwoofeed_settings["product_tmp_data"] = array("product"=>$product);
                    $this->_storedebug("START INVENTORY:");
                    if($this->_store_info->woocommerce_manage_stock) {
                        $this->_wpwoofeed_settings["product_tmp_data"]["_stock_main"]      = (int)$product->get_stock_quantity(); /* for inventory field */
                        $this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"]     = $product->get_manage_stock(); /* for inventory field */
						$this->_wpwoofeed_settings["product_tmp_data"]["_stock_childs"]    = 0; /* for inventory field */


                        $this->_storedebug("_stock_main:".$this->_wpwoofeed_settings["product_tmp_data"]["_stock_main"] );
                        $this->_storedebug("manage_stock:".$this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"] );
                        $this->_storedebug("_stock_childs:".$this->_wpwoofeed_settings["product_tmp_data"]["_stock_childs"]);


                    }

                    $this->_storedebug("FINISH INVENTORY:");

                    //$this->_store_info->woocommerce_manage_stock
                    //_manage_stock
                    //_stock

                    $prices =  $this->_generate_prices_for_product( $product );

                    //for SVI gallery plugin
                    $prod_images = method_exists($product,'get_gallery_image_ids') ? $product->get_gallery_image_ids() : null;

                    $this->_wpwoofeed_settings["prod_images"] = null;
                    if($prod_images && count($prod_images)>0){
                        foreach($prod_images as $key=>$val){
                            $woosvi_slug = get_post_meta($val, 'woosvi_slug', true);
                            if($woosvi_slug){
                                $prod_images[$key] = array($val,$woosvi_slug);
                            }else{
                                unset($prod_images[$key]);
                            }
                        }
                        if(count($prod_images)>0){
                            $this->_wpwoofeed_settings["prod_images"] = $prod_images;
                        }

                    }



                    $this->_storedebug("-------------------------------- PRODUCT ".  $post."PRICE ----------------------------");
                    $this->_storedebug( $prices );
                    if ( !empty($sale_price) ) {
                        $prices->sale_price = $sale_price;
                    }
                    $parent_prices = $prices;


                    //if ($product->has_child() && !$remove_variations) {
                        $fields['item_group_id']['define'] = $this->_get_id($product);
                    //}



                    $this->_store_info->item_group_id = $fields['item_group_id']['define'];

                    if($this->_store_info->feed_type == "adsensecustom" ) unset($fields['item_group_id']['define']);
                    $this->_storedebug("----------------------- PRICES START: ".print_r($parent_prices,true));
                    $children_count  = 0;
                    $children_output = 0;
                    if ($product->has_child()) {
                        $this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"] = "";
                        $is_parent = true;
                        $children = $product->get_children();
                        $children_count = count( $children );
                        $children_output = 0;

                        $this->_storedebug("PRODUCT:".$this->_get_id($product)." HAS CHILDREN");
                        $this->_storedebug($children);
                        $my_counter = 0;
                        $first_child = true;
                        $aChildPrices = array();
                        $this->_wpwoofeed_settings["product_tmp_data"]["taxproduct"]  = $product;

                        foreach ($children as $child) {
                            $child = $this->_get_child($product,$child);
                            if (!$child) continue;
                            
                            $this->_loadMetaExtraData($child);
                            
                            $_var = get_post_meta( $child->get_id(), 'wpfoof-exclude-product', true );
                            if( !empty($_var)) continue;

                            if($this->_store_info->woocommerce_manage_stock) {
                                if (empty($this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"]) && $child->get_manage_stock()) {
                                    $this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"] = $child->get_manage_stock();
                                }
                                if ($this->_store_info->woocommerce_manage_stock &&  $this->_wpwoofeed_settings["product_tmp_data"]["manage_stock"] ) {
                                     $this->_wpwoofeed_settings["product_tmp_data"]["_stock_childs"] += $child->is_in_stock() ? $child->get_stock_quantity() : 0; /* for inventory field */
                                }
                            }

                            /* block for parent product price */
                            $this->_storedebug("===============================================");
                            $this->_storedebug("CHILD ID:" . $this->_get_id($child) . "|" . $child->get_id());
                            $this->_storedebug("CHILD ID:" . $this->_get_id($child));

                            $children_output++;
                            $child_prices = $this->_generate_prices_for_product($child);
                            $aChildPrices[$this->_get_id($child)] = $child_prices;
                            $this->_storedebug("CHILD PRICES:" . print_r($child_prices, true));

                            if ($first_child && $child_prices->regular_price > 0) {
                                $first_child_prices = $child_prices;
                                $first_child = false;
                            }
                            if ( (!$prices->regular_price || $is_parent)
                                && ($child_prices->regular_price > 0)) {
                                $prices = $child_prices;
                                $parent_prices = $prices;
                                $this->_wpwoofeed_settings["product_tmp_data"]["taxproduct"]  = $child;
                                $is_parent     = false;
                                $this->_storedebug("567:CHILD PARENT PRICES:" . print_r($parent_prices, true));

                            } else {
                                if ($this->_wpwoofeed_settings['feed_variable_price'] == 'big') {
                                    if (($child_prices->regular_price > 0)
                                        && ( empty($prices->regular_price)
                                            ||  $child_prices->regular_price > $prices->regular_price
                                            || $is_parent
                                        )){
                                        $is_parent     = false;
                                        $prices = $child_prices;
                                        $this->_wpwoofeed_settings["product_tmp_data"]["taxproduct"]  = $child;
                                        $parent_prices = $prices;
                                        $this->_storedebug("CHILD".$child->get_id()." PRICES BIG for(ID:".$product->get_id()."):" . print_r($child_prices, true));

                                    }
                                } else if ($this->_wpwoofeed_settings['feed_variable_price'] == 'first') {
                                    $is_parent     = false;
                                    $prices = $first_child_prices;
                                    $this->_wpwoofeed_settings["product_tmp_data"]["taxproduct"]  = $child;
                                    $parent_prices = $prices;
                                    $this->_storedebug("CHILD".$child->get_id()." PRICES first for(ID:".$product->get_id()."):" . print_r($parent_prices, true));

                                } else if ( ($child_prices->regular_price > 0) && ($child_prices->regular_price < $prices->regular_price || $is_parent)) {
                                    $is_parent     = false;
                                    $prices = $child_prices;
                                    $this->_wpwoofeed_settings["product_tmp_data"]["taxproduct"]  = $child;
                                    $parent_prices = $prices;
                                    $this->_storedebug("CHILD".$child->get_id()." PRICES small for(ID:".$product->get_id()."):" . print_r($parent_prices, true));
                                }
                            }

                            /* end block for parent product price */
                        }

                        if (!$remove_variations && $children_output>0 && $children_count>0 ) {
                            foreach ($children as $child) {
                                $child = $this->_get_child($product,$child);
                                if (!$child) continue;
                                if(file_exists($this->_ticketDir."/".$this->_get_id( $child ).".item")){
                                    continue;
                                }
                                $_var = get_post_meta( $this->_get_id( $child ), 'wpfoof-exclude-product', true );
                                if( !empty($_var)) {
                                    //$aExistsProds[] =$this->_get_id($child);
                                    continue;
                                }
                                $my_counter++;
                                $child_prices = $aChildPrices[$this->_get_id($child)];
                                $this->_wpwoofeed_settings["product_tmp_data"]['prices'] = (empty($child_prices->regular_price)) ? $parent_prices : $child_prices;

                                $data = $this->_build_item($fields, $child);



                                //array_push($feedStatus["parsed_product_ids"], $this->_get_id($child));
                                $feedStatus['parsed_products']++;

                                if (($key = array_search($this->_get_id($child), $feedStatus['products_left'])) !== false) {
                                    unset($feedStatus['products_left'][$key]);
                                }
                               // $aExistsProds[] =$this->_get_id($child);
                                $this->_woocommerce_wpwoof_common->upadte_feed_status($this->_wpwoofeed_settings['edit_feed'],$feedStatus,1);

                            }
                        }

                    } else {
                        if ($product->is_type('bundle')) {
                            $parent_prices->sale_price = $product->get_bundle_price();
                            $parent_prices->regular_price = $product->get_bundle_regular_price();
                            $this->_wpwoofeed_settings["product_tmp_data"]['poduct'] = $product;
                        }

                    }
                    $this->_storedebug("PRODUCT:".$this->_get_id($product));
                    $this->_storedebug("PRICES FINISH:".print_r($parent_prices,true));


                    $this->_wpwoofeed_settings["product_tmp_data"]['prices']  = $parent_prices;
                    if (
                        ($children_count>0 && $children_output>0 || $children_count==0 )
                        && (
                            !$product->is_type('bundle') && !$product->is_type('grouped') && !$product->is_type('variable')
                            ||
                            $product->is_type('bundle') && (!empty($this->_wpwoofeed_settings['feed_bundle_show_main']) || !isset($this->wpwoofeed_settings['feed_bundle_show_main']))
                            ||
                            $product->is_type('grouped') && (!empty($this->_wpwoofeed_settings['feed_group_show_main']) || !isset($this->_wpwoofeed_settings['feed_group_show_main']))
                            ||
                            ( $product->is_type('variable') || $product->is_type('subscription_variation')  )
                            && (!empty($this->_wpwoofeed_settings['feed_variation_show_main'])  || !isset($this->_wpwoofeed_settings['feed_variation_show_main']))
                        )

                    ) {
                        $this->_build_item($fields, $product);
                    }
                    $feedStatus['parsed_products']++;
                    //array_push($feedStatus["parsed_product_ids"],$post);
                    if (($key = array_search($post, $feedStatus['products_left'])) !== false) {
                        unset($feedStatus['products_left'][$key]);
                    }
                    //$aExistsProds[] = $this->_get_id($product);
                    $this->_woocommerce_wpwoof_common->upadte_feed_status($this->_wpwoofeed_settings['edit_feed'],$feedStatus);

                }

                if (isset($this->_wpwoofeed_settings["product_tmp_data"]))  unset($this->_wpwoofeed_settings["product_tmp_data"]);
                if (isset($this->_wpwoofeed_settings["tax_rate_tmp_data"])) unset($this->_wpwoofeed_settings["tax_rate_tmp_data"]);
                if (isset($this->_wpwoofeed_settings['aBaseTax_tem_val']))  unset($this->_wpwoofeed_settings['aBaseTax_tem_val']);
                if (isset($this->_wpwoofeed_settings['atax_tem_val']))      unset($this->_wpwoofeed_settings['atax_tem_val']);


                $this->compileFeed($filepath);
                //@unlink($filepath);


                $this->_woocommerce_wpwoof_common->delete_feed_status($this->_wpwoofeed_settings['edit_feed']);
                wp_clear_scheduled_hook( 'wpwoof_generate_feed',  array( (int)$this->_wpwoofeed_settings['edit_feed'] )  );

                if(!empty($this->_wpwoofeed_settings['edit_feed'])) {
                    $this->_wpwoofeed_settings['status_feed'] = 'finished';
                    wpwoof_update_feed($this->_wpwoofeed_settings, $this->_wpwoofeed_settings['edit_feed'],true);
                }
            }catch(Exception $e){
                $this->_wpwoofeed_settings['status_feed'] = 'error: '.$e->getMessage();
                if(!empty($this->_wpwoofeed_settings['edit_feed'])) {
                    wpwoof_update_feed($this->_wpwoofeed_settings, $this->_wpwoofeed_settings['edit_feed'],true);
                }
                update_option("wpwoofeed_errors",$this->_wpwoofeed_settings['status_feed'] );
                $this->_storedebug("ERROR:".$e->getMessage());
                if(WPWOOF_DEBUG) exit("ERROR");
                return false;
            }
            $this->_storedebug('GENERATED');
            if(WPWOOF_DEBUG) exit("DONE");

    } //function generate()

    private function _build_item( $fields, $product ) {


        /* cheking block */
        if( empty($fields) || empty($product) ) return '';
        if(empty($this->_wpwoofeed_settings['feed_type'])) return '';

        $aItem = array(); /* JSON TICKET WITH DATA */


        $item = '';
        $columns = array();
        $values = array();


        $this->_loadMetaExtraData($product);
        
        // EXTRA FIELDS in products
        $extraFields =array();
        if (in_array($this->_product_get_type($product), $this->_aVariationsType)) {
            $mainMeta = (isset($this->_mainMetaData['wpwoofextra'][0])) ? $this->_mainMetaData['wpwoofextra'][0] : '';
            if (!empty($mainMeta)) {
                $mainMeta = unserialize($mainMeta);
                foreach ($mainMeta as $extraKey => $extraValue) {
                    if (isset($extraValue['custom_tag_name']) and !isset($extraValue['feed_type'][$this->_wpwoofeed_settings['feed_type']])) continue;
                    if (!isset($extraValue['custom_tag_name']) && (!isset($this->_woocommerce_wpwoof_common->product_fields[$extraKey]) || !in_array($this->_wpwoofeed_settings['feed_type'], $this->_woocommerce_wpwoof_common->product_fields[$extraKey]['feed_type']))) continue;
                    $nkey = isset($extraValue['custom_tag_name']) ? $extraValue['custom_tag_name'] : $extraKey;
                    $extraFields[$nkey] = $extraValue['value'];
                }
            }
        }
        $extrameda = $this->_getMeda('wpwoofextra', $product);
        if (!empty($extrameda)) {
            $extrameda = unserialize($extrameda);
            foreach ($extrameda as $extraKey => $extraValue) {
                if (isset($extraValue['custom_tag_name']) and !isset($value['feed_type'][$this->_wpwoofeed_settings['feed_type']])) continue;
                if (!isset($extraValue['custom_tag_name']) && (!isset($this->_woocommerce_wpwoof_common->product_fields[$extraKey]) || !in_array($this->_wpwoofeed_settings['feed_type'], $this->_woocommerce_wpwoof_common->product_fields[$extraKey]['feed_type']))) continue;
                $nkey = isset($extraValue['custom_tag_name']) ? $extraValue['custom_tag_name'] : $extraKey;
                $extraFields[$nkey] = $extraValue['value'];
            }
        } 
        
        if (!empty($extraFields)) {
            foreach ($extraFields as $extraKey => $extraValue) {
                if ($extraValue !== "") {
                    $aItem[$extraKey] = $extraValue;
                    if (!isset($this->_field_rules[$extraKey])) {
                        $this->_field_rules[$extraKey]['xml'] = 'g:' . $extraKey;
                        $this->_field_rules[$extraKey]['csv'] = $extraKey;
                    }
                    if (isset($fields[$extraKey])) {
                        unset($fields[$extraKey]);
                    }
                }
            }
        }
        $this->_wpwoofeed_settings['product-extra'] = $extraFields;
        $this->_storedebug("-------------------------------------- EXTRA FIELDS in products -------------------------------------\n".print_r($extraFields,true),true);


        foreach ($fields as $element => $field) {


           $this->_storedebug("--------------------------------------\n".$element."=>".print_r($field,true),true);

            /*  the  element exists */
           
           $custom_tag = false;
            if(!isset($this->_woocommerce_wpwoof_common->product_fields[$element])) {
                if (isset($this->_wpwoofeed_settings['extra'][$element])) {
                    $tagvalue = $this->_custom_user_function($product, $item, $field, $element);
                    $custom_tag =true;
                } else continue; 
            }
            
            if (!$custom_tag) {
                /*  the element has tag */
                if(!isset($this->_woocommerce_wpwoof_common->product_fields[$element][$this->_wpwoofeed_type]) ) continue;
                /* the element from this feed type */
                if(!in_array($this->_wpwoofeed_settings['feed_type'], $this->_woocommerce_wpwoof_common->product_fields[$element]['feed_type']  ) )

                $this->_storedebug("TAG:".$element." is valid");

                if(($element=='price' || $element=='sale_price') && !empty($this->_wpwoofeed_settings["tax_rate_tmp_data"])) {
                    $field['tax_rate'] = $this->_wpwoofeed_settings["tax_rate_tmp_data"];
                }

                $tagvalue = "";

                $func = !empty($this->_woocommerce_wpwoof_common->product_fields[$element]['funcgetdata']) ? $this->_woocommerce_wpwoof_common->product_fields[$element]['funcgetdata'] : '_wpwoofeed_' . str_replace(" ", "", strtolower($element));


                //if($this->_get_id($product)=="41" && $element=="image_link") exit($func);
                $tagvalue = method_exists($this, $func)
                    ? call_user_func_array(array($this, $func), array($product, $item, $field, $element))
                    : $this->_custom_user_function($product, $item, $field, $element);


                if ((($element=='price' && $this->_wpwoofeed_settings['product_tmp_data']['prices']->sale_price == 0) 
                        || ($element=='sale_price' && $this->_wpwoofeed_settings['product_tmp_data']['prices']->sale_price > 0 )) 
                        && (isset($this->_wpwoofeed_settings['feed_filter_price_bigger']) || isset($this->_wpwoofeed_settings['feed_filter_price_smaller']))) {
                    $price_without_format = floatval(str_replace( ' ' . $this->_store_info->currency, '',$tagvalue));
                    if ($price_without_format <= $this->_wpwoofeed_settings['feed_filter_price_bigger'] && $this->_wpwoofeed_settings['feed_filter_price_bigger']!=="") {
                        $this->_storedebug("!!!!!! Skip product !!! TAG:".print_r($element,true)."=>'" . print_r($price_without_format,true) . "' <= '".$this->_wpwoofeed_settings['feed_filter_price_bigger'] ."' (feed_filter_price_bigger)");
                        return '';
                    }
                    if ($price_without_format >= $this->_wpwoofeed_settings['feed_filter_price_smaller'] && $this->_wpwoofeed_settings['feed_filter_price_smaller']!=='') {
                        $this->_storedebug("!!!!!! Skip product !!! TAG:".print_r($element,true)."=>'" . print_r($price_without_format,true) . "' >= '".$this->_wpwoofeed_settings['feed_filter_price_smaller'] ."' (feed_filter_price_smaller)");
                        return '';
                    }

                }
            }
            //if($element=="id") echo ("id:::".htmlspecialchars($tagvalue));
            
            
            // global extra fields
            if (!$custom_tag && isset($this->_wpwoofeed_settings['extra'][$element]) && $tagvalue === "" 
                    && $element!= 'identifier_exists' && in_array($this->_wpwoofeed_settings['feed_type'], $this->_woocommerce_wpwoof_common->product_fields[$element]['feed_type'])) {
                $tagvalue = $this->_custom_user_function($product, $item, $field, $element);
                $this->_storedebug("Global!");
            }
            $this->_storedebug("TAG:".print_r($element,true)."=='" . print_r($tagvalue,true) . "'");
            if( $tagvalue != "" || ($element == 'inventory' && $tagvalue === 0) ) {
                $aItem[$element] = $tagvalue;
            }

        }

        if(count($aItem)){
//            $this->_storedebug("PHP_VERSION_ID:".PHP_VERSION_ID);
            if(PHP_VERSION_ID < 70200) {
                foreach (array_keys($aItem) as $key) {
                    if (!is_array($aItem[$key])) {
                        $aItem[$key] = iconv('UTF-8', 'UTF-8//IGNORE', $aItem[$key]);
                    }
                }
                $this->storeTicket($this->_get_id($product), json_encode($aItem));
            } else {
                $this->storeTicket($this->_get_id($product), json_encode($aItem,JSON_INVALID_UTF8_IGNORE));
            }
            $this->checkAndAddCsvColumn(array_keys($aItem));
            return true;
        } else {
            return false;
        }
    }
} //CLASS


function wpwoofeed_generate_feed($feed_data, $type = 'xml'){
    if(WPWOOF_DEBUG) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL );
    }
    $feedGenerator = new WPwoofGenerator( $feed_data, $type);
    $feedGenerator->generate();
}


