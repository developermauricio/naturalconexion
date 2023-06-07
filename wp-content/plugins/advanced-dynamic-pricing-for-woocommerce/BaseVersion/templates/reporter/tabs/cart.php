<?php
defined('ABSPATH') or exit;

?>

<div id="wdp_reporter_tab_cart_empty_template">
    <h3>
        <?php echo __('Cart is empty', 'advanced-dynamic-pricing-for-woocommerce'); ?>
    </h3>
</div>

<div id="wdp_reporter_tab_link_template">
    <div class="tab-link {selected}" data-tab-id="{tab_key}">{tab_label}</div>
</div>

<div id="wdp_reporter_tab_template">
    <div id="wdp-report-{tab_key}-tab" class="tab-content {active}">
        <div class="tab-links-list {sub_tabs_selector_class}">{sub_tabs_selector_html}</div>

        {tab_content_html}
    </div>
</div>


<!--CART ITEMS-->
<div id="wdp_reporter_tab_cart_items_template">
    <div class="item-row item-header">
        <div class="row-cell index"><?php echo __('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell item-title large"><?php echo __('Title',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell item-qty small"><?php echo __('Quantity',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell item-price small"><?php echo __('Initial price',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell item-price small"><?php echo __('Price',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell item-history large"><?php echo __('History',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    </div>

    {items}
</div>

<div id="wdp_reporter_tab_items_single_item_template">
    <div class="item-row" data-cart-item-hash="{hash}">
        <div class="row-cell index">{index}</div>
        <div class="row-cell item-title large title">{title}</div>
        <div class="row-cell item-qty small">{quantity}</div>
        <div class="row-cell item-price small">{original_price}</div>
        <div class="row-cell item-price small">{price}</div>
        <div class="row-cell item-history large">{history}</div>
    </div>
</div>

<!--COUPONS-->
<div id="wdp_reporter_tab_cart_coupons_template">
    <div class="item-coupon-row item-header">
        <div class="row-cell index"><?php echo __('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell coupon-code large"><?php echo __('Coupon code',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell coupon-amount small"><?php echo __('Coupon amount',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell coupon-rules large"><?php echo __('Affected',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    </div>

    {coupons}
</div>

<div id="wdp_reporter_tab_items_single_coupon_template">
    <div class="item-coupon-row" data-coupon-code="{coupon_code}">
        <div class="row-cell index">{index}</div>
        <div class="row-cell coupon-code large title">{coupon_code}</div>
        <div class="row-cell coupon-amount small">{coupon_amount}</div>
        <div class="row-cell coupon-rules large">{affected_rules}</div>
    </div>
</div>

<!--FEES-->
<div id="wdp_reporter_tab_cart_fees_template">
    <div class="item-fee-row item-header">
        <div class="row-cell index"><?php echo __('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell fee-name large"><?php echo __('Name', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell fee-amount small"><?php echo __('Fee amount',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell fee-rules large"><?php echo __('Affected rules',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    </div>

    {fees}
</div>

<div id="wdp_reporter_tab_items_single_fee_template">
    <div class="item-fee-row" data-fee-id="{fee_id}">
        <div class="row-cell index">{index}</div>
        <div class="row-cell fee-name large title">{fee_name}</div>
        <div class="row-cell fee-amount small">{fee_amount}</div>
        <div class="row-cell fee-rules large">{affected_rules}</div>
    </div>
</div>

<!--SHIPPING-->
<div id="wdp_reporter_tab_cart_shipping_template">
    {shipping_packages}
</div>

<div id="wdp_reporter_tab_cart_shipping_package_template">
    <h3>{package_title}</h3>

    <div class="item-fee-row item-header">
        <div class="row-cell index"><?php echo __('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell large"><?php echo __('Label', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell small"><?php echo __('Initial cost', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell small"><?php echo __('Cost', 'advanced-dynamic-pricing-for-woocommerce'); ?></div>
        <div class="row-cell large"><?php echo __('Affected rules',
                'advanced-dynamic-pricing-for-woocommerce'); ?></div>
    </div>

    {shipping_rates}
</div>

<div id="wdp_reporter_tab_items_single_shipping_rate_template">
    <div class="item-fee-row" data-shipping-rate-id="{instance_id}">
        <div class="row-cell index">{index}</div>
        <div class="row-cell fee-name large title">{label}</div>
        <div class="row-cell fee-amount small">{initial_cost}</div>
        <div class="row-cell fee-amount small">{cost}</div>
        <div class="row-cell fee-rules large">{affected_rules}</div>
    </div>
</div>

<div id="wdp_reporter_tab_items_single_free_shipping_rate_template">
    <div class="history-gifted rule-tooltip" data-rule-id="{rule_id}">
        <?php echo __('Free shipping!', 'advanced-dynamic-pricing-for-woocommerce'); ?>
    </div>
</div>

<!--CART ADJUSTMENT HISTORY-->
<div id="wdp_reporter_tab_items_cart_adj_history_chink_template">
    <div class="cart-adj-history-row rule-tooltip" data-rule-id="{rule_id}">
        <div class="cart-adj-history-cell item-history-amount">{amount}</div>
    </div>
</div>

<div id="wdp_reporter_tab_items_cart_merged_coupon_chink_template">
    <div class="rule-tooltip adp-margin-bottom adp-border adp-padding" data-rule-id="{rule_id}">
        <div>{type_title}</div>
        <div>{total_by_items_html}</div>
    </div>
</div>

<div id="wdp_reporter_tab_items_cart_merged_coupon_dist_item_chink_template">
    <div class="adp-align-left adp-margin-bottom">
        <span> - {name} x {qty}</span> - <span>{amount}</span>
    </div>
</div>


