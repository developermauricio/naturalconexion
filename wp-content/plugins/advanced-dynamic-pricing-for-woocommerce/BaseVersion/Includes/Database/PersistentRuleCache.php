<?php

namespace ADP\BaseVersion\Includes\Database;

defined('ABSPATH') or exit;

class PersistentRuleCache
{
    /** @var int */
    public $ruleId;

    /** @var array */
    public $additional;

    /** @var array */
    public $conditions;

    /** @var array */
    public $limits;

    /** @var array */
    public $getProducts;

    /** @var float */
    public $price;

    /**
     * @var string
     */
    public $triggerCouponCode;

    /**
     * @param int $rule_id
     * @param array $additional
     * @param array $conditions
     * @param array $limits
     * @param float $price
     */
    public function __construct(
        $rule_id = 0,
        $additional = array(),
        $triggerCouponCode = '',
        $conditions = array(),
        $limits = array(),
        $get_products = array(),
        $price = 0.0
    ) {
        $this->ruleId            = (int)$rule_id;
        $this->additional        = (array)$additional;
        $this->triggerCouponCode = (string)$triggerCouponCode;
        $this->conditions        = (array)$conditions;
        $this->limits            = (array)$limits;
        $this->getProducts       = (array)$get_products;
        $this->price             = (float)$price;
    }

}
