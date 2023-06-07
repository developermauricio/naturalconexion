<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Allow to exclude products in filters',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Allow to exclude products in filters',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="allow_to_exclude_products">
                <input name="allow_to_exclude_products" value="0" type="hidden">
                <input <?php checked($options['allow_to_exclude_products']) ?>
                    name="allow_to_exclude_products" id="allow_to_exclude_products" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
