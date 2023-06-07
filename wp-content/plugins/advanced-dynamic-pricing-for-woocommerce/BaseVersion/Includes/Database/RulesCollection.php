<?php

namespace ADP\BaseVersion\Includes\Database;

use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use Exception;

defined('ABSPATH') or exit;

class RulesCollection
{
    /** @var array<int,Rule> */
    protected $rules;

    /**
     * @param array<int,Rule> $rules
     */
    public function __construct($rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return array<int,Rule>
     */
    public function getRules()
    {
        return $this->rules;
    }


    public function count()
    {
        return count($this->rules);
    }

    public function isEmpty()
    {
        return empty($this->rules);
    }

    /**
     * @return Rule|null
     */
    public function getFirst()
    {
        return count($this->rules) ? reset($this->rules) : null;
    }

    protected function getRule($pos)
    {
        $rule = null;

        if (isset($this->rules[$pos])) {
            $rule = $this->rules[$pos];
        } else {
            throw new Exception('Invalid pos number for collection of rules');
        }

        return $rule;
    }

    public function getExact($ruleIds)
    {
        $filtered_rules = array();
        $ruleIds        = (array)$ruleIds;

        foreach ($this->rules as $rule) {
            /**
             * @var $rule Rule
             */
            if (in_array($rule->getId(), $ruleIds)) {
                $filtered_rules[] = $rule;
            }
        }

        return new self($filtered_rules);
    }

    public function withRangeDiscounts()
    {
        $filtered_rules = array();
        foreach ($this->rules as $rule) {
            if ($rule instanceof SingleItemRule) {
                if ($rule->getProductRangeAdjustmentHandler()) {
                    $filtered_rules[] = $rule;
                }
            } elseif ($rule instanceof PackageRule) {
                if ($rule->getProductRangeAdjustmentHandler()) {
                    $filtered_rules[] = $rule;
                }
            }
        }

        return new self($filtered_rules);
    }
}
