<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <?php _e('Use prices modified by get_price hook', 'advanced-dynamic-pricing-for-woocommerce') ?>
    </th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Use prices modified by get_price hook',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="initial_price_context">
                <input <?php checked('view', $options['initial_price_context']); ?> value="view"
                                                                                    name="initial_price_context"
                                                                                    id="initial_price_context"
                                                                                    type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
