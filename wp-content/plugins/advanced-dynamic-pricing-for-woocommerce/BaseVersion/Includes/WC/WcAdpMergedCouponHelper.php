<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\ExternalWcCoupon;
use ADP\BaseVersion\Includes\WC\WcAdpMergedCoupon\InMemoryAdpMergedCouponStorage;
use ADP\BaseVersion\Includes\WC\WcAdpMergedCoupon\WcAdpMergedCoupon;

class WcAdpMergedCouponHelper
{
    public static function loadOfCoupon($wcCoupon)
    {
        if (!$wcCoupon instanceof \WC_Coupon) {
            return null;
        }

        $couponCode = $wcCoupon->get_code("edit");
        $storage = InMemoryAdpMergedCouponStorage::getInstance();
        $coupon = $storage->getByCodeOrNull($couponCode);

        if ($coupon === null) {
            $coupon = new WcAdpMergedCoupon($couponCode);
            $coupon->setParts([new ExternalWcCoupon($wcCoupon, [])]);
        }

        return $coupon;
    }

    public static function loadOfCouponCode($couponCode)
    {
        if (is_numeric($couponCode)) {
            $couponCode = (string)$couponCode;
        }

        if (!is_string($couponCode)) {
            return null;
        }

        if ( $couponCode === '' ) {
            return null;
        }

        $storage = InMemoryAdpMergedCouponStorage::getInstance();
        $coupon = $storage->getByCodeOrNull($couponCode);

        if ($coupon === null) {
            $coupon = new WcAdpMergedCoupon($couponCode);

            $wcCoupon = new \WC_Coupon();
            $wcCoupon->set_code($couponCode);
            if ($id = wc_get_coupon_id_by_code($couponCode)) {
                $wcCoupon->set_id($id);
                $dataStore = \WC_Data_Store::load('coupon');
                $dataStore->read($wcCoupon);
            }

            $coupon->setParts([new ExternalWcCoupon($wcCoupon, [])]);
        }

        return $coupon;
    }

    public static function store(WcAdpMergedCoupon $wcAdpMergedCoupon)
    {
        InMemoryAdpMergedCouponStorage::getInstance()->insertOrUpdate($wcAdpMergedCoupon);
    }
}
