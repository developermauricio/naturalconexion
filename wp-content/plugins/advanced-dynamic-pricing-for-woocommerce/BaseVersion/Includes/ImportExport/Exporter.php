<?php

namespace ADP\BaseVersion\Includes\ImportExport;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\Enums\ProductAdjustmentSplitDiscount;
use ADP\BaseVersion\Includes\Core\Rule\NoItemRule;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ProductsAdjustmentSplit;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ProductsAdjustmentTotal;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ConditionMessageSplit;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\ConditionMessageTotal;
use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ProductsAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ConditionMessage;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAdd;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAddChoice;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\GiftChoice;
use ADP\BaseVersion\Includes\Core\Rule\Structures\SetDiscount;
use ADP\BaseVersion\Includes\Core\RuleProcessor\OptionsConverter;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepositoryInterface;
use ADP\BaseVersion\Includes\Database\RulesCollection;
use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;
use ADP\Factory;

defined('ABSPATH') or exit;

class Exporter
{
    protected $context;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context        = adp_context();
        $this->ruleRepository = new RuleRepository();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withRuleRepository(RuleRepositoryInterface $repository)
    {
        $this->ruleRepository = $repository;
    }

    public function exportRulesWithBulk()
    {
        $rules = $this->ruleRepository->getRulesWithBulk();
        $ruleStorage = Factory::get("Database_RuleStorage");
        /** @var RulesCollection $rulesCol */
        $rulesCol = $ruleStorage->buildRules($rules);

        $rules = array();

        foreach ($rulesCol->getRules() as $ruleObject) {
            $rule = $this->convertRule($ruleObject);
            $rule['id'] = $ruleObject->getId();
            $rules[] = $rule;
        }

        return $rules;
    }

