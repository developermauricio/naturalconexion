<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

defined('ABSPATH') or exit;

class MiniCartMenu
{
    const KEY = AdvertisingThemeProperties::KEY . "-mini-cart";

    /**
     * @var bool
     */
    public $isEnableAmountSaved;

    /**
     * @var string
     */
    public $positionAmountSavedAction;
}
