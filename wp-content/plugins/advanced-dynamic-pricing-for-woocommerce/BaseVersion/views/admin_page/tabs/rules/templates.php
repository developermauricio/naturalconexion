<?php

use ADP\BaseVersion\Includes\Core\Rule\Structures\PackageItem;

defined('ABSPATH') or exit;

/**
 * @var string $conditions_templates
 * @var array $conditions_templates
 * @var array $conditions_titles
 * @var array $limits_templates
 * @var array $limits_titles
 * @var array $cart_titles
 * @var array $cart_templates
 */
?>

<div id="templates" style="display: none;">

    <?php
    foreach ($conditions_templates as $id => $condition_template):
        echo '<div id="' . $id . '_template">' . $condition_template . '</div>';
    endforeach;
    ?>

    <?php
    foreach ($limits_templates as $id => $limit_template):
        echo '<div id="' . $id . '_limit_template">' . $limit_template . '</div>';
    endforeach;
    ?>

    <?php
    foreach ($cart_templates as $id => $cart_template):
        echo '<div id="' . $id . '_cart_adjustment_template">' . $cart_template . '</div>';
    endforeach;
    ?>

    <div id="rule_template">
        <?php include 'rule.php'; ?>
    </div>

    <div id="persistent_rule_template">
        <?php include 'persistent_rule.php'; ?>
    </div>

    <div id="condition_row_template">
        <div class="wdp-row wdp-condition" data-index="{c}">
            <div class="wdp-column wdp-drag-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <div class="wdp-column wdp-condition-field-type">
                <select name="rule[conditions][{c}][type]">
                    <?php foreach ($conditions_titles as $group_name => $group): ?>
                        <optgroup label="<?php echo $group_name ?>">
                            <?php foreach ($group as $condition_id => $condition_title): ?>
                                <option value="<?php echo $condition_id ?>"><?php echo $condition_title ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="wdp-column wdp-column-subfields wdp-condition-field-sub"></div>

            <div class="wdp-column wdp-btn-remove wdp-condition-remove">
                <div class="wdp-btn-remove-handle">
                    <span class="dashicons dashicons-no-alt"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="limit_row_template">
        <div class="wdp-row wdp-limit" data-index="{l}">
            <div class="wdp-column wdp-drag-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <div class="wdp-column wdp-limit-type">
                <select name="rule[limits][{l}][type]">
                    <?php foreach ($limits_titles as $group_name => $group): ?>
                        <optgroup label="<?php echo $group_name ?>">
                            <?php foreach ($group as $limit_id => $limit_title): ?>
                                <option value="<?php echo $limit_id ?>"><?php echo $limit_title ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="wdp-column wdp-column-subfields wdp-limit-field-sub"></div>

            <div class="wdp-column wdp-btn-remove wdp-limit-remove">
                <div class="wdp-btn-remove-handle">
                    <span class="dashicons dashicons-no-alt"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="cart_adjustment_row_template">
        <div class="wdp-row wdp-cart-adjustment" data-index="{ca}">
            <div class="wdp-column wdp-drag-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <div class="wdp-column wdp-cart-adjustment-type">
                <select name="rule[cart_adjustments][{ca}][type]">
                    <?php foreach ($cart_titles as $group_name => $group): ?>
                        <optgroup label="<?php echo $group_name ?>">
                            <?php foreach ($group as $cart_adj_id => $cart_adj_title): ?>
                                <option value="<?php echo $cart_adj_id ?>"><?php echo $cart_adj_title ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="wdp-column wdp-column-subfields wdp-cart-adjustment-field-sub"></div>

            <div class="wdp-column wdp-btn-remove wdp-cart-adjustment-remove">
                <div class="wdp-btn-remove-handle">
                    <span class="dashicons dashicons-no-alt"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="filter_item_qty_template">
        <div class="wdp-row wdp-filter-item" data-index="{f}">

            <div class="wdp-filter-content-no-remove">

                <div class="two-on-two">
                    <div class="two-on-two-column left-column">

                        <div style="display: flex;">
                            <div class="wdp-column wdp-condition-field-qty">
                                <input type="number" placeholder="1" min="1" name="rule[{t}][{f}][qty]" value="1">
                            </div>

                            <?php if ($options->getOption('show_qty_range_in_product_filter')): ?>
                                <div class="wdp-column range-sign">
                                    <span> — </span>
                                </div>

                                <div class="wdp-column wdp-condition-field-qty-end">
                                    <input type="number" min="1" name="rule[{t}][{f}][qty_end]" value="1">
                                </div>
                            <?php endif; ?>

                            <?php

                            $product_filter_type_list = array(
                                'any'                       => __('Any product',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'products'                  => __('Products',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'product_sku'           => __('SKUs',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'product_categories'        => __('Categories',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'product_category_slug'     => __('Category slugs',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'product_attributes'        => __('Attributes',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'product_custom_attributes' => __('Custom attributes',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'product_tags'              => __('Tags', 'advanced-dynamic-pricing-for-woocommerce'),
                            );

                            foreach (\ADP\BaseVersion\Includes\Helpers\Helpers::getCustomProductTaxonomies() as $tax) {
                                $product_filter_type_list[$tax->name] = $tax->labels->menu_name;
                            }

                            $product_filter_type_list = array_merge($product_filter_type_list, array(
                                'product_custom_fields' => __('Custom fields',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                            ));

                            $product_filter_type_list = apply_filters('wdp_select_product_filter_type_list',
                                $product_filter_type_list);

                            $default_filter = 'products';
                            ?>

                            <div class="wdp-column wdp-filter-field-type">
                                <select name="rule[{t}][{f}][type]" class="wdp-filter-type">
                                    <?php foreach ($product_filter_type_list as $value => $title): ?>
                                        <option value="<?php echo $value ?>" <?php echo $default_filter === $value ? 'selected' : '' ?>>
                                            <?php echo $title ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>

                        </div>

                        <div>
                            <div class="wdp-limitation" style="margin-top: 10px">
                                <select name="rule[{t}][{f}][limitation]">
                                    <option value="<?php echo PackageItem::LIMITATION_NONE; ?>"><?php _e('None',
                                            'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                    <option
                                        value="<?php echo PackageItem::LIMITATION_SAME_PRODUCT; ?>"><?php _e('Same product only',
                                            'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                    <option
                                        value="<?php echo PackageItem::LIMITATION_UNIQUE_PRODUCT; ?>"><?php _e('All different products',
                                            'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                    <option
                                        value="<?php echo PackageItem::LIMITATION_SAME_VARIATION; ?>"><?php _e('Same variation only',
                                            'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                    <option
                                        value="<?php echo PackageItem::LIMITATION_UNIQUE_VARIATION; ?>"><?php _e('All different variations',
                                            'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                    <option
                                        value="<?php echo PackageItem::LIMITATION_SAME_HASH; ?>"><?php _e('Same item meta only',
                                            'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                    <option
                                        value="<?php echo PackageItem::LIMITATION_UNIQUE_HASH; ?>"><?php _e('All different item meta',
                                            'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="two-on-two-column right-column">

                        <div>
                            <div class="wdp-column wdp-column-subfields wdp-condition-field-sub"></div>
                        </div>

                        <div>
                            <div class="wdp-product-filter-options">
                                <div class="wdp-row">

                                    <div class="wdp-product-exclude wdp-column wdp-column-subfields">
                                        <div style="width: 100px"></div>
                                        <div class="wdp-column" style="flex: 1">
                                            <span class="wdp-product-exclude-title">
                                                <?php _e('Exclude products',
                                                    'advanced-dynamic-pricing-for-woocommerce'); ?>
                                            </span>
                                            <select multiple
                                                    data-list="products"
                                                    data-field="autocomplete"
                                                    data-placeholder="<?php _e("Select values",
                                                        "advanced-dynamic-pricing-for-woocommerce") ?>"
                                                    name="rule[{t}][{f}][product_exclude][values][]">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="wdp-row">

                                    <div class="wdp-column wdp-column-subfields">

                                        <div style="width: 110px"></div>
                                        <div>
                                            <label>
                                                <span class="wdp-exclude-title">
													<?php _e( 'Exclude products', 'advanced-dynamic-pricing-for-woocommerce' ); ?>
												</span>
                                            </label>
                                        </div>
                                        <div style="margin-left: 5px" class="wdp-exclude-on-wc-sale-container">
                                            <label>
                                                <input type="checkbox" class="wdp-exclude-on-wc-sale" name="rule[{t}][{f}][product_exclude][on_wc_sale]" value="1" >
                                                <span class="wdp-exclude-on-wc-sale-title">
													<?php _e( 'on sale products', 'advanced-dynamic-pricing-for-woocommerce' ); ?>
												</span>
                                            </label>
                                        </div>
                                        <div style="margin-left: 5px" class="wdp-exclude-already-affected-container">
                                            <label>
                                                <input type="checkbox" class="wdp-exclude-already-affected" name="rule[{t}][{f}][product_exclude][already_affected]" value="1" >
                                                <span>
													<?php _e( 'modified by other pricing rules', 'advanced-dynamic-pricing-for-woocommerce' ); ?>
												</span>
                                            </label>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>


                </div>


            </div>


            <div class="wdp-column wdp-btn-remove wdp_filter_remove">
                <div class="wdp-btn-remove-handle">
                    <span class="dashicons dashicons-no-alt"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="filter_any_template">
        <input type="hidden" name="rule[{t}][{f}][method]" value="any">
        <input type="hidden" name="rule[{t}][{f}][value]">
    </div>

    <div id="filter_products_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected><?php _e('in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                <option value="not_in_list"><?php _e('not in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <div>
                <select multiple
                        data-list="products"
                        data-field="autocomplete"
                        data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                        name="rule[{t}][{f}][value][]">
                </select>
            </div>
        </div>
    </div>

    <div id="filter_giftable_products_template">
        <div class="wdp-column wdp-condition-field-value">
            <div>
                <select multiple
                        data-list="giftable_products"
                        data-field="autocomplete"
                        data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                        name="rule[{t}][{f}][value][]">
                </select>
            </div>
        </div>
    </div>

    <div id="filter_product_tags_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected><?php _e('in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                <option value="not_in_list"><?php _e('not in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <div>
                <select multiple
                        data-list="product_tags"
                        data-field="autocomplete"
                        data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                        name="rule[{t}][{f}][value][]">
                </select>
            </div>
        </div>
    </div>

    <div id="filter_product_categories_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected><?php _e('in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                <option value="not_in_list"><?php _e('not in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <div>
                <select multiple
                        data-list="product_categories"
                        data-field="autocomplete"
                        data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                        name="rule[{t}][{f}][value][]">
                </select>
            </div>
        </div>
    </div>

    <div id="filter_product_category_slug_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected><?php _e('in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                <option value="not_in_list"><?php _e('not in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <div>
                <select multiple
                        data-list="product_category_slug"
                        data-field="autocomplete"
                        data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                        name="rule[{t}][{f}][value][]">
                </select>
            </div>
        </div>
    </div>

    <?php foreach (\ADP\BaseVersion\Includes\Helpers\Helpers::getCustomProductTaxonomies() as $tax): ?>
        <div id="filter_<?php echo $tax->name; ?>_template">
            <div class="wdp-column wdp-filter-field-method">
                <select name="rule[{t}][{f}][method]">
                    <option value="in_list" selected><?php _e('in list',
                            'advanced-dynamic-pricing-for-woocommerce') ?></option>
                    <option value="not_in_list"><?php _e('not in list',
                            'advanced-dynamic-pricing-for-woocommerce') ?></option>
                </select>
            </div>

            <div class="wdp-column wdp-condition-field-value">
                <div>
                    <select multiple
                            data-list="product_taxonomies"
                            data-taxonomy="<?php echo $tax->name; ?>"
                            data-field="autocomplete"
                            data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                            name="rule[{t}][{f}][value][]">
                    </select>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div id="filter_product_attributes_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected><?php _e('in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                <option value="not_in_list"><?php _e('not in list',
                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <select multiple
                    data-list="product_attributes"
                    data-field="autocomplete"
                    data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                    name="rule[{t}][{f}][value][]">
            </select>
        </div>
    </div>

    <div id="filter_product_custom_attributes_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected>
                    <?php _e('in list', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
                <option value="not_in_list">
                    <?php _e('not in list', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <select multiple
                    data-list="product_custom_attributes"
                    data-field="autocomplete"
                    data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                    name="rule[{t}][{f}][value][]">
            </select>
        </div>
    </div>

    <div id="filter_product_sku_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected>
                    <?php _e('in list', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
                <option value="not_in_list">
                    <?php _e('not in list', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <select multiple
                    data-list="product_sku"
                    data-field="autocomplete"
                    data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                    name="rule[{t}][{f}][value][]">
            </select>
        </div>
    </div>

    <div id="filter_product_custom_fields_template">
        <div class="wdp-column wdp-filter-field-method">
            <select name="rule[{t}][{f}][method]">
                <option value="in_list" selected>
                    <?php _e('in list', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
                <option value="not_in_list">
                    <?php _e('not in list', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
            </select>
        </div>

        <div class="wdp-column wdp-condition-field-value">
            <select multiple
                    data-list="product_custom_fields"
                    data-field="autocomplete"
                    data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                    name="rule[{t}][{f}][value][]">
            </select>
        </div>
    </div>

    <div id="adjustment_split_row_template">
        <div class="wdp-row adjustment-split" data-index="{adj}">
            <div class="wdp-column">
                <select name="rule[product_adjustments][split][{adj}][type]" class="adjustment-split-type">
                    <option value="discount__amount"><?php _e('Fixed discount',
                            'advanced-dynamic-pricing-for-woocommerce') ?></option>
                    <option value="discount__percentage"><?php _e('Percentage discount',
                            'advanced-dynamic-pricing-for-woocommerce') ?></option>
                    <option value="price__fixed"><?php _e('Fixed price',
                            'advanced-dynamic-pricing-for-woocommerce') ?></option>
                </select>
            </div>

            <div class="wdp-column">
                <input name="rule[product_adjustments][split][{adj}][value]"
                       class="adjustment-split-value" type="number" placeholder="0.00" min="0" step="any">
            </div>
        </div>
    </div>

    <div id="adjustment_bulk_template">
        <div class="wdp-row wdp-range" data-index="{b}">
            <div class="wdp-column wdp-drag-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <div class="wdp-column">
                <input name="rule[bulk_adjustments][ranges][{b}][from]"
                       class="adjustment-from" type="number" placeholder="qty from" min="0" step="any">
            </div>

            <div class="wdp-column">
                <input name="rule[bulk_adjustments][ranges][{b}][to]"
                       class="adjustment-to" type="number" placeholder="qty to" min="0" step="any">
            </div>

            <div class="wdp-column">
                <input name="rule[bulk_adjustments][ranges][{b}][value]"
                       class="adjustment-value" type="number" placeholder="0.00" min="0">
            </div>

            <div class="wdp-btn-remove wdp-range-remove">
                <div class="wdp-btn-remove-handle"><span class="dashicons dashicons-no-alt"></span></div>
            </div>
        </div>
    </div>

    <div id="adjustment_deal_template">
        <div class="wdp-row wdp-filter-item" data-index="{f}">
            <div class="wdp-column wdp-drag-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <div class="wdp-column wdp-condition-field-qty">
                <input type="number" placeholder="qty" min="1" name="rule[get_products][value][{f}][qty]" value="1">
            </div>

            <div class="wdp-column wdp-condition-field-gift-mode" style="max-width: 200px">
                <select name="rule[get_products][value][{f}][gift_mode]">
                    <option value="giftable_products">
                        <?php _e("Give gift from the list (1st available)",
                            'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                    <option value="use_product_from_filter">
                        <?php _e("Use product from filter",
                            'advanced-dynamic-pricing-for-woocommerce') ?>
                    </option>
                </select>
            </div>

            <input type="hidden" value="giftable_products" name="rule[get_products][value][{f}][type]">

            <div class="wdp-column wdp-column-subfields wdp-condition-field-sub"></div>

            <div class="wdp-column wdp-btn-remove wdp_filter_remove">
                <div class="wdp-btn-remove-handle">
                    <span class="dashicons dashicons-no-alt"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="filter_block_template">
        <div class="wdp-block wdp-filter-block">
            <label><?php _e('Products', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
            <div class="wdp-wrapper wdp_product_filter wdp-sortable">
                <div class="wdp-product-filter-empty">
                    <?php _e('No filters', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </div>
            </div>
        </div>

        <div class="wdp-add-condition">
            <button type="button" class="button add-product-filter">
                <?php _e('Add product filter', 'advanced-dynamic-pricing-for-woocommerce'); ?>
            </button>
        </div>
    </div>

    <div id="role_discount_row_template">
        <div class="wdp-row wdp-role-discount" data-index="{indx}">
            <div class="wdp-column wdp-drag-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <div class="wdp-column">
                <select multiple
                        data-list="user_roles"
                        data-field="preloaded"
                        data-placeholder="<?php _e("Select values", "advanced-dynamic-pricing-for-woocommerce") ?>"
                        name="rule[role_discounts][rows][{indx}][roles][]"
                        class="role-discount wdp-role-discount-value"
                        data-field-name="roles">
                </select>
            </div>
            <div class="wdp-column">
                <select name="rule[role_discounts][rows][{indx}][discount_type]"
                        class="role-discount-type wdp-role-discount-value" data-field-name="discount_type">
                    <option value="discount__amount">
                        <?php _e('Fixed discount', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
                    <option value="discount__percentage">
                        <?php _e('Percentage discount', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
                    <option value="price__fixed">
                        <?php _e('Fixed unit price', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
                </select>
            </div>

            <div class="wdp-column">
                <input name="rule[role_discounts][rows][{indx}][discount_value]" data-field-name="discount_value"
                       class="role-discount-value wdp-role-discount-value" type="number" placeholder="0.00" min="0"
                       step="any">
            </div>

            <div class="wdp-column wdp-btn-remove wdp_role_discount_remove">
                <div class="wdp-btn-remove-handle">
                    <span class="dashicons dashicons-no-alt"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="condition_message_split_row_template">
        <div class="wdp-row condition-message-split" data-index="{adj}">
            <div class="wdp-column">
                <label>
                    <?php _e('Offer message.', 'advanced-dynamic-pricing-for-woocommerce') ?>
                    <?php _e('Available tags: {{qty}}, {{product}}, {{discount}}, {{cart_condition}}', 'advanced-dynamic-pricing-for-woocommerce') ?>
                    <input type="text" name="rule[condition_message][split][{adj}][message]" class="condition-message-split-message"/>
                </label>
            </div>
        </div>
    </div>

</div>
