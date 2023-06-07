<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <?php _e('Apply pricing rules while doing API request', 'advanced-dynamic-pricing-for-woocommerce') ?>
    </th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <label for="update_prices_while_doing_rest_api">
                <input <?php checked($options['update_prices_while_doing_rest_api']) ?>
                    name="update_prices_while_doing_rest_api" id="update_prices_while_doing_rest_api" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
