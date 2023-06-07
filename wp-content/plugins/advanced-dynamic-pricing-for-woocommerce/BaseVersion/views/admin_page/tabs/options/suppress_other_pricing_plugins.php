<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Suppress other pricing plugins in frontend',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Suppress other pricing plugins in frontend',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="suppress_other_pricing_plugins">
                <input <?php checked($options['suppress_other_pricing_plugins']) ?>
                    name="suppress_other_pricing_plugins" id="suppress_other_pricing_plugins" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
