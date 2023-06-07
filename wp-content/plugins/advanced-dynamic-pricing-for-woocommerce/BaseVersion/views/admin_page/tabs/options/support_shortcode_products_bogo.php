<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Support shortcode [adp_products_bogo]',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Support shortcode [adp_products_bogo]',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="support_shortcode_products_bogo">
                <input <?php checked($options['support_shortcode_products_bogo']); ?>
                    name="support_shortcode_products_bogo" id="support_shortcode_products_bogo" type="checkbox">
            </label>
            <a href="https://docs.algolplus.com/algol_pricing/rules-settings-free/"
               target=_blank><?php _e('Read short guide', 'advanced-dynamic-pricing-for-woocommerce') ?></a>
        </fieldset>
    </td>
</tr>
