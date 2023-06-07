<?php
defined('ABSPATH') or exit;

?>

<!--HISTORY-->
<div id="wdp_reporter_history_chunk_template">
    <div class="history-row rule-tooltip {is_replaced}" data-rule-id="{rule_id}">
        <div class="history-cell">{old_price}</div>
        <div class="history-cell">{amount}</div>
        <div class="history-cell">{new_price}</div>
    </div>
</div>

<div id="wdp_reporter_tab_items_single_item_gifted_history_template">
    <div class="history-gifted rule-tooltip {is_replaced}" data-rule-id="{rule_id}">
        <?php echo __('Gifted!', 'advanced-dynamic-pricing-for-woocommerce'); ?>
    </div>
</div>

<div id="wdp_reporter_tab_items_single_item_empty_history_template">
    <div class="item-history-row">
        <?php echo __('No changes!', 'advanced-dynamic-pricing-for-woocommerce'); ?>
    </div>
</div>
