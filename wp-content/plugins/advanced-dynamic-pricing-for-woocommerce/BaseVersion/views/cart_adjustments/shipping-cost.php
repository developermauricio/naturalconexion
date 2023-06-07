<?php
defined('ABSPATH') or exit;

?>
<div class="wdp-column wdp-cart-adjustment-value" style="font-size: 14px;">
    <div>
        <select
            data-list="all_shipping_methods"
            data-field="preloaded"
            data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
            name="rule[cart_adjustments][{ca}][options][0]"
            class="adjustment-value"
        >
        </select>
    </div>
</div>

<div class="wdp-column wdp-cart-adjustment-value">
    <input name="rule[cart_adjustments][{ca}][options][1]" class="adjustment-value" type="number" placeholder="0.00"
           step="any" min="0">
</div>
