<?php

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;

defined('ABSPATH') or exit;

?>
<div class="wdp-column wdp-condition-subfield wdp-condition-field-method">
    <select name="rule[conditions][{c}][options][<?php echo ListComparisonCondition::COMPARISON_LIST_METHOD_KEY ?>]">
        <option value="in_list" selected><?php _e('in list', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
        <option value="not_in_list"><?php _e('not in list', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
    </select>
</div>

<div class="wdp-column wdp-condition-subfield wdp-condition-field-value">
    <select multiple
            data-list="user_roles"
            data-field="preloaded"
            data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
            name="rule[conditions][{c}][options][<?php echo ListComparisonCondition::COMPARISON_LIST_KEY ?>][]">
    </select>
</div>
