<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\ExternalWcCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeDiscountsCalculator;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartContext;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCart;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponRuleTrigger;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\WcCouponCart;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\WcCouponExternal;
use ADP\BaseVersion\Includes\Core\Cart\CouponsAdjustment;
use ADP\BaseVersion\Includes\Core\Cart\DisableAllWcCouponsCart;
use ADP\BaseVersion\Includes\Core\Cart\DisableWcCouponsCart;
use ADP\BaseVersion\Includes\WC\Utils as WcUtils;
use ADP\BaseVersion\Includes\WC\WcAdpMergedCoupon\InMemoryAdpMergedCouponStorage;
use ADP\BaseVersion\Includes\WC\WcAdpMergedCoupon\WcAdpMergedCoupon;
use ADP\BaseVersion\Includes\WC\WcAdpMergedCouponHelper;
use WC_Cart;
use WC_Coupon;

defined('ABSPATH') or exit;

class CartCouponsProcessorMerge implements ICartCouponsProcessor
{
    /**
     * @var Context
     */
    protected $context;

    protected $mergedCoupons;

    /**
     * @var array<string, CouponsAdjustment>
     */
    protected $disabledWcCoupons;

    /**
     * @var bool
     */
    protected $disableAllInRuleWcCoupons;

    /**
     * @var CartContext
     */
    protected $cartContext;

    protected $wcCouponExternalExcludeCodes = [];

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
        $this->purge();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function installActions()
    {
        $this->setFilterToInstallCouponsData();
        $this->setFilterToSuppressDisabledWcCoupons();
        $this->setFiltersCoupon();
    }

    public function setFilterToInstallCouponsData()
    {
        add_filter('woocommerce_get_shop_coupon_data', array($this, 'getCouponData'), 10, 3);
    }

    public function removeFilterToInstallCouponsData()
    {
        remove_filter('woocommerce_get_shop_coupon_data', array($this, 'getCouponData'), 10);
    }

    public function setFilterToSuppressDisabledWcCoupons()
    {
        add_filter('woocommerce_coupon_is_valid', array($this, 'hookDisabledCouponsIsValidForCart'), 10, 3);
    }

    public function setFiltersCoupon()
    {
        add_filter('woocommerce_cart_coupon_types', array($this, 'addCouponCartType'), 10, 1);
        add_filter('woocommerce_coupon_discount_types', array($this, 'addCouponDiscountType'), 10, 1);
        add_filter('woocommerce_coupon_custom_discounts_array', array($this, 'calculateCouponDiscountsArray'), 10, 5);
    }

    public function init()
    {

    }

    public function sanitize(WC_Cart $wcCart)
    {
        $appliedCoupons = $wcCart->applied_coupons;
        $adpCoupons = InMemoryAdpMergedCouponStorage::getInstance()->getAllKeys();

        foreach ($appliedCoupons as $index => $couponCode) {
            if (in_array($couponCode, $adpCoupons, true)) {
                unset($appliedCoupons[$index]);
            }
        }

        $wcCart->applied_coupons = array_values($appliedCoupons);
        $this->purge();
    }

    public function updateTotals(WC_Cart $wcCart)
    {
        /**
         * Put non-merged coupons into 'merge' storage.
         * This allows to store and then fetch discounts per item for non-merged coupons.
         */
        $wcDiscounts = new \WC_Discounts($wcCart);

        foreach ($wcCart->get_coupons() as $wcCoupon) {
            $wcDiscounts->apply_coupon($wcCoupon);
        }

        $reflection = new \ReflectionClass($wcDiscounts);
        $discountsProperty = $reflection->getProperty('discounts');
        $discountsProperty->setAccessible(true);

        foreach ($wcCart->get_coupons() as $wcCoupon) {
            /** @var $wcCoupon WC_Coupon */
            $couponCode = $wcCoupon->get_code('edit');

            $mergedCoupon = WcAdpMergedCouponHelper::loadOfCoupon($wcCoupon);

            if (!$mergedCoupon->hasAdpPart()) {
                $mergedCoupon->setParts(
                    [
                        new ExternalWcCoupon(
                            new WC_Coupon($couponCode),
                            $discountsProperty->getValue($wcDiscounts)[$couponCode] ?: []
                        )
                    ]
                );
                WcAdpMergedCouponHelper::store($mergedCoupon);
            }
        }

        $this->cartContext->getSession()->insertCouponsData([], [], []);
    }

