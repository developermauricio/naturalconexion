<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces;

defined('ABSPATH') or exit;

interface TimeRangeCondition
{
    const TIME_RANGE_KEY = 'time_range';

    /**
     * @param string|null $timeRange
     */
    public function setTimeRange($timeRange);

    /**
     * @return string|null
     */
    public function getTimeRange();
}
