<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\Database\Models\OrderItem;

class OrderItemRepository implements OrderItemRepositoryInterface {
    public function addProductStats($orderItem)
    {
        global $wpdb;

        $table = $wpdb->prefix . OrderItem::TABLE_NAME;
        $data = $orderItem->getData();

        $data = array_merge(array(
            'order_id'      => 0,
            'product_id'    => 0,
            'rule_id'       => 0,
            'amount'        => 0.0,
            'qty'           => 0,
            'gifted_amount' => 0.0,
            'gifted_qty'    => 0,
            'date'          => current_time('mysql'),
        ), $data);

        $wpdb->replace($table, $data);
    }
}
