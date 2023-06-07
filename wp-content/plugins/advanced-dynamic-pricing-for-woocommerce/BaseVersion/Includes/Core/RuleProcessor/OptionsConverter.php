<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustmentsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\CouponCartAdj;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\FeeCartAdj;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\ShippingCartAdj;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\ShippingMethodCartAdj;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionCartItemsAmount;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionCartItemsQty;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionCartItemsWeight;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\AmountConditionIsInclTax;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\BinaryCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\CombinationCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\DateTimeComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ProductAll;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\RangeValueCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\TimeRangeCondition;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ValueComparisonCondition;
use ADP\BaseVersion\Includes\Core\Rule\Enums\ProductMeasure;
use ADP\BaseVersion\Includes\Core\Rule\Limit\Interfaces\MaxUsageLimit;
use ADP\BaseVersion\Includes\Core\Rule\Limit\LimitsLoader;
use ADP\Factory;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\CouponListCartAdj;
use Exception;

defined('ABSPATH') or exit;

class OptionsConverter
{
    public static function convertCondition($data)
    {
        if (empty($data['type'])) {
            throw new Exception('Missing condition type');
        }

        /** @var ConditionsLoader $conditionsLoader */
        $conditionsLoader = Factory::get("Core_Rule_CartCondition_ConditionsLoader");

        try {
            $condition = $conditionsLoader->create($data['type']);
        } catch (\Exception $e) {
            throw new Exception('Missing condition type');
        }

        if (isset($data['options'][0])) {
            if ($condition instanceof CombinationCondition and $condition instanceof ValueComparisonCondition) {
                $data['options'][$condition::COMBINE_TYPE_KEY]            = $data['options'][0];
                $data['options'][$condition::COMBINE_LIST_KEY]            = $data['options'][1];
                $data['options'][$condition::COMPARISON_VALUE_METHOD_KEY] = $data['options'][2];
                $data['options'][$condition::COMPARISON_VALUE_KEY]        = $data['options'][3];
                if(isset($data['options'][4])) {
                    $data['options'][$condition::COMPARISON_END_VALUE_KEY] = $data['options'][4];
                    unset($data['options'][4]);
                }
                if(isset($data['options'][5])) {
                    $data['options'][$condition::COMBINE_ANY_PRODUCT_KEY] = $data['options'][5];
                    unset($data['options'][5]);
                }
                unset($data['options'][0]);
                unset($data['options'][1]);
                unset($data['options'][2]);
                unset($data['options'][3]);
            } elseif ($condition instanceof ValueComparisonCondition and $condition instanceof TimeRangeCondition) {
                $data['options'][$condition::TIME_RANGE_KEY]              = $data['options'][0];
                $data['options'][$condition::COMPARISON_VALUE_METHOD_KEY] = $data['options'][1];
                $data['options'][$condition::COMPARISON_VALUE_KEY]        = $data['options'][2];
                unset($data['options'][0]);
                unset($data['options'][1]);
                unset($data['options'][2]);
            } elseif ($condition instanceof ListComparisonCondition and $condition instanceof ValueComparisonCondition and
                      $condition instanceof AmountConditionIsInclTax) {
                $data['options'][$condition::COMPARISON_LIST_METHOD_KEY]  = $data['options'][0];
                $data['options'][$condition::COMPARISON_LIST_KEY]         = $data['options'][1];
                $data['options'][$condition::COMPARISON_VALUE_METHOD_KEY] = $data['options'][2];
                $data['options'][$condition::COMPARISON_VALUE_KEY]        = $data['options'][3];
                unset($data['options'][0]);
                unset($data['options'][1]);
                unset($data['options'][2]);
                unset($data['options'][3]);

                if (isset($data['options'][4])) {
                    $data['options'][$condition::COMPARISON_IS_INCL_TAX_VALUE_KEY] = $data['options'][4];
                    unset($data['options'][4]);
                } else {
                    $data['options'][$condition::COMPARISON_IS_INCL_TAX_VALUE_KEY] = false;
                }
            } elseif ($condition instanceof ListComparisonCondition and $condition instanceof RangeValueCondition) {
                $data['options'][$condition::START_RANGE_KEY]            = $data['options'][0];
                $data['options'][$condition::COMPARISON_LIST_METHOD_KEY] = $data['options'][1];
                $data['options'][$condition::COMPARISON_LIST_KEY]        = $data['options'][2];
                $data['options'][$condition::END_RANGE_KEY]              = $data['options'][3];
                unset($data['options'][0]);
                unset($data['options'][1]);
                unset($data['options'][2]);
                unset($data['options'][3]);
            } elseif ($condition instanceof ValueComparisonCondition) {
                $data['options'][$condition::COMPARISON_VALUE_METHOD_KEY] = $data['options'][0];
                $data['options'][$condition::COMPARISON_VALUE_KEY]        = $data['options'][1];
                unset($data['options'][0]);
                unset($data['options'][1]);
            } elseif ($condition instanceof ListComparisonCondition) {
                $data['options'][$condition::COMPARISON_LIST_METHOD_KEY] = $data['options'][0];
                $data['options'][$condition::COMPARISON_LIST_KEY]        = isset($data['options'][1]) ? $data['options'][1] : array();
                unset($data['options'][0]);
                unset($data['options'][1]);
            } elseif ($condition instanceof DateTimeComparisonCondition) {
                $data['options'][$condition::COMPARISON_DATETIME_METHOD_KEY] = $data['options'][0];
                $data['options'][$condition::COMPARISON_DATETIME_KEY]        = $data['options'][1];
                unset($data['options'][0]);
                unset($data['options'][1]);
            } elseif ($condition instanceof BinaryCondition) {
                $data['options'][$condition::COMPARISON_BIN_VALUE_KEY] = $data['options'][0];
                unset($data['options'][0]);
            }
        }

        return $data;
    }

