<?php

namespace ADP\Settings\Constants;

class Constant
{
    private $id;
    private $title;
    private $value;

    public function __construct($id, $title, $value)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    public function getKey()
    {
        return $this->id;
    }

    public function getId()
    {
        return $this->id;
    }
}


