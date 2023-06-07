<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use ADP\BaseVersion\Includes\Enums\AutoAddModeEnum;

defined('ABSPATH') or exit;

class AutoAdd
{
    /**
     * @var array<int, AutoAddChoice>
     */
    protected $choices;

    /**
     * @var float
     */
    protected $qty;

    /**
     * @var AutoAddModeEnum
     */
    protected $mode;

    /**
     * @var Discount
     */
    protected $discount;

    public function __construct()
    {
        $this->choices = array();
        $this->qty     = floatval(0);
        $this->mode    = new AutoAddModeEnum();
    }

    /**
     * @param array<int,AutoAddChoice> $choices
     *
     * @return self
     */
    public function setChoices($choices)
    {
        $this->choices = array_filter(
            $choices,
            function ($choice) {
                return $choice instanceof AutoAddChoice && $choice->isValid();
            }
        );

        return $this;
    }

    /**
     * @return array<int,AutoAddChoice>
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param float $qty
     *
     * @return self
     */
    public function setQty($qty)
    {
        if (is_numeric($qty)) {
            $qty = floatval($qty);
            if ($qty >= 0) {
                $this->qty = floatval($qty);
            }
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ( ! isset($this->choices, $this->qty)) {
            return false;
        }

        return $this->qty > floatval(0);
    }

    /**
     * @param AutoAddModeEnum $mode
     */
    public function setMode($mode)
    {
        if ($mode instanceof AutoAddModeEnum) {
            $this->mode = $mode;
        }
    }

    /**
     * @return AutoAddModeEnum
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return Discount
     */
    public function getDiscount(): Discount
    {
        return $this->discount;
    }

    /**
     * @param Discount $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return string|null
     */
    public function getDiscountType()
    {
        if ($this->discount->getType() === Discount::TYPE_AMOUNT) {
            return 'discount__amount';
        } elseif ($this->discount->getType() === Discount::TYPE_PERCENTAGE) {
            return 'discount__percentage';
        } elseif ($this->discount->getType() === Discount::TYPE_FIXED_VALUE) {
            return 'price__fixed';
        }
        return null;
    }

    /**
     * @return float
     */
    public function getDiscountValue() {
        return $this->discount->getValue();
    }
}
