<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition;

use ADP\BaseVersion\Includes\Core\Rule\Enums\ProductMeasure;
use ADP\BaseVersion\Includes\Core\Rule\Enums\ProductMeasures;
use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\Factory;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\CartItemsAmount;
use Exception;

defined('ABSPATH') or exit;

class ConditionsLoader
{
    const KEY = 'conditions';

    const LIST_TYPE_KEY = 'type';
    const LIST_LABEL_KEY = 'label';
    const LIST_TEMPLATE_PATH_KEY = 'path';
    const LIST_PARENT_CLASS_KEY = 'parent_class';

    const GROUP_CART_ITEMS = 'cart_items';
    const GROUP_CART = 'cart';
    const GROUP_CUSTOMER = 'customer';
    const GROUP_DATE_TIME = 'date_time';
    const GROUP_SHIPPING = 'shipping';

    /**
     * @var array
     */
    protected $groups = array();

    /**
     * @var string[]
     */
    protected $items = array();

    protected $customTaxonomies = array();

    public function __construct()
    {
        $this->initGroups();

        $this->customTaxonomies = array();
        foreach (Helpers::getCustomProductTaxonomies(true) as $taxonomy) {
            $this->customTaxonomies[$taxonomy->name] = $taxonomy;
        }

        foreach (Factory::getClassNames('Core_Rule_CartCondition_Impl') as $className) {
            /**
             * @var $className RuleCondition
             */

            if ($className == Factory::getClassName("Core_Rule_CartCondition_Impl_ProductTaxonomyAll")) {
                foreach ($this->customTaxonomies as $taxonomy) {
                    $this->items[$className::getType() . '_' . $taxonomy->name] = $className;
                }
            } else {
                $this->items[$className::getType()] = $className;
            }
        }

        $this->items = apply_filters('adp_load_rule_conditions', $this->items, $this);

        $this->items = array_filter($this->items, function ($item) {
            return is_subclass_of($item, '\ADP\BaseVersion\Includes\Core\Rule\CartCondition\RuleCondition');
        });
    }

    protected function initGroups()
    {
        $this->groups[self::GROUP_CART_ITEMS] = __('Cart items', 'advanced-dynamic-pricing-for-woocommerce');
        $this->groups[self::GROUP_CART]       = __('Cart', 'advanced-dynamic-pricing-for-woocommerce');
        $this->groups[self::GROUP_CUSTOMER]   = __('Customer', 'advanced-dynamic-pricing-for-woocommerce');
        $this->groups[self::GROUP_DATE_TIME]  = __('Date & time', 'advanced-dynamic-pricing-for-woocommerce');
        $this->groups[self::GROUP_SHIPPING]   = __('Shipping', 'advanced-dynamic-pricing-for-woocommerce');
    }

