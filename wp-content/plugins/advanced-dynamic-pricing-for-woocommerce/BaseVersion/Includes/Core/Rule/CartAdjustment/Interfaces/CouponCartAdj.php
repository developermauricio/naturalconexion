<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces;

defined('ABSPATH') or exit;

interface CouponCartAdj
{
    const COUPON_VALUE_KEY = 'coupon_value';
    const COUPON_CODE_KEY = 'coupon_code';
    const COUPON_MAX_DISCOUNT = 'coupon_max_discount';

    /**
     * @param float|string $couponValue
     */
    public function setCouponValue($couponValue);

    /**
     * @param string $couponCode
     */
    public function setCouponCode($couponCode);

    /**
     * @return float
     */
    public function getCouponValue();

    /**
     * @return string
     */
    public function getCouponCode();
}
