<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces;

defined('ABSPATH') or exit;

interface CombinationCondition
{
    const COMBINE_TYPE_KEY = 'combine_type';
    const COMBINE_LIST_KEY = 'combine_list';
    const COMBINE_ANY_PRODUCT_KEY = 'combine_any_product';
    // special key for in_range method
    // start value implemented in ValueComparisonCondition
    const COMPARISON_END_VALUE_KEY = 'comparison_end_value';

    /**
     * @param string|null $combineType
     */
    public function setCombineType($combineType);

    /**
     * @return string|null
     */
    public function getCombineType();

    /**
     * @param array|null $combineList
     */
    public function setCombineList($combineList);

    /**
     * @return array|null
     */
    public function getCombineList();

    /**
     * @param bool|null $combineAnyProduct
     */
    public function setCombineAnyProduct($combineAnyProduct);

    /**
     * @return bool|null
     */
    public function getCombineAnyProduct();

    /**
     * @param int|null $comparisonEndValue
     */
    public function setComparisonEndValue($comparisonEndValue);

    /**
     * @return int|null
     */
    public function getComparisonEndValue();
}
