<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleBodyMenu
{
    const KEY = ProductBulkTableThemeProperties::KEY . "-table_body";

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
