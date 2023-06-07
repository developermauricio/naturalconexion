<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

defined('ABSPATH') or exit;

class DisableWcCouponsCart implements CouponsAdjustment
{
    /**
     * @var integer
     */
    protected $ruleId;

    /**
     * @var string
     */
    protected $code;

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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }
}
