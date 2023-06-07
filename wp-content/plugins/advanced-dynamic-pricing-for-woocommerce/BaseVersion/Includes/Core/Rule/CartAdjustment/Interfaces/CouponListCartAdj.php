<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces;

defined('ABSPATH') or exit;

interface CouponListCartAdj
{
    const COUPON_LIST_KEY = 'coupon_list';

    /**
     * @param array $couponList
     */
    public function setCouponList($couponList);

    /**
     * @return array
     */
    public function getCouponList();
}
