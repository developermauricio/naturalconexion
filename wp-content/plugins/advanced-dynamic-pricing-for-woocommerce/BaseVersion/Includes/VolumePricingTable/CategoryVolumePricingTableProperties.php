<?php

namespace ADP\BaseVersion\Includes\VolumePricingTable;

defined('ABSPATH') or exit;

class CategoryVolumePricingTableProperties
{
    const LAYOUT_SIMPLE = 'simple';
    const LAYOUT_VERBOSE = 'verbose';

    /**
     * @var bool
     */
    public $isUseMessageAsTitle;

    /**
     * @var string
     */
    public $headerBulkTitle;

    /**
     * @var string
     */
    public $headerTierTitle;

    /**
     * @var string
     */
    public $tableLayout;

    /**
     * @var bool
     */
    public $isShowFooter;

    /**
     * @var string
     */
    public $quantityColumnTitle;

    /**
     * @var bool
     */
    public $isShowFixedDiscountColumn;

    /**
     * @var string
     */
    public $discountColumnTitleForFixedPriceRule;

    /**
     * @var string
     */
    public $discountColumnTitle;
}
