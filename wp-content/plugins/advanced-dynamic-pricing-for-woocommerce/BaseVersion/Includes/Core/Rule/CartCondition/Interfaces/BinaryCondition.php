<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces;

defined('ABSPATH') or exit;

interface BinaryCondition
{
    const COMPARISON_BIN_VALUE_KEY = 'comparison_bin_value';

    /**
     * @param string|bool $comparisonValue
     */
    public function setComparisonBinValue($comparisonValue);

    /**
     * @return bool|null
     */
    public function getComparisonBinValue();
}
