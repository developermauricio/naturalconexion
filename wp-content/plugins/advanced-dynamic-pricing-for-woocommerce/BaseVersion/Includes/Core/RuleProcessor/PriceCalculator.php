<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Fee;
use ADP\BaseVersion\Includes\Core\Cart\ItemDiscount;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\PackageRangeAdjustments;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ProductsAdjustmentSplit;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ProductsAdjustmentTotal;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ProductsAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ProductsRangeAdjustments;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RoleDiscount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\SetDiscount;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSet;
use ADP\BaseVersion\Includes\SpecialStrategies\CompareStrategy;

defined('ABSPATH') or exit;

class PriceCalculator
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var Discount
     */
    protected $discount;

    /**
     * @var float
     */
    protected $discountTotalLimit;

    /**
     * @param Rule $rule
     * @param Discount $discount
     * @param float|null $discountTotalLimit
     */
    public function __construct($rule, $discount, $discountTotalLimit = null)
    {
        $this->rule               = $rule;
        $this->discount           = $discount;
        $this->discountTotalLimit = $discountTotalLimit;
    }

    /**
     * @param CartItemsCollection $collection
     * @param Context $globalContext
     */
    public function applyToAllItemsInCollection(&$collection, $globalContext)
    {
        foreach ($collection->get_items() as &$item) {
            $this->applyToItem($item, $globalContext);
        }
    }

    /**
     * @param CartItem $item
     * @param Context $globalContext
     */
    public function applyToItem(&$item, $globalContext)
    {
        $price = $this->calculateSinglePrice($item->getPrice());

        if ($item->getAddonsAmount() > 0) {
            if (Discount::TYPE_FIXED_VALUE === $this->discount->getType()) {
                $price += $item->getAddonsAmount();
            }
        } else {
            if ($globalContext->isToCompensateTrdPartAdjustmentForFixedPrice()) {
                if (Discount::TYPE_FIXED_VALUE === $this->discount->getType()) {
                    $price += $item->trdPartyPriceAdj;
                }
            }
        }

        $item->setPrice($this->rule->getId(), $price);
    }

    /**
     * @param CartItem $item
     * @param Cart $cart
     *
     * @return float|null
     */
    public function calculatePrice($item, $cart)
    {
        $globalContext = $cart->getContext()->getGlobalContext();
        $discount      = $this->discount;

        if ($discount instanceof SetDiscount) {
            return null;
        }

        if ($globalContext->getOption('apply_discount_to_original_price') && Discount::TYPE_PERCENTAGE === $discount->getType()) {
            $price = $item->getOriginalPrice();
        } else {
            $price = $item->getPrice();
        }

        $newPrice = $this->calculateSinglePrice($price);

        if ($item->getAddonsAmount() > 0) {
            if ($discount::TYPE_FIXED_VALUE === $discount->getType()) {
                $newPrice += $item->getAddonsAmount();
            }
        } else {
            if ($globalContext->isToCompensateTrdPartAdjustmentForFixedPrice()) {
                if ($discount::TYPE_FIXED_VALUE === $discount->getType()) {
                    $newPrice += $item->trdPartyPriceAdj;
                }
            }
        }

        return $newPrice;
    }

    /**
     * @param CartItem $item
     * @param Cart $cart
     * @param ProductsAdjustment|ProductsRangeAdjustments|ProductsAdjustmentTotal|ProductsAdjustmentSplit|PackageRangeAdjustments|RoleDiscount $handler
     */
    public function applyItemDiscount(&$item, &$cart, $handler)
    {
        $globalContext = $cart->getContext()->getGlobalContext();
        $discount = $this->discount;
        $settings = $globalContext->getSettings();
        $compatibilitySettings = $globalContext->getCompatibilitySettings();

        if ($discount instanceof SetDiscount) {
            return;
        }

        $flags = array();

        if ($globalContext->getOption('apply_discount_to_original_price') && Discount::TYPE_PERCENTAGE === $discount->getType()) {
            $price   = $item->getOriginalPrice();
            $flags[] = CartItem::FLAG_DISCOUNT_ORIGINAL;
        } else {
            $price = $item->getPrice();
        }

        $dontApplyDiscountToAddons = $compatibilitySettings->getOption('dont_apply_discount_to_addons');
        $addonsAmount = $item->getAddonsAmount();
        if ($dontApplyDiscountToAddons) {
            $itemWithoutAddonsAmount = $price - $item->getAddonsAmount();
        }

        $newPrice = $this->calculateSinglePrice($itemWithoutAddonsAmount ?? $price);
        if ($dontApplyDiscountToAddons) {
            $newPrice += $addonsAmount;
        }

        if ($item->getAddonsAmount() > 0) {
            if ($discount::TYPE_FIXED_VALUE === $discount->getType()) {
                $newPrice += $item->getAddonsAmount();
            }
        } else {
            if ($globalContext->isToCompensateTrdPartAdjustmentForFixedPrice()) {
                if ($discount::TYPE_FIXED_VALUE === $discount->getType()) {
                    $newPrice += $item->trdPartyPriceAdj;
                }
            }
        }
        $amount = ($price - $newPrice) * $item->getQty();

        if ($handler->isReplaceWithCartAdjustment()) {
            $flags[]        = CartItem::FLAG_IGNORE;
            $adjustmentCode = $handler->getReplaceCartAdjustmentCode();

            if ($amount > 0) {
                $coupon = new CouponCartItem(
                    $globalContext,
                    CouponCartItem::TYPE_ITEM_DISCOUNT,
                    $adjustmentCode,
                    $amount / $item->getQty(),
                    $this->rule->getId(),
                    $item->getWcItem()
                );
                $coupon->setAffectedCartItemQty($item->getQty());
                $cart->addCoupon($coupon);
            } elseif ($amount < 0) {
                $taxClass = $globalContext->getIsPricesIncludeTax() ? "" : "standard";
                $cart->addFee(
                    new Fee(
                        $globalContext,
                        Fee::TYPE_ITEM_OVERPRICE,
                        $adjustmentCode,
                        (-1) * $amount,
                        $taxClass,
                        $this->rule->getId()
                    )
                );
            }
        } elseif ($globalContext->getOption('item_adjustments_as_coupon', false)
                  && $globalContext->getOption('item_adjustments_coupon_name', false)
        ) {
            $flags[]        = CartItem::FLAG_IGNORE;
            $adjustmentCode = $globalContext->getOption('item_adjustments_coupon_name');

            if ($amount > 0) {
                $coupon = new CouponCartItem(
                    $globalContext,
                    CouponCartItem::TYPE_ITEM_DISCOUNT,
                    $adjustmentCode,
                    $amount / $item->getQty(),
                    $this->rule->getId(),
                    $item->getWcItem()
                );
                $coupon->setAffectedCartItemQty($item->getQty());
                $cart->addCoupon($coupon);
            } elseif ($amount < 0) {
                $taxClass = $globalContext->getIsPricesIncludeTax() ? "" : "standard";
                $cart->addFee(
                    new Fee(
                        $globalContext,
                        Fee::TYPE_ITEM_OVERPRICE,
                        $adjustmentCode,
                        (-1) * $amount,
                        $taxClass,
                        $this->rule->getId()
                    )
                );
            }
        }

        $item->setPrice($this->rule->getId(), $newPrice, $flags);

        if ($handler instanceof ProductsAdjustment) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_SINGLE_ITEM_SIMPLE, $newPrice);
        } elseif ($handler instanceof ProductsRangeAdjustments) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_SINGLE_ITEM_RANGE, $newPrice);
        } elseif ($handler instanceof ProductsAdjustmentTotal) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_PACKAGE_SIMPLE, $newPrice);
        } elseif ($handler instanceof ProductsAdjustmentSplit) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_PACKAGE_SPLIT, $newPrice);
        } elseif ($handler instanceof PackageRangeAdjustments) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_PACKAGE_RANGE, $newPrice);
        } elseif ($handler instanceof RoleDiscount) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_ROLE, $newPrice);
        } else {
            return;
        }

        $discount->setRuleId($this->rule->getId());
        foreach ($flags as $flag) {
            $discount->addFlag($flag);
        }

        // todo add range

        $item->setPriceNew($discount);
    }

    /**
     * @param float $price
     *
     * @return float
     */
    public function calculateSinglePrice($price)
    {
        $old_price = floatval($price);

        $operationType  = $this->discount->getType();
        $operationValue = $this->discount->getValue();

        if (Discount::TYPE_FREE === $operationType) {
            $new_price = $this->makeFree();
        } elseif (Discount::TYPE_AMOUNT === $operationType) {
            if ($operationValue > 0) {
                $new_price = $this->makeDiscountAmount($price, $operationValue);
            } else {
                $new_price = $this->makeOverpriceAmount($price, (-1) * $operationValue);
            }
        } elseif (Discount::TYPE_PERCENTAGE === $operationType) {
            $new_price = $this->makeDiscountPercentage($old_price, $operationValue);
        } elseif (Discount::TYPE_FIXED_VALUE === $operationType) {
            $new_price = $this->makePriceFixed($old_price, $operationValue);
        } else {
            $new_price = $old_price;
        }

        return (float)$new_price;
    }

    /**
     * @param $listOfItems CartItem[]|CartSet|CartItemsCollection
     * @param $globalContext Context
     *
     * @return float
     */
    protected function calculateAdjustmentsLeft($listOfItems, $globalContext)
    {
        $discountType = $this->discount->getType();

        $items = array();
        if (is_array($listOfItems)) {
            foreach ($listOfItems as $item) {
                if ($item instanceof CartItem) {
                    $items[] = $item;
                }
            }
        } elseif ($listOfItems instanceof CartSet || $listOfItems instanceof CartItemsCollection) {
            $items = $listOfItems->getItems();
        }

        $price_total = 0.0;
        foreach ($items as $item) {
            $price_total += $item->getTotalPrice();
        }

        $third_party_adjustments = 0.0;
        foreach ($items as $item) {
            if ($item->getAddonsAmount() > 0) {
                $third_party_adjustments += $item->getAddonsAmount();
            } elseif ( $globalContext->isToCompensateTrdPartAdjustmentForFixedPrice() ) {
                $third_party_adjustments += $item->trdPartyPriceAdj;
            }
        }

        $adjustments_left = 0.0;
        if (Discount::TYPE_PERCENTAGE === $discountType) {
            foreach ($items as $item) {
                /**
                 * @var $item CartItem
                 */
                if ($item->hasAttr($item::ATTR_READONLY_PRICE)) {
                    continue;
                }
                $new_price        = $this->makeDiscountPercentage($item->getTotalPrice(), $this->discount->getValue());
                $adjustments_left += $item->getTotalPrice() - $new_price;
            }
        } elseif (Discount::TYPE_FIXED_VALUE === $discountType || Discount::TYPE_AMOUNT === $discountType) {
            if ( ! empty($price_total)) {
                if (Discount::TYPE_FIXED_VALUE === $discountType) {
                    $adjustments_left = $price_total - $this->discount->getValue() - $third_party_adjustments;
                } else {
                    $adjustments_left = $this->discount->getValue();
                }
            }
        }

        return $adjustments_left;
    }

    /**
     * @param float $adjustmentTotal
     *
     * @return float|null
     */
    protected function checkAdjustmentTotal($adjustmentTotal)
    {
        // check only for discount
        if ($this->discountTotalLimit === null || $adjustmentTotal < 0) {
            return $adjustmentTotal;
        }

        return $adjustmentTotal > $this->discountTotalLimit ? $this->discountTotalLimit : $adjustmentTotal;
    }

    /**
     * @param CartSet                                                                                                                          $set
     * @param Cart                                                                                                                             $cart
     * @param ProductsAdjustment|ProductsRangeAdjustments|ProductsAdjustmentTotal|ProductsAdjustmentSplit|PackageRangeAdjustments|RoleDiscount $handler
     *
     * @return CartSet
     */
    public function calculatePriceForSet($set, $cart, $handler)
    {
        return $this->calculatePriceForSetSplitDiscountByCost($set, $cart, $handler);
    }

    /**
     * @param CartSet                                                                                                                          $set
     * @param Cart                                                                                                                             $cart
     * @param ProductsAdjustment|ProductsRangeAdjustments|ProductsAdjustmentTotal|ProductsAdjustmentSplit|PackageRangeAdjustments|RoleDiscount $handler
     *
     * @return CartSet
     */
    public function calculatePriceForSetSplitDiscountByCost($set, $cart, $handler)
    {
        $globalContext = $cart->getContext()->getGlobalContext();

        $totalPrice = 0;
        foreach ($set->getItems() as $item) {
            /**
             * @var $item CartItem
             */
            if ( ! $item->hasAttr($item::ATTR_READONLY_PRICE)) {
                $totalPrice += $item->getTotalPrice();
            }
        }

        $totalQty = 0;
        foreach ($set->getItems() as $item) {
            /**
             * @var $item CartItem
             */
            if ( ! $item->hasAttr($item::ATTR_READONLY_PRICE)) {
                $totalQty += $item->getQty();
            }
        }

        $adjustmentsLeft = $this->checkAdjustmentTotal(
            $this->calculateAdjustmentsLeft(
                $set->getItems(),
                $globalContext
            )
        );

        $overprice       = $adjustmentsLeft < 0;
        $adjustmentsLeft = $overprice ? -$adjustmentsLeft : $adjustmentsLeft;
        $diff            = 0.0;

        if ($adjustmentsLeft > 0 && $totalPrice > 0) {
            $diff = $adjustmentsLeft / $totalPrice;
        }

        foreach ($set->getPositions() as $position) {
            foreach ($set->getItemsByPosition($position) as $item) {
                /**
                 * @var $item CartItem
                 */

                if ($item->hasAttr($item::ATTR_READONLY_PRICE)) {
                    continue;
                }

                $price = $item->getPrice();

                $adjustmentAmount = min($price * $diff, $adjustmentsLeft);

                if ((float)$adjustmentAmount === 0.0) {
                    continue;
                }

                if ($overprice) {
                    $newPrice = $this->makeOverpriceAmount($price, $adjustmentAmount);
                } else {
                    $newPrice = $this->makeDiscountAmount($price, $adjustmentAmount);
                }

                $flags  = array();
                $amount = ($price - $newPrice) * $item->getQty() * $set->getQty();

                if ( ! $this->makeSetItemDiscount($item, $set, $cart, $handler, $amount, $newPrice, $flags)) {
                    continue;
                }

                $adjustmentsLeft -= $adjustmentAmount * $item->getQty();

                if ((new CompareStrategy())->floatLessAndEqual($adjustmentsLeft, 0.0)) {
                    break;
                }
            }
        }

        return $set;
    }

    /**
     * @param CartItem                                                                                                                         $item
     * @param CartSet                                                                                                                          $set
     * @param Cart                                                                                                                             $cart
     * @param ProductsAdjustment|ProductsRangeAdjustments|ProductsAdjustmentTotal|ProductsAdjustmentSplit|PackageRangeAdjustments|RoleDiscount $handler
     * @param float                                                                                                                            $amount
     * @param float                                                                                                                            $newPrice
     * @param array                                                                                                                            $flags
     *
     * @return bool
     */
    protected function makeSetItemDiscount($item, $set, $cart, $handler, $amount, $newPrice, $flags)
    {
        $globalContext = $cart->getContext()->getGlobalContext();

        if ($handler->isReplaceWithCartAdjustment()) {
            $flags[]        = CartItem::FLAG_IGNORE;
            $adjustmentCode = $handler->getReplaceCartAdjustmentCode();

            if ($amount > 0) {
                $coupon = new CouponCartItem(
                    $globalContext,
                    CouponCartItem::TYPE_ITEM_DISCOUNT,
                    $adjustmentCode,
                    $amount / $item->getQty(),
                    $this->rule->getId(),
                    $item->getWcItem(),
                    $item->getQty()
                );

                $coupon->setAffectedCartItemQty($item->getQty() * $set->getQty());
                $cart->addCoupon($coupon);
            } elseif ($amount < 0) {
                $taxClass = $globalContext->getIsPricesIncludeTax() ? "" : "standard";
                $cart->addFee(
                    new Fee(
                        $globalContext,
                        Fee::TYPE_ITEM_OVERPRICE,
                        $adjustmentCode,
                        (-1) * $amount,
                        $taxClass,
                        $this->rule->getId()
                    )
                );
            }
        } elseif ($globalContext->getOption(
                'item_adjustments_as_coupon',
                false
            ) && $globalContext->getOption('item_adjustments_coupon_name', false)) {
            $flags[]        = CartItem::FLAG_IGNORE;
            $adjustmentCode = $globalContext->getOption('item_adjustments_coupon_name');

            if ($amount > 0) {
                $coupon = new CouponCartItem(
                    $globalContext,
                    CouponCartItem::TYPE_ITEM_DISCOUNT,
                    $adjustmentCode,
                    $amount / $item->getQty(),
                    $this->rule->getId(),
                    $item->getWcItem(),
                    $item->getQty()
                );

                $coupon->setAffectedCartItemQty($item->getQty() * $set->getQty());
                $cart->addCoupon($coupon);
            } elseif ($amount < 0) {
                $taxClass = $globalContext->getIsPricesIncludeTax() ? "" : "standard";
                $cart->addFee(
                    new Fee(
                        $globalContext,
                        Fee::TYPE_ITEM_OVERPRICE,
                        $adjustmentCode,
                        (-1) * $amount,
                        $taxClass,
                        $this->rule->getId()
                    )
                );
            }
        }

        $item->setPrice($this->rule->getId(), $newPrice, $flags);

        if ($handler instanceof ProductsAdjustment) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_SINGLE_ITEM_SIMPLE, $newPrice);
        } elseif ($handler instanceof ProductsRangeAdjustments) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_SINGLE_ITEM_RANGE, $newPrice);
        } elseif ($handler instanceof ProductsAdjustmentTotal) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_PACKAGE_SIMPLE, $newPrice);
        } elseif ($handler instanceof ProductsAdjustmentSplit) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_PACKAGE_SPLIT, $newPrice);
        } elseif ($handler instanceof PackageRangeAdjustments) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_PACKAGE_RANGE, $newPrice);
        } elseif ($handler instanceof RoleDiscount) {
            $discount = new ItemDiscount($globalContext, ItemDiscount::SOURCE_ROLE, $newPrice);
        } else {
            return false;
        }

        $discount->setRuleId($this->rule->getId());
        foreach ($flags as $flag) {
            $discount->addFlag($flag);
        }

        $item->setPriceNew($discount);

        return true;
    }

    /**
     * @param float $price
     * @param float $percentage
     *
     * @return float
     */
    protected function makeDiscountPercentage($price, $percentage)
    {
        if ($percentage < 0) {
            return $this->checkOverprice($price, (float)$price * (1 - (float)$percentage / 100));
        }

        return $this->checkDiscount($price, (float)$price * (1 - (float)$percentage / 100));
    }

    /**
     * @param float $price
     * @param float $percentage
     *
     * @return float
     */
    protected function makeOverpricePercentage($price, $percentage)
    {
        return $this->checkOverprice($price, (float)$price * (1 + (float)$percentage / 100));
    }

    /**
     * @param float $price
     * @param float $discountAmount
     *
     * @return float
     */
    protected function makeDiscountAmount($price, $discountAmount)
    {
        return $this->checkDiscount($price, (float)$price - (float)$discountAmount);
    }

    protected function makeOverpriceAmount($price, $overpriceAmount)
    {
        return $this->checkOverprice($price, (float)$price + (float)$overpriceAmount);
    }

    /**
     * @param float $price
     * @param float $value
     *
     * @return float
     */
    protected function makePriceFixed($price, $value)
    {
        $value = floatval($value);
        if ($price < $value) {
            return $this->checkOverprice($price, $value);
        }

        return $this->checkDiscount($price, $value);
    }

    /**
     * @return float
     */
    protected function makeFree()
    {
        return 0.0;
    }

    /**
     * @param float $oldPrice
     * @param float $newPrice
     *
     * @return float
     */
    private function checkDiscount($oldPrice, $newPrice)
    {
        $newPrice = max($newPrice, 0.0);
        $newPrice = min($newPrice, $oldPrice);

        if (
            $this->discountTotalLimit !== null
            && $this->discountTotalLimit > 0
            && $oldPrice - $newPrice > $this->discountTotalLimit
        ) {
            $newPrice = $oldPrice - $this->discountTotalLimit;
        }

        return (float)$newPrice;
    }

    /**
     * @param float $oldPrice
     * @param float $newPrice
     *
     * @return float
     */
    private function checkOverprice($oldPrice, $newPrice)
    {
        $newPrice = max($newPrice, 0.0);
        $newPrice = max($newPrice, $oldPrice);

        return (float)$newPrice;
    }
}
