<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces;

defined('ABSPATH') or exit;

interface ShippingCartAdj
{
    const SHIPPING_CARTADJ_VALUE = 'shipping_cartadj_value';

    /**
     * @param float|string $shippingCartAdjValue
     */
    public function setShippingCartAdjValue($shippingCartAdjValue);

    /**
     * @return float
     */
    public function getShippingCartAdjValue();
}
