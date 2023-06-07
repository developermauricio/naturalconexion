<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Database\Models\Rule;
use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\ProVersion\Includes\Core\Rule\CartCondition\Impl\ShippingState;

class RuleRepository implements RuleRepositoryInterface {
    /**
     * @return array
     */
    public function getRulesWithBulk(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $sql = "SELECT * FROM $table WHERE bulk_adjustments LIKE '%ranges%' AND NOT deleted";

        $rows = $wpdb->get_results($sql);

        $rows = array_map(function ($item) {
            $result = array(
                'id'                       => $item->id,
                'title'                    => $item->title,
                'rule_type'		           => $item->rule_type,
                'type'                     => $item->type,
                'exclusive'                => $item->exclusive,
                'priority'                 => $item->priority,
                'enabled'                  => $item->enabled,
                'options'                  => unserialize($item->options),
                'additional'               => unserialize($item->additional),
                'conditions'               => unserialize($item->conditions),
                'filters'                  => unserialize($item->filters),
                'limits'                   => unserialize($item->limits),
                'product_adjustments'      => unserialize($item->product_adjustments),
                'sortable_blocks_priority' => unserialize($item->sortable_blocks_priority),
                'bulk_adjustments'         => unserialize($item->bulk_adjustments),
                'role_discounts'           => unserialize($item->role_discounts),
                'cart_adjustments'         => unserialize($item->cart_adjustments),
                'get_products'             => unserialize($item->get_products),
                'advertising'              => unserialize($item->advertising),
                'condition_message'        => unserialize($item->condition_message),
            );
            return self::decodeArrayTextFields($result);
        }, $rows);

        foreach ($rows as &$row) {
            $row = self::validateBulkAdjustments($row);
        }

        $rows = self::migrateTo_2_2_3($rows);

        $countryStates = WC()->countries->get_states();

        // fix collections in conditions
        foreach ($rows as &$row) {
            foreach ($row['conditions'] as &$condition) {
                $type    = $condition['type'];
                if ($type === 'shipping_state' && isset($condition['options'][ShippingState::COMPARISON_LIST_KEY])) {
                    $comparison_value = $condition['options'][ShippingState::COMPARISON_LIST_KEY];

                    $newComparisonValue = array();
                    $changed            = false;

                    foreach ($comparison_value as $value) {
                        if (strpos($value, ':') === false) {
                            foreach ($countryStates as $country_code => $states) {
                                if (isset($states[$value])) {
                                    $newComparisonValue[] = $country_code . ":" . $value;
                                    $changed              = true;
                                }
                            }
                        }
                    }

                    if ($changed) {
                        $condition['options'][ShippingState::COMPARISON_LIST_KEY] = $newComparisonValue;
                    }
                }
            }
        }

        $lastCustomTaxonomy = Helpers::getCustomProductTaxonomies();
        $lastCustomTaxonomy = end($lastCustomTaxonomy);

        $rules = array();

        foreach ($rows as &$row) {
            foreach ($row['conditions'] as &$condition) {
                $type = $condition['type'];

                if ($type === 'custom_taxonomy' || $type === 'amount_custom_taxonomy') {
                    $condition['type'] = $condition['type'] . '_' . $lastCustomTaxonomy->name;
                }
            }

            $rules[] = Rule::fromArray($row);
        }

        return $rules;
    }

    public function getRules($args = array()): array
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $sql = "SELECT * FROM $table WHERE 1 ";

        if (isset($args['types'])) {
            $types        = (array)$args['types'];
            $placeholders = array_fill(0, count($types), '%s');
            $placeholders = implode(', ', $placeholders);
            $sql          = $wpdb->prepare("$sql AND type IN($placeholders)", $types);
        }

        if (isset($args['rule_types'])) {
            $types        = (array)$args['rule_types'];
            $placeholders = array_fill(0, count($types), '%s');
            $placeholders = implode(', ', $placeholders);
            $sql          = $wpdb->prepare("$sql AND (rule_type IN($placeholders) OR rule_type is NULL)", $types);
        }

        $active_only = isset($args['active_only']) && $args['active_only'];
        if ($active_only) {
            $sql .= ' AND enabled = 1';
        }

        if (isset($args['active'])) {
            $sql .= sprintf(' AND enabled = %s', $args['active'] ? "1" : "0");
        }

