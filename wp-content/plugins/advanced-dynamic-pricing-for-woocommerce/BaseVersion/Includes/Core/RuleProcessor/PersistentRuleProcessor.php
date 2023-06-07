<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Fee;
use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Filter;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RangeDiscount;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock\ProductStockController;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\Factory;
use Exception;
use WC_Product;

defined('ABSPATH') or exit;

class PersistentRuleProcessor implements RuleProcessor
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
     * @var PersistentRule
     */
    protected $rule;

    /**
     * @var PersistentRule
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
     * @var ActivationTriggerStrategy
     */
    protected $activationTriggerStrategy;

    /**
     * @var ProductStockController
     */
    protected $ruleUsedStock;

    /**
     * @var ExclusivityAllStrategy
     */
    protected $exclusivityStrategy;

    /**
     * @param Context|PersistentRule $contextOrRule
     * @param PersistentRule|null $deprecated
     *
     * @throws Exception
     */
    public function __construct($contextOrRule, $deprecated = null)
    {
        $this->context = adp_context();
        $rule          = $contextOrRule instanceof PersistentRule ? $contextOrRule : $deprecated;

        if ( ! ($rule instanceof PersistentRule)) {
            $this->context->handleError(new Exception("Wrong rule type"));
        }

        $this->rule         = clone $rule;
        $this->originalRule = $rule;

        $this->conditionsCheckStrategy   = new ConditionsCheckStrategy($rule);
        $this->limitsCheckStrategy       = new LimitsCheckStrategy($rule);
        $this->ruleUsedStock             = new ProductStockController();
        $this->giftStrategy              = Factory::get('Core_RuleProcessor_GiftStrategy', $rule, $this->ruleUsedStock);
        $this->autoAddStrategy           = Factory::get('Core_RuleProcessor_AutoAddStrategy', $rule, $this->ruleUsedStock);
        $this->activationTriggerStrategy = new ActivationTriggerStrategy($rule);
        $this->exclusivityStrategy       = new ExclusivityAllStrategy($rule);
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
     * @return PersistentRule
     */
    public function getRule()
    {
        return $this->originalRule;
    }

    public function applyToCart($cart)
    {
        return false;
    }

    /**
     * @param Cart $cart
     * @param CartItem $item
     *
     * @return bool
     */
    public function applyToCartItem($cart, $item)
    {
        if ($item->hasAttr($item::ATTR_IMMUTABLE)) {
            return true;
        }

        global $wp_filter;
        $currentWpFilter = $wp_filter;

        $this->process($cart, $item);

        $wp_filter = $currentWpFilter;

        return true;
    }

    /**
     * @param Cart $cart
     * @param CartItem $item
     * @param float $price
     *
     * @return bool
     */
    public function applyPriceToCartItem($cart, $item, $price)
    {
        if ($item->hasAttr($item::ATTR_IMMUTABLE)) {
            return true;
        }

        global $wp_filter;
        $currentWpFilter = $wp_filter;

        $this->processWithPrice($cart, $item, $price);

        $wp_filter = $currentWpFilter;

        return true;
    }

    /**
     * @param Cart $cart
     * @param CartItem $item
     * @param float $price
     */
    protected function processWithPrice($cart, $item, $price)
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

        $collection = new CartItemsCollection($this->rule->getId());
        $collection->add($item);

        if ( $item->getAddonsAmount() > 0 ) {
            // recalculate price because the item has changed its price and structure
            $this->applyProductAdjustment($cart, $collection);
        } else {
            $flags = array();
            if ( $handler = $this->rule->getProductAdjustmentHandler() ) {
                $globalContext = $cart->getContext()->getGlobalContext();

                $amount = ($item->getPrice() - $price) * $item->getQty();

                if ($handler->isReplaceWithCartAdjustment()) {
                    $flags[]        = CartItem::FLAG_IGNORE;
                    $adjustmentCode = $handler->getReplaceCartAdjustmentCode();

                    if ($amount > 0) {
                        $cart->addCoupon(
                            new CouponCartItem(
                                $globalContext,
                                CouponCartItem::TYPE_ITEM_DISCOUNT,
                                $adjustmentCode,
                                $amount / $item->getQty(),
                                $this->rule->getId(),
                                $item->getWcItem()
                            )
                        );
                    } elseif ($amount < 0) {
                        $taxClass = $globalContext->getIsPricesIncludeTax() ? "" : "standard";
                        $cart->addFee(
                            new Fee(
                                $globalContext,
                                Fee::TYPE_ITEM_OVERPRICE,
                                $adjustmentCode,
                                (-1) * $amount,
                                $taxClass,
                                $this->rule->getId()
                            )
                        );
                    }
                } elseif ($globalContext->getOption('item_adjustments_as_coupon', false)
                          && $globalContext->getOption('item_adjustments_coupon_name', false)
                ) {
                    $flags[]        = CartItem::FLAG_IGNORE;
                    $adjustmentCode = $globalContext->getOption('item_adjustments_coupon_name');

                    if ($amount > 0) {
                        $cart->addCoupon(
                            new CouponCartItem(
                                $globalContext,
                                CouponCartItem::TYPE_ITEM_DISCOUNT,
                                $adjustmentCode,
                                $amount / $item->getQty(),
                                $this->rule->getId(),
                                $item->getWcItem()
                            )
                        );
                    } elseif ($amount < 0) {
                        $taxClass = $globalContext->getIsPricesIncludeTax() ? "" : "standard";
                        $cart->addFee(
                            new Fee(
                                $globalContext,
                                Fee::TYPE_ITEM_OVERPRICE,
                                $adjustmentCode,
                                (-1) * $amount,
                                $taxClass,
                                $this->rule->getId()
                            )
                        );
                    }
                }
            }

            $item->setPrice($this->rule->getId(), $price, $flags);
        }

        $this->addFreeProducts($cart, $collection);
        $this->addGifts($cart, $collection);

        $this->addAutoAddProducts($cart, $collection);
        $this->addGifts($cart, $collection);

        $this->exclusivityStrategy->makeAffectedItemAsExclusive($collection->get_items());
    }

    /**
     * @param Cart $cart
     * @param CartItem $item
     */
    protected function process($cart, $item)
    {
        $this->rule = apply_filters('adp_before_apply_rule', $this->rule, $this, $cart);

        if ( ! apply_filters('adp_is_apply_rule', true, $this->rule, $this, $cart)) {
            $this->status = self::STATUS_DISABLED_WITH_FORCE;

            return;
        }

        $collection = new CartItemsCollection($this->rule->getId());
        $collection->add($item);

        $this->applyProductAdjustment($cart, $collection);
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    protected function applyProductAdjustment(&$cart, &$collection)
    {
        if ($handler = $this->rule->getProductAdjustmentHandler()) {
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

        $this->applyRangeDiscounts($cart, $collection);
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
    public function isRuleOptionalMatchedCart($cart, $checkLimits = true, $checkConditions = true)
    {
        if ($checkLimits && ! $this->checkLimits($cart)) {
            $this->status = $this::STATUS_LIMITS_NOT_PASSED;

            return false;
        }

        if ($checkConditions && ! $this->checkConditions($cart)) {
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
        return 0.0;
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
        return false;
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
}
