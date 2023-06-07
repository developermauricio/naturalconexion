<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ProductAll;

defined('ABSPATH') or exit;

trait ProductAllCheck {
    protected $subCondition;

    public function getSubCondition() {
        return $this->subCondition;
    }

    public function setSubCondition($subCondition) {
        $subCondition->setFilterType($this->filterType ?? null);
        $this->subCondition = $subCondition;
    }

    public function match($cart) {
        return $this->subCondition->check($cart);
    }

    public function check($cart) {
        return $this->subCondition->check($cart);
    }

    public function translate($languageCode) {
        $this->subCondition->translate($languageCode);
    }

    public function multiplyAmounts($rate)
    {
        $this->subCondition->multiplyAmounts($rate);
    }
}
