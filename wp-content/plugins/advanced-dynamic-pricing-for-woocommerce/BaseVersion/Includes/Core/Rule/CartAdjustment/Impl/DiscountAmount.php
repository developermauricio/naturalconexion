<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Impl;

use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCart;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustmentsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\CouponCartAdj;

defined('ABSPATH') or exit;

class DiscountAmount extends AbstractCartAdjustment implements CouponCartAdj, CartAdjustment
{
    /**
     * @var float
     */
    protected $couponValue;

    /**
     * @var string
     */
    protected $couponCode;

    public static function getType()
    {
        return 'discount__amount';
    }

    public static function getLabel()
    {
        return __('Fixed discount, once', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'cart_adjustments/discount.php';
    }

    public static function getGroup()
    {
        return CartAdjustmentsLoader::GROUP_DISCOUNT;
    }

    public function __construct()
    {
        $this->amountIndexes = array('couponValue');
    }

    /**
     * @param float|string $couponValue
     */
    public function setCouponValue($couponValue)
    {
        $this->couponValue = $couponValue;
    }

    /**
     * @param string $couponCode
     */
    public function setCouponCode($couponCode)
    {
        $this->couponCode = $couponCode;
    }

    public function getCouponValue()
    {
        return $this->couponValue;
    }

    public function getCouponCode()
    {
        return $this->couponCode;
    }

    public function isValid()
    {
        return isset($this->couponValue) or isset($this->couponCode);
    }

    public function applyToCart($rule, $cart)
    {
        $context    = $cart->getContext()->getGlobalContext();
        $couponCode = ! empty($this->couponCode) ? $this->couponCode : $context->getOption('default_discount_name');

        $cart->addCoupon(
            new CouponCart(
                $context,
                CouponCart::TYPE_FIXED_VALUE,
                $couponCode,
                $this->couponValue,
                $rule->getId()
            )
        );
    }
}
