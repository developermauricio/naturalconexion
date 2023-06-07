<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="process_product_strategy_after_use_price">
            <?php _e('In "After matching condition" mode - use product price from cart', 'advanced-dynamic-pricing-for-woocommerce') ?>
        </label>
    </th>
    <td class="forminp">
        <select name="process_product_strategy_after_use_price" id="process_product_strategy_after_use_price">
            <option <?php selected($options['process_product_strategy_after_use_price'], 'first'); ?> value="first">
                <?php _e('First matched', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </option>

                <option <?php selected($options['process_product_strategy_after_use_price'], 'last'); ?> value="last">
                <?php _e('Last matched', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </option>

            <option <?php selected($options['process_product_strategy_after_use_price'], 'cheapest'); ?> value="cheapest">
                <?php _e('Cheapest', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </option>

            <option <?php selected($options['process_product_strategy_after_use_price'], 'most_expensive'); ?> value="most_expensive">
                <?php _e('Most expensive', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </option>
        </select>
    </td>
</tr>
