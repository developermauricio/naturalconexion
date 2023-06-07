<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\AmountConditionIsInclTax;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ValueComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\FilterTranslator;
use ADP\BaseVersion\Includes\Core\RuleProcessor\CartTotals;
use ADP\Factory;
use ADP\ProVersion\Includes\Core\Cart\Cart;
use ADP\ProVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\ProVersion\Includes\Core\RuleProcessor\ProductFiltering;

defined('ABSPATH') or exit;

class ConditionCartItemsAmount implements ListComparisonCondition, ValueComparisonCondition, AmountConditionIsInclTax
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

    protected $amountIndexes = array('comparisonAmount');

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
    protected $comparisonAmount;

    /**
     * @var bool
     */
    protected $inclTax = false;

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function check($cart)
    {
        $this->usedItems = array();

        $inListMode       = $this->inListMode;
        $comparisonList   = (array)$this->comparisonList;
        $comparisonMethod = $this->comparisonMethod;
        $comparisonAmount = (float)$this->comparisonAmount;

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

        $result = false;

        $newCart = clone $cart;
        $newCart->setItems(array());

        foreach ($cart->getItems() as $item_key => $item) {
            $wrapper = $item->getWcItem();
            $checked = $productFiltering->checkProductSuitability($wrapper->getProduct());

            if ($checked) {
                $newCart->addToCart(clone $item);
            }
        }

        $cartTotals     = new CartTotals($newCart);
        $itemsSubtotal = $cartTotals->getSubtotal($this->inclTax);

        if ( $this->compareValues($itemsSubtotal, $comparisonAmount, $comparisonMethod)) {
            $result = true;
        }

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
        $comparisonList = (array)$this->comparisonList;

        $comparisonList = (new FilterTranslator())->translateByType(
            $this->filterType,
            $comparisonList,
            $languageCode
        );

        $this->comparisonList = $comparisonList;
    }

    public function setListComparisonMethod($listComparisonMethod)
    {
        in_array(
            $listComparisonMethod,
            self::AVAILABLE_LIST_COMP_METHODS
        ) ? $this->inListMode = $listComparisonMethod : $this->inListMode = null;
    }

    public function setComparisonList($comparisonList)
    {
        gettype($comparisonList) === 'array' ? $this->comparisonList = $comparisonList : $this->comparisonList = null;
    }

    public function getComparisonList()
    {
        return $this->comparisonList;
    }

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
        $this->comparisonAmount = (float)$comparisonValue;
    }

    public function getValueComparisonMethod()
    {
        return $this->comparisonMethod;
    }

    public function getComparisonValue()
    {
        return $this->comparisonAmount;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->inListMode)
               && ! is_null($this->comparisonList)
               && ! is_null($this->comparisonMethod)
               && ! is_null($this->comparisonAmount);
    }

    public function multiplyAmounts($rate)
    {
        $this->comparisonAmount *= (float)$rate;
    }

    public function setInclTax($inclTax)
    {
        $this->inclTax = (bool)$inclTax;
    }

    public function isInclTax()
    {
        return $this->inclTax;
    }

    public function setFilterType($filterType) {
        $this->filterType = $filterType;
    }
}
