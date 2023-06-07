<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Support shortcode [adp_products_on_sale]',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Support shortcode [adp_products_on_sale]',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="support_shortcode_products_on_sale">
                <input <?php checked($options['support_shortcode_products_on_sale']); ?>
                    name="support_shortcode_products_on_sale" id="support_shortcode_products_on_sale" type="checkbox">
            </label>
            <a href="https://docs.algolplus.com/algol_pricing/rules-settings-free/"
               target=_blank><?php _e('Read short guide', 'advanced-dynamic-pricing-for-woocommerce') ?></a>
        </fieldset>
    </td>
</tr>
