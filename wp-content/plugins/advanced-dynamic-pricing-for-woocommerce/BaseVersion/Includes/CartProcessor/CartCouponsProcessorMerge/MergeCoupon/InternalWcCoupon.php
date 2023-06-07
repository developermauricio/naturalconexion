<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon;

class InternalWcCoupon implements IMergeCoupon, IMergeWcCoupon, IMergeAdpCoupon
{
    /** @var \WC_Coupon */
    private $wcCoupon;

    /** @var int */
    private $ruleId;

    /** @var array<string, float> */
    private $totalsPerItem;

    public function __construct(\WC_Coupon $wcCoupon, int $ruleId, array $totalsPerItem)
    {
        $this->wcCoupon = $wcCoupon;
        $this->ruleId = $ruleId;
        $this->totalsPerItem = $totalsPerItem;
    }

    public function wcCoupon(): \WC_Coupon
    {
        return $this->wcCoupon;
    }

    public function ruleId(): int
    {
        return $this->ruleId;
    }

    public function totalsPerItem(): array
    {
        return $this->totalsPerItem;
    }
}
