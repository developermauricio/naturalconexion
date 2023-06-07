<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor\Structures;

defined('ABSPATH') or exit;

class CartSetCollection
{
    /**
     * @var CartSet[]
     */
    private $sets = array();

    public function __clone()
    {
        $newSets = array();
        foreach ($this->sets as $set) {
            $newSets[] = clone $set;
        }

        $this->sets = $newSets;
    }

    public function __construct()
    {
    }

    /**
     * @param $setToAdd CartSet
     *
     * @return boolean
     */
    public function add(CartSet $setToAdd)
    {
        $added = false;
        foreach ($this->sets as &$set) {
            /**
             * @var $set CartSet
             */
            if ($set->getHash() === $setToAdd->getHash()) {
                $set->incQty($setToAdd->getQty());
                $added = true;
                break;
            }
        }

        if ( ! $added) {
            $this->sets[] = $setToAdd;
        }

        /**
         * Do use sorting here!
         * It breaks positional discounts like 'Tier discount'.
         */

        return true;
    }

    public function isEmpty()
    {
        return empty($this->sets);
    }

    /**
     * @return array<int, CartSet>
     */
    public function getSets()
    {
        return $this->sets;
    }

    public function getHash()
    {
        $sets = array();
        foreach ($this->sets as $set) {
            $sets[] = clone $set;
        }

        usort($sets, function ($setA, $setB) {
            /**
             * @var $setA CartSet
             * @var $setB CartSet
             */
            return strnatcmp($setA->getHash(), $setB->getHash());
        });

        $setsHashes = array_map(function ($set) {
            /**
             * @var $set CartSet
             */
            return $set->getHash();
        }, $sets);
        $encoded     = json_encode($setsHashes);
        $hash        = md5($encoded);

        return $hash;
    }

    public function purge()
    {
        $this->sets = array();
    }

    public function getTotalSetsQty()
    {
        $count = 0;

        foreach ($this->sets as $set) {
            $count += $set->getQty();
        }

        return $count;
    }

    public function getSetByHash($hash)
    {
        foreach ($this->sets as $set) {
            if ($set->getHash() === $hash) {
                return clone $set;
            }
        }

        return null;
    }


}
