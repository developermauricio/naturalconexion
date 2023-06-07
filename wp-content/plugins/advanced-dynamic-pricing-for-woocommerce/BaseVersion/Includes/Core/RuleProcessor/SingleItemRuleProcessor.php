<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Filter;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RangeDiscount;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Exceptions\RuleExecutionTimeout;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock\ProductStockController;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock\ProductStockItem;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\Factory;
use Exception;
use WC_Product;

defined('ABSPATH') or exit;

class SingleItemRuleProcessor implements RuleProcessor
{
    const STATUS_OUT_OF_TIME = -2;
    const STATUS_UNEXPECTED_ERROR = -1;
    const STATUS_NO_INFO = 0;
    const STATUS_STARTED = 1;
    const STATUS_DISABLED_WITH_FORCE = 2;
    const STATUS_LIMITS_NOT_PASSED = 3;
    const STATUS_CONDITIONS_NOT_PASSED = 4;
    const STATUS_FILTERS_NOT_PASSED = 5;
    const STATUS_DISABLED_BY_COUPON_CODE_TRIGGER = 6;
    const STATUS_DISABLED_BY_DATE = 7;

    protected $status;
    protected $lastUnexpectedErrorMessage;

    /**
     * @var float Rule start timestamp
     */
    protected $execRuleStart;

    /**
     * @var float Rule start timestamp
     */
    protected $lastExecTime;

    /**
     * @var SingleItemRule
     */
    protected $rule;

    /**
     * @var SingleItemRule
     */
    protected $originalRule;

    /**
     * @var Context
     */
    protected $context;

    /**
     * The way how we check conditions
     * @var ConditionsCheckStrategy
     */
    protected $conditionsCheckStrategy;

    /**
     * The way how we check limits
     * @var LimitsCheckStrategy
     */
    protected $limitsCheckStrategy;

    /**
     * The way how we apply cart adjustments
     * @var CartAdjustmentsApplyStrategy
     */
    protected $cartAdjustmentsApplyStrategy;

    /**
     * The way how we gift items
     * @var GiftStrategy
     */
    protected $giftStrategy;

    /**
     * The way how we auto add items
     * @var AutoAddStrategy
     */
    protected $autoAddStrategy;

    /**
     * @var RuleTimer
     */
    protected $ruleTimer;

    /**
     * @var RoleDiscountStrategy
     */
    protected $roleDiscountStrategy;

    /**
     * @var ActivationTriggerStrategy
     */
    protected $activationTriggerStrategy;

    /**
     * @var ProductStockController
     */
    protected $ruleUsedStock;

