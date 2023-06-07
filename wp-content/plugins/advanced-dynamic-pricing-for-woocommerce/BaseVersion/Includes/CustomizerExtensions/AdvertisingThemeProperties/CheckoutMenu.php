<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

defined('ABSPATH') or exit;

class CheckoutMenu
{
    const KEY = AdvertisingThemeProperties::KEY . "-checkout";

    /**
     * @var bool
     */
    public $isEnableAmountSaved;

    /**
     * @var string
     */
    public $positionAmountSavedAction;
}
