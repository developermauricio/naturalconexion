<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Show striked prices in the cart',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show striked prices in the cart',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="show_striked_prices">
                <input name="show_striked_prices" value="0" type="hidden">
                <input <?php checked($options['show_striked_prices']); ?>
                    name="show_striked_prices" id="show_striked_prices" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
