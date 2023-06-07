<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\CartProcessor\CartBuilder;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\CartCalculatorPersistent;
use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RangeDiscount;
use ADP\BaseVersion\Includes\Database\Models\PersistentRuleCache as PersistentRuleModel;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedGroupedProduct;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;
use ADP\BaseVersion\Includes\PriceDisplay\Processor;
use ADP\BaseVersion\Includes\Shortcodes\SqlGenerator;
use ADP\Factory;
use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Database\Models\PersistentRuleCache;
use ADP\BaseVersion\Includes\Database\PersistentRuleCacheObject;
use ADP\BaseVersion\Includes\Database\RuleStorage;

defined('ABSPATH') or exit;

class PersistentRuleRepository implements PersistentRuleRepositoryInterface
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct() {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param CartItem $item
     * @param float|null $qty
     *
     * @return array<int, PersistentRuleCacheObject>
     * @throws \Exception
     */
    public function getCache($item, $qty = null)
    {
        $cacheKey = $this->calculateCacheHash($item, $qty);

        $objects = CacheHelper::cacheGet($cacheKey, CacheHelper::GROUP_RULES_CACHE);

        if ( ! $objects) {
            if ($objects = $this->calculate($item, $qty)) {
                CacheHelper::cacheSet($cacheKey, $objects, CacheHelper::GROUP_RULES_CACHE);
            }
        }

        return $objects;
    }

    /**
     * @param \WC_Product $product
     *
     * @return array<int, PersistentRuleCacheObject>
     * @throws \Exception
     */
    public function getCacheWithProduct($product)
    {
        $cacheKey = $this->calculateCacheHashWithProduct($product);

        $objects = CacheHelper::cacheGet($cacheKey, CacheHelper::GROUP_RULES_CACHE);

        if ( ! $objects) {
            if ($objects = $this->calculate($product)) {
                CacheHelper::cacheSet($cacheKey, $objects, CacheHelper::GROUP_RULES_CACHE);
            }
        }

        return $objects;
    }

    public function addRule($rows, $ruleId)
    {

        global $wpdb;
        $table = $wpdb->prefix . PersistentRuleModel::TABLE_NAME;
        $wpdb->query('START TRANSACTION');

        if ( ! empty($ruleId)) {
            $where  = array('rule_id' => $ruleId);
            $result = $wpdb->delete($table, $where);
        }

        /**
         * @var PersistentRuleCache $cache
         */
        foreach ($rows as $cache) {
            $result = $wpdb->insert($table, $cache->getData());
        }

        $wpdb->query('COMMIT');
    }

    public function getAddRuleData($ruleId, Context $context)
    {

        global $wpdb;

        /** @var $sqlGenerator SqlGenerator */
        $sqlGenerator = Factory::get("Shortcodes_SqlGeneratorPersistent");

        /** @var RuleStorage $storage */
        $storage         = Factory::get("Database_RuleStorage");
        $storage->withContext($context);
        $ruleRepository = new RuleRepository();
        $rows            = $ruleRepository->getRules(array('id' => $ruleId));
        $rulesCollection = $storage->buildPersistentRules($rows);

        foreach ($rulesCollection->getRules() as $rule) {
            /** @var PersistentRule $rule */
            $sqlGenerator->applyRuleToQuery($context, $rule);
        }

        $productIds = $sqlGenerator->getProductIds();
        if(!$productIds) {
            return [];
        }

        $data = array();
        /** @var PersistentRule $rule */
        $rule             = $rulesCollection->getFirst();
        $cartCalculator   = new CartCalculatorPersistent($context, $rule);
        $productProcessor = new Processor($context, $cartCalculator);
        $cartBuilder      = new CartBuilder($context);
        $cart             = $cartBuilder->create(WC()->customer, WC()->session);
        $productProcessor->withCart($cart);

        foreach ($productIds as $productId) {
            $variationId = 0;

            if ('product_variation' === get_post_type($productId)) {
                $variationId = $productId;
                $productId   = wp_get_post_parent_id($variationId);
            }

            $product = CacheHelper::getWcProduct($variationId ? $variationId : $productId);
            $persistentRuleCaches = $this->calculateCacheForProductWithRule($context, $productProcessor, $rule, $product);
            foreach ($persistentRuleCaches as $cache) {
                $data[] = PersistentRuleCache::fromArray($cache);
            }
        }

        return $data;
    }

    public function removeRule($ruleId)
    {

        global $wpdb;
        $table = $wpdb->prefix .  PersistentRuleModel::TABLE_NAME;

        $where = array('rule_id' => $ruleId);
        $wpdb->delete($table, $where);
    }

    /**
     * @param Context $context
     * @param \WC_Product $product
     * @param array $cartItemData
     */
    public function recalculateCacheForProduct($context, $product, $cartItemData = array())
    {
        $objects = $this->getCacheWithProduct($product);

        global $wpdb;
        $tableCache = $wpdb->prefix . PersistentRuleModel::TABLE_NAME;
        $wpdb->query('START TRANSACTION');

        foreach ( $objects as $object ) {
            if ($object === null || $object->rule === null) {
                continue;
            }

            $rule = $object->rule;
            $hash = $this->calculateDbHashWithProduct($product);
            $where  = array('rule_id' => $rule->getId(), 'product' => $hash);
            $result = $wpdb->delete($tableCache, $where);

            $cartCalculator   = new CartCalculatorPersistent($context, $rule);
            $productProcessor = new Processor($context, $cartCalculator);
            $cartBuilder      = new CartBuilder($context);
            $cart             = $cartBuilder->create(WC()->customer, WC()->session);
            $productProcessor->withCart($cart);
            foreach ($this->calculateCacheForProductWithRule($context, $productProcessor, $rule, $product, $cartItemData) as $data) {
                $result = $wpdb->insert($tableCache, $data);
            }
        }

        $wpdb->query('COMMIT');
    }

    /**
     * @param Context $context
     * @param \WC_Cart $wcCart
     *
     * @return array<int, PersistentRule>
     */
    public function getRulesFromWcCart($context, $wcCart)
    {
        $rules = array();

        $cartBuilder = new CartBuilder($context);
        $cart        = $cartBuilder->create(WC()->customer, WC()->session);
        $cartBuilder->populateCart($cart, $wcCart);

        foreach ($cart->getItems() as $item) {
            $objects = $this->getCache($item);

            $object = null;
            foreach ( $objects as $tmpObject ) {
                $tmpProcessor = $tmpObject->rule->buildProcessor($context);

                if ( $tmpProcessor->isRuleMatchedCart($cart) ) {
                    $object = $tmpObject;
                }
            }

            if ($object !== null && $object->rule !== null) {
                $rules[] = $object->rule;
            }
        }

        return $rules;
    }

    public function truncate() {
        global $wpdb;
        $tableCache = $wpdb->prefix . PersistentRuleModel::TABLE_NAME;
        $wpdb->query("TRUNCATE TABLE $tableCache");
    }

    /**
     * @param Context $context
     * @param Processor $productProcessor
     * @param PersistentRule $rule
     * @param \WC_Product $product
     * @param array $cartItemData
     */
    protected function calculateCacheForProductWithRule(
        $context,
        $productProcessor,
        $rule,
        $product,
        $cartItemData = array()
    ) {
        $data = array();
        $hash = $this->calculateDbHashWithProduct($product, $cartItemData);

        if ($rule->hasProductRangeAdjustment()) {
            $handler = $rule->getProductRangeAdjustmentHandler();
            $ranges  = $handler->getRanges();

            if ( count($ranges) > 0 ) {
                $range = $ranges[0];
                if ( $range->getFrom() !== INF && $range->getFrom() > 1.0 ) {
                    $ranges = array_merge(
                        array(
                            new RangeDiscount(
                                1,
                                $range->getFrom() - 1.0,
                                new Discount($context, Discount::TYPE_PERCENTAGE, 0)
                            )
                        ),
                        $ranges
                    );
                }
            }

            foreach ($ranges as $range) {
                $processedProduct = $productProcessor->calculateProduct($product, $range->getFrom(), $cartItemData);

                if ($processedProduct === null || $processedProduct instanceof ProcessedVariableProduct || $processedProduct instanceof ProcessedGroupedProduct) {
                    return $data;
                }

                $data[] = array(
                    'product'        => $hash,
                    'rule_id'        => $rule->getId(),
                    'qty_start'      => $range->getFrom(),
                    'qty_finish'     => $range->getTo() === INF ? null : $range->getTo(),
                    'original_price' => $processedProduct->getOriginalPrice(),
                    'price'          => $processedProduct->getCalculatedPrice(),
                );
            }
        } else {
            $processedProduct = $productProcessor->calculateProduct($product, 1.0, $cartItemData);

            if ($processedProduct === null || $processedProduct instanceof ProcessedVariableProduct || $processedProduct instanceof ProcessedGroupedProduct) {
                return $data;
            }

            $data[] = array(
                'product'        => $hash,
                'rule_id'        => $rule->getId(),
                'qty_start'      => 1.0,
                'qty_finish'     => null,
                'original_price' => $processedProduct->getOriginalPrice(),
                'price'          => $processedProduct->getCalculatedPrice(),
            );
        }

        return $data;
    }


    /**
     * @param CartItem|\WC_Product $item
     * @param float|null $qty
     *
     * @return array<int, PersistentRuleCacheObject>
     * @throws \Exception
     */
    protected function calculate($item, $qty = null)
    {
        $context = $this->context;

        if ($item instanceof CartItem) {
            $hash = $this->calculateDbHash($item);
            $qty  = ($qty !== null ? (float)$qty : $item->getQty());
        } elseif ($item instanceof \WC_Product) {
            $hash = $this->calculateDbHashWithProduct($item);
            $qty  = ($qty !== null ? (float)$qty : 1.0);
        } else {
            return array();
        }

        global $wpdb;

        $tableCache = $wpdb->prefix . PersistentRuleModel::TABLE_NAME;

        $query = $wpdb->prepare("SELECT persistent_rules_cache.rule_id, persistent_rules_cache.price
            FROM {$tableCache} AS persistent_rules_cache
            WHERE persistent_rules_cache.product = %s
            AND persistent_rules_cache.qty_start <= %s
            AND (persistent_rules_cache.qty_finish IS NULL OR persistent_rules_cache.qty_finish >= %s)",
            array($hash, $qty, $qty)
        );
        $rows  = $wpdb->get_results($query, ARRAY_A);

        if (count($rows) === 0) {
            return array();
        }

        $objects = [];
        foreach ( $rows as $row ) {
            $price = $row['price'];

            $ruleRepository = new RuleRepository();
            $ruleRows  = $ruleRepository->getRules(array('id' => $row['rule_id']));

            if (count($ruleRows) === 0) {
                return array();
            }

            $ruleRow = reset($ruleRows);

            /** @var RuleStorage $storage */
            $storage         = Factory::get("Database_RuleStorage");
            $rulesCollection = $storage->buildPersistentRules(array($ruleRow));
            $rule            = $rulesCollection->getFirst();

            if ($rule === null) {
                continue;
            }

            $objects[] = new PersistentRuleCacheObject($rule, $price);
        }

        return $objects;
    }


    /**
     * @param CartItem $item
     */
    protected function calculateDbHash($item)
    {
        $productId           = $item->getWcItem()->getProductId();
        $variationId         = $item->getWcItem()->getVariationId();
        $cartItemData        = array();
        $product             = $item->getWcItem()->getProduct();
        $variationAttributes = $product instanceof \WC_Product_Variation ? $product->get_variation_attributes() : array();

        return CacheHelper::calcHashPersistentRuleProduct(
            $productId,
            $variationId,
            $variationAttributes,
            $cartItemData
        );
    }

    /**
     * @param \WC_Product $product
     * @param array $cartItemData
     */
    protected function calculateDbHashWithProduct($product, $cartItemData = array())
    {
        $parentId            = $product->get_parent_id('edit');
        $productId           = $parentId ?: $product->get_id();
        $variationId         = $parentId ? $product->get_id() : 0;
        $variationAttributes = $product instanceof \WC_Product_Variation ? $product->get_variation_attributes() : array();

        return CacheHelper::calcHashPersistentRuleProduct(
            (string)$productId,
            (string)$variationId,
            $variationAttributes,
            $cartItemData
        );
    }

    /**
     * @param CartItem $item
     * @param float|null $qty
     */
    protected function calculateCacheHash($item, $qty = null)
    {
        return $this->calculateDbHash($item) . '_' . ($qty !== null ? (float) $qty : $item->getQty());
    }

    /**
     * @param \WC_Product $product
     */
    protected function calculateCacheHashWithProduct($product)
    {
        $qty = (string)(1.0);

        return $this->calculateDbHashWithProduct($product) . '_' . $qty;
    }
}
