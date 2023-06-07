<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;

defined('ABSPATH') or exit;

class EditOrderMenu
{
    const KEY = AdvertisingThemeProperties::KEY . "-edit-order";

    /**
     * @var bool
     */
    public $isEnableAmountSaved;

    /**
     * @var bool
     */
    public $positionAmountSavedAction;
}
