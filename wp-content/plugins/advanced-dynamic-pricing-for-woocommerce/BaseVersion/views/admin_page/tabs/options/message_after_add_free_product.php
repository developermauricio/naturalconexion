<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <?php _e('Show message after adding free product', 'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <div>
                <label for="show_message_after_add_free_product">
                    <input <?php checked($options['show_message_after_add_free_product']) ?>
                        name="show_message_after_add_free_product" id="show_message_after_add_free_product"
                        type="checkbox">
                    <?php _e('Enable', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </label>
            </div>
            <div>
                <label for="message_template_after_add_free_product">
                    <?php _e('Output template', 'advanced-dynamic-pricing-for-woocommerce') ?>
                    <input style="min-width: 300px;"
                           value="<?php echo $options['message_template_after_add_free_product'] ?>"
                           name="message_template_after_add_free_product" id="message_template_after_add_free_product"
                           type="text">
                </label>
                <br>
                <?php _e('Available tags', 'advanced-dynamic-pricing-for-woocommerce') ?>
                : <?php _e('{{qty}}, {{product_name}}', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </div>
        </fieldset>
    </td>
</tr>
