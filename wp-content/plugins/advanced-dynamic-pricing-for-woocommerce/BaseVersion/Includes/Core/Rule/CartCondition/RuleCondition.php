<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\IObjectInternationalization;

defined('ABSPATH') or exit;

interface RuleCondition
{
    public function __construct();

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function check($cart);

    /** @return array|null */
    public function getInvolvedCartItems();

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function match($cart);

    /**
     * @return bool
     */
    public function hasProductDependency();

    /**
     * @return array
     */
    public function getProductDependency();

    /**
     * Compatibility with currency plugins
     *
     * @param float $rate
     */
    public function multiplyAmounts($rate);

    public function translate(IObjectInternationalization $oi);

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
