<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc">
    </th>
    <td class="forminp forminp-checkbox">
        <a href="https://docs.algolplus.com/algol_pricing/common-faq/"
           target="_blank">
            <?php _e('Guide for supported tags', 'advanced-dynamic-pricing-for-woocommerce') ?>
        </a>
    </td>
<tr>


<tr valign="top">
    <th scope="row" class="titledesc">
        <div><?php _e('Replace price with lowest bulk price', 'advanced-dynamic-pricing-for-woocommerce') ?></div>

    </th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <div style="display: inline-block; line-height: 2rem;">
                <label for="replace_price_with_min_bulk_price_category">
                    <input <?php checked($options['replace_price_with_min_bulk_price_category']) ?>
                        name="replace_price_with_min_bulk_price_category"
                        id="replace_price_with_min_bulk_price_category" type="checkbox">
                    <?php _e('Apply to category/tag pages', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </label>
                <br/>
                <label for="replace_price_with_min_bulk_price_category_template">
                    <?php _e('Output template', 'advanced-dynamic-pricing-for-woocommerce') ?>
                    <input value="<?php echo $options['replace_price_with_min_bulk_price_category_template'] ?>"
                           name="replace_price_with_min_bulk_price_category_template"
                           id="replace_price_with_min_bulk_price_category_template" type="text">
                </label>
            </div>
            <div>
                <?php _e('Available tags', 'advanced-dynamic-pricing-for-woocommerce') ?>
                : <?php _e('{{price}}, {{price_suffix}}, {{price_striked}}, {{initial_price}}, {{regular_price_striked}}',
                    'advanced-dynamic-pricing-for-woocommerce') ?>
            </div>
        </fieldset>
    </td>
</tr>
