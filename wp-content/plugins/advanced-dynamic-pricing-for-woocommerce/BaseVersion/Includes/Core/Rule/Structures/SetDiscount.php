<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use Exception;

defined('ABSPATH') or exit;

class SetDiscount extends Discount
{
    const TYPE_SET_AMOUNT = 'set_fixed_amount';
    const TYPE_SET_FIXED_VALUE = 'set_fixed_value';

    const AVAILABLE_SET_TYPES = array(
        self::TYPE_SET_AMOUNT,
        self::TYPE_PERCENTAGE,
        self::TYPE_SET_FIXED_VALUE,
    );

    public function __construct($context, $type, $value)
    {
        if ( ! in_array($type, self::AVAILABLE_SET_TYPES)) {
            $context->handleError(new Exception(sprintf("Discount type '%s' not supported", $type)));
        }

        $this->type         = $type;
        $this->value        = floatval($value);
        $this->currencyCode = $context->getCurrencyCode();
    }
}
