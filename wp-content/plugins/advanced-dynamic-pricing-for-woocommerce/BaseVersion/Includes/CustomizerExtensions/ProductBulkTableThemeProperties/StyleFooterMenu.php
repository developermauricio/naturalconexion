<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleFooterMenu
{
    const KEY = ProductBulkTableThemeProperties::KEY . "-table_footer";

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
