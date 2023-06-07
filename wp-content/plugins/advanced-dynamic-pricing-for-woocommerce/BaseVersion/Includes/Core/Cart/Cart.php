<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponInterface;
use ADP\BaseVersion\Includes\WC\WcCouponFacade;

defined('ABSPATH') or exit;

class Cart
{
    /**
     * @var CartItem[]
     */
    protected $items = array();

    /**
     * @var FreeCartItem[]
     */
    protected $freeItems = array();

    /**
     * @var AutoAddCartItem[]
     */
    protected $autoAddItems = array();

    /**
     * @var AutoAddCartItem[]
     */
    protected $recommendedPromotions = array();

    protected $originCouponsCodes = array();

    /**
     * @var CartContext
     */
    protected $cartContext;

    /**
     * @var ShippingAdjustment[]
     */
    protected $shippingAdjustments;

    /**
     * @var CouponInterface[]
     */
    protected $coupons;

    /**
     * @var Fee[]
     */
    protected $fees;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var string[]
     */
    protected $ruleTriggerCouponCodes = array();

    /**
     * @var array<int, Notice>
     */
    protected $notices = [];

    /**
     * @var array<int, CouponsAdjustment>
     */
    protected $couponsAdjustments = [];

    /** @var bool */
    protected $anyRulesApplied;

    /**
     * @param CartContext $cartContext
     */
    public function __construct(CartContext $cartContext)
    {
        $this->cartContext         = $cartContext;
        $this->shippingAdjustments = array();
        $this->coupons             = array();
        $this->fees                = array();

        $this->originCouponsCodes = array();
        $this->currency           = $cartContext->getGlobalContext()->currencyController->getCurrentCurrency();

        $this->ruleTriggerCouponCodes = array();
        $this->anyRulesApplied = false;
    }

    public function __clone()
    {
        $newItems = array();
        foreach ($this->items as $item) {
            $newItems[] = clone $item;
        }
        $this->items = $newItems;

        $newItems = array();
        foreach ($this->freeItems as $item) {
            $newItems[] = clone $item;
        }
        $this->freeItems = $newItems;

        $this->cartContext = clone $this->cartContext;

        $newAdj = array();
        foreach ($this->shippingAdjustments as $adj) {
            $newAdj[] = clone $adj;
        }
        $this->shippingAdjustments = $newAdj;

        $newAdj = array();
        foreach ($this->coupons as $adj) {
            $newAdj[] = clone $adj;
        }
        $this->coupons = $newAdj;

        $newAdj = array();
        foreach ($this->fees as $adj) {
            $newAdj[] = clone $adj;
        }
        $this->fees = $newAdj;

        $this->notices = array_map(function ($notice) {
            return clone $notice;
        }, $this->notices);
    }

    public function addOriginCoupon($code)
    {
        $this->originCouponsCodes[] = $code;
    }

    /**
     * @param array $originCouponsCodes
     */
    public function setOriginCouponsCodes(array $originCouponsCodes)
    {
        $this->originCouponsCodes = array_map('strval', $originCouponsCodes);
    }

    public function removeOriginCoupon($code)
    {
        $pos = array_search($code, $this->originCouponsCodes);

        if ($pos !== false) {
            unset($this->originCouponsCodes[$pos]);
            $this->originCouponsCodes = array_values($this->originCouponsCodes);
        }
    }

    public function removeAllOriginCoupon()
    {
        $this->originCouponsCodes = array();
    }

    /**
     * @return bool
     */
    public function removeCartOriginCoupon() {
        $removed = false;
        $this->originCouponsCodes = array_filter($this->originCouponsCodes, function($code) use (&$removed) {
            $coupon = new \WC_Coupon($code);
            $couponType = $coupon->get_discount_type();
            $valid = $couponType !== WcCouponFacade::TYPE_FIXED_CART;
            if (!$removed) {
                $removed = !$valid;
            }
            return $valid;
        });

        return $removed;
    }

    /**
     * @return bool
     */
    public function removeProductOriginCoupon() {
        $removed = false;
        $this->originCouponsCodes = array_filter($this->originCouponsCodes, function ($code) use (&$removed) {
            $coupon = new \WC_Coupon($code);
            $couponType = $coupon->get_discount_type();
            $valid = $couponType !== WcCouponFacade::TYPE_FIXED_PRODUCT && $couponType !== WcCouponFacade::TYPE_PERCENT;
            if (!$removed) {
                $removed = !$valid;
            }
            return $valid;
        });

        return $removed;
    }

    public function getOriginCoupons()
    {
        return $this->originCouponsCodes;
    }

    public function isEmpty()
    {
        return ! count($this->items) && ! count($this->freeItems);
    }

