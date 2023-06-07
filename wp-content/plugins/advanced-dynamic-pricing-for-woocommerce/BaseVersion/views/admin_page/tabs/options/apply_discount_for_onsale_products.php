<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="discount_for_onsale"><?php _e('How to apply rules to a product that already has a sale price',
                'advanced-dynamic-pricing-for-woocommerce') ?></label>
    </th>
    <td class="forminp">
        <select name="discount_for_onsale">
            <option <?php selected($options['discount_for_onsale'], 'sale_price'); ?>
                value="sale_price"><?php _e('Don\'t apply discount if product is already on sale', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
            <option <?php selected($options['discount_for_onsale'], 'discount_regular'); ?>
                value="discount_regular"><?php _e('Discount regular price',
                    'advanced-dynamic-pricing-for-woocommerce') ?></option>
            <option <?php selected($options['discount_for_onsale'], 'discount_sale'); ?>
                value="discount_sale"><?php _e('Discount sale price',
                    'advanced-dynamic-pricing-for-woocommerce') ?></option>
            <option <?php selected($options['discount_for_onsale'], 'compare_discounted_and_sale'); ?>
                value="compare_discounted_and_sale"><?php _e('Best between discounted regular price and sale price',
                    'advanced-dynamic-pricing-for-woocommerce') ?></option>
        </select>
    </td>
</tr>
