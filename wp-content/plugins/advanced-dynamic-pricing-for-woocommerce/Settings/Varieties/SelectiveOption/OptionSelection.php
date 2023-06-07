<?php

namespace ADP\Settings\Varieties\SelectiveOption;

class OptionSelection
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * OptionSelection constructor.
     *
     * @param mixed $value
     * @param string $title
     */
    public function __construct($value, $title = null)
    {
        $this->value = $value;

        if (is_null($title)) {
            $this->setTitle($value);
        } else {
            $this->setTitle($title);
        }
    }

    /**
     * @param mixed $title
     *
     * @return bool
     */
    public function setTitle($title)
    {
        if ( ! is_string($title) && $title) {
            return false;
        }

        $this->title = $title;

        return true;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
