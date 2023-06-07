<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ProductAll;
use ADP\BaseVersion\Includes\Core\Rule\Enums\ProductMeasure;

class ProductCustomAttributesAll extends AbstractCondition implements ProductAll
{
    use ProductAllCheck;

    protected $filterType = 'product_custom_attributes';

    public static function getType()
    {
        return 'product_custom_attributes_all';
    }

    public static function getLabel()
    {
        return __('Product custom attributes', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return self::TEMPLATE_PATH;
    }

    public static function getGroup()
    {
        return ConditionsLoader::GROUP_CART_ITEMS;
    }

    public function isValid()
    {
        return true;
    }

    public static function getMeasures() {
        return array(
            ProductMeasure::MEASURE_QTY()->getValue() => __('Qty', 'advanced-dynamic-pricing-for-woocommerce'),
        );
    }

    public static function getSubConditionTemplatePaths()
    {
        return array(
            'product_custom_attributes' => WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/products/product-custom-attributes.php',
        );
    }
}
