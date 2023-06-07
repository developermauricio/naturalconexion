<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Core\Cart\AutoAddCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAdd;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAddCartItemChoices;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock\ProductStockController;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSet;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSetCollection;
use ADP\BaseVersion\Includes\Enums\AutoAddChoiceMethodEnum;
use ADP\BaseVersion\Includes\Enums\AutoAddChoiceTypeEnum;
use ADP\Factory;

defined('ABSPATH') or exit;

class AutoAddStrategy
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var ProductStockController
     */
    protected $ruleUsedStock;

    /**
     * @var AutoAddCartItemChoicesSuitability
     */
    protected $autoAddCartItemChoicesSuitability;

    /**
     * @param Rule $rule
     * @param ProductStockController $ruleUsedStock
     */
    public function __construct($rule, $ruleUsedStock)
    {
        $this->rule = $rule;
        $this->ruleUsedStock = $ruleUsedStock;

        /** @var FreeCartItemChoicesSuitability $freeCartItemChoicesSuitability */
        $this->autoAddCartItemChoicesSuitability = Factory::get("Core_RuleProcessor_AutoAddCartItemChoicesSuitability");
    }

    public function canAutoAdd()
    {
        return method_exists($this->rule, 'getAutoAdds') && boolval($this->rule->getAutoAdds());
    }

    public function canItemAutoAdds()
    {
        return method_exists($this->rule, 'getAutoAddsCollection')
            && boolval($this->rule->getAutoAddsCollection()->asArray());
    }

    /**
     * TODO implement!
     * Not requires without frontend implementation
     *
     * @param Cart $cart
     */
    public function addAutoAdds(&$cart)
    {
        $rule = $this->rule;
        $gifts = $rule->getAutoAdds();
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    public function addCartItemAutoAdds(&$cart, $collection)
    {
        /** @var SingleItemRule $rule */
        $rule = $this->rule;

        $totalQty = floatval(0);

        /**
         * @var array $itemIndexes
         * Cheap solution to fetch product with which we add free product.
         * Needed for gifting products from collection.
         *
         * ItemIndexes is a list with items and indexes. Index in nutshell is number of attempt at which we begin to use item.
         * For example:
         *  We have 'single item' rule with collection 3 apple and 2 bananas and 1 orange.
         *  So, itemIndexes will be
         *      array(
         *          array( 'index' => 1, 'item' => apple ),
         *          array( 'index' => 4 (1 + qty of apple), 'item' => banana ),
         *          array( 'index' => 6 (1 + qty of apple + qty of bananas ), 'item' => orange ),
         *      )
         *  When we try to iterate on attempts, we get an item
         *  Attempt count | item
         *  1   | apple
         *  2   | apple
         *  3   | apple
         *  4   | banana
         *  5   | banana
         *  6   | orange
         */
        $itemIndexes = array();
        foreach ($collection->get_items() as $item) {
            $itemIndexes[] = array(
                'index' => $totalQty + 1,
                'item' => $item,
            );
            $totalQty += $item->getQty();
        }

        $attemptCount = 0;
        if ($rule->getAutoAddStrategy() === $rule::BASED_ON_LIMIT_STRATEGY) {
            $attemptCount = min($totalQty, $rule->getAutoAddLimit());
        } elseif (
            $rule->getAutoAddStrategy() === $rule::BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY
            || $rule->getAutoAddStrategy() === $rule::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY
        ) {
            if ($rule->getAutoAddSubtotalDivider()) {
                $inclTax = $rule->getAutoAddStrategy() === $rule::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY;
                $tmpCart = clone $cart;
                foreach ($collection->get_items() as $item) {
                    $tmpCart->addToCart($item);
                }
                $itemsSubtotals = (new CartTotals($tmpCart))->getSubtotal($inclTax);
                $attemptCount = intval($itemsSubtotals / $rule->getAutoAddSubtotalDivider());
            }
        }

        /** @var array<int, array<int,AutoAddCartItem>> $autoAddCartItemsByHash */
        $autoAddCartItemsByHash = array();

        $index = 0;
        while ($index < $attemptCount) {
            $index++;
            $item = null;
            foreach ($itemIndexes as $key => $data) {
                if ($data['index'] <= $index) {
                    $item = $data['item'];
                }
            }

            if (!$item) {
                continue;
            }

            $giftIndex = 0;
            /** @var CartItem $item */
            foreach ($rule->getAutoAddsCollection()->asArray() as $autoAdd) {
                if ($autoAdd->getQty() <= 0) {
                    continue;
                }

                $autoAddCartItemChoices = $this->convertAutoAddToAutoAddCartItemChoices($autoAdd, array($item));
                $hash = $autoAddCartItemChoices->generateHash($rule, $giftIndex, $autoAdd);

                $this->processAutoAddItem($autoAddCartItemsByHash, $hash, $cart, $autoAddCartItemChoices);
                $autoAddCartItemChoices[$hash]['auto_add'] = $autoAdd;

                $giftIndex++;
            }
        }

        $customer = $cart->getContext()->getCustomer();

        foreach ($autoAddCartItemsByHash as $hash => $autoAddArray) {
            $removedAutoAddItems = $customer->getRemovedAutoAddItems($hash);
            /**
             * @var $autoAdd AutoAdd
             */
            $autoAdd = $autoAddArray['auto_add'];
            $discount = $autoAdd->getDiscount();

            $productAdjustment = new SingleItemRule\ProductsAdjustment($discount);
            /** @var PriceCalculator $priceCalculator */
            $priceCalculator = Factory::get(
                "Core_RuleProcessor_PriceCalculator",
                $this->rule, $productAdjustment->getDiscount()
            );

            /** @var $autoAddCartItem AutoAddCartItem */
            foreach ($autoAddArray['auto_add_cart_items'] as $autoAddCartItem) {
                $deletedQty = $removedAutoAddItems->get($autoAddCartItem->hash());

                if($autoAddCartItem->getQty() <= $deletedQty) {
                    $deletedQty = $autoAddCartItem->getQty();
                    $autoAddCartItem->setQty(0);
                } else {
                    $autoAddCartItem->setQty($autoAddCartItem->getQty() - $deletedQty);
                }

                $removedAutoAddItems->set($autoAddCartItem->hash(), $deletedQty);

                $newPrice = $priceCalculator->calculateSinglePrice($autoAddCartItem->getPrice());
                $autoAddCartItem->setPrice($newPrice);
                $cart->addToCart($autoAddCartItem);
            }
        }
    }

    /**
     * @param AutoAdd $autoAdd
     * @param array<int,CartItem> $items
     *
     * @return AutoAddCartItemChoices
     */
    protected function convertAutoAddToAutoAddCartItemChoices($autoAdd, $items)
    {
        $newAutoAddChoices = array();
        foreach ($autoAdd->getChoices() as $autoAddChoice) {
            if ($autoAddChoice->getType()->getValue() === AutoAddChoiceTypeEnum::CLONE_ADJUSTED) {
                $newAutoAddChoice = clone $autoAddChoice;
                $newAutoAddChoice->setType(new AutoAddChoiceTypeEnum(AutoAddChoiceTypeEnum::PRODUCT));

                $values = array();
                foreach ($items as $item) {
                    $values[] = $item->getWcItem()->getProduct()->get_id();
                }
                $newAutoAddChoice->setValues($values);

                $newAutoAddChoice->setMethod(new AutoAddChoiceMethodEnum(AutoAddChoiceMethodEnum::IN_LIST));
                $newAutoAddChoices = array($newAutoAddChoice);
                break;
            }

            $newAutoAddChoices[] = $autoAddChoice;
        }

        $autoAddCartItemChoices = new AutoAddCartItemChoices();
        $autoAddCartItemChoices->setChoices($newAutoAddChoices);
        $autoAddCartItemChoices->setRequiredQty($autoAdd->getQty());

        return $autoAddCartItemChoices;
    }

    /**
     * @param Cart $cart
     * @param CartSetCollection $collection
     */
    public function addCartSetAutoAdds(&$cart, $collection)
    {
        /** @var PackageRule $rule */
        $rule = $this->rule;

        $totalQty = floatval(0);

        $setIndexes = array();
        foreach ($collection->getSets() as $set) {
            $setIndexes[] = array(
                'index' => $totalQty + 1,
                'set' => $set,
            );
            $totalQty += $set->getQty();
        }

        $attemptCount = 0;
        if ($rule->getAutoAddStrategy() === $rule::BASED_ON_LIMIT_STRATEGY) {
            $attemptCount = min($totalQty, $rule->getItemGiftLimit());
        } elseif (
            $rule->getAutoAddStrategy() === $rule::BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY
            || $rule->getAutoAddStrategy() === $rule::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY
        ) {
            if ($rule->getAutoAddSubtotalDivider()) {
                $inclTax = $rule->getAutoAddStrategy() === $rule::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY;
                $tmpCart = clone $cart;
                foreach ($collection->getSets() as $set) {
                    foreach ($set->getItems() as $item) {
                        $tmpCart->addToCart($item);
                    }
                }
                $itemsSubtotals = (new CartTotals($tmpCart))->getSubtotal($inclTax);
                $attemptCount = intval($itemsSubtotals / $rule->getAutoAddSubtotalDivider());
            }
        }

        /** @var AutoAddCartItem[][] $autoAddCartItemsByHash */
        $autoAddCartItemsByHash = array();

        $index = 0;
        while ($index < $attemptCount) {
            $index++;
            $set = null;
            foreach ($setIndexes as $key => $data) {
                if ($data['index'] <= $index) {
                    $set = $data['set'];
                }
            }

            if (!$set) {
                continue;
            }

            $giftIndex = 0;
            /** @var CartSet $set */
            foreach ($rule->getAutoAddsCollection()->asArray() as $autoAdd) {
                if ($autoAdd->getQty() <= 0) {
                    continue;
                }

                $autoAddCartItemChoices = $this->convertAutoAddToAutoAddCartItemChoices($autoAdd, $set->getItems());
                $hash = $autoAddCartItemChoices->generateHash($rule, $giftIndex, $autoAdd);

                $this->processAutoAddItem($autoAddCartItemsByHash, $hash, $cart, $autoAddCartItemChoices);
                $autoAddCartItemChoices[$hash]['auto_add'] = $autoAdd;

                $giftIndex++;
            }
        }

        $customer = $cart->getContext()->getCustomer();

        foreach ($autoAddCartItemsByHash as $hash => $autoAddArray) {
            $removedAutoAddItems = $customer->getRemovedAutoAddItems($hash);
            /**
             * @var $autoAdd AutoAdd
             */
            $autoAdd = $autoAddArray['auto_add'];
            $discount = $autoAdd->getDiscount();

            $productAdjustment = new SingleItemRule\ProductsAdjustment($discount);
            /** @var PriceCalculator $priceCalculator */
            $priceCalculator = Factory::get(
                "Core_RuleProcessor_PriceCalculator",
                $this->rule, $productAdjustment->getDiscount()
            );

            /** @var $autoAddCartItem AutoAddCartItem */
            foreach ($autoAddArray['auto_add_cart_items'] as $autoAddCartItem) {
                $deletedQty = $removedAutoAddItems->get($autoAddCartItem->hash());

                if($autoAddCartItem->getQty() <= $deletedQty) {
                    $deletedQty = $autoAddCartItem->getQty();
                    $autoAddCartItem->setQty(0);
                } else {
                    $autoAddCartItem->setQty($autoAddCartItem->getQty() - $deletedQty);
                }

                $removedAutoAddItems->set($autoAddCartItem->hash(), $deletedQty);

                $newPrice = $priceCalculator->calculateSinglePrice($autoAddCartItem->getPrice());
                $autoAddCartItem->setPrice($newPrice);
                $cart->addToCart($autoAddCartItem);
            }
        }
    }

    /**
     * @param Cart $cart
     * @param int $productId
     * @param float $qty
     * @param string $associatedHash
     * @param array $variation
     * @param array $cartItemData
     *
     * @return AutoAddCartItem|false
     */
    protected function prepareAutoAddCartItem(
        $cart,
        $productId,
        $qty,
        $associatedHash,
        $variation = array(),
        $cartItemData = array()
    )
    {
        if (!($cart instanceof Cart && is_numeric($productId) && is_numeric($qty))) {
            return false;
        }

        if (!$this->canItemAutoAdds()) {
            return false;
        }

        /** @var SingleItemRule|PackageRule $rule */
        $rule = $this->rule;

        $productId = intval($productId);
        $product = CacheHelper::getWcProduct($productId);
        $qty = floatval($qty);

        if ($qty < floatval(0)) {
            return false;
        }

        if ($qty === floatval(0)) {
            return false;
        }

        $isReplace   = $rule->isReplaceAutoAdds();
        $replaceCode = $rule->getReplaceAutoAddsCode();

        $canBeRemoved = !$rule->getAutoAddRemoveDisable();

        try {
            $autoAddItem = new AutoAddCartItem($product, $qty, $this->rule->getId(), $associatedHash);
        } catch (\Exception $e) {
            return false;
        }

        if (count($variation) > 0) {
            $autoAddItem->setVariation($variation);
        }

        $autoAddItem->setCartItemData($cartItemData);

        if ($isReplace && $replaceCode) {
            $autoAddItem->setReplaceWithCoupon($isReplace);
            $autoAddItem->setReplaceCouponCode($replaceCode);
        }

        $autoAddItem->setCanBeRemoved($canBeRemoved);
        $autoAddItem->setIsRecommended($rule->getAutoAddShowAsRecommended());

        return $autoAddItem;
    }

    /**
     * @param array<int, array<int,AutoAddCartItem>> $autoAddCartItemsByHash
     * @param string $hash
     * @param Cart $cart
     * @param AutoAddCartItemChoices $autoAddCartItemChoices
     * @param float $autoAddedCount
     */
    protected function processAutoAddItem(
        &$autoAddCartItemsByHash,
        $hash,
        $cart,
        $autoAddCartItemChoices,
        $autoAddedCount = 0.0
    )
    {
        $readyList = $this->autoAddCartItemChoicesSuitability->getProductsSuitableToAutoAdd($autoAddCartItemChoices,
            $this->ruleUsedStock, $autoAddedCount);

        if (!isset($autoAddCartItemsByHash[$hash]['auto_add_cart_items'])) {
            $autoAddCartItemsByHash[$hash]['auto_add_cart_items'] = array();
        }

        /** @var AutoAddCartItem[] $autoAddCartItems */
        $autoAddCartItems = $autoAddCartItemsByHash[$hash]['auto_add_cart_items'];

        foreach ($readyList as $value) {
            list($productId, $qty) = $value;

            if (!($autoAddCartItem = $this->prepareAutoAddCartItem($cart, $productId, $qty, $hash))) {
                continue;
            }
            $calculatedHash = $autoAddCartItem->hash();

            if (isset($autoAddCartItems[$calculatedHash])) {
                $autoAddCartItems[$calculatedHash]->setQty(
                    $autoAddCartItems[$calculatedHash]->getQty() + $autoAddCartItem->getQty()
                );
            } else {
                $autoAddCartItems[$calculatedHash] = clone $autoAddCartItem;
            }

            $this->ruleUsedStock->add(
                $autoAddCartItem->getProduct()->get_id(),
                $autoAddCartItem->getQty(),
                $autoAddCartItem->getProduct()->get_parent_id(),
                $autoAddCartItem->getVariation(),
                $autoAddCartItem->getCartItemData()
            );
        }

        $autoAddCartItemsByHash[$hash]['auto_add_cart_items'] = $autoAddCartItems;
    }
}
