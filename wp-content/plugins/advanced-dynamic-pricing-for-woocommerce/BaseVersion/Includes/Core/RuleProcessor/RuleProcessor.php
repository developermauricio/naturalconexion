<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

interface RuleProcessor
{
    /**
     * @param Context|Rule $contextOrRule
     * @param Rule|null $deprecated
     */
    public function __construct($contextOrRule, $deprecated = null);

    /**
     * @return Rule
     */
    public function getRule();

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function applyToCart($cart);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @return float
     */
    public function getLastExecTime();

    /**
     * @return bool
     */
    public function isProductMatched($cart, $product, $checkConditions = false);
}