    /**
     * @param $newCartItems array
     */
    public function addToCart(...$newCartItems)
    {
        foreach ($newCartItems as $newCartItem) {
            if ($newCartItem instanceof CartItem) {
                $this->addSingleItem($newCartItem);
            } elseif ($newCartItem instanceof FreeCartItem) {
                $this->addFreeItem($newCartItem);
            } elseif ($newCartItem instanceof AutoAddCartItem) {
                $this->addAutoAddItem($newCartItem);
            }
        }
    }

    /**
     * @param FreeCartItem $newFreeItem
     *
     * @return boolean
     */
    protected function addFreeItem(FreeCartItem $newFreeItem)
    {
        if ( ! $newFreeItem instanceof FreeCartItem) {
            return false;
        }

        foreach ($this->freeItems as $freeItem) {
            if ($freeItem->hash() === $newFreeItem->hash()) {
                $freeItem->qty += $newFreeItem->qty;
                $freeItem->setQtyAlreadyInWcCart($freeItem->getQtyAlreadyInWcCart() + $newFreeItem->getQtyAlreadyInWcCart());

                return true;
            }
        }

        $this->freeItems[] = $newFreeItem;

        return true;
    }

    /**
     * @param AutoAddCartItem $newAutoAddItem
     *
     * @return boolean
     */
    protected function addAutoAddItem(AutoAddCartItem $newAutoAddItem)
    {
        if ( ! $newAutoAddItem instanceof AutoAddCartItem) {
            return false;
        }

        foreach ($this->autoAddItems as $autoAddItem) {
            if ($autoAddItem->hash() === $newAutoAddItem->hash()) {
                $autoAddItem->qty += $newAutoAddItem->qty;
                $autoAddItem->setQtyAlreadyInWcCart($autoAddItem->getQtyAlreadyInWcCart() + $newAutoAddItem->getQtyAlreadyInWcCart());
                $autoAddItem->setPrice($newAutoAddItem->getPrice());

                return true;
            }
        }

        $this->autoAddItems[] = $newAutoAddItem;

        return true;
    }

    /**
     * @param CartItem $newCartItem
     *
     * @return boolean
     */
    protected function addSingleItem(CartItem $newCartItem)
    {
        if ( ! $newCartItem instanceof CartItem) {
            return false;
        }

        foreach ($this->items as $cartItem) {
            if ($cartItem->hasAttr($cartItem::ATTR_IMMUTABLE)) {
                continue;
            }

            /**
             * The single 'if' condition is too long, so we have what you see
             */
            $identical = true;
            if ($identical && $cartItem->hasAttr($cartItem::ATTR_IMMUTABLE) !== $newCartItem->hasAttr($newCartItem::ATTR_IMMUTABLE)) {
                $identical = false;
            }
            if ($identical && $cartItem->getHash() !== $newCartItem->getHash()) {
                $identical = false;
            }
            if ($identical && $cartItem->getOriginalPrice() !== $newCartItem->getOriginalPrice()) {
                $identical = false;
            }
            if ($identical && md5(json_encode($cartItem->getHistory())) !== md5(json_encode($newCartItem->getHistory()))) {
                $identical = false;
            }
            if ($identical) {
                $cartItem->setQty($cartItem->getQty() + $newCartItem->getQty());

                return true;
            }
        }

        $this->items[] = $newCartItem;
//		usort( $this->items, function ( $item1, $item2 ) {
//			/**
//			 * @var $item1 CartItem
//			 * @var $item2 CartItem
//			 */
//
//			$pos1 = $item1->get_pos();
//			$pos2 = $item2->get_pos();
//
//			return $pos1 - $pos2;
//		} );


        return true;
    }

    /**
     * @param AutoAddCartItem $newRecommendationItem
     */
    public function addRecommendedPromotion($newRecommendationItem)
    {
        if (!$newRecommendationItem instanceof AutoAddCartItem) {
            return false;
        }

        foreach ($this->recommendedPromotions as $item) {
            if ($item->hash() === $newRecommendationItem->hash()) {
                $item->qty += $newRecommendationItem->qty;
                $item->setQtyAlreadyInWcCart($item->getQtyAlreadyInWcCart() + $newRecommendationItem->getQtyAlreadyInWcCart());
                $item->setPrice($newRecommendationItem->getPrice());

                return true;
            }
        }

        $this->recommendedPromotions[] = $newRecommendationItem;

        return true;
    }

    /**
     * @return AutoAddCartItem[]
     */
    public function getRecommendedPromotions()
    {
        return $this->recommendedPromotions;
    }

    /**
     * @return bool
     */
    public function hasImmutableChangedItems()
    {
        $result = false;
        foreach ($this->items as $item) {
            /**
             * @var CartItem $item
             */
            if ($item->hasAttr($item::ATTR_IMMUTABLE) && $item->areRuleApplied()) {
                $result = true;
                break;
            }

        }

        return $result;
    }

    /**
     * @return array<int, CartItem>
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param $items array<int, CartItem>
     */
    public function setItems($items)
    {
        $this->items = array();

        foreach ($items as $item) {
            if ($item instanceof CartItem) {
                $this->items[] = $item;
            }
        }
    }