        $include_deleted = isset($args['include_deleted']) && $args['include_deleted'];
        if ( ! $include_deleted) {
            $sql .= ' AND deleted = 0';
        }

        if (isset($args['exclusive'])) {
            $showExclusive = $args['exclusive'] ? 1 : 0;
            $sql           = "$sql AND exclusive = $showExclusive";
        }

        if (isset($args['id'])) {
            $ids          = (array)$args['id'];
            $placeholders = array_fill(0, count($ids), '%d');
            $placeholders = implode(', ', $placeholders);
            $sql          = $wpdb->prepare("$sql AND id IN($placeholders)", $ids);
        }

        if (isset($args['filter_types'])) {
            $types        = (array)$args['filter_types'];

            foreach ( $types as $type ) {
                $wpdb->prepare("$sql AND (filters LIKE '%s')", "%$type%");
            }
        }

        if (isset($args['q'])) {
            $q = $args['q'];
            $sql = $wpdb->prepare("$sql AND (summary LIKE '%s')", "%$q%");
        }

        $sql .= " ORDER BY rule_type DESC, exclusive DESC, priority";

        if (isset($args['limit'])) {
            $sql_limit = "";
            $limit     = $args['limit'];

            $count = null;
            $start = null;

            if (is_string($limit)) {
                $count = $limit;
            } elseif (is_array($limit)) {
                if (1 === count($limit)) {
                    $count = reset($limit);
                } elseif (2 === count($limit)) {
                    list($start, $count) = $limit;
                }
            }

            if ( ! is_null($count)) {
                $count = (integer)$count;
                if ( ! is_null($start)) {
                    $start     = (integer)$start;
                    $sql_limit = sprintf("LIMIT %d, %d", $start, $count);
                } else {
                    $sql_limit = sprintf("LIMIT %d", $count);
                }
            }

            $sql .= " " . $sql_limit;
        }
        $rows = $wpdb->get_results($sql);

        $rows = array_map(function ($item) {
            $result = array(
                'id'                       => $item->id,
                'title'                    => $item->title,
                'rule_type'		           => $item->rule_type,
                'type'                     => $item->type,
                'exclusive'                => $item->exclusive,
                'priority'                 => $item->priority,
                'enabled'                  => $item->enabled,
                'options'                  => unserialize($item->options ?? ""),
                'additional'               => unserialize($item->additional ?? ""),
                'conditions'               => unserialize($item->conditions ?? ""),
                'filters'                  => unserialize($item->filters ?? ""),
                'limits'                   => unserialize($item->limits ?? ""),
                'product_adjustments'      => unserialize($item->product_adjustments ?? ""),
                'sortable_blocks_priority' => unserialize($item->sortable_blocks_priority ?? ""),
                'bulk_adjustments'         => unserialize($item->bulk_adjustments ?? ""),
                'role_discounts'           => unserialize($item->role_discounts ?? ""),
                'cart_adjustments'         => unserialize($item->cart_adjustments ?? ""),
                'get_products'             => unserialize($item->get_products ?? ""),
                'auto_add_products'        => unserialize($item->auto_add_products ?? ""),
                'advertising'              => unserialize($item->advertising ?? ""),
                'condition_message'        => unserialize($item->condition_message ?? ""),
            );
            $result = self::decodeArrayTextFields($result);

            return $result;
        }, $rows);

