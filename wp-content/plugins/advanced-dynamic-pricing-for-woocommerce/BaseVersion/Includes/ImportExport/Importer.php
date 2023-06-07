<?php

namespace ADP\BaseVersion\Includes\ImportExport;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Models\Rule;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Database\RuleStorage;
use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\Factory;
use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;

defined('ABSPATH') or exit;

class Importer
{
    public static function importRules($data, $resetRules)
    {
        $ruleRepository = new RuleRepository();

        $imported = array();
        /** @var RuleStorage $ruleStorage */
        $ruleStorage = Factory::get("Database_RuleStorage");
        $persistentRuleRepository = new PersistentRuleRepository();

        $ruleObjects = array();

        foreach ( $data as $rawRule ) {
            /** Do not allow importing data that does not fit. E.g.: collections */
            if ( ! isset($rawRule['rule_type'])) {
                continue;
            }

            if ( $rawRule[KeyKeeperDB::RULE_TYPE] === RuleTypeEnum::PERSISTENT()->getValue() ) {
                $rulesCol    = $ruleStorage->buildPersistentRules(array(Rule::fromArray($rawRule)));
            } else {
                $rulesCol    = $ruleStorage->buildRules(array(Rule::fromArray($rawRule)));
            }

            if ( $rulesCol->getRules() ) {
                $ruleObjects[] = $rulesCol->getRules()[0];
            }
        }

        if (count($ruleObjects) === 0) {
            return array();
        }

        if ($resetRules) {
            $ruleRepository->deleteAllRules();
        }

        $exporter = Factory::get("ImportExport_Exporter");

        foreach ($ruleObjects as $ruleObject) {
            $rule = $exporter->convertRule($ruleObject);
            //unset( $rule['id'] );

            $rule['enabled'] = (isset($rule['enabled']) && $rule['enabled'] === 'on') ? 1 : 0;

            if ( ! empty($rule['filters'])) {
                foreach ($rule['filters'] as &$item) {
                    $item['value'] = isset($item['value']) ? $item['value'] : array();
                    $item['value'] = self::convertElementsFromNameToId($item['value'], $item['type']);
                }
                unset($item);
            }

            if ( ! empty($rule['get_products']['value'])) {
                foreach ($rule['get_products']['value'] as &$item) {
                    $item['value'] = isset($item['value']) ? $item['value'] : array();
                    $item['value'] = self::convertElementsFromNameToId($item['value'], $item['type']);
                }
                unset($item);
            }

            if ( ! empty($rule['auto_add_products']['value'])) {
                foreach ($rule['auto_add_products']['value'] as &$item) {
                    $item['value'] = $item['value'] ?? array();
                    $item['value'] = self::convertElementsFromNameToId($item['value'], $item['type']);
                }
                unset($item);
            }

            if ( ! empty($rule['conditions'])) {
                foreach ($rule['conditions'] as &$item) {
                    if ( ! isset($item['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        continue;
                    }

                    $item['options'][ListComparisonCondition::COMPARISON_LIST_KEY] =
                        self::convertElementsFromNameToId($item['options'][ListComparisonCondition::COMPARISON_LIST_KEY], $item['type']);
                }
                unset($item);
            }

            $ruleObj = Rule::fromArray($rule);

            $id         = $ruleRepository->storeRule($ruleObj);
            $imported[] = $id;

            if (isset($rule['rule_type']) && $rule['rule_type'] === RuleTypeEnum::PERSISTENT()->getValue()) {
                $persistentRuleRepository->addRule($persistentRuleRepository->getAddRuleData($id, new Context()), $id);
            }
        }

        return $imported;
    }

    public static function UpdateRulesRanges($items)
    {
        $ruleRepository = new RuleRepository();
        $imported = array();
        /** @var RuleStorage $ruleStorage */
        $ruleStorage = Factory::get("Database_RuleStorage");
        $persistentRuleRepository = new PersistentRuleRepository();

        $ruleObjects = array();

        foreach ( $items as $rawRule ) {
            if ( $rawRule[KeyKeeperDB::RULE_TYPE] === RuleTypeEnum::PERSISTENT()->getValue() ) {
                $rulesCol    = $ruleStorage->buildPersistentRules(array(Rule::fromArray($rawRule)));
            } else {
                $rulesCol    = $ruleStorage->buildRules(array(Rule::fromArray($rawRule)));
            }

            if ( $rulesCol->getRules() ) {
                $ruleObjects[] = $rulesCol->getRules()[0];
            }
        }

        $exporter = Factory::get("ImportExport_Exporter");

        foreach ($ruleObjects as $ruleObject) {
            $rule = $exporter->convertRule($ruleObject);
            $rule['id'] = $ruleObject->getId();

            $rule['enabled'] = (isset($rule['enabled']) && $rule['enabled'] === 'on') ? 1 : 0;

//            if ( ! empty($rule['filters'])) {
//                foreach ($rule['filters'] as &$item) {
//                    $item['value'] = isset($item['value']) ? $item['value'] : array();
//                    $item['value'] = self::convertElementsFromNameToId($item['value'], $item['type']);
//                }
//                unset($item);
//            }
//
//            if ( ! empty($rule['get_products']['value'])) {
//                foreach ($rule['get_products']['value'] as &$item) {
//                    $item['value'] = isset($item['value']) ? $item['value'] : array();
//                    $item['value'] = self::convertElementsFromNameToId($item['value'], $item['type']);
//                }
//                unset($item);
//            }
//
//            if ( ! empty($rule['conditions'])) {
//                foreach ($rule['conditions'] as &$item) {
//                    if ( ! isset($item['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
//                        continue;
//                    }
//
//                    $item['options'][ListComparisonCondition::COMPARISON_LIST_KEY] =
//                        self::convertElementsFromNameToId($item['options'][ListComparisonCondition::COMPARISON_LIST_KEY], $item['type']);
//                }
//                unset($item);
//            }

            $ruleObj    = Rule::fromArray($rule);
            $id         = $ruleRepository->storeRule($ruleObj);
            $imported[] = $id;

            if (isset($rule['rule_type']) && $rule['rule_type'] === RuleTypeEnum::PERSISTENT()->getValue()) {
                $persistentRuleRepository->addRule($persistentRuleRepository->getAddRuleData($id, new Context()), $id);
            }
        }

        return $imported;
    }

    /**
     * @param array|string $items
     * @param string $type
     *
     * @return array|string
     */
    protected static function convertElementsFromNameToId($items, $type)
    {
        if (empty($items) || ! is_array($items)) {
            return $items;
        }
        foreach ($items as &$value) {
            if ('products' === $type) {
                $value = Helpers::getProductId($value);
            } elseif ('product_categories' === $type) {
                $value = Helpers::getCategoryId($value);
            } elseif ('product_tags' === $type) {
                $value = Helpers::getTagId($value);
            } elseif ('product_attributes' === $type) {
                $value = Helpers::getAttributeId($value);
            }

            if (empty($value)) {
                $value = 0;
            }
        }

        return $items;
    }


}
