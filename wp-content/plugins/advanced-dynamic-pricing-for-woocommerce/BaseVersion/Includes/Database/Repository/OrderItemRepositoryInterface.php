<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\Database\Models\OrderItem;

interface OrderItemRepositoryInterface {
    /**
     * @param OrderItem $orderItem
     */
    public function addProductStats($orderItem);
}
