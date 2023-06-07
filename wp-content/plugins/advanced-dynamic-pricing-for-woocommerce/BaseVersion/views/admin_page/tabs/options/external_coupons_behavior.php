<?php
if ( ! defined('ABSPATH')) {
    exit;
}

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('External coupons', 'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp">
        <div style="display:table; margin-top: -0.5em; border-spacing: 0.5em">
            <p style="display: table-row">
                <label for="external_cart_coupons_behavior" style="display: table-cell">
                    <?php _e("Cart coupons", 'advanced-dynamic-pricing-for-woocommerce') ?>
                </label>
                <select id="external_cart_coupons_behavior" name="external_cart_coupons_behavior"
                        style="display: table-cell">
                    <option <?php selected($options['external_cart_coupons_behavior'], 'apply') ?> value="apply">
                        <?php _e("Apply", 'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                    <option <?php selected($options['external_cart_coupons_behavior'], 'disable_if_any_rule_applied') ?>
                        value="disable_if_any_rule_applied">
                        <?php _e("Disable all if any rule applied", 'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                    <option <?php selected($options['external_cart_coupons_behavior'], 'disable_if_any_of_cart_items_updated') ?>
                        value="disable_if_any_of_cart_items_updated">
                        <?php _e("Disable all if any of cart items updated", 'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                </select>
            </p>
            <p style="display: table-row">
                <label for="external_product_coupons_behavior" style="display: table-cell">
                    <?php _e("Product coupons", 'advanced-dynamic-pricing-for-woocommerce') ?>
                </label>
                <select id="external_product_coupons_behavior" name="external_product_coupons_behavior"
                        style="display: table-cell">
                    <option <?php selected($options['external_product_coupons_behavior'], 'apply') ?> value="apply">
                        <?php _e("Apply", 'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                    <option <?php selected($options['external_product_coupons_behavior'], 'disable_if_any_rule_applied') ?>
                        value="disable_if_any_rule_applied">
                        <?php _e("Disable all if any rule applied", 'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                    <option <?php selected($options['external_product_coupons_behavior'], 'disable_if_any_of_cart_items_updated') ?>
                        value="disable_if_any_of_cart_items_updated">
                        <?php _e("Disable all if any of cart items updated", 'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                </select>
            </p>
        </div>
    </td>
</tr>
