<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces;

defined('ABSPATH') or exit;

interface ValueComparisonCondition
{
    const COMPARISON_VALUE_KEY = 'comparison_value';
    const COMPARISON_VALUE_METHOD_KEY = 'comparison_value_method';

    /**
     * @param string|null $comparisonMethod
     */
    public function setValueComparisonMethod($comparisonMethod);

    /**
     * @return string|null
     */
    public function getValueComparisonMethod();

    /**
     * @param string|float|null $comparisonValue
     */
    public function setComparisonValue($comparisonValue);

    /**
     * @return string|float|null
     */
    public function getComparisonValue();
}
