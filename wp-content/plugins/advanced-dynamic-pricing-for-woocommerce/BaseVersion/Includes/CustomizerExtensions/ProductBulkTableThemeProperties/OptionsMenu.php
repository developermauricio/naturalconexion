<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;

defined('ABSPATH') or exit;

class OptionsMenu
{
    const KEY = ProductBulkTableThemeProperties::KEY . "-table";
    const LAYOUT_VERBOSE = "verbose";
    const LAYOUT_SIMPLE = "simple";

    /**
     * @var string
     */
    public $tableLayout;

    /**
     * @var string
     */
    public $tablePositionAction;

    /**
     * @var bool
     */
    public $isShowDiscountedPrice;

    /**
     * @var bool
     */
    public $isShowFixedDiscountColumn;

    /**
     * @var bool
     */
    public $isShowFooter;
}
