<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Show striked subtotal in cart totals',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show striked subtotal in cart totals',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="show_cross_out_subtotal_in_cart_totals">
                <input <?php checked($options['show_cross_out_subtotal_in_cart_totals']); ?>
                    name="show_cross_out_subtotal_in_cart_totals" id="show_cross_out_subtotal_in_cart_totals"
                    type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