    /**
     * @param Context|SingleItemRule $contextOrRule
     * @param SingleItemRule|null $deprecated
     *
     * @throws Exception
     */
    public function __construct($contextOrRule, $deprecated = null)
    {
        $this->context = adp_context();
        $rule          = $contextOrRule instanceof SingleItemRule ? $contextOrRule : $deprecated;

        if ( ! ($rule instanceof SingleItemRule)) {
            $this->context->handleError(new Exception("Wrong rule type"));
        }

        $this->rule         = clone $rule;
        $this->originalRule = $rule;

        $this->conditionsCheckStrategy      = new ConditionsCheckStrategy($rule);
        $this->limitsCheckStrategy          = new LimitsCheckStrategy($rule);
        $this->cartAdjustmentsApplyStrategy = new CartAdjustmentsApplyStrategy($rule);
        $this->ruleTimer                    = new RuleTimer($rule);
        $this->ruleUsedStock                = new ProductStockController();
        $this->giftStrategy                 = Factory::get('Core_RuleProcessor_GiftStrategy', $rule, $this->ruleUsedStock);
        $this->autoAddStrategy              = Factory::get('Core_RuleProcessor_AutoAddStrategy', $rule, $this->ruleUsedStock);
        $this->roleDiscountStrategy         = new RoleDiscountStrategy($rule);
        $this->activationTriggerStrategy    = new ActivationTriggerStrategy($rule);
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return SingleItemRule
     */
    public function getRule()
    {
        return $this->originalRule;
    }

    public function applyToCart($cart)
    {
        $this->ruleTimer->start();

        global $wp_filter;
        $current_wp_filter = $wp_filter;

        try {
            $this->process($cart);
        } catch (RuleExecutionTimeout $e) {
            $this->status = self::STATUS_OUT_OF_TIME;
            $this->ruleTimer->handleOutOfTime();
        }

        $wp_filter = $current_wp_filter;

        $this->ruleTimer->finish();

        return true;
    }

    /**
     * @param Cart $cart
     *
     * @throws RuleExecutionTimeout
     */
    protected function process($cart)
    {
        $this->ruleUsedStock->initFromCart($cart);
        $this->status = self::STATUS_STARTED;

        $this->rule = apply_filters('adp_before_apply_rule', $this->rule, $this, $cart);

        if ( ! apply_filters('adp_is_apply_rule', true, $this->rule, $this, $cart)) {
            $this->status = self::STATUS_DISABLED_WITH_FORCE;

            return;
        }

        if ( ! $this->activationTriggerStrategy->canBeAppliedByDate($cart)) {
            $this->status = self::STATUS_DISABLED_BY_DATE;

            return;
        }

        if ( ! $this->activationTriggerStrategy->canBeAppliedUsingCouponCode($cart)) {
            $this->status = self::STATUS_DISABLED_BY_COUPON_CODE_TRIGGER;

            return;
        }

        if ( ! $this->isRuleMatchedCart($cart)) {
            return;
        }

        $this->ruleTimer->checkExecutionTime();

        try {
            $collection = $this->getItemsToDiscount($cart);
        } catch (Exception $exception) {
            $this->status                     = self::STATUS_UNEXPECTED_ERROR;
            $this->lastUnexpectedErrorMessage = $exception->getMessage();

            return;
        }

        if ($collection->isEmpty()) {
            $this->status = $this::STATUS_FILTERS_NOT_PASSED;

            return;
        }

        $this->applyProductAdjustment($cart, $collection);
        $this->ruleTimer->checkExecutionTime();

        $this->addFreeProducts($cart, $collection);
        $this->ruleTimer->checkExecutionTime();

        $this->addGifts($cart, $collection);
        $this->ruleTimer->checkExecutionTime();

        $this->addAutoAddProducts($cart, $collection);
        $this->ruleTimer->checkExecutionTime();

        $this->addAutoAdds($cart, $collection);
        $this->ruleTimer->checkExecutionTime();

        $this->applyCartAdjustments($cart, $collection);

        $this->applyChangesToCart($cart, $collection);
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function isRuleMatchedCart($cart)
    {
        if ( ! $this->checkLimits($cart)) {
            $this->status = $this::STATUS_LIMITS_NOT_PASSED;

            return false;
        }

        if ( ! $this->checkConditions($cart)) {
            $this->status = $this::STATUS_CONDITIONS_NOT_PASSED;

            return false;
        }

        return true;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function checkLimits($cart)
    {
        return $this->limitsCheckStrategy->check($cart);
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function checkConditions($cart)
    {
        return $this->conditionsCheckStrategy->check($cart);
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function matchConditions($cart)
    {
        return $this->conditionsCheckStrategy->match($cart);
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function applyCartAdjustments($cart, $collection)
    {
        $this->cartAdjustmentsApplyStrategy->applyToCartWithItems($cart, $collection);
    }

    /**
     * @param Cart $cart
     *
     * @return CartItemsCollection
     * @throws Exception
     */
    public function getItemsToDiscount($cart)
    {
        $collection = new CartItemsCollection($this->rule->getId());

        if ( ! $cartMutableItems = $cart->getMutableItems()) {
            return $collection;
        }

        $productStockController = new ProductStockController();
        foreach ($cart->getItems() as $item) {
            /** @var CartItem $item */
            if ($item->hasAttr($item::ATTR_IMMUTABLE)) {
                $wcCartItemFacade = $item->getWcItem();
                $product          = $wcCartItemFacade->getProduct();

                $stockItem = new ProductStockItem(
                    $product->get_id(),
                    $item->getQty(),
                    $product->get_parent_id(),
                    $wcCartItemFacade->getVariation(),
                    $wcCartItemFacade->getThirdPartyData()
                );

                $productStockController->addItem($stockItem);
            }
        }

        $cart->purgeMutableItems();

        uasort($cartMutableItems, array($this, 'sortItems'));
        $cartMutableItems = array_values($cartMutableItems);

        $filters = $this->rule->getFilters();
        /** @var $productFiltering ProductFiltering */
        $productFiltering = Factory::get("Core_RuleProcessor_ProductFiltering", $this->context);
        /** @var $productExcluding ProductFiltering */
        $productExcluding = Factory::get("Core_RuleProcessor_ProductFiltering", $this->context);

        $productExcludingEnabled = $cart->getContext()->getOption('allow_to_exclude_products');

        $totalQtyLeft = $this->rule->getItemsCountLimit() !== -1 ? floatval($this->rule->getItemsCountLimit()) : INF;

        foreach ($cartMutableItems as $index => $mutableItem) {
            /** @var $mutableItem CartItem */

            if ($totalQtyLeft <= floatval(0)) {
                $cart->addToCart($mutableItem);
                continue;
            }

            $wcCartItemFacade = $mutableItem->getWcItem();
            $product          = $wcCartItemFacade->getProduct();

            /** @var CartItem[] $filterAffectedItems */
            $filterAffectedItems = array();

            /**
             * Item must match all filters
             */
            $match = true;
            foreach ($filters as $filter) {
                $filterMutableItem = $mutableItem;

                $productFiltering->prepare($filter->getType(), $filter->getValue(), $filter->getMethod());

                if ($productExcludingEnabled) {
                    $productExcluding->prepare($filter::TYPE_PRODUCT, $filter->getExcludeProductIds(),
                        $filter::METHOD_IN_LIST);

                    if ($productExcluding->checkProductSuitability($product, $wcCartItemFacade->getData())) {
                        $match = false;
                        break;
                    }

                    if ($filter->isExcludeWcOnSale() && $product->is_on_sale('')) {
                        $match = false;
                        break;
                    }

                    if ($filter->isExcludeAlreadyAffected() && $filterMutableItem->areRuleApplied()) {
                        $match = false;
                        break;
                    }

                    if ($filter->isExcludeBackorder()) {
                        if ('onbackorder' === $product->get_stock_status('edit')) {
                            $match = false;
                            break;
                        } elseif ($product->managing_stock() && $product->backorders_allowed()) {
                            $qtyInCart = $productStockController->get($product->get_id(), $product->get_parent_id(),
                                $wcCartItemFacade->getVariation(), array());

                            $availableQty = $product->get_stock_quantity('edit') - $qtyInCart;

                            if ($availableQty <= 0) {
                                $match = false;
                                break;
                            }

                            if ($filterMutableItem->getQty() > $availableQty) {
                                $tmpCartItem = clone $filterMutableItem;
                                $tmpCartItem->setQty($availableQty);
                                $filterMutableItem = $tmpCartItem;
                            }
                        }
                    }
                }

                if ( ! $productFiltering->checkProductSuitability($product, $wcCartItemFacade->getData())) {
                    $match = false;
                    break;
                }

                if ($match && !$filterMutableItem->hasAttr($filterMutableItem::ATTR_TEMP)) {
                    $filter->setCollectedQtyInCart($filter->getCollectedQtyInCart() + $filterMutableItem->getQty());
                }

                $filterAffectedItems[] = $filterMutableItem;
            }

            $productStockController->add(
                $product->get_id(),
                $mutableItem->getQty(),
                $product->get_parent_id(),
                $wcCartItemFacade->getVariation(),
                array()
            );

            if (count($filterAffectedItems) > 0) {
                $readyMutableItem = reset($filterAffectedItems);
                foreach ($filterAffectedItems as $item) {
                    if ($readyMutableItem->getQty() > $item->getQty()) {
                        $readyMutableItem = $item;
                    }
                }

                if ($mutableItem->getQty() > $readyMutableItem->getQty()) {
                    $mutableItem->setQty($mutableItem->getQty() - $readyMutableItem->getQty());
                    $cart->addToCart($mutableItem);
                    $mutableItem = $readyMutableItem;
                }
            }

            if ($match) {
                if ($totalQtyLeft !== INF) {
                    if ($totalQtyLeft < $mutableItem->getQty()) {
                        $tmpCartItem = clone $mutableItem;
                        $tmpCartItem->setQty($mutableItem->getQty() - $totalQtyLeft);
                        $cart->addToCart($tmpCartItem);

                        $mutableItem->setQty($totalQtyLeft);
                        $totalQtyLeft = floatval(0);
                    } else {
                        $totalQtyLeft -= $mutableItem->getQty();
                        unset($cartMutableItems[$index]);
                    }
                }

                $collection->add($mutableItem);
            } else {
                $cart->addToCart($mutableItem);
            }
        }

        return $collection;
    }

    /**
     * @param CartItem $item1
     * @param CartItem $item2
     *
     * @return float|int
     */
    protected function sortItems($item1, $item2)
    {
        $rule = $this->rule;

        if ($rule::APPLY_FIRST_AS_APPEAR === $this->rule->getApplyFirstTo()) {
            return 0;
        }

        $price1 = $item1->getOriginalPrice();
        $price2 = $item2->getOriginalPrice();

        if ($rule::APPLY_FIRST_TO_CHEAP === $this->rule->getApplyFirstTo()) {
            if ($price1 - $price2 > 0) {
                return 1;
            } elseif ($price1 - $price2 < 0) {
                return -1;
            }
        } elseif ($rule::APPLY_FIRST_TO_EXPENSIVE === $this->rule->getApplyFirstTo()) {
            if ($price2 - $price1 > 0) {
                return 1;
            } elseif ($price2 - $price1 < 0) {
                return -1;
            }
        }

        return 0;
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function applyProductAdjustment(&$cart, &$collection)
    {
        if ($handler = $this->rule->getProductAdjustmentHandler()) {
            /*if (!$this->context->is($this->context::WC_CART_PAGE)) {
                if ($this->context->getOption('show_unmodified_price_if_discounts_with_coupon')
                    && $handler->getReplaceCartAdjustmentCode() !== '') {
                    return;
                }
            }*/
            /** @var PriceCalculator $priceCalculator */
            $priceCalculator = Factory::get(
                "Core_RuleProcessor_PriceCalculator",
                $this->rule, $handler->getDiscount(),
                $handler->getMaxAvailableAmount()
            );

            foreach ($collection->get_items() as &$item) {
                $priceCalculator->applyItemDiscount($item, $cart, $handler);
            }
        }

        if ( ! $this->rule->getRoleDiscounts() && ! $this->rule->getProductRangeAdjustmentHandler()) {
            return;
        } elseif ( ! $this->rule->getRoleDiscounts()) {
            $this->applyRangeDiscounts($cart, $collection);
        } elseif ( ! $this->rule->getProductRangeAdjustmentHandler()) {
            $this->roleDiscountStrategy->processItems($cart, $collection);
        } elseif ($this->rule->getSortableApplyMode() === 'consistently') {
            $rolesApplied            = false;
            $doNotApplyBulkAfterRole = $this->rule->isDontApplyBulkIfRolesMatched();
            $initialCollection       = clone $collection;
            foreach ($this->rule->getSortableBlocksPriority() as $blockName) {
                if ('roles' == $blockName) {
                    $this->roleDiscountStrategy->processItems($cart, $collection);
                    $rolesApplied = $initialCollection->getHash() !== $collection->getHash();
                } elseif ('bulk-adjustments' == $blockName) {
                    if ($doNotApplyBulkAfterRole && $rolesApplied) {
                        continue;
                    }

                    $this->applyRangeDiscounts($cart, $collection);
                }
            }
        } elseif (
            $this->rule->getSortableApplyMode() === 'min_price_between'
            || $this->rule->getSortableApplyMode() === 'max_price_between'
        ) {
            $roleSetCollection = clone $collection;
            $this->roleDiscountStrategy->processItems($cart, $roleSetCollection);

            $discountRangeSetCollection = clone $collection;
            $this->applyRangeDiscounts($cart, $discountRangeSetCollection);

            $discountRangeItems = $discountRangeSetCollection->get_items();

            $collection->purge();
            foreach ($roleSetCollection->get_items() as $roleItem) {
                $matched = false;
                foreach ($discountRangeItems as $index => $discountRangeItem) {
                    if ($roleItem->getWcItem()->getKey() !== $discountRangeItem->getWcItem()->getKey()) {
                        continue;
                    }

                    $comparison = $this->rule->getSortableApplyMode() === 'min_price_between' ? "min" : "max";

                    if ($comparison(
                            $roleItem->getTotalPrice(),
                            $discountRangeItem->getTotalPrice()) === $roleItem->getTotalPrice()
                    ) {
                        $collection->add($roleItem);
                    } else {
                        $collection->add($discountRangeItem);
                    }

                    unset($discountRangeItems[$index]);
                    $matched = true;
                    break;
                }

                if ( ! $matched) {
                    $collection->add($roleItem);
                }
            }
        }
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     *
     * @throws Exception
     */
    protected function applyRangeDiscounts(&$cart, &$collection)
    {
        if ( ! ($handler = $this->rule->getProductRangeAdjustmentHandler())) {
            return;
        }

        $ranges = $handler->getRanges();

        // Add a "dummy" range, so all always start from 1
        $firstRange = reset($ranges);
        if ($firstRange && $firstRange->getFrom() > 1) {
            $context  = $cart->getContext()->getGlobalContext();
            $discount = new Discount($context, Discount::TYPE_PERCENTAGE, 0);
            $discount = new RangeDiscount(1, $firstRange->getFrom() - 1, $discount);
            $ranges   = array_merge(array($discount), $ranges); // at first position!
            $handler->setRanges($ranges);
        }

        if ($handler::TYPE_BULK === $handler->getType()) {
            $groupedItems = array();
            if ($handler::GROUP_BY_DEFAULT === $handler->getGroupBy()) {
                $groupedItems[] = $collection->get_items();
            } elseif ($handler::GROUP_BY_PRODUCT === $handler->getGroupBy()) {
                foreach ($collection->get_items() as $item) {
                    /**
                     * @var CartItem $item
                     */
                    $facade = $item->getWcItem();

                    if ( ! isset($groupedItems[$facade->getProductId()])) {
                        $groupedItems[$facade->getProductId()] = array();
                    }

                    $groupedItems[$facade->getProductId()][] = $item;
                }
            } elseif ($handler::GROUP_BY_VARIATION === $handler->getGroupBy()) {
                foreach ($collection->get_items() as $item) {
                    /**
                     * @var CartItem $item
                     */
                    $facade = $item->getWcItem();

                    if ( ! isset($groupedItems[$facade->getVariationId()])) {
                        $groupedItems[$facade->getVariationId()] = array();
                    }

                    $groupedItems[$facade->getVariationId()][] = $item;
                }
            } elseif ($handler::GROUP_BY_CART_POSITIONS === $handler->getGroupBy()) {
                foreach ($collection->get_items() as $item) {
                    /**
                     * @var CartItem $item
                     */
                    $facade = $item->getWcItem();

                    if ( ! isset($groupedItems[$facade->getKey()])) {
                        $groupedItems[$facade->getKey()] = array();
                    }

                    $groupedItems[$facade->getKey()][] = $item;
                }
            } elseif ($handler::GROUP_BY_ALL_ITEMS_IN_CART === $handler->getGroupBy()) {
                $totalQty = array_sum(array_map(function ($item) {
                    $facade = $item->getWcItem();

                    return $facade->isVisible() ? $item->getQty() : floatval(0);
                }, array_merge($collection->get_items(), $cart->getItems())));

                foreach ($ranges as $range) {
                    if ($range->isIn($totalQty)) {
                        /** @var PriceCalculator $priceCalculator */
                        $priceCalculator = Factory::get("Core_RuleProcessor_PriceCalculator", $this->rule, $range->getData());
                        foreach ($collection->get_items() as $item) {
                            $priceCalculator->applyItemDiscount($item, $cart, $handler);
                        }
                        break;
                    }
                }
            } elseif ($handler::GROUP_BY_PRODUCT_CATEGORIES === $handler->getGroupBy()) {
                $usedCategoryIds = array();
                foreach ($collection->get_items() as $item) {
                    $product = $item->getWcItem()->getProduct();

                    if ($product->is_type('variation') && $product->get_parent_id()) {
                        $product         = CacheHelper::getWcProduct($product->get_parent_id());
                        $usedCategoryIds += $product->get_category_ids();
                    } else {
                        $usedCategoryIds += $product->get_category_ids();
                    }
                }
                $usedCategoryIds = array_unique($usedCategoryIds);

                $product_filtering = Factory::get("Core_RuleProcessor_ProductFiltering", $this->context);
                /** @var ProductFiltering $product_filtering */
                $product_filtering->prepare(Filter::TYPE_CATEGORY, $usedCategoryIds, 'in_list');

                // count items with same categories in WC cart
                $totalQty = floatval(0);
                if ($usedCategoryIds) {
                    foreach (array_merge($collection->get_items(), $cart->getItems()) as $cartItem) {
                        /** @var CartItem $cartItem */
                        $facade = $cartItem->getWcItem();

                        if ( ! $facade->isVisible()) {
                            continue;
                        }

                        if ($product_filtering->checkProductSuitability($facade->getProduct())) {
                            $totalQty += $facade->getQty();
                        }
                    }
                }

                foreach ($ranges as $range) {
                    if ($range->isIn($totalQty)) {
                        /** @var PriceCalculator $priceCalculator */
                        $priceCalculator = Factory::get("Core_RuleProcessor_PriceCalculator", $this->rule, $range->getData());
                        foreach ($collection->get_items() as $item) {
                            $priceCalculator->applyItemDiscount($item, $cart, $handler);
                        }
                        break;
                    }
                }
            } elseif ($handler::GROUP_BY_PRODUCT_SELECTED_PRODUCTS === $handler->getGroupBy()) {
                $selectedProductIds = $handler->getSelectedProductIds();

                $totalQty = floatval(0);
                if ($selectedProductIds) {
                    foreach (array_merge($collection->get_items(), $cart->getItems()) as $cartItem) {
                        /** @var CartItem $cartItem */
                        $facade = $cartItem->getWcItem();

                        if ( ! $facade->isVisible()) {
                            continue;
                        }

                        if (in_array($facade->getProduct()->get_id(), $selectedProductIds)) {
                            $totalQty += $facade->getQty();
                        }
                    }
                }

                foreach ($ranges as $range) {
                    if ($range->isIn($totalQty)) {
                        /** @var PriceCalculator $priceCalculator */
                        $priceCalculator = Factory::get("Core_RuleProcessor_PriceCalculator", $this->rule, $range->getData());
                        foreach ($collection->get_items() as $item) {
                            $priceCalculator->applyItemDiscount($item, $cart, $handler);
                        }
                        break;
                    }
                }
            } elseif ($handler::GROUP_BY_PRODUCT_SELECTED_CATEGORIES === $handler->getGroupBy()) {
                $selectedCategoryIds = $handler->getSelectedCategoryIds();

                $product_filtering = Factory::get("Core_RuleProcessor_ProductFiltering", $this->context);
                /** @var ProductFiltering $product_filtering */
                $product_filtering->prepare(Filter::TYPE_CATEGORY, $selectedCategoryIds, 'in_list');

                $totalQty = floatval(0);
                if ($selectedCategoryIds) {
                    foreach (array_merge($collection->get_items(), $cart->getItems()) as $cartItem) {
                        /** @var CartItem $cartItem */
                        $facade = $cartItem->getWcItem();

                        if ( ! $facade->isVisible()) {
                            continue;
                        }

                        if ($product_filtering->checkProductSuitability($facade->getProduct())) {
                            $totalQty += $facade->getQty();
                        }
                    }
                }

                foreach ($ranges as $range) {
                    if ($range->isIn($totalQty)) {
                        /** @var PriceCalculator $priceCalculator */
                        $priceCalculator = Factory::get("Core_RuleProcessor_PriceCalculator", $this->rule, $range->getData());
                        foreach ($collection->get_items() as $item) {
                            $priceCalculator->applyItemDiscount($item, $cart, $handler);
                        }
                        break;
                    }
                }
            } elseif ($handler::GROUP_BY_META_DATA === $handler->getGroupBy()) {
                foreach ($collection->get_items() as $item) {
                    /**
                     * @var CartItem $item
                     */
                    $facade = $item->getWcItem();

                    $meta = $facade->getProduct()->get_meta_data();

                    usort($meta, function ($a, $b) {
                        return strcmp($a->__get('key'), $b->__get('key'));
                    });

                    $meta[] = $facade->getProductId();
                    $meta[] = $facade->getVariationId();

                    $key = md5(json_encode($meta));

                    if ( ! isset($groupedItems[$key])) {
                        $groupedItems[$key] = array();
                    }

                    $groupedItems[$key][] = $item;
                }
            }

            foreach ($groupedItems as $items) {
                $totalQty = array_sum(array_map(function ($item) {
                    /**
                     * @var CartItem $item
                     */
                    return $item->getQty();
                }, $items));

                foreach ($ranges as $range) {
                    /** @var PriceCalculator $priceCalculator */
                    $priceCalcMinDiscountRange = Factory::get(
                        "Core_RuleProcessor_PriceCalculator",
                        $this->rule,
                        $range->getData()
                    );
                    foreach ($items as $item) {
                        $price = $priceCalcMinDiscountRange->calculatePrice($item, $cart);

                        if ($price === null) {
                            continue;
                        }

                        $minPrice = $item->getMinDiscountRangePrice();

                        if ($minPrice !== null) {
                            if ($price < $minPrice) {
                                $item->setMinDiscountRangePrice($price);
                            }
                        } else {
                            $item->setMinDiscountRangePrice($price);
                        }
                    }
                }

                foreach ($ranges as $range) {
                    if ($range->isIn($totalQty)) {
                        /** @var PriceCalculator $priceCalculator */
                        $priceCalculator = Factory::get(
                            "Core_RuleProcessor_PriceCalculator",
                            $this->rule,
                            $range->getData()
                        );
                        foreach ($items as $item) {
                            $priceCalculator->applyItemDiscount($item, $cart, $handler);
                        }
                    }
                }
            }
        } elseif ($handler::TYPE_TIER === $handler->getType()) {
            if ($handler::GROUP_BY_DEFAULT === $handler->getGroupBy()) {
                $cal           = new TierUpItems($this->rule, $cart);
                $newCollection = new CartItemsCollection($this->rule->getId());
                foreach ($cal->executeItems($collection->get_items()) as $item) {
                    $newCollection->add($item);
                }

                $collection = $newCollection;
            } elseif ($handler::GROUP_BY_PRODUCT === $handler->getGroupBy()) {
                $groupedByProduct = array();
                foreach ($collection->get_items() as $item) {
                    $productId = $item->getWcItem()->getProductId();

                    if ( ! isset($groupedByProduct[$productId])) {
                        $groupedByProduct[$productId] = array();
                    }
                    $groupedByProduct[$productId][$item->getHash()] = $item;
                }

                $cal           = new TierUpItems($this->rule, $cart);
                $newCollection = new CartItemsCollection($this->rule->getId());
                foreach ($groupedByProduct as $items) {
                    foreach ($cal->executeItems($items) as $item) {
                        $newCollection->add($item);
                    }
                }

                $collection = $newCollection;
            } elseif ($handler::GROUP_BY_VARIATION === $handler->getGroupBy()) {
                $groupedByVariation = array();
                foreach ($collection->get_items() as $item) {
                    if ($item->getWcItem()->getVariationId()) {
                        $productId = $item->getWcItem()->getVariationId();
                    } else {
                        $productId = $item->getWcItem()->getProductId();
                    }

                    if ( ! isset($groupedByVariation[$productId])) {
                        $groupedByVariation[$productId] = array();
                    }
                    $groupedByVariation[$productId][$item->getHash()] = $item;
                }

                $cal           = new TierUpItems($this->rule, $cart);
                $newCollection = new CartItemsCollection($this->rule->getId());
                foreach ($groupedByVariation as $items) {
                    foreach ($cal->executeItems($items) as $item) {
                        $newCollection->add($item);
                    }
                }

                $collection = $newCollection;
            } elseif ($handler::GROUP_BY_PRODUCT_SELECTED_PRODUCTS === $handler->getGroupBy()) {
                $selectedProductIds = $handler->getSelectedProductIds();

                $totalQty = floatval(0);
                if ($selectedProductIds) {
                    foreach (array_merge($collection->get_items(), $cart->getItems()) as $cartItem) {
                        /** @var CartItem $cartItem */
                        $facade = $cartItem->getWcItem();

                        if ( ! $facade->isVisible()) {
                            continue;
                        }

                        if (in_array($facade->getProduct()->get_id(), $selectedProductIds)) {
                            $totalQty += $facade->getQty();
                        }
                    }

                    $cal           = new TierUpItems($this->rule, $cart);
                    $newCollection = new CartItemsCollection($this->rule->getId());
                    foreach ($cal->executeItemsWithCustomQty($collection->get_items(), $totalQty) as $item) {
                        $newCollection->add($item);
                    }

                    $collection = $newCollection;
                }
            } elseif ($handler::GROUP_BY_PRODUCT_SELECTED_CATEGORIES === $handler->getGroupBy()) {
                $selectedCategoryIds = $handler->getSelectedCategoryIds();

                $productFiltering = Factory::get("Core_RuleProcessor_ProductFiltering", $this->context);
                /** @var ProductFiltering $productFiltering */
                $productFiltering->prepare(Filter::TYPE_CATEGORY, $selectedCategoryIds, 'in_list');

                $totalQty = floatval(0);
                if ($selectedCategoryIds) {
                    foreach (array_merge($collection->get_items(), $cart->getItems()) as $cartItem) {
                        /** @var CartItem $cartItem */
                        $facade = $cartItem->getWcItem();

                        if ( ! $facade->isVisible()) {
                            continue;
                        }

                        if ($productFiltering->checkProductSuitability($facade->getProduct())) {
                            $totalQty += $facade->getQty();
                        }
                    }

                    $cal           = new TierUpItems($this->rule, $cart);
                    $newCollection = new CartItemsCollection($this->rule->getId());
                    foreach ($cal->executeItemsWithCustomQty($collection->get_items(), $totalQty) as $item) {
                        $newCollection->add($item);
                    }

                    $collection = $newCollection;
                }
            } elseif ($handler::GROUP_BY_CART_POSITIONS === $handler->getGroupBy()) {
                $groupedItems = array();

                foreach ($collection->get_items() as $item) {
                    /**
                     * @var CartItem $item
                     */
                    $facade = $item->getWcItem();

                    if ( ! isset($groupedItems[$facade->getKey()])) {
                        $groupedItems[$facade->getKey()] = array();
                    }

                    $groupedItems[$facade->getKey()][] = $item;
                }

                $cal           = new TierUpItems($this->rule, $cart);
                $newCollection = new CartItemsCollection($this->rule->getId());
                foreach ($groupedItems as $items) {
                    foreach ($cal->executeItems($items) as $item) {
                        $newCollection->add($item);
                    }
                }

                $collection = $newCollection;
            }
        }
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function applyChangesToCart(&$cart, $collection)
    {
        foreach ($collection->get_items() as $item) {
            $cart->addToCart($item);
        }

        $cart->destroyEmptyItems();
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function addFreeProducts($cart, $collection)
    {
        if ( ! $this->giftStrategy->canItemGifts()) {
            return;
        }

        // needs for calculate limit
        $totalQty = floatval(0);
        foreach ($collection->get_items() as $item) {
            $totalQty += $item->getQty();
        }

        $this->giftStrategy->addCartItemGifts($cart, $collection);
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function addGifts($cart, $collection)
    {
        if ( ! $this->giftStrategy->canGift()) {
            return;
        }

        $this->giftStrategy->addGifts($cart);
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function addAutoAddProducts($cart, $collection)
    {
        if ( ! $this->autoAddStrategy->canItemAutoAdds()) {
            return;
        }

        // needs for calculate limit
        $totalQty = floatval(0);
        foreach ($collection->get_items() as $item) {
            $totalQty += $item->getQty();
        }

        $this->autoAddStrategy->addCartItemAutoAdds($cart, $collection);
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function addAutoAdds($cart, $collection)
    {
        if ( ! $this->autoAddStrategy->canAutoAdd()) {
            return;
        }

        $this->autoAddStrategy->addAutoAdds($cart);
    }

    /**
     * @return float
     */
    public function getLastExecTime()
    {
        return $this->ruleTimer->getLastExecTime();
    }

    /**
     * @param Cart $cart
     * @param WC_Product $product
     * @param bool $checkConditions
     *
     * @return bool
     */
    public function isProductMatched($cart, $product, $checkConditions = false)
    {
        if ( ! ($product instanceof WC_Product)) {
            return false;
        }

        if ( ! $this->activationTriggerStrategy->canBeAppliedByDate($cart)) {
            return false;
        }

        if ( ! $this->checkLimits($cart)) {
            return false;
        }

        if ($checkConditions && ! $this->checkConditions($cart)) {
            return false;
        }

        $filters = $this->rule->getFilters();
        /** @var $productFiltering ProductFiltering */
        $productFiltering = Factory::get("Core_RuleProcessor_ProductFiltering", $this->context);
        /** @var $productExcluding ProductFiltering */
        $productExcluding = Factory::get("Core_RuleProcessor_ProductFiltering", $this->context);

        $productExcludingEnabled = $cart->getContext()->getOption('allow_to_exclude_products');

        /**
         * Item must match all filters
         */
        $match = true;
        foreach ($filters as $filter) {
            $productFiltering->prepare($filter->getType(), $filter->getValue(), $filter->getMethod());

            if ($productExcludingEnabled) {
                $productExcluding->prepare($filter::TYPE_PRODUCT, $filter->getExcludeProductIds(),
                    $filter::METHOD_IN_LIST);

                if ($productExcluding->checkProductSuitability($product, array())) {
                    $match = false;
                    break;
                }

                if ($filter->isExcludeWcOnSale() && $product->is_on_sale('')) {
                    $match = false;
                    break;
                }
            }

            if ( ! $productFiltering->checkProductSuitability($product, array())) {
                $match = false;
                break;
            }
        }

        return $match;
    }

    /**
     * @param Cart $cart
     * @param int $termId
     * @param bool $checkConditions
     *
     * @return bool
     */
    public function isCategoryMatched($cart, $termId, $checkConditions = false)
    {
        if ( ! $termId) {
            return false;
        }

        $termId = intval($termId);

        if ( ! $this->activationTriggerStrategy->canBeAppliedByDate($cart)) {
            return false;
        }

        if ( ! $this->checkLimits($cart)) {
            return false;
        }

        if ($checkConditions && ! $this->matchConditions($cart)) {
            return false;
        }

        /**
         * Item must match all filters
         */
        $match = true;
        foreach ($this->rule->getFilters() as $filter) {
            if ( ! ($filter->getType() === $filter::TYPE_CATEGORY && in_array($termId, $filter->getValue()))) {
                $match = false;
                break;
            }
        }

        return $match;
    }

    /**
     * @return SingleItemRule
     */
    public function getProcessorRule()
    {
        return $this->rule;
    }
}
