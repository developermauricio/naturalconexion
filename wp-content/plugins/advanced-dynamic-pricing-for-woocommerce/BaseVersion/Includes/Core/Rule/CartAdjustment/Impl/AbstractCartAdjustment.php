<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Impl;

defined('ABSPATH') or exit;

abstract class AbstractCartAdjustment
{
    protected $amountIndexes;

    /**
     * @param float $rate
     */
    public function multiplyAmounts($rate)
    {
        foreach ($this->amountIndexes as $index) {
            /**
             * @var string $index
             */
            if (isset($this->$index)) {
                $amount       = (float)$this->$index;
                $this->$index = $amount * $rate;
            }
        }
    }
}
