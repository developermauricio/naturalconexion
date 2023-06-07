<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions;

defined('ABSPATH') or exit;

class ThemeProperties
{
    /**
     * @var ProductBulkTableThemeProperties
     */
    public $productBulkTable;

    /**
     * @var CategoryBulkTableThemeProperties
     */
    public $categoryBulkTable;

    /**
     * @var AdvertisingThemeProperties
     */
    public $advertisingThemeProperties;

    public function __construct()
    {
        $this->productBulkTable           = new ProductBulkTableThemeProperties();
        $this->categoryBulkTable          = new CategoryBulkTableThemeProperties();
        $this->advertisingThemeProperties = new AdvertisingThemeProperties();
    }

    /**
     * @param array $properties
     *
     * @return self
     */
    public static function create(array $properties)
    {
        $obj = new self();

        return $obj;
    }
}
