<?php

namespace ADP\BaseVersion\Includes\WC\WcAdpMergedCoupon;

use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\InternalWcCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\IMergeAdpCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\IMergeCoupon;
use ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon\RuleTriggerCoupon;

class WcAdpMergedCoupon
{
    const COUPON_DISCOUNT_TYPE = "adp_discount";

    /** @var string */
    private $code;

    /** @var float|null */
    private $amount;

    /** @var array<int, IMergeCoupon> */
    private $parts;

    public function __construct(string $code)
    {
        $this->code = $code;
        $this->amount = null;
        $this->parts = [];
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /** @return float|null */
    public function getAmount()
    {
        return $this->amount;
    }

    /** @param float|null $amount */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }

    /** @return IMergeCoupon[]|array */
    public function getParts(): array
    {
        return $this->parts;
    }

    /** @param IMergeCoupon[]|array $parts */
    public function setParts(array $parts)
    {
        $newParts = [];
        $ruleTriggerCoupons = [];
        foreach ($parts as $part) {
            if (!($part instanceof IMergeCoupon)) {
                continue;
            }

            if ($part instanceof RuleTriggerCoupon) {
                if (!isset($ruleTriggerCoupons[$part->ruleId()])) {
                    $newParts[] = $part;
                    $ruleTriggerCoupons[$part->ruleId()] = $part;
                }
            } else {
                $newParts[] = $part;
            }
        }

        $this->parts = array_values($newParts);
    }

    public function hasAdpPart(): bool
    {
        foreach ($this->parts as $part) {
            if ($part instanceof IMergeAdpCoupon) {
                return true;
            }
        }

        return false;
    }

    public function hasRuleTriggerPart(): bool
    {
        foreach ($this->parts as $part) {
            if ($part instanceof RuleTriggerCoupon) {
                return true;
            }
        }

        return false;
    }

    public function hasOnlyRuleTriggerParts(): bool
    {
        $parts = array_filter($this->parts, function ($part) {
            return !($part instanceof RuleTriggerCoupon);
        });

        return count($parts) === 0;
    }

    public function hasOnlyInternalWcCouponPart(): bool
    {
        $parts = array_filter($this->parts, function ($part) {
            return !($part instanceof RuleTriggerCoupon);
        });

        return count($parts) === 1 && $parts[0] instanceof InternalWcCoupon;
    }
}