    public function applyCouponsToWcCart(Cart $cart, WC_Cart $wcCart)
    {
        $wcCart->applied_coupons = [];
        $this->purge();

        $this->processOriginCoupons($cart, $wcCart);
        $this->processRuleTriggerCoupons($cart, $wcCart);
        $this->processCouponAdjustments($cart, $wcCart);
        $this->processCartCoupons($cart, $wcCart);
        $this->processIndividualUseCoupons($cart, $wcCart);

        $wcCart->applied_coupons = array_map('strval', array_keys($this->mergedCoupons));
    }

    protected function processOriginCoupons(Cart $cart, WC_Cart $wcCart)
    {
        foreach ($cart->getOriginCoupons() as $couponCode) {
            $wcCoupon = $this->loadWcCouponByCode($couponCode);

            if ($this->isWcCouponValid($cart, $wcCart, $wcCoupon)) {
                $this->addToMerged($couponCode, new WcCouponExternal($wcCoupon));
            } else {
                $this->wcCouponExternalExcludeCodes[] = $couponCode;
                $this->replaceCouponNotices();
            }
        }
    }

    protected function processRuleTriggerCoupons(Cart $cart, WC_Cart $wcCart)
    {
        $ruleIdByActivationCouponCode = [];
        foreach (CacheHelper::loadActiveRules($this->context)->getRules() as $activeRule) {
            if ($activeRule->getActivationCouponCode() !== null) {
                $ruleIdByActivationCouponCode[$activeRule->getActivationCouponCode()] = $activeRule->getId();
            }
        }

        foreach ($cart->getRuleTriggerCoupons() as $couponCode) {
            $this->addToMerged(
                $couponCode,
                new CouponRuleTrigger($couponCode, $ruleIdByActivationCouponCode[$couponCode] ?? 0)
            );
            $this->addExternalWcCouponWithSameCodeIfPossible($cart, $wcCart, $couponCode);
        }
    }

    protected function processCouponAdjustments(Cart $cart, WC_Cart $wcCart)
    {
        $this->cartContext = $cart->getContext();

        $this->disabledWcCoupons = [];
        $this->disableAllInRuleWcCoupons = false;
        foreach ($cart->getCouponsAdjustments() as $coupon) {
            if ($coupon instanceof DisableWcCouponsCart) {
                $this->disabledWcCoupons[] = $coupon->getCode();
            } elseif ($coupon instanceof DisableAllWcCouponsCart) {
                $this->disableAllInRuleWcCoupons = true;
            }
        }
    }

    protected function processCartCoupons(Cart $cart, WC_Cart $wcCart)
    {
        $context = $cart->getContext();
        $this->cartContext = $cart->getContext();

        foreach ($cart->getCoupons() as $coupon) {
            $coupon = clone $coupon;

            if (empty($coupon->getCode())) {
                continue;
            }

            if ($coupon instanceof CouponCart && $coupon->getValue()) {
                if ($coupon->isType($coupon::TYPE_FIXED_VALUE) && $context->isCombineMultipleDiscounts()) {
                    $coupon->setCode($context->getOption('default_discount_name'));
                }

                $this->addToMerged($coupon->getCode(), $coupon);
                $this->addExternalWcCouponWithSameCodeIfPossible($cart, $wcCart, $coupon->getCode());
            } elseif ($coupon instanceof CouponCartItem && $coupon->getValue()) {
                if ($context->isCombineMultipleDiscounts()) {
                    $coupon->setCode($context->getOption('default_discount_name'));
                }

                $this->addToMerged($coupon->getCode(), $coupon);
                $this->addExternalWcCouponWithSameCodeIfPossible($cart, $wcCart, $coupon->getCode());
            } elseif ($coupon instanceof WcCouponCart) {
                if (!$this->disableAllInRuleWcCoupons
                    && !in_array($coupon->getCode(), $this->disabledWcCoupons, true)
                ) {
                    $this->addToMerged($coupon->getCode(), $coupon);
                }
            }
        }
    }

