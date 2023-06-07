<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

class AutoAddsCollection
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var array<int,AutoAdd>
     */
    protected $autoAdds;

    /**
     * AutoAddCollection constructor.
     *
     * @param Rule $rule
     */
    public function __construct($rule)
    {
        $this->rule  = $rule;
        $this->autoAdds = array();
    }

    public function __clone()
    {
        $this->autoAdds = array_map(function ($gift) {
            return clone $gift;
        }, $this->autoAdds);
    }

    /**
     * @param AutoAdd $autoAdd
     */
    public function add($autoAdd)
    {
        if ($this->isAutoAdd($autoAdd)) {
            $this->autoAdds[$this->generateHash($autoAdd)] = $autoAdd;
        }
    }

    /**
     * @param array<int,AutoAdd> ...$autoAdds
     */
    public function bulkAdd(...$autoAdds)
    {
        if ( ! is_array($autoAdds)) {
            return;
        }

        foreach ($autoAdds as $autoAdd) {
            $this->add($autoAdd);
        }
    }

    /**
     * @param string $hash
     */
    public function removeByHash($hash)
    {
        unset($this->autoAdds[$hash]);
    }

    public function purge()
    {
        $this->autoAdds = array();
    }

    /**
     * @return array<int,AutoAdd>
     */
    public function asArray()
    {
        return $this->autoAdds;
    }

    /**
     * @param AutoAdd $autoAdd
     *
     * @return bool
     */
    protected function isAutoAdd($autoAdd)
    {
        return $autoAdd instanceof AutoAdd && $autoAdd->isValid();
    }

    /**
     * @param AutoAdd $autoAdd
     *
     * @return string
     */
    protected function generateHash($autoAdd)
    {
        $pieces = array(strval($this->rule->getId()), strval(count($this->autoAdds)), serialize($autoAdd));

        return md5(join("_", $pieces));
    }
}
