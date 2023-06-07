<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon;

use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\Builder\CartItemCouponBuilder;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

class CartItemCoupon implements IMergeCoupon, IMergeAdpCoupon
{
    /** @var int */
    private $ruleId;

    /** @var string */
    private $type;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var WcCartItemFacade */
    private $affectedCartItem;

    /** @var array<string, float> */
    private $totalsPerItem;

    public function __construct(
        int $ruleId,
        string $type,
        string $code,
        string $label,
        WcCartItemFacade $affectedCartItemKey,
        array $totalsPerItem
    ) {
        $this->ruleId = $ruleId;
        $this->type = $type;
        $this->code = $code;
        $this->label = $label;
        $this->affectedCartItem = $affectedCartItemKey;
        $this->totalsPerItem = $totalsPerItem;
    }

    public static function builder()
    {
        return new CartItemCouponBuilder();
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

    public function affectedCartItemKey(): WcCartItemFacade
    {
        return $this->affectedCartItem;
    }

    public function totalsPerItem(): array
    {
        return $this->totalsPerItem;
    }
}
