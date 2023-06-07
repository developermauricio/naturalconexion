<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <?php _e('Cents', 'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <div>
                <label for="is_override_cents">
                    <input <?php checked($options['is_override_cents']) ?> name="is_override_cents"
                                                                           id="is_override_cents" type="checkbox">
                    <?php _e('Override the cents on the calculated price.',
                        'advanced-dynamic-pricing-for-woocommerce') ?>
                </label>
            </div>
            <div>
                <label for="prices_ends_with">
                    <?php _e('If selected, prices will end with: ', 'advanced-dynamic-pricing-for-woocommerce') ?>
                    <?php echo('0' . wc_get_price_decimal_separator()) ?>
                    <input value="<?php echo $options['prices_ends_with'] ?>" name="prices_ends_with"
                           id="prices_ends_with" placeholder="99" type="text" maxlength="2" size=3>
                </label>
            </div>
        </fieldset>
    </td>
</tr>
