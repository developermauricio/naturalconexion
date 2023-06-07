<?php

namespace ADP\BaseVersion\Includes\Database;

use ADP\BaseVersion\Includes\Compatibility\Polylang\PolylangCmp;
use ADP\BaseVersion\Includes\Compatibility\PriceBasedOnCountryCmp;
use ADP\BaseVersion\Includes\Compatibility\Wpml\WpmlCmp;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustmentsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\Enums\ProductAdjustmentSplitDiscount;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\RuleTranslator;
use ADP\BaseVersion\Includes\Core\Rule\Limit\LimitsLoader;
use ADP\BaseVersion\Includes\Core\Rule\NoItemRule;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAdd;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAddChoice;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Filter;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Gift;
use ADP\BaseVersion\Includes\Core\Rule\Structures\GiftChoice;
use ADP\BaseVersion\Includes\Core\Rule\Structures\PackageItem;
use ADP\BaseVersion\Includes\Core\Rule\Structures\PackageItemFilter;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RangeDiscount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RoleDiscount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\SetDiscount;
use ADP\BaseVersion\Includes\Core\RuleProcessor\OptionsConverter;
use ADP\BaseVersion\Includes\Enums\AutoAddChoiceTypeEnum;
use ADP\BaseVersion\Includes\Enums\AutoAddModeEnum;
use ADP\BaseVersion\Includes\Enums\Exceptions\UnexpectedValueException;
use ADP\BaseVersion\Includes\Enums\GiftChoiceTypeEnum;
use ADP\BaseVersion\Includes\Enums\GiftModeEnum;
use ADP\Factory;
use Exception;

defined('ABSPATH') or exit;

