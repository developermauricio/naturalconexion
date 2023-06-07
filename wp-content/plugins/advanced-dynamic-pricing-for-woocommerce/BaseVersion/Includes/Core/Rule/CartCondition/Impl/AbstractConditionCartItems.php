<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\Rule\Internationalization\FilterTranslator;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\IObjectInternationalization;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\RuleTranslator;
use ADP\Factory;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductFiltering;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\RangeValueCondition;

defined('ABSPATH') or exit;

abstract class AbstractConditionCartItems extends AbstractCondition implements ListComparisonCondition, RangeValueCondition
{
    const IN_LIST = 'in_list';
    const NOT_IN_LIST = 'not_in_list';
    const NOT_CONTAINING = 'not_containing';

    const AVAILABLE_COMP_METHODS = array(
        self::IN_LIST,
        self::NOT_IN_LIST,
        self::NOT_CONTAINING,
    );

    /**
     * @var array
     */
    protected $comparisonList;

    /**
     * @var string
     */
    protected $comparisonMethod;

    /**
     * @var int
     */
    protected $comparisonQty;

    /**
     * @var int
     */
    protected $comparisonQtyFinish;

    protected $usedItems;
    protected $hasProductDependency = true;
    protected $filterType = '';

    public function check($cart)
    {
        $this->usedItems = array();

        $comparisonQty             = (float)$this->comparisonQty;
        $comparisonQtyFinishExists = isset($this->comparisonQtyFinish) && $this->comparisonQtyFinish != 0 ? "" !== $this->comparisonQtyFinish : false;
        $comparisonQtyFinish       = $comparisonQtyFinishExists ? (float)$this->comparisonQtyFinish : INF;
        $comparisonMethod          = $this->comparisonMethod ?? 'in_list';
        $comparisonList            = $this->comparisonList ?? array();

        if (empty($comparisonQty)) {
            return true;
        }

        $invertFiltering = false;
        if ($comparisonMethod === "not_containing") {
            $invertFiltering  = true;
            $comparisonMethod = 'in_list';
        }

        $qty              = 0;
        /** @var ProductFiltering $productFiltering */
        $productFiltering = Factory::get("Core_RuleProcessor_ProductFiltering", $cart->getContext()->getGlobalContext());

        $productFiltering->prepare($this->filterType, $comparisonList, $comparisonMethod);

        foreach ($cart->getItems() as $item_key => $item) {
            $wrapper = $item->getWcItem();
            $checked = $productFiltering->checkProductSuitability($wrapper->getProduct());

            if ($checked) {
                $qty += $item->getQty();
            }
        }

        $result = $comparisonQtyFinishExists ? ($comparisonQty <= $qty) && ($qty <= $comparisonQtyFinish) : $comparisonQty <= $qty;

        return $invertFiltering ? ! $result : $result;
    }

    public function getInvolvedCartItems()
    {
        return $this->usedItems;
    }

    public function match($cart)
    {
        return $this->check($cart);
    }

    public function getProductDependency()
    {
        return array(
            'qty'    => $this->comparisonQty,
            'type'   => $this->filterType,
            'method' => $this->comparisonMethod,
            'value'  => (array)$this->comparisonList,
        );
    }

    public function translate(IObjectInternationalization $oi)
    {
        parent::translate($oi);

        $this->comparisonList = (new FilterTranslator())->translateByType(
            $this->filterType,
            (array)$this->comparisonList,
            $oi
        );
    }

    /**
     * @param array $comparisonList
     */
    public function setComparisonList($comparisonList)
    {
        gettype($comparisonList) === 'array' ? $this->comparisonList = $comparisonList : $this->comparisonList = null;
    }

    public function setListComparisonMethod($comparisonMethod)
    {
        in_array(
            $comparisonMethod,
            self::AVAILABLE_COMP_METHODS)
            ? $this->comparisonMethod = $comparisonMethod
            : $this->comparisonMethod = null;
    }

    public function getComparisonList()
    {
        return $this->comparisonList;
    }

    public function getListComparisonMethod()
    {
        return $this->comparisonMethod;
    }

    public function setStartRange($startRange)
    {
        $this->comparisonQty = (int)$startRange;
    }

    public function getStartRange()
    {
        return $this->comparisonQty;
    }

    public function setEndRange($endRange)
    {
        $this->comparisonQtyFinish = (int)$endRange;
    }

    public function getEndRange()
    {
        return $this->comparisonQtyFinish;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->comparisonMethod) and ! is_null($this->comparisonList) and ! is_null($this->comparisonQty);
    }
}