    public static function convertConditionToArray($condition)
    {
        $result         = array();
        $result['type'] = $condition->getType();
        if ($condition instanceof ProductAll) {
            $condition = $condition->getSubCondition();
        }
        if ($condition instanceof CombinationCondition and $condition instanceof ValueComparisonCondition) {
            $result['options'] = array(
                CombinationCondition::COMBINE_TYPE_KEY => $condition->getCombineType(),
                CombinationCondition::COMBINE_LIST_KEY => $condition->getCombineList(),
                ValueComparisonCondition::COMPARISON_VALUE_METHOD_KEY => $condition->getValueComparisonMethod(),
                ValueComparisonCondition::COMPARISON_VALUE_KEY => $condition->getComparisonValue(),
            );
            if($comparisonEndValue = $condition->getComparisonEndValue()) {
                $result['options'][CombinationCondition::COMPARISON_END_VALUE_KEY] = $comparisonEndValue;
            }
            if($combineAnyProduct = $condition->getCombineAnyProduct()) {
                $result['options'][CombinationCondition::COMBINE_ANY_PRODUCT_KEY] = $combineAnyProduct;
            }
        } elseif ($condition instanceof ValueComparisonCondition and $condition instanceof TimeRangeCondition) {
            $result['options'] = array(
                TimeRangeCondition::TIME_RANGE_KEY => $condition->getTimeRange(),
                ValueComparisonCondition::COMPARISON_VALUE_METHOD_KEY => $condition->getValueComparisonMethod(),
                ValueComparisonCondition::COMPARISON_VALUE_KEY => $condition->getComparisonValue(),
            );
        } elseif ($condition instanceof ListComparisonCondition and $condition instanceof ValueComparisonCondition and
            $condition instanceof AmountConditionIsInclTax) {
            $result['options'] = array(
                ListComparisonCondition::COMPARISON_LIST_METHOD_KEY => $condition->getListComparisonMethod(),
                ListComparisonCondition::COMPARISON_LIST_KEY => $condition->getComparisonList(),
                ValueComparisonCondition::COMPARISON_VALUE_METHOD_KEY => $condition->getValueComparisonMethod(),
                ValueComparisonCondition::COMPARISON_VALUE_KEY => $condition->getComparisonValue(),
            );
            if($inclTax = $condition->isInclTax()) {
                $result['options'][AmountConditionIsInclTax::COMPARISON_IS_INCL_TAX_VALUE_KEY] = $inclTax;
            }
        } elseif ($condition instanceof ListComparisonCondition and $condition instanceof RangeValueCondition) {
            $result['options'] = array(
                RangeValueCondition::START_RANGE_KEY => $condition->getStartRange(),
                ListComparisonCondition::COMPARISON_LIST_METHOD_KEY => $condition->getListComparisonMethod(),
                ListComparisonCondition::COMPARISON_LIST_KEY => $condition->getComparisonList(),
                RangeValueCondition::END_RANGE_KEY => $condition->getEndRange(),
            );
        } elseif ($condition instanceof ValueComparisonCondition) {
            $result['options'] = array(
                ValueComparisonCondition::COMPARISON_VALUE_METHOD_KEY => $condition->getValueComparisonMethod(),
                ValueComparisonCondition::COMPARISON_VALUE_KEY => $condition->getComparisonValue(),
            );
        } elseif ($condition instanceof ListComparisonCondition) {
            $result['options'] = array(
                ListComparisonCondition::COMPARISON_LIST_METHOD_KEY => $condition->getListComparisonMethod(),
                ListComparisonCondition::COMPARISON_LIST_KEY => $condition->getComparisonList(),
            );
        } elseif ($condition instanceof DateTimeComparisonCondition) {
            $result['options'] = array(
                DateTimeComparisonCondition::COMPARISON_DATETIME_METHOD_KEY => $condition->getDateTimeComparisonMethod(),
                DateTimeComparisonCondition::COMPARISON_DATETIME_KEY => $condition->getComparisonDateTime(),
            );
        } elseif ($condition instanceof BinaryCondition) {
            $result['options'] = array(
                BinaryCondition::COMPARISON_BIN_VALUE_KEY => $condition->getComparisonBinValue(),
            );
        }

        if ($condition instanceof ConditionCartItemsQty) {
            $result['options'][ProductAll::PRODUCT_MEASURE_KEY] = ProductMeasure::MEASURE_QTY()->getValue();
        } elseif ($condition instanceof ConditionCartItemsAmount) {
            $result['options'][ProductAll::PRODUCT_MEASURE_KEY] = ProductMeasure::MEASURE_SUM()->getValue();
        } elseif ($condition instanceof ConditionCartItemsWeight) {
            $result['options'][ProductAll::PRODUCT_MEASURE_KEY] = ProductMeasure::MEASURE_WEIGHT()->getValue();
        }

        return $result;
    }

