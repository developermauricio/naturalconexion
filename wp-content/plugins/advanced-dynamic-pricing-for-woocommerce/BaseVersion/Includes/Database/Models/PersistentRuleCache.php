<?php

namespace ADP\BaseVersion\Includes\Database\Models;

class PersistentRuleCache
{
    const TABLE_NAME = 'wdp_persistent_rules_cache';

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $product;

    /**
     * @var int
     */
    public $ruleId;

    /**
     * @var float
     */
    public $qtyStart;

    /**
     * @var float|null
     */
    public $qtyFinish;

    /**
     * @var float
     */
    public $originalPrice;

    /**
     * @var float
     */
    public $price;

    /**
     * @param int $id
     * @param string $product
     * @param int $ruleId
     * @param float $qtyStart
     * @param float|null $qtyFinish
     * @param float $originalPrice
     * @param float $price
     */
    public function __construct(
        $id,
        $product,
        $ruleId,
        $qtyStart,
        $qtyFinish,
        $originalPrice,
        $price
    ) {
        $this->id            = intval($id);
        $this->product       = strval($product);
        $this->ruleId        = intval($ruleId);
        $this->qtyStart      = floatval($qtyStart);
        $this->qtyFinish     = is_numeric($qtyFinish) ? floatval($qtyFinish) : null;
        $this->originalPrice = floatval($originalPrice);
        $this->price         = floatval($price);
    }

    /**
     * @var array $data
     */
    public static function fromArray($data)
    {
        $persistentRule = array(
            'id'             => null,
            'product'        => null,
            'rule_id'        => null,
            'qty_start'      => null,
            'qty_finish'     => null,
            'original_price' => null,
            'price'          => null,
        );
        $persistentRule = array_merge($persistentRule, $data);

        return new self($persistentRule['id'], $persistentRule['product'], $persistentRule['rule_id'],
            $persistentRule['qty_start'], $persistentRule['qty_finish'], $persistentRule['original_price'],
            $persistentRule['price']);
    }

    public function getData()
    {
        $data = array(
            'product'        => $this->product,
            'rule_id'        => $this->ruleId,
            'qty_start'      => $this->qtyStart,
            'qty_finish'     => $this->qtyFinish,
            'original_price' => $this->originalPrice,
            'price'          => $this->price,
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
        product VARCHAR(255) NOT NULL,
        rule_id INT NOT NULL,
        qty_start FLOAT NOT NULL DEFAULT 1.0,
        qty_finish FLOAT,
        original_price FLOAT NOT NULL,
        price FLOAT NOT NULL,
        PRIMARY KEY  (id)
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
