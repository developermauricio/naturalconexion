<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Enums;

use ADP\BaseVersion\Includes\Enums\BaseEnum;

defined('ABSPATH') or exit;

/**
 * @method static self MEASURE_QTY()
 * @method static self MEASURE_SUM()
 * @method static self MEASURE_WEIGHT()
 */
class ProductMeasure extends BaseEnum{
    const __default = self::MEASURE_QTY;

    const MEASURE_QTY    = 'qty';
    const MEASURE_SUM    = 'amount';
    const MEASURE_WEIGHT = 'weight';

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
