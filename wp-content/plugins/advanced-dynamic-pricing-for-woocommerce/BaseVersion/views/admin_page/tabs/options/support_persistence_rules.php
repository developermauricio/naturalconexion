<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Support Product only rules',
            'advanced-dynamic-pricing-for-woocommerce') ?>
        <div style="font-style: italic; font-weight: normal; margin: 10px 0;">
            <label><?php _e('Use it only if you should setup huge # of product rules',
                    'advanced-dynamic-pricing-for-woocommerce') ?></label>
        </div>
    </th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Support Product only rules',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="support_persistence_rules">
                <input <?php checked($options['support_persistence_rules']) ?>
                    name="support_persistence_rules" id="support_persistence_rules" type="checkbox">
            </label>
            <a href="<?php echo admin_url('admin.php?page=wdp_settings&tab=tools#section=migration_rules');?>"
               target="_blank"><?php _e('Migrate rules', 'advanced-dynamic-pricing-for-woocommerce') ?></a>
            <a href="https://docs.algolplus.com/algol_pricing/product-only-type-rule/" style="margin-left: 10px;"
               target="_blank"><?php _e('Read short guide', 'advanced-dynamic-pricing-for-woocommerce') ?></a>
        </fieldset>
    </td>
</tr>
