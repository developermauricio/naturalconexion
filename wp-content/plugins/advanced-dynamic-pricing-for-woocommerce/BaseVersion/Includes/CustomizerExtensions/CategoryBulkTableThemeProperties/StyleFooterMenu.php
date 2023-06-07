<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleFooterMenu
{
    const KEY = CategoryBulkTableThemeProperties::KEY . "-table_footer";

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
}
