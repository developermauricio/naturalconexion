<?php

namespace ADP\BaseVersion\Includes\VolumePricingTable;

use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Filter;
use ADP\BaseVersion\Includes\Helpers\Helpers;

defined('ABSPATH') or exit;

class FiltersFormatter
{
    public function __construct()
    {
    }

    /**
     * @param Filter $filter
     *
     * @return string
     */
    public function formatFilter($filter)
    {
        $filterType    = $filter->getType();
        $filter_method = $filter->getMethod();

        if ($filter::TYPE_ANY === $filterType) {
            return sprintf('<a href="%s">%s</a>', get_permalink(wc_get_page_id('shop')),
                __('Any product', 'advanced-dynamic-pricing-for-woocommerce'));
        }

        $templates = array_merge(array(
            'products' => array(
                'in_list'     => __('Product from the list: %s', 'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product not from the list: %s', 'advanced-dynamic-pricing-for-woocommerce'),
            ),

            'product_sku' => array(
                'in_list'     => __('Product with SKUs from the list: %s', 'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product with SKUs not from the list: %s',
                    'advanced-dynamic-pricing-for-woocommerce'),
            ),

            'product_sellers' => array(
                'in_list'     => __('Product from sellers: %s', 'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product not from sellers: %s', 'advanced-dynamic-pricing-for-woocommerce'),
            ),

            'product_categories' => array(
                'in_list'     => __('Product from categories: %s', 'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product not from categories: %s',
                    'advanced-dynamic-pricing-for-woocommerce'),
            ),

            'product_category_slug' => array(
                'in_list'     => __('Product from categories with slug: %s',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product not from categories with slug: %s',
                    'advanced-dynamic-pricing-for-woocommerce'),
            ),

            'product_tags' => array(
                'in_list'     => __('Product with tags from list: %s', 'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product with tags not from list: %s', 'advanced-dynamic-pricing-for-woocommerce'),
            ),

            'product_attributes' => array(
                'in_list'     => __('Product with attributes from list: %s',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product with attributes not from list: %s',
                    'advanced-dynamic-pricing-for-woocommerce'),
            ),

            'product_custom_fields' => array(
                'in_list'     => __('Product with custom fields: %s', 'advanced-dynamic-pricing-for-woocommerce'),
                'not_in_list' => __('Product without custom fields: %s', 'advanced-dynamic-pricing-for-woocommerce'),
            ),
        ), array_combine(array_keys(Helpers::getCustomProductTaxonomies()),
            array_map(function ($tmpFilterType) {
                return array(
                    'in_list'     => __('Product with ' . $tmpFilterType . ' from list: %s',
                        'advanced-dynamic-pricing-for-woocommerce'),
                    'not_in_list' => __('Product with ' . $tmpFilterType . ' not from list: %s',
                        'advanced-dynamic-pricing-for-woocommerce'),
                );
            }, array_keys(Helpers::getCustomProductTaxonomies()))));

        if ( ! isset($templates[$filterType][$filter_method])) {
            return "";
        }

        $humanizedValues = array();
        foreach ($filter->getValue() as $id) {
            $name = Helpers::getTitleByType($id, $filterType);
            $link = Helpers::getPermalinkByType($id, $filterType);

            if ( ! empty($link)) {
                $humanized_value = "<a href='{$link}'>{$name}</a>";
            } else {
                $humanized_value = "'{$name}'";
            }

            $humanizedValues[$id] = $humanized_value;
        }

        return sprintf($templates[$filterType][$filter_method], implode(", ", $humanizedValues));
    }

    /**
     * @param SingleItemRule $rule
     *
     * @return array
     */
    public function formatRule(SingleItemRule $rule)
    {
        $humanizedFilters = array();

        foreach ($rule->getFilters() as $filter) {
            $humanizedFilters[] = $this->formatFilter($filter);
        }

        return $humanizedFilters;
    }
}
