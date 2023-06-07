<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSetCollection;

defined('ABSPATH') or exit;

interface CartAdjUsingCollection
{
    /**
     * @param Rule $rule
     * @param Cart $cart
     * @param CartItemsCollection $itemsCollection
     */
    public function applyToCartWithItems($rule, $cart, $itemsCollection);

    /**
     * @param Rule $rule
     * @param Cart $cart
     * @param CartSetCollection $setCollection
     */
    public function applyToCartWithSets($rule, $cart, $setCollection);
}
