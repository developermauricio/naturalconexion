<?php

namespace ADP\BaseVersion\Includes\Core\Cart\Coupon;

class WcCouponExternal
{
    private $wcCoupon;

    public function __construct(\WC_Coupon $wcCoupon)
    {
        $this->wcCoupon = $wcCoupon;
    }

    /**
     * @return \WC_Coupon
     */
    public function getWcCoupon(): \WC_Coupon
    {
        return $this->wcCoupon;
    }

    public function getCode(): string
    {
        return $this->wcCoupon->get_code("edit");
    }
}
