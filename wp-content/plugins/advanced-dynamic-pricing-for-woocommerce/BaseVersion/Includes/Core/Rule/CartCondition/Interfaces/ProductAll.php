<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces;

defined('ABSPATH') or exit;

interface ProductAll
{
    const PRODUCT_MEASURE_KEY = 'product_measure';
    const TEMPLATE_PATH = WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/products/product-all.php';

    /**
     * @return array
     */
    public static function getMeasures();

    /**
     * @return array
     */
    public static function getSubConditionTemplatePaths();

    /**
     * @return object
     */
    public function getSubCondition();
}
