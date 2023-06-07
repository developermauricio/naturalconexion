<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

class GiftsCollection
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var array<int,Gift>
     */
    protected $gifts;

    /**
     * GiftCollection constructor.
     *
     * @param Rule $rule
     */
    public function __construct($rule)
    {
        $this->rule  = $rule;
        $this->gifts = array();
    }

    public function __clone()
    {
        $this->gifts = array_map(function ($gift) {
            return clone $gift;
        }, $this->gifts);
    }

    /**
     * @param Gift $gift
     */
    public function add($gift)
    {
        if ($this->isGift($gift)) {
            $this->gifts[$this->generateHash($gift)] = $gift;
        }
    }

    /**
     * @param array<int,Gift> ...$gifts
     */
    public function bulkAdd(...$gifts)
    {
        if ( ! is_array($gifts)) {
            return;
        }

        foreach ($gifts as $gift) {
            $this->add($gift);
        }
    }

    /**
     * @param string $hash
     */
    public function removeByHash($hash)
    {
        unset($this->gifts[$hash]);
    }

    public function purge()
    {
        $this->gifts = array();
    }

    /**
     * @return array<int,Gift>
     */
    public function asArray()
    {
        return $this->gifts;
    }

    /**
     * @param Gift $gift
     *
     * @return bool
     */
    protected function isGift($gift)
    {
        return $gift instanceof Gift && $gift->isValid();
    }

    /**
     * @param Gift $gift
     *
     * @return string
     */
    protected function generateHash($gift)
    {
        $pieces = array(strval($this->rule->getId()), strval(count($this->gifts)), serialize($gift));

        return md5(join("_", $pieces));
    }
}
