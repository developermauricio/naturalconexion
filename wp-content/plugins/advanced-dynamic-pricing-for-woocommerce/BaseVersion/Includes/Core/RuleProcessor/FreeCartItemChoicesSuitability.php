<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Structures\FreeCartItemChoices;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock\ProductStockController;
use ADP\BaseVersion\Includes\Enums\GiftChoiceMethodEnum;
use ADP\BaseVersion\Includes\Enums\GiftChoiceTypeEnum;
use ADP\BaseVersion\Includes\WC\WcCustomerConverter;
use ADP\BaseVersion\Includes\WC\WcTaxFunctions;

defined('ABSPATH') or exit;

class FreeCartItemChoicesSuitability
{
    /**
     * @var WcTaxFunctions
     */
    protected $wcTaxFunctions;

    public function __construct() {
        $this->wcTaxFunctions = new WcTaxFunctions();
    }

    /**
     * @param FreeCartItemChoices $cartItemChoices
     *
     * @return array<int,int>
     */
    protected function getMatchedProductsIds($cartItemChoices)
    {
        $includeIds = array();

        foreach ($cartItemChoices->getChoices() as $choice) {
            if ($choice->getType()->equals(GiftChoiceTypeEnum::PRODUCT())) {
                if ($choice->getMethod()->equals(GiftChoiceMethodEnum::IN_LIST())) {
                    $includeIds = array_merge($includeIds, $choice->getValues());
                }
            }
        }

        return $includeIds;
    }

    /**
     * @param FreeCartItemChoices $cartItemChoices
     * @param ProductStockController $ruleUsedStock
     * @param float $giftedCount
     * @param Cart $cart
     * @param float|null $maxAmountForGiftsLeft
     *
     * @return array
     */
    public function getProductsSuitableToGift(
        $cartItemChoices,
        $ruleUsedStock,
        $giftedCount,
        $cart,
        &$maxAmountForGiftsLeft = null
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

        $giftQty           = $cartItemChoices->getRequiredQty();
        $initialProductQty = count($products);

        while ($giftQty > 0 && count($products) > 0) {
            $currentIndex = $giftedCount % $initialProductQty;

            if ( ! isset($products[$currentIndex])) {
                $giftedCount++;
                continue;
            }

            $currentProduct = $products[$currentIndex];
            $qtyToAdd = $ruleUsedStock->getQtyAvailableForSale($currentProduct->get_id(), 1, $currentProduct->get_parent_id());

            if ( ! isset($result[md5($currentProduct->get_id())]) ) {
                $result[md5($currentProduct->get_id())] = array($currentProduct->get_id(), 0, false, false);
            }

            $productBaseSubtotal = $this->getProductBaseSubtotal($cart, $currentProduct, $qtyToAdd);

            if ( $maxAmountForGiftsLeft !== null && $productBaseSubtotal > $maxAmountForGiftsLeft ) {
                $result[md5($currentProduct->get_id())][3] = true;
                unset($products[$currentIndex]);
                continue;
            }

            if ($qtyToAdd === (float)0) {
                $result[md5($currentProduct->get_id())][2] = true;
                unset($products[$currentIndex]);
                $giftedCount++;
                continue;
            }

            $result[md5($currentProduct->get_id())][1] += $qtyToAdd;
            $giftQty  -= $qtyToAdd;
            $giftedCount++;

            if ( $maxAmountForGiftsLeft !== null ) {
                $maxAmountForGiftsLeft -= $productBaseSubtotal;
            }
        }

        return $result;
    }

    /**
     * @param \ADP\BaseVersion\Includes\Core\Cart\Cart $cart
     * @param \WC_Product $product
     * @param float $qtyToAdd
     * @return float
     */
    protected function getProductBaseSubtotal($cart, $product, $qtyToAdd)
    {
        $context = $cart->getContext()->getGlobalContext();
        if (!$context->getIsTaxEnabled() || $context->getIsPricesIncludeTax()) {
            return (float)$product->get_price('edit');
        }

        $wcCustomer = (new WcCustomerConverter())->convertToWcCustomer($cart->getContext()->getCustomer());

        return $this->wcTaxFunctions->getBaseProductPrice($product, $wcCustomer) * $qtyToAdd;
    }

    /**
     * @param FreeCartItemChoices $cartItemChoices
     * @param array $queryArgs
     *
     * @return array
     */
    public function getMatchedProductsGlobalQueryArgs($cartItemChoices, $queryArgs)
    {
        $includeIds = array();
        $excludeIds = array();

        foreach ($cartItemChoices->getChoices() as $choice) {
            if ($choice->getType()->equals(GiftChoiceTypeEnum::PRODUCT())) {
                if ($choice->getMethod()->equals(GiftChoiceMethodEnum::IN_LIST())) {
                    $includeIds = array_merge($includeIds, $choice->getValues());
                } elseif ($choice->getMethod()->equals(GiftChoiceMethodEnum::NOT_IN_LIST())) {
                    $excludeIds = array_merge($excludeIds, $choice->getValues());
                }
            }
        }

        $queryArgs['include'] = $includeIds;

        return $queryArgs;
    }

    /**
     * @param FreeCartItemChoices $cartItemChoices
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

            if ($choice->getType()->equals(GiftChoiceTypeEnum::PRODUCT())) {
                if ($choice->getMethod()->equals(GiftChoiceMethodEnum::IN_LIST())) {
                    $choiceMatch = in_array($product->get_id(), $choice->getValues(), true);
                }
            }

            $result &= $choiceMatch;
        }

        return $result;
    }
}
