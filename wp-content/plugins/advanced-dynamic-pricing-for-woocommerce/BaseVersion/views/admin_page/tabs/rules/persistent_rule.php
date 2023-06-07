<?php

use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;

defined('ABSPATH') or exit;

$isCouponEnabled = wc_coupons_enabled();

$pleaseEnableText = __("Please, enable coupons to use price replacements.", 'advanced-dynamic-pricing-for-woocommerce');

?>

<form class="postbox closed not-initialized" data-index="{r}">
    <div style="float: left; margin: 20px 10px;">
        <input type="checkbox" class="bulk-action-mark">
    </div>

    <input type="hidden" name="action" value="wdp_ajax">
    <input type="hidden" name="method" value="save_rule">
    <input type="hidden" name="rule[priority]" value="{p}" class="rule-priority"/>
    <input type="hidden" value="" name="rule[id]" class="rule-id">
    <input type="hidden" name="rule[type]" value="persistent" class="rule-type">
    <input type="hidden" name="rule[exclusive]" value="0">

    <input type="hidden" name="rule[additional][blocks][productFilters][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][productDiscounts][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][roleDiscounts][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][bulkDiscounts][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][freeProducts][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][autoAddToCart][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][advertising][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][cartAdjustments][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][conditions][isOpen]" value="0">
    <input type="hidden" name="rule[additional][blocks][limits][isOpen]" value="0">

    <button type="button" class="handlediv" aria-expanded="false">
        <span class="screen-reader-text"><?php _e('Expand', 'advanced-dynamic-pricing-for-woocommerce') ?></span>
        <span class="toggle-indicator" aria-hidden="true"
              title="<?php _e('Expand', 'advanced-dynamic-pricing-for-woocommerce') ?>"></span>
    </button>

    <div class="wdp-actions">
        <button type="button" class="button-link wdp_copy_rule">
            <span class="screen-reader-text"><?php _e('Clone', 'advanced-dynamic-pricing-for-woocommerce') ?>
                <span data-wdp-title></span></span>
            <span class="dashicons dashicons-admin-page"
                  title="<?php _e('Clone', 'advanced-dynamic-pricing-for-woocommerce') ?>"></span>
        </button>
        <button type="button" class="button-link wdp_remove_rule">
            <span class="screen-reader-text"><?php _e('Delete', 'advanced-dynamic-pricing-for-woocommerce') ?>
                <span data-wdp-title></span></span>
            <span class="dashicons dashicons-no-alt"
                  title="<?php _e('Delete', 'advanced-dynamic-pricing-for-woocommerce') ?>"></span>
        </button>
        <div class="rule-id-badge wdp-list-item-id-badge">
            <label><?php _e('#', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
            <label class="rule-id"></label>
        </div>

        <div style="float: right;margin: 10px" class="rule-type">
            <span><?php _e('Rule type', 'advanced-dynamic-pricing-for-woocommerce') ?></span>
            <select name="rule[rule_type]">
                <option value="<?php echo RuleTypeEnum::PERSISTENT()->getValue() ?>">
                    <?php _e('Product only', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
                <option value="<?php echo RuleTypeEnum::COMMON()->getValue() ?>">
                    <?php _e('Common', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </option>
            </select>
        </div>

        <div style="float: right;margin: 10px" class="rule-date-from-to">
            <span><?php _e('From', 'advanced-dynamic-pricing-for-woocommerce') ?></span>
            <input style="max-width: 100px;" class="datepicker" name="rule[additional][date_from]" type="text">
            <span><?php _e('To', 'advanced-dynamic-pricing-for-woocommerce') ?></span>
            <input style="max-width: 100px;" class="datepicker" name="rule[additional][date_to]" type="text">
        </div>

        <div class="rule-type-bage"></div>
    </div>

    <h2 class="hndle ui-sortable-handle">
        <div class="wdp-column wdp-field-enabled">
            <select name="rule[enabled]" data-role="flipswitch" data-mini="true">
                <option value="off">Off</option>
                <option value="on" selected>On</option>
            </select>
        </div>
        <div class="wdp-disabled-automatically-prefix">[disabled automatically]</div>
        <span data-wdp-title></span>&nbsp;
    </h2>
    <!-- <div style="clear: both;"></div> -->
    <div class="inside">
        <div class="wdp-row wdp-options">
            <div class="wdp-row wdp-column wdp-field-title">
                <label><?php _e('Title', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                <input class="wdp-column wdp-title" type="text" name="rule[title]">
            </div>
        </div>

        <div class="wdp-row wdp-options">
            <div class="buffer"></div>
            <div class="replace-adjustments">
                <div style="float: right" <?php echo $isCouponEnabled ? "" : "title='{$pleaseEnableText}'"; ?>>
                    <label>
                        <input type="checkbox"
                               name="rule[additional][is_replace]">
                        <?php _e("Don't modify product prices and show discount as coupon",
                            'advanced-dynamic-pricing-for-woocommerce') ?>
                    </label>
                    <input type="text" name="rule[additional][replace_name]"
                           placeholder="<?php _e("coupon_name", 'advanced-dynamic-pricing-for-woocommerce') ?>"
                    >

                </div>
            </div>
        </div>

        <div class="wdp-block wdp-filter-block wdp-row" style="display: none;">
            <div class="wdp-column wdp-column-help">
                <label><?php _e('Filter by products', 'advanced-dynamic-pricing-for-woocommerce'); ?></label><br>
                <label class="wdp-filter-warning" style="color:red"><?php _e('If you add many lines to this section – you will create product bundle',
                        'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                <p class="wdp-rule-help">
                <?php
                    echo sprintf(
                        wp_kses(
                            __('Select what to discount: any products, certain products, collections, categories, category slugs, attributes, custom attributes, tags, SKUs, custom fields, sellers.', 'advanced-dynamic-pricing-for-woocommerce')
                            .'<br><br>' .__('Exclude products that wouldn’t be discounted: enter the values into the field “Exclude products” or turn on the checkboxes with the same name.', 'advanced-dynamic-pricing-for-woocommerce')
                            .'<br><a href="%s">' .__('Read docs', 'advanced-dynamic-pricing-for-woocommerce') .'</a>',
                            array('br' => array(), 'a' => array('href' => array()))
                        ),
                        esc_url('https://docs.algolplus.com/algol_pricing/product-filters-free/')
                    );
                ?>
                </p>
            </div>
            <div class="wdp-wrapper wdp_product_filter wdp-column">
                <div class="wdp-product-filter-container"></div>
            </div>
        </div>

        <div class="wdp-block wdp-product-adjustments wdp-row" style="display: none;">
            <div class="wdp-column wdp-column-help">
                <label><?php _e('Product discounts', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                <p class="wdp-rule-help">
                <?php
                        echo sprintf(
                            wp_kses(
                                    __('Select the discount type and enter its value.', 'advanced-dynamic-pricing-for-woocommerce')
                                    .'<br><a href="%s">' .__('Read docs', 'advanced-dynamic-pricing-for-woocommerce') .'</a>',
                                array('a' => array('href' => array()), 'br' => array())
                            ),
                            esc_url('https://docs.algolplus.com/algol_pricing/product-discounts-free/')
                        );
                    ?>
                </p>
            </div>
            <div class="wdp-wrapper wdp-column">
                <div class="wdp-row">
                    <div class="wdp-column">
                        <label>
                            <input type="radio" name="rule[product_adjustments][type]"
                                   class="adjustment-mode adjustment-mode-total"
                                   data-readonly="1"
                                   value="total"/><?php _e('Total', 'advanced-dynamic-pricing-for-woocommerce') ?>
                        </label>
                    </div>

                    <div class="wdp-column wdp-btn-remove wdp_product_adjustment_remove">
                        <div class="wdp-btn-remove-handle">
                            <span class="dashicons dashicons-no-alt"></span>
                        </div>
                    </div>
                </div>

                <div class="wdp-row" data-show-if="total">
                    <div class="wdp-column">
                        <select name="rule[product_adjustments][total][type]" class="adjustment-total-type">
                            <option value="discount__amount"><?php _e('Fixed discount',
                                    'advanced-dynamic-pricing-for-woocommerce') ?></option>
                            <option value="discount__percentage"><?php _e('Percentage discount',
                                    'advanced-dynamic-pricing-for-woocommerce') ?></option>
                            <option value="price__fixed"><?php _e('Fixed price',
                                    'advanced-dynamic-pricing-for-woocommerce') ?></option>
                        </select>
                    </div>

                    <div class="wdp-column">
                        <input name="rule[product_adjustments][total][value]" class="adjustment-total-value"
                               type="number" placeholder="0.00" min="0" step="any">
                    </div>
                </div>

                <div class="wdp-product-adjustments-split-container" data-show-if="split"></div>

                <div class="wdp-product-adjustments-options">
                    <div>
                        <div style="display: inline-block;margin: 0 10px 0 0;">
                            <label>
                                <?php _e('Max discount sum:', 'advanced-dynamic-pricing-for-woocommerce') ?>
                                <input style="display: inline-block; width: 200px;"
                                       name="rule[product_adjustments][max_discount_sum]" type="number"
                                       class="product-adjustments-max-discount" placeholder="0.00" min="0" step="any"/>
                            </label>
                        </div>

                        <div style="display: none;margin: 0 10px;width: 20rem;">
                            <div class="split-discount-controls">
                                <label>
                                    <?php _e('Split discount by:', 'advanced-dynamic-pricing-for-woocommerce') ?>
                                    <select name="rule[product_adjustments][split_discount_by]"
                                            style="display: inline-block; width: 200px;"
                                            class="adjustment-split-discount-type">
                                        <option class="split-discount-by-cost" value="cost"><?php _e('Item cost',
                                                'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                                    </select>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div style="">
            <div class="wdp-block wdp-bulk-adjustments" style="display: none;">
                <input data-readonly="1" type="hidden" class="priority_block_name"
                       name="rule[sortable_blocks_priority][]" value="bulk-adjustments">
                <div class="wdp-row">
                    <div class="wdp-column wdp-column-help">
                        <label><?php _e('Bulk mode', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                        <p class="wdp-rule-help">
                        <?php
                            echo sprintf(
                                wp_kses(
                                        __('Enter the discount amount based on the number of items in the cart. Put the product quantity in the range  and choose the type of bulk and discount.', 'advanced-dynamic-pricing-for-woocommerce')
                                        .'<br><a href="%s">' .__('Read docs', 'advanced-dynamic-pricing-for-woocommerce') .'</a>',
                                    array('br' => array(), 'a' =>array('href' => array()), )
                                ),
                                esc_url('https://docs.algolplus.com/algol_pricing/overview-bulk-mode/')
                            );
                            ?>
                        </p>
                    </div>
                    <div class="wdp-wrapper wdp-column">
                        <div class="wdp-row">
                            <div class="smaller-width">
                                <div class="wdp-column">
                                    <select name="rule[bulk_adjustments][type]" class="bulk-adjustment-type">
                                        <option value="bulk"><?php _e('Bulk',
                                                'advanced-dynamic-pricing-for-woocommerce') ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="wdp-column">
                                <select name="rule[bulk_adjustments][qty_based]" class="bulk-qty_based-type"></select>
                            </div>

                            <div class="wdp-column bulk-selected_categories-type">
                                <select multiple
                                        data-list="product_categories"
                                        data-field="autocomplete"
                                        data-placeholder="<?php _e("Select values",
                                            "advanced-dynamic-pricing-for-woocommerce") ?>"
                                        name="rule[bulk_adjustments][selected_categories][]">
                                </select>
                            </div>

                            <div class="wdp-column bulk-selected_products-type">
                                <select multiple
                                        data-list="products"
                                        data-field="autocomplete"
                                        data-placeholder="<?php _e("Select values",
                                            "advanced-dynamic-pricing-for-woocommerce") ?>"
                                        name="rule[bulk_adjustments][selected_products][]">
                                </select>
                            </div>

                            <div class="wdp-column">
                                <select name="rule[bulk_adjustments][discount_type]"
                                        class="bulk-discount-type"></select>
                            </div>

                            <div class="wdp-column wdp-btn-remove wdp_bulk_adjustment_remove">
                                <div class="wdp-btn-remove-handle">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </div>
                            </div>
                        </div>

                        <div class="wdp-adjustment-ranges">
                            <div class="wdp-ranges wdp-sortable">
                                <div class="wdp-ranges-empty"><?php _e('No ranges',
                                        'advanced-dynamic-pricing-for-woocommerce') ?></div>
                            </div>

                            <div class="wdp-add-condition">
                                <button type="button" class="button add-range"><?php _e('Add range',
                                        'advanced-dynamic-pricing-for-woocommerce'); ?></button>
                            </div>
                        </div>

                        <div class="wdp-bulk-adjustment-options">
                            <div class="wdp-column">
                                <label>
                                    <?php _e('Bulk table message', 'advanced-dynamic-pricing-for-woocommerce') ?>
                                    <input type="text" name="rule[bulk_adjustments][table_message]"
                                           class="bulk-table-message"
                                           placeholder="<?php _e('If you leave this field empty, we will show default bulk description',
                                               'advanced-dynamic-pricing-for-woocommerce') ?>"/>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="wdp-block wdp-get-products-block wdp-get-products-options wdp-row" style="display: none;">
            <div class="wdp-column wdp-column-help">
                <label><?php _e('Free products.', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                <p class="wdp-rule-help">
                <?php
                    echo sprintf(
                        wp_kses(
                                __('Select products that would be gifted to the customers.', 'advanced-dynamic-pricing-for-woocommerce')
                                .'<br><a href="%s">' .__('Read docs', 'advanced-dynamic-pricing-for-woocommerce') .'</a>',
                            array('br' => array(), 'a' => array('href' => array()), )
                        ),
                        esc_url('https://docs.algolplus.com/algol_pricing/free-products-free/')
                    );
                    ?>
                </p>
            </div>
            <div class="wdp-column">
                <div class="wdp-row wdp-get-products-repeat">
                    <div class="wdp-column">
                        <label><?php _e('Can be applied ', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>

                        <select name="rule[get_products][repeat]">
                            <optgroup label="<?php _e('Can be applied', 'advanced-dynamic-pricing-for-woocommerce') ?>">
                                <option value="-1"><?php _e('Unlimited',
                                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                                <option value="1"><?php _e('Once', 'advanced-dynamic-pricing-for-woocommerce') ?></option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </optgroup>
                            <optgroup label="<?php _e('Based on', 'advanced-dynamic-pricing-for-woocommerce') ?>">
                                <option value="based_on_subtotal"><?php _e('Subtotal (exc. VAT)',
                                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                                <option value="based_on_subtotal_inc"><?php _e('Subtotal (inc. VAT)',
                                        'advanced-dynamic-pricing-for-woocommerce') ?></option>
                            </optgroup>
                        </select>

                        <div class="repeat-subtotal" style="display: none">
                            <label><?php _e('Repeat counter = subtotal amount divided by',
                                    'advanced-dynamic-pricing-for-woocommerce'); ?>
                                <input class="repeat-subtotal-value" name="rule[get_products][repeat_subtotal]"
                                    placeholder="<?php _e("amount", 'advanced-dynamic-pricing-for-woocommerce') ?>">
                            </label>
                        </div>
                    </div>
                    <div style="flex: 1;" class="replace-free-products">
                        <div
                            style="float: right;" <?php echo $isCouponEnabled ? "" : "title='Please, enable coupons to use price replacements.'"; ?>>
                            <label>
                                <input <?php echo $isCouponEnabled ? "" : "disabled"; ?> type="checkbox"
                                                                                        name="rule[additional][is_replace_free_products_with_discount]">
                                <?php _e("Add free products to cart at normal cost, and add a coupon that will deduce that cost",
                                    'advanced-dynamic-pricing-for-woocommerce') ?>
                            </label>
                            <input <?php echo $isCouponEnabled ? "" : "disabled"; ?> type="text"
                                                                                    name="rule[additional][free_products_replace_name]"
                                                                                    style="width: 100px; display: inline-block;"
                                                                                    placeholder="<?php _e("coupon_name",
                                                                                        'advanced-dynamic-pricing-for-woocommerce') ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="wdp-wrapper">
                    <div class="wdp-get-products"></div>

                    <div class="wdp-add-condition">
                        <button type="button" class="button add-filter-get-product"><?php _e('Add product',
                                'advanced-dynamic-pricing-for-woocommerce'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="wdp-block wdp-filter-item-qty-block wdp-filter-item-qty-options" style="display: none;">
            <div class="wdp-row wdp-filter-item" data-index="{f}">

                <div class="wdp-filter-content-no-remove">

                    <div class="two-on-two">
                        <div class="two-on-two-column left-column">

                            <div style="display: flex;">
                                <div class="wdp-column wdp-condition-field-qty" style="display: none">
                                    <input type="number" placeholder="1" min="1" name="rule[{t}][{f}][qty]" value="1">
                                </div>
                                <?php

                                $productFilterTypeList = array(
                                    'products'                  => __(
                                        'Products',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                    'product_sku'           => __(
                                        'SKUs',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                    'product_categories'        => __(
                                        'Categories',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                    'product_category_slug'     => __(
                                        'Category slugs',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                    'product_attributes'        => __(
                                        'Attributes',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                    'product_custom_attributes' => __(
                                        'Custom attributes',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                    'product_tags'              => __(
                                        'Tags',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                );

                                foreach (\ADP\BaseVersion\Includes\Helpers\Helpers::getCustomProductTaxonomies() as $tax) {
                                    $productFilterTypeList[$tax->name] = $tax->labels->menu_name;
                                }

                                $productFilterTypeList = array_merge($productFilterTypeList, array(
                                    'product_sellers'       => __(
                                        'Sellers',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ),
                                ));

                                $productFilterTypeList = apply_filters(
                                    'wdp_select_product_filter_type_list',
                                    $productFilterTypeList
                                );
                                ?>

                                <div class="wdp-column wdp-filter-field-type">
                                    <select name="rule[{t}][{f}][type]" class="wdp-filter-type">
                                        <?php foreach ($productFilterTypeList as $value => $title): ?>
                                            <option value="<?php echo $value ?>"><?php echo $title ?></option>
                                        <?php endforeach; ?>
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

                                        <div class="wdp-exclude-on-wc-sale-container wdp-column wdp-column-subfields">
                                            <div style="width: 100px"></div>
                                            <div class="wdp-column" style="flex: 1">
                                                <label>
                                                    <input type="checkbox" class="wdp-exclude-on-wc-sale"
                                                           name="rule[{t}][{f}][product_exclude][on_wc_sale]" value="1">
                                                    <span class="wdp-exclude-on-wc-sale-title">
                                                                                                            <?php _e('Exclude on sale products',
                                                                                                                'advanced-dynamic-pricing-for-woocommerce'); ?>
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

        <div class="wdp-block wdp-conditions wdp-sortable wdp-row" style="display: none;">
            <div class="wdp-column wdp-column-help">
                <label><?php _e('Conditions', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                <p class="wdp-rule-help">
                <?php
                    echo sprintf(
                        wp_kses(
                                __('Select a cart condition that would trigger a rule execution.', 'advanced-dynamic-pricing-for-woocommerce')
                                .'<br><a href="%s">' .__('Read docs', 'advanced-dynamic-pricing-for-woocommerce') .'</a>',
                            array('br' => array(), 'a' => array('href' =>array()))
                        ),
                        esc_url('https://docs.algolplus.com/algol_pricing/cart-conditions-free/')
                    );
                    ?>
                </p>
            </div>
            <div class="wdp-wrapper wdp-column">
                <div class="wdp-conditions-relationship">
                    <label><?php _e('Conditions relationship', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                    <label><input type="radio" name="rule[additional][conditions_relationship]" value="and"
                                  checked><?php _e('AND', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                    <label><input type="radio" name="rule[additional][conditions_relationship]"
                                  value="or"><?php _e('OR', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                </div>
                <div class="wdp-conditions-container"></div>
                <div class="add-condition">
                    <button type="button" class="button"><?php _e('Add condition',
                            'advanced-dynamic-pricing-for-woocommerce'); ?></button>
                </div>

                <a href="https://algolplus.com/plugins/downloads/advanced-dynamic-pricing-woocommerce-pro/"
                   target=_blank><?php _e('Need more conditions?', 'advanced-dynamic-pricing-for-woocommerce') ?></a>
            </div>
        </div>

        <div class="wdp-block wdp-limits wdp-sortable wdp-row" style="display: none;">
            <div class="wdp-column wdp-column-help">
                <label><?php _e('Limits', 'advanced-dynamic-pricing-for-woocommerce'); ?></label>
                <p class="wdp-rule-help">
                <?php
                    echo sprintf(
                        wp_kses(
                                __('Configure how often the rule would be applied.', 'advanced-dynamic-pricing-for-woocommerce')
                                .'<br><a href="%s">' .__('Read docs', 'advanced-dynamic-pricing-for-woocommerce') .'</a>',
                            array('br' => array(), 'a' => array('href' => array()))
                        ),
                        esc_url('https://docs.algolplus.com/algol_pricing/limits-free/')
                    );
                    ?>
                </p>
            </div>
            <div class="wdp-wrapper wdp-column">
                <div class="wdp-limits-container"></div>
                <div class="add-limit">
                    <button type="button" class="button"><?php _e('Add limit',
                            'advanced-dynamic-pricing-for-woocommerce'); ?></button>
                </div>
            </div>
        </div>

        <div class="wdp-add-condition">
            <button type="button" class="button wdp-btn-add-product-filter"><?php _e('Product filters',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></button>
            <button type="button" class="button wdp-btn-add-product-adjustment"><?php _e('Product discounts',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></button>
            <button type="button" class="button wdp-btn-add-bulk"><?php _e('Bulk rules',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></button>
            <button type="button" class="button wdp-btn-add-getproduct"><?php _e('Free products',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></button>
            <button type="button" class="button wdp-btn-add-condition"><?php _e('Cart conditions',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></button>
            <button type="button" class="button wdp-btn-add-limit"><?php _e('Limits',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></button>
            <button type="submit" class="button button-primary save-rule"><?php _e('Save changes',
                    'advanced-dynamic-pricing-for-woocommerce') ?></button>
        </div>
    </div>
</form>
