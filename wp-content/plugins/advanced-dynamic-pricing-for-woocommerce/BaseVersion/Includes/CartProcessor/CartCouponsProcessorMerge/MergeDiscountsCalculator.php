<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge;

use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\CartCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\CartItemCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\ExternalWcCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\InternalWcCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\IMergeCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\RuleTriggerCoupon;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCart;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponRuleTrigger;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\WcCouponCart;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\WcCouponExternal;
use ADP\BaseVersion\Includes\WC\PriceFunctions;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use WC_Coupon;

class MergeDiscountsCalculator
{
    /** @var Context */
    private $context;

    /** @var array */
    private $couponDiscountsArray;

    /** @var CartCouponsProcessorMerge */
    private $cartCouponsProcessorMerge;

    /** @var \WC_Discounts */
    private $wcDiscounts;

    /** @var \ReflectionProperty */
    private $discountsProperty;

    /** @var \ReflectionMethod */
    private $getItemsToApplyCouponMethod;

    /** @var \ReflectionMethod */
    private $applyCouponPercentMethod;

    /** @var \ReflectionMethod */
    private $applyCouponFixedCartMethod;

    /** @var array<int, IMergeCoupon> */
    private $coupons;

    private $calculateMethods;

    private $splitCartItemCouponApply;

    public function __construct(
        CartCouponsProcessorMerge $cartCouponsProcessorMerge,
        array $couponDiscountsArray,
        \WC_Cart $wcCart
    ) {
        $this->context = adp_context();
        $this->cartCouponsProcessorMerge = $cartCouponsProcessorMerge;
        $this->couponDiscountsArray = $couponDiscountsArray;

        $this->wcDiscounts = new \WC_Discounts();
        $this->wcDiscounts->set_items_from_cart($wcCart);

        $reflection = new \ReflectionClass($this->wcDiscounts);
        $this->discountsProperty = $reflection->getProperty('discounts');
        $this->discountsProperty->setAccessible(true);

        $this->getItemsToApplyCouponMethod = $reflection->getMethod('get_items_to_apply_coupon');
        $this->getItemsToApplyCouponMethod->setAccessible(true);

        $this->applyCouponPercentMethod = $reflection->getMethod('apply_coupon_percent');
        $this->applyCouponPercentMethod->setAccessible(true);

        $this->applyCouponFixedCartMethod = $reflection->getMethod('apply_coupon_fixed_cart');
        $this->applyCouponFixedCartMethod->setAccessible(true);

        $this->coupons = [];

        $this->splitCartItemCouponApply = [];

        $this->calculateMethods = [
            WcCouponCart::class => [$this, 'applyWcCouponCart'],
            WcCouponExternal::class => [$this, 'applyWcCouponExternal'],
            CouponCartItem::class => [$this, 'applyCouponCartItem'],
            CouponCart::class => [$this, 'applyCouponCart'],
            CouponRuleTrigger::class => [$this, 'applyCouponRuleTrigger'],
        ];
    }

    public function applyCoupon($coupon)
    {
        $method = $this->calculateMethods[get_class($coupon)] ?? null;

        if ($method === null) {
            throw new \Exception(sprintf("Implementation is missing for class %s", get_class($coupon)));
        }

        try {
            $method($coupon);
        } catch (\Exception $e) {

        }
    }

    /** @return IMergeCoupon[]|array */
    public function getCoupons(): array
    {
        return $this->coupons;
    }

    protected function applyWcCouponCart(WcCouponCart $coupon): InternalWcCoupon
    {
        $couponObj = $this->loadWcCouponByCode($coupon->getCode());

        $id = $this->genId();
        $couponObj->set_code($id);
        $result = $this->wcDiscounts->apply_coupon($couponObj);
        $couponObj->set_code($coupon->getCode());

        $mergeCoupon = new InternalWcCoupon(
            $couponObj,
            $coupon->getRuleId(),
            $this->discountsProperty->getValue($this->wcDiscounts)[$id] ?? []
        );

        $this->coupons[] = $mergeCoupon;

        return $mergeCoupon;
    }

    protected function applyWcCouponExternal(WcCouponExternal $coupon): ExternalWcCoupon
    {
        $couponObj = $coupon->getWcCoupon();

        $id = $this->genId();
        $couponObj->set_code($id);
        $result = $this->wcDiscounts->apply_coupon($couponObj);
        $couponObj->set_code($coupon->getCode());

        $mergeCoupon = new ExternalWcCoupon(
            $couponObj,
            $this->discountsProperty->getValue($this->wcDiscounts)[$id] ?? []
        );

        $this->coupons[] = $mergeCoupon;

        return $mergeCoupon;
    }

