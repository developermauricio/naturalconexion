<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor\Exceptions;

use Exception;

defined('ABSPATH') or exit;

class RuleExecutionTimeout extends Exception
{
    public function errorMessage()
    {
        return __('Rule execution timeout', 'advanced-dynamic-pricing-for-woocommerce');
    }

}
