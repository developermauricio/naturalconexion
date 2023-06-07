<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleBodyMenu
{
    const KEY = CategoryBulkTableThemeProperties::KEY . "-table_body";

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
     * @var string
     */
    public $backgroundColor;
}
