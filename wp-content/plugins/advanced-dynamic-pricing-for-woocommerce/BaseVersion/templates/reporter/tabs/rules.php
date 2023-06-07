<?php
defined('ABSPATH') or exit;

?>

<div id="wdp_reporter_tab_rules_template">
    <div class="rule-timing-tables">
        <div class="rule-timing-table {cart_table_classes}">
            {cart_table}
        </div>
        <div class="rule-timing-table {products_table_classes}">
            {products_table}
        </div>
    </div>
</div>


<div id="wdp_reporter_tab_rules_products_table_template">
    <div class="rule-table-title"><?php echo __('Applied to visible products',
            'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    <div class="rule-row rule-header">
        <div class="rule-cell index"><?php echo __('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="rule-cell large"><?php echo __('Title', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="rule-cell "><?php echo __('Timing', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    </div>

    {rule_rows}
</div>

<div id="wdp_reporter_tab_rules_cart_table_template">
    <div class="rule-table-title"><?php echo __('Applied to cart items',
            'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    <div class="rule-row rule-header">
        <div class="rule-cell index"><?php echo __('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="rule-cell large"><?php echo __('Title', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    </div>

    {rule_rows}
</div>

<div id="wdp_reporter_tab_rules_single_rule_template">
    <div class="rule-row">
        <div class="rule-cell index">{index}</div>
        <div class="rule-cell large title"><a href="{edit_page_url}" target="_blank">{title}</a></div>
    </div>
</div>
