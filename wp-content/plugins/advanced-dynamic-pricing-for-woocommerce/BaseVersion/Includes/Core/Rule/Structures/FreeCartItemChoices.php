<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Enums\GiftModeEnum;

defined('ABSPATH') or exit;

class FreeCartItemChoices implements \Serializable
{
    /**
     * @var GiftChoice[]
     */
    protected $choices;

    /**
     * @var float
     */
    protected $requiredQty;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var array
     */
    protected $attributes;
    const ATTR_TEMP = 'temporary';

    public function __construct()
    {
        $this->choices     = array();
        $this->requiredQty = floatval(0);
        $this->required    = false;
        $this->attributes  = array();
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
     * @param array<int, GiftChoice> $choices
     */
    public function setChoices($choices)
    {
        if ( ! is_array($choices)) {
            return;
        }

        $this->choices = array();
        foreach ($choices as $choice) {
            if ($choice instanceof GiftChoice) {
                $this->choices[] = $choice;
            }
        }
    }

    /**
     * @return array<int, GiftChoice>
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
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = boolval($required);
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param Rule $rule
     * @param int $index
     * @param Gift $gift
     *
     * @return string
     */
    public function generateHash($rule, $index, $gift)
    {
        if ($gift->getMode()->equals(GiftModeEnum::ALLOW_TO_CHOOSE()) || $gift->getMode()->equals(GiftModeEnum::ALLOW_TO_CHOOSE_FROM_PRODUCT_CAT())) {
            $tmpMode = "allow";
        } elseif ($gift->getMode()->equals(GiftModeEnum::REQUIRE_TO_CHOOSE()) || $gift->getMode()->equals(GiftModeEnum::REQUIRE_TO_CHOOSE_FROM_PRODUCT_CAT())) {
            $tmpMode = "required";
        } else {
            $tmpMode = "false";
        }

        $pieces = array($rule->getHash(), strval($index), strval($tmpMode), $this->serialize());

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
            'choices'     => array_map(function ($choice) {
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

        $this->choices     = $data['choices'];
        $this->requiredQty = $data['requiredQty'];
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function hasAttr($attribute)
    {
        return in_array($attribute, $this->attributes);
    }

    public function addAttr(...$attributes)
    {
        $allowedAttrs = array(
            self::ATTR_TEMP,
        );

        foreach ($attributes as $attribute) {
            if (in_array($attribute, $allowedAttrs)) {
                $this->attributes[] = $attribute;
            }
        }
    }

    public function removeAttr(...$attributes)
    {
        foreach ($attributes as $attr) {
            $pos = array_search($attr, $this->attributes);

            if ($pos !== false) {
                unset($this->attributes[$pos]);
            }
        }

        $this->attributes = array_values($this->attributes);
    }

    public function getAttrs()
    {
        return $this->attributes;
    }
}
