<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Disable shipping calculation',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Disable shipping calculation',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="disable_shipping_calc_during_process">
                <input <?php checked($options['disable_shipping_calc_during_process']) ?>
                    name="disable_shipping_calc_during_process" id="disable_shipping_calc_during_process" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
