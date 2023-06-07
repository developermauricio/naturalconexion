<?php

namespace ADP\BaseVersion\Includes\Enums;

/**
 * @method static self TYPE_ADP_FREE_SHIPPING()
 */
class ShippingMethodEnum extends BaseEnum
{
    const __default = self::TYPE_ADP_FREE_SHIPPING;

    const TYPE_ADP_FREE_SHIPPING = 'adp_free_shipping';

    /**
     * @param self $variable
     *
     * @return bool
     */
    public function equals($variable)
    {
        return parent::equals($variable);
    }
}
