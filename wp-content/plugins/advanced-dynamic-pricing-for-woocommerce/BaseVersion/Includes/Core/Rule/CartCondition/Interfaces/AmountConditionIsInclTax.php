<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces;

defined('ABSPATH') or exit;

interface AmountConditionIsInclTax
{
    const COMPARISON_IS_INCL_TAX_VALUE_KEY = 'is_incl_tax';

    /**
     * @param bool $inclTax
     */
    public function setInclTax($inclTax);

    /**
     * @return bool
     */
    public function isInclTax();
}
