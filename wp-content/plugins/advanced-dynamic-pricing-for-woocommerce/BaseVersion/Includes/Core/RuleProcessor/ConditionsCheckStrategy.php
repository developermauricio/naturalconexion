<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

class ConditionsCheckStrategy
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
        $conditions = $this->rule->getConditions();

        if (count($conditions) === 0) {
            return true;
        }

        $relationship = $this->rule->getConditionsRelationship();
        $result       = false;

        foreach ($conditions as $condition) {
            if ($condition->check($cart)) {
                // check_conditions always true if relationship not 'and' and at least one condition checked
                $result = true;
            } elseif ('and' == $relationship) {
                return false;
            }
        }

        return $result;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function match($cart)
    {
        $conditions = $this->rule->getConditions();

        if (count($conditions) === 0) {
            return true;
        }

        $relationship = $this->rule->getConditionsRelationship();
        $result       = false;

        foreach ($conditions as $condition) {
            if ($condition->match($cart)) {
                // check_conditions always true if relationship not 'and' and at least one condition checked
                $result = true;
            } elseif ('and' == $relationship) {
                return false;
            }
        }

        return $result;
    }
}
