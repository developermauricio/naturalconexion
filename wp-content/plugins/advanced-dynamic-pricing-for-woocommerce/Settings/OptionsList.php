<?php

namespace ADP\Settings;

use ADP\Settings\Exceptions\KeyNotFound;
use ADP\Settings\Interfaces\OriginOptionInterface;
use ADP\Settings\Varieties\Option\Interfaces\OptionInterface;

class OptionsList
{
    /**
     * @var OptionInterface[]
     */
    protected $list = array();

    /**
     * @param OriginOptionInterface[] $options
     */
    public function register(...$options)
    {
        if ( ! $options || ! is_array($options)) {
            return;
        }

        foreach ($options as $option) {
            if ($option instanceof OriginOptionInterface) {
                $this->list[$option->getId()] = $option;
            }
        }
    }

    /**
     * @param string $key
     *
     * @return OriginOptionInterface
     * @throws KeyNotFound
     */
    public function getByKey(string $key)
    {
        if ( ! isset($this->list[$key])) {
            throw new KeyNotFound($key);
        }

        return $this->list[$key];
    }

    public function getOptionsArray()
    {
        if (isset($this->list)) {
            $options = array();

            foreach ($this->list as $id => $option) {
                $options[$id] = $option->get();
            }

            return $options;
        } else {
            return false;
        }
    }
}
