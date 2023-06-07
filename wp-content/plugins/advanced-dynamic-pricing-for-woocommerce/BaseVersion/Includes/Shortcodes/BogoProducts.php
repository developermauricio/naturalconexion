<?php

namespace ADP\BaseVersion\Includes\Shortcodes;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\RuleStorage;
use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;
use ADP\Factory;

defined('ABSPATH') or exit;

class BogoProducts extends Products
{
    const NAME = 'adp_products_bogo';
    const STORAGE_KEY = 'wdp_products_bogo';

    protected function set_adp_products_bogo_query_args(&$queryArgs)
    {
        $queryArgs['post__in'] = array_merge(array(0), static::getCachedProductsIds());
    }

    /**
     * @return array
     */
    public static function getProductsIds($from = null, $count = null)
    {
        global $wpdb;

        $context = adp_context();

        $rulesCollection = CacheHelper::loadActiveRules();

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
            if (self::isSimpleBogoRule($rule)) {
                $sqlGenerator->applyRuleToQuery($rule);
            }
        }

        $sqlGenerator->limit($count);
        $sqlGenerator->offset($from);

        return $sqlGenerator->getProductIds();
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    protected static function isSimpleBogoRule($rule)
    {
        return
            $rule instanceof SingleItemRule &&
            ! $rule->getProductAdjustmentHandler() &&
            ! $rule->getProductRangeAdjustmentHandler() &&
            ! $rule->getRoleDiscounts() &&
            count($rule->getGifts()) === 0 &&
            count($rule->getItemGiftsCollection()->asArray()) > 0 &&
            count($rule->getConditions()) === 0 &&
            count($rule->getLimits()) === 0;
    }
}
