<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Internationalization;

use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\FeeCartAdj;
use ADP\BaseVersion\Includes\Enums\GiftChoiceTypeEnum;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\NoItemRule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ProductsAdjustmentSplit;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ProductsAdjustmentTotal;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ProductsAdjustment;

defined('ABSPATH') or exit;

class RuleTranslator
{
    /**
     * @param SingleItemRule|PackageRule|NoItemRule $rule
     * @param float $rate
     *
     * @return NoItemRule|PackageRule|SingleItemRule
     */
    public static function setCurrency($rule, $rate)
    {
        if ($rule->hasProductAdjustment()) {
            $productAdj = $rule->getProductAdjustmentHandler();
            if ($productAdj instanceof ProductsAdjustment or
                $productAdj instanceof ProductsAdjustmentTotal) {
                if ($productAdj->isMaxAvailableAmountExists()) {
                    $productAdj->setMaxAvailableAmount($productAdj->getMaxAvailableAmount() * $rate);
                }
                $discount = $productAdj->getDiscount();
                if ($discount->getType() !== Discount::TYPE_PERCENTAGE) {
                    $discount->setValue($discount->getValue() * $rate);
                }
                $productAdj->setDiscount($discount);
            } elseif ($productAdj instanceof ProductsAdjustmentSplit) {
                $discounts = $productAdj->getDiscounts();
                foreach ($discounts as $discount) {
                    if ($discount->getType() !== Discount::TYPE_PERCENTAGE) {
                        $discount->setValue($discount->getValue() * $rate);
                    }
                }
                $productAdj->setDiscounts($discounts);
            }

            $rule->installProductAdjustmentHandler($productAdj);
        }

        if ($rule->hasProductRangeAdjustment()) {
            $productAdj = $rule->getProductRangeAdjustmentHandler();
            $ranges     = $productAdj->getRanges();
            foreach ($ranges as &$range) {
                $discount = $range->getData();
                if ($discount->getType() !== Discount::TYPE_PERCENTAGE) {
                    $discount->setValue($discount->getValue() * $rate);
                    $range->setData($discount);
                }
            }
            $productAdj->setRanges($ranges);

            $rule->installProductRangeAdjustmentHandler($productAdj);
        }

        $roleDiscounts = array();
        if ($rule->getRoleDiscounts() !== null) {
            foreach ($rule->getRoleDiscounts() as $roleDiscount) {
                $discount = $roleDiscount->getDiscount();
                if ($discount->getType() !== Discount::TYPE_PERCENTAGE) {
                    $discount->setValue($discount->getValue() * $rate);
                }
                $roleDiscount->setDiscount($discount);
                $roleDiscounts[] = $roleDiscount;
            }
            $rule->setRoleDiscounts($roleDiscounts);
        }

        if ($rule->getCartAdjustments()) {
            $cartAdjs = $rule->getCartAdjustments();
            foreach ($cartAdjs as $cartAdjustment) {
                $cartAdjustment->multiplyAmounts($rate);
            }
            $rule->setCartAdjustments($cartAdjs);
        }

        if ($rule->getConditions()) {
            $cartConditions = $rule->getConditions();
            foreach ($cartConditions as $cart_condition) {
                $cart_condition->multiplyAmounts($rate);
            }
            $rule->setConditions($cartConditions);
        }

        if ($rule instanceof SingleItemRule || $rule instanceof PackageRule) {
            $rule->setItemGiftSubtotalDivider($rule->getItemGiftSubtotalDivider() * $rate);
        }

        return $rule;
    }

    public static function translate(Rule $rule, IObjectInternationalization $oi) : Rule
    {
        $filterTranslator = new FilterTranslator();

        if ($rule instanceof SingleItemRule) {
            $filters = [];
            foreach ($rule->getFilters() as $filter) {
                $filter->setValue($filterTranslator->translateByType($filter->getType(), $filter->getValue(), $oi));

                if ( $filter->getExcludeProductIds() ) {
                    $filter->setExcludeProductIds(
                        array_map([$oi, 'translateProductId'], $filter->getExcludeProductIds())
                    );
                }

                $filters[] = $filter;
            }
            $rule->setFilters($filters);
        } elseif ($rule instanceof PackageRule) {
            $packages = [];
            foreach ($rule->getPackages() as $package) {
                $filters = [];
                foreach ($package->getFilters() as $filter) {
                    $filter->setValue($filterTranslator->translateByType($filter->getType(), $filter->getValue(), $oi));

                    if ( $filter->getExcludeProductIds() ) {
                        $filter->setExcludeProductIds(
                            array_map([$oi, 'translateProductId'], $filter->getExcludeProductIds())
                        );
                    }

                    $filters[] = $filter;
                }
                $package->setFilters($filters);
                $packages[] = $package;
            }
            $rule->setPackages($packages);
        }

        if ($rule instanceof SingleItemRule || $rule instanceof PackageRule) {
            if ($rule->hasProductRangeAdjustment()) {
                $productAdj = $rule->getProductRangeAdjustmentHandler();

                $productAdj->setSelectedProductIds(
                    array_map([$oi, 'translateProductId'], $productAdj->getSelectedProductIds())
                );

                $productAdj->setSelectedCategoryIds(
                    array_map([$oi, 'translateCategoryId'], $productAdj->getSelectedCategoryIds())
                );

                $rule->installProductRangeAdjustmentHandler($productAdj);
            }

            foreach ($rule->getItemGiftsCollection()->asArray() as $gift) {
                foreach ($gift->getChoices() as $choice) {
                    if ($choice->getType()->equals(GiftChoiceTypeEnum::PRODUCT())) {
                        $choice->setValues(
                            array_map([$oi, 'translateProductId'], $choice->getValues())
                        );
                    }

                    if ($choice->getType()->equals(GiftChoiceTypeEnum::CATEGORY())) {
                        $choice->setValues(
                            array_map([$oi, 'translateCategoryId'], $choice->getValues())
                        );
                    }
                }
            }
        }

        $cartConditions = [];
        foreach ($rule->getConditions() as $cartCondition) {
            $cartCondition->translate($oi);
            $cartConditions[] = $cartCondition;
        }
        $rule->setConditions($cartConditions);

        $cartAdjustments = [];
        foreach ($rule->getCartAdjustments() as $cartAdjustment) {
            if ($cartAdjustment instanceof FeeCartAdj) {
                $cartAdjustment->translate();
            }

            $cartAdjustments[] = $cartAdjustment;
        }
        $rule->setCartAdjustments($cartAdjustments);

        return $rule;
    }
}
