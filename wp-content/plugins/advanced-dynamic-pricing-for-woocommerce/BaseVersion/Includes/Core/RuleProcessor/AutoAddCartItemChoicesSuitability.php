<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAddCartItemChoices;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock\ProductStockController;
use ADP\BaseVersion\Includes\Enums\AutoAddChoiceMethodEnum;
use ADP\BaseVersion\Includes\Enums\AutoAddChoiceTypeEnum;

defined('ABSPATH') or exit;

class AutoAddCartItemChoicesSuitability
{
    /**
     * @param AutoAddCartItemChoices $cartItemChoices
     *
     * @return array<int,int>
     */
    protected function getMatchedProductsIds($cartItemChoices)
    {
        $includeIds = array();

        foreach ($cartItemChoices->getChoices() as $choice) {
            if ($choice->getType()->equals(AutoAddChoiceTypeEnum::PRODUCT())) {
                if ($choice->getMethod()->equals(AutoAddChoiceMethodEnum::IN_LIST())) {
                    $includeIds = array_merge($includeIds, $choice->getValues());
                }
            }
        }

        return $includeIds;
    }

    /**
     * @param AutoAddCartItemChoices $cartItemChoices
     * @param ProductStockController $ruleUsedStock
     * @param float $autoAddedCount
     *
     * @return array
     */
    public function getProductsSuitableToAutoAdd(
        $cartItemChoices,
        $ruleUsedStock,
        $autoAddedCount
    ) {
        $result = array();

        $productIds = $this->getMatchedProductsIds($cartItemChoices);
        $products   = array_values(
            array_filter(
                array_map(array("ADP\BaseVersion\Includes\Cache\CacheHelper", "getWcProduct"), $productIds)
            )
        );

        if ( count($products) === 0 ) {
            return array();
        }

        $giftQty = $cartItemChoices->getRequiredQty();

        while ($giftQty > 0 && count($products) > 0) {
            $currentIndex = $autoAddedCount % count($products);
            $currentProduct = $products[$currentIndex];
            $qtyToAdd = $ruleUsedStock->getQtyAvailableForSale($currentProduct->get_id(), 1, $currentProduct->get_parent_id());

            if ($qtyToAdd === (float)0) {
                unset($products[$currentIndex]);
                continue;
            }

            if ( isset($result[md5($currentProduct->get_id())]) ) {
                $result[md5($currentProduct->get_id())][1] += $qtyToAdd;
            } else {
                $result[md5($currentProduct->get_id())] = array($currentProduct->get_id(), $qtyToAdd);
            }
            $giftQty  -= $qtyToAdd;
            $autoAddedCount++;
        }

        return $result;
    }

    /**
     * @param AutoAddCartItemChoices $cartItemChoices
     * @param array $queryArgs
     *
     * @return array
     */
    public function getMatchedProductsGlobalQueryArgs($cartItemChoices, $queryArgs)
    {
        $includeIds = array();
        $excludeIds = array();

        foreach ($cartItemChoices->getChoices() as $choice) {
            if ($choice->getType()->equals(AutoAddChoiceTypeEnum::PRODUCT())) {
                if ($choice->getMethod()->equals(AutoAddChoiceMethodEnum::IN_LIST())) {
                    $includeIds = array_merge($includeIds, $choice->getValues());
                } elseif ($choice->getMethod()->equals(AutoAddChoiceMethodEnum::NOT_IN_LIST())) {
                    $excludeIds = array_merge($excludeIds, $choice->getValues());
                }
            }
        }

        $queryArgs['include'] = $includeIds;

        return $queryArgs;
    }

    /**
     * @param AutoAddCartItemChoices $cartItemChoices
     * @param \WC_Product $product
     *
     * @return bool
     */
    public function isProductMatched($cartItemChoices, $product)
    {
        if (count($cartItemChoices->getChoices()) === 0) {
            return false;
        }

        if ($product instanceof \WC_Product_Grouped) {
            return false;
        }

        $result = true;
        foreach ($cartItemChoices->getChoices() as $choice) {
            $choiceMatch = false;

            if ($choice->getType()->equals(AutoAddChoiceTypeEnum::PRODUCT())) {
                if ($choice->getMethod()->equals(AutoAddChoiceMethodEnum::IN_LIST())) {
                    $choiceMatch = in_array($product->get_id(), $choice->getValues(), true);
                }
            }

            $result &= $choiceMatch;
        }

        return $result;
    }
}
