<?php

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ValueComparisonCondition;

defined('ABSPATH') or exit;

?>
<div class="wdp-column wdp-condition-subfield wdp-condition-field-method">
    <select name="rule[conditions][{c}][options][<?php echo ValueComparisonCondition::COMPARISON_VALUE_METHOD_KEY ?>]">
        <option value="<"><?php _e('&lt;', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
        <option value="<="><?php _e('&lt;=', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
        <option value=">="><?php _e('&gt;=', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
        <option value=">"><?php _e('&gt;', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
    </select>
</div>

<div class="wdp-column wdp-condition-subfield wdp-condition-field-value">
    <input name="rule[conditions][{c}][options][<?php echo ValueComparisonCondition::COMPARISON_VALUE_KEY ?>]" type="number" placeholder="0.00" min="0">
</div>
