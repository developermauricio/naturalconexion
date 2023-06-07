<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;

defined('ABSPATH') or exit;

class OptionsMenu
{
    const KEY = CategoryBulkTableThemeProperties::KEY . "-table";
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
    public $isShowFixedDiscountColumn;

    /**
     * @var bool
     */
    public $isShowFooter;
}
