<?php

namespace ADP\BaseVersion\Includes\LoadStrategies;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

interface LoadStrategy
{
    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null);

    public function start();
}
