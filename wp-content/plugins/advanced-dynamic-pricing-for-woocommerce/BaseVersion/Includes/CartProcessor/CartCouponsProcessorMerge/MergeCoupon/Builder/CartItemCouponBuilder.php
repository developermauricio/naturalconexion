<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\Builder;

use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\CartItemCoupon;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

class CartItemCouponBuilder
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

    public function __construct()
    {
        $this->ruleId = 0;
        $this->type = '';
        $this->code = '';
        $this->label = '';
        $this->affectedCartItem = null;
        $this->totalsPerItem = [];
    }

    public function ruleId(int $ruleId)
    {
        $this->ruleId = $ruleId;

        return $this;
    }

    public function typeItem()
    {
        $this->type = 'item';

        return $this;
    }

    public function typeFreeItem()
    {
        $this->type = 'free_item';

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

    public function affectedCartItem(WcCartItemFacade $affectedCartItem)
    {
        $this->affectedCartItem = $affectedCartItem;

        return $this;
    }

    public function totalsPerItem(array $totalsPerItem)
    {
        $this->totalsPerItem = $totalsPerItem;

        return $this;
    }

    public function build(): CartItemCoupon
    {
        return new CartItemCoupon(
            $this->ruleId,
            $this->type,
            $this->code,
            $this->label,
            $this->affectedCartItem,
            $this->totalsPerItem
        );
    }
}
