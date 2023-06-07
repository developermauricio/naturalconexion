<?php

namespace ADP\HighLander;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CallBackDetails
{
    const TYPE_UNKNOWN = 0;
    const TYPE_COMPLETE = 1;
    const TYPE_FUNCTION = 2;
    const TYPE_CLOSURE = 3;

    /**
     * @var callable
     */
    protected $function;

    /**
     * @var int
     */
    protected $type;

    /**
     * @param array $callbackDetails
     */
    public function __construct($callbackDetails)
    {
        $this->type = self::TYPE_UNKNOWN;
        $this->parse($callbackDetails);
    }

    /**
     * @param array $callbackDetails
     */
    private function parse($callbackDetails)
    {
        if ( ! isset($callbackDetails['function'])) {
            return;
        }

        $function = $callbackDetails['function'];

        if ($function instanceof \Closure) {
            $this->function = $function;
            $this->type     = self::TYPE_CLOSURE;

            return;
        }

        if (is_array($function)) {
            $callbackObj  = $function[0];
            $callbackFunc = $function[1];
        } elseif (is_string($function)) {
            $callbackObj  = '';
            $callbackFunc = $function;
        } else {
            return;
        }

        if (is_object($callbackObj)) {
            $callbackClass = get_class($callbackObj);
        } elseif (is_string($callbackObj) && class_exists($callbackObj)) {
            $callbackClass = $callbackObj;
        } else {
            $callbackClass = null;
        }

        if (isset($callbackClass)) {
            $this->function = array($callbackClass, $callbackFunc);
            $this->type     = self::TYPE_COMPLETE;
        } else {
            $this->function = $callbackFunc;
            $this->type     = self::TYPE_FUNCTION;
        }
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return bool
     */
    public function isType($type)
    {
        return $this->type === $type;
    }

    /**
     * @return string|null
     */
    public function maybeGetClassName()
    {
        if ($this->type !== self::TYPE_COMPLETE) {
            return null;
        }

        list($className, $methodName) = $this->function;

        return $className;
    }

    /**
     * @return string|null
     */
    public function maybeGetClassMethodName()
    {
        if ($this->type !== self::TYPE_COMPLETE) {
            return null;
        }

        list($className, $methodName) = $this->function;

        return $methodName;
    }

    /**
     * @return string|null
     */
    public function maybeGetFuncName()
    {
        if ($this->type !== self::TYPE_FUNCTION) {
            return null;
        }

        return $this->function;
    }

    public function maybeGetClosure()
    {
        if ($this->type !== self::TYPE_CLOSURE) {
            return null;
        }

        return $this->function;
    }

    /**
     * @return callable
     */
    public function getFunc()
    {
        return $this->function;
    }

}
