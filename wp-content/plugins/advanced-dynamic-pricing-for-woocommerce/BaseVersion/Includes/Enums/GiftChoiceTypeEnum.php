<?php

namespace ADP\BaseVersion\Includes\Enums;

/**
 * @method static self PRODUCT()
 * @method static self CATEGORY()
 * @method static self CLONE_ADJUSTED()
 * @method static self CLONE_ADJUSTED_FIRST()
 */
class GiftChoiceTypeEnum extends BaseEnum
{
    const __default = self::PRODUCT;

    const PRODUCT = 'product';
    const CATEGORY = 'product_category';

    const CLONE_ADJUSTED = 'clone';
    const CLONE_ADJUSTED_FIRST = 'clone_first';

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
