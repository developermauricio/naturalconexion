<?php

namespace PixelYourSite\GA\Helpers;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get related products based on product category and tags. No shuffle
 *
 * @since  3.0.0
 * @param  int   $product_id  Product ID.
 * @param  int   $limit       Limit of results.
 * @param  array $exclude_ids Exclude IDs from the results.
 * @return array
 */
function custom_wc_get_related_products( $product_id, $limit = 5, $exclude_ids = array() ) {

    $product_id     = absint( $product_id );
    $limit          = $limit >= -1 ? $limit : 5;
    $exclude_ids    = array_merge( array( 0, $product_id ), $exclude_ids );
    $transient_name = 'wc_related_' . $product_id;
    $query_args     = http_build_query(
        array(
            'limit'       => $limit,
            'exclude_ids' => $exclude_ids,
        )
    );

    $transient     = get_transient( $transient_name );
    $related_posts = $transient && isset( $transient[ $query_args ] ) ? $transient[ $query_args ] : false;

    // We want to query related posts if they are not cached, or we don't have enough.
    if ( false === $related_posts || count( $related_posts ) < $limit ) {

        $cats_array = apply_filters( 'woocommerce_product_related_posts_relate_by_category', true, $product_id ) ? apply_filters( 'woocommerce_get_related_product_cat_terms', wc_get_product_term_ids( $product_id, 'product_cat' ), $product_id ) : array();
        $tags_array = apply_filters( 'woocommerce_product_related_posts_relate_by_tag', true, $product_id ) ? apply_filters( 'woocommerce_get_related_product_tag_terms', wc_get_product_term_ids( $product_id, 'product_tag' ), $product_id ) : array();

        // Don't bother if none are set, unless woocommerce_product_related_posts_force_display is set to true in which case all products are related.
        if ( empty( $cats_array ) && empty( $tags_array ) && ! apply_filters( 'woocommerce_product_related_posts_force_display', false, $product_id ) ) {
            $related_posts = array();
        } else {
            $data_store    = \WC_Data_Store::load( 'product' );
            $related_posts = $data_store->get_related_products( $cats_array, $tags_array, $exclude_ids, $limit + 10, $product_id );
        }

        if ( $transient ) {
            $transient[ $query_args ] = $related_posts;
        } else {
            $transient = array( $query_args => $related_posts );
        }

        set_transient( $transient_name, $transient, DAY_IN_SECONDS );
    }

    $related_posts = apply_filters(
        'woocommerce_related_products',
        $related_posts,
        $product_id,
        array(
            'limit'        => $limit,
            'excluded_ids' => $exclude_ids,
        )
    );

   // if ( apply_filters( 'woocommerce_product_related_posts_shuffle', true ) ) {
    //    shuffle( $related_posts );
   // }

    return $related_posts; //array_slice( $related_posts, 0, $limit );
}

/**
 * @param $product_id
 * @return string
 */

function getWooProductContentId( $product_id ) {

    if ( PixelYourSite\GA()->getOption( 'woo_content_id' ) == 'product_sku' ) {
        $content_id = get_post_meta( $product_id, '_sku', true );
    } else {
        $content_id = $product_id;
    }

    $prefix = PixelYourSite\GA()->getOption( 'woo_content_id_prefix' );
    $suffix = PixelYourSite\GA()->getOption( 'woo_content_id_suffix' );

    $value = $prefix . $content_id . $suffix;

    return $value;
}

function getWooEventCartItemId( $item ) {

    if ( PixelYourSite\GA()->getOption( 'woo_variable_as_simple' )
        && isset( $item['parent_id'] )
        && $item['parent_id'] !== 0 )
    {
        $product_id = $item['parent_id'];
    } else {
        $product_id = $item['product_id'];
    }

    return $product_id;
}
/**
 * @deprecated use getWooEventCartItemId
 * @param $item
 * @return mixed
 */
function getWooCartItemId( $item ) {

    if ( ! PixelYourSite\GA()->getOption( 'woo_variable_as_simple' ) && isset( $item['variation_id'] ) && $item['variation_id'] !== 0 ) {
        $product_id = $item['variation_id'];
    } else {
        $product_id = $item['product_id'];
    }

    return $product_id;
}

function getWooProductDataId( $item ) {

    if($item['type'] == 'variation'
        && PixelYourSite\GA()->getOption( 'woo_variable_as_simple' )
    ) {
        $product_id = $item['parent_id'];
    }else {
        $product_id = $item['product_id'];
    }

    return $product_id;

}

function adaptDynamicRemarketingParams( $params ) {
	
	if ( PixelYourSite\PYS()->getOption( 'google_retargeting_logic' ) == 'ecomm' ) {
		
		return array(
			'ecomm_prodid'     => $params['product_id'],
			'ecomm_pagetype'   => $params['page_type'],
			'ecomm_totalvalue' => $params['total_value'],
		);
		
	} else {
		
		// custom vertical has different than retail page types
		$page_types = array(
			'search' => 'searchresults',
			'product' => 'offerdetail',
			'category' => null, //not supported by custom vertical
			'cart' => 'conversionintent',
			'checkout' => 'conversionintent',
			'purchase' => 'conversion'
		);
		
		return array(
			'dynx_itemid'     => $params['product_id'],
			'dynx_pagetype'   => $page_types[ $params['page_type'] ],
			'dynx_totalvalue' => $params['total_value'],
		);
		
	}
	
}

/**
 * Render Cross Domain Domain text field
 *
 * @param int    $index
 */
function renderCrossDomainDomain( $index = 0 ) {
    
    $slug = PixelYourSite\GA()->getSlug();
    
    $attr_name = "pys[$slug][cross_domain_domains][]";
    $attr_id = 'pys_' . $slug . '_cross_domain_domains_' . $index;
    
    $values = (array) PixelYourSite\GA()->getOption( 'cross_domain_domains' );
    $attr_value = isset( $values[ $index ] ) ? $values[ $index ] : null;
    
    ?>
    
    <input type="text" name="<?php esc_attr_e( $attr_name ); ?>"
           id="<?php esc_attr_e( $attr_id ); ?>"
           value="<?php esc_attr_e( $attr_value ); ?>"
           placeholder="Enter domain"
           class="form-control">
    
    <?php
    
}

/*
 * EASY DIGITAL DOWNLOADS
 */

function getEddDownloadContentId( $download_id ) {

    if ( PixelYourSite\GA()->getOption( 'edd_content_id' ) == 'download_sku' ) {
        $content_id = get_post_meta( $download_id, 'edd_sku', true );
    } else {
        $content_id = $download_id;
    }

    $prefix = PixelYourSite\GA()->getOption( 'edd_content_id_prefix' );
    $suffix = PixelYourSite\GA()->getOption( 'edd_content_id_suffix' );

    return $prefix . $content_id . $suffix;

}