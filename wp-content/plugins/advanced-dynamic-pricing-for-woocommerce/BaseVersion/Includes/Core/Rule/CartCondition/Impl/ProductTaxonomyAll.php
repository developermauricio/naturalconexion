<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ProductAll;
use ADP\BaseVersion\Includes\Core\Rule\Enums\ProductMeasure;
use WP_Taxonomy;

class ProductTaxonomyAll extends AbstractCondition implements ProductAll
{
    use ProductAllCheck;

    /**
     * @var WP_Taxonomy
     */
    protected $taxonomy;
    protected $filterType = '';

    public static function getType()
    {
        return 'custom_taxonomy_all';
    }

    public static function getMeasures()
    {
        return array(
            ProductMeasure::MEASURE_QTY()->getValue() => __('Qty', 'advanced-dynamic-pricing-for-woocommerce'),
        );
    }


    public static function getLabel()
    {
        return "";
    }

    /**
     * @return string
     */
    public function getTaxonomyLabel()
    {
        return $this->taxonomy->label;
    }

    /**
     * @param WP_Taxonomy $taxonomy
     */
    public function setTaxonomy(WP_Taxonomy $taxonomy)
    {
        $this->taxonomy = $taxonomy;
        $this->filterType = $taxonomy->name;
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

    public static function getSubConditionTemplatePaths()
    {
        return array(
            'custom_taxonomy' => WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/products/product-taxonomy.php',
        );
    }
}
