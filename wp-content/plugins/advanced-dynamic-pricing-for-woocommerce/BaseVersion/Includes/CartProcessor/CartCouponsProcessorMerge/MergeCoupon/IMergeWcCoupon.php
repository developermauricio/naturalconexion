<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon;

interface IMergeWcCoupon
{
    public function wcCoupon(): \WC_Coupon;
}
