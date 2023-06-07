<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Use first range as minimum quantity if bulk rule is active',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Use first range as minimum quantity if bulk rule is active',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="use_first_range_as_min_qty">
                <input <?php checked($options['use_first_range_as_min_qty']); ?>
                    name="use_first_range_as_min_qty" id="use_first_range_as_min_qty" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
