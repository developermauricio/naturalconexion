<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\RuleProcessor\CartTotals;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ValueComparisonCondition;

defined('ABSPATH') or exit;

class CartSubtotal extends AbstractCondition implements ValueComparisonCondition
{
    const LT = '<';
    const LTE = '<=';
    const MT = '>';
    const MTE = '>=';

    const AVAILABLE_COMP_METHODS = array(
        self::LT,
        self::LTE,
        self::MT,
        self::MTE,
    );

    protected $amountIndexes = array('comparison_value');
    /**
     * @var string
     */
    protected $comparisonMethod;

    /**
     * @var float|integer
     */
    protected $comparisonValue;

    public function check($cart)
    {
        $cartTotals     = new CartTotals($cart);
        $itemsSubtotals = $cartTotals->getSubtotalWithoutImmutable(false);

        $comparisonValue  = (float)$this->comparisonValue;
        $comparisonMethod = $this->comparisonMethod;

        return $this->compareValues($itemsSubtotals, $comparisonValue, $comparisonMethod);
    }

    public static function getType()
    {
        return 'cart_subtotal';
    }

    public static function getLabel()
    {
        return __('Subtotal (exc. VAT)', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/cart/subtotal.php';
    }

    public static function getGroup()
    {
        return ConditionsLoader::GROUP_CART;
    }

    public function setValueComparisonMethod($comparisonMethod)
    {
        in_array(
            $comparisonMethod,
            self::AVAILABLE_COMP_METHODS
        ) ? $this->comparisonMethod = $comparisonMethod : $this->comparisonMethod = null;
    }

    public function getValueComparisonMethod()
    {
        return $this->comparisonMethod;
    }

    /**
     * @param float|string $comparisonValue
     */
    public function setComparisonValue($comparisonValue)
    {
        is_numeric($comparisonValue) ? $this->comparisonValue = (float)$comparisonValue : $this->comparisonValue = null;
    }

    public function getComparisonValue()
    {
        return $this->comparisonValue;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->comparisonMethod) and ! is_null($this->comparisonValue);
    }

    public function multiplyAmounts($rate)
    {
        $this->comparisonValue *= (float)$rate;
    }

    public function getCartComparisonValue($cart)
    {
        $cartTotals = new CartTotals($cart);

        return $cartTotals->getSubtotalWithoutImmutable(false);
    }
}
