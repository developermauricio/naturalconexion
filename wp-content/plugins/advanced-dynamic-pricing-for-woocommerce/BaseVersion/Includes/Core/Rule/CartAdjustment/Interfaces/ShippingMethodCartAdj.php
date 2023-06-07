<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces;

defined('ABSPATH') or exit;

interface ShippingMethodCartAdj
{
    const SHIPPING_CARTADJ_METHOD = 'shipping_cartadj_method';

    /**
     * @param string $shippingCartAdjMethod
     */
    public function setShippingCartAdjMethod($shippingCartAdjMethod);

    public function getShippingCartAdjMethod();
}
