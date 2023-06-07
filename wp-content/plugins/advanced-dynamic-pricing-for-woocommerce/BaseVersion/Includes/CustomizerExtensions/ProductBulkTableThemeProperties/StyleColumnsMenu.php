<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

defined('ABSPATH') or exit;

class StyleColumnsMenu
{
    const KEY = ProductBulkTableThemeProperties::KEY . "-table_columns";

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
    public $discountedPriceColumnTitle;

    /**
     * @var string
     */
    public $headerBackgroundColor;
}
