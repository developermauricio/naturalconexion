<?php

namespace ADP\BaseVersion\Includes\Enums;


/**
 * @method static self IN_LIST()
 * @method static self NOT_IN_LIST()
 */
class AutoAddChoiceMethodEnum extends BaseEnum
{
    const __default = self::IN_LIST;

    const IN_LIST = 'in_list';
    const NOT_IN_LIST = 'not_in_list';

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
