<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\Database\Models\Order;
use ADP\BaseVersion\Includes\Database\Models\Rule;

class OrderRepository implements OrderRepositoryInterface {
    public function addOrderStats($order)
    {
        global $wpdb;

        $table = $wpdb->prefix . Order::TABLE_NAME;
        $data = $order->getData();

        $data = array_merge(array(
            'order_id'         => 0,
            'rule_id'          => 0,
            'amount'           => 0.0,
            'extra'            => 0,
            'shipping'         => 0,
            'is_free_shipping' => 0,
            'gifted_amount'    => 0.0,
            'gifted_qty'       => 0,
            'date'             => current_time('mysql'),
        ), $data);

        $wpdb->replace($table, $data);
    }

    /**
     * @param $orderId
     *
     * @return array<int, array{order: Order, rule: Rule}>
     */
    public function getAppliedRulesForOrder($orderId)
    {
        global $wpdb;

        $table_order_rules = $wpdb->prefix . Order::TABLE_NAME;

        $sql = $wpdb->prepare("
            SELECT $table_order_rules.*
            FROM $table_order_rules
            WHERE order_id = %d
            ORDER BY amount DESC
        ", $orderId);

        $rows = $wpdb->get_results($sql, ARRAY_A);

        if ( count($rows) === 0 ) {
            return [];
        }

        $ruleIds = [];
        $orderRules = array_map(function ($orderRule) use (&$ruleIds) {
            $ruleId = (int)($orderRule["rule_id"]);
            $ruleIds[] = $ruleId;

            return [
                'order' => Order::fromArray($orderRule),
                'rule' => $ruleId
            ];
        }, $rows);
        $rules = [];
        foreach ((new RuleRepository())->getRules(["id" => $ruleIds]) as $rule) {
            $rules[$rule->id] = $rule;
        }
        $orderRules = array_map(function ($orderRule) use (&$rules) {
            return [
                'order' => $orderRule['order'],
                'rule' => $rules[$orderRule["rule"]] ?? null,
            ];
        }, $orderRules);

        return $orderRules;
    }

    public function getCountOfRuleUsages($ruleId)
    {
        global $wpdb;

        $tableOrderRules = $wpdb->prefix . Order::TABLE_NAME;

        $sql = $wpdb->prepare("
            SELECT COUNT(*)
            FROM {$tableOrderRules}
            WHERE rule_id = %d
        ", $ruleId);

        $value = $wpdb->get_var($sql);

        return (integer)$value;
    }

    public function getCountOfRuleUsagesPerCustomer($ruleId, $customerId)
    {
        global $wpdb;

        $tableOrderRules = $wpdb->prefix . Order::TABLE_NAME;

        $customerOrdersIds = wc_get_orders(array(
            'return'      => 'ids',
            'numberposts' => -1,
            'customer_id' => $customerId,
            'post_type'   => wc_get_order_types(),
            'post_status' => array_keys(wc_get_order_statuses()),
        ));
        if (empty($customerOrdersIds)) {
            return 0;
        }

        $value = $wpdb->get_var("SELECT COUNT(*) FROM {$tableOrderRules}
		            WHERE rule_id = $ruleId  AND order_id IN (" . implode(',', $customerOrdersIds) . ")");

        return (integer)$value;
    }
}
