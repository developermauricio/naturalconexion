<?php
// Quick Export

// HTML template for Filter Products by Brand widget on Store Exporter screen
function woo_ce_products_filter_by_product_brand() {

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() == false )
		return;

	$args = array(
		'hide_empty' => 1,
		'orderby' => 'term_group'
	);
	$product_brands = woo_ce_get_product_brands( $args );
	$types = woo_ce_get_option( 'product_brands', array() );

	ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-brands" name="product_filter_brand_include"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Brand', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-brands" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_brands ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Brand...', 'woocommerce-exporter' ); ?>" name="product_filter_brand[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_brands as $product_brand ) { ?>
				<option value="<?php echo $product_brand->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_brand->term_id, $types, false ), true ) : '' ); ?><?php disabled( $product_brand->count, 0 ); ?>><?php echo woo_ce_format_product_category_label( $product_brand->name, $product_brand->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_brand->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Brands were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Brands you want to filter exported Products by. Product Brands not assigned to Products are hidden from view. Default is to include all Product Brands.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-brands -->
<?php
	ob_end_flush();

}

// HTML template for Filter Products by Product Vendor widget on Store Exporter screen
function woo_ce_products_filter_by_product_vendor() {

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) == false && woo_ce_detect_export_plugin( 'yith_vendor' ) == false )
		return;

	$args = array(
		'hide_empty' => 1
	);
	$product_vendors = woo_ce_get_product_vendors( $args, 'full' );

	ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-vendors" name="product_filter_vendor_include" /> <?php _e( 'Filter Products by Product Vendor', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-vendors" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_vendors ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Vendor...', 'woocommerce-exporter' ); ?>" name="product_filter_vendor[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_vendors as $product_vendor ) { ?>
				<option value="<?php echo $product_vendor->term_id; ?>"<?php disabled( $product_vendor->count, 0 ); ?>><?php echo $product_vendor->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Vendors you want to filter exported Products by. Product Vendors not assigned to Products are hidden from view. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-vendors -->
<?php
	ob_end_flush();

}

// HTML template for Filter Products by Language widget on Store Exporter screen
function woo_ce_products_filter_by_language() {

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( !woo_ce_detect_wpml() || !woo_ce_detect_export_plugin( 'wpml_wc' ) )
		return;

	$languages = ( function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=N' ) : array() );

	ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-language" name="product_filter_language_include" /> <?php _e( 'Filter Products by Language', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-language" class="separator">
	<ul>
		<li>
<?php if( !empty( $languages ) ) { ?>
			<select id="products-filters-language" data-placeholder="<?php _e( 'Choose a Language...', 'woocommerce-exporter' ); ?>" name="product_filter_language[]" multiple style="width:95%;">
				<option value=""><?php _e( 'Default', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $languages as $key => $language ) { ?>
				<option value="<?php echo $key; ?>"><?php echo $language['native_name']; ?> (<?php echo $language['translated_name']; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Languages were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Language\'s you want to filter exported Products by. Default is to include all Language\'s.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-language -->
<?php
	ob_end_flush();

}

// Scheduled Export

function woo_ce_scheduled_export_product_filter_by_product_brand( $post_ID = 0 ) {

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() == false )
		return;

	$args = array(
		'hide_empty' => 1,
		'orderby' => 'term_group'
	);
	$product_brands = woo_ce_get_product_brands( $args );
	$types = get_post_meta( $post_ID, '_filter_product_brand', true );

	ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_brand"><?php _e( 'Product brand', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $product_brands ) ) { ?>
	<select id="product_filter_brand" data-placeholder="<?php _e( 'Choose a Product Brand...', 'woocommerce-exporter' ); ?>" name="product_filter_brand[]" multiple class="chzn-select select short" style="width:95%;">
<?php foreach( $product_brands as $product_brand ) { ?>
		<option value="<?php echo $product_brand->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $product_brand->term_id, $types ) : false ), true ); ?><?php disabled( $product_brand->count, 0 ); ?>><?php echo $product_brand->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_brand->term_id ); ?>)</option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product Brand\'s you want to filter exported Products by. Default is to include all Product Brands.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Product Brands were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_product_filter_by_language( $post_ID = 0 ) {

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( !woo_ce_detect_wpml() || !woo_ce_detect_export_plugin( 'wpml_wc' ) )
		return;

	$languages = ( function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=N' ) : array() );
	$types = get_post_meta( $post_ID, '_filter_product_language', true );

	ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_language"><?php _e( 'Language', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $languages ) ) { ?>
	<select id="product_filter_language" data-placeholder="<?php _e( 'Choose a Language...', 'woocommerce-exporter' ); ?>" name="product_filter_language[]" multiple style="width:95%;">
		<option value=""><?php _e( 'Default', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $languages as $key => $language ) { ?>
		<option value="<?php echo $key; ?>"<?php selected( ( !empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo $language['native_name']; ?> (<?php echo $language['translated_name']; ?>)</option>
	<?php } ?>
	</select>
<?php } else { ?>
	<?php _e( 'No Languages were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_product_filter_by_product_vendor( $post_ID = 0 ) {

	if( woo_ce_detect_export_plugin( 'vendors' ) == false && woo_ce_detect_export_plugin( 'yith_vendor' ) == false )
		return;

	$args = array(
		'hide_empty' => 1
	);
	$product_vendors = woo_ce_get_product_vendors( $args, 'full' );
	$types = get_post_meta( $post_ID, '_filter_product_vendor', true );

	ob_start(); ?>
<?php if( !empty( $product_vendors ) ) { ?>
<p class="form-field discount_type_field">
	<label for="product_filter_vendor"><?php _e( 'Product vendor', 'woocommerce-exporter' ); ?></label>
	<select data-placeholder="<?php _e( 'Choose a Product Vendor...', 'woocommerce-exporter' ); ?>" name="product_filter_vendor[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_vendors as $product_vendor ) { ?>
		<option value="<?php echo $product_vendor->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $product_vendor->term_id, $types ) : false ), true ); ?><?php disabled( $product_vendor->count, 0 ); ?>><?php echo $product_vendor->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ); ?>)</option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product Vendor\'s you want to filter exported Products by. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>

<?php
	ob_end_flush();

}
?>