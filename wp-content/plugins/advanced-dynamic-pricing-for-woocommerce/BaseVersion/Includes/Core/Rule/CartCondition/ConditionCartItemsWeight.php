<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ValueComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\FilterTranslator;
use ADP\Factory;
use ADP\ProVersion\Includes\Core\Cart\Cart;
use ADP\ProVersion\Includes\Core\RuleProcessor\ProductFiltering;

class ConditionCartItemsWeight implements ListComparisonCondition, ValueComparisonCondition
{
    use Comparison;

    const IN_LIST = 'in_list';
    const NOT_IN_LIST = 'not_in_list';
    const NOT_CONTAINING = 'not_containing';

    const AVAILABLE_LIST_COMP_METHODS = array(
        self::IN_LIST,
        self::NOT_IN_LIST,
        self::NOT_CONTAINING,
    );

    const LT = '<';
    const LTE = '<=';
    const MT = '>';
    const MTE = '>=';

    const AVAILABLE_VALUE_COMP_METHODS = array(
        self::LT,
        self::LTE,
        self::MT,
        self::MTE,
    );

    protected $weightIndexes = array('comparisonWeight');

    protected $usedItems;
    protected $hasProductDependency = true;
    protected $filterType = '';

    /**
     * @var string
     */
    protected $inListMode;
    /**
     * @var array
     */
    protected $comparisonList;
    /**
     * @var string
     */
    protected $comparisonMethod;
    /**
     * @var float
     */
    protected $comparisonValue;

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function check($cart)
    {
        $this->usedItems = array();

        $inListMode       = $this->inListMode;
        $comparisonList   = $this->comparisonList;

        if (empty($comparisonList)) {
            return true;
        }

        $invertFiltering = false;
        if ($inListMode === "not_containing") {
            $invertFiltering = true;
            $inListMode      = ComparisonMethods::IN_LIST;
        }

        /** @var $productFiltering ProductFiltering */
        $productFiltering = Factory::get(
            "Core_RuleProcessor_ProductFiltering",
            $cart->getContext()->getGlobalContext()
        );
        $productFiltering->prepare($this->filterType, $comparisonList, $inListMode);

        $weight = 0;

        foreach ($cart->getItems() as $item_key => $item) {
            $checked = $productFiltering->checkProductSuitability($item->getWcItem()->getProduct());

            if ($checked) {
                $weight += $item->getWeight() * $item->getQty();
            }
        }
        $comparisonValue  = $this->comparisonValue;
        $comparisonMethod = $this->comparisonMethod;

        $result = $this->compareValues($weight, $comparisonValue, $comparisonMethod);

        return $invertFiltering ? ! $result : $result;
    }

    public function getInvolvedCartItems()
    {
        return $this->usedItems;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function match($cart)
    {
        return $this->check($cart);
    }

    public function translate($languageCode)
    {
        $comparisonList = $this->comparisonList;

        $comparisonList = (new FilterTranslator())->translateByType(
            $this->filterType,
            $comparisonList,
            $languageCode
        );

        $this->comparisonList = $comparisonList;
    }

    public function multiplyAmounts($rate) {
    }

    /**
     * @inheritDoc
     */
    public function setComparisonList($comparisonList)
    {
        gettype($comparisonList) === 'array' ? $this->comparisonList = $comparisonList : $this->comparisonList = null;
    }

    /**
     * @inheritDoc
     */
    public function getComparisonList()
    {
        return $this->comparisonList;
    }

    /**
     * @inheritDoc
     */
    public function setListComparisonMethod($listComparisonMethod)
    {
        in_array(
            $listComparisonMethod,
            self::AVAILABLE_LIST_COMP_METHODS
        ) ? $this->inListMode = $listComparisonMethod : $this->inListMode = null;
    }

    /**
     * @inheritDoc
     */
    public function getListComparisonMethod()
    {
        return $this->inListMode;
    }

    public function setValueComparisonMethod($comparisonMethod)
    {
        if (in_array($comparisonMethod, self::AVAILABLE_VALUE_COMP_METHODS)) {
            $this->comparisonMethod = $comparisonMethod;
        } else {
            $this->comparisonMethod = null;
        }
    }

    public function setComparisonValue($comparisonValue)
    {
        $this->comparisonValue = (float)$comparisonValue;
    }

    public function getValueComparisonMethod()
    {
        return $this->comparisonMethod;
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
        return ! is_null($this->inListMode)
               && ! is_null($this->comparisonList)
               && ! is_null($this->comparisonMethod)
               && ! is_null($this->comparisonValue);
    }

    public function setFilterType($filterType) {
        $this->filterType = $filterType;
    }
}
