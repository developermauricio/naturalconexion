<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

class LimitsCheckStrategy
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @param Rule $rule
     */
    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function check($cart)
    {
        $limits = $this->rule->getLimits();

        if (count($limits) === 0) {
            return true;
        }

        foreach ($limits as $limit) {
            if ( ! $limit->check($this->rule, $cart)) {
                return false;
            }
        }

        return true;
    }
}