        if (isset($args['product'])) {
            $new_rows              = array();
            $filters_to_check      = array_column($rows, "filters");
            $sellerRulesExist      = false;
            $customFieldsRuleExist = false;
            array_map(function ($ruleFilters) use (&$sellerRulesExist, &$customFieldsRuleExist) {
                $rulesFiltersValues = array_values(array_column($ruleFilters, "type"));
                if ($sellerRulesExist === false && in_array("product_sellers", $rulesFiltersValues)) {
                    $sellerRulesExist = true;
                }
                if ($customFieldsRuleExist === false && in_array("product_custom_fields", $rulesFiltersValues)) {
                    $customFieldsRuleExist = true;
                }

                return $ruleFilters;
            }, $filters_to_check);
            if ($sellerRulesExist) {
                $productSellers = array_column(Helpers::getUsers(array()), 'id');
            }
            if ($customFieldsRuleExist) {
                $customFields = array_column(Helpers::getProductCustomFields($args['product']),
                    'id');
            }
            foreach ($rows as $row) {
                foreach ($row['filters'] as $filter) {
                    switch ($filter['type']) {
                        case 'products':
                            foreach ($filter['value'] as $value) {
                                if ((integer)$value == $args['product'] || (isset($args["product_childs"]) && in_array((integer)$value,
                                            $args["product_childs"]))) {
                                    $new_rows[] = $row;
                                    break 3;
                                }
                            }
                            break 2;
                        case 'product_sku':
                            foreach ($filter['value'] as $value) {
                                if (isset($args[$filter['type']]) && strcmp((string)$value,
                                        $args[$filter['type']]) === 0) {
                                    $new_rows[] = $row;
                                    break 3;
                                }
                            }
                            break 2;
                        case 'product_categories':
                        case 'product_attributes':
                        case 'product_tags':
                            foreach ($filter['value'] as $value) {
                                if (isset($args[$filter['type']]) && in_array((integer)$value,
                                        $args[$filter['type']])) {
                                    $new_rows[] = $row;
                                    break 3;
                                }
                            }
                            break 2;
                        case 'product_sellers':
                            foreach ($filter['value'] as $value) {
                                if ( ! empty($productSellers) && in_array((integer)$value, $productSellers)) {
                                    $new_rows[] = $row;
                                    break 3;
                                }
                            }
                            break 2;
                        case 'product_custom_fields':
                            foreach ($filter['value'] as $value) {
                                if ( ! empty($customFields) && in_array((string)$value, $customFields)) {
                                    $new_rows[] = $row;
                                    break 3;
                                }
                            }
                            break 2;
                        case 'product_category_slug':
                            foreach ($filter['value'] as $value) {
                                if (isset($args["product_category_slug"]) && in_array((string)$value,
                                        $args['product_category_slug'])) {
                                    $new_rows[] = $row;
                                    break 3;
                                }
                            }
                            break 2;
                        default:
                            break 1;
                    }
                }
            }
            $rows = $new_rows;
        }

        foreach ($rows as &$row) {
            $row = self::validateBulkAdjustments($row);
        }

        $rows = self::migrateTo_2_2_3($rows);

        $countryStates = WC()->countries->get_states();

        // fix collections in conditions
        foreach ($rows as &$row) {
            foreach ($row['conditions'] as &$condition) {
                $type    = $condition['type'];
                if ($type === 'shipping_state' && isset($condition['options'][ShippingState::COMPARISON_LIST_KEY])) {
                    $comparison_value = $condition['options'][ShippingState::COMPARISON_LIST_KEY];

                    $newComparisonValue = array();
                    $changed            = false;

                    foreach ($comparison_value as $value) {
                        if (strpos($value, ':') === false) {
                            foreach ($countryStates as $country_code => $states) {
                                if (isset($states[$value])) {
                                    $newComparisonValue[] = $country_code . ":" . $value;
                                    $changed              = true;
                                }
                            }
                        }
                    }

                    if ($changed) {
                        $condition['options'][ShippingState::COMPARISON_LIST_KEY] = $newComparisonValue;
                    }
                }
            }
        }

        $lastCustomTaxonomy = Helpers::getCustomProductTaxonomies();
        $lastCustomTaxonomy = end($lastCustomTaxonomy);

        $rules = array();

        foreach ($rows as &$row) {
            foreach ($row['conditions'] as &$condition) {
                $type = $condition['type'];

                if ($type === 'custom_taxonomy' || $type === 'amount_custom_taxonomy') {
                    $condition['type'] = $condition['type'] . '_' . $lastCustomTaxonomy->name;
                }
            }

            $rules[] = Rule::fromArray($row);
        }

