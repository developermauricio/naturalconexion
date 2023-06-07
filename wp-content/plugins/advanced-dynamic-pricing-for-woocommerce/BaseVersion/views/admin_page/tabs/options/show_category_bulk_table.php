<?php
defined('ABSPATH') or exit;
/**
 * @var string $category_bulk_table_customizer_url
 */

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Show bulk table on category page',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show bulk table on category page',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="show_category_bulk_table">
                <input <?php checked($options['show_category_bulk_table']); ?>
                    name="show_category_bulk_table" id="show_category_bulk_table" type="checkbox">
            </label>
            <a href="<?php echo $category_bulk_table_customizer_url; ?>" target="_blank">
                <?php _e('Customize', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </a>
            &nbsp; <?php _e('You can use shortcode [adp_category_bulk_rules_table]',
                'advanced-dynamic-pricing-for-woocommerce') ?>
        </fieldset>
    </td>
</tr>
