<?php

namespace ADP\BaseVersion\Includes\Core\Rule;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ProductsAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ProductsRangeAdjustments;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ConditionMessage;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAdd;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAddsCollection;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Filter;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Gift;
use ADP\BaseVersion\Includes\Core\Rule\Structures\GiftsCollection;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RoleDiscount;
use ADP\BaseVersion\Includes\Core\RuleProcessor\SingleItemRuleProcessor;
use Exception;

defined('ABSPATH') or exit;

class SingleItemRule extends BaseRule implements Rule
{
    /**
     * @var array<int,Filter>
     */
    protected $filters;

    /**
     * @var ProductsAdjustment
     */
    protected $productAdjustmentHandler;

    /**
     * @var ProductsRangeAdjustments
     */
    protected $productRangeAdjustmentHandler;

    /**
     * @var GiftsCollection
     */
    protected $itemGiftsCollection;

    /**
     * @var int|float
     */
    protected $itemGiftLimit;

    /**
     * @var bool
     */
    protected $itemGiftsUseProductFromFilter = false;

    /**
     * @var bool
     */
    protected $replaceItemGifts = false;

    /**
     * @var string
     */
    protected $replaceItemGiftsCode = '';

    /**
     * @var AutoAddsCollection
     */
    protected $autoAddsCollection;

    /**
     * @var int|float
     */
    protected $autoAddLimit;

    /**
     * @var bool
     */
    protected $autoAddUseProductFromFilter = false;

    /**
     * @var bool
     */
    protected $replaceAutoAdds = false;

    /**
     * @var string
     */
    protected $replaceAutoAddsCode = '';

    /**
     * @var bool
     */
    protected $autoAddRemoveDisable = false;

    /**
    * @var bool
    */
    protected $autoAddShowAsRecommended = false;

    /**
     * @var string
     */
    protected $autoAddStrategy;

    /**
     * @var float
     */
    protected $autoAddSubtotalDivider;

    /**
     * @var array<int,RoleDiscount>
     */
    protected $roleDiscounts;

    /**
     * @var string
     */
    protected $sortableApplyMode;

    /**
     * @var array
     */
    protected $sortableBlocksPriority;

    /**
     * @var bool
     */
    protected $dontApplyBulkIfRolesMatched;

    /**
     * @var string
     */
    protected $itemGiftStrategy;

    /**
     * @var int
     */
    protected $itemsCountLimit;

    /**
     * @var float
     */
    protected $itemGiftSubtotalDivider;

    /**
     * @var string
     */
    protected $applyFirstTo;

    /**
     * @var float|null
     */
    protected $maxAmountForGifts;

    /**
     * @var ConditionMessage
     */
    protected $conditionMessageHandler;

    const APPLY_FIRST_TO_EXPENSIVE = 'expensive';
    const APPLY_FIRST_TO_CHEAP = 'cheap';
    const APPLY_FIRST_AS_APPEAR = 'appeared';

    const BASED_ON_LIMIT_STRATEGY = 'based_on_limit';
    const BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY = 'based_on_subtotal';
    const BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY = 'based_on_subtotal_inc';

    public function __construct()
    {
        parent::__construct();
        $this->filters             = array();
        $this->itemGiftsCollection = new GiftsCollection($this);
        $this->autoAddsCollection  = new AutoAddsCollection($this);

        $this->sortableApplyMode           = 'consistently';
        $this->sortableBlocksPriority      = array('roles', 'bulk-adjustments');
        $this->dontApplyBulkIfRolesMatched = false;
        $this->itemsCountLimit             = -1;
        $this->applyFirstTo                = self::APPLY_FIRST_AS_APPEAR;

        $this->itemGiftStrategy        = self::BASED_ON_LIMIT_STRATEGY;
        $this->itemGiftLimit           = INF;
        $this->itemGiftSubtotalDivider = null;
        $this->autoAddStrategy         = self::BASED_ON_LIMIT_STRATEGY;
        $this->autoAddLimit            = INF;
        $this->autoAddSubtotalDivider  = null;
        $this->roleDiscounts           = array();
        $this->maxAmountForGifts       = null;
    }

