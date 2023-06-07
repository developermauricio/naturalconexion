<?php

namespace ADP\BaseVersion\Includes\Core\Cart\Coupon;

defined('ABSPATH') or exit;

class WcCouponCart implements CouponInterface
{
    /**
     * @var integer
     */
    protected $ruleId;

    /**
     * @var float
     */
    protected $value;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string Original coupon name
     */
    protected $label;

    /**
     * @param string $code
     * @param int    $ruleId
     */
    public function __construct($code, $ruleId)
    {
        $this->code   = wc_format_coupon_code($code);
        $this->ruleId = $ruleId;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        return;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return 0.0;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->code;
    }

    public function setMaxDiscount($amount)
    {
    }

    public function getMaxDiscount()
    {
        return 0.0;
    }

    public function isMaxDiscountDefined()
    {
        return false;
    }
}
