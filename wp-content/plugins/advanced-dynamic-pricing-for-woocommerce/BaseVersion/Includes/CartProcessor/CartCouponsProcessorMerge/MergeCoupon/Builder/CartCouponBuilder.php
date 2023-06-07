<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\Builder;

use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\CartCoupon;

class CartCouponBuilder
{
    /** @var int */
    private $ruleId;

    /** @var string */
    private $type;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var array<string, float> */
    private $totalsPerItem;

    public function __construct()
    {
        $this->ruleId = 0;
        $this->type = '';
        $this->code = '';
        $this->label = '';
        $this->totalsPerItem = [];
    }

    public function ruleId(int $ruleId)
    {
        $this->ruleId = $ruleId;

        return $this;
    }

    public function typePercentage()
    {
        $this->type = 'percentage';

        return $this;
    }

    public function typeFixedValue()
    {
        $this->type = 'fixed_value';

        return $this;
    }

    public function code(string $code)
    {
        $this->code = $code;

        return $this;
    }

    public function label(string $label)
    {
        $this->label = $label;

        return $this;
    }

    public function totalsPerItem(array $totalsPerItem)
    {
        $this->totalsPerItem = $totalsPerItem;

        return $this;
    }

    public function build(): CartCoupon
    {
        return new CartCoupon(
            $this->ruleId,
            $this->type,
            $this->code,
            $this->label,
            $this->totalsPerItem
        );
    }
}
