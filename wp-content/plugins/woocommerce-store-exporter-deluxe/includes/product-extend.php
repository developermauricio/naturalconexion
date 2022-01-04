<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Quick Export

	// Scheduled Exports

	function woo_ce_products_filter_post_stati( $product_stati = '' ) {

		// Discontinued Product for WooCommerce - https://wordpress.org/plugins/discontinued-product-for-woocommerce/
		if( function_exists( 'discontinued_product_for_woocommerce_init' ) ) {
			$product_stati['wc-discontinued'] = __( 'Discontinued', 'woocommerce-exporter' );
		}

		return $product_stati;

	}
	add_filter( 'woo_ce_products_filter_post_stati', 'woo_ce_products_filter_post_stati' );

	function woo_ce_products_custom_fields_tab_manager() {

		// WooCommerce Tab Manager - http://www.woothemes.com/products/woocommerce-tab-manager/
		if( woo_ce_detect_export_plugin( 'wc_tabmanager' ) == false )
			return;

		$custom_product_tabs = woo_ce_get_option( 'custom_product_tabs', '' );
		if( !empty( $custom_product_tabs ) )
			$custom_product_tabs = implode( "\n", $custom_product_tabs );

		ob_start(); ?>
<tr>
	<th>
		<label for="custom_product_tabs"><?php _e( 'Custom Product Tabs', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<textarea id="custom_product_tabs" name="custom_product_tabs" rows="5" cols="70"><?php echo esc_textarea( $custom_product_tabs ); ?></textarea>
		<p class="description"><?php _e( 'Include custom Product Tabs linked to individual Products within your export file by adding the Name of each Product Tab to a new line above.<br />For example: <code>Ingredients</code> (new line) <code>Specification</code>', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	function woo_ce_products_custom_fields_wootabs() {

		if( woo_ce_detect_export_plugin( 'wootabs' ) == false )
			return;

		$custom_wootabs = woo_ce_get_option( 'custom_wootabs', '' );
		if( !empty( $custom_wootabs ) )
			$custom_wootabs = implode( "\n", $custom_wootabs );

		ob_start(); ?>
<tr>
	<th>
		<label for="custom_wootabs"><?php _e( 'Custom WooTabs', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<textarea id="custom_wootabs" name="custom_wootabs" rows="5" cols="70"><?php echo esc_textarea( $custom_wootabs ); ?></textarea>
		<p class="description"><?php _e( 'Include WooTabs linked to individual Products within in your export file by adding the Name of each WooTab to a new line above.<br />For example: <code>Custom Tab no.1</code> (new line) <code>Custom Tab no.2</code>', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// Product Add-ons - http://www.woothemes.com/
	function woo_ce_products_custom_fields_product_addons() {

		if( woo_ce_detect_export_plugin( 'product_addons' ) == false )
			return;

		ob_start(); ?>
<tr>
	<th>
		<label><?php _e( 'Custom Product Add-ons', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<p class="description"><?php _e( 'The Custom Product Add-ons field has moved. Switch to the Orders export type and scroll to the Custom Product Add-ons field within the Custom Order Fields section.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	/* End of: WordPress Administration */

}

function woo_ce_extend_product_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// Product Attribute support can be disabled where FORM limits are being hit which affects Quick Exports
	if( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {

		// Global Attributes
		$has_attributes = false;
		$attributes = ( function_exists( 'wc_get_attribute_taxonomies' ) ? wc_get_attribute_taxonomies() : array() );
		if( !empty( $attributes ) ) {
			$has_attributes = true;
			foreach( $attributes as $attribute ) {

				$label = $attribute->attribute_label ? $attribute->attribute_label : $attribute->attribute_name;
				$fields[] = array(
					'name' => sprintf( 'attribute_%s', esc_attr( $attribute->attribute_name ) ),
					'label' => sprintf( __( 'Attribute: %s', 'woocommerce-exporter' ), esc_attr( $label ) ),
					'alias' => array( sprintf( 'pa_%s', esc_attr( $attribute->attribute_name ) ) ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_attribute', '%s: %s (Term ID: %d)' ), __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
				);

				// Advanced Product Attribute support can be disabled where FORM limits are being hit or just cluttering the Quick Export/Export Template screen; enables by default when Product Importer Deluxe is activated
				if(
					apply_filters( 'woo_ce_enable_advanced_product_attributes', false ) ||
					woo_ce_detect_export_plugin( 'wc_product_importer_deluxe' )
				) {

					$fields[] = array(
						'name' => sprintf( 'attribute_%s_position', esc_attr( $attribute->attribute_name ) ),
						'label' => sprintf( __( 'Attribute: %s - Position', 'woocommerce-exporter' ), esc_attr( $label ) ),
						'alias' => array( sprintf( 'pa_%s_position', esc_attr( $attribute->attribute_name ) ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_attribute', '%s: %s (Term ID: %d)' ), __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
					);
					$fields[] = array(
						'name' => sprintf( 'attribute_%s_visible', esc_attr( $attribute->attribute_name ) ),
						'label' => sprintf( __( 'Attribute: %s - Visible on the product page', 'woocommerce-exporter' ), esc_attr( $label ) ),
						'alias' => array( sprintf( 'pa_%s_visible', esc_attr( $attribute->attribute_name ) ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_attribute', '%s: %s (Term ID: %d)' ), __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
					);
					$fields[] = array(
						'name' => sprintf( 'attribute_%s_variation', esc_attr( $attribute->attribute_name ) ),
						'label' => sprintf( __( 'Attribute: %s - Used for variations', 'woocommerce-exporter' ), esc_attr( $label ) ),
						'alias' => array( sprintf( 'pa_%s_variation', esc_attr( $attribute->attribute_name ) ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_attribute', '%s: %s (Term ID: %d)' ), __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
					);
					$fields[] = array(
						'name' => sprintf( 'attribute_%s_taxonomy', esc_attr( $attribute->attribute_name ) ),
						'label' => sprintf( __( 'Attribute: %s - Is Taxonomy', 'woocommerce-exporter' ), esc_attr( $label ) ),
						'alias' => array( sprintf( 'pa_%s_taxonomy', esc_attr( $attribute->attribute_name ) ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_attribute', '%s: %s (Term ID: %d)' ), __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
					);

				}

				if( apply_filters( 'woo_ce_enable_product_attribute_quantities', false ) ) {
					$fields[] = array(
						'name' => sprintf( 'attribute_%s_quantity', esc_attr( $attribute->attribute_name ) ),
						'label' => sprintf( __( 'Attribute: %s (Quantity)', 'woocommerce-exporter' ), esc_attr( $label ) ),
						'alias' => array( sprintf( 'pa_%s_quantity', esc_attr( $attribute->attribute_name ) ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_attribute', '%s: %s (Term ID: %d)' ), __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
					);
				}

			}
			unset( $attributes, $attribute, $label );
		}

		// Custom Attributes
		$custom_attributes = woo_ce_get_option( 'custom_attributes', '' );
		if( !empty( $custom_attributes ) ) {
			$has_attributes = true;
			foreach( $custom_attributes as $custom_attribute ) {
				if( !empty( $custom_attribute ) ) {
					$fields[] = array(
						'name' => sprintf( 'attribute_%s', ( function_exists( 'remove_accents' ) ? remove_accents( sanitize_key( $custom_attribute ) ) : sanitize_key( $custom_attribute ) ) ),
						'label' => sprintf( __( 'Attribute: %s', 'woocommerce-exporter' ), woo_ce_clean_export_label( $custom_attribute ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_custom_attribute_hover', '%s: %s' ), __( 'Custom Attribute', 'woocommerce-exporter' ), $custom_attribute )
					);
					// @mod - Add support for Custom Attribute Quantity in 2.4+
				}
			}
			unset( $custom_attributes, $custom_attribute );
		}

		// Show Default Attributes field
		if( $has_attributes ) {
			$fields[] = array(
				'name' => 'default_attributes',
				'label' => __( 'Default Attributes', 'woocommerce-exporter' )
			);
		}

	}

	// Term Taxonomies
	$term_taxonomies = woo_ce_get_product_custom_term_taxonomies();
	if( !empty( $term_taxonomies ) ) {
		foreach( $term_taxonomies as $key => $term_taxonomy ) {
			$fields[] = array(
				'name' => sprintf( 'term_taxonomy_%s', $key ),
				'label' => sprintf( __( 'Term Taxonomy: %s', 'woocommerce-exporter' ), ucfirst( $term_taxonomy->label ) ),
				'hover' => sprintf( __( 'Term Taxonomy: %s', 'woocommerce-exporter' ), $key )
			);
		}
	}
	unset( $term_taxonomies, $term_taxonomy );

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				if( !empty( $product_addon ) ) {
					$fields[] = array(
						'name' => sprintf( 'product_addon_%s', $product_addon->post_name ),
						'label' => sprintf( __( 'Product Add-ons: %s', 'woocommerce-exporter' ), ucfirst( $product_addon->post_title ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_product_addons', '%s: %s' ), __( 'Product Add-ons', 'woocommerce-exporter' ), $product_addon->form_title )
					);
				}
			}
		}
		unset( $product_addons, $product_addon );
	}

	// WooCommerce Google Product Feed - http://www.leewillis.co.uk/wordpress-plugins/
	if( woo_ce_detect_export_plugin( 'gpf' ) ) {
		$fields[] = array(
			'name' => 'gpf_availability',
			'label' => __( 'WooCommerce Google Product Feed - Availability', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_condition',
			'label' => __( 'WooCommerce Google Product Feed - Condition', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_brand',
			'label' => __( 'WooCommerce Google Product Feed - Brand', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_product_type',
			'label' => __( 'WooCommerce Google Product Feed - Product Type', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_google_product_category',
			'label' => __( 'WooCommerce Google Product Feed - Google Product Category', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_gtin',
			'label' => __( 'WooCommerce Google Product Feed - Global Trade Item Number (GTIN)', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_mpn',
			'label' => __( 'WooCommerce Google Product Feed - Manufacturer Part Number (MPN)', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_gender',
			'label' => __( 'WooCommerce Google Product Feed - Gender', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_agegroup',
			'label' => __( 'WooCommerce Google Product Feed - Age Group', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_color',
			'label' => __( 'WooCommerce Google Product Feed - Colour', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_size',
			'label' => __( 'WooCommerce Google Product Feed - Size', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Google Product Feed', 'woocommerce-exporter' )
		);
	}

	// All in One SEO Pack - http://wordpress.org/extend/plugins/all-in-one-seo-pack/
	if( woo_ce_detect_export_plugin( 'aioseop' ) ) {
		$fields[] = array(
			'name' => 'aioseop_keywords',
			'label' => __( 'All in One SEO - Keywords', 'woocommerce-exporter' ),
			'hover' => __( 'All in One SEO Pack', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_description',
			'label' => __( 'All in One SEO - Description', 'woocommerce-exporter' ),
			'hover' => __( 'All in One SEO Pack', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_title',
			'label' => __( 'All in One SEO - Title', 'woocommerce-exporter' ),
			'hover' => __( 'All in One SEO Pack', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_title_attributes',
			'label' => __( 'All in One SEO - Title Attributes', 'woocommerce-exporter' ),
			'hover' => __( 'All in One SEO Pack', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_menu_label',
			'label' => __( 'All in One SEO - Menu Label', 'woocommerce-exporter' ),
			'hover' => __( 'All in One SEO Pack', 'woocommerce-exporter' )
		);
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$fields[] = array(
			'name' => 'wpseo_focuskw',
			'label' => __( 'WordPress SEO - Focus Keyword', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_metadesc',
			'label' => __( 'WordPress SEO - Meta Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_title',
			'label' => __( 'WordPress SEO - SEO Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_noindex',
			'label' => __( 'WordPress SEO - Noindex', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_follow',
			'label' => __( 'WordPress SEO - Follow', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_googleplus_description',
			'label' => __( 'WordPress SEO - Google+ Description', 'woocommerce-exporter' ),
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
		$fields[] = array(
			'name' => 'wpseo_canonical',
			'label' => __( 'WordPress SEO - Canonical URL', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
	}

	// Ultimate SEO - http://wordpress.org/plugins/seo-ultimate/
	if( woo_ce_detect_export_plugin( 'ultimate_seo' ) ) {
		$fields[] = array(
			'name' => 'useo_meta_title',
			'label' => __( 'Ultimate SEO - Title Tag', 'woocommerce-exporter' ),
			'hover' => __( 'Ultimate SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_description',
			'label' => __( 'Ultimate SEO - Meta Description', 'woocommerce-exporter' ),
			'hover' => __( 'Ultimate SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_keywords',
			'label' => __( 'Ultimate SEO - Meta Keywords', 'woocommerce-exporter' ),
			'hover' => __( 'Ultimate SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_social_title',
			'label' => __( 'Ultimate SEO - Social Title', 'woocommerce-exporter' ),
			'hover' => __( 'Ultimate SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_social_description',
			'label' => __( 'Ultimate SEO - Social Description', 'woocommerce-exporter' ),
			'hover' => __( 'Ultimate SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_noindex',
			'label' => __( 'Ultimate SEO - Noindex', 'woocommerce-exporter' ),
			'hover' => __( 'Ultimate SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_noautolinks',
			'label' => __( 'Ultimate SEO - Disable Autolinks', 'woocommerce-exporter' ),
			'hover' => __( 'Ultimate SEO', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() ) {
		$fields[] = array(
			'name' => 'brands',
			'label' => __( 'Brands', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Brands', 'woocommerce-exporter' )
		);
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		$fields[] = array(
			'name' => 'msrp',
			'label' => __( 'MSRP', 'woocommerce-exporter' ),
			'hover' => __( 'Manufacturer Suggested Retail Price (MSRP)', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Germanized Pro - https://www.vendidero.de/woocommerce-germanized
	if( woo_ce_detect_export_plugin( 'wc_germanized_pro' ) ) {
		// Check for Product Units
		if( get_option( 'woocommerce_gzd_display_listings_product_units' ) == 'yes' ) {
			$fields[] = array(
				'name' => 'sale_price_label',
				'label' => __( 'Sale Label', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'sale_price_regular_label',
				'label' => __( 'Sale Regular Label', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'unit',
				'label' => __( 'Unit', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'unit_product',
				'label' => __( 'Product Units', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'unit_base',
				'label' => __( 'Base Price Units', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'unit_price_auto',
				'label' => __( 'Calculation', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'unit_price_regular',
				'label' => __( 'Regular Base Price', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'unit_price_sale',
				'label' => __( 'Sale Base Price', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'unit_price_regular_display',
				'label' => __( 'Regular Base Price Display', 'woocommerce-germanized' ),
				'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
			);
		}
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		$fields[] = array(
			'name' => 'cost_of_goods',
			'label' => __( 'Cost of Good', 'woocommerce-exporter' ),
			'hover' => __( 'Cost of Goods', 'woocommerce-exporter' )
		);
	}

	// Per Product Shipping - http://www.woothemes.com/products/per-product-shipping/
	if( woo_ce_detect_export_plugin( 'per_product_shipping' ) ) {
		$fields[] = array(
			'name' => 'per_product_shipping',
			'label' => __( 'Per-Product Shipping', 'woocommerce-exporter' ),
			'hover' => __( 'Per-Product Shipping', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'per_product_shipping_country',
			'label' => __( 'Per-Product Shipping - Country', 'woocommerce-exporter' ),
			'hover' => __( 'Per-Product Shipping', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'per_product_shipping_state',
			'label' => __( 'Per-Product Shipping - State', 'woocommerce-exporter' ),
			'hover' => __( 'Per-Product Shipping', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'per_product_shipping_postcode',
			'label' => __( 'Per-Product Shipping - Postcode', 'woocommerce-exporter' ),
			'hover' => __( 'Per-Product Shipping', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'per_product_shipping_cost',
			'label' => __( 'Per-Product Shipping - Cost', 'woocommerce-exporter' ),
			'hover' => __( 'Per-Product Shipping', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'per_product_shipping_item_cost',
			'label' => __( 'Per-Product Shipping - Item Cost', 'woocommerce-exporter' ),
			'hover' => __( 'Per-Product Shipping', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'per_product_shipping_order',
			'label' => __( 'Per-Product Shipping - Priority', 'woocommerce-exporter' ),
			'hover' => __( 'Per-Product Shipping', 'woocommerce-exporter' )
		);
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) ) {
		$fields[] = array(
			'name' => 'vendors',
			'label' => __( 'Product Vendors', 'woocommerce-exporter' ),
			'hover' => __( 'Product Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'vendor_ids',
			'label' => __( 'Product Vendor ID\'s', 'woocommerce-exporter' ),
			'hover' => __( 'Product Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'vendor_commission',
			'label' => __( 'Vendor Commission', 'woocommerce-exporter' ),
			'hover' => __( 'Product Vendors', 'woocommerce-exporter' )
		);
	}

	// WC Vendors - http://wcvendors.com
	if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
		$fields[] = array(
			'name' => 'vendor',
			'label' => __( 'Vendor' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'vendor_commission_rate',
			'label' => __( 'Commission (%)' ),
			'hover' => __( 'WC Vendors', 'woocommerce-exporter' )
		);
	}

	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		$fields[] = array(
			'name' => 'vendor',
			'label' => __( 'Vendor' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'vendor_commission_rate',
			'label' => __( 'Commission (%)' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
	}

	// WC Marketplace - https://wc-marketplace.com/
	if( woo_ce_detect_export_plugin( 'wc_marketplace' ) ) {
		$fields[] = array(
			'name' => 'vendor',
			'label' => __( 'Vendor' ),
			'hover' => __( 'WC Marketplace', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'vendor_commission',
			'label' => __( 'Commission' ),
			'hover' => __( 'WC Marketplace', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Wholesale Pricing - http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-wholesale-pricing/
	if( woo_ce_detect_export_plugin( 'wholesale_pricing' ) ) {
		$fields[] = array(
			'name' => 'wholesale_price',
			'label' => __( 'Wholesale Price', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Wholesale Pricing', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wholesale_price_text',
			'label' => __( 'Wholesale Text', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Wholesale Pricing', 'woocommerce-exporter' )
		);
	}

	// Advanced Custom Fields - http://www.advancedcustomfields.com
	if( woo_ce_detect_export_plugin( 'acf' ) ) {
		$custom_fields = woo_ce_get_acf_product_fields();
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ) {
				$fields[] = array(
					'name' => sprintf( 'acf_%s', sanitize_key( $custom_field['name'] ) ),
					'label' => $custom_field['label'],
					'hover' => __( 'Advanced Custom Fields', 'woocommerce-exporter' )
				);
			}
		}
		unset( $custom_fields, $custom_field );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( !get_option( 'wccf_migrated_to_20' ) ) {
			// Legacy WooCommerce Custom Fields was stored in a single Option
			$options = get_option( 'rp_wccf_options' );
			if( !empty( $options ) ) {
				$custom_fields = ( isset( $options[1]['product_admin_fb_config'] ) ? $options[1]['product_admin_fb_config'] : false );
				if( !empty( $custom_fields ) ) {
					foreach( $custom_fields as $custom_field ) {
						$fields[] = array(
							'name' => sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) ),
							'label' => ucfirst( $custom_field['label'] ),
							'hover' => __( 'WooCommerce Custom Fields', 'woocommerce-exporter' )
						);
					}
				}
			}
			unset( $options );
		} else {
			// WooCommerce Custom Fields uses CPT for Product properties
			$custom_fields = woo_ce_get_wccf_product_properties();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$label = get_post_meta( $custom_field->ID, 'label', true );
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$fields[] = array(
						'name' => sprintf( 'wccf_pp_%s', sanitize_key( $key ) ),
						'label' => ucfirst( $label ),
						'hover' => __( 'WooCommerce Custom Fields', 'woocommerce-exporter' )
					);
				}
			}
			unset( $label, $key );
		}
		unset( $custom_fields, $custom_field );
	}

	// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/
	if( woo_ce_detect_export_plugin( 'wc_fields_factory' ) ) {
		// Admin Fields
		$admin_fields = woo_ce_get_wcff_admin_fields();
		if( !empty( $admin_fields ) ) {
			foreach( $admin_fields as $admin_field ) {
				$fields[] = array(
					'name' => sprintf( 'wccaf_%s', sanitize_key( $admin_field['name'] ) ),
					'label' => ucfirst( $admin_field['label'] ),
					'hover' => sprintf( '%s: %s (%s)', __( 'WC Fields Factory', 'woocommerce-exporter' ), __( 'Admin Field', 'woocommerce-exporter' ), sanitize_key( $admin_field['name'] ) )
				);
			}
		}
		unset( $admin_fields, $admin_field );
	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
		$fields[] = array(
			'name' => 'subscription_price',
			'label' => __( 'Subscription Price', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_period_interval',
			'label' => __( 'Subscription Period Interval', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_period',
			'label' => __( 'Subscription Period', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_length',
			'label' => __( 'Subscription Length', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_sign_up_fee',
			'label' => __( 'Subscription Sign-up Fee', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_trial_length',
			'label' => __( 'Subscription Trial Length', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_trial_period',
			'label' => __( 'Subscription Trial Period', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_limit',
			'label' => __( 'Limit Subscription', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$fields[] = array(
			'name' => 'booking_has_persons',
			'label' => __( 'Booking Has Persons', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_has_resources',
			'label' => __( 'Booking Has Resources', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_base_cost',
			'label' => __( 'Booking Base Cost', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_block_cost',
			'label' => __( 'Booking Block Cost', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_display_cost',
			'label' => __( 'Booking Display Cost', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_requires_confirmation',
			'label' => __( 'Booking Requires Confirmation', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_user_can_cancel',
			'label' => __( 'Booking Can Be Cancelled', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		$fields[] = array(
			'name' => 'barcode_type',
			'label' => __( 'Barcode Type', 'woocommerce-exporter' ),
			'hover' => __( 'Barcodes for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'barcode',
			'label' => __( 'Barcode', 'woocommerce-exporter' ),
			'hover' => __( 'Barcodes for WooCommerce', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Pre-Orders - http://www.woothemes.com/products/woocommerce-pre-orders/
	if( woo_ce_detect_export_plugin( 'wc_preorders' ) ) {
		$fields[] = array(
			'name' => 'pre_orders_enabled',
			'label' => __( 'Pre-Order Enabled', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Pre-Orders', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'pre_orders_availability_date',
			'label' => __( 'Pre-Order Availability Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Pre-Orders', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'pre_orders_fee',
			'label' => __( 'Pre-Order Fee', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Pre-Orders', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'pre_orders_charge',
			'label' => __( 'Pre-Order Charge', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Pre-Orders', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Product Fees - https://wordpress.org/plugins/woocommerce-product-fees/
	if( woo_ce_detect_export_plugin( 'wc_productfees' ) ) {
		$fields[] = array(
			'name' => 'fee_name',
			'label' => __( 'Product Fee Name', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Product Fees', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'fee_amount',
			'label' => __( 'Product Fee Amount', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Product Fees', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'fee_multiplier',
			'label' => __( 'Product Fee Multiplier', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Product Fees', 'woocommerce-exporter' )
		);
	}

	// FooEvents for WooCommerce - http://www.woocommerceevents.com/
	if( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		$fields[] = array(
			'name' => 'is_event',
			'label' => __( 'Is Event', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_date',
			'label' => __( 'Event Date', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_start_time',
			'label' => __( 'Event Start Time', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_end_time',
			'label' => __( 'Event End Time', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_venue',
			'label' => __( 'Event Venue', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_gps',
			'label' => __( 'Event GPS Coordinates', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_googlemaps',
			'label' => __( 'Event Google Maps Coordinates', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_directions',
			'label' => __( 'Event Directions', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_phone',
			'label' => __( 'Event Phone', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_email',
			'label' => __( 'Event E-mail', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_ticket_logo',
			'label' => __( 'Event Ticket Logo', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_ticket_subject',
			'label' => __( 'Event Ticket Subject', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_ticket_text',
			'label' => __( 'Event Ticket Text', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_ticket_thankyou_text',
			'label' => __( 'Event Ticket Thank You Page Text', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_ticket_background_color',
			'label' => __( 'Event Ticket Background Colour', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_ticket_button_color',
			'label' => __( 'Event Ticket Background Colour', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'event_ticket_text_color',
			'label' => __( 'Event Ticket Background Colour', 'woocommerce-exporter' ),
			'hover' => __( 'FooEvents for WooCommerce', 'woocommerce-exporter' )
		);		
	}

/*
	// WooCommerce Variation Swatches and Photos - https://www.woothemes.com/products/variation-swatches-and-photos/
	// @mod - Needs implementation, limitation in fetching defaults from Term Taxonomy. Check in 2.4+
	if( woo_ce_detect_export_plugin( 'variation_swatches_photos' ) ) {
		// Do nothing
	}
*/

	// WooCommerce Uploads - https://wpfortune.com/shop/plugins/woocommerce-uploads/
	if( woo_ce_detect_export_plugin( 'wc_uploads' ) ) {
		$fields[] = array(
			'name' => 'enable_uploads',
			'label' => __( 'Enable Uploads', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Uploads', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		$fields[] = array(
			'name' => 'posr',
			'label' => __( 'Cost of Good', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Profit of Sales Report', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Product Bundles - http://www.woothemes.com/products/product-bundles/
	if( woo_ce_detect_export_plugin( 'wc_product_bundles' ) ) {
		$fields[] = array(
			'name' => 'bundled_products',
			'label' => __( 'Bundled Products', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Product Bundles', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'bundled_product_ids',
			'label' => __( 'Bundled Product ID\'s', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Product Bundles', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Min/Max Quantities - https://woocommerce.com/products/minmax-quantities/
	if( woo_ce_detect_export_plugin( 'wc_min_max' ) ) {
		$fields[] = array(
			'name' => 'minimum_quantity',
			'label' => __( 'Minimum Quantity', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Min/Max Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'maximum_quantity',
			'label' => __( 'Maximum Quantity', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Min/Max Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'group_of',
			'label' => __( 'Group of', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Min/Max Quantities', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Tab Manager - http://www.woothemes.com/products/woocommerce-tab-manager/
	if( woo_ce_detect_export_plugin( 'wc_tabmanager' ) ) {
		// Custom Product Tabs
		$custom_product_tabs = woo_ce_get_option( 'custom_product_tabs', '' );
		if( !empty( $custom_product_tabs ) ) {
			foreach( $custom_product_tabs as $custom_product_tab ) {
				if( !empty( $custom_product_tab ) ) {
					$fields[] = array(
						'name' => sprintf( 'product_tab_%s', sanitize_key( $custom_product_tab ) ),
						'label' => sprintf( __( 'Product Tab: %s', 'woocommerce-exporter' ), woo_ce_clean_export_label( $custom_product_tab ) ),
						'hover' => sprintf( __( 'Custom Product Tab: %s', 'woocommerce-exporter' ), $custom_product_tab )
					);
				}
			}
		}
		unset( $custom_product_tabs, $custom_product_tab );
	}

	// WooTabs - https://codecanyon.net/item/wootabsadd-extra-tabs-to-woocommerce-product-page/7891253
	if( woo_ce_detect_export_plugin( 'wootabs' ) ) {
		// Custom WooTabs
		$custom_wootabs = woo_ce_get_option( 'custom_wootabs', '' );
		if( !empty( $custom_wootabs ) ) {
			foreach( $custom_wootabs as $custom_wootab ) {
				if( !empty( $custom_wootab ) ) {
					$fields[] = array(
						'name' => sprintf( 'wootab_%s', sanitize_key( $custom_wootab ) ),
						'label' => sprintf( __( 'WooTab: %s', 'woocommerce-exporter' ), woo_ce_clean_export_label( $custom_wootab ) ),
						'hover' => sprintf( __( 'WooTab: %s', 'woocommerce-exporter' ), $custom_wootab )
					);
				}
			}
		}
		unset( $custom_wootabs, $custom_wootab );
	}

	// WooCommerce Tiered Pricing - http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-tiered-pricing/
	if( woo_ce_detect_export_plugin( 'ign_tiered' ) ) {

		global $wp_roles;

		// User Roles
		if( isset( $wp_roles->roles ) ) {
			asort( $wp_roles->roles );
			foreach( $wp_roles->roles as $role => $role_data ) {
				// Skip default User Roles
				if( 'ignite_level_' != substr( $role, 0, 13 ) )
					continue;
				$fields[] = array(
					'name' => sanitize_key( $role ),
					'label' => sprintf( __( '%s ($)', 'woocommerce-exporter' ), woo_ce_clean_export_label( stripslashes( $role_data['name'] ) ) ),
					'hover' => __( 'WooCommerce Tiered Pricing', 'woocommerce-exporter' )
				);
			}
			unset( $role, $role_data );
		}
	}

	// WooCommerce BookStore - http://www.wpini.com/woocommerce-bookstore-plugin/
	if( woo_ce_detect_export_plugin( 'wc_books' ) ) {
		$custom_books = ( function_exists( 'woo_book_get_custom_fields' ) ? woo_book_get_custom_fields() : false );
		if( !empty( $custom_books ) ) {
			foreach( $custom_books as $custom_book ) {
				if( !empty( $custom_book ) ) {
					$fields[] = array(
						'name' => sprintf( 'book_%s', sanitize_key( $custom_book['name'] ) ),
						'label' => $custom_book['name'],
						'hover' => __( 'WooCommerce BookStore', 'woocommerce-exporter' )
					);
				}
			}
		}
		unset( $custom_books, $custom_book );

		$fields[] = array(
			'name' => 'book_category',
			'label' => __( 'Book Category', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce BookStore', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'book_author',
			'label' => __( 'Book Author', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce BookStore', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'book_publisher',
			'label' => __( 'Book Publisher', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce BookStore', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$fields[] = array(
			'name' => 'language',
			'label' => __( 'Language', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Multilingual', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Jetpack - http://woojetpack.com/shop/wordpress-woocommerce-jetpack-plus/
	if( woo_ce_detect_export_plugin( 'woocommerce_jetpack' ) || woo_ce_detect_export_plugin( 'woocommerce_jetpack_plus' ) ) {

		// @mod - Needs alot of love in 2.4+, JetPack Plus, now Booster is huge

		// Check for Product Cost Price
		if( get_option( 'wcj_purchase_price_enabled', false ) == 'yes' ) {
			$fields[] = array(
				'name' => 'wcj_purchase_price',
				'label' => __( 'Product cost (purchase) price', 'woocommerce-jetpack' ),
				'hover' => __( 'WooCommerce Jetpack', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'wcj_purchase_price_extra',
				'label' => __( 'Extra expenses (shipping etc.)', 'woocommerce-jetpack' ),
				'hover' => __( 'WooCommerce Jetpack', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'wcj_purchase_price_affiliate_commission',
				'label' => __( 'Affiliate commission', 'woocommerce-jetpack' ),
				'hover' => __( 'WooCommerce Jetpack', 'woocommerce-exporter' )
			);
			// @mod - Let's add custom Product Cost Price fields once we get some more Booster modules sorted.
			$fields[] = array(
				'name' => 'wcj_purchase_date',
				'label' => __( '(Last) Purchase date', 'woocommerce-jetpack' ),
				'hover' => __( 'WooCommerce Jetpack', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'wcj_purchase_partner',
				'label' => __( 'Seller', 'woocommerce-jetpack' ),
				'hover' => __( 'WooCommerce Jetpack', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'wcj_purchase_info',
				'label' => __( 'Purchase info', 'woocommerce-jetpack' ),
				'hover' => __( 'WooCommerce Jetpack', 'woocommerce-exporter' )
			);
		}

/*
		// Check if Call for Price is enabled
		if( get_option( 'wcj_call_for_price_enabled', false ) == 'yes' ) {
			// Instead of the price
			$fields[] = array(
				'name' => 'wcf_price_instead',
				'label' => __( 'Instead of the ', 'woocommerce-exporter' ),
				'hover' => __( 'WooCommerce Jetpack', 'woocommerce-exporter' )
			);
			// WooCommerce Jetpack Plus fields
			if( woo_ce_detect_export_plugin( 'woocommerce_jetpack_plus' ) ) {
				// Do something
			}
		}
*/

	}

	// WooCommerce Ultimate Multi Currency Suite - https://codecanyon.net/item/woocommerce-ultimate-multi-currency-suite/11997014
	if( woo_ce_detect_export_plugin( 'wc_umcs' ) ) {
		$currencies = json_decode( get_option( 'wcumcs_available_currencies' ) );
		if( !empty( $currencies ) ) {
			$current_currency = ( function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : false );
			foreach( $currencies as $currency_code => $currency_data ) {
				// Skip the base currency
				if( $currency_code == $current_currency )
					continue;
				// Regular Price
				$fields[] = array(
					'name' => sprintf( 'wcumcs_regular_price_%s', sanitize_key( $currency_code ) ),
					'label' => sprintf( __( 'Regular Price (%s)', 'woocommerce-exporter' ), $currency_code ),
					'hover' => __( 'WooCommerce Ultimate Multi Currency Suite', 'woocommerce-exporter' )
				);
				// Sale Price
				$fields[] = array(
					'name' => sprintf( 'wcumcs_sale_price_%s', sanitize_key( $currency_code ) ),
					'label' => sprintf( __( 'Sale Price (%s)', 'woocommerce-exporter' ), $currency_code ),
					'hover' => __( 'WooCommerce Ultimate Multi Currency Suite', 'woocommerce-exporter' )
				);
			}
			unset( $currency_code, $currency_data, $current_currency );
		}
		unset( $currencies );
	}

	// Products Purchase Price for Woocommerce - https://wordpress.org/plugins/products-purchase-price-for-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_products_purchase_price' ) ) {
		$fields[] = array(
			'name' => 'purchase_price',
			'label' => __( 'Purchase Price', 'woocommerce-exporter' ),
			'hover' => __( 'Products Purchase Price for WooCommerce', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				$fields[] = array(
					'name' => sprintf( '%s_wholesale_price', $key ),
					'label' => sprintf( __( 'Wholesale Price: %s', 'woocommerce-exporter' ), $wholesale_role['roleName'] ),
					'hover' => __( 'WooCommerce Wholesale Prices', 'woocommerce-exporter' )
				);
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// WooCommerce Currency Switcher - http://aelia.co/shop/currency-switcher-woocommerce/
	if( woo_ce_detect_export_plugin( 'currency_switcher' ) ) {
		$options = get_option( 'wc_aelia_currency_switcher' );
		$currencies = ( isset( $options['enabled_currencies'] ) ? $options['enabled_currencies'] : false );
		if( !empty( $currencies ) ) {
			$woocommerce_currency = get_option( 'woocommerce_currency' );
			foreach( $currencies as $currency ) {

				// Skip the WooCommerce default currency
				if( $woocommerce_currency == $currency )
					continue;

				// Product Base Currency

				$fields[] = array(
					'name' => sprintf( 'wcae_regular_price_%s', sanitize_key( $currency ) ),
					'label' => sprintf( __( 'Regular Price (%s)', 'woocommerce-exporter' ), $currency ),
					'hover' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' )
				);
				$fields[] = array(
					'name' => sprintf( 'wcae_sale_price_%s', sanitize_key( $currency ) ),
					'label' => sprintf( __( 'Sale Price (%s)', 'woocommerce-exporter' ), $currency ),
					'hover' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' )
				);

			}
			unset( $woocommerce_currency, $currencies, $currency );
		}
		unset( $options );
	}

	// WooCommerce Show Single Variations - https://iconicwp.com/products/woocommerce-show-single-variations/
	if( woo_ce_detect_export_plugin( 'wc_show_single_variations' ) ) {
		$fields[] = array(
			'name' => 'show_search_results',
			'label' => __( 'Show in Search Results', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Show Single Variations', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'show_filtered_results',
			'label' => __( 'Show in Filtered Results', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Show Single Variations', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'show_catalog',
			'label' => __( 'Show in Catalog', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Show Single Variations', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'disable_add_to_cart',
			'label' => __( 'Disable Add to Cart', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Show Single Variations', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Deposits - https://woocommerce.com/products/woocommerce-deposits/
	if( woo_ce_detect_export_plugin( 'wc_deposits' ) ) {
		$fields[] = array(
			'name' => 'enable_deposit',
			'label' => __( 'Enable Deposit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'force_deposit',
			'label' => __( 'Force Deposit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'amount_type',
			'label' => __( 'Deposit Type', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'deposit_amount',
			'label' => __( 'Deposit Amount', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Unit of Measure - https://wordpress.org/plugins/woocommerce-unit-of-measure/
	if( woo_ce_detect_export_plugin( 'wc_unitofmeasure' ) ) {
		$fields[] = array(
			'name' => 'unit_of_measure',
			'label' => __( 'Unit of Measure', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Unit of Measure', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$fields[] = array(
			'name' => 'label_instead_of_price',
			'label' => __( 'Label instead of price', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'price_label',
			'label' => __( 'Price Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'duration',
			'label' => __( 'Duration', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'interval',
			'label' => __( 'Interval', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'padding_time',
			'label' => __( 'Padding Time', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'lead_time',
			'label' => __( 'Lead Time', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'scheduling_window',
			'label' => __( 'Scheduling Window', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'requires_confirmation',
			'label' => __( 'Requires Confirmation', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'can_be_cancelled',
			'label' => __( 'Can be Cancelled', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'cancelled_at_least',
			'label' => __( 'Cancellation Window', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'customer_timezones',
			'label' => __( 'Customer timezones', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'calendar_color',
			'label' => __( 'Calendar color', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$fields[] = array(
			'name' => 'bookable',
			'label' => __( 'Bookable', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_dates',
			'label' => __( 'Number of Dates to Select', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_duration',
			'label' => __( 'Booking Duration', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_min',
			'label' => __( 'Minimum Booking Duration', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'booking_max',
			'label' => __( 'Maximum Booking Duration', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'first_available_date',
			'label' => __( 'First Available Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Advanced Product Quantities - http://www.wpbackoffice.com/plugins/woocommerce-incremental-product-quantities/
	if( woo_ce_detect_export_plugin( 'wc_advanced_quantities' ) ) {
		$fields[] = array(
			'name' => 'deactivate_quantity_rules',
			'label' => __( 'De-activate Quantity Rules', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'override_quantity_rules',
			'label' => __( 'Override Quantity Rules', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'step_value',
			'label' => __( 'Step Value', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'minimum_quantity',
			'label' => __( 'Minimum Quantity', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'maximum_quantity',
			'label' => __( 'Maximum Quantity', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'oos_minimum',
			'label' => __( 'Out of Stock Minimum', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'oos_maximum',
			'label' => __( 'Out of Stock Maximum', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Chained Products - https://woocommerce.com/products/chained-products/
	if( woo_ce_detect_export_plugin( 'wc_chained_products' ) ) {
		$fields[] = array(
			'name' => 'chained_products',
			'label' => __( 'Chained Products', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Chained Products', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'chained_products_ids',
			'label' => __( 'Chained Product IDs', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Chained Products', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'chained_products_names',
			'label' => __( 'Chained Product Names', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Chained Products', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'chained_products_skus',
			'label' => __( 'Chained Product SKUs', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Chained Products', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'chained_products_units',
			'label' => __( 'Chained Product Units', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Chained Products', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'chained_products_manage_stock',
			'label' => __( 'Manage Stock for Chained Products', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Chained Products', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Sample - https://wordpress.org/plugins/woocommerce-sample/
	if( woo_ce_detect_export_plugin( 'wc_sample' ) ) {
		$fields[] = array(
			'name' => 'enable_sample',
			'label' => __( 'Enable Sample', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Sample', 'woocommerce-exporter' )
		);

		// WooCommerce Chained Products - https://woocommerce.com/products/chained-products/
		if( woo_ce_detect_export_plugin( 'wc_chained_products' ) ) {
			$fields[] = array(
				'name' => 'enable_sample_chained',
				'label' => __( 'Enable Sample on Chained Products', 'woocommerce-exporter' ),
				'hover' => __( 'WooCommerce Sample', 'woocommerce-exporter' )
			);
		}
		$fields[] = array(
			'name' => 'sample_shipping_mode',
			'label' => __( 'Sample Shipping Mode', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Sample', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'sample_shipping',
			'label' => __( 'Sample Shipping', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Sample', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'sample_price_mode',
			'label' => __( 'Sample Price Mode', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Sample', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'sample_price',
			'label' => __( 'Sample Price', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Sample', 'woocommerce-exporter' )
		);
	}

	// AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO - https://www.weareag.co.uk/product/woocommerce-barcodeisbn-amazon-asin-pro/
	if( woo_ce_detect_export_plugin( 'wc_ag_barcode_pro' ) ) {
		$fields[] = array(
			'name' => 'barcode',
			'label' => __( 'Barcode', 'woocommerce-exporter' ),
			'hover' => __( 'AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'isbn',
			'label' => __( 'ISBN', 'woocommerce-exporter' ),
			'hover' => __( 'AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'amazon',
			'label' => __( 'ASIN', 'woocommerce-exporter' ),
			'hover' => __( 'AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO', 'woocommerce-exporter' )
		);
	}

	// N-Media WooCommerce Personalized Product Meta Manager - https://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/
	// PPOM for WooCommerce - https://wordpress.org/plugins/woocommerce-product-addon/
	if(
		woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) || 
		woo_ce_detect_export_plugin( 'wc_ppom' )
	) {
		$fields[] = array(
			'name' => 'select_personalized_meta',
			'label' => __( 'Select Personalized Meta', 'woocommerce-exporter' ),
			'hover' => __( 'N-Media WooCommerce Personalized Product Meta Manager', 'woocommerce-exporter' )
		);
	}

	// SEO Squirrly - https://wordpress.org/plugins/squirrly-seo/
	if( woo_ce_detect_export_plugin( 'seo_squirrly' ) ) {
		$fields[] = array(
			'name' => 'sq_keywords',
			'label' => __( 'Keywords', 'woocommerce-exporter' ),
			'hover' => __( 'SEO Squirrly', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Measurement Price Calculator - http://www.woocommerce.com/products/measurement-price-calculator/
	if( woo_ce_detect_export_plugin( 'wc_measurement_price_calc' ) ) {

		$fields[] = array(
			'name' => 'area',
			'label' => __( 'Area', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'volume',
			'label' => __( 'Volume', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_type',
			'label' => __( 'Measurement', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);

		// Dimensions
		$fields[] = array(
			'name' => 'measurement_dimension_pricing',
			'label' => __( 'Dimension: Show Product Price Per Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_dimension_pricing_label',
			'label' => __( 'Dimension: Pricing Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_dimension_pricing_unit',
			'label' => __( 'Dimension: Pricing Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);

		// Area
		$fields[] = array(
			'name' => 'measurement_area_pricing',
			'label' => __( 'Area: Show Product Price Per Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_pricing_label',
			'label' => __( 'Area: Pricing Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_pricing_unit',
			'label' => __( 'Area: Pricing Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_area_label',
			'label' => __( 'Area: Area Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_area_unit',
			'label' => __( 'Area: Area Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_editable',
			'label' => __( 'Area: Editable', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);

		// Area (LxW)
		$fields[] = array(
			'name' => 'measurement_area_dimension_pricing',
			'label' => __( 'Area Dimension: Show Product Price Per Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_pricing_label',
			'label' => __( 'Area Dimension: Pricing Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_pricing_unit',
			'label' => __( 'Area Dimension: Pricing Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_calculator',
			'label' => __( 'Area Dimension: Calculated Price', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_weight',
			'label' => __( 'Area Dimension: Calculated Weight', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_inventory',
			'label' => __( 'Area Dimension: Calculated Inventory', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_overage',
			'label' => __( 'Area Dimension: Add Overage', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_length_label',
			'label' => __( 'Area Dimension: Length Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_length_unit',
			'label' => __( 'Area Dimension: Length Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_length_input',
			'label' => __( 'Area Dimension: Length Input', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_length_options',
			'label' => __( 'Area Dimension: Length Options', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_width_label',
			'label' => __( 'Area Dimension: Width Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_width_unit',
			'label' => __( 'Area Dimension: Width Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_width_input',
			'label' => __( 'Area Dimension: Width Input', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_dimension_width_options',
			'label' => __( 'Area Dimension: Width Options', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);

		// Perimeter
		$fields[] = array(
			'name' => 'measurement_area_linear_pricing',
			'label' => __( 'Perimeter: Show Product Price Per Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_linear_pricing_label',
			'label' => __( 'Perimeter: Pricing Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_linear_pricing_unit',
			'label' => __( 'Perimeter: Pricing Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_linear_length_label',
			'label' => __( 'Perimeter: Length Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_linear_length_unit',
			'label' => __( 'Perimeter: Length Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_linear_width_label',
			'label' => __( 'Perimeter: Width Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'measurement_area_linear_width_unit',
			'label' => __( 'Perimete: Width Unit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' )
		);

		// Surface Area
		// Volume
		// Volume (LxWxH)
		// Volume (AxH)
		// Weight
		// Room Walls

	}

	// Google Customer Reviews for WooCommerce - https://wordpress.org/plugins/ecr-google-customer-reviews/
	if( woo_ce_detect_export_plugin( 'wc_ecr_gcr' ) ) {
		$fields[] = array(
			'name' => 'gtin',
			'label' => __( 'GTIN', 'woocommerce-exporter' ),
			'hover' => __( 'Google Customer Reviews for WooCommerce', 'woocommerce-exporter' )
		);
	}

	// WooCommerce UPC, EAN, and ISBN - https://wordpress.org/plugins/woo-add-gtin/
	if( woo_ce_detect_export_plugin( 'woo_add_gtin' ) ) {
		$fields[] = array(
			'name' => 'gtin',
			'label' => __( 'GTIN', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce UPC, EAN, and ISBN', 'woocommerce-exporter' )
		);
	}

	// WP-Lister Pro for Amazon - https://www.wplab.com/plugins/wp-lister-for-amazon/
	if( woo_ce_detect_export_plugin( 'wpla_wplister' ) ) {
		$fields[] = array(
			'name' => 'wpl_amazon_product_id',
			'label' => __( 'Product ID', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_id_type',
			'label' => __( 'ID Type', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_title',
			'label' => __( 'Listing Title', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		if( get_option( 'wpla_enable_custom_product_prices', 1 ) != 0 ) {
			$fields[] = array(
				'name' => 'wpl_amazon_price',
				'label' => __( 'Amazon Price', 'woocommerce-exporter' ),
				'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
			);
		}
		if( get_option( 'wpla_enable_minmax_product_prices', 0 ) != 0 ) {
			$fields[] = array(
				'name' => 'wpl_amazon_minimum_price',
				'label' => __( 'Minimum Price', 'woocommerce-exporter' ),
				'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'wpl_amazon_maximum_price',
				'label' => __( 'Maximum Price', 'woocommerce-exporter' ),
				'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
			);
		}
		if( get_option( 'wpla_enable_item_condition_fields', 2 ) != 0 ) {
			$fields[] = array(
				'name' => 'wpl_amazon_condition_type',
				'label' => __( 'Item Condition', 'woocommerce-exporter' ),
				'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'wpl_amazon_condition_note',
				'label' => __( 'Condition Note', 'woocommerce-exporter' ),
				'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
			);
		}
		$fields[] = array(
			'name' => 'wpl_amazon_bullet_point1',
			'label' => __( 'Bullet Point 1', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_bullet_point2',
			'label' => __( 'Bullet Point 2', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_bullet_point3',
			'label' => __( 'Bullet Point 3', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_bullet_point4',
			'label' => __( 'Bullet Point 4', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_bullet_point5',
			'label' => __( 'Bullet Point 5', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_generic_keywords1',
			'label' => __( 'Keywords 1', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_generic_keywords2',
			'label' => __( 'Keywords 2', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_generic_keywords3',
			'label' => __( 'Keywords 3', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_generic_keywords4',
			'label' => __( 'Keywords 4', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_generic_keywords5',
			'label' => __( 'Keywords 5', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_product_description',
			'label' => __( 'Custom Product Description', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_amazon_asin',
			'label' => __( 'ASIN', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for Amazon', 'woocommerce-exporter' )
		);
	}

	// WP-Lister Pro for eBay - https://www.wplab.com/plugins/wp-lister/
	if( woo_ce_detect_export_plugin( 'wple_wplister' ) ) {
		$fields[] = array(
			'name' => 'wpl_ebay_title',
			'label' => __( 'Listing Title', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_ebay_subtitle',
			'label' => __( 'Listing Subtitle', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_ebay_start_price',
			'label' => __( 'Start Price', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_ebay_auction_type',
			'label' => __( 'Listing Type', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_ebay_listing_duration',
			'label' => __( 'Listing Duration', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_ebay_condition',
			'label' => __( 'Condition', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpl_ebay_condition_description',
			'label' => __( 'Condition Description', 'woocommerce-exporter' ),
			'hover' => __( 'WP-Lister Pro for eBay', 'woocommerce-exporter' )
		);
	}

	// AliDropship for WooCommerce - https://alidropship.com/
	if( woo_ce_detect_export_plugin( 'alidropship' ) ) {
		$fields[] = array(
			'name' => 'ali_product_id',
			'label' => __( 'Product ID', 'woocommerce-exporter' ),
			'hover' => __( 'AliDropship for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ali_product_url',
			'label' => __( 'Product URL', 'woocommerce-exporter' ),
			'hover' => __( 'AliDropship for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ali_store_url',
			'label' => __( 'Store URL', 'woocommerce-exporter' ),
			'hover' => __( 'AliDropship for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ali_store_name',
			'label' => __( 'Store Name', 'woocommerce-exporter' ),
			'hover' => __( 'AliDropship for WooCommerce', 'woocommerce-exporter' )
		);
	}

	// WooCommerce All Discounts Lite - https://wordpress.org/plugins/woo-advanced-discounts/
	if( woo_ce_detect_export_plugin( 'wc_alldiscounts_lite' ) ) {
		$fields[] = array(
			'name' => 'qbp_enable',
			'label' => __( 'Enable Quantity Based Pricing', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce All Discounts Lite', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'qbp_discount_type',
			'label' => __( 'Discount Type', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce All Discounts Lite', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'qbp_rules_type',
			'label' => __( 'Rules Type', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce All Discounts Lite', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'qbp_rules',
			'label' => __( 'Rules', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce All Discounts Lite', 'woocommerce-exporter' )
		);
	}

	// ATUM Inventory Management for WooCommerce - https://wordpress.org/plugins/atum-stock-manager-for-woocommerce/
	if( woo_ce_detect_export_plugin( 'atum_inventory' ) ) {
		$fields[] = array(
			'name' => 'purchase_price',
			'label' => __( 'Purchase Price', 'woocommerce-exporter' ),
			'hover' => __( 'ATUM Inventory Management for WooCommerce', 'woocommerce-exporter' )
		);
	}

	// Custom Product meta
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) ) {
				$fields[] = array(
					'name' => $custom_product,
					'label' => woo_ce_clean_export_label( $custom_product ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_product_fields_custom_product_hover', '%s: %s' ), __( 'Custom Product', 'woocommerce-exporter' ), $custom_product )
				);
			}
		}
	}
	unset( $custom_products, $custom_product );

	return $fields;

}
add_filter( 'woo_ce_product_fields', 'woo_ce_extend_product_fields' );

function woo_ce_extend_product_item( $product, $product_id, $_product = false ) {

	global $export;

	if( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {

		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) ) {
			woo_ce_error_log( sprintf( 'Debug: %s', 'post_id: ' . $product_id ) );
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_product_item(): woo_ce_enable_product_attributes...' ) );
			woo_ce_error_log( sprintf( 'Debug: %s', 'product->post_type: ' . $product->post_type ) );
		}

		// Scan for global Attributes first
		$attributes = woo_ce_get_product_attributes();
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'attributes: ' . print_r( $attributes, true ) ) );

		if( !empty( $attributes ) && $product->post_type == 'product_variation' ) {

			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
				woo_ce_error_log( sprintf( 'Debug: %s', 'We are dealing with a single Variation Product Type' ) );

			// We're dealing with a single Variation, strap yourself in.
			foreach( $attributes as $attribute ) {

				if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
					woo_ce_error_log( sprintf( 'Debug: %s', 'Scanning for Product Attribute: ' . $attribute->attribute_name ) );

				if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
					$attribute_value = get_post_meta( $product_id, sprintf( 'attribute_%s', $attribute->attribute_name ), true );
					// Check incase the Attribute is saved in the legacy Post meta name format
					if( $attribute_value == false ) {
						// Desperate times call for desperate measures...
						$attribute_value = get_post_meta( $product_id, sprintf( 'attribute_pa_%s', $attribute->attribute_name ), true );
					}
				} else {
					$attribute_value = get_post_meta( $product_id, sprintf( 'attribute_pa_%s', $attribute->attribute_name ), true );
				}
				if( !empty( $attribute_value ) ) {
					if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) ) {
						woo_ce_error_log( sprintf( 'Debug: %s', 'Found Attribute assigned to Variation; checking Attribute: ' . $attribute->attribute_name ) );
						woo_ce_error_log( sprintf( 'Debug: %s', 'attribute: ' . print_r( $attribute, true ) ) );
						woo_ce_error_log( sprintf( 'Debug: %s', 'attribute_value: ' . $attribute_value ) );
					}
					$term_id = term_exists( $attribute_value, sprintf( 'pa_%s', $attribute->attribute_name ) );
					if( $term_id !== 0 && $term_id !== null && !is_wp_error( $term_id ) ) {
						$term = get_term( $term_id['term_id'], sprintf( 'pa_%s', $attribute->attribute_name ) );
						$attribute_value = $term->name;
						unset( $term );
						if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
							woo_ce_error_log( sprintf( 'Debug: %s', 'Matched attribute_value to Attribute Term, attribute_value: ' . $attribute_value ) );
					} else {
						if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) ) {
							woo_ce_error_log( sprintf( 'Debug: %s', 'Could not attribute_value to Attribute Term, must be a custom Attribute' ) );
							woo_ce_error_log( sprintf( 'Debug: %s', 'attribute_value: ' . $attribute_value ) );
						}
					}
					unset( $term_id );
				} else {
					if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
						woo_ce_error_log( sprintf( 'Debug: %s', 'Could not find Attribute assigned to this Variation' ) );
				}
				$product->{sprintf( 'attribute_%s', $attribute->attribute_name )} = $attribute_value;
				unset( $attribute_value );

			}

		} else {

			// This is either the Variable (Variation Parent) or a Simple Product Type, scan for global and custom Attributes
			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
				woo_ce_error_log( sprintf( 'Debug: %s', 'We are dealing with a Variable or Simple Product Type' ) );

			$product->attributes = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );
			if( !empty( $product->attributes ) ) {
				$default_attributes = maybe_unserialize( get_post_meta( $product_id, '_default_attributes', true ) );
				$product->default_attributes = '';
				// Check for Taxonomy-based attributes

				if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
					woo_ce_error_log( sprintf( 'Debug: %s', 'Detected $product->attributes: ' . print_r( $product->attributes, true ) ) );

				if( !empty( $attributes ) ) {
					foreach( $attributes as $attribute ) {

						if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
							woo_ce_error_log( sprintf( 'Debug: %s', 'Scanning for Product Attribute: ' . $attribute->attribute_name ) );

						if( !empty( $default_attributes ) && is_array( $default_attributes ) ) {
							if( array_key_exists( sprintf( 'pa_%s', $attribute->attribute_name ), $default_attributes ) ) {
								$product->default_attributes .= $attribute->attribute_label . ': ' . woo_ce_get_product_attribute_name_by_slug( $default_attributes[sprintf( 'pa_%s', $attribute->attribute_name )], sprintf( 'pa_%s', $attribute->attribute_name ) ) . "|";
								unset( $default_attributes[sprintf( 'pa_%s', $attribute->attribute_name )], $default_attributes[$attribute->attribute_name] );
							}
						}

						// Time to do some WooCommerce 2.6 vs 3.0 magic
						if( isset( $product->attributes[sprintf( 'pa_%s', $attribute->attribute_name )] ) || isset( $product->attributes[$attribute->attribute_name] ) ) {
							// Check for WooCommerce 2.6
							if( isset( $product->attributes[sprintf( 'pa_%s', $attribute->attribute_name )] ) ) {
								$args = array(
									'attribute' => $product->attributes[sprintf( 'pa_%s', $attribute->attribute_name )],
									'type' => 'product'
								);
								// Advanced Product Attribute support can be disabled where FORM limits are being hit or just cluttering the Quick Export/Export Template screen
								if(
									apply_filters( 'woo_ce_enable_advanced_product_attributes', false ) ||
									woo_ce_detect_export_plugin( 'wc_product_importer_deluxe' )
								) {
									$product->{sprintf( 'attribute_%s_position', $attribute->attribute_name )} = $product->attributes[sprintf( 'pa_%s', $attribute->attribute_name )]['position'];
									$product->{sprintf( 'attribute_%s_visible', $attribute->attribute_name )} = woo_ce_format_switch( $product->attributes[sprintf( 'pa_%s', $attribute->attribute_name )]['is_visible'] );
									$product->{sprintf( 'attribute_%s_variation', $attribute->attribute_name )} = woo_ce_format_switch( $product->attributes[sprintf( 'pa_%s', $attribute->attribute_name )]['is_variation'] );
									$product->{sprintf( 'attribute_%s_taxonomy', $attribute->attribute_name )} = woo_ce_format_switch( $product->attributes[sprintf( 'pa_%s', $attribute->attribute_name )]['is_taxonomy'] );
								}
							}
							// Check for WooCommerce 3.0+
							if( isset( $product->attributes[$attribute->attribute_name] ) ) {
								$args = array(
									'attribute' => $product->attributes[$attribute->attribute_name],
									'type' => 'product'
								);
								// Advanced Product Attribute support can be disabled where FORM limits are being hit or just cluttering the Quick Export/Export Template screen
								if(
									apply_filters( 'woo_ce_enable_advanced_product_attributes', false ) ||
									woo_ce_detect_export_plugin( 'wc_product_importer_deluxe' )
								) {
									$product->{sprintf( 'attribute_%s_position', $attribute->attribute_name )} = $product->attributes[$attribute->attribute_name]['position'];
									$product->{sprintf( 'attribute_%s_visible', $attribute->attribute_name )} = woo_ce_format_switch( $product->attributes[$attribute->attribute_name]['is_visible'] );
									$product->{sprintf( 'attribute_%s_variation', $attribute->attribute_name )} = woo_ce_format_switch( $product->attributes[$attribute->attribute_name]['is_variation'] );
									$product->{sprintf( 'attribute_%s_taxonomy', $attribute->attribute_name )} = woo_ce_format_switch( $product->attributes[$attribute->attribute_name]['is_taxonomy'] );
								}
							}
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
								woo_ce_error_log( sprintf( 'Debug: %s', 'Found Attribute assigned to Product; checking Attribute: ' . $attribute->attribute_name ) );
							$product->{sprintf( 'attribute_%s', $attribute->attribute_name )} = woo_ce_get_product_assoc_attributes( $product_id, $args );
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
								woo_ce_error_log( sprintf( 'Debug: %s', 'response: ' . $product->{sprintf( 'attribute_%s', $attribute->attribute_name )} ) );
							if( apply_filters( 'woo_ce_enable_product_attribute_quantities', false ) )
								$product->{sprintf( 'attribute_%s_quantity', $attribute->attribute_name )} = woo_ce_get_product_assoc_attribute_quantities( $product_id, $args );
						} else {
							$args = array(
								'attribute' => $attribute,
								'type' => 'global'
							);
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
								woo_ce_error_log( sprintf( 'Debug: %s', 'Could not find Attribute assigned to Product; checking Global Attribute: ' . $attribute->attribute_name ) );
							$product->{sprintf( 'attribute_%s', $attribute->attribute_name )} = woo_ce_get_product_assoc_attributes( $product_id, $attribute, 'global' );
						}

					}
				}

				// Check for per-Product attributes (custom)
				foreach( $product->attributes as $attribute_key => $attribute ) {
					if( !empty( $default_attributes ) && is_array( $default_attributes ) ) {
						if( array_key_exists( $attribute_key, $default_attributes ) ) {
							$product->default_attributes .= $attribute['name'] . ': ' . $default_attributes[$attribute_key] . "|";
						}
					}
					if( $attribute['is_taxonomy'] == 0 ) {
						if( !isset( $product->{sprintf( 'attribute_%s', $attribute_key )} ) )
							$product->{sprintf( 'attribute_%s', $attribute_key )} = $attribute['value'];
					}
				}
				unset( $default_attributes );
				if( !empty( $product->default_attributes ) )
					$product->default_attributes = substr( $product->default_attributes, 0, -1 );
			}

		}
	}

	// WordPress MultiSite
	if( is_multisite() ) {
		$product->blog_id = get_current_blog_id();
	}

	// Term Taxonomies
	$term_taxonomies = woo_ce_get_product_custom_term_taxonomies();
	if( !empty( $term_taxonomies ) ) {
		foreach( $term_taxonomies as $key => $term_taxonomy ) {
			// Check if we are dealing with a hierachial Term Taxonomy
			if( $term_taxonomy->hierarchical )
				$product->{sprintf( 'term_taxonomy_%s', $key )} = woo_ce_get_product_assoc_categories( $product_id, $product->parent_id, $key );
			else
				$product->{sprintf( 'term_taxonomy_%s', $key )} = woo_ce_get_product_assoc_tags( $product_id, $key );
		}
	}
	unset( $term_taxonomies, $term_taxonomy );

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			$meta = maybe_unserialize( get_post_meta( $product_id, '_product_addons', true ) );
			if( !empty( $meta ) ) {
				foreach( $product_addons as $product_addon ) {
					if( !empty( $product_addon ) ) {
						foreach( $meta as $product_addon_item ) {
							// Check for a matching Product Add-on
							if( $product_addon->post_name == $product_addon_item['name'] ) {
								// Check the Product Add-on type
								switch( $product_addon_item['type'] ) {

									// Type: Checkbox
									case 'checkbox':
										// Check if the Product Add-on has Options
										if( !empty( $product_addon_item['options'] ) ) {
											$product->{sprintf( 'product_addon_%s', $product_addon->post_name )} = '';
											$size = count( $product_addon_item['options'] );
											for( $i = 0; $i < $size; $i++ ) {
												$product->{sprintf( 'product_addon_%s', $product_addon->post_name )} .= $product_addon_item['options'][$i]['label'];
												if( !empty( $product_addon_item['options'][$i]['label'] ) )
													$product_addon_item['options'][$i]['price'] .= sprintf( ': %s', $product_addon_item['options'][$i]['price'] );
												$product->{sprintf( 'product_addon_%s', $product_addon->post_name )} .= $export->category_separator;
											}
											$product->{sprintf( 'product_addon_%s', $product_addon->post_name )} = substr( $product->{sprintf( 'product_addon_%s', $product_addon->post_name )}, 0, -1 );
										}
										break;

									case 'custom':
									default:
										$product->{sprintf( 'product_addon_%s', $product_addon->post_name )} = apply_filters( 'woo_ce_extend_product_fields_product_addons_item', false, $product_addon_item['options'], $product_id, $product_addon_item, $product_addon );
										break;

								}
								// Skip the rest as we've found our match
								break;
							}
						}
					}
				}
				unset( $product_addon_item, $meta );
			}
		}
		unset( $product_addons, $product_addon );
	}

	// WooCommerce Google Product Feed - http://plugins.leewillis.co.uk/downloads/wp-e-commerce-product-feeds/
	if( woo_ce_detect_export_plugin( 'gpf' ) ) {
		$gpf_data = get_post_meta( $product_id, '_woocommerce_gpf_data', true );
		$product->gpf_availability = ( isset( $gpf_data['availability'] ) ? woo_ce_format_gpf_availability( $gpf_data['availability'] ) : '' );
		$product->gpf_condition = ( isset( $gpf_data['condition'] ) ? woo_ce_format_gpf_condition( $gpf_data['condition'] ) : '' );
		$product->gpf_brand = ( isset( $gpf_data['brand'] ) ? $gpf_data['brand'] : '' );
		$product->gpf_product_type = ( isset( $gpf_data['product_type'] ) ? $gpf_data['product_type'] : '' );
		$product->gpf_google_product_category = ( isset( $gpf_data['google_product_category'] ) ? $gpf_data['google_product_category'] : '' );
		$product->gpf_gtin = ( isset( $gpf_data['gtin'] ) ? $gpf_data['gtin'] : '' );
		$product->gpf_mpn = ( isset( $gpf_data['mpn'] ) ? $gpf_data['mpn'] : '' );
		$product->gpf_gender = ( isset( $gpf_data['gender'] ) ? $gpf_data['gender'] : '' );
		$product->gpf_age_group = ( isset( $gpf_data['age_group'] ) ? $gpf_data['age_group'] : '' );
		$product->gpf_color = ( isset( $gpf_data['color'] ) ? $gpf_data['color'] : '' );
		$product->gpf_size = ( isset( $gpf_data['size'] ) ? $gpf_data['size'] : '' );
		unset( $gpf_data );
	}

	// All in One SEO Pack - http://wordpress.org/extend/plugins/all-in-one-seo-pack/
	if( woo_ce_detect_export_plugin( 'aioseop' ) ) {
		$product->aioseop_keywords = get_post_meta( $product_id, '_aioseop_keywords', true );
		$product->aioseop_description = get_post_meta( $product_id, '_aioseop_description', true );
		$product->aioseop_title = get_post_meta( $product_id, '_aioseop_title', true );
		$product->aioseop_title_attributes = get_post_meta( $product_id, '_aioseop_titleatr', true );
		$product->aioseop_menu_label = get_post_meta( $product_id, '_aioseop_menulabel', true );
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$product->wpseo_focuskw = get_post_meta( $product_id, '_yoast_wpseo_focuskw', true );
		$product->wpseo_metadesc = get_post_meta( $product_id, '_yoast_wpseo_metadesc', true );
		$product->wpseo_title = get_post_meta( $product_id, '_yoast_wpseo_title', true );
		$product->wpseo_noindex = woo_ce_format_wpseo_noindex( get_post_meta( $product_id, '_yoast_wpseo_meta-robots-noindex', true ) );
		$product->wpseo_follow = woo_ce_format_wpseo_follow( get_post_meta( $product_id, '_yoast_wpseo_meta-robots-nofollow', true ) );
		$product->wpseo_googleplus_description = get_post_meta( $product_id, '_yoast_wpseo_google-plus-description', true );
		$product->wpseo_opengraph_title = get_post_meta( $product_id, '_yoast_wpseo_opengraph-title', true );
		$product->wpseo_opengraph_description = get_post_meta( $product_id, '_yoast_wpseo_opengraph-description', true );
		$product->wpseo_opengraph_image = get_post_meta( $product_id, '_yoast_wpseo_opengraph-image', true );
		$product->wpseo_twitter_title = get_post_meta( $product_id, '_yoast_wpseo_twitter-title', true );
		$product->wpseo_twitter_description = get_post_meta( $product_id, '_yoast_wpseo_twitter-description', true );
		$product->wpseo_twitter_image = get_post_meta( $product_id, '_yoast_wpseo_twitter-image', true );
		$product->wpseo_canonical = get_post_meta( $product_id, '_yoast_wpseo_canonical', true );
	}

	// Ultimate SEO - http://wordpress.org/plugins/seo-ultimate/
	if( woo_ce_detect_export_plugin( 'ultimate_seo' ) ) {
		$product->useo_meta_title = get_post_meta( $product_id, '_su_title', true );
		$product->useo_meta_description = get_post_meta( $product_id, '_su_description', true );
		$product->useo_meta_keywords = get_post_meta( $product_id, '_su_keywords', true );
		$product->useo_social_title = get_post_meta( $product_id, '_su_og_title', true );
		$product->useo_social_description = get_post_meta( $product_id, '_su_og_description', true );
		$product->useo_meta_noindex = get_post_meta( $product_id, '_su_meta_robots_noindex', true );
		$product->useo_meta_noautolinks = get_post_meta( $product_id, '_su_disable_autolinks', true );
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() ) {
		$product->brands = woo_ce_get_product_assoc_brands( $product_id, $product->parent_id );
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		$product->msrp = get_post_meta( $product_id, '_msrp_price', true );
		if( $product->msrp == false && $product->post_type == 'product_variation' )
			$product->msrp = get_post_meta( $product_id, '_msrp', true );
		// Check that a valid price has been provided
		if( isset( $product->msrp ) && $product->msrp != '' )
			$product->msrp = woo_ce_format_price( $product->msrp );
	}

	// WooCommerce Germanized Pro - https://www.vendidero.de/woocommerce-germanized
	if( woo_ce_detect_export_plugin( 'wc_germanized_pro' ) ) {
		// Check for Product Units
		if( get_option( 'woocommerce_gzd_display_listings_product_units' ) == 'yes' ) {
			$product->sale_price_label = get_post_meta( $product_id, '_sale_price_label', true );
			$product->sale_price_regular_label = get_post_meta( $product_id, '_sale_price_regular_label', true );
			$product->unit = get_post_meta( $product_id, '_unit', true );
			$product->unit_product = get_post_meta( $product_id, '_unit_product', true );
			$product->unit_base = get_post_meta( $product_id, '_unit_base', true );
			$calculation = get_post_meta( $product_id, '_unit_price_auto', true );
			// if( $calculation == 'yes' ) {
			if( false ) {
				$product_base = $product->unit_base;
				$base = $product->unit_base;
				if( empty( $product->unit_product ) )
					$base = 1;
				else
					$product_base = $product->unit_product;
				$product->unit_price_regular = wc_format_decimal( ( $_product->get_regular_price() / $product_base ) * $base, wc_get_price_decimals() );
				$product->unit_price_sale = wc_format_decimal( ( $_product->get_sale_price() / $product_base ) * $base, wc_get_price_decimals() );
				unset( $product_base, $unit );
			} else {
				$product->unit_price_regular = get_post_meta( $product_id, '_unit_price_regular', true );
				$product->unit_price_sale = get_post_meta( $product_id, '_unit_price_sale', true );
			}
			$product->unit_price_regular_display = sprintf( '%s %s / %s', $product->unit_price_regular, get_woocommerce_currency_symbol(), $product->unit );
			if( $product->unit_price_regular != '' )
				$product->unit_price_regular = woo_ce_format_price( $product->unit_price_regular );
			if( $product->unit_price_sale != '' )
				$product->unit_price_sale = woo_ce_format_price( $product->unit_price_sale );
			$product->unit_price_auto = woo_ce_format_switch( $calculation );
			unset( $calculation );
		}
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		$product->cost_of_goods = get_post_meta( $product_id, '_wc_cog_cost', true );
		if( strtolower( $product->type ) == 'variable' )
			$product->cost_of_goods = get_post_meta( $product_id, '_wc_cog_cost_variable', true );
		// Check if this is a Variation and the Cost of Goods is empty
		if( $product->post_type == 'product_variation' && $product->cost_of_goods == '' )
			$product->cost_of_goods = get_post_meta( $product->parent_id, '_wc_cog_cost_variable', true );
		if( isset( $product->cost_of_goods ) && $product->cost_of_goods != '' )
			$product->cost_of_goods = woo_ce_format_price( $product->cost_of_goods );
	}

	// Per-Product Shipping - http://www.woothemes.com/products/per-product-shipping/
	if( woo_ce_detect_export_plugin( 'per_product_shipping' ) ) {
		$product->per_product_shipping = woo_ce_format_switch( get_post_meta( $product_id, '_per_product_shipping', true ) );
		$shipping_rules = woo_ce_get_product_assoc_per_product_shipping_rules( $product_id );
		if( !empty( $shipping_rules ) ) {
			$product->per_product_shipping_country = $shipping_rules['country'];
			$product->per_product_shipping_state = $shipping_rules['state'];
			$product->per_product_shipping_postcode = $shipping_rules['postcode'];
			$product->per_product_shipping_cost = $shipping_rules['cost'];
			$product->per_product_shipping_item_cost = $shipping_rules['item_cost'];
			$product->per_product_shipping_order = $shipping_rules['order'];
		}
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) ) {
		$product->vendors = woo_ce_get_product_assoc_product_vendors( $product_id, $product->parent_id );
		$product->vendor_ids = woo_ce_get_product_assoc_product_vendors( $product_id, $product->parent_id, 'term_id' );
		$product->vendor_commission = woo_ce_get_product_assoc_product_vendor_commission( $product_id, $product->vendor_ids );
	}

	// WC Vendors - http://wcvendors.com
	if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
		$product->vendor = ( !empty( $product->post_author ) ? woo_ce_get_username( $product->post_author ) : false );
		$product->vendor_commission_rate = get_post_meta( $product_id, 'pv_commission_rate', true );
	}

	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		$term_taxonomy = 'yith_shop_vendor';
		$product_vendors = wp_get_post_terms( $product_id, $term_taxonomy );
		if( !empty( $product_vendors ) ) {
			if( !is_wp_error( $product_vendors ) ) {
				foreach( $product_vendors as $product_vendor ) {
					$product->vendor = $product_vendor->name;
					$product->vendor_commission_rate = get_post_meta( $product_id, '_product_commission', true );
					break;
				}
			}
		}
		unset( $product_vendors, $product_vendor );
	}

	// WC Marketplace - https://wc-marketplace.com/
	if( woo_ce_detect_export_plugin( 'wc_marketplace' ) ) {
		$term_taxonomy = 'dc_vendor_shop';
		$product_vendors = wp_get_post_terms( $product_id, $term_taxonomy );
		if( !empty( $product_vendors ) ) {
			if( !is_wp_error( $product_vendors ) ) {
				foreach( $product_vendors as $product_vendor ) {
					$product->vendor = $product_vendor->name;
					$product->vendor_commission = get_post_meta( $product_id, '_commission_per_product', true );
					break;
				}
			}
		}
		unset( $product_vendors, $product_vendor );
	}

	// WooCommerce Wholesale Pricing - http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-wholesale-pricing/
	if( woo_ce_detect_export_plugin( 'wholesale_pricing' ) ) {
		$product->wholesale_price = woo_ce_format_price( get_post_meta( $product_id, 'wholesale_price', true ) );
		$product->wholesale_price_text = get_post_meta( $product_id, 'wholesale_price_text', true );
	}

	// WooCommerce Currency Switcher - http://aelia.co/shop/currency-switcher-woocommerce/
	if( woo_ce_detect_export_plugin( 'currency_switcher' ) ) {
		$options = get_option( 'wc_aelia_currency_switcher' );
		$currencies = ( isset( $options['enabled_currencies'] ) ? $options['enabled_currencies'] : false );
		if( !empty( $currencies ) ) {
			$woocommerce_currency = get_option( 'woocommerce_currency' );
			// Check if we are dealing with a Variation
			if( $product->post_type == 'product_variation' ) {
				$regular_price = json_decode( get_post_meta( $product_id, 'variable_regular_currency_prices', true ) );
				$sale_price = json_decode( get_post_meta( $product_id, 'variable_sale_currency_prices', true ) );
			} else {
				$regular_price = json_decode( get_post_meta( $product_id, '_regular_currency_prices', true ) );
				$sale_price = json_decode( get_post_meta( $product_id, '_sale_currency_prices', true ) );
			}
			foreach( $currencies as $currency ) {

				// Skip the WooCommerce default currency
				if( $woocommerce_currency == $currency )
					continue;

				$product->{sprintf( 'wcae_regular_price_%s', sanitize_key( $currency ) )} = ( isset( $regular_price->$currency ) ? woo_ce_format_price( $regular_price->$currency ) : false );
				$product->{sprintf( 'wcae_sale_price_%s', sanitize_key( $currency ) )} = ( isset( $sale_price->$currency ) ? woo_ce_format_price( $sale_price->$currency ) : false );

			}
			unset( $regular_price, $sale_price, $woocommerce_currency, $currencies, $currency );
		}
		unset( $options );
	}

	// Advanced Custom Fields - http://www.advancedcustomfields.com
	if( woo_ce_detect_export_plugin( 'acf' ) ) {
		$custom_fields = woo_ce_get_acf_product_fields();
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ) {
				$key = $custom_field['name'];
				$product->{sprintf( 'acf_%s', sanitize_key( $key ) )} = get_post_meta( $product_id, sanitize_key( $key ), true );
			}
		}
		unset( $custom_fields, $custom_field );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( !get_option( 'wccf_migrated_to_20' ) ) {
			$custom_fields = get_post_meta( $product_id, '_wccf_product_admin', true );
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$product->{sanitize_key( $custom_field['key'] )} = ( isset( $custom_field['value'] ) ? $custom_field['value'] : '' );
				}
			}
		} else {
			$custom_fields = woo_ce_get_wccf_product_properties();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$product->{sprintf( 'wccf_pp_%s', sanitize_key( $key ) )} = get_post_meta( $product_id, sprintf( '_wccf_pp_%s', sanitize_key( $key ) ), true );
				}
			}
		}
		unset( $custom_fields, $custom_field );
	}

	// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/
	if( woo_ce_detect_export_plugin( 'wc_fields_factory' ) ) {
		// Admin Fields
		$admin_fields = woo_ce_get_wcff_admin_fields();
		if( !empty( $admin_fields ) ) {
			foreach( $admin_fields as $admin_field ) {
				$product->{sprintf( 'wccaf_%s', sanitize_key( $admin_field['name'] ) )} = get_post_meta( $product_id, sprintf( 'wccaf_%s', $admin_field['name'] ), true );
			}
		}
		unset( $admin_fields, $admin_field );
	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
		$product->subscription_price = get_post_meta( $product_id, '_subscription_price', true );
		$product->subscription_period_interval = woo_ce_format_product_subscription_period_interval( get_post_meta( $product_id, '_subscription_period_interval', true ) );
		$product->subscription_period = get_post_meta( $product_id, '_subscription_period', true );
		$product->subscription_length = woo_ce_format_product_subscripion_length( get_post_meta( $product_id, '_subscription_length', true ), $product->subscription_period );
		$product->subscription_sign_up_fee = get_post_meta( $product_id, '_subscription_sign_up_fee', true );
		$product->subscription_trial_length = get_post_meta( $product_id, '_subscription_trial_length', true );
		$product->subscription_trial_period = get_post_meta( $product_id, '_subscription_trial_period', true );
		$product->subscription_limit = woo_ce_format_product_subscription_limit( get_post_meta( $product_id, '_subscription_limit', true ) );
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$product->booking_has_persons = get_post_meta( $product_id, '_wc_booking_has_persons', true );
		$product->booking_has_resources = get_post_meta( $product_id, '_wc_booking_has_resources', true );
		$product->booking_base_cost = get_post_meta( $product_id, '_wc_booking_cost', true );
		$product->booking_block_cost = get_post_meta( $product_id, '_wc_booking_base_cost', true );
		$product->booking_display_cost = get_post_meta( $product_id, '_wc_display_cost', true );
		$product->booking_requires_confirmation = get_post_meta( $product_id, '_wc_booking_requires_confirmation', true );
		$product->booking_user_can_cancel = get_post_meta( $product_id, '_wc_booking_user_can_cancel', true );
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		// Cannot clean up the barcode type as the developer has not exposed any functions or methods
		$product->barcode_type = get_post_meta( $product_id, '_barcode_type', true );
		$product->barcode = get_post_meta( $product_id, '_barcode', true );
	}

	// WooCommerce Pre-Orders - http://www.woothemes.com/products/woocommerce-pre-orders/
	if( woo_ce_detect_export_plugin( 'wc_preorders' ) ) {
		$product->pre_orders_enabled = woo_ce_format_switch( get_post_meta( $product_id, '_wc_pre_orders_enabled', true ) );
		$product->pre_orders_availability_date = woo_ce_format_product_sale_price_dates( get_post_meta( $product_id, '_wc_pre_orders_availability_datetime', true ) );
		$product->pre_orders_fee = woo_ce_format_price( get_post_meta( $product_id, '_wc_pre_orders_fee', true ) );
		$product->pre_orders_charge = woo_ce_format_pre_orders_charge( get_post_meta( $product_id, '_wc_pre_orders_when_to_charge', true ) );
	}

	// WooCommerce Product Fees - https://wordpress.org/plugins/woocommerce-product-fees/
	if( woo_ce_detect_export_plugin( 'wc_productfees' ) ) {
		$product->fee_name = get_post_meta( $product_id, 'product-fee-name', true );
		$product->fee_amount = get_post_meta( $product_id, 'product-fee-amount', true );
		$product->fee_multiplier = woo_ce_format_switch( get_post_meta( $product_id, 'product-fee-multiplier', true ) );
	}

	// FooEvents for WooCommerce - http://www.woocommerceevents.com/
	if( woo_ce_detect_export_plugin( 'fooevents' ) ) {
		$product->is_event = woo_ce_format_events_is_event( get_post_meta( $product_id, 'WooCommerceEventsEvent', true ) );
		if( apply_filters( 'woo_ce_use_fooevents_event_timestamp', true ) ) {
			$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
			$timestamp = get_post_meta( $product_id, 'WooCommerceEventsDateTimestamp', true );
			if(
				!empty( $timestamp ) && 
				class_exists( 'DateTime' )
			) {
				$event_date = new DateTime();
				$event_date->setTimestamp( $timestamp );
				if( !empty( $event_date ) )
					$product->event_date = $event_date->format( $date_format );
			}
			unset( $timestamp, $event_date );
		} else {
			$product->event_date = get_post_meta( $product_id, 'WooCommerceEventsDate', true );
		}
		$event_hour = absint( get_post_meta( $product_id, 'WooCommerceEventsHour', true ) );
		$event_minutes = absint( get_post_meta( $product_id, 'WooCommerceEventsMinutes', true ) );
		if( !empty( $event_hour ) || !empty( $event_minutes ) )
			$product->event_start_time = sprintf( '%d:%s', $event_hour, $event_minutes );
		unset( $event_hour, $event_minutes );
		$event_hour = absint( get_post_meta( $product_id, 'WooCommerceEventsHourEnd', true ) );
		$event_minutes = absint( get_post_meta( $product_id, 'WooCommerceEventsMinutesEnd', true ) );
		if( !empty( $event_hour ) || !empty( $event_minutes ) )
			$product->event_end_time = sprintf( '%d:%s', $event_hour, $event_minutes );
		unset( $event_hour, $event_minutes );
		$product->event_venue = get_post_meta( $product_id, 'WooCommerceEventsLocation', true );
		$product->event_gps = get_post_meta( $product_id, 'WooCommerceEventsGPS', true );
		$product->event_googlemaps = get_post_meta( $product_id, 'WooCommerceEventsGoogleMaps', true );
		$product->event_directions = get_post_meta( $product_id, 'WooCommerceEventsDirections', true );
		$product->event_phone = get_post_meta( $product_id, 'WooCommerceEventsSupportContact', true );
		$product->event_email = get_post_meta( $product_id, 'WooCommerceEventsEmail', true );
		$product->event_ticket_logo = get_post_meta( $product_id, 'WooCommerceEventsTicketLogo', true );
		$product->event_ticket_subject = get_post_meta( $product_id, 'WooCommerceEventsEmailSubjectSingle', true );
		$product->event_ticket_text = get_post_meta( $product_id, 'WooCommerceEventsTicketText', true );
		$product->event_ticket_thankyou_text = get_post_meta( $product_id, 'WooCommerceEventsThankYouText', true );
		$product->event_ticket_background_color = get_post_meta( $product_id, 'WooCommerceEventsTicketBackgroundColor', true );
		$product->event_ticket_button_color = get_post_meta( $product_id, 'WooCommerceEventsTicketButtonColor', true );
		$product->event_ticket_text_color = get_post_meta( $product_id, 'WooCommerceEventsTicketTextColor', true );
	}

	// WooCommerce Uploads - https://wpfortune.com/shop/plugins/woocommerce-uploads/
	if( woo_ce_detect_export_plugin( 'wc_uploads' ) ) {
		$product->enable_uploads = woo_ce_format_switch( get_post_meta( $product_id, '_wpf_umf_upload_enable', true ) );
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		$product->posr = woo_ce_format_price( get_post_meta( $product_id, '_posr_cost_of_good', true ) );
	}

	// WooCommerce Product Bundles - http://www.woothemes.com/products/product-bundles/
	if( woo_ce_detect_export_plugin( 'wc_product_bundles' ) ) {
		$bundled_products = get_post_meta( $product_id, '_bundle_data', true );
		if( !empty( $bundled_products ) ) {
			$product->bundled_products = '';
			$product->bundled_product_ids = '';
			foreach( $bundled_products as $bundled_product ) {
				$product->bundled_products .= get_the_title( $bundled_product['product_id'] ) . $export->category_separator;
				$product->bundled_product_ids .= $bundled_product['product_id'] . $export->category_separator;
			}
			$product->bundled_products = substr( $product->bundled_products, 0, -1 );
			$product->bundled_product_ids = substr( $product->bundled_product_ids, 0, -1 );
		}
		unset( $bundled_products, $bundled_product );
	}

	// WooCommerce Min/Max Quantities - https://woocommerce.com/products/minmax-quantities/
	if( woo_ce_detect_export_plugin( 'wc_min_max' ) ) {
		$product->minimum_quantity = get_post_meta( $product_id, 'minimum_allowed_quantity', true );
		$product->maximum_quantity = get_post_meta( $product_id, 'maximum_allowed_quantity', true );
		$product->group_of = get_post_meta( $product_id, 'group_of_quantity', true );
	}

/*
	// WooCommerce Variation Swatches and Photos - https://www.woothemes.com/products/variation-swatches-and-photos/
	// @mod - need more information from WooCommerce Variation Swatches and Photos. Check in 2.4+
	if( woo_ce_detect_export_plugin( 'variation_swatches_photos' ) ) {
		$colours = get_post_meta( $product_id, '_swatch_type_options', true );
		unset( $colours );
	}
*/

	// WooCommerce Tab Manager - http://www.woothemes.com/products/woocommerce-tab-manager/
	if( woo_ce_detect_export_plugin( 'wc_tabmanager' ) ) {
		$tabs = get_post_meta( $product_id, '_product_tabs', true );
		if( !empty( $tabs ) ) {
			foreach( $tabs as $tab ) {
				if( !empty( $tab ) )
					$product->{'product_tab_' . sanitize_key( $tab['name'] ) } = get_post_field( 'post_content', $tab['id'] );
			}
		}
	}

	// WooTabs - https://codecanyon.net/item/wootabsadd-extra-tabs-to-woocommerce-product-page/7891253
	if( woo_ce_detect_export_plugin( 'wootabs' ) ) {
		// We have to base64 decode and then unserialize this, workout much?
		$tabs = get_post_meta( $product_id, 'wootabs-product-tabs', true );
		if( !empty( $tabs ) ) {
			$tabs = ( function_exists( 'base64_decode' ) ? base64_decode( $tabs ) : false );
			if( !empty( $tabs ) ) {
				$tabs = maybe_unserialize( $tabs );
				// Custom WooTabs
				$custom_wootabs = woo_ce_get_option( 'custom_wootabs', '' );
				if( !empty( $custom_wootabs ) ) {
					foreach( $tabs as $tab ) {
						foreach( $custom_wootabs as $custom_wootab ) {
							if( $tab['title'] == $custom_wootab ) {
								$product->{sprintf( 'wootab_%s', sanitize_key( $custom_wootab ) )} = $tab['content'];
								break;
							}
						}
					}
				}
				unset( $custom_wootabs, $custom_wootab );
			}
		}
		unset( $tabs );
	}

	// WooCommerce Tiered Pricing - http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-tiered-pricing/
	if( woo_ce_detect_export_plugin( 'ign_tiered' ) ) {

		global $wp_roles;

		// User Roles
		if( isset( $wp_roles->roles ) ) {
			asort( $wp_roles->roles );
			foreach( $wp_roles->roles as $role => $role_data ) {
				// Skip default User Roles
				if( 'ignite_level_' != substr( $role, 0, 13 ) )
					continue;
				$product->{sanitize_key( $role )} = get_post_meta( $product_id, sprintf( '_%s_price', $role ), true ); 
			}
			unset( $role, $role_data );
		}
	}

	// WooCommerce BookStore - http://www.wpini.com/woocommerce-bookstore-plugin/
	if( woo_ce_detect_export_plugin( 'wc_books' ) ) {
		$custom_books = ( function_exists( 'woo_book_get_custom_fields' ) ? woo_book_get_custom_fields() : false );
		if( !empty( $custom_books ) ) {
			foreach( $custom_books as $custom_book ) {
				if( !empty( $custom_book ) )
					$product->{sprintf( 'book_%s', sanitize_key( $custom_book['name'] ) )} = get_post_meta( $product_id, $custom_book['meta_key'], true );
			}
		}
		unset( $custom_books, $custom_book );
		$term_taxonomy = 'book_category';
		$product->book_category = woo_ce_get_product_assoc_tags( $product_id, $term_taxonomy );
		$term_taxonomy = 'book_author';
		$product->book_author = woo_ce_get_product_assoc_tags( $product_id, $term_taxonomy );
		$term_taxonomy = 'book_publisher';
		$product->book_publisher = woo_ce_get_product_assoc_tags( $product_id, $term_taxonomy );
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$post_type = 'product';
		$product->language = woo_ce_wpml_get_language_name( apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $product_id, 'element_type' => $post_type ) ) );
	}

	// WooCommerce Jetpack - http://woojetpack.com/shop/wordpress-woocommerce-jetpack-plus/
	if( woo_ce_detect_export_plugin( 'woocommerce_jetpack' ) || woo_ce_detect_export_plugin( 'woocommerce_jetpack_plus' ) ) {

		// @mod - Needs alot of love in 2.4+, JetPack Plus, now Booster is huge

		// Check for Product Cost Price
		if( get_option( 'wcj_purchase_price_enabled', false ) == 'yes' ) {
			$product->wcj_purchase_price = get_post_meta( $product_id, '_wcj_purchase_price', true );
			$product->wcj_purchase_price_extra = get_post_meta( $product_id, '_wcj_purchase_price_extra', true );
			$product->wcj_purchase_price_affiliate_commission = get_post_meta( $product_id, '_wcj_purchase_price_affiliate_commission', true );
			// @mod - Let's add custom Product Cost Price fields once we get some more Booster modules sorted.
			$product->wcj_purchase_date = get_post_meta( $product_id, '_wcj_purchase_date', true );
			$product->wcj_purchase_partner = get_post_meta( $product_id, '_wcj_purchase_partner', true );
			$product->wcj_purchase_info = get_post_meta( $product_id, '_wcj_purchase_info', true );
		}

	}

	// WooCommerce Ultimate Multi Currency Suite - https://codecanyon.net/item/woocommerce-ultimate-multi-currency-suite/11997014
	if( woo_ce_detect_export_plugin( 'wc_umcs' ) ) {
		$currencies = json_decode( get_option( 'wcumcs_available_currencies' ) );
		if( !empty( $currencies ) ) {
			$current_currency = ( function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : false );
			foreach( $currencies as $currency_code => $currency_data ) {
				// Skip the base currency
				if( $currency_code == $current_currency )
					continue;
				$product->{sprintf( 'wcumcs_regular_price_%s', sanitize_key( $currency_code ) )} = get_post_meta( $product_id, sprintf( '_wcumcs_regular_price_%s', $currency_code ), true );
				$product->{sprintf( 'wcumcs_sale_price_%s', sanitize_key( $currency_code ) )} = get_post_meta( $product_id, sprintf( '_wcumcs_sale_price_%s', $currency_code ), true );
			}
			unset( $currency_code, $currency_data, $current_currency );
		}
		unset( $currencies );
	}

	// Products Purchase Price for Woocommerce - https://wordpress.org/plugins/products-purchase-price-for-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_products_purchase_price' ) ) {
		$product->purchase_price = get_post_meta( $product_id, '_purchase_price', true );
		// Check that a valid price has been provided
		if( isset( $product->purchase_price ) && $product->purchase_price != '' )
			$product->purchase_price = woo_ce_format_price( $product->purchase_price );
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				$product->{sprintf( '%s_wholesale_price', $key )} = get_post_meta( $product_id, sprintf( '%s_wholesale_price', $key ), true );
				// Check that a valid price has been provided
				if( isset( $product->{sprintf( '%s_wholesale_price', $key )} ) && $product->{sprintf( '%s_wholesale_price', $key )} != '' )
					$product->{sprintf( '%s_wholesale_price', $key )} = woo_ce_format_price( $product->{sprintf( '%s_wholesale_price', $key )} );
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// WooCommerce Show Single Variations - https://iconicwp.com/products/woocommerce-show-single-variations/
	if( woo_ce_detect_export_plugin( 'wc_show_single_variations' ) ) {
		if( $product->post_type == 'product_variation' ) {
			$featured = get_post_meta( $product_id, '_featured', true );
			if( !empty( $featured ) )
				$product->featured = woo_ce_format_switch( $featured );
			unset( $featured );
			$product->disable_add_to_cart = woo_ce_format_switch( 0 );
			$disable_add_to_cart = get_post_meta( $product_id, '_disable_add_to_cart', true );
			if( $disable_add_to_cart <> '' )
				$product->disable_add_to_cart = woo_ce_format_switch( $disable_add_to_cart );
			unset( $disable_add_to_cart );
			$visibility = get_post_meta( $product_id, '_visibility', true );
			if( !empty( $visibility ) && is_array( $visibility ) ) {
				if( isset( $product->visibility ) == false )
					$product->visibility = '';
				$product->show_search_results = woo_ce_format_switch( 1 );
				if( in_array( 'search', $visibility ) ) {
					$product->visibility .= __( 'Show in Search Results', 'woocommerce-exporter' ) . $export->category_separator;
					$product->show_search_results = woo_ce_format_switch( 1 );
				}
				$product->show_filtered_results = woo_ce_format_switch( 0 );
				if( in_array( 'filtered', $visibility ) ) {
					$product->visibility .= __( 'Show in Filtered Results', 'woocommerce-exporter' ) . $export->category_separator;
					$product->show_filtered_results = woo_ce_format_switch( 1 );
				}
				$product->show_catalog = woo_ce_format_switch( 0 );
				if( in_array( 'catalog', $visibility ) ) {
					$product->visibility .= __( 'Show in Catalog', 'woocommerce-exporter' ) . $export->category_separator;
					$product->show_catalog = woo_ce_format_switch( 1 );
				}
				$product->visibility = substr( $product->visibility, 0, -1 );
			}
			unset( $visibility );
		}
	}

	// WooCommerce Deposits - https://woocommerce.com/products/woocommerce-deposits/
	if( woo_ce_detect_export_plugin( 'wc_deposits' ) ) {
		$product->enable_deposit = woo_ce_format_switch( get_post_meta( $product_id, '_wc_deposits_enable_deposit', true ) );
		$product->force_deposit = woo_ce_format_switch( get_post_meta( $product_id, '_wc_deposits_force_deposit', true ) );
		$product->amount_type = get_post_meta( $product_id, '_wc_deposits_amount_type', true );
		$product->deposit_amount = get_post_meta( $product_id, '_wc_deposits_deposit_amount', true );
		// Check that a valid price has been provided
		if( isset( $product->deposit_amount ) && $product->deposit_amount != '' ) {
			switch( $product->amount_type ) {

				case 'fixed':
				default:
					$product->deposit_amount = woo_ce_format_price( $product->deposit_amount );
					break;

				case 'percent':
					$product->deposit_amount .= '%';
					break;

			}
		}
	}

	// WooCommerce Unit of Measure - https://wordpress.org/plugins/woocommerce-unit-of-measure/
	if( woo_ce_detect_export_plugin( 'wc_unitofmeasure' ) ) {
		$product->unit_of_measure = get_post_meta( $product_id, '_woo_uom_input', true );
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$duration = get_post_meta( $product_id, '_wc_appointment_duration', true );
		$duration_unit = get_post_meta( $product_id, '_wc_appointment_duration_unit', true );
		if( !empty( $duration ) )
			$product->duration = sprintf( '%d %s', $duration, $duration_unit );
		$interval = get_post_meta( $product_id, '_wc_appointment_duration', true );
		$interval_unit = get_post_meta( $product_id, '_wc_appointment_duration_unit', true );
		if( !empty( $interval ) )
			$product->interval = sprintf( '%d %s', $interval, $interval_unit );
		$padding = get_post_meta( $product_id, '_wc_appointment_padding_duration', true );
		$padding_unit = get_post_meta( $product_id, '_wc_appointment_padding_duration_unit', true );
		if( !empty( $padding ) )
			$product->padding_time = sprintf( '%d %s', $padding, $padding_unit );
		$lead_time = get_post_meta( $product_id, '_wc_appointment_min_date_duration', true );
		$lead_time_unit = get_post_meta( $product_id, '_wc_appointment_min_date_unit', true );
		if( !empty( $lead_time ) )
			$product->lead_time = sprintf( '%d %s', $lead_time, $lead_time_unit );
		$scheduling_window = get_post_meta( $product_id, '_wc_appointment_max_date_duration', true );
		$scheduling_window_unit = get_post_meta( $product_id, '_wc_appointment_max_date_unit', true );
		if( !empty( $scheduling_window ) )
			$product->scheduling_window = sprintf( '%d %s', $scheduling_window, $scheduling_window_unit );
		$requires_confirmation = get_post_meta( $product_id, '_wc_appointment_requires_confirmation', true );
		$product->requires_confirmation = woo_ce_format_switch( $requires_confirmation );
		$can_be_cancelled = get_post_meta( $product_id, '_wc_appointment_user_can_cancel', true );
		if( !empty( $can_be_cancelled ) ) {
			$cancellation_window = get_post_meta( $product_id, '_wc_appointment_max_date_duration', true );
			$cancellation_window_unit = get_post_meta( $product_id, '_wc_appointment_max_date_unit', true );
			$product->cancelled_at_least = sprintf( '%d %s', $cancellation_window, $cancellation_window_unit );
		}
		$product->can_be_cancelled = woo_ce_format_switch( $can_be_cancelled );
		$customer_timezones = get_post_meta( $product_id, '_wc_appointment_customer_timezones', true );
		$product->customer_timezones = woo_ce_format_switch( $customer_timezones );
		$product->calendar_color = get_post_meta( $product_id, '_wc_appointment_cal_color', true );
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$is_bookable = get_post_meta( $product_id, '_booking_option', true );
		$product->bookable = woo_ce_format_switch( $is_bookable );
		$booking_dates = get_post_meta( $product_id, '_booking_dates', true );
		if( $booking_dates == 'global' )
			$booking_dates = __( 'Same as global settings', 'woocommerce-exporter' );
		$product->booking_dates = $booking_dates;
		$booking_duration = get_post_meta( $product_id, '_booking_duration', true );
		if( $booking_duration == 'global' )
			$booking_duration = __( 'Same as global settings', 'woocommerce-exporter' );
		$product->booking_duration = $booking_duration;
		$product->booking_min = get_post_meta( $product_id, '_booking_min', true );
		$product->booking_max = get_post_meta( $product_id, '_booking_max', true );
		$product->first_available_date = get_post_meta( $product_id, '_first_available_date', true );
	}

	// WooCommerce Advanced Product Quantities - http://www.wpbackoffice.com/plugins/woocommerce-incremental-product-quantities/
	if( woo_ce_detect_export_plugin( 'wc_advanced_quantities' ) ) {
		$product->deactivate_quantity_rules = woo_ce_format_switch( get_post_meta( $product_id, '_wpbo_deactive', true ) );
		$product->override_quantity_rules = woo_ce_format_switch( get_post_meta( $product_id, '_wpbo_override', true ) );
		$product->step_value = get_post_meta( $product_id, '_wpbo_step', true );
		$product->minimum_quantity = get_post_meta( $product_id, '_wpbo_minimum', true );
		$product->maximum_quantity = get_post_meta( $product_id, '_wpbo_maximum', true );
		$product->oos_minimum = get_post_meta( $product_id, '_wpbo_minimum_oos', true );
		$product->oos_maximum = get_post_meta( $product_id, '_wpbo_maximum_oos', true );
	}

	// WooCommerce Chained Products - https://woocommerce.com/products/chained-products/
	if( woo_ce_detect_export_plugin( 'wc_chained_products' ) ) {
		$chained_product_ids = get_post_meta( $product_id, '_chained_product_ids', true );
		if( !empty( $chained_product_ids ) ) {
			$product->chained_products_ids = implode( $export->category_separator, $chained_product_ids );
			$chained_product_details = get_post_meta( $product_id, '_chained_product_detail', true );
			if( !empty( $chained_product_details ) ) {
				$chained_products = array();
				$chained_products_names = array();
				$chained_products_skus = array();
				$chained_products_units = array();
				foreach( $chained_product_details as $chained_product_id => $chained_product ) {
					$chained_products[] = sprintf( '%s: %s', $chained_product['product_name'], $chained_product['unit'] );
					$chained_products_names[] = $chained_product['product_name'];
					$chained_products_skus[] = get_post_meta( $chained_product_id, '_sku', true );
					$chained_products_units[] = $chained_product['unit'];
				}
				$product->chained_products = implode( "\n", $chained_products );
				$product->chained_products_skus = implode( $export->category_separator, $chained_products_skus );
				$product->chained_products_names = implode( $export->category_separator, $chained_products_names );
				$product->chained_products_units = implode( $export->category_separator, $chained_products_units );
				unset( $chained_products, $chained_products_skus, $chained_products_names, $chained_products_units );
			}
		}
		$product->chained_products_manage_stock = woo_ce_format_switch( get_post_meta( $product_id, '_chained_product_manage_stock', true ) );
		unset( $chained_product_ids, $chained_product_details, $chained_product_id, $chained_product );
	}

	// WooCommerce Sample - https://wordpress.org/plugins/woocommerce-sample/
	if( woo_ce_detect_export_plugin( 'wc_sample' ) ) {
		$product->enable_sample = woo_ce_format_switch( get_post_meta( $product_id, 'sample_enamble', true ) );
		// WooCommerce Chained Products - https://woocommerce.com/products/chained-products/
		if( woo_ce_detect_export_plugin( 'wc_chained_products' ) ) {
			$product->enable_sample_chained = woo_ce_format_switch( get_post_meta( $product_id, 'sample_chained_enambled', true ) );
		}
		$product->sample_shipping_mode = get_post_meta( $product_id, 'sample_shipping_mode', true );
		$product->sample_shipping = get_post_meta( $product_id, 'sample_shipping', true );
		$product->sample_price_mode = get_post_meta( $product_id, 'sample_price_mode', true );
		$product->sample_price = get_post_meta( $product_id, 'sample_price', true );
	}

	// AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO - https://www.weareag.co.uk/product/woocommerce-barcodeisbn-amazon-asin-pro/
	if( woo_ce_detect_export_plugin( 'wc_ag_barcode_pro' ) ) {
		$product->barcode = get_post_meta( $product_id, 'barcode', true );
		$product->isbn = get_post_meta( $product_id, 'ISBN', true );
		$product->amazon = get_post_meta( $product_id, 'amazon', true );
	}

	// N-Media WooCommerce Personalized Product Meta Manager - https://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/
	// PPOM for WooCommerce - https://wordpress.org/plugins/woocommerce-product-addon/
	if(
		woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) || 
		woo_ce_detect_export_plugin( 'wc_ppom' )
	) {
		$group_name_id = get_post_meta( $product_id, '_product_meta_id', true );
		if( !empty( $group_name_id ) ) {

			global $wpdb;

			$group_name_sql = $wpdb->prepare( "SELECT `productmeta_name` FROM `" . $wpdb->prefix . "nm_personalized` WHERE %s LIMIT 1", $group_name_id );
			$group_name = $wpdb->get_var( $group_name_sql );
			if( !empty( $group_name ) )
				$product->select_personalized_meta = $group_name;
		}
	}

	// SEO Squirrly - https://wordpress.org/plugins/squirrly-seo/
	if( woo_ce_detect_export_plugin( 'seo_squirrly' ) ) {
		$meta_value = json_decode( get_post_meta( $product_id, '_sq_post_keyword', true ) );
		if( !empty( $meta_value ) ) {
			if( isset( $meta_value->keyword ) )
				$product->sq_keywords = $meta_value->keyword;
		}
	}

	// WooCommerce Measurement Price Calculator - http://www.woocommerce.com/products/measurement-price-calculator/
	if( woo_ce_detect_export_plugin( 'wc_measurement_price_calc' ) ) {
		$product->area = get_post_meta( $product_id, '_area', true );
		$product->volume = get_post_meta( $product_id, '_volume', true );
		$meta_value = get_post_meta( $product_id, '_wc_price_calculator', true );
		if( !empty( $meta_value ) ) {

			$product->measurement_type = 'None';
			if( isset( $meta_value['calculator_type'] ) )
				$product->measurement_type = woo_ce_format_measurement_type_label( $meta_value['calculator_type'] );

			// Dimensions
			$product->measurement_dimension_pricing = ( isset( $meta_value['dimension']['pricing']['enabled'] ) ? woo_ce_format_switch( $meta_value['dimension']['pricing']['enabled'] ) : '' );
			$product->measurement_dimension_pricing_label = ( isset( $meta_value['dimension']['pricing']['label'] ) ? $meta_value['dimension']['pricing']['label'] : '' );
			$product->measurement_dimension_pricing_unit = ( isset( $meta_value['dimension']['pricing']['unit'] ) ? $meta_value['dimension']['pricing']['unit'] : '' );

			// Area
			$product->measurement_area_pricing = ( isset( $meta_value['area']['pricing']['enabled'] ) ? woo_ce_format_switch( $meta_value['area']['pricing']['enabled'] ) : '' );
			$product->measurement_area_pricing_label = ( isset( $meta_value['area']['pricing']['label'] ) ? $meta_value['area']['pricing']['label'] : '' );
			$product->measurement_area_pricing_unit = ( isset( $meta_value['area']['pricing']['unit'] ) ? $meta_value['area']['pricing']['unit'] : '' );
			$product->measurement_area_area_label = ( isset( $meta_value['area']['area']['label'] ) ? $meta_value['area']['area']['label'] : '' );
			$product->measurement_area_area_unit = ( isset( $meta_value['area']['area']['unit'] ) ? $meta_value['area']['area']['unit'] : '' );
			$product->measurement_area_editable = ( isset( $meta_value['area']['area']['editable'] ) ? woo_ce_format_switch( $meta_value['area']['area']['editable'] ) : '' );

			// Area (LxW)
			$product->measurement_area_dimension_pricing = ( isset( $meta_value['area-dimension']['pricing']['enabled'] ) ? woo_ce_format_switch( $meta_value['area-dimension']['pricing']['enabled'] ) : '' );
			$product->measurement_area_dimension_pricing_label = ( isset( $meta_value['area-dimension']['pricing']['label'] ) ? $meta_value['area-dimension']['pricing']['label'] : '' );
			$product->measurement_area_dimension_pricing_unit = ( isset( $meta_value['area-dimension']['pricing']['unit'] ) ? $meta_value['area-dimension']['pricing']['unit'] : '' );
			$product->measurement_area_dimension_calculator = ( isset( $meta_value['area-dimension']['pricing']['calculator']['enabled'] ) ? woo_ce_format_switch( $meta_value['area-dimension']['pricing']['calculator']['enabled'] ) : '' );
			$product->measurement_area_dimension_weight = ( isset( $meta_value['area-dimension']['pricing']['weight']['enabled'] ) ? woo_ce_format_switch( $meta_value['area-dimension']['pricing']['weight']['enabled'] ) : '' );
			$product->measurement_area_dimension_inventory = ( isset( $meta_value['area-dimension']['pricing']['inventory']['enabled'] ) ? woo_ce_format_switch( $meta_value['area-dimension']['pricing']['inventory']['enabled'] ) : '' );
			$product->measurement_area_dimension_overage = ( isset( $meta_value['area-dimension']['pricing']['overage'] ) ? $meta_value['area-dimension']['pricing']['overage'] : '' );
			$product->measurement_area_dimension_length_label = ( isset( $meta_value['area-dimension']['length']['label'] ) ? $meta_value['area-dimension']['length']['label'] : '' );
			$product->measurement_area_dimension_length_unit = ( isset( $meta_value['area-dimension']['length']['unit'] ) ? $meta_value['area-dimension']['length']['unit'] : '' );
			$product->measurement_area_dimension_length_input = ( isset( $meta_value['area-dimension']['length']['accepted_input'] ) ? woo_ce_format_measurement_input_type( $meta_value['area-dimension']['length']['accepted_input'] ) : '' );
			$product->measurement_area_dimension_length_options = ( isset( $meta_value['area-dimension']['length']['options'] ) ? implode( $export->category_separator, $meta_value['area-dimension']['length']['options'] ) : '' );
			$product->measurement_area_dimension_width_label = ( isset( $meta_value['area-dimension']['width']['label'] ) ? $meta_value['area-dimension']['width']['label'] : '' );
			$product->measurement_area_dimension_width_unit = ( isset( $meta_value['area-dimension']['width']['unit'] ) ? $meta_value['area-dimension']['width']['unit'] : '' );
			$product->measurement_area_dimension_width_input = ( isset( $meta_value['area-dimension']['width']['accepted_input'] ) ? woo_ce_format_measurement_input_type( $meta_value['area-dimension']['width']['accepted_input'] ) : '' );
			$product->measurement_area_dimension_width_options = ( isset( $meta_value['area-dimension']['width']['options'] ) ? implode( $export->category_separator, $meta_value['area-dimension']['width']['options'] ) : '' );

			// Perimeter
			$product->measurement_area_linear_pricing = ( isset( $meta_value['area-linear']['pricing']['enabled'] ) ? woo_ce_format_switch( $meta_value['area-linear']['pricing']['enabled'] ) : '' );
			$product->measurement_area_linear_pricing_label = ( isset( $meta_value['area-linear']['pricing']['label'] ) ? $meta_value['area-linear']['pricing']['label'] : '' );
			$product->measurement_area_linear_pricing_unit = ( isset( $meta_value['area-linear']['pricing']['unit'] ) ? $meta_value['area-linear']['pricing']['unit'] : '' );
			$product->measurement_area_linear_length_label = ( isset( $meta_value['area-linear']['length']['label'] ) ? $meta_value['area-linear']['length']['label'] : '' );
			$product->measurement_area_linear_length_unit = ( isset( $meta_value['area-linear']['length']['unit'] ) ? $meta_value['area-linear']['length']['unit'] : '' );
			$product->measurement_area_linear_width_label = ( isset( $meta_value['area-linear']['width']['label'] ) ? $meta_value['area-linear']['width']['label'] : '' );
			$product->measurement_area_linear_width_unit = ( isset( $meta_value['area-linear']['width']['unit'] ) ? $meta_value['area-linear']['width']['unit'] : '' );

			// Surface Area
			// Volume
			// Volume (LxWxH)
			// Volume (AxH)
			// Weight
			// Room Walls

		}
	}

	// Google Customer Reviews for WooCommerce - https://wordpress.org/plugins/ecr-google-customer-reviews/
	if( woo_ce_detect_export_plugin( 'wc_ecr_gcr' ) ) {
		// Check what the default GTIN post meta key is...
	  $gtin_field = get_option( 'ecr_gtin_field' );
	  if( empty( $gtin_field ) )
	  	$gtin_field = '_gtin';
	  $product->gtin = get_post_meta( $product_id, $gtin_field, true );
	  unset( $gtin_field );
	}

	// WooCommerce UPC, EAN, and ISBN - https://wordpress.org/plugins/woo-add-gtin/
	if( woo_ce_detect_export_plugin( 'woo_add_gtin' ) ) {
		$product->gtin = get_post_meta( $product_id, 'hwp_product_gtin', true );
	}

	// WP-Lister Pro for Amazon - https://www.wplab.com/plugins/wp-lister-for-amazon/
	if( woo_ce_detect_export_plugin( 'wpla_wplister' ) ) {
		$product->wpl_amazon_product_id = get_post_meta( $product_id, '_amazon_product_id', true );
		$product->wpl_amazon_id_type = woo_ce_format_wpla_id_type( get_post_meta( $product_id, '_amazon_id_type', true ) );
		$product->wpl_amazon_title = get_post_meta( $product_id, '_amazon_title', true );
		$product->wpl_amazon_price = get_post_meta( $product_id, '_amazon_price', true );
		$product->wpl_amazon_minimum_price = get_post_meta( $product_id, '_amazon_minimum_price', true );
		$product->wpl_amazon_maximum_price = get_post_meta( $product_id, '_amazon_maximum_price', true );
		$product->wpl_amazon_condition_type = woo_ce_format_wpla_condition_type( get_post_meta( $product_id, '_amazon_condition_type', true ) );
		$product->wpl_amazon_condition_note = get_post_meta( $product_id, '_amazon_condition_note', true );
		$product->wpl_amazon_bullet_point1 = get_post_meta( $product_id, '_amazon_bullet_point1', true );
		$product->wpl_amazon_bullet_point2 = get_post_meta( $product_id, '_amazon_bullet_point2', true );
		$product->wpl_amazon_bullet_point3 = get_post_meta( $product_id, '_amazon_bullet_point3', true );
		$product->wpl_amazon_bullet_point4 = get_post_meta( $product_id, '_amazon_bullet_point4', true );
		$product->wpl_amazon_bullet_point5 = get_post_meta( $product_id, '_amazon_bullet_point5', true );
		$product->wpl_amazon_generic_keywords1 = get_post_meta( $product_id, '_amazon_generic_keywords1', true );
		$product->wpl_amazon_generic_keywords2 = get_post_meta( $product_id, '_amazon_generic_keywords2', true );
		$product->wpl_amazon_generic_keywords3 = get_post_meta( $product_id, '_amazon_generic_keywords3', true );
		$product->wpl_amazon_generic_keywords4 = get_post_meta( $product_id, '_amazon_generic_keywords4', true );
		$product->wpl_amazon_generic_keywords5 = get_post_meta( $product_id, '_amazon_generic_keywords5', true );
		$product->wpl_amazon_product_description = get_post_meta( $product_id, '_amazon_product_description', true );
		$product->wpl_amazon_asin = get_post_meta( $product_id, '_wpla_asin', true );
	}

	// WP-Lister Pro for eBay - https://www.wplab.com/plugins/wp-lister/
	if( woo_ce_detect_export_plugin( 'wple_wplister' ) ) {
		$product->wpl_ebay_title = get_post_meta( $product_id, '_ebay_title', true );
		$product->wpl_ebay_subtitle = get_post_meta( $product_id, '_ebay_subtitle', true );
		$product->wpl_ebay_start_price = get_post_meta( $product_id, '_ebay_start_price', true );
		$product->wpl_ebay_auction_type = get_post_meta( $product_id, '_ebay_auction_type', true );
		$product->wpl_ebay_listing_duration = get_post_meta( $product_id, '_ebay_listing_duration', true );
		$product->wpl_ebay_condition = get_post_meta( $product_id, '_ebay_condition_id', true );
		$product->wpl_ebay_condition_description = get_post_meta( $product_id, '_ebay_condition_description', true );
	}

	// AliDropship for WooCommerce - https://alidropship.com/
	if( woo_ce_detect_export_plugin( 'alidropship' ) ) {

		global $wpdb;

		// Check the adsw_ali_meta table exists
		if( $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "adsw_ali_meta'" ) ) {
			// Override if Variation Formatting is enabled
			if( $product->post_type == 'product_variation' && woo_ce_get_option( 'variation_formatting', 0 ) )
				$meta_sql = "SELECT * FROM `" . $wpdb->prefix . "adsw_ali_meta` WHERE `post_id` = " . absint( $product->parent_id );
			else
				$meta_sql = "SELECT * FROM `" . $wpdb->prefix . "adsw_ali_meta` WHERE `post_id` = " . absint( $product_id );
			$meta = $wpdb->get_row( $meta_sql, ARRAY_A );
			if( !empty( $meta ) ) {
				$product->ali_product_id = $meta['product_id'];
				$product->ali_product_url = $meta['productUrl'];
				$product->ali_store_url = $meta['storeUrl'];
				$product->ali_store_name = $meta['storeName'];
			}
			unset( $meta );
		}
	}

	// WooCommerce All Discounts Lite - https://wordpress.org/plugins/woo-advanced-discounts/
	if( woo_ce_detect_export_plugin( 'wc_alldiscounts_lite' ) ) {
		$discounts = get_post_meta( $product_id, 'o-discount', true );
		$product->qbp_enable = woo_ce_format_switch( isset( $discounts['enable'] ) ? $discounts['enable'] : false );
		if( !empty( $discounts ) ) {
			$rules_type = ( isset( $discounts['rules-type'] ) ? $discounts['rules-type'] : false );
			$product->qbp_discount_type = ( !empty( $discounts['type'] ) ? woo_ce_format_qbp_discount_type( $discounts['type'] ) : '' );
			$product->qbp_rules_type = ( !empty( $rules_type ) ? ucwords( $rules_type ) : false );
			if( !empty( $rules_type ) ) {
				$rules = false;
				$discount_rules = array();
				switch( $rules_type ) {

					case 'intervals':
						$rules = ( isset( $discounts['rules'] ) ? $discounts['rules'] : false );
						break;

					case 'steps':
						$rules = ( isset( $discounts['rules-by-step'] ) ? $discounts['rules-by-step'] : false );
						break;

				}
				if( !empty( $rules ) ) {

					// Allow Plugin/Theme authors to change the Rule separator
					$rule_separator = apply_filters( 'woo_ce_qbp_rule_separator', $export->category_separator );

					// Allow Plugin/Theme authors to change the Rule value separator
					$value_separator = apply_filters( 'woo_ce_qbp_value_separator', ':' );

					foreach( $rules as $rule ) {
						switch( $rules_type ) {

							case 'intervals':
								$discount_rules[] = $rule['min'] . $value_separator . $rule['max'] . $value_separator . $rule['discount'];
								break;

							case 'steps':
								$discount_rules[] = $rule['every'] . $value_separator . $rule['discount'];
								break;

						}
					}
					if( !empty( $discount_rules ) )
						$product->qbp_rules = ( is_array( $discount_rules ) ? implode( $rule_separator, $discount_rules ) : false );
				}
				unset( $rules, $rule );
			}
		}
		unset( $discounts, $rules_type );
	}

	// ATUM Inventory Management for WooCommerce - https://wordpress.org/plugins/atum-stock-manager-for-woocommerce/
	if( woo_ce_detect_export_plugin( 'atum_inventory' ) ) {

		global $wpdb;

		$purchase_price_sql = $wpdb->prepare( "SELECT `purchase_price` FROM `" . $wpdb->prefix . "atum_product_data` WHERE `product_id`= %s LIMIT 1", $product_id );
		$purchase_price = $wpdb->get_var( $purchase_price_sql );
		if( !empty( $purchase_price ) ) {
			$product->purchase_price = $purchase_price;
			// Check that a valid price has been provided
			if( isset( $product->purchase_price ) && $product->purchase_price != '' )
				$product->purchase_price = woo_ce_format_price( $product->purchase_price );
		}
		unset( $purchase_price );
	}

	// Custom Product meta
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) ) {
				$product->{$custom_product} = woo_ce_format_custom_meta( get_post_meta( $product_id, $custom_product, true ) );
				// Override the Custom Product meta if Variation Formatting is enabled
				if( $product->post_type == 'product_variation' && woo_ce_get_option( 'variation_formatting', 0 ) ) {
					if( $product->{$custom_product} == '' )
						$product->{$custom_product} = woo_ce_format_custom_meta( get_post_meta( $product->parent_id, $custom_product, true ) );
				}
			}
		}
	}

	if( $export->args['gallery_unique'] ) {
		$max_size = woo_ce_get_option( 'max_product_gallery', 3 );
		if( !empty( $product->product_gallery ) ) {
			// Tack on a extra digit to max_size so we get the correct number of columns
			$max_size++;
			$product_gallery = explode( $export->category_separator, $product->product_gallery );
			$size = count( $product_gallery );
			for( $i = 1; $i < $size; $i++ ) {
				if( $i == $max_size )
					break;
				$product->{sprintf( 'product_gallery_%d', $i )} = $product_gallery[$i];
			}
			$product->product_gallery = $product_gallery[0];
			unset( $product_gallery );
		}
	}

	return $product;

}
add_filter( 'woo_ce_product_item', 'woo_ce_extend_product_item', 10, 3 );

// WooCommerce All Discounts Lite - https://wordpress.org/plugins/woo-advanced-discounts/
function woo_ce_format_qbp_discount_type( $discount_type = '' ) {

	if( empty( $discount_type ) )
		return;

	$output = $discount_type;

	switch( $discount_type ) {

		case 'percentage':
			$output = 'Percentage';
			break;

		case 'fixed':
			$output = 'Fixed amount';
			break;

	}

	return $output;

}

// WP-Lister Pro for Amazon - https://www.wplab.com/plugins/wp-lister-for-amazon/
function woo_ce_format_wpla_id_type( $id_type = '' ) {

	if( empty( $id_type ) )
		return;

	$output = $id_type;
	switch( $id_type ) {

		case 'UPC':
			$output = __( 'UPC', 'wpla' );
			break;

		case 'EAN':
			$output = __( 'EAN', 'wpla' );
			break;

	}
	return $output;

}

// WP-Lister Pro for Amazon - https://www.wplab.com/plugins/wp-lister-for-amazon/
function woo_ce_format_wpla_condition_type( $condition_type = '' ) {

	if( empty( $condition_type ) )
		return;

	$output = $condition_type;
	switch( $condition_type ) {

		case 'New':
			$output = __( 'New', 'wpla' );
			break;

		case 'UsedLikeNew':
			$output = __( 'Used - Like New', 'wpla' );
			break;

		case 'UsedVeryGood':
			$output = __( 'Used - Very Good', 'wpla' );
			break;

		case 'UsedGood':
			$output = __( 'Used - Good', 'wpla' );
			break;

		case 'UsedAcceptable':
			$output = __( 'Used - Acceptable', 'wpla' );
			break;

		case 'Refurbished':
			$output = __( 'Refurbished', 'wpla' );
			break;

		case 'CollectibleLikeNew':
			$output = __( 'Collectible - Like New', 'wpla' );
			break;

		case 'CollectibleVeryGood':
			$output = __( 'Collectible - Very Good', 'wpla' );
			break;

		case 'CollectibleGood':
			$output = __( 'Collectible - Good', 'wpla' );
			break;

		case 'CollectibleAcceptable':
			$output = __( 'Collectible - Acceptable', 'wpla' );
			break;

	}
	return $output;

}

// WooCommerce Pre-Orders - http://www.woothemes.com/products/woocommerce-pre-orders/
function woo_ce_format_pre_orders_charge( $charge = '' ) {

	$output = $charge;
	if( !empty( $charge ) ) {
		switch( $charge ) {

			case 'upon_release':
				$output = __( 'Upon Release', 'woocommerce-exporter' );
				break;

			case 'upfront':
				$output = __( 'Upfront', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

// WooCommerce Measurement Price Calculator - http://www.woocommerce.com/products/measurement-price-calculator/
function woo_ce_format_measurement_type_label( $measurement_type = '' ) {

	$output = $measurement_type;
	switch( $measurement_type ) {

		// Dimensions
		case 'dimensions':
			$output = 'Dimensions';
			break;

		// Area
		case 'area':
			$output = 'Area';
			break;

		// Area (LxW)
		case 'area-dimension':
			$output = 'Area (LxW)';
			break;

		// Perimeter
		case 'area-linear':
			$output = 'Perimeter';
			break;

	}
	return $output;

}


// WooCommerce Measurement Price Calculator - http://www.woocommerce.com/products/measurement-price-calculator/
function woo_ce_format_measurement_input_type( $input_type = '' ) {

	if( empty( $input_type ) )
		return;

	$output = $input_type;

	switch( $input_type ) {

		case 'free':
			$output = 'Free-form';
			break;

		case 'limited':
			$output = 'Limited';
			break;

	}
	return $output;

}

function woo_ce_extend_product_dataset_args( $args, $export_type = '' ) {

	// Check if we're dealing with the Product Export Type
	if( $export_type <> 'product' )
		return $args;

	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		$product_meta = array();
		foreach( $custom_products as $custom_product )
			$product_meta[esc_attr( $custom_product )] = ( isset( $_POST[sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) )] ) ? $_POST[sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) )] : false );
		if( !empty( $product_meta ) )
			$args['product_custom_meta'] = $product_meta;
	}

	return $args;

}
add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_extend_product_dataset_args', 10, 2 );

function woo_ce_extend_get_products_args( $args ) {

	global $export;

	// Custom Product meta
	$product_meta = ( isset( $export->args['product_custom_meta'] ) ? $export->args['product_custom_meta'] : false );
	if( !empty( $product_meta ) ) {
		$custom_products = woo_ce_get_option( 'custom_products', '' );
		if( !empty( $custom_products ) ) {
			if( !isset( $args['meta_query'] ) )
				$args['meta_query'] = array();
			foreach( $custom_products as $custom_product ) {
				if( isset( $product_meta[esc_attr( $custom_product )] ) && !empty( $product_meta[esc_attr( $custom_product )] ) ) {
					$meta_key = $custom_product;
					$args['meta_query'][] = array(
						'key' => $meta_key,
						'value' => $product_meta[esc_attr( $custom_product )]
					);
				}
			}
		}
	}

	return $args;

}
add_filter( 'woo_ce_get_products_args', 'woo_ce_extend_get_products_args' );

function woo_ce_extend_cron_product_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	if( $export_type <> 'product' )
		return $args;

	$product_orderby = false;
	$product_filter_brand = false;
	$product_filter_vendor = false;
	$product_filter_language = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

		$product_orderby = get_post_meta( $scheduled_export, '_filter_product_orderby', true );

		// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
		// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
		if( woo_ce_detect_product_brands() ) {
			$product_filter_brand = get_post_meta( $scheduled_export, '_filter_product_brand', true );
		}
		// Product Vendors - http://www.woothemes.com/products/product-vendors/
		// WC Vendors - http://wcvendors.com
		// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
		if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
			$product_filter_vendor = get_post_meta( $scheduled_export, '_filter_product_vendor', true );
		}
		// WPML - https://wpml.org/
		// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
		if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
			$product_filter_language = get_post_meta( $scheduled_export, '_filter_product_language', true );
		}
	} else {
		if( isset( $_GET['product_brand'] ) ) {
			$product_filter_brand = sanitize_text_field( $_GET['product_brand'] );
			if( !empty( $product_filter_brand ) ) {
				$product_filter_brand = explode( ',', $product_filter_brand );
				$product_filter_brand = array_map( 'absint', (array)$product_filter_brand );
			}
		}
		if( isset( $_GET['product_vendor'] ) ) {
			$product_filter_vendor = sanitize_text_field( $_GET['product_vendor'] );
			if( !empty( $product_filter_vendor ) ) {
				$product_filter_vendor = explode( ',', $product_filter_vendor );
				$product_filter_vendor = array_map( 'absint', (array)$product_filter_vendor );
			}
		}
		if( isset( $_GET['product_language'] ) ) {
			$product_filter_language = sanitize_text_field( $_GET['product_language'] );
			if( !empty( $product_filter_language ) ) {
				$product_filter_language = explode( ',', $product_filter_language );
				$product_filter_language = array_map( 'absint', (array)$product_filter_language );
			}
		}
	}
	$defaults = array(
		'product_order' => ( !empty( $product_orderby ) ? $product_orderby : false ),
		'product_brand' => ( !empty( $product_filter_brand ) ? $product_filter_brand : false ),
		'product_vendor' => ( !empty( $product_filter_vendor ) ? $product_filter_vendor : false ),
		'product_language' => ( !empty( $product_filter_language ) ? $product_filter_language : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_action( 'woo_ce_extend_cron_dataset_args', 'woo_ce_extend_cron_product_dataset_args', 10, 3 );

// Return a list of custom Term Taxonomies linked to Products
function woo_ce_get_product_custom_term_taxonomies() {

	$post_type = 'product';
	$taxonomy_objects = get_object_taxonomies( $post_type, 'objects' );
	if( !empty( $taxonomy_objects ) ) {
		// Remove Category, Tag, Type, Visibility and Shipping Class as we already support them...
		unset( $taxonomy_objects['product_cat'] );
		unset( $taxonomy_objects['product_tag'] );
		unset( $taxonomy_objects['product_type'] );
		unset( $taxonomy_objects['product_visibility'] );
		unset( $taxonomy_objects['product_shipping_class'] );
	}
	if( !empty( $taxonomy_objects ) ) {
		// Remove any Attributes that snuck in...
		foreach( $taxonomy_objects as $key => $taxonomy_object ) {
			if( strstr( $key, 'pa_' ) !== false )
				unset( $taxonomy_objects[$key] );
		}
	}
	return $taxonomy_objects;

}

// Returns list of Product Add-on columns
function woo_ce_get_product_addons() {

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$post_type = 'global_product_addon';
		$args = array(
			'post_type' => $post_type,
			'numberposts' => -1
		);
		$output = array();

		// First grab the Global Product Add-ons
		$product_addons = get_posts( $args );
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				$meta = maybe_unserialize( get_post_meta( $product_addon->ID, '_product_addons', true ) );
				if( !empty( $meta ) ) {
					$size = count( $meta );
					for( $i = 0; $i < $size; $i++ ) {
						$output[] = (object)array(
							'post_name' => $meta[$i]['name'],
							'post_title' => $meta[$i]['name'],
							'form_title' => sprintf( __( 'Global Product Add-on: %s', 'woocommerce-exporter' ), $product_addon->post_title )
						);
					}
					unset( $size );
				}
				unset( $meta );
			}
		}

		// Custom Product Add-ons
		$custom_product_addons = woo_ce_get_option( 'custom_product_addons', '' );
		if( !empty( $custom_product_addons ) ) {
			foreach( $custom_product_addons as $custom_product_addon ) {
				if( !empty( $custom_product_addon ) ) {
					$output[] = (object)array(
						'post_name' => $custom_product_addon,
						'post_title' => woo_ce_clean_export_label( $custom_product_addon ),
						'form_title' => sprintf( __( 'Custom Product Add-on: %s', 'woocommerce-exporter' ), $custom_product_addon )
					);
				}
			}
		}
		unset( $custom_product_addons, $custom_product_addon );

		if( !empty( $output ) )
			return $output;
	}

}

// WooCommerce Tab Manager - https://woocommerce.com/products/woocommerce-tab-manager/
function woo_ce_get_product_tabs() {

	$post_type = 'wc_product_tab';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$product_tabs = new WP_Query( $args );
	if( !empty( $product_tabs->posts ) ) {
		return $product_tabs->posts;
	}

}

// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
function woo_ce_get_wccf_product_properties() {

	$post_type = 'wccf_product_prop';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$product_fields = new WP_Query( $args );
	if( !empty( $product_fields->posts ) ) {
		return $product_fields->posts;
	}

}

// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/
function woo_ce_get_wcff_admin_fields() {

	$post_type = 'wccaf';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids'
	);
	$admin_groups = new WP_Query( $args );
	if( !empty( $admin_groups->posts ) ) {
		$admin_fields = array();
		$prefix = 'wccaf_';
		$excluded_meta = array(
			$prefix . 'condition_rules',
			$prefix . 'location_rules',
			$prefix . 'group_rules',
			$prefix . 'pricing_rules',
			$prefix . 'fee_rules',
			$prefix . 'sub_fields_group_rules'
		);
		foreach( $admin_groups->posts as $post_id ) {
			$meta = get_post_meta( $post_id );
			foreach( $meta as $key => $meta_value ) {
				// Meta name must contain the prefix
				if( preg_match( '/' . $prefix . '/', $key ) ) {
					// Skip default meta
					if( !in_array( $key, $excluded_meta ) ) {
						$meta_value = json_decode( $meta_value[0] );
						if( !is_object( $meta_value ) )
							continue;

						$admin_fields[] = array(
							'name' => $meta_value->name,
							'label' => $meta_value->label,
							'type' => $meta_value->type
						);

					}
				}
			}
		}
		unset( $admin_groups, $meta );
		return $admin_fields;
	}

}

// WC Fields Factory - https://wordpress.org/plugins/wc-fields-factory/
function woo_ce_get_wcff_product_fields() {

	$post_type = 'wccpf';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids'
	);
	$product_groups = new WP_Query( $args );
	if( !empty( $product_groups->posts ) ) {
		$product_fields = array();
		$prefix = 'wccpf_';
		$excluded_meta = array(
			$prefix . 'condition_rules',
			$prefix . 'field_location_on_archive',
			$prefix . 'field_location_on_product'
		);
		foreach( $product_groups->posts as $post_id ) {
			$meta = get_post_meta( $post_id );
			foreach( $meta as $key => $meta_value ) {
				// Meta name must contain the prefix
				if( preg_match( '/' . $prefix . '/', $key ) ) {
					// Skip default meta
					if( !in_array( $key, $excluded_meta ) ) {
						$meta_value = json_decode( $meta_value[0] );
						if( !is_object( $meta_value ) )
							continue;

						$product_fields[] = array(
							'name' => $meta_value->name,
							'label' => $meta_value->label,
							'type' => $meta_value->type
						);

					}
				}
			}
		}
		unset( $product_groups, $meta );
		return $product_fields;
	}

}

function woo_ce_format_gpf_availability( $availability = null ) {

	$output = '';
	if( !empty( $availability ) ) {
		switch( $availability ) {

			case 'in stock':
				$output = __( 'In Stock', 'woocommerce-exporter' );
				break;

			case 'available for order':
				$output = __( 'Available For Order', 'woocommerce-exporter' );
				break;

			case 'preorder':
				$output = __( 'Pre-order', 'woocommerce-exporter' );
				break;

		}
	}

	return $output;

}

function woo_ce_format_gpf_condition( $condition ) {

	$output = '';
	if( !empty( $condition ) ) {
		switch( $condition ) {

			case 'new':
				$output = __( 'New', 'woocommerce-exporter' );
				break;

			case 'refurbished':
				$output = __( 'Refurbished', 'woocommerce-exporter' );
				break;

			case 'used':
				$output = __( 'Used', 'woocommerce-exporter' );
				break;

		}
	}

	return $output;

}

// Advanced Custom Fields - http://www.advancedcustomfields.com
function woo_ce_get_acf_product_fields() {

	global $wpdb;

	$fields = array();

	if( function_exists( 'acf_get_field_groups' ) ) {
		$post_type = 'product';
		$args = array(
			'post_type' => $post_type
		);
		$field_groups = acf_get_field_groups( $args );
		if( !empty( $field_groups ) ) {
			foreach( $field_groups as $field_group ) {
				$acf_fields = ( isset( $field_group['key'] ) ? acf_get_fields( $field_group['key'] ) : false );
				if( !empty( $acf_fields ) ) {
					foreach( $acf_fields as $acf_field ) {
						$fields[] = array(
							'name' => $acf_field['name'],
							'label' => $acf_field['label'],
							'hover' => __( 'Advanced Custom Fields', 'woocommerce-exporter' )
						);
					}
				}
			}
		}
		if( !empty( $fields ) ) {
			return $fields;
		}
	}

	// Fallback to legacy ACF
	$acf_version = ( defined( 'ACF_VERSION' ) ? ACF_VERSION : false );
	if( version_compare( $acf_version, '5.6', '>=' ) )
		$post_type = 'acf-field-group';
	else
		$post_type = 'acf';

	$args = array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'fields' => 'ids'
	);
	$field_groups = new WP_Query( $args );
	if( !empty( $field_groups->posts ) ) {
		$post_types = array( 'product', 'product_variation' );
		foreach( $field_groups->posts as $field_group ) {
			$has_fields = false;
			$rules = get_post_meta( $field_group, 'rule' );
			if( !empty( $rules ) ) {
				$size = count( $rules );
				for( $i = 0; $i < $size; $i++ ) {
					if( ( $rules[$i]['param'] == 'post_type' ) && ( $rules[$i]['operator'] == '==' ) && ( in_array( $rules[$i]['value'], $post_types ) ) ) {
						$has_fields = true;
						$i = $size;
					}
				}
			}
			unset( $rules );

			if( !$has_fields )
				continue;

			$custom_fields_sql = "SELECT `meta_value` FROM `" . $wpdb->postmeta . "` WHERE `post_id` = " . absint( $field_group ) . " AND `meta_key` LIKE 'field_%'";
			$custom_fields = $wpdb->get_col( $custom_fields_sql );
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$custom_field = maybe_unserialize( $custom_field );
					$fields[] = array(
						'name' => $custom_field['name'],
						'label' => $custom_field['label'],
						'hover' => __( 'Advanced Custom Fields', 'woocommerce-exporter' )
					);
				}
			}
			unset( $custom_fields, $custom_field );
		}

		return $fields;

	}

}

// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
function woo_ce_get_wholesale_prices_roles() {

	$output = false;
	$option_name = ( defined( 'WWP_OPTIONS_REGISTERED_CUSTOM_ROLES' ) ? WWP_OPTIONS_REGISTERED_CUSTOM_ROLES : 'wwp_options_registered_custom_roles' );
	$wholesale_roles = unserialize( get_option( $option_name ) );
	if( is_array( $wholesale_roles ) )
		$output = $wholesale_roles;
	unset( $wholesale_roles );

	return $output;

}

// FooEvents for WooCommerce - https://www.fooevents.com/
function woo_ce_format_events_is_event( $is_event = '' ) {

	$is_event = strtolower( $is_event );
	switch( $is_event ) {

		case 'event':
			$output = __( 'Yes', 'woocommerce-exporter' );
			break;

		default:
		case 'notevent':
			$output = __( 'No', 'woocommerce-exporter' );
			break;

	}

	return $output;

}