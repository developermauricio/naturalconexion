<?php

namespace ADP\BaseVersion\Includes\VolumePricingTable;

defined('ABSPATH') or exit;

class ProductVolumePricingTableProperties
{
    const LAYOUT_SIMPLE = 'simple';
    const LAYOUT_VERBOSE = 'verbose';

    /**
     * @var bool
     */
    public $isSimpleLayoutForcePercentage;

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
    public $discountedPriceColumnTitleForFixedPriceRule;

    /**
     * @var string
     */
    public $discountColumnTitle;

    /**
     * @var bool
     */
    public $isShowDiscountedPrice;

    /**
     * @var string
     */
    public $discountedPriceColumnTitle;
}
