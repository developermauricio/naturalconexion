<?php
defined('ABSPATH') or exit;
/**
 * @var string $product_bulk_table_customizer_url
 */

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Show bulk table on product page',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show bulk table on product page',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="show_matched_bulk_table">
                <input name="show_matched_bulk_table" value="0" type="hidden">
                <input <?php checked($options['show_matched_bulk_table']); ?>
                    name="show_matched_bulk_table" id="show_matched_bulk_table" type="checkbox">
            </label>
            <a href="<?php echo $product_bulk_table_customizer_url; ?>" target="_blank">
                <?php _e('Customize', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </a>
            &nbsp; <?php _e('You can use shortcode [adp_product_bulk_rules_table] too',
                'advanced-dynamic-pricing-for-woocommerce') ?>
        </fieldset>
    </td>
</tr>
