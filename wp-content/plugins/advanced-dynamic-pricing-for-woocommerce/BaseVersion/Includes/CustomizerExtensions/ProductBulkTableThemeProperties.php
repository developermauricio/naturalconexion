<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions;

use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties\OptionsMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties\StyleBodyMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties\StyleColumnsMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties\StyleFooterMenu;
use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties\StyleHeaderMenu;

defined('ABSPATH') or exit;

class ProductBulkTableThemeProperties
{
    const KEY = "wdp_product_bulk_table";
    const SHORT_KEY = "product";

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
