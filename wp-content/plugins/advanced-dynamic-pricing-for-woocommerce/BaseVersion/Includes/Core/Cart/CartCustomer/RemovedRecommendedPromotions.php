<?php

namespace ADP\BaseVersion\Includes\Core\Cart\CartCustomer;

class RemovedRecommendedPromotions
{
    /**
     * @var string
     */
    protected $associatedAutoAddHash;

    /**
     * @var float[]
     */
    protected $removed;

    /**
     * @param string $associatedAutoAddHash
     */
    public function __construct($associatedAutoAddHash)
    {
        $this->associatedAutoAddHash = $associatedAutoAddHash;
        $this->removed = array();
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
        return floatval(array_sum($this->removed));
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

        $this->removed[$autoAddItemHash] = floatval($qty);
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

        if (!isset($this->removed[$autoAddItemHash])) {
            $this->removed[$autoAddItemHash] = floatval(0);
        }

        $this->removed[$autoAddItemHash] += floatval($qty);
    }

    /**
     * @param string $autoAddItemHash
     *
     * @return float
     */
    public function get($autoAddItemHash)
    {
        $result = floatval(0);

        if (isset($this->removed[$autoAddItemHash])) {
            $result = $this->removed[$autoAddItemHash];
        }

        return $result;
    }

    /**
     * @param string $autoAddItemHash
     */
    public function remove($autoAddItemHash)
    {
        unset($this->removed[$autoAddItemHash]);
    }

    public function purge()
    {
        $this->removed = array();
    }
}