    protected function processIndividualUseCoupons(Cart $cart, WC_Cart $wcCart)
    {
        $mergedCoupons = $this->mergedCoupons;

        $atLeastOneNonIndividualExists = false;
        foreach ($mergedCoupons as $couponCode => $coupons) {
            foreach ($coupons as $coupon) {
                if ($coupon instanceof WcCouponCart) {
                    $wcCoupon = $this->loadWcCouponByCode($coupon->getCode());

                    if (!$wcCoupon->get_individual_use("edit")) {
                        $atLeastOneNonIndividualExists = true;
                        break;
                    }
                } elseif ($coupon instanceof WcCouponExternal) {
                    $wcCoupon = $coupon->getWcCoupon();
                    if (!$wcCoupon->get_individual_use("edit")) {
                        $atLeastOneNonIndividualExists = true;
                        break;
                    }
                } else {
                    $atLeastOneNonIndividualExists = true;
                    break;
                }
            }
        }

        $individualExists = false;
        foreach ($mergedCoupons as $couponCode => $coupons) {
            $newCoupons = [];
            foreach ($coupons as $coupon) {
                if ($coupon instanceof WcCouponCart) {
                    $wcCoupon = $this->loadWcCouponByCode($coupon->getCode());

                    if ($wcCoupon->get_individual_use("edit")) {
                        if (!$atLeastOneNonIndividualExists && !$individualExists) {
                            $newCoupons[] = $coupon;
                            $individualExists = true;
                        }

                        continue;
                    }
                } elseif ($coupon instanceof WcCouponExternal) {
                    $wcCoupon = $coupon->getWcCoupon();
                    if ($wcCoupon->get_individual_use("edit")) {
                        if (!$atLeastOneNonIndividualExists && !$individualExists) {
                            $newCoupons[] = $coupon;
                            $individualExists = true;
                        }

                        continue;
                    }
                }

                $newCoupons[] = $coupon;
            }
            $mergedCoupons[$couponCode] = $newCoupons;
        }

        $this->mergedCoupons = $mergedCoupons;
    }

    protected function addExternalWcCouponWithSameCodeIfPossible(Cart $cart, WC_Cart $wcCart, string $couponCode)
    {
        $wcCoupon = $this->loadWcCouponByCode($couponCode);

        if ($this->isWcCouponValid($cart, $wcCart, $wcCoupon)) {
            $this->addToMerged($couponCode, new WcCouponExternal($wcCoupon));
        }
    }

    protected function addToMerged($code, $coupon)
    {
        if (!isset($this->mergedCoupons[$code])) {
            $this->mergedCoupons[$code] = [];
        }

        foreach ($this->mergedCoupons[$code] as $loopCoupon) {
            if ($coupon instanceof WcCouponExternal && $loopCoupon instanceof WcCouponExternal) {
                return;
            }
        }

        $this->mergedCoupons[$code][] = $coupon;
    }

    protected function isWcCouponValid(Cart $cart, WC_Cart $wcCart, \WC_Coupon $wcCoupon): bool
    {
        return (new WcCouponCartProcessorFilterChain($wcCoupon))
            ->filterIsValid($wcCart)
            ->filterByExternalCartCouponsBehaviorOption($cart)
            ->filterByExternalProductCouponsBehaviorOption($cart)
            ->getFilteredValue();
    }

    /**
     * This filter allows custom coupon objects to be created on the fly.
     *
     * @param false $couponData
     * @param mixed $couponCode Coupon code
     * @param WC_Coupon $wcCoupon
     *
     * @return array|mixed
     */
    public function getCouponData($couponData, $couponCode, $wcCoupon)
    {
        if ($couponCode === "") {
            return $couponData;
        }

        $mergeCoupon = WcAdpMergedCouponHelper::loadOfCouponCode($couponCode);
        if ($mergeCoupon === null || !$mergeCoupon->hasAdpPart()) {
            return isset($this->mergedCoupons[$couponCode])
                ? [
                    'discount_type' => WcAdpMergedCoupon::COUPON_DISCOUNT_TYPE,
                    'amount' => 0.0,
                ]
                : $couponData;
        }

        return [
            'discount_type' => WcAdpMergedCoupon::COUPON_DISCOUNT_TYPE,
            'amount' => 0.0,
        ];
    }

