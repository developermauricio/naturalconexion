<?php

namespace ADP\BaseVersion\Includes\Enums;

/**
 * @method static self PRODUCT()
 * @method static self CLONE_ADJUSTED()
 */
class AutoAddChoiceTypeEnum extends BaseEnum
{
    const __default = self::PRODUCT;

    const PRODUCT = 'product';
    const CLONE_ADJUSTED = 'clone';

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
