<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;

class WcCouponCartProcessorFilterChain
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \WC_Coupon
     */
    protected $wcCoupon;

    /**
     * @var bool
     */
    protected $filteredValue;

    /**
     * @var string[]
     */
    protected $productCouponTypes;

    /**
     * @var string[]
     */
    protected $cartCouponTypes;

    public function __construct(\WC_Coupon $wcCoupon)
    {
        $this->context = adp_context();
        $this->wcCoupon = $wcCoupon;
        $this->filteredValue = true;

        $this->productCouponTypes = ['percent', 'fixed_product'];
        $this->cartCouponTypes = ['fixed_cart'];
    }

    /**
     * @param string[] $productCouponTypes
     */
    public function setProductCouponTypes(array $productCouponTypes)
    {
        $this->productCouponTypes = $productCouponTypes;
    }

    /**
     * @param string[] $cartCouponTypes
     */
    public function setCartCouponTypes(array $cartCouponTypes)
    {
        $this->cartCouponTypes = $cartCouponTypes;
    }

    public function filterByExternalProductCouponsBehaviorOption(Cart $cart): WcCouponCartProcessorFilterChain
    {
        if ($this->filteredValue === false) {
            return $this;
        }

        $wcCoupon = $this->wcCoupon;
        $wcCouponType = $wcCoupon->get_discount_type("edit");
        if (!in_array($wcCouponType, $this->productCouponTypes, true)) {
            return $this;
        }

        $externalProductCouponsBehavior = $this->context->getOption('external_product_coupons_behavior');

        if ($externalProductCouponsBehavior === 'disable_if_any_rule_applied') {
            $this->filteredValue = !$cart->isAnyRulesApplied();
        } elseif ($externalProductCouponsBehavior === 'disable_if_any_of_cart_items_updated') {
            $totalDiscountsSum = array_sum(
                array_map(
                    function ($item) {
                        return array_sum(
                            array_map(
                                function ($amounts) {
                                    return array_sum($amounts);
                                },
                                $item->getDiscounts()
                            )
                        );
                    },
                    $cart->getItems()
                )
            );

            $this->filteredValue = $totalDiscountsSum <= 0;
        }

        return $this;
    }

    public function filterByExternalCartCouponsBehaviorOption(Cart $cart): WcCouponCartProcessorFilterChain
    {
        if ($this->filteredValue === false) {
            return $this;
        }

        $wcCoupon = $this->wcCoupon;
        $wcCouponType = $wcCoupon->get_discount_type("edit");
        if (!in_array($wcCouponType, $this->cartCouponTypes, true)) {
            return $this;
        }

        $externalCartCouponsBehavior = $this->context->getOption('external_cart_coupons_behavior');

        if ($externalCartCouponsBehavior === 'disable_if_any_rule_applied') {
            $this->filteredValue = !$cart->isAnyRulesApplied();
        } elseif ($externalCartCouponsBehavior === 'disable_if_any_of_cart_items_updated') {
            $totalDiscountsSum = array_sum(
                array_map(
                    function ($item) {
                        return array_sum(
                            array_map(
                                function ($amounts) {
                                    return array_sum($amounts);
                                },
                                $item->getDiscounts()
                            )
                        );
                    },
                    $cart->getItems()
                )
            );

            $this->filteredValue = $totalDiscountsSum <= 0;
        } elseif ($externalCartCouponsBehavior === 'apply_to_unmodified_only') {
            $totalHistorySum = array_sum(
                array_map(
                    function ($item) {
                        return array_sum(
                            array_map(
                                function ($amounts) {
                                    return array_sum($amounts);
                                },
                                $item->getHistory()
                            )
                        );
                    },
                    $cart->getItems()
                )
            );

            $this->filteredValue = $totalHistorySum <= 0;
        }

        return $this;
    }

    public function filterIsValid(\WC_Cart $wcCart): WcCouponCartProcessorFilterChain
    {
        if ($this->filteredValue === false) {
            return $this;
        }

        $wcCoupon = $this->wcCoupon;
        $discounts = new \WC_Discounts($wcCart);

        try {
            $this->filteredValue = $discounts->is_coupon_valid($wcCoupon) === true;
        } catch (\Exception $e) {
            $this->filteredValue = false;
        }

        return $this;
    }

    public function getFilteredValue(): bool
    {
        return $this->filteredValue;
    }
}
