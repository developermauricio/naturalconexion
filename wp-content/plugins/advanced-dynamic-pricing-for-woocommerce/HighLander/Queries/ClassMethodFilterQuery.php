<?php

namespace ADP\HighLander\Queries;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ClassMethodFilterQuery
{
    const ACTION_SAVE = 1;
    const ACTION_REMOVE = 2;

    const ALLOWED_ACTIONS = array(
        self::ACTION_SAVE,
        self::ACTION_REMOVE,
    );

    /**
     * @var array[] array(class_name, method_name)[]
     */
    protected $list = array();

    /**
     * @var int
     */
    protected $action;

    /**
     * @var string|null
     */
    protected $tag;

    public function __construct()
    {
        $this->action = null;
        $this->list   = array();
    }

    public function isValid()
    {
        return $this->action !== null;
    }

    /**
     * @param array<int, string>|array<int, array<int, string>>|null $list
     *
     * @return self
     */
    public function setList($list)
    {
        $this->list = array();

        if (is_array($list)) {
            foreach ($list as $item) {
                if (is_array($item) && count($item) === 2 && isset($item[0], $item[1])) {
                    $className  = is_object($item[0]) ? get_class($item[0]) : strval($item[0]);
                    $methodName = strval($item[1]);

                    if ($className && $methodName) {
                        $this->list[] = array($className, $methodName);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return array[]
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param int|null $action
     *
     * @return self
     */
    public function setAction($action)
    {
        if (in_array($action, self::ALLOWED_ACTIONS)) {
            $this->action = $action;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param int|null $action
     *
     * @return bool
     */
    public function isAction($action)
    {
        return in_array($action, self::ALLOWED_ACTIONS) ? $this->action === $action : false;
    }

    /**
     * @param string|null $tag
     *
     * @return self
     */
    public function useTag($tag)
    {
        if (is_string($tag)) {
            $this->tag = $tag;
        }

        return $this;
    }

    /**
     * @param string|null $tag
     *
     * @return bool
     */
    public function isUseTag($tag)
    {
        if ( ! is_string($tag)) {
            return false;
        }

        return ! is_null($this->tag) ? $this->tag === $tag : true;
    }

    /**
     * @return string|null
     */
    public function getTag()
    {
        return $this->tag;
    }
}
