<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Hide "Coupon" word in cart totals',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Hide "Coupon" word in cart totals',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="hide_coupon_word_in_totals">
                <input <?php checked($options['hide_coupon_word_in_totals']) ?>
                    name="hide_coupon_word_in_totals" id="hide_coupon_word_in_totals" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