        return $rules;
    }

    public function getRulesCount($args = array())
    {
        //    	return self::get_test_rules();
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $sql = "SELECT COUNT(*) FROM $table WHERE 1 ";

        if (isset($args['types'])) {
            $types        = (array)$args['types'];
            $placeholders = array_fill(0, count($types), '%s');
            $placeholders = implode(', ', $placeholders);
            $sql          = $wpdb->prepare("$sql AND type IN($placeholders)", $types);
        }

        $active_only = isset($args['active_only']) && $args['active_only'];
        if ($active_only) {
            $sql .= ' AND enabled = 1';
        }

        if (isset($args['active'])) {
            $sql .= sprintf(' AND enabled = %s', $args['active'] ? "1" : "0");
        }

        $include_deleted = isset($args['include_deleted']) && $args['include_deleted'];
        if ( ! $include_deleted) {
            $sql .= ' AND deleted = 0';
        }

        if (isset($args['exclusive'])) {
            $showExclusive = $args['exclusive'] ? 1 : 0;
            $sql           = "$sql AND exclusive = $showExclusive";
        }

        if (isset($args['id'])) {
            $ids          = (array)$args['id'];
            $placeholders = array_fill(0, count($ids), '%d');
            $placeholders = implode(', ', $placeholders);
            $sql          = $wpdb->prepare("$sql AND id IN($placeholders)", $ids);
        }

        if (isset($args['q'])) {
            $q = $args['q'];
            $sql = $wpdb->prepare("$sql AND (summary LIKE '%s')", "%$q%");
        }

        return (integer)$wpdb->get_var($sql);
    }

    private static function validateBulkAdjustments($row)
    {
        if (empty($row['bulk_adjustments']['ranges'])) {
            return $row;
        }

        $ranges = $row['bulk_adjustments']['ranges'];
        $ranges = array_values(array_filter(array_map(function ($range) {
            return isset($range['to'], $range['from'], $range['value']) ? $range : false;
        }, $ranges)));


        usort($ranges, function ($a, $b) {
            if ($a["to"] === '' && $b["to"] === '') {
                return 0;
            } elseif ($a["to"] === '') {
                return 1;
            } elseif ($b["to"] === '') {
                return -1;
            }

            return (integer)$a["to"] - (integer)$b["to"];
        });

        $previousRange = null;
        foreach ($ranges as &$range) {
            $from = $range['from'];
            if ($from === '') {
                if (is_null($previousRange)) {
                    $from = 1;
                } else {
                    if ($previousRange['to'] !== '') {
                        $from = (integer)$previousRange['to'] + 1;
                    }
                }
            }
            $range['from'] = $from;
            $previousRange = $range;
        }

        $row['bulk_adjustments']['ranges'] = $ranges;

        return $row;
    }

    private static function migrateTo_2_2_3($rows)
    {
        // add selector "in_list/not_in_list" for amount conditions
        foreach ($rows as &$row) {
            foreach ($row['conditions'] as &$condition) {
                if ('amount_' === substr($condition['type'], 0, strlen('amount_')) &&
                !isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_METHOD_KEY])) {
                    $condition['options'][ListComparisonCondition::COMPARISON_LIST_METHOD_KEY] = 'in_list';
                }
            }
        }

        return $rows;
    }

    private static function decodeArrayTextFields($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::decodeArrayTextFields($value);
            } else {
                $value = trim(htmlspecialchars_decode($value));
            }
        }

        return $array;
    }

    public function deleteAllRules()
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;
        $sql   = "DELETE FROM $table";
        $wpdb->query($sql);
    }

    public function markRulesAsDeleted($type)
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $sql = "UPDATE $table SET deleted = 1 WHERE type ";
        if (is_array($type)) {
            $format = implode(', ', array_fill(0, count($type), '%s'));
            $sql    = $wpdb->prepare("$sql IN ($format)", $type);
        } else {
            $sql = $wpdb->prepare("$sql = %s", $type);
        }

        $wpdb->query($sql);
    }

    public function markRuleAsDeleted($rule_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $data  = array('deleted' => 1);
        $where = array('id' => $rule_id);
        $wpdb->update($table, $data, $where);
    }

    public function storeRule($rule)
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;
        $data = $rule->getDataForDB();

        if ($id = $rule->id) {
            $where  = array('id' => $id);
            $wpdb->update($table, $data, $where);

            return $id;
        } else {
            $wpdb->insert($table, $data);

            return $wpdb->insert_id;
        }
    }

    public function changeRulePriority(int $ruleId, int $priority): int
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $result = 0;

        if ($ruleId) {
            $result = $wpdb->update($table, array('priority' => $priority), array('id' => $ruleId));
        }

        return $result;
    }

    public function markAsDisabledByPlugin($ruleId)
    {
        global $wpdb;

        $tableRules = $wpdb->prefix . Rule::TABLE_NAME;

        $sql = $wpdb->prepare("
            SELECT {$tableRules}.additional
            FROM {$tableRules}
            WHERE id = %d
        ", $ruleId);

        $additional                       = $wpdb->get_var($sql);
        $additional                       = unserialize($additional);
        $additional['disabled_by_plugin'] = 1;

        $data  = array('enabled' => 0, 'additional' => serialize($additional));
        $where = array('id' => $ruleId);
        $wpdb->update($tableRules, $data, $where);
    }

    public function deleteConditionsFromDbByTypes($types)
    {

        $rules = array_merge($this->getRules(), $this->getRules(array(
            'exclusive' => true,
        )));

        /**
         * @var Rule $rule
         */
        foreach ($rules as $rule) {
            if (isset($rule->conditions) && !empty($rule->conditions)) {
                $conditions = $rule->conditions;
            } else {
                continue;
            }
            foreach ($conditions as $keyCondition => $condition) {
                if (in_array($condition['type'], $types)) {
                    unset($conditions[$keyCondition]);
                }
            }
            $conditions = array_values($conditions);

            $data = array_merge($rule->getDataForDB(), array('id' => $rule->id, 'conditions' => serialize($conditions)));
            $ruleObj = Rule::fromArray($data);
            $this->storeRule($ruleObj);
        }
    }

    public function isConditionTypeActive($types)
    {
        $rules = array_merge($this->getRules(array(
            'active_only' => true,
        )), $this->getRules(array(
            'exclusive'   => true,
            'active_only' => true,
        )));

        foreach ($rules as $rule) {
            if (isset($rule->conditions)) {
                $conditions = $rule->conditions;
            } else {
                continue;
            }
            foreach ($conditions as $condition) {
                if (in_array($condition['type'], $types)) {
                    return true;
                }
            }

        }

        return false;
    }

    public function disableRule($ruleId)
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $data  = array('enabled' => 0);
        $where = array('id' => $ruleId);
        $wpdb->update($table, $data, $where);
    }

    public function enableRule($ruleId)
    {
        global $wpdb;
        $table = $wpdb->prefix . Rule::TABLE_NAME;

        $data  = array('enabled' => 1);
        $where = array('id' => $ruleId);
        $wpdb->update($table, $data, $where);
    }

    public function migrateSuitableCommonRulesToPersistence() : int
    {
        global $wpdb;
        $tableRules = $wpdb->prefix . Rule::TABLE_NAME;

        $sql = $wpdb->prepare("
            UPDATE {$tableRules}
            SET `rule_type` = 'persistent'
            WHERE
                  `deleted` = 0
              AND `exclusive` = 0
              AND `rule_type` <> %s
              AND `rule_type` <> %s
              AND `filters` LIKE %s
              AND `filters` LIKE %s
              AND `filters` NOT LIKE %s
              AND `cart_adjustments` = %s
              AND (`bulk_adjustments` = %s OR `bulk_adjustments` LIKE %s OR
                   `bulk_adjustments` = %s OR `bulk_adjustments` = %s OR
                   `bulk_adjustments` IS NULL)
        ",
            'persistent',
            'exclusive',
            'a:1:{%', // single filter
            '%s:3:"qty";s:1:"1";%', // qty equals 1
            '%s:4:"type";s:3:"any";%', // not any product
            'a:0:{}',
            'a:2:{s:4:"type";s:4:"bulk";s:13:"table_message";s:0:"";}', //no bulk
            '%s:4:"type";s:4:"bulk";s:9:"qty_based";s:3:"all";%', //bulk with qty based on all matched products
            'a:1:{s:13:"table_message";s:0:"";}', //TODO: prevent rules to save bulk like this
            'a:0:{}' //for pre-4.0.0 imported rules with no bulk
        );

        return (int)($wpdb->query($sql));
    }

    public function migrateSuitablePersistenceRulesToCommon() : int
    {
        global $wpdb;
        $tableRules = $wpdb->prefix . Rule::TABLE_NAME;

        $sql = $wpdb->prepare("
            UPDATE {$tableRules}
            SET `rule_type` = 'common'
            WHERE
                  `deleted` = 0
              AND `exclusive` = 0
              AND `rule_type` = %s
        ",
            'persistent'
        );

        return (int)($wpdb->query($sql));
    }
}
