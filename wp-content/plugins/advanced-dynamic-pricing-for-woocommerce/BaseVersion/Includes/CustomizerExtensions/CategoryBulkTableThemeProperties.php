<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions;

use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties\OptionsMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties\StyleBodyMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties\StyleColumnsMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties\StyleFooterMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties\StyleHeaderMenu;

defined('ABSPATH') or exit;

class CategoryBulkTableThemeProperties
{
    const KEY = "wdp_category_bulk_table";
    const SHORT_KEY = "category";

    /**
     * @var OptionsMenu
     */
    public $options;

    /**
     * @var StyleHeaderMenu
     */
    public $styleHeader;

    /**
     * @var StyleColumnsMenu
     */
    public $styleColumns;

    /**
     * @var StyleBodyMenu
     */
    public $styleBody;

    /**
     * @var StyleFooterMenu
     */
    public $styleFooter;

    public function __construct()
    {
        $this->options      = new OptionsMenu();
        $this->styleHeader  = new StyleHeaderMenu();
        $this->styleColumns = new StyleColumnsMenu();
        $this->styleBody    = new StyleBodyMenu();
        $this->styleFooter  = new StyleFooterMenu();
    }
}
