<?php

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Show unmodified price if product discounts added as coupon',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show unmodified price if product discounts added as coupon',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="show_unmodified_price_if_discounts_with_coupon">
                <input name="show_unmodified_price_if_discounts_with_coupon" value="0" type="hidden">
                <input <?php checked($options['show_unmodified_price_if_discounts_with_coupon']); ?>
                    name="show_unmodified_price_if_discounts_with_coupon" id="show_unmodified_price_if_discounts_with_coupon" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
