<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleHeaderMenu
{
    const KEY = CategoryBulkTableThemeProperties::KEY . "-table_header";

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
