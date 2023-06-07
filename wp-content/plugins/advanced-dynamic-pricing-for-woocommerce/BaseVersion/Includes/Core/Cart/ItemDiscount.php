<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Context;
use Exception;

defined('ABSPATH') or exit;

class ItemDiscount
{
    const FLAG_IGNORE = 0;
    const FLAG_DISCOUNT_ORIGINAL = 1;
    /**
     * @var string[]
     */
    protected $flags = array();

    const SOURCE_SINGLE_ITEM_SIMPLE = 'single_item_simple';
    const SOURCE_SINGLE_ITEM_RANGE = 'single_item_range';
    const SOURCE_PACKAGE_SIMPLE = 'package_simple';
    const SOURCE_PACKAGE_SPLIT = 'package_split';
    const SOURCE_PACKAGE_RANGE = 'package_range';
    const SOURCE_ROLE = 'role';
    const AVAILABLE_TYPES = array(
        self::SOURCE_SINGLE_ITEM_SIMPLE,
        self::SOURCE_SINGLE_ITEM_RANGE,
        self::SOURCE_PACKAGE_SIMPLE,
        self::SOURCE_PACKAGE_SPLIT,
        self::SOURCE_PACKAGE_RANGE,
        self::SOURCE_ROLE,
    );
    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    protected $ruleId;

    /**
     * @var float
     */
    protected $amount;

    const DATA_KEY_RANGE_DISCOUNT = 'range_discount';
    /**
     * @var array
     */
    protected $data;

    /**
     * @param Context|string $contextOrSource
     * @param string|float $sourceOrAmount
     * @param float|null $deprecated
     */
    public function __construct($contextOrSource, $sourceOrAmount, $deprecated = null)
    {
        $context = adp_context();
        $source  = is_string($contextOrSource) ? $contextOrSource : $sourceOrAmount;
        $amount  = is_numeric($sourceOrAmount) ? $sourceOrAmount : $deprecated;

        if ( ! in_array($source, self::AVAILABLE_TYPES)) {
            $context->handleError(new Exception(sprintf("New price context type '%s' not supported", $source)));
        }

        $this->source = $source;
        $this->data   = array();
        $this->amount = floatval($amount);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function getData($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * @param int $ruleId
     */
    public function setRuleId($ruleId)
    {
        $this->ruleId = $ruleId;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = floatval($amount);
    }

    /**
     * @param string $flag
     *
     * @return bool
     */
    public function hasFlag($flag)
    {
        return in_array($flag, $this->flags);
    }

    public function addFlag(...$flags)
    {
        $allowedFlags = array(
            self::FLAG_IGNORE,
            self::FLAG_DISCOUNT_ORIGINAL,
        );

        foreach ($flags as $flag) {
            if (in_array($flag, $allowedFlags)) {
                $this->flags[] = $flag;
            }
        }
    }

    public function removeFlag(...$flags)
    {
        foreach ($flags as $flag) {
            $pos = array_search($flag, $this->flags);

            if ($pos !== false) {
                unset($this->flags[$pos]);
            }
        }

        $this->flags = array_values($this->flags);
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    public function isType($source)
    {
        return $this->source === $source;
    }
}
