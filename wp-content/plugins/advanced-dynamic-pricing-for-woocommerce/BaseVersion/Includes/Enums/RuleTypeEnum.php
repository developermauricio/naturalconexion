<?php

namespace ADP\BaseVersion\Includes\Enums;

/**
 * @method static self PERSISTENT()
 * @method static self COMMON()
 * @method static self EXCLUSIVE()
 */
class RuleTypeEnum extends BaseEnum
{
    const __default = self::PERSISTENT;

    const PERSISTENT = 'persistent';
    const COMMON     = 'common';
    const EXCLUSIVE  = 'exclusive';

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