    public static function convertCartAdj($data)
    {
        if (empty($data['type'])) {
            throw new Exception('Missing cart adjustment type');
        }

        /** @var CartAdjustmentsLoader $cartAdjLoader */
        $cartAdjLoader = Factory::get("Core_Rule_CartAdjustment_CartAdjustmentsLoader");
        $adj = $cartAdjLoader->create($data['type']);

        if ($adj instanceof CouponCartAdj) {
            if (isset($data['options'][0])) {
                $data['options'][$adj::COUPON_VALUE_KEY] = $data['options'][0];
            }

            if (isset($data['options'][1])) {
                $data['options'][$adj::COUPON_CODE_KEY] = $data['options'][1];
            }

            if (isset($data['options'][2])) {
                $data['options'][$adj::COUPON_MAX_DISCOUNT] = $data['options'][2];
                unset($data['options'][2]);
            }
            unset($data['options'][0]);
            unset($data['options'][1]);
        } elseif ($adj instanceof CouponListCartAdj) {
            if (isset($data['options'][0])) {
                $data['options'][$adj::COUPON_LIST_KEY] = $data['options'];
            }

            unset($data['options'][0]);
        } elseif ($adj instanceof FeeCartAdj) {
            if (isset($data['options'][0])) {
                $data['options'][$adj::FEE_VALUE_KEY] = $data['options'][0];
            }

            if (isset($data['options'][1])) {
                $data['options'][$adj::FEE_NAME_KEY] = $data['options'][1];
            }

            if (isset($data['options'][2])) {
                $data['options'][$adj::FEE_TAX_CLASS_KEY] = $data['options'][2];
            }

            unset($data['options'][0]);
            unset($data['options'][1]);
            unset($data['options'][2]);
        } elseif ($adj instanceof ShippingCartAdj && $adj instanceof ShippingMethodCartAdj) {
            if (isset($data['options'][0])) {
                $data['options'][$adj::SHIPPING_CARTADJ_METHOD] = $data['options'][0];
            }
            if (isset($data['options'][1])) {
                $data['options'][$adj::SHIPPING_CARTADJ_VALUE] = $data['options'][1];
            }
            unset($data['options'][0]);
            unset($data['options'][1]);
        } elseif ($adj instanceof ShippingCartAdj) {
            if (isset($data['options'][0])) {
                $data['options'][$adj::SHIPPING_CARTADJ_VALUE] = $data['options'][0];
            }
            unset($data['options'][0]);
        } elseif ($adj instanceof ShippingMethodCartAdj) {
            if (isset($data['options'][0])) {
                $data['options'][$adj::SHIPPING_CARTADJ_METHOD] = $data['options'][0];
            }
            unset($data['options'][0]);
        }

        return $data;
    }

    public static function convertCartAdjToArray($adj)
    {
        $result         = array();
        $result['type'] = $adj->getType();

        if ($adj instanceof CouponCartAdj) {
            $result['options'] = array(
                0 => $adj->getCouponValue(),
                1 => $adj->getCouponCode(),
            );
        } elseif ($adj instanceof FeeCartAdj) {
            $result['options'] = array(
                0 => $adj->getFeeValue(),
                1 => $adj->getFeeName(),
                2 => $adj->getFeeTaxClass(),
            );
        } elseif ($adj instanceof ShippingCartAdj) {
            $result['options'] = array(
                0 => $adj->getShippingCartAdjValue(),
            );
        }

        return $result;
    }

    public static function convertLimit($data)
    {
        if (empty($data['type'])) {
            throw new Exception('Missing cart adjustment type');
        }

        /** @var LimitsLoader $limitsLoader */
        $limitsLoader = Factory::get("Core_Rule_Limit_LimitsLoader");
        $limit = $limitsLoader->create($data['type']);

        if ($limit instanceof MaxUsageLimit) {
            $data['options'] = array(
                $limit::MAX_USAGE_KEY => $data['options'],
            );
        }

        return $data;
    }

    public static function convertLimitToArray($limit)
    {
        $result         = array();
        $result['type'] = $limit->getType();

        if ($limit instanceof MaxUsageLimit) {
            $result['options'] = $limit->getMaxUsage();
        }

        return $result;
    }
}
