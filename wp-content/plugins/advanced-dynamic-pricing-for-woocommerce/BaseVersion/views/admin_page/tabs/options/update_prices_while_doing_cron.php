<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <?php _e('Apply pricing rules while doing cron', 'advanced-dynamic-pricing-for-woocommerce') ?>
    </th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <label for="update_prices_while_doing_cron">
                <input <?php checked($options['update_prices_while_doing_cron']) ?>
                    name="update_prices_while_doing_cron" id="update_prices_while_doing_cron" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
