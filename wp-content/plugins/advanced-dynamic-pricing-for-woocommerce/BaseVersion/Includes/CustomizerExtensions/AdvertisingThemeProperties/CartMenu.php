<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

defined('ABSPATH') or exit;

class CartMenu
{
    const KEY = AdvertisingThemeProperties::KEY . "-cart";

    /**
     * @var bool
     */
    public $isEnableAmountSaved;

    /**
     * @var string
     */
    public $positionAmountSavedAction;
}