    /**
     * @throws \Exception
     */
    protected function applyCouponCartItem(CouponCartItem $coupon): CartItemCoupon
    {
        $items = $this->wcDiscounts->get_items();
        $priceFunctions = new PriceFunctions($this->context);

        $mergeCouponBuilder = CartItemCoupon::builder()
            ->label($coupon->getLabel())
            ->code($coupon->getCode())
            ->ruleId($coupon->getRuleId());

        $itemToApply = array_filter($items, function ($item) use ($coupon) {
            $facade = new WcCartItemFacade($item->object, $item->key);

            return $facade->getHistory()
                && isset($facade->getHistory()[$coupon->getRuleId()])
                && !isset($this->splitCartItemCouponApply[$facade->getKey()][$coupon->getRuleId()])
                && (
                    $coupon->getAffectedCartItemKey() === $facade->getKey()
                    || $coupon->getAffectedCartItemKey() === $facade->getOriginalKey()
                );
        });

        if (count($itemToApply) === 0) {
            throw new \Exception("Affected cart item was not found.");
        }

        $item = reset($itemToApply);

        $facade = new WcCartItemFacade($item->object, $item->key);
        $ruleId = $coupon->getRuleId();

        if ($coupon->getType() === $coupon::TYPE_ITEM_DISCOUNT) {
            $newAmount = floatval(0);
            $newAmount += array_sum($facade->getHistory()[$ruleId] ?? []) * $coupon->getAffectedCartItemQty();

            if ($newAmount > 0) {
                $args = array('price' => $newAmount);
                if ($this->context->getIsPricesIncludeTax()) {
                    $newAmount = $priceFunctions->getPriceIncludingTax($facade->getProduct(), $args);
                } else {
                    $newAmount = $priceFunctions->getPriceExcludingTax($facade->getProduct(), $args);
                }
            }

            $mergeCouponBuilder->typeItem();
        } elseif ($coupon->getType() === $coupon::TYPE_FREE_ITEM) {
            $newAmount = $coupon->getValue() * $coupon->getAffectedCartItemQty();
            $mergeCouponBuilder->typeFreeItem();
        } else {
            throw new \Exception(sprintf("Incorrect coupon type: %s", $coupon->getType()));
        }

        $mergeCouponBuilder->affectedCartItem($facade);

        if ( isset($this->splitCartItemCouponApply[$facade->getKey()]) ) {
            $this->splitCartItemCouponApply[$facade->getKey()][$ruleId] = $newAmount;
        } else {
            $this->splitCartItemCouponApply[$facade->getKey()] = [$ruleId => $newAmount];
        }

        $newAmount = round(wc_add_number_precision_deep($newAmount), wc_get_rounding_precision());
        $itemDiscountAmounts = [$facade->getKey() => $newAmount];
        $mergeCouponBuilder->totalsPerItem($itemDiscountAmounts);

        $mergeCoupon = $mergeCouponBuilder->build();
        $this->coupons[] = $mergeCoupon;

        return $mergeCoupon;
    }

    protected function applyCouponCart(CouponCart $coupon)
    {
        $couponCode = $coupon->getCode();
        $items = $this->wcDiscounts->get_items();

        $couponObj = new \WC_Coupon();
        $couponObj->set_virtual(true);
        $couponObj->set_code($couponCode);
        $couponObj->set_amount($coupon->getValue());
        $couponObj->set_maximum_amount($coupon->getMaxDiscount());

        $mergeCouponBuilder = CartCoupon::builder()
            ->ruleId($coupon->getRuleId())
            ->code($couponCode)
            ->label($coupon->getLabel());

        if ($coupon->isType($coupon::TYPE_PERCENTAGE)) {
            $applyCouponMethod = $this->applyCouponPercentMethod;
            $couponObj->get_discount_type('percent');
            $mergeCouponBuilder->typePercentage();
        } elseif ($coupon->isType($coupon::TYPE_FIXED_VALUE)) {
            $applyCouponMethod = $this->applyCouponFixedCartMethod;
            $couponObj->get_discount_type('fixed_cart');
            $mergeCouponBuilder->typeFixedValue();
        } else {
            return null;
        }

        $id = $this->genId();
        $couponObj->set_code($id);
        $this->discountsProperty->setValue(
            $this->wcDiscounts,
            array_merge(
                $this->discountsProperty->getValue($this->wcDiscounts),
                [$id => array_fill_keys(array_keys($this->couponDiscountsArray), 0)]
            )
        );

        if ($coupon->getMaxDiscount()) {
            $discountLeft = $coupon->getMaxDiscount();
            $limiter = function ($discount, $discountingAmount, $cartItem, $single, $wcDiscount) use (
                &
                $discountLeft
            ) {
                if ($discountLeft > $discount) {
                    $newDiscount = $discount;
                    $discountLeft -= $newDiscount;
                } else {
                    $newDiscount = $discountLeft;
                    $discountLeft = 0;
                }

                return $newDiscount;
            };


            add_filter('woocommerce_coupon_get_discount_amount', $limiter, 10, 5);
            $applyCouponMethod->invoke($this->wcDiscounts, $couponObj, $this->wcDiscounts->get_items());
            remove_filter('woocommerce_coupon_get_discount_amount', $limiter, 10);
        } else {
            $applyCouponMethod->invoke($this->wcDiscounts, $couponObj, $items);
        }

        $mergeCouponBuilder->totalsPerItem(
            $this->discountsProperty->getValue($this->wcDiscounts)[$id] ?: []
        );

        $mergeCoupon = $mergeCouponBuilder->build();
        $this->coupons[] = $mergeCoupon;

        return $mergeCoupon;
    }

    protected function applyCouponRuleTrigger(CouponRuleTrigger $coupon)
    {
        $mergeCoupon = new RuleTriggerCoupon(
            $coupon->getRuleId()
        );

        $this->coupons[] = $mergeCoupon;

        return $mergeCoupon;
    }

    protected function genId(): string
    {
        return wp_generate_uuid4();
    }

    protected function loadWcCouponByCode($couponCode): WC_Coupon
    {
        $this->cartCouponsProcessorMerge->removeFilterToInstallCouponsData();
        $wcCoupon = new WC_Coupon($couponCode);
        $this->cartCouponsProcessorMerge->setFilterToInstallCouponsData();

        return $wcCoupon;
    }
}
