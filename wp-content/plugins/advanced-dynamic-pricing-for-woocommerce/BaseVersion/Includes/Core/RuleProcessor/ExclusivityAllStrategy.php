<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

class ExclusivityAllStrategy
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
     * @param array<int,CartItem> $items
     *
     * @return array<int,CartItem>
     */
    public function makeAffectedItemAsExclusive($items)
    {
        foreach ($items as $item) {
            $item->addAttr($item::ATTR_IMMUTABLE);
        }

        return $items;
    }
}
