<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Enums;

use ADP\BaseVersion\Includes\Enums\BaseEnum;

defined('ABSPATH') or exit;

/**
 * @method static self ITEM_COST()
 * @method static self ITEM_QTY()
 */
class ProductAdjustmentSplitDiscount extends BaseEnum
{
    const __default = self::ITEM_COST;

    const ITEM_COST = 'cost';
    const ITEM_QTY = 'qty';

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
