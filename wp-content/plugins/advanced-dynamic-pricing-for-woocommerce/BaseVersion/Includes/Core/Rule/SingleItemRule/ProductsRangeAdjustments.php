<?php

namespace ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RangeDiscount;
use Exception;

defined('ABSPATH') or exit;

class ProductsRangeAdjustments
{
    const TYPE_BULK = 'bulk';
    const TYPE_TIER = 'tier';

    const AVAILABLE_TYPES = array(
        self::TYPE_BULK,
        self::TYPE_TIER,
    );

    /**
     * @var string
     */
    protected $type;

    const GROUP_BY_DEFAULT = 'not';
    const GROUP_BY_PRODUCT = 'product';
    const GROUP_BY_VARIATION = 'variation';
    const GROUP_BY_CART_POSITIONS = 'cart_pos';

    // degenerated aggregations
    const GROUP_BY_ALL_ITEMS_IN_CART = 'total_qty_in_cart';
    const GROUP_BY_PRODUCT_CATEGORIES = 'product_categories';
    const GROUP_BY_PRODUCT_SELECTED_CATEGORIES = 'product_selected_categories';
    const GROUP_BY_PRODUCT_SELECTED_PRODUCTS = 'selected_products';
    const GROUP_BY_META_DATA = 'meta_data';

    const AVAILABLE_GROUP_BY = array(
        self::GROUP_BY_PRODUCT,
        self::GROUP_BY_VARIATION,
        self::GROUP_BY_CART_POSITIONS,
    );


    /**
     * @var string
     */
    protected $groupBy;

    /**
     * Coupon or Fee
     *
     * @var bool
     */
    protected $replaceAsCartAdjustment;

    /**
     * @var RangeDiscount[]
     */
    protected $ranges;

    /**
     * @var string
     */
    protected $replaceCartAdjustmentCode;

    /**
     * @var string
     */
    protected $promotionalMessage;

    /**
     * @var int[]
     */
    protected $selectedProductIds;

    /**
     * @var int[]
     */
    protected $selectedCategoryIds;

    /**
     * @param Context $context
     * @param string $type
     * @param string $groupBy
     */
    public function __construct($context, $type, $groupBy)
    {
        if ( ! in_array($type, self::AVAILABLE_TYPES)) {
            $context->handleError(
                new Exception(
                    sprintf(
                        "Item range adjustment type '%s' not supported",
                        $type
                    )
                )
            );
        }

        if ( ! in_array($groupBy, self::AVAILABLE_GROUP_BY)) {
            $context->handleError(
                new Exception(
                    sprintf(
                        "Item range adjustment qty based '%s' not supported",
                        $groupBy
                    )
                )
            );
        }

        $this->type                      = $type;
        $this->groupBy                   = $groupBy;
        $this->replaceAsCartAdjustment   = false;
        $this->replaceCartAdjustmentCode = null;
        $this->selectedProductIds        = array();
        $this->selectedCategoryIds       = array();
    }

    /**
     * @param RangeDiscount $range
     */
    public function addRange($range)
    {
        if ($range instanceof RangeDiscount) {
            $this->ranges[] = $range;
        }
    }

    /**
     * @param array<int,RangeDiscount> $ranges
     */
    public function setRanges($ranges)
    {
        $this->ranges = array();

        foreach ($ranges as $range) {
            $this->addRange($range);
        }
    }

    /**
     * @return array<int,RangeDiscount>
     */
    public function getRanges()
    {
        return $this->ranges;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param bool $replace
     */
    public function setReplaceAsCartAdjustment($replace)
    {
        $this->replaceAsCartAdjustment = boolval($replace);
    }

    /**
     * @return bool
     */
    public function isReplaceWithCartAdjustment()
    {
        return $this->replaceCartAdjustmentCode && $this->replaceAsCartAdjustment;
    }

    /**
     * @param string $code
     */
    public function setReplaceCartAdjustmentCode($code)
    {
        $this->replaceCartAdjustmentCode = (string)$code;
    }

    /**
     * @return string
     */
    public function getReplaceCartAdjustmentCode()
    {
        return $this->replaceCartAdjustmentCode;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return count($this->ranges) > 0;
    }

    /**
     * @param string $promotionalMessage
     */
    public function setPromotionalMessage($promotionalMessage)
    {
        $this->promotionalMessage = $promotionalMessage;
    }

    /**
     * @return string
     */
    public function getPromotionalMessage()
    {
        return $this->promotionalMessage;
    }

    /**
     * @param array<int, int> $selectedProductIds
     */
    public function setSelectedProductIds($selectedProductIds)
    {
        $this->selectedProductIds = $selectedProductIds;
    }

    /**
     * @return array<int, int>
     */
    public function getSelectedProductIds()
    {
        return $this->selectedProductIds;
    }

    /**
     * @param array<int, int> $selectedCategoryIds
     */
    public function setSelectedCategoryIds($selectedCategoryIds)
    {
        $this->selectedCategoryIds = $selectedCategoryIds;
    }

    /**
     * @return array<int, int>
     */
    public function getSelectedCategoryIds()
    {
        return $this->selectedCategoryIds;
    }
}
