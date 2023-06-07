<?php

namespace ADP\BaseVersion\Includes\Core\Cart\CartCustomer;

class AddedRecommendedAutoAddItems
{
    /**
     * @var string
     */
    protected $associatedAutoAddHash;

    /**
     * @var float[]
     */
    protected $added;

    /**
     * @param string $associatedAutoAddHash
     */
    public function __construct($associatedAutoAddHash)
    {
        $this->associatedAutoAddHash = $associatedAutoAddHash;
        $this->added = array();
    }

    /**
     * @return string
     */
    public function getAutoAddHash()
    {
        return $this->associatedAutoAddHash;
    }

    /**
     * @return float
     */
    public function getTotalQty()
    {
        return floatval(array_sum($this->added));
    }

    /**
     * @param string $autoAddItemHash
     * @param float $qty
     */
    public function set($autoAddItemHash, $qty)
    {
        if (!is_string($autoAddItemHash) || !is_numeric($qty)) {
            return;
        }

        $this->added[$autoAddItemHash] = floatval($qty);
    }

    /**
     * @param string $autoAddItemHash
     * @param float $qty
     */
    public function add($autoAddItemHash, $qty)
    {
        if (!is_string($autoAddItemHash) || !is_numeric($qty)) {
            return;
        }

        if (!isset($this->added[$autoAddItemHash])) {
            $this->added[$autoAddItemHash] = floatval(0);
        }

        $this->added[$autoAddItemHash] += floatval($qty);
    }

    /**
     * @param string $autoAddItemHash
     *
     * @return float
     */
    public function get($autoAddItemHash)
    {
        $result = floatval(0);

        if (isset($this->added[$autoAddItemHash])) {
            $result = $this->added[$autoAddItemHash];
        }

        return $result;
    }

    /**
     * @param string $autoAddItemHash
     */
    public function remove($autoAddItemHash)
    {
        unset($this->added[$autoAddItemHash]);
    }

    public function purge()
    {
        $this->added = array();
    }
}
