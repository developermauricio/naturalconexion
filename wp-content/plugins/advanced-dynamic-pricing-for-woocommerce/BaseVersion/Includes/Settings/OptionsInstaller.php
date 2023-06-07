<?php

namespace ADP\BaseVersion\Includes\Settings;

use ADP\Settings\OptionBuilder;
use ADP\Settings\OptionsList;
use ADP\Settings\OptionsManager;

defined('ABSPATH') or exit;

class OptionsInstaller
{
    public static function install()
    {
        $settings    = new OptionsManager(new StoreStrategy());
        $optionsList = new OptionsList();

        static::registerSettings($optionsList);

        $settings->installOptions($optionsList);
        $settings->load();

        return $settings;
    }

    /**
     * @param OptionsList $optionsList
     */
    public static function registerSettings(&$optionsList)
    {
        $builder = new OptionBuilder();

        $optionsList->register(
            $builder::boolean('show_unmodified_price_if_discounts_with_coupon',
                false,
                __('Show unmodified price if product discounts added as coupon', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::boolean('show_matched_bulk', false,
                __('Show matched bulk', 'advanced-dynamic-pricing-for-woocommerce')),
            $builder::boolean(
                'show_matched_cart_adjustments',
                false,
                __('Show matched cart adjustments', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'show_matched_cart_adjustments',
                false,
                __('Show matched cart adjustments', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::boolean(
                'show_matched_get_products',
                false,
                __('Show matched get products', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'show_matched_adjustments',
                false,
                __('Show matched adjustments', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'show_matched_deals',
                false,
                __('Show matched deals', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'show_matched_bulk_table',
                true,
                __('Show bulk table on product page', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::boolean(
                'show_category_bulk_table',
                false,
                __('Show bulk table on category page', 'advanced-dynamic-pricing-for-woocommerce')
            ),


            $builder::boolean(
                'show_striked_prices',
                true,
                __('Show striked prices in the cart', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::boolean(
                'show_onsale_badge',
                true,
                __('Show On Sale badge for Simple product if price was modified', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::integer(
                'limit_results_in_autocomplete',
                25,
                __('Show first X results in autocomplete', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::integer(
                'rule_max_exec_time',
                5,
                __('Disable rule if it runs longer than X seconds', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::integer(
                'rules_per_page',
                50,
                __('Rules per page', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'support_shortcode_products_on_sale',
                false,
                __('Support shortcode [adp_products_on_sale]', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'support_shortcode_products_bogo',
                false,
                __('Support shortcode [adp_products_bogo]', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'show_cross_out_subtotal_in_cart_totals',
                false,
                __('Show striked subtotal in cart totals', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::selective(
                'bulk_table_calculation_mode',
                __('Calculate price based on', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "only_bulk_rule_table" => __("Current bulk rule", 'advanced-dynamic-pricing-for-woocommerce'),
                    "all"                  => __("All active rules", 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                "only_bulk_rule_table"
            ),


            $builder::boolean(
                'combine_discounts',
                false,
                __('Combine multiple fixed discounts', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::shortText(
                'default_discount_name',
                __('Coupon', 'advanced-dynamic-pricing-for-woocommerce'),
                __('Default discount name', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'combine_fees',
                false,
                __('Combine multiple fees', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::shortText(
                'default_fee_name',
                __('Fee', 'advanced-dynamic-pricing-for-woocommerce'),
                __('Default fee name', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::shortText(
                'default_fee_tax_class',
                "",
                __('Default fee tax class', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'enable_product_html_template',
                false,
                __('Product price html template|Enable', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::htmlText(
                'price_html_template',
                "{{price_html}}",
                __('Product price html template|Output template', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::shortText(
                'initial_price_context',
                "nofilter",
                __('Use prices modified by get_price hook', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'do_not_modify_price_at_product_page',
                false,
                __('Don\'t modify product price on product page', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'discount_table_ignores_conditions',
                false,
                __('Show bulk table regardless of conditions', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'use_first_range_as_min_qty',
                false,
                __('Use first range as minimum quantity if bulk rule is active',
                    'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'show_message_after_add_free_product',
                false,
                __('Show message after adding free product|Enable', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::shortText(
                'message_template_after_add_free_product',
                __("Added {{qty}} free {{product_name}}", 'advanced-dynamic-pricing-for-woocommerce'),
                __('Show message after adding free product|Output template', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'is_calculate_based_on_wc_precision',
                false,
                __('Round up totals to match modified item prices', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'replace_price_with_min_bulk_price_category',
                false,
                __('Replace price with lowest bulk price|Enable', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::htmlText(
                'replace_price_with_min_bulk_price_category_template',
                __("From {{price}} {{price_suffix}}", 'advanced-dynamic-pricing-for-woocommerce'),
                __('Replace price with lowest bulk price|Output template', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'uninstall_remove_data',
                false,
                __('Remove all data on uninstall', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'update_prices_while_doing_cron',
                false,
                __('Apply pricing rules while doing cron', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'update_prices_while_doing_rest_api',
                false,
                __('Apply pricing rules while doing REST API', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'allow_to_edit_prices_in_po',
                false,
                __('Allow to edit prices in Phone Orders', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'suppress_other_pricing_plugins',
                false,
                __('Suppress other pricing plugins in frontend', 'advanced-dynamic-pricing-for-woocommerce')
            ),


            $builder::boolean(
                'allow_to_exclude_products',
                true,
                __('Allow to exclude products in filters', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::boolean(
                'show_debug_bar',
                false,
                __('Show debug panel at bottom of the page', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::selective(
                'discount_for_onsale',
                __('How to apply rules to a product that already has a sale price', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "sale_price"                  => __("Don't apply discount if product is already on sale", 'advanced-dynamic-pricing-for-woocommerce'),
                    "discount_regular"            => __("Discount regular price",
                        'advanced-dynamic-pricing-for-woocommerce'),
                    "discount_sale"               => __("Discount sale price",
                        'advanced-dynamic-pricing-for-woocommerce'),
                    "compare_discounted_and_sale" => __("Best between discounted regular price and sale price",
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                "compare_discounted_and_sale"
            ),

            $builder::boolean(
                'is_override_cents',
                false,
                __('Cents|Override the cents on the calculated price.', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::integer(
                'prices_ends_with',
                99,
                __('Cents|If selected, prices will end with: 0.', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::boolean(
                'hide_coupon_word_in_totals',
                false,
                __('Hide "Coupon" word in cart totals', 'advanced-dynamic-pricing-for-woocommerce')
            ),

            $builder::selective(
                "process_product_strategy",
                __('When the striked price should be shown', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "when" => __(
                        "Before matching condition",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "after" => __(
                        "After matching condition",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                ),
                "when"
            ),

            $builder::selective(
                "process_product_strategy_after_use_price",
                __('In "After matching condition" mode - use product price from cart', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "first" => __(
                        "First matched",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "last" => __(
                        "Last matched",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "cheapest" => __(
                        "Cheapest",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "most_expensive" => __(
                        "Most expensive",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                ),
                "first"
            ),

            // rewrite 'external_coupons_behavior' option
            $builder::selective(
                "external_cart_coupons_behavior",
                __('External coupons|Cart coupons', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "apply"                                => __("Apply", 'advanced-dynamic-pricing-for-woocommerce'),
                    "apply_to_unmodified_only"             => __(
                        "Apply to unmodified cart",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "disable_if_any_rule_applied"          => __(
                        "Disable all if any rule applied",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "disable_if_any_of_cart_items_updated" => __(
                        "Disable all if any of cart items updated",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                ),
                "apply"
            ),

            // rewrite 'external_coupons_behavior' option
            $builder::selective(
                "external_product_coupons_behavior",
                __('External coupons|Product coupons', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "apply"                                => __("Apply", 'advanced-dynamic-pricing-for-woocommerce'),
                    "apply_to_unmodified_only"             => __(
                        "Apply to unmodified cart items only",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "disable_if_any_rule_applied"          => __(
                        "Disable all if any rule applied",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    "disable_if_any_of_cart_items_updated" => __(
                        "Disable all if any of cart items updated",
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                ),
                "apply"
            ),

            // deprecated
            $builder::selective(
                'disable_external_coupons',
                __('Disable external coupons', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "dont_disable"                 => __("Don't disable", 'advanced-dynamic-pricing-for-woocommerce'),
                    "if_any_rule_applied"          => __("If any rule applied",
                        'advanced-dynamic-pricing-for-woocommerce'),
                    "if_any_of_cart_items_updated" => __("If any of cart items updated",
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                "dont_disable"
            ),
            $builder::boolean(
                'load_in_backend',
                false,
                __('Apply pricing rules to backend orders', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'replace_price_with_min_bulk_price',
                false,
                __('Replace price with lowest bulk price|Enable', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::htmlText(
                'replace_price_with_min_bulk_price_template',
                __("From {{price}} {{price_suffix}}", 'advanced-dynamic-pricing-for-woocommerce'),
                __('Replace price with lowest bulk price|Output template', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'disable_shipping_calc_during_process',
                false,
                __('Disable shipping calculation', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::boolean(
                'support_persistence_rules',
                false,
                __('Support Product only rules', 'advanced-dynamic-pricing-for-woocommerce')
            ),
            $builder::selective(
                "external_coupons_behavior",
                __('External coupons', 'advanced-dynamic-pricing-for-woocommerce'),
                array(
                    "apply"                                => __("Apply", 'advanced-dynamic-pricing-for-woocommerce'),
                    "disable_if_any_rule_applied"          => __("Disable all if any rule applied",
                        'advanced-dynamic-pricing-for-woocommerce'),
                    "disable_if_any_of_cart_items_updated" => __("Disable all if any of cart items updated",
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                "apply"
            )
        );

    }
}
