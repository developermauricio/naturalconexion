<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon;

class ExternalWcCoupon implements IMergeCoupon, IMergeWcCoupon
{
    /** @var \WC_Coupon */
    private $wcCoupon;

    /** @var array<string, float> */
    private $totalsPerItem;

    public function __construct(\WC_Coupon $wcCoupon, array $totalsPerItem)
    {
        $this->wcCoupon = $wcCoupon;
        $this->totalsPerItem = $totalsPerItem;
    }

    public function wcCoupon(): \WC_Coupon
    {
        return $this->wcCoupon;
    }

    public function totalsPerItem(): array
    {
        return $this->totalsPerItem;
    }
}
