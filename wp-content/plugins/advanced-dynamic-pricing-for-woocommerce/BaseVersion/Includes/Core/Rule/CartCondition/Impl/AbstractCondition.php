<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\RuleCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Comparison;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\IObjectInternationalization;

defined('ABSPATH') or exit;

abstract class AbstractCondition implements RuleCondition
{
    use Comparison;

    protected $amountIndexes = array();
    protected $hasProductDependency = false;

    public function __construct()
    {

    }

    /**
     * @param float $rate
     */
    public function multiplyAmounts($rate)
    {
    }

    /**
     * @return bool
     */
    public function check($cart)
    {
        return false;
    }

    public function getInvolvedCartItems()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function match($cart)
    {
        return $this->check($cart);
    }

    public function hasProductDependency()
    {
        return $this->hasProductDependency;
    }

    public function getProductDependency()
    {
        return array();
    }

    public function translate(IObjectInternationalization $oi)
    {
        return;
    }

    /**
     * @return mixed
     */
    public function getCartComparisonValue($cart)
    {
        return false;
    }
}
