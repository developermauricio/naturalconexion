<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

class Notice
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $id
     * @param array  $data
     */
    public function __construct($id, $data = [])
    {
        $this->id   = $id ? (string)$id : '';
        $this->data = is_array($data) ? $data : [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }
}
