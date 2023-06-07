<?php

namespace ADP\BaseVersion\Includes\Debug\Collectors;

use ADP\BaseVersion\Includes\PriceDisplay\Processor;
use ADP\BaseVersion\Includes\PriceDisplay\WcProductProcessor\IWcProductProcessor;

defined('ABSPATH') or exit;

class Products
{
    /**
     * @var IWcProductProcessor
     */
    protected $processor;

    /**
     * @param $listener IWcProductProcessor
     */
    public function __construct($listener)
    {
        $this->processor = $listener;
    }

    /**
     * @return array
     */
    public function collect()
    {
        return $this->processor->getListener()->getTotals();
    }

}
