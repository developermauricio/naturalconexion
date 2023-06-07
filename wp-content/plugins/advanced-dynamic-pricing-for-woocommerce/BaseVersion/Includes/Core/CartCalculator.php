<?php

namespace ADP\BaseVersion\Includes\Core;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Compatibility\TmExtraOptionsCmp;
use ADP\BaseVersion\Includes\Compatibility\WcProductAddonsCmp;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Listener;
use ADP\BaseVersion\Includes\Core\RuleProcessor\RuleProcessor;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\RulesCollection;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepositoryInterface;

defined('ABSPATH') or exit;

class CartCalculator implements ICartCalculator
{
    /**
     * @var RulesCollection
     */
    protected $ruleCollection;
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Listener
     */
    public $listener;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PersistentRuleRepositoryInterface
     */
    protected $persistentRuleRepository;

    /**
     * @param Context|RulesCollection $contextOrRuleCollection
     * @param RulesCollection|Listener|null $ruleCollectionOrListener
     * @param Listener|null $deprecated
     */
    public function __construct($contextOrRuleCollection, $ruleCollectionOrListener = null, $deprecated = null)
    {
        $this->context                  = adp_context();
        $this->ruleCollection           = $contextOrRuleCollection instanceof RulesCollection ? $contextOrRuleCollection : $ruleCollectionOrListener;
        $this->persistentRuleRepository = new PersistentRuleRepository();
        $this->listener                 = $ruleCollectionOrListener instanceof Listener ? $ruleCollectionOrListener : $deprecated;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withPersistentRuleRepository(PersistentRuleRepositoryInterface $repository)
    {
        $this->persistentRuleRepository = $repository;
    }

    /**
     * @param Context|Listener|null $context
     * @param Listener|null $listener
     *
     * @return self
     */
    public static function make($contextOrListener, $deprecated = null)
    {
        $listener = $contextOrListener instanceof Listener ? $contextOrListener : $deprecated;

        return new static(CacheHelper::loadActiveRules(), $listener);
    }

    /**
     * @param Listener $listener
     *
     * @return self
     */
    public static function makeWithoutConditions($listener)
    {
        $rules = [];

        foreach ( CacheHelper::loadActiveRules()->getRules() as $rule ) {
            $newRule = clone $rule;
            $newRule->setConditions([]);
            $rules[] = $newRule;
        }

        return new static(new RulesCollection($rules), $listener);
    }

    public function getRulesCollection()
    {
        return $this->ruleCollection;
    }

    /**
     * @param Cart $cart
     * @param CartItem $item
     *
     * @return bool
     */
    public function processItem(&$cart, $item)
    {
        return $this->processCart($cart);
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function processCart(&$cart)
    {
        if ($cart->isEmpty()) {
            return false;
        }

        if ( ! $this->isCalculationAllowed()) {
            return false;
        }

        if ($this->listener) {
            $this->listener->calcProcessStarted();
        }

        $appliedRules = 0;

        if ($this->context->getOption('support_persistence_rules')) {
            $appliedRules = $this->applyPersistentRules($cart);
        }

        foreach ($this->ruleCollection->getRules() as $rule) {
            $proc = $rule->buildProcessor($this->context);
            if ($proc->applyToCart($cart)) {
                $appliedRules++;
            }

            $this->announceRuleCalculated($proc);
        }

        $result = boolval($appliedRules);

        if ($result) {
            if ('compare_discounted_and_sale' === $this->context->getOption('discount_for_onsale')) {
                $newItems = array();
                foreach ($cart->getItems() as $item) {
                    $productPrice = $item->getOriginalPrice();
                    foreach ($item->getDiscounts() as $ruleId => $amounts) {
                        $productPrice -= array_sum($amounts);
                    }
                    if ($this->context->getOption('is_calculate_based_on_wc_precision')) {
                        $productPrice = round($productPrice, wc_get_price_decimals());
                    }

                    $product     = $item->getWcItem()->getProduct();
                    $wcSalePrice = null;

                    /** Always remember about scheduled WC sales */
                    if ($product->is_on_sale('edit') && $product->get_sale_price('edit') !== '') {
                        $wcSalePrice = floatval($product->get_sale_price('edit'));
                        if ( count($item->getAddons()) > 0 ) {
                            $wcSalePrice += $item->getAddonsAmount();
                        }
                    }

                    if ( ! is_null($wcSalePrice) && $wcSalePrice < $productPrice) {
                        $newItem = new CartItem($item->getWcItem(), $wcSalePrice, $item->getQty(), $item->getPos());

                        foreach ($item->getAttrs() as $attr) {
                            $newItem->addAttr($attr);
                        }

                        foreach ($item->getMarks() as $mark) {
                            $newItem->addMark($mark);
                        }

                        $minDiscountRangePrice = $item->getMinDiscountRangePrice();
                        if ($minDiscountRangePrice !== null) {
                            $minDiscountRangePrice = $minDiscountRangePrice < $wcSalePrice ? $minDiscountRangePrice : $wcSalePrice;
                            $newItem->setMinDiscountRangePrice($minDiscountRangePrice);
                        }

                        $item = $newItem;
                    }

                    $newItems[] = $item;
                }

                $cart->setItems($newItems);
            } elseif ('discount_regular' === $this->context->getOption('discount_for_onsale')) {
                $newItems = array();
                foreach ($cart->getItems() as $item) {
                    $product     = $item->getWcItem()->getProduct();
                    $wcSalePrice = null;

                    /** Always remember about scheduled WC sales */
                    if ($product->is_on_sale('edit') && $product->get_sale_price('edit') !== '') {
                        $wcSalePrice = floatval($product->get_sale_price('edit'));
                        if ( count($item->getAddons()) > 0 ) {
                            $wcSalePrice += $item->getAddonsAmount();
                        }
                    }

                    if ( ! is_null($wcSalePrice) && count($item->getHistory()) == 0) {
                        $newItem = new CartItem($item->getWcItem(), $wcSalePrice, $item->getQty(), $item->getPos());

                        foreach ($item->getAttrs() as $attr) {
                            $newItem->addAttr($attr);
                        }

                        foreach ($item->getMarks() as $mark) {
                            $newItem->addMark($mark);
                        }

                        $minDiscountRangePrice = $item->getMinDiscountRangePrice();
                        if ($minDiscountRangePrice !== null) {
                            $newItem->setMinDiscountRangePrice($minDiscountRangePrice);
                        }

                        $item = $newItem;
                    }

                    $newItems[] = $item;
                }

                $cart->setItems($newItems);
            }
        }

        if ($this->listener) {
            $this->listener->processResult($result);
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isCalculationAllowed()
    {
        return ! $this->context->isRuleSuppressed();
    }

    /**
     * @param RuleProcessor $proc
     */
    protected function announceRuleCalculated($proc)
    {
        if ($this->listener) {
            $this->listener->ruleCalculated($proc);
        }
    }

    /**
     * @param Cart $cart
     *
     * @return int
     */
    protected function applyPersistentRules(&$cart)
    {
        $appliedRules = 0;
        $context = $this->context;

        /**
         * Accumulating non-temporary quantity
         *
         * The temporary and 'in cart' items are defined as different in the cart ( mean does not merge with each other ).
         * But we need to get 'persistent' price based on quantity as if they were merged.
         * E.g. we calculate price for 6th apple and the other 5 is already in the cart. So, if we do not 'accumulate'
         * quantity ( as you see below ), we will receive the price of first item, because they are split in the cart.
         *
         * Without a doubt, we should divide cart items based on quantity from persistent cache storage,
         * but it is too complicated for now.
         */
        $nonTempQtyCounter = [];
        $mappingQty        = [];
        foreach ($cart->getItems() as $item) {
            $nonTempHash                     = $item->calculateNonTemporaryHash();
            $nonTempQtyCounter[$nonTempHash] = isset($nonTempQtyCounter[$nonTempHash]) ? $nonTempQtyCounter[$nonTempHash] + $item->getQty() : $item->getQty();
            $mappingQty[$item->getHash()]    = $nonTempQtyCounter[$nonTempHash];
        }
        /** Finish accumulating non-temporary quantity */

        /**
         * @var array<int, CartItem> $newItems
         * Create the list of cloned items with modified prices, so modifications do not affect the work of conditions.
         */
        $newItems = [];

        $initialItems = $cart->getItems();

        foreach ($cart->getItems() as $item) {
            $newItem = clone $item;
            $newItems[] = $newItem;

            $persistentQty = $mappingQty[$item->getHash()] ?? 1.0;
            $objects = $this->persistentRuleRepository->getCache($item, $persistentQty);

            $object = null;
            $processor = null;
            foreach ($objects as $tmpObject) {
                $tmpProcessor = $tmpObject->rule->buildProcessor($context);

                if ( $tmpProcessor->isRuleMatchedCart($cart) ) {
                    $object = $tmpObject;
                    $processor = $tmpProcessor;
                }
            }

            if ( ! $object || ! $object->rule || ! $object->price ) {
                continue;
            }

            $price = $object->price;

            $currencySwitcher = $this->context->currencyController;
            if ($currencySwitcher->isCurrencyChanged()) {
                $price = $price * $this->context->currencyController->getRate();
            }

            /** Replace cloned item in $cart */
            $tmpItems = $cart->getItems();
            foreach ( $tmpItems as $index => $tmpItem ) {
                if ( $tmpItem === $item ) {
                    $tmpItems[$index] = $newItem;
                }
            }
            $cart->setItems($tmpItems);
            $processor->applyPriceToCartItem($cart, $newItem, $price);
            $cart->setItems($initialItems);

            $appliedRules++;
        }

        $cart->setItems($newItems);

        return $appliedRules;
    }
}
