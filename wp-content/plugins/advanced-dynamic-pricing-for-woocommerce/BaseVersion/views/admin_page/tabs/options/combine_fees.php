<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Combine multiple fees',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Combine multiple fees', 'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="combine_fees">
                <input <?php checked($options['combine_fees']) ?>
                    name="combine_fees" id="combine_fees" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
