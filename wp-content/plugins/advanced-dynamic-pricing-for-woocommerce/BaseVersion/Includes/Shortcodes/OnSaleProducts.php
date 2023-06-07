<?php

namespace ADP\BaseVersion\Includes\Shortcodes;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Database\Database;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\RuleStorage;
use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;
use ADP\Factory;

defined('ABSPATH') or exit;

class OnSaleProducts extends Products
{
    const NAME = 'adp_products_on_sale';
    const STORAGE_KEY = 'wdp_products_onsale';
    const STORAGE_WITH_RULES_KEY = 'wdp_rules_products_onsale';

    protected function set_adp_products_on_sale_query_args(&$queryArgs)
    {
        if ($this->attributes['rule_id'] !== false) {
            $productIds = static::getCachedProductsIdsByRule($this->attributes['rule_id']);
        } else {
            $productIds = static::getCachedProductsIds();
        }

        if ($this->attributes["show_wc_onsale_products"])
            $queryArgs['post__in'] = array_unique(array_merge(array(0), $productIds, wc_get_product_ids_on_sale()));
        else
            $queryArgs['post__in'] = array_merge(array(0), $productIds);
    }

    /**
     * @param null $deprecated
     *
     * @return array
     */
    public static function getProductsIds($from = null, $count = null, $deprecated = null)
    {
        $context         = adp_context();
        $rulesCollection = CacheHelper::loadActiveRules($context);
        if ($context->isRuleSuppressed() || $context->getOption('rules_apply_mode') === "none") {
            $rulesArray = [];
        } else {
            $rulesArray = $rulesCollection->getRules();
        }

        /** @var RuleStorage $storage */
        $storage                   = Factory::get("Database_RuleStorage");
        $ruleRepository            = new RuleRepository();
        $rows                      = $ruleRepository->getRules(
            array(
                'active_only' => true,
                'rule_types'  => array(RuleTypeEnum::PERSISTENT()->getValue())
            )
        );
        $persistentRulesCollection = $storage->buildPersistentRules($rows);
        if ($context->isRuleSuppressed() || $context->getOption('rules_apply_mode') === "none") {
            $persistentRulesArray = [];
        } else {
            $persistentRulesArray = $persistentRulesCollection->getRules();
        }

        $rulesArray = array_merge($rulesArray, $persistentRulesArray);

        /** @var $sqlGenerator SqlGenerator */
        $sqlGenerator = Factory::get("Shortcodes_SqlGeneratorPersistent");

        foreach ($rulesArray as $rule) {
            if (self::isSimpleRule($rule)) {
                $sqlGenerator->applyRuleToQuery($rule);
            }
        }

        $sqlGenerator->limit($count);
        $sqlGenerator->offset($from);

        return $sqlGenerator->getProductIds();
    }

    public static function getProductsIdsPerRule($from = null, $count = null) {
        $context         = adp_context();
        $rulesCollection = CacheHelper::loadActiveRules($context);
        if ($context->isRuleSuppressed() || $context->getOption('rules_apply_mode') === "none") {
            $rulesArray = [];
        } else {
            $rulesArray = $rulesCollection->getRules();
        }

        /** @var RuleStorage $storage */
        $storage                   = Factory::get("Database_RuleStorage");
        $ruleRepository            = new RuleRepository();
        $rows                      = $ruleRepository->getRules(
            array(
                'active_only' => true,
                'rule_types'  => array(RuleTypeEnum::PERSISTENT()->getValue())
            )
        );
        $persistentRulesCollection = $storage->buildPersistentRules($rows);
        if ($context->isRuleSuppressed() || $context->getOption('rules_apply_mode') === "none") {
            $persistentRulesArray = [];
        } else {
            $persistentRulesArray = $persistentRulesCollection->getRules();
        }

        $rulesArray = array_merge($rulesArray, $persistentRulesArray);

        /** @var $sqlGenerator SqlGenerator */
        $sqlGenerator = Factory::get("Shortcodes_SqlGeneratorPersistent");

        $productsOnSalePerRule = array();

        foreach ($rulesArray as $rule) {
            if (self::isSimpleRule($rule)) {
                $sqlGenerator->clear();
                $sqlGenerator->applyRuleToQuery($rule);
                $sqlGenerator->limit($count);
                $sqlGenerator->offset($from);

                $productsOnSalePerRule[$rule->getId()] = $sqlGenerator->getProductIds();
            }
        }

        return $productsOnSalePerRule;
    }

