<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

defined('ABSPATH') or exit;

class DisableAllWcCouponsCart implements CouponsAdjustment
{
    /**
     * @var integer
     */
    protected $ruleId;

    /**
     * @param int    $ruleId
     */
    public function __construct($ruleId)
    {
        $this->ruleId = $ruleId;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }
}
