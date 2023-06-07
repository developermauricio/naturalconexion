<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Show bulk table regardless of conditions',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show bulk table regardless of conditions',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="discount_table_ignores_conditions">
                <input <?php checked($options['discount_table_ignores_conditions']); ?>
                        name="discount_table_ignores_conditions" id="discount_table_ignores_conditions" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
