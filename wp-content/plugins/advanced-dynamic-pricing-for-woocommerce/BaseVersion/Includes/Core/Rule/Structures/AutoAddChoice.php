<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use ADP\BaseVersion\Includes\Enums\AutoAddChoiceMethodEnum;
use ADP\BaseVersion\Includes\Enums\AutoAddChoiceTypeEnum;

defined('ABSPATH') or exit;

class AutoAddChoice implements \Serializable
{
    /**
     * @var AutoAddChoiceTypeEnum
     */
    protected $type;

    /**
     * @var AutoAddChoiceMethodEnum
     */
    protected $method;

    /**
     * @var array
     */
    protected $values;

    public function __construct()
    {
        $this->type   = new AutoAddChoiceTypeEnum();
        $this->method = new AutoAddChoiceMethodEnum();
        $this->values = array();
    }

    /**
     * @param AutoAddChoiceTypeEnum $type
     *
     * @return self
     */
    public function setType($type)
    {
        if ($type instanceof AutoAddChoiceTypeEnum) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * @param AutoAddChoiceMethodEnum $method
     *
     * @return self
     */
    public function setMethod($method)
    {
        if ($method instanceof AutoAddChoiceMethodEnum) {
            $this->method = $method;
        }

        return $this;
    }

    /**
     * @param array $values
     *
     * @return self
     */
    public function setValues($values)
    {
        if (is_array($values)) {
            $this->values = $values;
        }

        return $this;
    }

    /**
     * @return AutoAddChoiceTypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return AutoAddChoiceMethodEnum
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    public function isValid()
    {
        if (strval($this->type) === AutoAddChoiceTypeEnum::CLONE_ADJUSTED) {
            return true;
        }

        return count($this->values) > 0;
    }

    /**
     * @return string|null
     */
    public function serialize()
    {
        // due to random gifting we modify values on runtime, so need to sort before serialize
        $values = $this->values;
        sort($values);

        return serialize(array(
            'type'   => $this->type,
            'method' => $this->method,
            'values' => $values,
        ));
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->type   = $data['type'];
        $this->method = $data['method'];
        $this->values = $data['values'];
    }
}
