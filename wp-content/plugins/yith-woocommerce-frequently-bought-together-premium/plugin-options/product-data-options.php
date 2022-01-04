<?php
/**
 * PRODUCT DATA OPTION
 */

$product_data = array(
	array(
		'class'             => 'show_if_variable',
		'default_variation' => array(
			'name'  => 'yith_wfbt_default_variation',
			'label' => __( 'Select default variation', 'yith-woocommerce-frequently-bought-together' ),
			'type'  => 'variation_select',
		),
	),

	array(
		'products_type'   => array(
			'name'    => 'yith_wfbt_product_type',
			'label'   => __( 'Products type', 'yith-woocommerce-frequently-bought-together' ),
			'desc'    => __( 'Choose which products you want to use as frequently bought products', 'yith-woocommerce-frequently-bought-together' ),
			'type'    => 'radio',
			'options' => array(
				'related'     => __( 'Use related', 'yith-woocommerce-frequently-bought-together' ),
				'cross-sells' => __( 'Use cross-sells', 'yith-woocommerce-frequently-bought-together' ),
				'up-sells'    => __( 'Use up-sells', 'yith-woocommerce-frequently-bought-together' ),
				'custom'      => __( 'Custom products', 'yith-woocommerce-frequently-bought-together' ),
			),
			'default' => 'custom',
		),
		'products'        => array(
			'name'  => 'yith_wfbt_ids',
			'label' => __( 'Select products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => __( 'Select products for "Frequently bought together" group', 'yith-woocommerce-frequently-bought-together' ),
			'type'  => 'product_select',
			'data'  => array(
				'deps'  => 'yith_wfbt_product_type',
				'value' => 'custom',
			),
		),
		'visibility_type' => array(
			'name'    => 'yith_wfbt_visibility_type',
			'label'   => __( 'Show', 'yith-woocommerce-frequently-bought-together' ),
			'desc'    => __( 'Choose whether to show all products or set a limited number of products that will show randomly', 'yith-woocommerce-frequently-bought-together' ),
			'type'    => 'radio',
			'options' => array(
				'all'      => __( 'All selected products', 'yith-woocommerce-frequently-bought-together' ),
				'randomly' => __( 'Show randomly a limited number of products', 'yith-woocommerce-frequently-bought-together' ),
			)
		),
		'num_visible'     => array(
			'name'    => 'yith_wfbt_num',
			'label'   => __( 'Number of products to show randomly', 'yith-woocommerce-frequently-bought-together' ),
			'desc'    => __( 'Set how many products to show excluding current one for "Frequently bought together" group', 'yith-woocommerce-frequently-bought-together' ),
			'type'    => 'number',
			'data'    => array(
				'deps'  => 'yith_wfbt_visibility_type',
				'value' => 'randomly',
			),
			'attr'    => array(
				'min' => 1,
			)
		),
		'show_unchecked'  => array(
			'name'  => 'yith_wfbt_show_unchecked',
			'label' => __( 'Show products unchecked', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => __( 'Show all products in group unchecked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'  => 'checkbox',
		),
		'additional_text' => array(
			'name'  => 'yith_wfbt_additional_text',
			'label' => __( 'Set additional text', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => __( 'Set additional text to show before products', 'yith-woocommerce-frequently-bought-together' ),
			'type'  => 'textarea',
		),
		'discount_enabled'  => array(
			'name'  => 'yith_wfbt_discount_enabled',
			'label' => __( 'Apply a discount on linked products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => '',
			'type'  => 'checkbox',
		),
		'discount_type'         => array(
			'name'    => 'yith_wfbt_discount_type',
			'label'   => __( 'Discount type', 'yith-woocommerce-frequently-bought-together' ),
			'type'    => 'select',
			'options' => array(
				'fixed'      => __( 'Fixed amount', 'yith-woocommerce-frequently-bought-together' ),
				'percentage' => __( 'Percentage', 'yith-woocommerce-frequently-bought-together' ),
			),
			'data'    => array(
				'deps'  => 'yith_wfbt_discount_enabled',
				'value' => 'yes',
			),
		),
		'discount_fixed'        => array(
			'name'  => 'yith_wfbt_discount_fixed',
			'label' => __( 'Discount amount', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => get_woocommerce_currency_symbol(),
			'type'  => 'text',
			'class' => 'wc_input_price inline-desc',
			'data'  => array(
				'deps'  => 'yith_wfbt_discount_enabled,yith_wfbt_discount_type',
				'value' => 'yes,fixed',
			),
		),
		'discount_percentage'   => array(
			'name'  => 'yith_wfbt_discount_percentage',
			'label' => __( 'Discount percentage', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => '%',
			'type'  => 'number',
			'class' => 'wc-product-number inline-desc',
			'attr'  => array(
				'min' => 0,
				'max' => 100,
			),
			'data'  => array(
				'deps'  => 'yith_wfbt_discount_enabled,yith_wfbt_discount_type',
				'value' => 'yes,percentage',
			),
		),
		'discount_conditions'  => array(
			'name'  => 'yith_wfbt_discount_conditions',
			'label' => __( 'Apply conditions to discount', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => '',
			'type'  => 'checkbox',
			'data'  => array(
				'deps'  => 'yith_wfbt_discount_enabled',
				'value' => 'yes',
			),
		),
		'discount_min_spend'    => array(
			'name'  => 'yith_wfbt_discount_min_spend',
			'label' => __( 'Apply discount only if the user spend at least', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => get_woocommerce_currency_symbol(),
			'type'  => 'text',
			'class' => 'wc_input_price inline-desc',
			'data'    => array(
				'deps'  => 'yith_wfbt_discount_enabled,yith_wfbt_discount_conditions',
				'value' => 'yes,yes',
			),
		),
		'discount_min_products' => array(
			'name'  => 'yith_wfbt_discount_min_products',
			'label' => __( 'Apply discount only if the user choose at least', 'yith-woocommerce-frequently-bought-together' ),
			'desc'  => _x( 'products', 'Inline input label for admin option', 'yith-woocommerce-frequently-bought-together' ),
			'type'  => 'number',
			'class' => 'wc-product-number inline-desc',
			'attr'  => array(
				'min' => 2,
			),
			'data'    => array(
				'deps'  => 'yith_wfbt_discount_enabled,yith_wfbt_discount_conditions',
				'value' => 'yes,yes',
			),
		),
	)
);

return apply_filters( 'yith_wcfbt_panel_product_data_options', $product_data );