    /**
     * @param array $ruleId
     * @return array
     */
    public static function getCachedProductsIdsByRule($ruleIds)
    {
        $productIdsPerRule = static::getCachedProductsIdsPerRule();

        $productIdsByRuleIds = array();
        foreach ($ruleIds as $ruleId) {
            $productIdsByRuleIds = array_merge($productIdsByRuleIds, $productIdsPerRule[$ruleId]);
        }

        return array_unique($productIdsByRuleIds);
    }

    /**
     * @return mixed
     */
    public static function getCachedProductsIdsPerRule()
    {

        // Load from cache.
        $productIdsPerRule = get_transient(static::STORAGE_WITH_RULES_KEY);

        // Valid cache found.
        if (false !== $productIdsPerRule) {
            return $productIdsPerRule;
        }

        return static::updateCachedProductsIdsPerRule();
    }

    /**
     * @return mixed
     */
    public static function updateCachedProductsIdsPerRule()
    {

        $productIdsPerRule = static::getProductsIdsPerRule();

        set_transient(static::STORAGE_WITH_RULES_KEY, $productIdsPerRule, DAY_IN_SECONDS * 30);

        return $productIdsPerRule;
    }

    public static function clearCache() {
        parent::clearCache();
        delete_transient(self::STORAGE_WITH_RULES_KEY);
    }

    public static function partialUpdateCachedProductsIds($from, $count)
    {
        $result     = static::getProductsIdsPerRule($from, $count);
        $productIdsPerRule = get_transient(static::STORAGE_WITH_RULES_KEY);
        if ($productIdsPerRule) {
            foreach ($productIdsPerRule as $ruleId => $productIdsForRule) {
                if (isset($result[$ruleId])) {
                    $productIdsPerRule[$ruleId] = array_merge($productIdsForRule, $result[$ruleId]);
                }
            }
        } else {
            $productIdsPerRule = $result;
        }

        set_transient(static::STORAGE_WITH_RULES_KEY, $productIdsPerRule, DAY_IN_SECONDS * 30);
        return parent::partialUpdateCachedProductsIds($from, $count);
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    protected static function isSimpleRule($rule)
    {
        return
            $rule instanceof SingleItemRule &&
            $rule->getProductAdjustmentHandler() &&
            ! $rule->getProductRangeAdjustmentHandler() &&
            ! $rule->getRoleDiscounts() &&
            count($rule->getGifts()) === 0 &&
            count($rule->getItemGiftsCollection()->asArray()) === 0 &&
            adp_functions()->isRuleMatchedCart($rule) &&
            count($rule->getLimits()) === 0;
    }

    /**
     * Parse attributes.
     *
     * @since  3.2.0
     * @param  array $attributes Shortcode attributes.
     * @return array
     */
    protected function parse_attributes( $attributes ) {
        $parsed_attributes = parent::parse_attributes( $attributes );
        //parse own attrubutes
        $parsed_attributes['show_wc_onsale_products'] = false;
        if ( isset($attributes['show_wc_onsale_products']) )
            $parsed_attributes['show_wc_onsale_products'] = wc_string_to_bool($attributes['show_wc_onsale_products']);
        $parsed_attributes['rule_id'] = false;
        if (isset($attributes['rule_id'])) {
            $parsed_attributes['rule_id'] = explode(',', $attributes['rule_id']);
        }
        return $parsed_attributes;
    }

}
