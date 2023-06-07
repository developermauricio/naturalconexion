<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\Database\Models\Order;
use ADP\BaseVersion\Includes\Database\Models\Rule;

interface OrderRepositoryInterface {
    /**
     * @param Order $order
     */
    public function addOrderStats($order);

    /**
     * @return array{order: Order, rule: Rule}
     */
    public function getAppliedRulesForOrder($orderId);

    public function getCountOfRuleUsages($rule_id);

    public function getCountOfRuleUsagesPerCustomer($ruleId, $customerId);
}