    /**
     * @return array<int, FreeCartItem>
     */
    public function getFreeItems()
    {
        return $this->freeItems;
    }

    public function purgeFreeItems()
    {
        $this->freeItems = [];
    }

    /**
     * @return array<int, AutoAddCartItem>
     */
    public function getAutoAddItems()
    {
        return $this->autoAddItems;
    }

    public function purgeAutoAddItems()
    {
        $this->autoAddItems = [];
    }

    /**
     * @return CartContext
     */
    public function getContext(): CartContext
    {
        return $this->cartContext;
    }

    private function sortItems()
    {
        usort($this->items, function ($item_a, $item_b) {
            /**
             * @var $item_a CartItem
             * @var $item_b CartItem
             */
            $tmp_a = $item_a->hasAttr($item_a::ATTR_TEMP);
            $tmp_b = $item_b->hasAttr($item_a::ATTR_TEMP);

            if ( ! $tmp_a && $tmp_b) {
                return -1;
            }

            if ($tmp_a && ! $tmp_b) {
                return 1;
            }

            return 0;
        });

    }

    /**
     * @return array<int, CartItem>
     */
    public function getMutableItems()
    {
        $this->sortItems();

        return array_filter($this->items, function ($item) {
            /**@var $item CartItem */
            return ! $item->hasAttr($item::ATTR_IMMUTABLE);
        });
    }


    public function purgeMutableItems()
    {
        $this->items = array_filter($this->items, function ($item) {
            /** @var $item CartItem */
            return $item->hasAttr($item::ATTR_IMMUTABLE);
        });
    }

    public function destroyEmptyItems()
    {
        $this->items = array_values(array_filter($this->items, function ($item) {
            /**
             * @var $item CartItem
             */
            return $item->getQty() > 0;
        }));
    }

    /**
     * @param ShippingAdjustment $adj
     */
    public function addShippingAdjustment(ShippingAdjustment $adj)
    {
        $this->shippingAdjustments[] = $adj;
    }

    /**
     * @return array<int, ShippingAdjustment[]>
     */
    public function getShippingAdjustments()
    {
        return $this->shippingAdjustments;
    }

    /**
     * @param CouponInterface $coupon
     */
    public function addCoupon(CouponInterface $coupon)
    {
        $this->coupons[] = $coupon;
    }

    /**
     * @return array<int, CouponInterface>
     */
    public function getCoupons()
    {
        return $this->coupons;
    }

    /**
     * @param Fee $fee
     */
    public function addFee(Fee $fee)
    {
        $this->fees[] = $fee;
    }

    /**
     * @return array<int, Fee>
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @param string $code
     */
    public function addRuleTriggerCoupon($code)
    {
        if (is_string($code) && ($code = strval($code))) {
            $this->ruleTriggerCouponCodes[] = $code;
        }
    }

    /**
     * @param string $code
     */
    public function removeRuleTriggerCoupon($code)
    {
        $pos = array_search($code, $this->ruleTriggerCouponCodes);

        if ($pos !== false) {
            unset($this->ruleTriggerCouponCodes[$pos]);
            $this->ruleTriggerCouponCodes = array_values($this->ruleTriggerCouponCodes);
        }
    }

    public function removeAllRuleTriggerCoupons()
    {
        $this->ruleTriggerCouponCodes = array();
    }

    /**
     * @return array<int, string>
     */
    public function getRuleTriggerCoupons()
    {
        return $this->ruleTriggerCouponCodes;
    }

    public function removeExactItem($item) {
        $this->items = array_values(array_filter($this->items, function ($tmpItem) use ($item) {
            return $tmpItem !== $item;
        }));
    }

    public function addNotice(Notice $notice)
    {
        $this->notices[] = $notice;
    }

    public function getNotices()
    {
        return array_map(function ($notice) {
            return clone $notice;
        }, $this->notices);
    }

    public function clearNotices()
    {
        $this->notices = [];
    }

    /**
     * @return CouponsAdjustment[]
     */
    public function getCouponsAdjustments(): array
    {
        return $this->couponsAdjustments;
    }

    /**
     * @param CouponsAdjustment $couponsAdjustment
     */
    public function addCouponsAdjustments($couponsAdjustment)
    {
        if ($couponsAdjustment instanceof CouponsAdjustment) {
            $this->couponsAdjustments[] = $couponsAdjustment;
        }
    }

    /**
     * @return bool
     */
    public function isAnyRulesApplied(): bool
    {
        return $this->anyRulesApplied;
    }

    /**
     * @param bool $anyRulesApplied
     */
    public function setAnyRulesApplied(bool $anyRulesApplied)
    {
        $this->anyRulesApplied = $anyRulesApplied;
    }

    /**
     * @return array<int,FreeCartItemChoices>
     */
    public function getListOfFreeCartItemChoices()
    {
        return [];
    }
}
