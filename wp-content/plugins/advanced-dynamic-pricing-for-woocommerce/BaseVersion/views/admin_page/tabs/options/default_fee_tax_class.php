<?php
defined('ABSPATH') or exit;

?>
<?php

$tax_classes = array(
    array(
        'slug'  => "",
        'title' => __('Not taxable', 'phone-orders-for-woocommerce'),
    ),
    array(
        'slug'  => "standard",
        'title' => __('Standard rate', 'phone-orders-for-woocommerce'),
    ),
);
foreach (WC_Tax::get_tax_classes() as $tax_class_title) {
    $tax_classes[] = array(
        'slug'  => sanitize_title($tax_class_title),
        'title' => $tax_class_title,
    );
}

?>
<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Default fee tax class',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Default fee tax class', 'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="default_fee_tax_class">
                <select name="default_fee_tax_class">
                    <?php foreach ($tax_classes as $tax):
                        if (empty($tax['title'])) {
                            continue;
                        }
                        ?>
                        <option value="<?php echo $tax['slug']; ?>"
                            <?php selected($options['default_fee_tax_class'], $tax['slug'], true) ?>>
                            <?php echo $tax['title']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </fieldset>
    </td>
</tr>