    public function __clone()
    {
        parent::__clone();

        $this->filters = array_map(function ($item) {
            return clone $item;
        }, $this->filters);

        $this->itemGiftsCollection = clone $this->itemGiftsCollection;

        $this->roleDiscounts = array_map(function ($item) {
            return clone $item;
        }, $this->roleDiscounts);

        if ($this->productAdjustmentHandler) {
            $this->productAdjustmentHandler = clone $this->productAdjustmentHandler;
        }

        if ($this->productRangeAdjustmentHandler) {
            $this->productRangeAdjustmentHandler = clone $this->productRangeAdjustmentHandler;
        }

        if ($this->conditionMessageHandler) {
            $this->conditionMessageHandler = clone $this->conditionMessageHandler;
        }
    }

    /**
     * @param Context $context
     *
     * @return SingleItemRuleProcessor
     * @throws Exception
     */
    public function buildProcessor($context)
    {
        return new SingleItemRuleProcessor($context, $this);
    }

    /**
     * @param Filter $filter
     */
    public function addFilter($filter)
    {
        if ($filter instanceof Filter) {
            $this->filters[] = $filter;
        }
    }

    /**
     * @param array<int,Filter> $filters
     */
    public function setFilters($filters)
    {
        $this->filters = array();

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @return array<int,Filter>
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param ProductsAdjustment $handler
     */
    public function installProductAdjustmentHandler($handler)
    {
        if ($handler instanceof ProductsAdjustment) {
            $this->productAdjustmentHandler = $handler;
        }
    }

    /**
     * @param ProductsRangeAdjustments $handler
     */
    public function installProductRangeAdjustmentHandler($handler)
    {
        if ($handler instanceof ProductsRangeAdjustments) {
            $this->productRangeAdjustmentHandler = $handler;
        }
    }

    /**
     * @param ConditionMessage $handler
     */
    public function installConditionMessageHandler($handler)
    {
        if ($handler instanceof ConditionMessage) {
            $this->conditionMessageHandler = $handler;
        }
    }

    /**
     * @return ProductsAdjustment|null
     */
    public function getProductAdjustmentHandler()
    {
        return $this->productAdjustmentHandler;
    }

    /**
     * @return ProductsRangeAdjustments|null
     */
    public function getProductRangeAdjustmentHandler()
    {
        return $this->productRangeAdjustmentHandler;
    }

    /**
     * @return ConditionMessage|null
     */
    public function getConditionMessageHandler()
    {
        return $this->conditionMessageHandler;
    }

    /**
     * @return bool
     */
    public function hasProductAdjustment()
    {
        return isset($this->productAdjustmentHandler) && $this->productAdjustmentHandler->isValid();
    }

    /**
     * @return bool
     */
    public function hasProductRangeAdjustment()
    {
        return isset($this->productRangeAdjustmentHandler) && $this->productRangeAdjustmentHandler->isValid();
    }

    /**
     * @return bool
     */
    public function hasConditionMessage()
    {
        return isset($this->conditionMessageHandler) && $this->conditionMessageHandler->isValid();
    }

    /**
     * @param array<int,Gift> $gifts
     */
    public function setItemGifts($gifts)
    {
        $this->itemGiftsCollection->purge();
        $this->itemGiftsCollection->bulkAdd(...$gifts);
    }

    /**
     * @return GiftsCollection
     */
    public function getItemGiftsCollection()
    {
        return $this->itemGiftsCollection;
    }

    /**
     * @param bool $itemGiftsUseProductFromFilter
     */
    public function setItemGiftsUseProductFromFilter($itemGiftsUseProductFromFilter)
    {
        $this->itemGiftsUseProductFromFilter = $itemGiftsUseProductFromFilter;
    }

    /**
     * @return bool
     */
    public function isItemGiftsUseProductFromFilter()
    {
        return $this->itemGiftsUseProductFromFilter;
    }

    /**
     * @param string $strategy
     */
    public function setItemGiftStrategy($strategy)
    {
        if (
            in_array($strategy,
                array(
                    self::BASED_ON_LIMIT_STRATEGY,
                    self::BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY,
                    self::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY
                )
            )
        ) {
            $this->itemGiftStrategy = $strategy;
        }
    }

    /**
     * @return string
     */
    public function getItemGiftStrategy()
    {
        return $this->itemGiftStrategy;
    }

    /**
     * @param int|float $itemGiftLimit
     */
    public function setItemGiftLimit($itemGiftLimit)
    {
        $this->itemGiftLimit = $itemGiftLimit !== INF ? intval($itemGiftLimit) : INF;
    }

    /**
     * @return int|float
     */
    public function getItemGiftLimit()
    {
        return $this->itemGiftLimit;
    }

    /**
     * @param float $itemGiftSubtotalDivider
     */
    public function setItemGiftSubtotalDivider($itemGiftSubtotalDivider)
    {
        $this->itemGiftSubtotalDivider = floatval($itemGiftSubtotalDivider);
    }

    /**
     * @return float
     */
    public function getItemGiftSubtotalDivider()
    {
        return $this->itemGiftSubtotalDivider;
    }

    /**
     * @return bool
     */
    public function isReplaceItemGifts()
    {
        return $this->replaceItemGifts;
    }

    /**
     * @param bool $replaceItemGifts
     */
    public function setReplaceItemGifts($replaceItemGifts)
    {
        $this->replaceItemGifts = boolval($replaceItemGifts);
    }

    /**
     * @return string
     */
    public function getReplaceItemGiftsCode()
    {
        return $this->replaceItemGiftsCode;
    }

    /**
     * @param string $replaceItemGiftsCode
     */
    public function setReplaceItemGiftsCode($replaceItemGiftsCode)
    {
        $this->replaceItemGiftsCode = $replaceItemGiftsCode;
    }

    /**
     * @param array<int,AutoAdd> $autoAdds
     */
    public function setAutoAdds($autoAdds)
    {
        $this->autoAddsCollection->purge();
        $this->autoAddsCollection->bulkAdd(...$autoAdds);
    }

    /**
     * @return AutoAddsCollection
     */
    public function getAutoAddsCollection()
    {
        return $this->autoAddsCollection;
    }

    /**
     * @param bool $autoAddUseProductFromFilter
     */
    public function setAutoAddUseProductFromFilter($autoAddUseProductFromFilter)
    {
        $this->autoAddUseProductFromFilter = $autoAddUseProductFromFilter;
    }

    /**
     * @return bool
     */
    public function isAutoAddUseProductFromFilter()
    {
        return $this->autoAddUseProductFromFilter;
    }

    /**
     * @param string $strategy
     */
    public function setAutoAddStrategy($strategy)
    {
        if (
            in_array($strategy,
                array(
                    self::BASED_ON_LIMIT_STRATEGY,
                    self::BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY,
                    self::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY
                )
            )
        ) {
            $this->autoAddStrategy = $strategy;
        }
    }

    /**
     * @return string
     */
    public function getAutoAddStrategy()
    {
        return $this->autoAddStrategy;
    }

    /**
     * @param int|float $autoAddLimit
     */
    public function setAutoAddLimit($autoAddLimit)
    {
        $this->autoAddLimit = $autoAddLimit !== INF ? intval($autoAddLimit) : INF;
    }

    /**
     * @return int|float
     */
    public function getAutoAddLimit()
    {
        return $this->autoAddLimit;
    }

    /**
     * @param float $autoAddSubtotalDivider
     */
    public function setAutoAddSubtotalDivider($autoAddSubtotalDivider)
    {
        $this->autoAddSubtotalDivider = floatval($autoAddSubtotalDivider);
    }

    /**
     * @return float
     */
    public function getAutoAddSubtotalDivider()
    {
        return $this->autoAddSubtotalDivider;
    }

    /**
     * @return bool
     */
    public function isReplaceAutoAdds()
    {
        return $this->replaceAutoAdds;
    }

    /**
     * @param bool $replaceAutoAdd
     */
    public function setReplaceAutoAdds($replaceAutoAdd)
    {
        $this->replaceAutoAdds = boolval($replaceAutoAdd);
    }

    /**
     * @return string
     */
    public function getReplaceAutoAddsCode()
    {
        return $this->replaceAutoAddsCode;
    }

    /**
     * @param string $replaceAutoAddsCode
     */
    public function setReplaceAutoAddsCode($replaceAutoAddsCode)
    {
        $this->replaceAutoAddsCode = $replaceAutoAddsCode;
    }

    /**
     * @return bool
     */
    public function getAutoAddRemoveDisable()
    {
        return $this->autoAddRemoveDisable;
    }

    /**
     * @param bool $autoAddRemoveDisable
     */
    public function setAutoAddRemoveDisable($autoAddRemoveDisable)
    {
        $this->autoAddRemoveDisable = boolval($autoAddRemoveDisable);
    }

    /**
    * @return bool
    */
    public function getAutoAddShowAsRecommended()
    {
        return $this->autoAddShowAsRecommended;
    }

    /**
    * @param bool $autoAddShowAsRecommended
    */
    public function setAutoAddShowAsRecommended($autoAddShowAsRecommended)
    {
        $this->autoAddShowAsRecommended = boolval($autoAddShowAsRecommended);
    }

    /**
     * @return array<int,RoleDiscount>
     */
    public function getRoleDiscounts()
    {
        return $this->roleDiscounts;
    }

    /**
     * @param array<int,RoleDiscount> $roleDiscounts
     */
    public function setRoleDiscounts($roleDiscounts)
    {
        $this->roleDiscounts = array();
        foreach ($roleDiscounts as $roleDiscount) {
            if ($roleDiscount instanceof RoleDiscount) {
                $this->roleDiscounts[] = $roleDiscount;
            }
        }
    }

    /**
     * @return string
     */
    public function getSortableApplyMode()
    {
        return $this->sortableApplyMode;
    }

    /**
     * @param string $sortableApplyMode
     */
    public function setSortableApplyMode($sortableApplyMode)
    {
        $this->sortableApplyMode = $sortableApplyMode;
    }

    /**
     * @return array
     */
    public function getSortableBlocksPriority()
    {
        return $this->sortableBlocksPriority;
    }

    /**
     * @param array $sortableBlocksPriority
     */
    public function setSortableBlocksPriority($sortableBlocksPriority)
    {
        $this->sortableBlocksPriority = $sortableBlocksPriority;
    }

    /**
     * @return bool
     */
    public function isDontApplyBulkIfRolesMatched()
    {
        return $this->dontApplyBulkIfRolesMatched;
    }

    /**
     * @param bool $dontApplyBulkIfRolesMatched
     */
    public function setDontApplyBulkIfRolesMatched($dontApplyBulkIfRolesMatched)
    {
        $this->dontApplyBulkIfRolesMatched = $dontApplyBulkIfRolesMatched;
    }

    /**
     * @param int $itemsCountLimit
     */
    public function setItemsCountLimit($itemsCountLimit)
    {
        $this->itemsCountLimit = intval($itemsCountLimit);
    }

    /**
     * @return int
     */
    public function getItemsCountLimit()
    {
        return $this->itemsCountLimit;
    }

    /**
     * @return bool
     */
    public function isItemsCountLimitExists()
    {
        return $this->itemsCountLimit !== -1;
    }

    /**
     * @param string $applyFirstTo
     */
    public function setApplyFirstTo($applyFirstTo)
    {
        if (
            in_array(
                $applyFirstTo,
                array(self::APPLY_FIRST_AS_APPEAR, self::APPLY_FIRST_TO_EXPENSIVE, self::APPLY_FIRST_TO_CHEAP)
            )
        ) {
            $this->applyFirstTo = $applyFirstTo;
        }
    }

    /**
     * @return string
     */
    public function getApplyFirstTo()
    {
        return $this->applyFirstTo;
    }

    /**
     * @param float|null $maxAmountForGifts
     */
    public function setMaxAmountForGifts($maxAmountForGifts)
    {
        $this->maxAmountForGifts = $maxAmountForGifts;
    }

    /**
     * @return float|null
     */
    public function getMaxAmountForGifts()
    {
        return $this->maxAmountForGifts;
    }
}