class RuleStorage
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ConditionsLoader
     */
    protected $conditionsLoader;

    /**
     * @var LimitsLoader
     */
    protected $limitsLoader;

    /**
     * @var CartAdjustmentsLoader
     */
    protected $cartAdjLoader;

    /**
     * @var WpmlCmp
     */
    protected $wpmlCmp;

    /**
     * @var PolylangCmp
     */
    protected $polylangCmp;

    /**
     * @var PriceBasedOnCountryCmp
     */
    protected $priceBasedOnCountryCmp;

    /**
     * Temporary object to convert rules to new scheme
     * todo remove after upgrade DB to version 3.0
     *
     * @var OptionsConverter
     */
    protected $optionsConverter;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context          = adp_context();
        $this->conditionsLoader = Factory::get("Core_Rule_CartCondition_ConditionsLoader");
        $this->limitsLoader     = Factory::get("Core_Rule_Limit_LimitsLoader");
        $this->cartAdjLoader    = Factory::get('Core_Rule_CartAdjustment_CartAdjustmentsLoader');
        $this->optionsConverter = new OptionsConverter();
        $this->wpmlCmp          = new WpmlCmp();
        if ( $this->wpmlCmp->isActiveSitepress() && $this->wpmlCmp->isActiveWcWpml() ) {
            $this->wpmlCmp->replaceVariationDataStore();
            $this->wpmlCmp->addFilterPreloadedListLanguages();
            $this->wpmlCmp->modifyContext($this->context);
        }
        $this->priceBasedOnCountryCmp = new PriceBasedOnCountryCmp();

        $this->polylangCmp          = new PolylangCmp();
        if ( $this->polylangCmp->isActive() ) {
            $this->polylangCmp->addFilterPreloadedListLanguages();
            $this->polylangCmp->modifyContext($this->context);
        }
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
//        $this->conditionsLoader->withContext($this->context);
//        $this->limitsLoader->withContext($this->context);
//        $this->cartAdjLoader->withContext($this->context);
//        $this->priceBasedOnCountryCmp->withContext($this->context);
    }

    /**
     * @param array<int, Models\Rule> $rows
     *
     * @return RulesCollection
     */
    public function buildRules($rows): RulesCollection
    {
        $rules = array();

        /**
         * @var Models\Rule $row
         */
        foreach ($rows as $row) {
            $filters = isset($row->filters) ? $row->filters : array();
            $filter  = reset($filters);

            $filterFromIsSingle  = $filter !== false && floatval($filter['qty']) === floatval(1);
            $filterToIsNotSingle = $filter !== false
                                   && $this->context->getOption("show_qty_range_in_product_filter")
                                   && isset($filter['qty_end'])
                                   && (floatval($filter['qty_end']) > floatval(1));

            if (count($filters) === 1 && $filter !== false && $filterFromIsSingle && !$filterToIsNotSingle) {
                $rule = $this->buildSingleItemRule($row);
            } elseif (count($filters) > 1 || (count($filters) === 1 && $filter !== false && ( ! $filterFromIsSingle || $filterToIsNotSingle))) {
                $rule = $this->buildPackageRule($row);
            } elseif (( ! isset($row->productAdjustments['total']['value']) || $row->productAdjustments['total']['value'] === "") ||
                      ( ! isset($row->bulkAdjustments['ranges']) || count($row->bulkAdjustments['ranges']) == 0) ||
                      ( ! isset($row->roleDiscounts['rows']) || count($row->roleDiscounts['rows']) == 0)
            ) {
                $rule   = $this->buildSingleItemRule($row);
                $filter = new Filter();
                $filter->setType($filter::TYPE_ANY);
                $filter->setMethod($filter::METHOD_IN_LIST);
                $rule->addFilter($filter);
            } else {
                $rule = $this->buildNoItemRule($row);
            }

            $implodeRecursive = function ($item) use (&$implodeRecursive) {
                return is_array($item) ? implode("_", array_map($implodeRecursive, $item)) : serialize($item);
            };

            $rule->setHash(md5($implodeRecursive($row)));

            if ($this->wpmlCmp->isActiveSitepress() && $this->wpmlCmp->shouldTranslate()) {
                if ($this->wpmlCmp->isActiveWcWpml()) {
                    $rule = $this->wpmlCmp->changeRuleCurrency($rule);
                }

                if ( $this->wpmlCmp->getObjectInternationalization() && $this->context->isTranslateRules() ) {
                    $rule = RuleTranslator::translate($rule, $this->wpmlCmp->getObjectInternationalization());
                }
            } else if ($this->polylangCmp->isActive() && $this->polylangCmp->shouldTranslate()) {
                if ( $this->polylangCmp->getObjectInternationalization() && $this->context->isTranslateRules() ) {
                    $rule = RuleTranslator::translate($rule, $this->polylangCmp->getObjectInternationalization());
                }
            }

            if ( $this->priceBasedOnCountryCmp->isActive() ) {
                $rule = $this->priceBasedOnCountryCmp->changeRuleCurrency($rule);
            }

            $currencySwitcher = $this->context->currencyController;
            if ($currencySwitcher->isCurrencyChanged()) {
                $rule = RuleTranslator::setCurrency($rule, $this->context->currencyController->getRate());
            }
            $rule->setCurrency($this->context->currencyController->getCurrentCurrency());

            $rule->setBlocks($row->additional['blocks'] ?? []);

            $rules[] = apply_filters('adp_rule_loaded', $rule, $row->getData());
        }

        return new RulesCollection($rules);
    }

    /**
     * @param array<int, Models\Rule> $rows
     *
     * @return RulesCollection
     */
    public function buildPersistentRules($rows): RulesCollection
    {
        $rules = array();

        foreach ($rows as $row) {
            $rule = $this->buildPersistentRule($row);

            $implodeRecursive = function ($item) use (&$implodeRecursive) {
                return is_array($item) ? implode("_", array_map($implodeRecursive, $item)) : serialize($item);
            };

            $rule->setHash(md5($implodeRecursive($row)));

            if ($this->wpmlCmp->isActiveSitepress() && $this->wpmlCmp->shouldTranslate()) {
                if ($this->wpmlCmp->isActiveWcWpml()) {
                    $rule = $this->wpmlCmp->changeRuleCurrency($rule);
                }

                if ( $this->wpmlCmp->getObjectInternationalization() && $this->context->isTranslateRules() ) {
                    $rule = RuleTranslator::translate($rule, $this->wpmlCmp->getObjectInternationalization());
                }
            } else if ($this->polylangCmp->isActive() && $this->polylangCmp->shouldTranslate()) {
                if ( $this->polylangCmp->getObjectInternationalization() && $this->context->isTranslateRules() ) {
                    $rule = RuleTranslator::translate($rule, $this->polylangCmp->getObjectInternationalization());
                }
            }

            if ( $this->priceBasedOnCountryCmp->isActive() ) {
                $rule = $this->priceBasedOnCountryCmp->changeRuleCurrency($rule);
            }

            $currencySwitcher = $this->context->currencyController;
            if ($currencySwitcher->isCurrencyChanged()) {
                $rule = RuleTranslator::setCurrency($rule, $this->context->currencyController->getRate());
            }
            $rule->setCurrency($this->context->currencyController->getCurrentCurrency());

            $rules[] = apply_filters('adp_rule_loaded', $rule, $row);
        }

        return new RulesCollection($rules);
    }

    /**
     * @param Models\Rule $ruleData
     *
     * @return PersistentRule
     * @throws Exception
     */
    protected function buildPersistentRule($ruleData)
    {
        $context = $this->context;
        /** @var PersistentRule $rule */
        $rule = Factory::get("Core_Rule_PersistentRule");

        if (isset($ruleData->id)) {
            $rule->setId($ruleData->id);
        }

        $rule->setTitle($ruleData->title);
        $rule->setEnabled($ruleData->enabled);

        if (isset($ruleData->additional['conditions_relationship'])) {
            $rule->setConditionsRelationship($ruleData->additional['conditions_relationship']);
        }

        if (isset($ruleData->additional['trigger_coupon_code'])) {
            $rule->setActivationCouponCode($ruleData->additional['trigger_coupon_code']);
        }

        if (isset($ruleData->additional['date_from'])) {
            $dateFrom = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_from'],
                new \DateTimeZone("UTC")
            );

            if ( $dateFrom ) {
                $dateFrom->setTime(0, 0, 0);
                $rule->setDateFrom($dateFrom);
            }
        }

        if (isset($ruleData->additional['date_to'])) {
            $dateTo = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_to'],
                new \DateTimeZone("UTC")
            );

            if ( $dateTo ) {
                $dateTo->setTime(0, 0, 0);
                $rule->setDateTo($dateTo);
            }
        }

        if (isset($ruleData->options['repeat'])) {
            $rule->setItemsCountLimit($ruleData->options['repeat']);
        }

        foreach ($ruleData->filters as $filterData) {
            $type   = $filterData['type'];
            $method = $filterData['method'] ?? "";
            $value  = $filterData['value'];

            $filter = new Filter();
            $filter->setType($type);
            $filter->setMethod($method);
            $filter->setValue($value);

            if (isset($filterData['product_exclude']['values'])) {
                $filter->setExcludeProductIds($filterData['product_exclude']['values']);
            }

            if (isset($filterData['product_exclude']['on_wc_sale'])) {
                $filter->setExcludeWcOnSale($filterData['product_exclude']['on_wc_sale'] === "1");
            }

            if (isset($filterData['product_exclude']['already_affected'])) {
                $filter->setExcludeAlreadyAffected($filterData['product_exclude']['already_affected'] === "1");
            }

            if (isset($filterData['product_exclude']['backorder'])) {
                $filter->setExcludeBackorder($filterData['product_exclude']['backorder'] === "1");
            }

            $rule->addFilter($filter);
        }

        $this->installProductAdjustment($rule, $ruleData);

        $this->installFreeItems($rule, $ruleData);

        $this->installConditions($rule, $ruleData);
        $this->installLimits($rule, $ruleData);
        $this->installAdvertising($rule, $ruleData);

        $this->installConditionMessage($rule, $ruleData);

        return $rule;
    }

    /**
     * @param Models\Rule $ruleData
     *
     * @return NoItemRule
     */
    protected function buildNoItemRule($ruleData)
    {
        /** @var NoItemRule $rule */
        $rule = Factory::get("Core_Rule_NoItemRule");

        if (isset($ruleData->id)) {
            $rule->setId($ruleData->id);
        }

        $rule->setTitle($ruleData->title);
        $rule->setEnabled($ruleData->enabled);
        $rule->setPriority($ruleData->priority);
        if (isset($ruleData->additional['conditions_relationship'])) {
            $rule->setConditionsRelationship($ruleData->additional['conditions_relationship']);
        }

        if (isset($ruleData->additional['trigger_coupon_code'])) {
            $rule->setActivationCouponCode($ruleData->additional['trigger_coupon_code']);
        }

        if (isset($ruleData->additional['date_from'])) {
            $dateFrom = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_from'],
                new \DateTimeZone("UTC")
            );

            if ( $dateFrom ) {
                $dateFrom->setTime(0, 0, 0);
                $rule->setDateFrom($dateFrom);
            }
        }

        if (isset($ruleData->additional['date_to'])) {
            $dateTo = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_to'],
                new \DateTimeZone("UTC")
            );

            if ( $dateTo ) {
                $dateTo->setTime(0, 0, 0);
                $rule->setDateTo($dateTo);
            }
        }

        $this->installCartAdjustments($rule, $ruleData);
        $this->installConditions($rule, $ruleData);
        $this->installLimits($rule, $ruleData);
        $this->installAdvertising($rule, $ruleData);

        return $rule;
    }

    /**
     * @param Models\Rule $ruleData
     *
     * @return PackageRule
     * @throws Exception
     */
    protected function buildPackageRule($ruleData)
    {
        $context = $this->context;
        /** @var PackageRule $rule */
        $rule = Factory::get("Core_Rule_PackageRule");

        if (isset($ruleData->id)) {
            $rule->setId($ruleData->id);
        }

        $rule->setTitle($ruleData->title);
        $rule->setEnabled($ruleData->enabled);
        $rule->setPriority($ruleData->priority);
        if (isset($ruleData->additional['conditions_relationship'])) {
            $rule->setConditionsRelationship($ruleData->additional['conditions_relationship']);
        }

        if (isset($ruleData->additional['trigger_coupon_code'])) {
            $rule->setActivationCouponCode($ruleData->additional['trigger_coupon_code']);
        }

        if (isset($ruleData->additional['date_from'])) {
            $dateFrom = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_from'],
                new \DateTimeZone("UTC")
            );

            if ( $dateFrom ) {
                $dateFrom->setTime(0, 0, 0);
                $rule->setDateFrom($dateFrom);
            }
        }

        if (isset($ruleData->additional['date_to'])) {
            $dateTo = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_to'],
                new \DateTimeZone("UTC")
            );

            if ( $dateTo ) {
                $dateTo->setTime(0, 0, 0);
                $rule->setDateTo($dateTo);
            }
        }

        if (isset($ruleData->options['repeat'])) {
            $rule->setPackagesCountLimit($ruleData->options['repeat']);
        }

        if (isset($ruleData->options['apply_to'])) {
            $rule->setApplyFirstTo($ruleData->options['apply_to']);
        }

        foreach ($ruleData->filters as $filterData) {
            $item = $this->createRulePackage($filterData);
            $rule->addPackage($item);
        }

        $this->installProductAdjustment($rule, $ruleData);
        $this->installRoleDiscounts($rule, $ruleData);
        $this->installSortableProperties($rule, $ruleData);

        $this->installFreeItems($rule, $ruleData);

        $this->installCartAdjustments($rule, $ruleData);
        $this->installConditions($rule, $ruleData);
        $this->installLimits($rule, $ruleData);
        $this->installAdvertising($rule, $ruleData);
        $this->installConditionMessage($rule, $ruleData);

        return $rule;
    }

    /**
     * @param array $filterData
     *
     * @return PackageItem
     */
    protected function createRulePackage($filterData)
    {
        $context = $this->context;

        $type   = $filterData['type'];
        $method = $filterData['method'] ?? "";
        $value  = $filterData['value'];
        $qty    = $filterData['qty'];
        $qtyEnd = isset($filterData['qty_end']) ? $filterData['qty_end'] : $qty;

        $item = new PackageItem();
        $item->setQty($qty);
        $item->setQtyEnd($qtyEnd);

        $filter = new PackageItemFilter();
        $filter->setType($type);
        $filter->setMethod($method);
        $filter->setValue($value);

        if (isset($filterData['product_exclude']['values'])) {
            $filter->setExcludeProductIds($filterData['product_exclude']['values']);
        }

        if (isset($filterData['product_exclude']['on_wc_sale'])) {
            $filter->setExcludeWcOnSale($filterData['product_exclude']['on_wc_sale'] === "1");
        }

        if (isset($filterData['product_exclude']['already_affected'])) {
            $filter->setExcludeAlreadyAffected($filterData['product_exclude']['already_affected'] === "1");
        }

        if (isset($filterData['product_exclude']['backorder'])) {
            $filter->setExcludeBackorder($filterData['product_exclude']['backorder'] === "1");
        }

        if (isset($filterData['limitation'])) {
            $item->setLimitation($filterData['limitation']);
        }

        $item->addFilter($filter);

        return $item;
    }

    /**
     * @param Models\Rule $ruleData
     *
     * @return SingleItemRule
     * @throws Exception
     */
    protected function buildSingleItemRule($ruleData)
    {
        $context = $this->context;
        /** @var SingleItemRule $rule */
        $rule = Factory::get("Core_Rule_SingleItemRule");

        if (isset($ruleData->id)) {
            $rule->setId($ruleData->id);
        }

        $rule->setTitle($ruleData->title);
        $rule->setEnabled($ruleData->enabled);
        $rule->setPriority($ruleData->priority);

        if (isset($ruleData->additional['conditions_relationship'])) {
            $rule->setConditionsRelationship($ruleData->additional['conditions_relationship']);
        }

        if (isset($ruleData->additional['trigger_coupon_code'])) {
            $rule->setActivationCouponCode($ruleData->additional['trigger_coupon_code']);
        }

        if (isset($ruleData->additional['date_from'])) {
            $dateFrom = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_from'],
                new \DateTimeZone("UTC")
            );

            if ( $dateFrom ) {
                $dateFrom->setTime(0, 0, 0);
                $rule->setDateFrom($dateFrom);
            }
        }

        if (isset($ruleData->additional['date_to'])) {
            $dateTo = \DateTime::createFromFormat(
                "Y-m-d",
                $ruleData->additional['date_to'],
                new \DateTimeZone("UTC")
            );

            if ( $dateTo ) {
                $dateTo->setTime(0, 0, 0);
                $rule->setDateTo($dateTo);
            }
        }

        if (isset($ruleData->options['repeat'])) {
            $rule->setItemsCountLimit($ruleData->options['repeat']);
        }

        if (isset($ruleData->options['apply_to'])) {
            $rule->setApplyFirstTo($ruleData->options['apply_to']);
        }

        foreach ($ruleData->filters as $filterData) {
            $type   = $filterData['type'];
            $method = $filterData['method'] ?? 'in_list'; //for persistent rules where default method is in_list
            $value  = $filterData['value'];

            $filter = new Filter();
            $filter->setType($type);
            $filter->setMethod($method);
            $filter->setValue($value);

            if (isset($filterData['product_exclude']['values'])) {
                $filter->setExcludeProductIds($filterData['product_exclude']['values']);
            }

            if (isset($filterData['product_exclude']['on_wc_sale'])) {
                $filter->setExcludeWcOnSale($filterData['product_exclude']['on_wc_sale'] === "1");
            }

            if (isset($filterData['product_exclude']['already_affected'])) {
                $filter->setExcludeAlreadyAffected($filterData['product_exclude']['already_affected'] === "1");
            }

            if (isset($filterData['product_exclude']['backorder'])) {
                $filter->setExcludeBackorder($filterData['product_exclude']['backorder'] === "1");
            }

            $rule->addFilter($filter);
        }

        $this->installProductAdjustment($rule, $ruleData);
        $this->installRoleDiscounts($rule, $ruleData);
        $this->installSortableProperties($rule, $ruleData);

        $this->installFreeItems($rule, $ruleData);

        $this->installCartAdjustments($rule, $ruleData);
        $this->installConditions($rule, $ruleData);
        $this->installLimits($rule, $ruleData);
        $this->installAdvertising($rule, $ruleData);
        $this->installConditionMessage($rule, $ruleData);

        return $rule;
    }

    /**
     * @param Rule $rule
     * @param Models\Rule $ruleData
     *
     * @throws Exception
     */
    protected function installProductAdjustment(&$rule, $ruleData)
    {
        $replaceDiscount     = isset($ruleData->additional['is_replace']) ? $ruleData->additional['is_replace'] === 'on' : false;
        $replaceDiscountName = isset($ruleData->additional['replace_name']) ? $ruleData->additional['replace_name'] : "";

        if (isset($ruleData->bulkAdjustments['ranges'])) {//check rule for having bulk adj
            $bulkData = $ruleData->bulkAdjustments;

            $qty_based = $bulkData['qty_based'];

            if ($rule instanceof SingleItemRule) {
                if ($qty_based === 'all') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_DEFAULT;
                } elseif ($qty_based === 'total_qty_in_cart') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_ALL_ITEMS_IN_CART;
                } elseif ($qty_based === 'product_categories') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_PRODUCT_CATEGORIES;
                } elseif ($qty_based === 'product_selected_categories') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_PRODUCT_SELECTED_CATEGORIES;
                } elseif ($qty_based === 'selected_products') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_PRODUCT_SELECTED_PRODUCTS;
                } elseif ($qty_based === 'sets') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_DEFAULT;
                } elseif ($qty_based === 'product') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_PRODUCT;
                } elseif ($qty_based === 'variation') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_VARIATION;
                } elseif ($qty_based === 'cart_position') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_CART_POSITIONS;
                } elseif ($qty_based === 'meta_data') {
                    $qty_based = SingleItemRule\ProductsRangeAdjustments::GROUP_BY_META_DATA;
                }

                $productAdjustment = new SingleItemRule\ProductsRangeAdjustments($this->context, $bulkData['type'],
                    $qty_based);
            } elseif ($rule instanceof PackageRule) {
                if ($qty_based === 'all') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_DEFAULT;
                } elseif ($qty_based === 'total_qty_in_cart') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_ALL_ITEMS_IN_CART;
                } elseif ($qty_based === 'product_categories') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_PRODUCT_CATEGORIES;
                } elseif ($qty_based === 'product_selected_categories') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_PRODUCT_SELECTED_CATEGORIES;
                } elseif ($qty_based === 'selected_products') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_PRODUCT_SELECTED_PRODUCTS;
                } elseif ($qty_based === 'sets') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_SETS;
                } elseif ($qty_based === 'product') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_PRODUCT;
                } elseif ($qty_based === 'variation') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_VARIATION;
                } elseif ($qty_based === 'cart_position') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_CART_POSITIONS;
                } elseif ($qty_based === 'meta_data') {
                    $qty_based = PackageRule\PackageRangeAdjustments::GROUP_BY_META_DATA;
                }

                $productAdjustment = new PackageRule\PackageRangeAdjustments($this->context, $bulkData['type'],
                    $qty_based);
            } else {
                return;
            }

            if (isset($bulkData['selected_products']) && is_array($bulkData['selected_products'])) {
                $productAdjustment->setSelectedProductIds($bulkData['selected_products']);
            }

            if (isset($bulkData['selected_categories']) && is_array($bulkData['selected_categories'])) {
                $productAdjustment->setSelectedCategoryIds($bulkData['selected_categories']);
            }

            $rangeDiscounts = array();
            foreach ($bulkData['ranges'] as $range) {
                if ($productAdjustment instanceof SingleItemRule\ProductsRangeAdjustments) {
                    $bulkData['discount_type'] = str_replace('set_', '', $bulkData['discount_type']);
                    if ($bulkData['discount_type'] === 'price__fixed') {
                        if ( $range['value'] === '' ) {
                            $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, 0);
                        } else {
                            $discount = new Discount($this->context, Discount::TYPE_FIXED_VALUE, $range['value']);
                        }
                    } elseif ($bulkData['discount_type'] === 'discount__amount') {
                        $discount = new Discount($this->context, Discount::TYPE_AMOUNT, $range['value']);
                    } else {
                        $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, $range['value']);
                    }
                } elseif ($productAdjustment instanceof PackageRule\PackageRangeAdjustments) {
                    if (strpos($bulkData['discount_type'], 'set') === false) {
                        if ($bulkData['discount_type'] === 'price__fixed') {
                            if ( $range['value'] === '' ) {
                                $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, 0);
                            } else {
                                $discount = new Discount($this->context, Discount::TYPE_FIXED_VALUE, $range['value']);
                            }
                        } elseif ($bulkData['discount_type'] === 'discount__amount') {
                            $discount = new Discount($this->context, Discount::TYPE_AMOUNT, $range['value']);
                        } else {
                            $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, $range['value']);
                        }
                    } else {
                        if ($bulkData['discount_type'] === 'set_price__fixed') {
                            if ( $range['value'] === '' ) {
                                $discount = new SetDiscount($this->context, SetDiscount::TYPE_PERCENTAGE, 0);
                            } else {
                                $discount = new SetDiscount($this->context, SetDiscount::TYPE_FIXED_VALUE, $range['value']);
                            }
                        } elseif ($bulkData['discount_type'] === 'set_discount__amount') {
                            $discount = new SetDiscount($this->context, SetDiscount::TYPE_AMOUNT, $range['value']);
                        } else {
                            $discount = new SetDiscount($this->context, SetDiscount::TYPE_PERCENTAGE, $range['value']);
                        }
                    }
                } else {
                    return;
                }

                $rangeDiscounts[] = new RangeDiscount($range['from'], $range['to'], $discount);
            }

            /**
             * Fixed: range discount doesn't work if ending values are empty
             * e.g.
             * 1 - INF
             * 2 - INF
             * 3 - INF
             */
            foreach ($rangeDiscounts as $index => &$rangeDiscount) {
                if ($rangeDiscount->getTo() === INF && isset($rangeDiscounts[$index + 1])) {
                    $nextItem      = $rangeDiscounts[$index + 1];
                    $rangeDiscount = new RangeDiscount(
                        $rangeDiscount->getFrom(),
                        $nextItem->getFrom() - 1,
                        $rangeDiscount->getData()
                    );
                }
            }

            $productAdjustment->setRanges($rangeDiscounts);

            $productAdjustment->setReplaceAsCartAdjustment($replaceDiscount);
            $productAdjustment->setReplaceCartAdjustmentCode($replaceDiscountName);

            if (isset($bulkData['table_message'])) {
                $productAdjustment->setPromotionalMessage($bulkData['table_message']);
            }

            $rule->installProductRangeAdjustmentHandler($productAdjustment);
        }

        if (isset($ruleData->productAdjustments, $ruleData->productAdjustments['type'])) {
            $prodAdjData = $ruleData->productAdjustments;
            $type        = $prodAdjData['type'];

            if ($type === 'total' and isset($prodAdjData['total']['type'])) {//check rule for having total adj
                $value        = $prodAdjData['total']['value'];
                $discountType = $prodAdjData['total']['type'];

                if ($discountType === 'price__fixed') {
                    if ( $value === '' ) {
                        $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, 0);
                    } else {
                        $discount = new Discount($this->context, Discount::TYPE_FIXED_VALUE, $value);
                    }
                } elseif ($discountType === 'price__fixed_per_item') {
                    if ( $value === '' ) {
                        $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, 0);
                    } else {
                        $discount = new Discount($this->context, Discount::TYPE_FIXED_VALUE_PER_ITEM, $value);
                    }
                } elseif ($discountType === 'discount__percentage') {
                    $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, $value);
                } elseif ($discountType === 'discount__amount') {
                    $discount = new Discount($this->context, Discount::TYPE_AMOUNT, $value);
                } elseif ($discountType === 'discount__amount_per_item') {
                    $discount = new Discount($this->context, Discount::TYPE_AMOUNT_PER_ITEM, $value);
                } else {
                    return;
                }

                if ($rule instanceof SingleItemRule) {
                    $productAdjustment = new SingleItemRule\ProductsAdjustment($discount);
                } elseif ($rule instanceof PackageRule) {
                    $productAdjustment = new PackageRule\ProductsAdjustmentTotal($discount);
                } else {
                    return;
                }

                $productAdjustment->setReplaceAsCartAdjustment($replaceDiscount);
                $productAdjustment->setReplaceCartAdjustmentCode($replaceDiscountName);
                if (isset($prodAdjData['max_discount_sum']) && is_numeric($prodAdjData['max_discount_sum'])) {
                    $productAdjustment->setMaxAvailableAmount((float)$prodAdjData['max_discount_sum']);
                }

                if (isset($prodAdjData['split_discount_by'])) {
                    if ($productAdjustment instanceof PackageRule\ProductsAdjustmentTotal) {
                        try {
                            $splitDiscount = new ProductAdjustmentSplitDiscount($prodAdjData['split_discount_by']);
                        } catch (UnexpectedValueException $e) {
                            $splitDiscount = ProductAdjustmentSplitDiscount::ITEM_COST();
                        }

                        $productAdjustment->setSplitDiscount($splitDiscount);
                    }
                }

                $rule->installProductAdjustmentHandler($productAdjustment);
            } elseif ($type === 'split' and isset($prodAdjData['split'][0]['type'])) {//check rule for having split adj
                if ($rule instanceof SingleItemRule) {
                    return;
                }

                $discounts = array();
                foreach ($prodAdjData[$type] as $split_discount) {
                    $value        = $split_discount['value'];
                    $discountType = $split_discount['type'];

                    if ($discountType === 'price__fixed') {
                        if ( $value === '' ) {
                            $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, 0);
                        } else {
                            $discount = new Discount($this->context, Discount::TYPE_FIXED_VALUE, $value);
                        }
                    } elseif ($discountType === 'price__fixed_per_item') {
                        if ( $value === '' ) {
                            $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, 0);
                        } else {
                            $discount = new Discount($this->context, Discount::TYPE_FIXED_VALUE_PER_ITEM, $value);
                        }
                    } elseif ($discountType === 'discount__amount') {
                        $discount = new Discount($this->context, Discount::TYPE_AMOUNT, $value);
                    } elseif ($discountType === 'discount__amount_per_item') {
                        $discount = new Discount($this->context, Discount::TYPE_AMOUNT_PER_ITEM, $value);
                    } else {
                        $discount = new Discount($this->context, Discount::TYPE_PERCENTAGE, $value);
                    }

                    $discounts[] = $discount;
                }

                $productAdjustment = new PackageRule\ProductsAdjustmentSplit($discounts);
                $productAdjustment->setReplaceAsCartAdjustment($replaceDiscount);
                $productAdjustment->setReplaceCartAdjustmentCode($replaceDiscountName);
                if (isset($prodAdjData['max_discount_sum']) && is_numeric($prodAdjData['max_discount_sum'])) {
                    $productAdjustment->setMaxAvailableAmount((float)$prodAdjData['max_discount_sum']);
                }

                if (isset($prodAdjData['split_discount_by'])) {
                    try {
                        $splitDiscount = new ProductAdjustmentSplitDiscount($prodAdjData['split_discount_by']);
                    } catch (UnexpectedValueException $e) {
                        $splitDiscount = ProductAdjustmentSplitDiscount::ITEM_COST();
                    }

                    $productAdjustment->setSplitDiscount($splitDiscount);
                }

                $rule->installProductAdjustmentHandler($productAdjustment);
            }
        }
    }

    /**
     * @param SingleItemRule|PackageRule $rule
     * @param Models\Rule $ruleData
     */
    protected function installRoleDiscounts(&$rule, $ruleData)
    {
        $replaceDiscount     = isset($ruleData->additional['is_replace']) ? $ruleData->additional['is_replace'] === 'on' : false;
        $replaceDiscountName = isset($ruleData->additional['replace_name']) ? $ruleData->additional['replace_name'] : "";

        if ( ! isset($ruleData->roleDiscounts['rows'])) {
            return;
        }

        $roleDiscounts = array();
        foreach ($ruleData->roleDiscounts['rows'] as $row) {
            $type  = isset($row['discount_type']) ? $row['discount_type'] : null;
            $value = isset($row['discount_value']) ? $row['discount_value'] : null;
            $roles = isset($row['roles']) ? $row['roles'] : array();

            if ( ! isset($type, $value)) {
                continue;
            }

            if ($type === 'discount__percentage') {
                $type = Discount::TYPE_PERCENTAGE;
            } elseif ($type === 'discount__amount') {
                $type = Discount::TYPE_AMOUNT;
            } elseif ($type === 'price__fixed') {
                if ( $value === '' ) {
                    $type = Discount::TYPE_PERCENTAGE;
                    $value = 0;
                } else {
                    $type = Discount::TYPE_FIXED_VALUE;
                }
            }

            $roleDiscount = new RoleDiscount(new Discount($this->context, $type, $value));
            $roleDiscount->setReplaceAsCartAdjustment($replaceDiscount);
            $roleDiscount->setReplaceCartAdjustmentCode($replaceDiscountName);
            $roleDiscount->setRoles($roles);
            $roleDiscounts[] = $roleDiscount;
        }

        $rule->setRoleDiscounts($roleDiscounts);
    }

    /**
     * @param Rule $rule
     * @param Models\Rule $ruleData
     */
    protected function installConditions(&$rule, $ruleData)
    {
        $conditionsLoader = $this->conditionsLoader;

        if ( ! empty($ruleData->conditions)) {
            foreach ($ruleData->conditions as $conditionData) {
                try {
                    $rule->addCondition($conditionsLoader->build($conditionData));
                } catch (Exception $exception) {
                    $this->context->handleError($exception);
                }
            }
        }
    }

    /**
     * @param Rule $rule
     * @param Models\Rule $ruleData
     */
    protected function installLimits(&$rule, $ruleData)
    {
        $converter    = $this->optionsConverter;
        $limitsLoader = $this->limitsLoader;

        if ( ! empty($ruleData->limits)) {
            foreach ($ruleData->limits as $limitData) {
                try {
                    $limitData = $converter::convertLimit($limitData);
                    $rule->addLimit($limitsLoader->build($limitData));
                } catch (Exception $exception) {
                    $this->context->handleError($exception);
                }
            }
        }
    }

    /**
     * @param Rule $rule
     * @param Models\Rule $ruleData
     */
    protected function installCartAdjustments(&$rule, $ruleData)
    {
        $converter     = $this->optionsConverter;
        $cartAdjLoader = $this->cartAdjLoader;

        if ( ! empty($ruleData->cartAdjustments)) {
            foreach ($ruleData->cartAdjustments as $cartAdjData) {
                try {
                    $cartAdjData = $converter::convertCartAdj($cartAdjData);
                    $rule->addCartAdjustment($cartAdjLoader->build($cartAdjData));
                } catch (Exception $exception) {
                    $this->context->handleError($exception);
                }
            }
        }
    }

    /**
     * @param SingleItemRule|PackageRule|PersistentRule $rule
     * @param Models\Rule $ruleData
     */
    protected function installFreeItems(&$rule, $ruleData)
    {
        $context = $this->context;

        // for Persistence rules
        if ( method_exists($rule, 'setReplaceItemGifts') && method_exists($rule, 'setReplaceItemGiftsCode')) {
            $replaceFreeProducts = isset($ruleData->additional['is_replace_free_products_with_discount']) ? $ruleData->additional['is_replace_free_products_with_discount'] === 'on' : false;
            $rule->setReplaceItemGifts($replaceFreeProducts);
            $replaceFreeProductsName = isset($ruleData->additional['free_products_replace_name']) ? $ruleData->additional['free_products_replace_name'] : "";
            $rule->setReplaceItemGiftsCode($replaceFreeProductsName);
        }

        $rule = $this->setGiftItemStrategy($rule, $ruleData);

        $values = isset($ruleData->getProducts['value']) ? $ruleData->getProducts['value'] : array();
        $gifts  = array();
        foreach ($values as $value) {
            $qty        = floatval($value['qty']);
            $giftValues = isset($value['value']) ? array_map('intval', $value['value']) : array();
            $giftMode   = isset($value['gift_mode']) ? $value['gift_mode'] : "giftable_products";

            $gift = new Gift();

            if ($giftMode === "use_product_from_filter") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::CLONE_ADJUSTED))));
                $gift->setMode(GiftModeEnum::USE_PRODUCT_FROM_FILTER());
            } elseif ($giftMode === "use_only_first_product_from_filter") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::CLONE_ADJUSTED_FIRST))));
                $gift->setMode(GiftModeEnum::USE_ONLY_FIRST_PRODUCT_FROM_FILTER());
            }  elseif ($giftMode === "giftable_products") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::PRODUCT))->setValues($giftValues)));
                $gift->setMode(GiftModeEnum::GIFTABLE_PRODUCTS());
            } elseif ($giftMode === "giftable_products_in_rotation") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::PRODUCT))->setValues($giftValues)));
                $gift->setMode(GiftModeEnum::GIFTABLE_PRODUCTS_ROTATION());
            } elseif ($giftMode === "giftable_products_in_random") { // removed
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::PRODUCT))->setValues($giftValues)));
                $gift->setMode(GiftModeEnum::GIFTABLE_PRODUCTS_ROTATION()); // replaced with "giftable_products_in_rotation"
            } elseif ($giftMode === "allow_to_choose") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::PRODUCT))->setValues($giftValues)));
                $gift->setMode(GiftModeEnum::ALLOW_TO_CHOOSE());
            } elseif ($giftMode === "allow_to_choose_from_product_cat") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::CATEGORY))->setValues($giftValues)));
                $gift->setMode(GiftModeEnum::ALLOW_TO_CHOOSE_FROM_PRODUCT_CAT());
            } elseif ($giftMode === "require_to_choose") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::PRODUCT))->setValues($giftValues)));
                $gift->setMode(GiftModeEnum::REQUIRE_TO_CHOOSE());
            } elseif ($giftMode === "require_to_choose_from_product_cat") {
                $gift->setChoices(array((new GiftChoice())->setType(new GiftChoiceTypeEnum(GiftChoiceTypeEnum::CATEGORY))->setValues($giftValues)));
                $gift->setMode(GiftModeEnum::REQUIRE_TO_CHOOSE_FROM_PRODUCT_CAT());
            }

            $gift->setQty($qty);
            $gifts[] = $gift;
        }
        $rule->setItemGifts($gifts);
    }

    /**
     * @param SingleItemRule|PackageRule $rule
     * @param Models\Rule $ruleData
     */
    protected function installSortableProperties(&$rule, $ruleData)
    {
        if (isset($ruleData->sortableBlocksPriority)) {
            $rule->setSortableBlocksPriority($ruleData->sortableBlocksPriority);
        }

        if (isset($ruleData->additional['sortable_apply_mode'])) {
            $rule->setSortableApplyMode($ruleData->additional['sortable_apply_mode']);
        }

        if (isset($ruleData->roleDiscounts['dont_apply_bulk_if_roles_matched'])) {
            $rule->setDontApplyBulkIfRolesMatched($ruleData->roleDiscounts['dont_apply_bulk_if_roles_matched'] === "1");
        }
    }

    /**
     * @param Rule $rule
     * @param Models\Rule $ruleData
     */
    protected function installAdvertising(&$rule, $ruleData)
    {
        if (isset($ruleData->advertising['enabled_timer'])) {
            $rule->setEnabledTimer($ruleData->advertising['enabled_timer']);
        }
        if (isset($ruleData->advertising['timer_message'])) {
            $rule->setTimerMessage($ruleData->advertising['timer_message']);
        }
        if (isset($ruleData->advertising['discount_message'])) {
            $rule->setDiscountMessage($ruleData->advertising['discount_message']);
        }
        if (isset($ruleData->advertising['discount_message_cart_item'])) {
            $rule->setDiscountMessageCartItem($ruleData->advertising['discount_message_cart_item']);
        }
        if (isset($ruleData->advertising['long_discount_message'])) {
            $rule->setLongDiscountMessage($ruleData->advertising['long_discount_message']);
        }
        if (isset($ruleData->advertising['sale_badge'])) {
            $rule->setSaleBadge($ruleData->advertising['sale_badge']);
        }
    }

    /**
     * @param SingleItemRule|PackageRule $rule
     * @param Models\Rule $ruleData
     *
     * @return SingleItemRule|PackageRule
     */
    protected function setGiftItemStrategy(&$rule, $ruleData)
    {
        if ( ! isset($ruleData->getProducts['repeat'])) {
            return $rule;
        }

        $repeat = $ruleData->getProducts['repeat'];

        if ($repeat === 'based_on_subtotal') {
            $rule->setItemGiftStrategy($rule::BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY);
            if (isset($ruleData->getProducts['repeat_subtotal'])) {
                $rule->setItemGiftSubtotalDivider($ruleData->getProducts['repeat_subtotal']);
            }
        } elseif ($repeat === 'based_on_subtotal_inc') {
            $rule->setItemGiftStrategy($rule::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY);
            if (isset($ruleData->getProducts['repeat_subtotal'])) {
                $rule->setItemGiftSubtotalDivider($ruleData->getProducts['repeat_subtotal']);
            }
        } elseif (is_numeric($repeat)) {
            $rule->setItemGiftStrategy($rule::BASED_ON_LIMIT_STRATEGY);
            $attemptCount = (int)$ruleData->getProducts['repeat'];
            $attemptCount = $attemptCount !== -1 ? $attemptCount : INF;
            $rule->setItemGiftLimit($attemptCount);
        }

        return $rule;
    }

    protected function installConditionMessage(&$rule, $ruleData)
    {

        if (isset($ruleData->conditionMessage, $ruleData->conditionMessage['type'])) {
            $conditionMessageData = $ruleData->conditionMessage;
            $beginningMessage   = $conditionMessageData['beginning_message'];
            $endMessage         = $conditionMessageData['end_message'];
            $type               = $conditionMessageData['type'];

            if ($type === 'total' and isset($conditionMessageData['total']['message'])) {//check rule for having total adj
                $message = $conditionMessageData['total']['message'];

                if ($rule instanceof SingleItemRule) {
                    $conditionMessage = new SingleItemRule\ConditionMessage($beginningMessage, $message, $endMessage);
                } elseif ($rule instanceof PackageRule) {
                    $conditionMessage = new PackageRule\ConditionMessageTotal($beginningMessage, $message, $endMessage);
                } else {
                    return;
                }

                $rule->installConditionMessageHandler($conditionMessage);
            } elseif ($type === 'split' and isset($conditionMessageData['split'][0]['message'])) {//check rule for having split adj
                if ($rule instanceof SingleItemRule) {
                    return;
                }

                $messages = $conditionMessageData['split'];

                $conditionMessage = new PackageRule\ConditionMessageSplit($beginningMessage, $messages, $endMessage);

                $rule->installConditionMessageHandler($conditionMessage);
            }
        }
    }
}
