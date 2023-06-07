<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Limit;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

interface RuleLimit
{

    public function __construct();

    /**
     * @param Rule $rule
     * @param Cart $cart
     *
     * @return bool
     */
    public function check($rule, $cart);

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
