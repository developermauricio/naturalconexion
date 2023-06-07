<?php

namespace ADP\Settings;

use ADP\Settings\Interfaces\OriginOptionInterface;
use ADP\Settings\Constants\Constant;
use ADP\Settings\Exceptions\KeyNotFound;
use ADP\Settings\Interfaces\StoreStrategyInterface;

class OptionsManager
{

    /**
     * @var OptionsList
     */
    protected $options;

    /**
     * @var ConstantsList
     */
    protected $constants;

    /**
     * @var StoreStrategyInterface
     */
    protected $storeStrategy;

    /**
     * @param StoreStrategyInterface $storeStrategy
     */
    public function __construct($storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
        $this->options       = new OptionsList();
        $this->constants     = new ConstantsList();
    }

    public function installOptions($options)
    {
        if ($options instanceof OptionsList) {
            $this->options = $options;
        }
    }

    public function installConstants($constants)
    {
        if ($constants instanceof ConstantsList) {
            $this->constants = $constants;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        $option = $this->tryGetOption($key);

        return $option ? $option->set($value) : false;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        $option = $this->tryGetOption($key);

        return $option ? $option->get() : null;
    }

    public function getOptions()
    {
        return $this->options->getOptionsArray();
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getConstant($key)
    {
        $constant = $this->tryGetConstant($key);

        return $constant ? $constant->get() : null;
    }

    /**
     * @param string $key
     *
     * @return OriginOptionInterface|null
     */
    public function tryGetOption(string $key)
    {
        try {
            $option = $this->options->getByKey($key);
        } catch (KeyNotFound $exception) {
            // TODO if in production LOG IT!
            $option = null;
        }

        return $option;
    }

    /**
     * @param string $key
     *
     * @return Constant|null
     */
    public function tryGetConstant(string $key)
    {
        try {
            $const = $this->constants->getByKey($key);
        } catch (KeyNotFound $exception) {
            // TODO if in production LOG IT!
            $const = null;
        }

        return $const;
    }

    public function save()
    {
        $this->storeStrategy->save($this->options);
    }

    public function load()
    {
        $this->storeStrategy->load($this->options);
    }
}

