<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

interface CartAdjustment
{

    public function __construct();

    /**
     * @param Rule $rule
     * @param Cart $cart
     */
    public function applyToCart($rule, $cart);

    /**
     * Compatibility with currency plugins
     *
     * @param float $rate
     */
    public function multiplyAmounts($rate);

    /**
     * @return string
     */
    public static function getType();

    /**
     * @return string Localized label
     */
    public static function getLabel();

    /**
     * @return string
     */
    public static function getTemplatePath();

    /**
     * @return string
     */
    public static function getGroup();

    /**
     * @return bool
     */
    public function isValid();
}
