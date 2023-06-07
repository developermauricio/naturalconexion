<?php

namespace ADP\BaseVersion\Includes\ImportExport;

use ADP\BaseVersion\Includes\Database\Models\Rule;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\Factory;
use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;

class ImporterCSV {

    public static $warnings = array();

    public static function importRules($rules, $importOption)
    {
        if ( ! empty(self::$warnings)) {
            return;
        }
        $ruleRepository = new RuleRepository();
        $ruleStorage    = Factory::get("Database_RuleStorage");
        if ($importOption === 'reset') {
            self::$warnings[] = __('All rules were deleted', 'advanced-dynamic-pricing-for-woocommerce');
            $ruleRepository->deleteAllRules();
        }

        self::createRules($rules);

        $ruleObjects = array();

        foreach ($rules as $rawRule) {
            /** Do not allow importing data that does not fit. E.g.: collections */
            if ( ! isset($rawRule['rule_type'])) {
                continue;
            }

            if ($rawRule[KeyKeeperDB::RULE_TYPE] === RuleTypeEnum::PERSISTENT()->getValue()) {
                $rulesCol = $ruleStorage->buildPersistentRules(array(Rule::fromArray($rawRule)));
            } else {
                $rulesCol = $ruleStorage->buildRules(array(Rule::fromArray($rawRule)));
            }

            if ($rulesCol->getRules()) {
                $ruleObjects[] = $rulesCol->getRules()[0];
            }
        }

        if (count($ruleObjects) === 0) {
            return;
        }
        self::$warnings[] = sprintf(
            _n(
                '%s rule were imported',
                '%s  rules were imported',
                count($ruleObjects),
                'advanced-dynamic-pricing-for-woocommerce'
            ),
            count($ruleObjects)
        );
        $exporter         = Factory::get("ImportExport_Exporter");
        $ruleCounter      = $ruleRepository->getRulesCount() + 1;
        foreach ($ruleObjects as &$ruleObject) {
            $rule        = $exporter->convertRule($ruleObject);
            $ruleCounter = self::setRuleTitleAndId($rule, $ruleRepository, $importOption, $ruleCounter);
            $ruleObj     = Rule::fromArray($rule);
            $ruleRepository->storeRule($ruleObj);
        }
    }

    protected static function convertSupportedValueToType($type){
        if (in_array(strtolower($type), array('product', 'category', 'sku', 'products'))) {
            $type = strtolower($type);
            $type = ('category' == $type)? 'categories': $type;
            if ($type == 'product' || $type == 'products') {
                $type .= ($type == 'products') ? '' : 's';
            } else {
                $type = 'product_' . $type;
            }

            return $type;
        } elseif (in_array(strtolower($type), array('fixeddiscount', 'fixedcost', 'percentage', 'amount'))) {
            $type = strtolower($type);
            if ($type == 'fixedcost') {
                $type = 'price__fixed';
            } else {
                $type = str_replace('discount', '', $type);
                $type = 'discount__' . (($type == 'fixed') ? 'amount' : $type);
            }

            return $type;
        } else {
            self::$warnings[] = __('Unknown type ', 'advanced-dynamic-pricing-for-woocommerce') . $type;

            return '';
        }
    }

