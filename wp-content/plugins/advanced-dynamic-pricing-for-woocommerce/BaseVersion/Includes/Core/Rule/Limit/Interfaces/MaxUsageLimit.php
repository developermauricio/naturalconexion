<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Limit\Interfaces;

defined('ABSPATH') or exit;

interface MaxUsageLimit
{
    const MAX_USAGE_KEY = 'max_usage';

    /**
     * @param string|int $maxUsage
     */
    public function setMaxUsage($maxUsage);

    /**
     * @return int
     */
    public function getMaxUsage();
}
