<?php

namespace ADP\BaseVersion\Includes\Core\Cart\CartCustomer;

defined('ABSPATH') or exit;

class RemovedFreeItems
{
    /**
     * @var string
     */
    protected $associatedGiftHash;

    /**
     * @var float[]
     */
    protected $removed;

    /**
     * @param string $associatedGiftHash
     */
    public function __construct($associatedGiftHash)
    {
        $this->associatedGiftHash = $associatedGiftHash;
        $this->removed            = array();
    }

    /**
     * @return string
     */
    public function getGiftHash()
    {
        return $this->associatedGiftHash;
    }

    /**
     * @return float
     */
    public function getTotalQty()
    {
        return floatval(array_sum($this->removed));
    }

    /**
     * @param string $freeItemHash
     * @param float $qty
     */
    public function set($freeItemHash, $qty)
    {
        if ( ! is_string($freeItemHash) || ! is_numeric($qty)) {
            return;
        }

        $this->removed[$freeItemHash] = floatval($qty);
    }

    /**
     * @param string $freeItemHash
     * @param float $qty
     */
    public function add($freeItemHash, $qty)
    {
        if ( ! is_string($freeItemHash) || ! is_numeric($qty)) {
            return;
        }

        if ( ! isset($this->removed[$freeItemHash])) {
            $this->removed[$freeItemHash] = floatval(0);
        }

        $this->removed[$freeItemHash] += floatval($qty);
    }

    /**
     * @param string $freeItemHash
     *
     * @return float
     */
    public function get($freeItemHash)
    {
        $result = floatval(0);

        if (isset($this->removed[$freeItemHash])) {
            $result = $this->removed[$freeItemHash];
        }

        return $result;
    }

    /**
     * @param string $freeItemHash
     */
    public function remove($freeItemHash)
    {
        unset($this->removed[$freeItemHash]);
    }

    public function purge()
    {
        $this->removed = array();
    }
}
