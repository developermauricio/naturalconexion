<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces;

defined('ABSPATH') or exit;

interface DateTimeComparisonCondition
{
    const COMPARISON_DATETIME_KEY = 'comparison_datetime';
    const COMPARISON_DATETIME_METHOD_KEY = 'comparison_datetime_method';

    /**
     * @param string|null $comparisonDatetime
     */
    public function setComparisonDateTime($comparisonDatetime);

    /**
     * @return string|null
     */
    public function getComparisonDateTime();

    /**
     * @param string|null $comparisonMethod
     */
    public function setDateTimeComparisonMethod($comparisonMethod);

    /**
     * @return string|null
     */
    public function getDateTimeComparisonMethod();
}
