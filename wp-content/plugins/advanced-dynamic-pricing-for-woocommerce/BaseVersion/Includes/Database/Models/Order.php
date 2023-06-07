<?php

namespace ADP\BaseVersion\Includes\Database\Models;

use DateTime;

class Order
{
    const TABLE_NAME = 'wdp_orders';

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
    public $extra;

    /**
     * @var float
     */
    public $shipping;

    /**
     * @var bool
     */
    public $isFreeShipping;

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
        $ruleId,
        $amount,
        $qty,
        $extra,
        $shipping,
        $isFreeShipping,
        $giftedAmount,
        $giftedQty,
        $date
    ) {
        $this->id             = intval($id);
        $this->orderId        = intval($orderId);
        $this->ruleId         = intval($ruleId);
        $this->amount         = floatval($amount);
        $this->qty            = intval($qty);
        $this->extra          = floatval($extra);
        $this->shipping       = floatval($shipping);
        $this->isFreeShipping = boolval($isFreeShipping);
        $this->giftedAmount   = floatval($giftedAmount);
        $this->giftedQty      = intval($giftedQty);
        $this->date           = is_string($date) ? new DateTime($date) : $date;
    }

    /**
     * @param array $data
     */
    public static function fromArray($data)
    {
        $orderRule = array(
            'id'               => 0,
            'order_id'         => 0,
            'rule_id'          => 0,
            'amount'           => 0.0,
            'qty'              => 0,
            'extra'            => 0.0,
            'shipping'         => 0.0,
            'is_free_shipping' => false,
            'gifted_amount'    => 0.0,
            'gifted_qty'       => 0,
            'date'             => new DateTime(),
        );
        $orderRule = array_merge($orderRule, $data);

        return new self($orderRule['id'], $orderRule['order_id'], $orderRule['rule_id'], $orderRule['amount'],
            $orderRule['qty'], $orderRule['extra'], $orderRule['shipping'], $orderRule['is_free_shipping'],
            $orderRule['gifted_amount'], $orderRule['gifted_qty'], $orderRule['date']);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'order_id'         => $this->orderId,
            'rule_id'          => $this->ruleId,
            'amount'           => $this->amount,
            'extra'            => $this->extra,
            'shipping'         => $this->shipping,
            'is_free_shipping' => intval($this->isFreeShipping),
            'gifted_amount'    => $this->giftedAmount,
            'gifted_qty'       => $this->giftedQty,
            'date'             => $this->date->format('Y-m-d H:i:s'),
        );

        return $data;
    }

    public static function createTable()
    {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charsetCollate = $wpdb->get_charset_collate();

        $tableName = $wpdb->prefix . self::TABLE_NAME;

        // Table for history of applied rules
        $sql = /** @lang MySQL */
            "CREATE TABLE {$tableName} (
            id INT NOT NULL AUTO_INCREMENT,
            order_id INT NOT NULL,
            rule_id INT NOT NULL,
            amount DECIMAL(50,2) DEFAULT 0,
            qty INT DEFAULT 0,
            extra DECIMAL(50,2) DEFAULT 0,
            shipping DECIMAL(50,2) DEFAULT 0,
            is_free_shipping TINYINT(1) DEFAULT 0,
            gifted_amount DECIMAL(50,2) DEFAULT 0,
            gifted_qty INT DEFAULT 0,
            date DATETIME,
            PRIMARY KEY  (id),
            UNIQUE KEY order_id (order_id, rule_id),
            KEY rule_id (rule_id),
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
