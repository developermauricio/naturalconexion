<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

class CustomerTax
{
    /**
     * @var int
     */
    protected $ruleId;

    /**
     * @var boolean
     */
    protected $withTax;

    public function __construct(bool $withTax, int $ruleId)
    {
        $this->withTax = $withTax;
        $this->ruleId = $ruleId;
    }

    public function getRuleId(): int
    {
        return $this->ruleId;
    }

    /**
     * @return bool
     */
    public function isWithTax(): bool
    {
        return $this->withTax;
    }
}
