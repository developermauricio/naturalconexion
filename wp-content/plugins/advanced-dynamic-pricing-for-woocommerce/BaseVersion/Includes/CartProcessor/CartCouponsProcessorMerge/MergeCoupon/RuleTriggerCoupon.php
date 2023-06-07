<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon;

class RuleTriggerCoupon implements IMergeCoupon, IMergeAdpCoupon
{
    /** @var int */
    private $ruleId;

    public function __construct(int $ruleId)
    {
        $this->ruleId = $ruleId;
    }

    public function ruleId(): int
    {
        return $this->ruleId;
    }

    public function totalsPerItem(): array
    {
        return [];
    }
}
