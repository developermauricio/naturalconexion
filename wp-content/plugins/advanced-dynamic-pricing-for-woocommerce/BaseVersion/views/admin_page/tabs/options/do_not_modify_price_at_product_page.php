<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Don\'t modify product price on product page',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Don\'t modify product price on product page',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="do_not_modify_price_at_product_page">
                <input <?php checked($options['do_not_modify_price_at_product_page']); ?>
                        name="do_not_modify_price_at_product_page" id="do_not_modify_price_at_product_page"
                        type="checkbox">
            </label>
        </fieldset>
    </td>
</tr>
