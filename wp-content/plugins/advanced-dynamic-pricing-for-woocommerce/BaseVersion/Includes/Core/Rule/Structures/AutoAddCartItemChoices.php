<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

class AutoAddCartItemChoices implements \Serializable
{
    /**
     * @var AutoAddChoice[]
     */
    protected $choices;

    /**
     * @var float
     */
    protected $requiredQty;

    public function __construct()
    {
        $this->choices = array();
        $this->requiredQty = floatval(0);
    }

    public function __clone()
    {
        $newChoices = array();
        foreach ($this->choices as $newChoice) {
            $newChoices[] = clone $newChoice;
        }
        $this->choices = $newChoices;
    }

    /**
     * @param array<int, AutoAddChoice> $choices
     */
    public function setChoices($choices)
    {
        if (!is_array($choices)) {
            return;
        }

        $this->choices = array();
        foreach ($choices as $choice) {
            if ($choice instanceof AutoAddChoice) {
                $this->choices[] = $choice;
            }
        }
    }

    /**
     * @return array<int, AutoAddChoice>
     */
    public function getChoices()
    {
        return $this->choices;
    }


    /**
     * @param float $requiredQty
     */
    public function setRequiredQty($requiredQty)
    {
        if (is_numeric($requiredQty)) {
            $this->requiredQty = floatval($requiredQty);
        }
    }

    /**
     * @return float
     */
    public function getRequiredQty()
    {
        return $this->requiredQty;
    }

    /**
     * @param Rule $rule
     * @param int $index
     * @param AutoAdd $autoAdd
     *
     * @return string
     */
    public function generateHash($rule, $index, $autoAdd)
    {
        $pieces = array($rule->getHash(), strval($index), $this->serialize());

        return md5(join("_", $pieces));
    }

    /**
     * @return string|null
     */
    public function serialize()
    {
        $choices = $this->choices;
        sort($choices);

        return serialize(array(
            'choices' => array_map(function ($choice) {
                return $choice->serialize();
            }, $choices),
            'requiredQty' => $this->requiredQty,
        ));
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->choices = $data['choices'];
        $this->requiredQty = $data['requiredQty'];
    }
}