    /**
     * @param array<string, string> $discountTypes
     *
     * @return array<string, string>
     */
    public function addCouponDiscountType($discountTypes)
    {
        $discountTypes[WcAdpMergedCoupon::COUPON_DISCOUNT_TYPE] = __(
            'WDP Coupon',
            'advanced-dynamic-pricing-for-woocommerce'
        );

        return $discountTypes;
    }

    /**
     * @param array<int, string> $discountTypes
     *
     * @return array<int, string>
     */
    public function addCouponCartType($discountTypes)
    {
        $discountTypes[] = WcAdpMergedCoupon::COUPON_DISCOUNT_TYPE;

        return $discountTypes;
    }

    /**
     *
     *
     * @param array<int, float> $couponDiscountsArray
     * @param \WC_Coupon $wcCoupon
     *
     * @return array<int, float>
     */
    public function calculateCouponDiscountsArray($couponDiscountsArray, $wcCoupon)
    {
        $couponCode = $wcCoupon->get_code('edit');
        $couponsToMerge = $this->mergedCoupons[$couponCode] ?? [];

        if (count($couponsToMerge) === 0) {
            return $couponDiscountsArray;
        }

        $discountsCalc = new MergeDiscountsCalculator(
            $this,
            $couponDiscountsArray,
            WC()->cart
        );

        foreach ($couponsToMerge as $coupon) {
            $discountsCalc->applyCoupon($coupon);
        }

        $resultAmounts = array_map(function ($coupon) {
            return $coupon->totalsPerItem();
        }, $discountsCalc->getCoupons());

        $newCouponDiscountsArray = [];
        foreach ($resultAmounts as $discountAmounts) {
            foreach ($discountAmounts as $cartItemKey => $amount) {
                if (!isset($newCouponDiscountsArray[$cartItemKey])) {
                    $newCouponDiscountsArray[$cartItemKey] = 0.0;
                }

                $newCouponDiscountsArray[$cartItemKey] += $amount;
            }
        }

        foreach ($couponDiscountsArray as $cartItemKey => $amount) {
            if (!isset($newCouponDiscountsArray[$cartItemKey])) {
                $newCouponDiscountsArray[$cartItemKey] = 0.0;
            }
        }

        $mergeCoupon = new WcAdpMergedCoupon($couponCode);
        $mergeCoupon->setParts($discountsCalc->getCoupons());
        WcAdpMergedCouponHelper::store($mergeCoupon);

        return $newCouponDiscountsArray;
    }

    /**
     * @param bool $valid
     * @param \WC_Coupon $wcCoupon
     * @param \WC_Discounts $wcDiscounts
     *
     * @return bool
     */
    public function hookDisabledCouponsIsValidForCart($valid, $wcCoupon, $wcDiscounts)
    {
        $mergedCoupon = WcAdpMergedCouponHelper::loadOfCoupon($wcCoupon);

        if ($this->disableAllInRuleWcCoupons === true && $mergedCoupon->hasOnlyInternalWcCouponPart()) {
            throw new \Exception(
                __('Sorry, this coupon is not applicable to cart.', 'advanced-dynamic-pricing-for-woocommerce')
            );
        }

        if (in_array($wcCoupon->get_code(), $this->disabledWcCoupons, true)) {
            throw new \Exception(
                __('Sorry, this coupon is not applicable to cart.', 'advanced-dynamic-pricing-for-woocommerce')
            );
        }

        return $valid;
    }

    protected function purge()
    {
        $this->mergedCoupons = [];
        $this->disabledWcCoupons = array();
        $this->wcCouponExternalExcludeCodes = array();
        $this->disableAllInRuleWcCoupons = false;
        InMemoryAdpMergedCouponStorage::getInstance()->purge();
    }

    protected function loadWcCouponByCode($couponCode): WC_Coupon
    {
        $this->removeFilterToInstallCouponsData();
        $wcCoupon = new WC_Coupon($couponCode);
        $this->setFilterToInstallCouponsData();

        return $wcCoupon;
    }

    /**
     * Replace notice in case of removing coupons later
     * If coupons won't be removed, notice will be replaced back
     */
    protected function replaceCouponNotices()
    {
        WcUtils::replaceWcNotice(
            array(
                'text' => __('Coupon code applied successfully.', 'woocommerce'),
                'type' => 'success',
            ),
            array(
                'text' => __('Sorry, coupons are disabled for these products.',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'type' => 'error',
            )
        );
    }
}