    public static function prepareCSV($file){
        if (($handle = fopen($file, "r")) !== false) {
            $filterType = '';
            if (($data = fgetcsv($handle)) !== false && is_array($data)) {
                if (str_contains(strtolower($data[0]), 'type')) {
                    $filterType = self::convertSupportedValueToType($data[1]);
                }
            }
            $discountType = '';
            if (($data = fgetcsv($handle)) !== false && is_array($data)) {
                if (str_contains(strtolower($data[0]), 'type')) {
                    $discountType = self::convertSupportedValueToType($data[1]);
                }
            }
            $ruleBlocksSet = array();
            while (($data = fgetcsv($handle)) !== false) {
                $data = array_map('strtolower', $data);
                if (in_array($data[0], array('filter', 'discountedprice', 'fromqty', 'toqty', 'role'))) {
                    foreach ($data as $name) {
                        if (empty($name)) {
                            break;
                        }
                        $ruleBlocksSet[] = $name;
                    }
                    break;
                }
            }
            if (empty($ruleBlocksSet)) {
                self::$warnings[] = __(
                    'File must contain one rule or more.',
                    'advanced-dynamic-pricing-for-woocommerce'
                );
            }
            if ( ! empty(self::$warnings)) {
                return array();
            }
            $ruleBlocksSetLength = count($ruleBlocksSet);
            $rules               = array();
            $newRule             = array();
            while (($data = fgetcsv($handle)) !== false) {
                if (empty($data[0])) {
                    for ($setIter = 1; $setIter < $ruleBlocksSetLength; $setIter++) {
                        if (empty($data[$setIter])) {
                            continue;
                        }
                        $newRule[$ruleBlocksSet[$setIter]]['value'][] = $data[$setIter];
                    }
                } else {
                    if ( ! empty($newRule)) {
                        $rules[] = $newRule;
                    }
                    for ($setIter = 0; $setIter < $ruleBlocksSetLength; $setIter++) {
                        if (empty($data[$setIter]) && ! isset($data[$setIter])) {
                            continue;
                        }
                        $type = '';
                        if ($ruleBlocksSet[$setIter] == 'filter') {
                            $type = $filterType;
                        } elseif ($ruleBlocksSet[$setIter] == 'discountedprice') {
                            $type = $discountType;
                        }
                        $newRule[$ruleBlocksSet[$setIter]] = array(
                            'value' => empty($data[$setIter]) ? null : array($data[$setIter]),
                            'type'  => $type,
                        );
                    }
                }
            }
            $rules[] = $newRule;
            fclose($handle);

            return $rules;
        }
        self::$warnings[] = __('Can\'t open file.', 'advanced-dynamic-pricing-for-woocommerce');

        return array();
    }

    protected static function setRuleTitleAndId(&$rule, &$ruleRepository, $importOption, $iter){
        if ( ! empty($rule['bulk_adjustments'])) {
            $rule['title'] = __('Bulk ', 'advanced-dynamic-pricing-for-woocommerce');
        }
        if ( ! empty($rule['conditions'])) {
            $rule['title'] .= __('for Role ', 'advanced-dynamic-pricing-for-woocommerce');
        }
        if ( ! empty($rule['role_discounts'])) {
            $rule['title'] = __('Role ', 'advanced-dynamic-pricing-for-woocommerce');
        }
        if (empty($rule['role_discounts']) && empty($rule['bulk_adjustments'])) {
            $rule['title'] = __('Sample Product ', 'advanced-dynamic-pricing-for-woocommerce');
        }
        $rule['title'] .= __('Discount ', 'advanced-dynamic-pricing-for-woocommerce');

        if ($importOption == 'reset') {
            $rule['title'] .= $iter;

            return ++$iter;
        } elseif ($importOption == 'add') {
            $rule['title'] .= $iter;

            return ++$iter;
        } elseif ($importOption == 'update') {
            $rulesLikeFilter = $ruleRepository->getRules(array('filter_types' => array($rule['filters'][0]['type'], 'active_only' => true)));
            $findRule        = false;
            foreach ($rulesLikeFilter as $ruleLikeFilter) {
                if ($rule['filters'][0]['type'] == $ruleLikeFilter->filters[0]['type']
                    && count($ruleLikeFilter->filters) === 1
                    && empty(
                    array_diff(
                        $ruleLikeFilter->filters[0]['value'],
                        $rule['filters'][0]['value']
                    )
                    )) {
                    $rule['id']       = $ruleLikeFilter->id;
                    $rule['priority'] = $ruleLikeFilter->priority;
                    $rule['title']    = $ruleLikeFilter->title;
                    $findRule         = true;
                    break;
                }
            }
            if ( ! $findRule) {
                $rule['title'] .= $iter;

                return ++$iter;
            }

            return $iter;
        }

        return $iter;
    }

