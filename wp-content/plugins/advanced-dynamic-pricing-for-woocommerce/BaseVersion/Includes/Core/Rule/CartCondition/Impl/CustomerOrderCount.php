<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\CartProcessor\CartCustomerHelper;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ValueComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\TimeRangeCondition;

defined('ABSPATH') or exit;

class CustomerOrderCount extends AbstractCondition implements ValueComparisonCondition, TimeRangeCondition
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

    /**
     * @var string
     */
    protected $comparisonMethod;

    /**
     * @var int
     */
    protected $comparisonValue;

    /**
     * @var string
     */
    protected $timeRange;

    public function check($cart)
    {
        $timeRange          = $this->timeRange;
        $comparisonMethod   = $this->comparisonMethod;
        $comparisonValue    = (int)$this->comparisonValue;
        $context            = $cart->getContext()->getGlobalContext();
        $cartCustomerHelper = new CartCustomerHelper($context, $cart->getContext()->getCustomer());
        $orderCount         = $cartCustomerHelper->getOrderCountAfter($timeRange);

        return $this->compareValues($orderCount, $comparisonValue, $comparisonMethod);
    }

    public static function getType()
    {
        return 'customer_order_count';
    }

    public static function getLabel()
    {
        return __('Order count', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/customer/order-count.php';
    }

    public static function getGroup()
    {
        return ConditionsLoader::GROUP_CUSTOMER;
    }

    public function setValueComparisonMethod($comparisonMethod)
    {
        in_array(
            $comparisonMethod,
            self::AVAILABLE_COMP_METHODS
        ) ? $this->comparisonMethod = $comparisonMethod : $this->comparisonMethod = null;
    }

    /**
     * @param int $comparisonValue
     */
    public function setComparisonValue($comparisonValue)
    {
        $this->comparisonValue = (int)$comparisonValue;
    }

    /**
     * @return string|null
     */
    public function getValueComparisonMethod()
    {
        return $this->comparisonMethod;
    }

    public function getComparisonValue()
    {
        return $this->comparisonValue;
    }

    public function setTimeRange($timeRange)
    {
        gettype($timeRange) === 'string' ? $this->timeRange = $timeRange : $this->timeRange = null;
    }

    public function getTimeRange()
    {
        return $this->timeRange;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->comparisonMethod) and ! is_null($this->comparisonValue) and ! is_null($this->timeRange);
    }
}
