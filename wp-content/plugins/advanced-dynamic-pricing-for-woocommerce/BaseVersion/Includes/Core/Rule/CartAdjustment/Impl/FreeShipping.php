<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Impl;

use ADP\BaseVersion\Includes\Core\Cart\ShippingAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustmentsLoader;

defined('ABSPATH') or exit;

class FreeShipping extends AbstractCartAdjustment implements CartAdjustment
{
    public static function getType()
    {
        return 'free__shipping';
    }

    public static function getLabel()
    {
        return __('Set zero cost for all shipping methods', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'cart_adjustments/empty.php';
    }

    public static function getGroup()
    {
        return CartAdjustmentsLoader::GROUP_SHIPPING;
    }

    public function __construct()
    {
        $this->amountIndexes = array();
    }

    public function isValid()
    {
        return true;
    }

    public function applyToCart($rule, $cart)
    {
        $context = $cart->getContext()->getGlobalContext();
        $cart->addShippingAdjustment(
            new ShippingAdjustment($context, ShippingAdjustment::TYPE_FREE, 0, $rule->getId())
        );
    }
}