    /**
     * @param $data
     *
     * @return RuleCondition
     * @throws Exception
     */
    public function build($data)
    {
        if (empty($data['type'])) {
            throw new Exception('Missing condition type');
        }

        $condition = $this->create($data['type']);

        if ($condition instanceof Interfaces\ProductAll) {
            if (empty($data['options'][$condition::PRODUCT_MEASURE_KEY])) {
                throw new Exception('Missing measure for condition');
            }
            $measure = new ProductMeasure($data['options'][$condition::PRODUCT_MEASURE_KEY]);
            if ($measure->equals(ProductMeasure::MEASURE_QTY())) {
                $subCondition = new ConditionCartItemsQty();
                $subCondition->setStartRange($data['options'][$subCondition::START_RANGE_KEY] ?? null);
                $subCondition->setEndRange($data['options'][$subCondition::END_RANGE_KEY] ?? null);
                $subCondition->setComparisonList($data['options'][$subCondition::COMPARISON_LIST_KEY] ?? array());
                $subCondition->setListComparisonMethod($data['options'][$subCondition::COMPARISON_LIST_METHOD_KEY] ?? null);
            } elseif ($measure->equals(ProductMeasure::MEASURE_SUM())) {
                $subCondition = new ConditionCartItemsAmount();
                $subCondition->setComparisonValue($data['options'][$subCondition::COMPARISON_VALUE_KEY] ?? null);
                $subCondition->setValueComparisonMethod($data['options'][$subCondition::COMPARISON_VALUE_METHOD_KEY] ?? null);
                $subCondition->setComparisonList($data['options'][$subCondition::COMPARISON_LIST_KEY] ?? array());
                $subCondition->setListComparisonMethod($data['options'][$subCondition::COMPARISON_LIST_METHOD_KEY] ?? null);
                $inclTax = false;
                if ( isset($data['options'][$subCondition::COMPARISON_IS_INCL_TAX_VALUE_KEY] ) ) {
                    if ( is_string($data['options'][$subCondition::COMPARISON_IS_INCL_TAX_VALUE_KEY]) ) {
                        $inclTax = $data['options'][$subCondition::COMPARISON_IS_INCL_TAX_VALUE_KEY] === "1";
                    } elseif ( is_bool($data['options'][$subCondition::COMPARISON_IS_INCL_TAX_VALUE_KEY]) ) {
                        $inclTax = $data['options'][$subCondition::COMPARISON_IS_INCL_TAX_VALUE_KEY];
                    }
                }
                $subCondition->setInclTax($inclTax);
            } elseif ($measure->equals(ProductMeasure::MEASURE_WEIGHT())) {
                $subCondition = new ConditionCartItemsWeight();
                $subCondition->setComparisonValue($data['options'][$subCondition::COMPARISON_VALUE_KEY] ?? null);
                $subCondition->setValueComparisonMethod($data['options'][$subCondition::COMPARISON_VALUE_METHOD_KEY] ?? null);
                $subCondition->setComparisonList($data['options'][$subCondition::COMPARISON_LIST_KEY] ?? array());
                $subCondition->setListComparisonMethod($data['options'][$subCondition::COMPARISON_LIST_METHOD_KEY] ?? null);
            } else {
                throw new Exception('Wrong condition measure');
            }
            if (!$subCondition->isValid()) {
                throw new Exception('Wrong condition');
            }
            $condition->setSubCondition($subCondition);
        }
        if ($condition instanceof Interfaces\ValueComparisonCondition) {
            $condition->setComparisonValue($data['options'][$condition::COMPARISON_VALUE_KEY] ?? null);
            $condition->setValueComparisonMethod($data['options'][$condition::COMPARISON_VALUE_METHOD_KEY] ?? null);
        }
        if ($condition instanceof Interfaces\ListComparisonCondition) {
            $condition->setComparisonList($data['options'][$condition::COMPARISON_LIST_KEY] ?? array());
            $condition->setListComparisonMethod($data['options'][$condition::COMPARISON_LIST_METHOD_KEY] ?? null);
        }
        if ($condition instanceof Interfaces\RangeValueCondition) {
            $condition->setStartRange($data['options'][$condition::START_RANGE_KEY] ?? null);
            $condition->setEndRange($data['options'][$condition::END_RANGE_KEY] ?? null);
        }
        if ($condition instanceof Interfaces\TimeRangeCondition) {
            $condition->setTimeRange($data['options'][$condition::TIME_RANGE_KEY] ?? null);
        }
        if ($condition instanceof Interfaces\DateTimeComparisonCondition) {
            $condition->setComparisonDateTime($data['options'][$condition::COMPARISON_DATETIME_KEY] ?? null);
            $condition->setDateTimeComparisonMethod($data['options'][$condition::COMPARISON_DATETIME_METHOD_KEY] ?? null);
        }
        if ($condition instanceof Interfaces\BinaryCondition) {
            $condition->setComparisonBinValue($data['options'][$condition::COMPARISON_BIN_VALUE_KEY] ?? null);
        }
        if ($condition instanceof Interfaces\CombinationCondition) {
            $condition->setCombineType($data['options'][$condition::COMBINE_TYPE_KEY] ?? null);
            $condition->setCombineList($data['options'][$condition::COMBINE_LIST_KEY] ?? array());
            $condition->setComparisonEndValue($data['options'][$condition::COMPARISON_END_VALUE_KEY] ?? null);
            $condition->setCombineAnyProduct($data['options'][$condition::COMBINE_ANY_PRODUCT_KEY] ?? null);
        }
        if ($condition instanceof Interfaces\AmountConditionIsInclTax) {
            $inclTax = false;
            if ( isset($data['options'][$condition::COMPARISON_IS_INCL_TAX_VALUE_KEY] ) ) {
                if ( is_string($data['options'][$condition::COMPARISON_IS_INCL_TAX_VALUE_KEY]) ) {
                    $inclTax = $data['options'][$condition::COMPARISON_IS_INCL_TAX_VALUE_KEY] === "1";
                } elseif ( is_bool($data['options'][$condition::COMPARISON_IS_INCL_TAX_VALUE_KEY]) ) {
                    $inclTax = $data['options'][$condition::COMPARISON_IS_INCL_TAX_VALUE_KEY];
                }
            }
            $condition->setInclTax($inclTax);
        }

        if ($condition->isValid()) {
            return $condition;
        } else {
            throw new Exception('Wrong condition');
        }
    }

