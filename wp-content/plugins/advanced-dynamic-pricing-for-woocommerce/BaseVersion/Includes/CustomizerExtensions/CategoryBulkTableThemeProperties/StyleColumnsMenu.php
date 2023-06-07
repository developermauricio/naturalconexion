<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleColumnsMenu
{
    const KEY = CategoryBulkTableThemeProperties::KEY . "-table_columns";

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
    public $quantityColumnTitle;

    /**
     * @var string
     */
    public $discountColumnTitle;

    /**
     * @var string
     */
    public $discountColumnTitleForFixedPriceRule;

    /**
     * @var string
     */
    public $discountedPriceColumnTitleForFixedPriceRule;

    /**
     * @var string
     */
    public $headerBackgroundColor;
}
