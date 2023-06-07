<?php
defined('ABSPATH') or exit;

?>
    <div class="wdp-column wdp-cart-adjustment-value">
        <input name="rule[cart_adjustments][{ca}][options][0]" class="adjustment-value" type="number"
               placeholder="0.00" step="any" min="0">
    </div>
    <div class="wdp-column wdp-cart-adjustment-value">
        <input name="rule[cart_adjustments][{ca}][options][1]" class="adjustment-value" type="text"
               placeholder="<?php _e('discount name', 'advanced-dynamic-pricing-for-woocommerce') ?>">
    </div>
<?php if ($item['type'] === 'discount__percentage'): ?>
    <div class="wdp-column wdp-cart-adjustment-value">
        <input name="rule[cart_adjustments][{ca}][options][2]" class="adjustment-value" type="number"
               placeholder="<?php _e('max discount', 'advanced-dynamic-pricing-for-woocommerce') ?>" step="any" min="0">
    </div>
<?php endif; ?>
