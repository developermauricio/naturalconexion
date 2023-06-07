<?php

namespace ADP\BaseVersion\Includes\CartProcessor\CartCouponsProcessorMerge\MergeCoupon;

interface IMergeCoupon
{
    public function totalsPerItem(): array;
}
