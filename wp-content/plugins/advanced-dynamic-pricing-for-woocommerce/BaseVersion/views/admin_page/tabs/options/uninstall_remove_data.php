<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Remove all data on uninstall',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Remove all data on uninstall', 'advanced-dynamic-pricing-for-woocommerce') ?></span>
            </legend>
            <label for="uninstall_remove_data">
                <input <?php checked($options['uninstall_remove_data']) ?>
                    name="uninstall_remove_data" id="uninstall_remove_data" type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