    private static function createRules(&$rules){
        foreach ($rules as &$rule) {
            $rule['rule_type'] = 'common';
            $rule['enabled']   = 'on';
            $rule['filters']   = array(
                array(
                    'qty'    => 1,
                    'type'   => $rule['filter']['type'],
                    'method' => 'in_list',
                    'value'  => self::convertProductNameToId($rule['filter']['type'], $rule['filter']['value']),
                ),
            );
            if (isset($rule['filters'][0]['value'][0]) && $rule['filters'][0]['value'][0] == 'undefined') {
                self::$warnings[] = __(
                                        'Can not find ',
                                        'advanced-dynamic-pricing-for-woocommerce'
                                    ) . $rule['filter']['type'] . ' ' . $rule['filter']['value'][0];
                $rule             = null;
                continue;
            }
            if (is_array($rule['fromqty']['value'])) {
                $rule['bulk_adjustments'] = array(
                    'type'              => 'bulk',
                    'discount_type'     => $rule['discountedprice']['type'],
                    'ranges'            => array(),
                    "qty_based"         => 'not',
                    'split_discount_by' => 'cost',
                    'total'             => array(
                        'type'  => $rule['discountedprice']['type'],
                        'value' => 0,
                    ),
                );
                foreach (
                    array_map(
                        null,
                        $rule['fromqty']['value'],
                        $rule['toqty']['value'],
                        $rule['discountedprice']['value']
                    ) as $range
                ) {
                    $rule['bulk_adjustments']['ranges'][] = array(
                        'from'  => $range[0],
                        'to'    => $range[1],
                        'value' => $range[2],
                    );
                }
            }
            if (is_array($rule['role']['value'])) {
                if(is_array($rule['role']['value'])){
                    $role = array();
                    foreach($rule['role']['value'] as $rowRole){
                        $role = array_merge($role, explode('|', $rowRole));
                    }
                    $rule['role']['value'] = $role;
                }else{
                    $rule['role']['value'] = explode('|', $rule['role']['value']);
                }
                if (is_array($rule['fromqty']['value'])) {
                    $rule['conditions'] = array(
                        array(
                            'type'    => 'customer_role',
                            'options' => array(
                                'comparison_list_method' => 'in_list',
                                'comparison_list'        => $rule['role']['value'],
                            ),
                        ),
                    );
                    $rule['additional'] = array(
                        'conditions_relationship' => 'and',
                    );
                } else {
                    $rule['role_discounts'] = array(
                        'rows' => array(
                            array(
                                'discount_type'  => $rule['discountedprice']['type'],
                                'discount_value' => $rule['discountedprice']['value'][0],
                                'roles'          => $rule['role']['value'],
                            ),
                        ),
                    );
                }
            }
            if ( ! isset($rule['bulk_adjustments']) && ! isset($rule['role_discounts'])) {
                if ( ! is_array($rule['discountedprice']['value'])) {
                    throw new \RuntimeException('Discount price must be set.');
                }
                $rule['product_adjustments'] = array(
                    'type'              => 'total',
                    'split_discount_by' => 'cost',
                    'total'             => array(
                        'type'  => $rule['discountedprice']['type'],
                        'value' => $rule['discountedprice']['value'][0],
                    ),
                );
            }
            unset($rule['discountedprice'], $rule['fromqty'], $rule['toqty'], $rule['filter'], $rule['role']);
        }
    }

    protected static function convertProductNameToId($type, $items)
    {
        if (empty($items)) {
            return $items;
        }
        foreach ($items as &$value) {
            if ('products' === $type) {
                $value = Helpers::getProductId($value);
            } elseif ('product_categories' === $type) {
                if(str_contains($value, '>')){
                    $parent = '';
                    foreach(explode('>', $value) as $category){
                        $parent = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'parent' => $parent,
                            'name' => $category
                        ));
                        if(is_array($parent) && $parent[0] instanceof \WP_Term){
                            $parent = $parent[0]->term_id;
                        }else{
                            $parent = 0;
                            break;
                        }
                    }
                    $value = $parent;
                }
                $value = Helpers::getCategoryId($value);
            } elseif ('product_tag' === $type) {
                $value = Helpers::getTagId($value);
            } elseif ('product_attribute' === $type) {
                $value = Helpers::getAttributeId($value);
            }

            if (empty($value)) {
                $value = 'undefined';
            }
        }

        return $items;
    }
}