    public function exportRules()
    {
        $rules       = $this->ruleRepository->getRules(['rule_types' => array(RuleTypeEnum::COMMON()->getValue())]);
        $ruleStorage = Factory::get("Database_RuleStorage");
        /** @var RulesCollection $rulesCol */
        $rulesCol = $ruleStorage->buildRules($rules);

        $persistentRules    = $this->ruleRepository->getRules(array(
            'rule_types' => array(
                RuleTypeEnum::PERSISTENT()->getValue(),
            ),
        ));
        $persistentRulesCol = $ruleStorage->buildPersistentRules($persistentRules);

        $rules = array();

        foreach (array_merge($persistentRulesCol->getRules(), $rulesCol->getRules()) as $ruleObject) {
            $rule    = $this->convertRule($ruleObject);
            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * @param Rule $ruleObject
     *
     * @return array
     */
    public function convertRule($ruleObject)
    {
        $rule = array();

        if ($ruleObject instanceof PersistentRule) {
            $rule[KeyKeeperDB::TYPE]      = 'persistent';
            $rule[KeyKeeperDB::RULE_TYPE] = RuleTypeEnum::PERSISTENT()->getValue();

            $filters = array();
            foreach ($ruleObject->getFilters() as $filter) {
                $filters[] = array(
                    'qty'             => 1,
                    'type'            => $filter->getType(),
                    'method'          => $filter->getMethod(),
                    'value'           => $filter->getValue(),
                    'product_exclude' => array(
                        'on_wc_sale'       => $filter->isExcludeWcOnSale() ? "1" : "",
                        'already_affected' => $filter->isExcludeAlreadyAffected() ? "1" : "",
                        'backorder'        => $filter->isExcludeBackorder() ? "1" : "",
                        'values'           => $filter->getExcludeProductIds() ? $filter->getExcludeProductIds() : array(),
                    ),
                );
            }
            $rule[KeyKeeperDB::FILTERS] = $filters;
        } elseif ($ruleObject instanceof SingleItemRule) {
            $rule[KeyKeeperDB::TYPE]      = 'single_item';
            $rule[KeyKeeperDB::RULE_TYPE] = RuleTypeEnum::COMMON()->getValue();

            $filters = array();
            foreach ($ruleObject->getFilters() as $filter) {
                $filters[] = array(
                    'qty'             => 1,
                    'type'            => $filter->getType(),
                    'method'          => $filter->getMethod(),
                    'value'           => $filter->getValue(),
                    'product_exclude' => array(
                        'on_wc_sale'       => $filter->isExcludeWcOnSale() ? "1" : "",
                        'already_affected' => $filter->isExcludeAlreadyAffected() ? "1" : "",
                        'backorder'        => $filter->isExcludeBackorder() ? "1" : "",
                        'values'           => $filter->getExcludeProductIds() ? $filter->getExcludeProductIds() : array(),
                    ),
                );
            }
            $rule[KeyKeeperDB::FILTERS] = $filters;
        } elseif ($ruleObject instanceof PackageRule) {
            $rule[KeyKeeperDB::TYPE]      = 'package';
            $rule[KeyKeeperDB::RULE_TYPE] = RuleTypeEnum::COMMON()->getValue();

            $filters = array();
            foreach ($ruleObject->getPackages() as $package) {
                foreach ($package->getFilters() as $filter) {
                    $filters[] = array(
                        'qty'             => $package->getQty(),
                        'qty_end'         => $package->getQtyEnd(),
                        'type'            => $filter->getType(),
                        'method'          => $filter->getMethod(),
                        'value'           => $filter->getValue(),
                        'product_exclude' => array(
                            'on_wc_sale'       => $filter->isExcludeWcOnSale() ? "1" : "",
                            'already_affected' => $filter->isExcludeAlreadyAffected() ? "1" : "",
                            'backorder'        => $filter->isExcludeBackorder() ? "1" : "",
                            'values'           => $filter->getExcludeProductIds() ? $filter->getExcludeProductIds() : array(),
                        ),
                    );
                }
            }
            $rule[KeyKeeperDB::FILTERS] = $filters;
        } else {
            $rule[KeyKeeperDB::TYPE]      = 'no_item';
            $rule[KeyKeeperDB::RULE_TYPE] = RuleTypeEnum::COMMON()->getValue();

            $rule[KeyKeeperDB::FILTERS] = array();
        }

//        $rule[KeyKeeperDB::TYPE]     = 'package';
        $rule[KeyKeeperDB::TITLE]    = $ruleObject->getTitle();
        $rule[KeyKeeperDB::PRIORITY] = $ruleObject->getPriority() ? $ruleObject->getPriority() : "";
        $rule[KeyKeeperDB::ENABLED]  = $ruleObject->getEnabled() ? "on" : "off";

        if ( ! ($ruleObject instanceof NoItemRule)) {
            $rule[KeyKeeperDB::SORT_BLOCKS_PRIOR] = $ruleObject->getSortableBlocksPriority();
        }
        //options?

        $additional                            = array();
        $additional['conditions_relationship'] = $ruleObject->getConditionsRelationship() ? $ruleObject->getConditionsRelationship() : "";
        $additional['blocks'] = $ruleObject->getBlocks();
        if ( ! ($ruleObject instanceof NoItemRule)) {
            if ($ruleObject->hasProductAdjustment()) {
                $additional['is_replace']  = $ruleObject->getProductAdjustmentHandler()->isReplaceWithCartAdjustment() ? 'on' : '';
                $additional['replace_name'] = $ruleObject->getProductAdjustmentHandler()->getReplaceCartAdjustmentCode();
            } elseif ($ruleObject->getProductRangeAdjustmentHandler()) {
                $additional['is_replace']  = $ruleObject->getProductRangeAdjustmentHandler()->isReplaceWithCartAdjustment() ? 'on' : '';
                $additional['replace_name'] = $ruleObject->getProductRangeAdjustmentHandler()->getReplaceCartAdjustmentCode();
            } else {
                $additional['replace_name'] = "";
            }
            $additional['is_replace_free_products_with_discount'] = $ruleObject->isReplaceItemGifts() ? 'on' : '';
            $additional['free_products_replace_name']             = $ruleObject->getReplaceItemGiftsCode();
            $additional['is_replace_auto_add_products_with_discount'] = $ruleObject->isReplaceAutoAdds() ? 'on' : '';
            $additional['auto_add_products_replace_name']         = $ruleObject->getReplaceAutoAddsCode();
            $additional['sortable_apply_mode']                    = $ruleObject->getSortableApplyMode();

            $additional['auto_add_cant_be_removed_from_cart'] = $ruleObject->getAutoAddRemoveDisable();
            $additional['auto_add_show_as_recommended_product'] = $ruleObject->getAutoAddShowAsRecommended();
        } else {
            $additional['replace_name']               = "";
            $additional['free_products_replace_name'] = "";
            $additional['auto_add_products_replace_name'] = "";
            $additional['sortable_apply_mode']        = "consistently";
        }
        $rule[KeyKeeperDB::ADDITIONAL] = $additional;

        $filters = array();


        $conditions = array();
        foreach ($ruleObject->getConditions() as $condition) {
            $conditions[] = OptionsConverter::convertConditionToArray($condition);
        }
        $rule[KeyKeeperDB::CONDITIONS] = $conditions;

        $cart_adjs = array();
        foreach ($ruleObject->getCartAdjustments() as $adj) {
            $cart_adjs[] = OptionsConverter::convertCartAdjToArray($adj);
        }
        $rule[KeyKeeperDB::CART_ADJS] = $cart_adjs;

        $limits = array();
        foreach ($ruleObject->getLimits() as $limit) {
            $limits[] = OptionsConverter::convertLimitToArray($limit);
        }
        $rule[KeyKeeperDB::LIMITS] = $limits;

        if ( ! ($ruleObject instanceof NoItemRule)) {
            if ($ruleObject->hasProductAdjustment()) {
                $product_adj = array();
                $adj_handler = $ruleObject->getProductAdjustmentHandler();
                if ($adj_handler instanceof ProductsAdjustment or
                    $adj_handler instanceof ProductsAdjustmentTotal) {
                    $product_adj['type']  = "total";
                    $product_adj['total'] = array(
                        'type'  => $this->getDiscountType($adj_handler->getDiscount()),
                        'value' => $adj_handler->getDiscount()->getValue(),
                    );
                    if ($adj_handler->isMaxAvailableAmountExists()) {
                        $product_adj['max_discount_sum'] = $adj_handler->getMaxAvailableAmount();
                    }

                    if ($adj_handler instanceof ProductsAdjustmentTotal) {
                        $product_adj['split_discount_by'] = $adj_handler->getSplitDiscount()->getValue();
                    } else {
                        $product_adj['split_discount_by'] = ProductAdjustmentSplitDiscount::ITEM_COST()->getValue();
                    }
                    $rule[KeyKeeperDB::PROD_ADJS] = $product_adj;
                } elseif ($adj_handler instanceof ProductsAdjustmentSplit) {
                    $product_adj['type'] = "split";
                    foreach ($adj_handler->getDiscounts() as $discount) {
                        $product_adj['split'][] = array(
                            'type'  => $this->getDiscountType($discount),
                            'value' => $discount->getValue(),
                        );
                    }
                    if ($adj_handler->isMaxAvailableAmountExists()) {
                        $product_adj['max_discount_sum'] = $adj_handler->getMaxAvailableAmount();
                    }

                    $product_adj['split_discount_by'] = $adj_handler->getSplitDiscount()->getValue();

                    $rule[KeyKeeperDB::PROD_ADJS] = $product_adj;
                }
            }

            if ($adj_handler = $ruleObject->getProductRangeAdjustmentHandler()) {
                $product_adj['type']          = $adj_handler->getType();
                $product_adj['qty_based']     = $adj_handler->getGroupBy();
                $ranges                       = $adj_handler->getRanges();
                $product_adj['discount_type'] = $this->getDiscountType($ranges[0]->getData());
                foreach ($ranges as $range) {
                    $product_adj['ranges'][] = array(
                        'from'  => $range->getFrom(),
                        'to'    => $range->getTo() !== INF ? $range->getTo() : "",
                        'value' => $range->getData()->getValue(),
                    );
                }
                $product_adj['table_message'] = $adj_handler->getPromotionalMessage() ? $adj_handler->getPromotionalMessage() : "";

                $rule[KeyKeeperDB::BULK_ADJS] = $product_adj;
            }

            $role_discounts = array();
            if ($ruleObject->isDontApplyBulkIfRolesMatched()) {
                $role_discounts['dont_apply_bulk_if_roles_matched'] = "1";
            }
            if (null !== $ruleObject->getRoleDiscounts()) {
                foreach ($ruleObject->getRoleDiscounts() as $role_discount) {
                    $role_discounts['rows'][] = array(
                        'discount_type'  => $this->getDiscountType($role_discount->getDiscount()),
                        'discount_value' => $role_discount->getDiscount()->getValue(),
                        'roles'          => $role_discount->getRoles()
                    );
                }
            }

            $rule[KeyKeeperDB::ROLE_DISCOUNTS] = $role_discounts;

            $free_products = $this->addFreeProductsRepeatAndSubtotal($ruleObject);

            foreach ($ruleObject->getItemGiftsCollection()->asArray() as $gift) {
                $giftChoices = $gift->getChoices();

                /** @var GiftChoice $giftChoice */
                if ( $giftChoice = reset($giftChoices) ) {
                    $free_products['value'][] = array(
                        'qty'       => $gift->getQty(),
                        'gift_mode' => $gift->getMode()->getValue(),
                        'type'      => $giftChoice->getType()->getValue(),
                        'value'     => $giftChoice->getValues(),
                    );
                }
            }
            $rule[KeyKeeperDB::FREE_PRODUCTS] = $free_products;

            $autoAddProducts = $this->addAutoAddProductsRepeatAndSubtotal($ruleObject);

            /** @var AutoAdd $autoAdd */
            foreach ($ruleObject->getAutoAddsCollection()->asArray() as $autoAdd) {
                $autoAddChoices = $autoAdd->getChoices();

                /** @var AutoAddChoice $autoAddChoice */
                if ( $autoAddChoice = reset($autoAddChoices) ) {
                    $autoAddProducts['value'][] = array(
                        'qty'       => $autoAdd->getQty(),
                        'auto_add_mode' => $autoAdd->getMode()->getValue(),
                        'type'      => $autoAddChoice->getType()->getValue(),
                        'value'     => $autoAddChoice->getValues(),
                        'discount_type' => $autoAdd->getDiscountType(),
                        'discount_value' => $autoAdd->getDiscountValue(),
                    );
                }
            }
            $rule[KeyKeeperDB::AUTO_ADD_PRODUCTS] = $autoAddProducts;

            if ($ruleObject->hasConditionMessage()) {
                $conditionMessage = array();
                $handler = $ruleObject->getConditionMessageHandler();
                if ($handler instanceof ConditionMessage or
                    $handler instanceof ConditionMessageTotal) {
                    $conditionMessage['type']  = "total";
                    $conditionMessage['total'] = array(
                        'message' => $handler->getMessage(),
                    );
                    $conditionMessage['beginning_message']  = $handler->getBeginningMessage();
                    $conditionMessage['end_message']  = $handler->getEndMessage();

                    $rule[KeyKeeperDB::CONDITION_MESSAGE] = $conditionMessage;
                } elseif ($handler instanceof ConditionMessageSplit) {
                    $conditionMessage['type'] = "split";
                    foreach ($handler->getMessages() as $message) {
                        $conditionMessage['split'][] = array(
                            'message' => $message,
                        );
                    }

                    $conditionMessage['beginning_message']  = $handler->getBeginningMessage();
                    $conditionMessage['end_message']  = $handler->getEndMessage();

                    $rule[KeyKeeperDB::CONDITION_MESSAGE] = $conditionMessage;
                }
            }

        }

        $options = array();
        if ( ! ($ruleObject instanceof NoItemRule)) {
            $options['apply_to'] = $ruleObject->getApplyFirstTo();
            if ($ruleObject instanceof SingleItemRule) {
                $options['repeat'] = $ruleObject->getItemsCountLimit();
            } elseif ($ruleObject instanceof PackageRule) {
                $options['repeat'] = $ruleObject->getPackagesCountLimit();
            }
        } else {
            $options = array(
                'repeat'   => "-1",
                'apply_to' => "expensive",
            );
        }
        $rule[KeyKeeperDB::OPTIONS] = $options;

        $rule[KeyKeeperDB::ADVERTISING] = array(
            "discount_message"      => $ruleObject->getDiscountMessage(),
            "discount_message_cart_item" => $ruleObject->getDiscountMessageCartItem(),
            "long_discount_message" => $ruleObject->getLongDiscountMessage(),
            "sale_badge"            => $ruleObject->getSaleBadge(),
        );

        $rule['version'] = WC_ADP_VERSION;

        return $rule;
    }

    /**
     * @param SingleItemRule|PackageRule $ruleObject
     *
     * @return array
     */
    protected function addFreeProductsRepeatAndSubtotal($ruleObject)
    {
        $free_products = array();
        if ($ruleObject->getItemGiftStrategy() === $ruleObject::BASED_ON_LIMIT_STRATEGY) {
            $free_products['repeat']          = $ruleObject->getItemGiftLimit() === INF ? "-1" : $ruleObject->getItemGiftLimit();
            $free_products['repeat_subtotal'] = "";
        } elseif ($ruleObject->getItemGiftStrategy() === $ruleObject::BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY) {
            $free_products['repeat']          = "based_on_subtotal";
            $free_products['repeat_subtotal'] = $ruleObject->getItemGiftSubtotalDivider();
        } elseif ($ruleObject->getItemGiftStrategy() === $ruleObject::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY) {
            $free_products['repeat']          = "based_on_subtotal_inc";
            $free_products['repeat_subtotal'] = $ruleObject->getItemGiftSubtotalDivider();
        }

        $free_products['max_amount_for_gifts'] = $ruleObject->getMaxAmountForGifts();

        return $free_products;
    }

    /**
     * @param SingleItemRule|PackageRule $ruleObject
     *
     * @return array
     */
    protected function addAutoAddProductsRepeatAndSubtotal($ruleObject)
    {
        $auto_add_products = array();
        if ($ruleObject->getAutoAddStrategy() === $ruleObject::BASED_ON_LIMIT_STRATEGY) {
            $auto_add_products['repeat']          = $ruleObject->getAutoAddLimit() === INF ? "-1" : $ruleObject->getAutoAddLimit();
            $auto_add_products['repeat_subtotal'] = "";
        } elseif ($ruleObject->getAutoAddStrategy() === $ruleObject::BASED_ON_SUBTOTAL_EXCL_TAX_STRATEGY) {
            $auto_add_products['repeat']          = "based_on_subtotal";
            $auto_add_products['repeat_subtotal'] = $ruleObject->getAutoAddSubtotalDivider();
        } elseif ($ruleObject->getAutoAddStrategy() === $ruleObject::BASED_ON_SUBTOTAL_INCL_TAX_STRATEGY) {
            $auto_add_products['repeat']          = "based_on_subtotal_inc";
            $auto_add_products['repeat_subtotal'] = $ruleObject->getAutoAddSubtotalDivider();
        }

        return $auto_add_products;
    }

    /**
     * @param SetDiscount|Discount $discount
     *
     * @return string
     * @return string
     */
    public function getDiscountType($discount)
    {
        $discountType = $discount->getType();
        $setPrefix    = "";
        if ($discount instanceof SetDiscount) {
            $setPrefix = "set_";
        }

        if ($discountType === Discount::TYPE_FIXED_VALUE) {
            return $setPrefix . "price__fixed";
        } elseif ($discountType === Discount::TYPE_PERCENTAGE) {
            return "discount__percentage";
        } elseif ($discountType === Discount::TYPE_AMOUNT) {
            return $setPrefix . "discount__amount";
        }

        return null;
    }
}
