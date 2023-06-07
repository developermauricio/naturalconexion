<?php

namespace ADP\Settings\Abstracts;

use ADP\Settings\Exceptions\OptionValueFilterFailed;
use ADP\Settings\Interfaces\OriginOptionInterface;

abstract class OriginOption implements OriginOptionInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;


    protected $value;
    protected $default;

    /**
     * @var boolean
     */
    protected $valueInstalled = false;

    /**
     * OriginOption constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id      = $id;
        $this->title   = $id;
        $this->value   = null;
        $this->default = null;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function filter($value)
    {
        return $value;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $value
     *
     * @return boolean
     */
    public function setDefault($value)
    {
        try {
            $value = $this->filter($value);
        } catch (OptionValueFilterFailed $e) {
            return false;
        }

        $this->default = $value;

        return true;
    }

    /**
     * @param mixed $value
     *
     * @return boolean
     */
    public function set($value)
    {
        try {
            $value = $this->filter($value);
        } catch (OptionValueFilterFailed $e) {
            return false;
        }

        $this->valueInstalled = true;
        $this->value          = $value;

        return true;
    }

    public function get()
    {
        return $this->valueInstalled ? $this->value : $this->default;
    }

    public function setTitle($title)
    {
        if (empty($title)) {
            return false;
        }

        $this->title = $title;

        return true;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isValueInstalled()
    {
        return $this->valueInstalled;
    }
}
