<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleHeaderMenu
{
    const KEY = ProductBulkTableThemeProperties::KEY . "-table_header";

    /**
     * @var bool
     */
    public $isBold;

    /**
     * @var bool
     */
    public $isItalic;

    /**
     * @var string
     */
    public $textAlign;

    /**
     * @var string
     */
    public $textColor;

    /**
     * @var bool
     */
    public $isUseMessageAsTitle;

    /**
     * @var string
     */
    public $headerBulkTitle;

    /**
     * @var string
     */
    public $headerTierTitle;
}
