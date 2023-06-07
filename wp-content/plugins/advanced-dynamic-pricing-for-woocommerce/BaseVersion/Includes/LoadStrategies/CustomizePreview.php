<?php

namespace ADP\BaseVersion\Includes\LoadStrategies;

use ADP\BaseVersion\Includes\Context;
use ADP\Factory;

defined('ABSPATH') or exit;

class CustomizePreview implements LoadStrategy
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function start()
    {
        /** @var $strategy ClientCommon */
        $clientCommonStrategy = Factory::get("LoadStrategies_ClientCommon", $this->context);
        $clientCommonStrategy->start();

        /** @var $strategy AdminAjax */
        $ajaxStrategy = Factory::get("LoadStrategies_AdminAjax", $this->context);
        $ajaxStrategy->start();
    }
}
