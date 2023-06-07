<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon;

use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\Builder\CartCouponBuilder;

class CartCoupon implements IMergeCoupon, IMergeAdpCoupon
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

    public function __construct(
        int $ruleId,
        string $type,
        string $code,
        string $label,
        array $totalsPerItem
    ) {
        $this->ruleId = $ruleId;
        $this->type = $type;
        $this->code = $code;
        $this->label = $label;
        $this->totalsPerItem = $totalsPerItem;
    }

    public static function builder()
    {
        return new CartCouponBuilder();
    }

    public function ruleId(): int
    {
        return $this->ruleId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function totalsPerItem(): array
    {
        return $this->totalsPerItem;
    }
}

