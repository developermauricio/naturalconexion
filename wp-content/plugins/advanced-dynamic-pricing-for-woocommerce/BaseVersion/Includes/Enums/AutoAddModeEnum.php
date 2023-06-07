<?php

namespace ADP\BaseVersion\Includes\Enums;

/**
 * @method static self USE_PRODUCT_FROM_FILTER()
 * @method static self AUTO_ADD_PRODUCTS()
 * @method static self AUTO_ADD_PRODUCTS_ROTATION()
 */
class AutoAddModeEnum extends BaseEnum
{
    const __default = self::USE_PRODUCT_FROM_FILTER;

    const USE_PRODUCT_FROM_FILTER = 'use_product_from_filter';
    const AUTO_ADD_PRODUCTS = 'auto_add_products';
    const AUTO_ADD_PRODUCTS_ROTATION = 'auto_add_products_in_rotation';

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
