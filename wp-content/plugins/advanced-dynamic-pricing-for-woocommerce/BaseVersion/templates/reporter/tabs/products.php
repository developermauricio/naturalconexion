<?php
defined('ABSPATH') or exit;

?>


<div id="wdp_reporter_tab_products_template">
    <div class="product-row product-header">
        <div class="product-cell index"><?php echo __('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="product-cell large"><?php echo __('Name', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="product-cell small"><?php echo __('Original price',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="product-cell small"><?php echo __('Discounted price',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="product-cell product-history-cell large"><?php echo __('History',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    </div>

    {product_rows}
</div>

<div id="wdp_reporter_tab_products_single_product_template">
    <div class="product-row" data-product-id="{product_id}" data-parent-product-id="{parent_product_id}">
        <div class="product-cell index">{index}</div>
        <div class="product-cell large title"><a href="{page_url}" target="_blank">{name}</a></div>
        <div class="product-cell small">{original_price}</div>
        <div class="product-cell small">{discounted_price}</div>
        <div class="product-cell product-history-cell large">{history}</div>
    </div>
</div>
