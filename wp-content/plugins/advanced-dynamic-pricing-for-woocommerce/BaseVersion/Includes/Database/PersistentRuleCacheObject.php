<?php

namespace ADP\BaseVersion\Includes\Database;

use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;

defined('ABSPATH') or exit;

class PersistentRuleCacheObject
{
    /** @var PersistentRule|null */
    public $rule;

    /** @var float|null */
    public $price;

    /**
     * @param PersistentRule|null $rule
     * @param float|null $price
     */
    public function __construct($rule = null, $price = null)
    {
        $this->rule  = $rule !== null ? $rule : null;
        $this->price = $price !== null ? (float)$price : null;
    }
}
