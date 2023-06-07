<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\CartAdjUsingCollection;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSetCollection;

defined('ABSPATH') or exit;

class CartAdjustmentsApplyStrategy
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
     */
    public function applyToCart($cart)
    {
        $cartAdjustments = $this->rule->getCartAdjustments();

        if (count($cartAdjustments) === 0) {
            return;
        }

        foreach ($cartAdjustments as $cartAdjustment) {
            $cartAdjustment->applyToCart($this->rule, $cart);
        }
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $items
     */
    public function applyToCartWithItems($cart, $items)
    {
        $cartAdjustments = $this->rule->getCartAdjustments();

        if (count($cartAdjustments) === 0) {
            return;
        }

        foreach ($cartAdjustments as $cartAdjustment) {
            if ($cartAdjustment instanceof CartAdjUsingCollection) {
                $cartAdjustment->applyToCartWithItems($this->rule, $cart, $items);
            } else {
                $cartAdjustment->applyToCart($this->rule, $cart);
            }
        }
    }

    /**
     * @param Cart $cart
     * @param CartSetCollection $sets
     */
    public function applyToCartWithSets($cart, $sets)
    {
        $cartAdjustments = $this->rule->getCartAdjustments();

        if (count($cartAdjustments) === 0) {
            return;
        }

        foreach ($cartAdjustments as $cartAdjustment) {
            if ($cartAdjustment instanceof CartAdjUsingCollection) {
                $cartAdjustment->applyToCartWithSets($this->rule, $cart, $sets);
            } else {
                $cartAdjustment->applyToCart($this->rule, $cart);
            }
        }
    }
}
