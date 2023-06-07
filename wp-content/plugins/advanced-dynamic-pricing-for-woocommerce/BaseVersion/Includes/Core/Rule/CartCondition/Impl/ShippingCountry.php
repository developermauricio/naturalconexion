<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;

defined('ABSPATH') or exit;

class ShippingCountry extends AbstractCondition implements ListComparisonCondition
{
    const IN_LIST = 'in_list';
    const NOT_IN_LIST = 'not_in_list';

    const AVAILABLE_COMP_METHODS = array(
        self::IN_LIST,
        self::NOT_IN_LIST,
    );

    /**
     * @var array
     */
    protected $comparisonList;

    /**
     * @var string
     */
    protected $comparisonMethod;

    public function check($cart)
    {
        $country = $cart->getContext()->getCustomer()->getShippingCountry();

        $comparisonList   = (array)$this->comparisonList;
        $comparisonMethod = $this->comparisonMethod;

        return $this->compareValueWithList($country, $comparisonList, $comparisonMethod);
    }

    public static function getType()
    {
        return 'shipping_country';
    }

    public static function getLabel()
    {
        return __('Country', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/shipping/country.php';
    }

    public static function getGroup()
    {
        return ConditionsLoader::GROUP_SHIPPING;
    }

    public function setComparisonList($comparisonList)
    {
        gettype($comparisonList) === 'array' ? $this->comparisonList = $comparisonList : $this->comparisonList = null;
    }

    public function setListComparisonMethod($comparisonMethod)
    {
        in_array(
            $comparisonMethod,
            self::AVAILABLE_COMP_METHODS
        ) ? $this->comparisonMethod = $comparisonMethod : $this->comparisonMethod = null;
    }

    public function getComparisonList()
    {
        return $this->comparisonList;
    }

    public function getListComparisonMethod()
    {
        return $this->comparisonMethod;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->comparisonMethod) and ! is_null($this->comparisonList);
    }
}
