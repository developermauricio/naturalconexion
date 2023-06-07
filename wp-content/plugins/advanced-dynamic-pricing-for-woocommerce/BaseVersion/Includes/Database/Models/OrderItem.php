<?php

namespace ADP\BaseVersion\Includes\Database\Models;

use DateTime;

class OrderItem
{
    const TABLE_NAME = 'wdp_order_items';

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $orderId;

    /**
     * @var int
     */
    public $productId;

    /**
     * @var int
     */
    public $ruleId;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var int
     */
    public $qty;

    /**
     * @var float
     */
    public $giftedAmount;

    /**
     * @var int
     */
    public $giftedQty;

    /**
     * @var DateTime
     */
    public $date;

    public function __construct(
        $id,
        $orderId,
        $productId,
        $ruleId,
        $qty,
        $amount,
        $giftedAmount,
        $giftedQty,
        $date
    ) {
        $this->id           = intval($id);
        $this->orderId      = intval($orderId);
        $this->productId    = intval($productId);
        $this->ruleId       = intval($ruleId);
        $this->qty          = intval($qty);
        $this->amount       = floatval($amount);
        $this->giftedAmount = floatval($giftedAmount);
        $this->giftedQty    = intval($giftedQty);
        $this->date         = is_string($date) ? new DateTime($date) : $date;
    }

    /**
     * @param array $data
     */
    public static function fromArray($data)
    {
        $orderItemRule = array(
            'id'            => 0,
            'order_id'      => 0,
            'product_id'    => 0,
            'rule_id'       => 0,
            'qty'           => 0,
            'amount'        => 0.0,
            'gifted_amount' => 0.0,
            'gifted_qty'    => 0,
            'date'          => new DateTime(),
        );
        $orderItemRule = array_merge($orderItemRule, $data);

        return new self($orderItemRule['id'], $orderItemRule['order_id'], $orderItemRule['product_id'],
            $orderItemRule['rule_id'], $orderItemRule['qty'], $orderItemRule['amount'],
            $orderItemRule['gifted_amount'], $orderItemRule['gifted_qty'], $orderItemRule['date']);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'order_id'      => $this->orderId,
            'product_id'    => $this->productId,
            'rule_id'       => $this->ruleId,
            'qty'           => $this->qty,
            'amount'        => $this->amount,
            'gifted_amount' => $this->giftedAmount,
            'gifted_qty'    => $this->giftedQty,
            'date'          => $this->date->format('Y-m-d H:i:s'),
        );

        return $data;
    }

    public static function createTable()
    {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charsetCollate = $wpdb->get_charset_collate();

        $tableName = $wpdb->prefix . self::TABLE_NAME;

        $sql = /** @lang MySQL */
            "CREATE TABLE {$tableName} (
            id INT NOT NULL AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            rule_id INT NOT NULL,
            amount DECIMAL(50,2) DEFAULT 0,
            qty INT DEFAULT 0,
            gifted_amount DECIMAL(50,2) DEFAULT 0,
            gifted_qty INT DEFAULT 0,
            date DATETIME,
            PRIMARY KEY  (id),
            UNIQUE KEY order_id (order_id, rule_id, product_id),
            KEY rule_id (rule_id),
            KEY product_id (product_id),
            KEY date (date)
        ) $charsetCollate;";
        dbDelta($sql);
    }

    public static function deleteTable()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query("DROP TABLE IF EXISTS $tableName");
    }
}
