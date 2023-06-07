<?php

namespace ADP\BaseVersion\Includes\Debug\Collectors;

use ADP\BaseVersion\Includes\CartProcessor\CartProcessor;

defined('ABSPATH') or exit;

class WcCart
{
    /**
     * @var CartProcessor
     */
    protected $processor;

    /**
     * @param $processor CartProcessor
     */
    public function __construct($processor)
    {
        $this->processor = $processor;
    }

    /**
     * @return array
     */
    public function collect()
    {
        return $this->processor->getListener()->getTotals();
    }
}
