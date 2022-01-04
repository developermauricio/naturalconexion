<?php
// Adds custom Brand columns to the Brand fields list
function woo_ce_extend_brand_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// YITH WooCommerce Brands Add-On - http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
	if( woo_ce_detect_export_plugin( 'yith_brands_pro' ) ) {
		$fields[] = array(
			'name' => 'custom_url',
			'label' => __( 'Custom URL', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Brands Add-On', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'banner',
			'label' => __( 'Banner', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Brands Add-On', 'woocommerce-exporter' )
		);
	}

	// Perfect WooCommerce Brands - https://wordpress.org/plugins/perfect-woocommerce-brands/
	if( woo_ce_detect_export_plugin( 'wc_pwb' ) ) {
		$fields[] = array(
			'name' => 'logo',
			'label' => __( 'Logo', 'woocommerce-exporter' ),
			'hover' => __( 'Perfect WooCommerce Brands', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'banner',
			'label' => __( 'Banner', 'woocommerce-exporter' ),
			'hover' => __( 'Perfect WooCommerce Brands', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'banner_link',
			'label' => __( 'Banner Link', 'woocommerce-exporter' ),
			'hover' => __( 'Perfect WooCommerce Brands', 'woocommerce-exporter' )
		);
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$fields[] = array(
			'name' => 'wpseo_title',
			'label' => __( 'WordPress SEO - SEO Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_description',
			'label' => __( 'WordPress SEO - SEO Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_focuskw',
			'label' => __( 'WordPress SEO - Focus Keyword', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_canonical',
			'label' => __( 'WordPress SEO - Canonical', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_noindex',
			'label' => __( 'WordPress SEO - Noindex', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_sitemap_include',
			'label' => __( 'WordPress SEO - Sitemap include', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_title',
			'label' => __( 'WordPress SEO - Facebook Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_description',
			'label' => __( 'WordPress SEO - Facebook Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_image',
			'label' => __( 'WordPress SEO - Facebook Image', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_title',
			'label' => __( 'WordPress SEO - Twitter Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_description',
			'label' => __( 'WordPress SEO - Twitter Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_image',
			'label' => __( 'WordPress SEO - Twitter Image', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
	}

	// Custom Brand meta
	$custom_terms = woo_ce_get_option( 'custom_brands', '' );
	if( !empty( $custom_terms ) ) {
		foreach( $custom_terms as $custom_term ) {
			if( !empty( $custom_term ) ) {
				$fields[] = array(
					'name' => $custom_term,
					'label' => woo_ce_clean_export_label( $custom_term ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_brand_fields_custom_brand_hover', '%s: %s' ), __( 'Custom Brand', 'woocommerce-exporter' ), $custom_term )
				);
			}
		}
	}
	unset( $custom_terms, $custom_term );

	return $fields;

}
add_filter( 'woo_ce_brand_fields', 'woo_ce_extend_brand_fields' );

function woo_ce_extend_brand_item( $brand ) {

	$term_id = ( isset( $brand->term_id ) ? $brand->term_id : 0 );

	// WordPress MultiSite
	if( is_multisite() ) {
		$brand->blog_id = get_current_blog_id();
	}

	// YITH WooCommerce Brands Add-On - http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
	if( woo_ce_detect_export_plugin( 'yith_brands_pro' ) ) {
		$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
		if( !empty( $thumbnail_id ) )
			$brand->image = wp_get_attachment_url( $thumbnail_id );
		$thumbnail_id = get_term_meta( $term_id, 'banner_id', true );
		if( !empty( $thumbnail_id ) )
			$brand->banner = wp_get_attachment_url( $thumbnail_id );
		$brand->custom_url = get_term_meta( $term_id, 'custom_url', true );
	}

	// Perfect WooCommerce Brands - https://wordpress.org/plugins/perfect-woocommerce-brands/
	if( woo_ce_detect_export_plugin( 'wc_pwb' ) ) {
		$thumbnail_id = get_term_meta( $term_id, 'pwb_brand_image', true );
		if( !empty( $thumbnail_id ) )
			$brand->logo = wp_get_attachment_url( $thumbnail_id );
		$thumbnail_id = get_term_meta( $term_id, 'pwb_brand_banner', true );
		if( !empty( $thumbnail_id ) )
			$brand->banner = wp_get_attachment_url( $thumbnail_id );
		$brand->banner_link = get_term_meta( $term_id, 'pwb_brand_banner_link', true );
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$meta = get_option( 'wpseo_taxonomy_meta' );
		// Check if the WordPress Option is empty
		if( $meta !== false ) {
			// Check if the WordPress Option is an array
			if( is_array( $meta ) ) {
				// Check if the product_cat Taxonomy exists within the WordPress Option
				$term_taxonomy = 'product_cat';
				if( array_key_exists( $term_taxonomy, $meta ) ) {
					$meta = $meta[$term_taxonomy];
					// Check if the Term ID exists within the array
					if( array_key_exists( $term_id, $meta ) ) {
						$category->wpseo_title = ( isset( $meta[$term_id]['wpseo_title'] ) ? $meta[$term_id]['wpseo_title'] : '' );
						$category->wpseo_description = ( isset( $meta[$term_id]['wpseo_desc'] ) ? $meta[$term_id]['wpseo_desc'] : '' );
						$category->wpseo_canonical = ( isset( $meta[$term_id]['wpseo_canonical'] ) ? $meta[$term_id]['wpseo_canonical'] : '' );
						$category->wpseo_noindex = ( isset( $meta[$term_id]['wpseo_noindex'] ) ? woo_ce_format_wpseo_noindex( $meta[$term_id]['wpseo_noindex'] ) : '' );
						$category->wpseo_sitemap_include = ( isset( $meta[$term_id]['wpseo_sitemap_include'] ) ? woo_ce_format_wpseo_sitemap_include( $meta[$term_id]['wpseo_sitemap_include'] ) : '' );
						$category->wpseo_focuskw = ( isset( $meta[$term_id]['wpseo_focuskw'] ) ? $meta[$term_id]['wpseo_focuskw'] : '' );
						$category->wpseo_opengraph_title = ( isset( $meta[$term_id]['wpseo_opengraph-title'] ) ? $meta[$term_id]['wpseo_opengraph-title'] : '' );
						$category->wpseo_opengraph_description = ( isset( $meta[$term_id]['wpseo_opengraph-description'] ) ? $meta[$term_id]['wpseo_opengraph-description'] : '' );
						$category->wpseo_opengraph_image = ( isset( $meta[$term_id]['wpseo_opengraph-image'] ) ? $meta[$term_id]['wpseo_opengraph-image'] : '' );
						$category->wpseo_twitter_title = ( isset( $meta[$term_id]['wpseo_twitter-title'] ) ? $meta[$term_id]['wpseo_twitter-title'] : '' );
						$category->wpseo_twitter_description = ( isset( $meta[$term_id]['wpseo_twitter-description'] ) ? $meta[$term_id]['wpseo_twitter-description'] : '' );
						$category->wpseo_twitter_image = ( isset( $meta[$term_id]['wpseo_twitter-image'] ) ? $meta[$term_id]['wpseo_twitter-image'] : '' );
					}
					unset( $term_id );
				}
			}
		}
		unset( $meta );
	}

	// Custom Brand meta
	$custom_terms = woo_ce_get_option( 'custom_brands', '' );
	if( !empty( $custom_terms ) ) {
		foreach( $custom_terms as $custom_term ) {
			if( !empty( $custom_term ) ) {
				$brand->{$custom_term} = woo_ce_format_custom_meta( get_term_meta( $term_id, $custom_term, true ) );
			}
		}
	}

	return $brand;

}
add_filter( 'woo_ce_brand_item', 'woo_ce_extend_brand_item' );

function woo_ce_extend_brand_term_taxonomy( $term_taxonomy = '' ) {

	// YITH WooCommerce Brands Add-On - http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
	if( woo_ce_detect_export_plugin( 'yith_brands_pro' ) )
		$term_taxonomy = 'yith_product_brand';

	// Perfect WooCommerce Brands - https://wordpress.org/plugins/perfect-woocommerce-brands/
	if( woo_ce_detect_export_plugin( 'wc_pwb' ) )
		$term_taxonomy = 'pwb-brand';

	return $term_taxonomy;

}
if( woo_ce_detect_product_brands() )
	add_filter( 'woo_ce_brand_term_taxonomy', 'woo_ce_extend_brand_term_taxonomy' );