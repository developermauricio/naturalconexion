<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use ADP\BaseVersion\Includes\Enums\GiftModeEnum;

defined('ABSPATH') or exit;

class Gift
{
    /**
     * @var array<int, GiftChoice>
     */
    protected $choices;

    /**
     * @var float
     */
    protected $qty;

    /**
     * @var GiftModeEnum
     */
    protected $mode;

    public function __construct()
    {
        $this->choices = array();
        $this->qty     = floatval(0);
        $this->mode    = new GiftModeEnum();
    }

    /**
     * @param array<int,GiftChoice> $choices
     *
     * @return self
     */
    public function setChoices($choices)
    {
        $this->choices = array_filter(
            $choices,
            function ($choice) {
                return $choice instanceof GiftChoice && $choice->isValid();
            }
        );

        return $this;
    }

    /**
     * @return array<int,GiftChoice>
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
     * @return bool
     */
    public function isAllowToSelect()
    {
        return $this->mode->equals(GiftModeEnum::ALLOW_TO_CHOOSE())
               || $this->mode->equals(GiftModeEnum::ALLOW_TO_CHOOSE_FROM_PRODUCT_CAT())
               || $this->mode->equals(GiftModeEnum::REQUIRE_TO_CHOOSE())
               || $this->mode->equals(GiftModeEnum::REQUIRE_TO_CHOOSE_FROM_PRODUCT_CAT());
    }

    /**
     * @param GiftModeEnum $mode
     */
    public function setMode($mode)
    {
        if ($mode instanceof GiftModeEnum) {
            $this->mode = $mode;
        }
    }

    /**
     * @return GiftModeEnum
     */
    public function getMode()
    {
        return $this->mode;
    }
}