    /**
     * @param string $type
     *
     * @return RuleCondition
     * @throws Exception
     */
    public function create($type)
    {
        if ($type === 'custom_taxonomy_all') {
            $lastTaxName = array_keys($this->customTaxonomies);
            $lastTaxName = end($lastTaxName);

            $type = $type . '_' . $lastTaxName;
        }

        if (isset($this->items[$type])) {
            $className = $this->items[$type];

            if ($className == Factory::getClassName("Core_Rule_CartCondition_Impl_ProductTaxonomyAll")) {
                $obj = new $className();

                $taxonomyName = explode("_", $type);
                $taxonomyName = end($taxonomyName);

                if (isset($this->customTaxonomies[$taxonomyName])) {
                    $obj->setTaxonomy($this->customTaxonomies[$taxonomyName]);
                }
            } else {
                $obj = new $className();
            }

            return $obj;
        } else {
            throw new Exception('Wrong condition');
        }
    }

    /**
     * @return array
     */
    public function getAsList()
    {
        $list = array();

        foreach ($this->items as $type => $className) {
            /**
             * @var $className RuleCondition
             */

            if ($className == Factory::getClassName("Core_Rule_CartCondition_Impl_ProductTaxonomyAll")) {
                $taxonomyName = explode("_", $type);
                $taxonomyName = end($taxonomyName);

                if ( ! isset($this->customTaxonomies[$taxonomyName])) {
                    continue;
                }

                $taxonomy = $this->customTaxonomies[$taxonomyName];

                $taxonomyCondition = new $className();
                $taxonomyCondition->setTaxonomy($taxonomy);
                $list[$taxonomyCondition->getGroup()][] = array(
                    self::LIST_TYPE_KEY          => $type,
                    self::LIST_LABEL_KEY         => $taxonomyCondition->getTaxonomyLabel(),
                    self::LIST_TEMPLATE_PATH_KEY => $taxonomyCondition->getTemplatePath(),
                    self::LIST_PARENT_CLASS_KEY  => Factory::getShortClassName(get_parent_class($className)),
                    'taxonomy'                   => $taxonomy,
                );
            } else {
                $list[$className::getGroup()][] = array(
                    self::LIST_TYPE_KEY          => $className::getType(),
                    self::LIST_LABEL_KEY         => $className::getLabel(),
                    self::LIST_TEMPLATE_PATH_KEY => $className::getTemplatePath(),
                    self::LIST_PARENT_CLASS_KEY  => Factory::getShortClassName(get_parent_class($className)),
                );
            }
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getGroupLabel($key)
    {
        return isset($this->groups[$key]) ? $this->groups[$key] : null;
    }

    public function getItems()
    {
        return $this->items;
    }
}
