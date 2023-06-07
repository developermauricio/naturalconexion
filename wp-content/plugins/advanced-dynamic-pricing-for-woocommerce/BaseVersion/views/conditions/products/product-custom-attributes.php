<?php

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\RangeValueCondition;

defined('ABSPATH') or exit;

?>
<div class="wdp-column wdp-condition-subfield wdp-condition-field-qty">
    <input type="number" placeholder="qty" min="1" name="rule[conditions][{c}][options][<?php echo RangeValueCondition::START_RANGE_KEY ?>]" value="1">
</div>

<div class="wdp-column wdp-condition-field-qty-separator">—</div>

<div class="wdp-column wdp-condition-subfield wdp-condition-field-qty">
    <input type="number" placeholder="qty" min="1" name="rule[conditions][{c}][options][<?php echo RangeValueCondition::END_RANGE_KEY ?>]" value="">
</div>

<div class="wdp-column wdp-condition-subfield wdp-condition-field-method">
    <select name="rule[conditions][{c}][options][<?php echo ListComparisonCondition::COMPARISON_LIST_METHOD_KEY ?>]">
        <option value="in_list" selected><?php _e('in list', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
        <option value="not_in_list"><?php _e('not in list', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
        <option value="not_containing"><?php _e('not containing',
                'advanced-dynamic-pricing-for-woocommerce') ?></option>
    </select>
</div>

<div class="wdp-column wdp-condition-subfield wdp-condition-field-value">
    <div>
        <select multiple
                data-list="product_custom_attributes"
                data-field="autocomplete"
                data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                name="rule[conditions][{c}][options][<?php echo ListComparisonCondition::COMPARISON_LIST_KEY ?>][]">
        </select>
    </div>
</div>
