<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Context;
use Exception;

defined('ABSPATH') or exit;

class Fee
{
    const TYPE_ITEM_OVERPRICE = 'item';
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED_VALUE = 'fixed_value';

    const AVAILABLE_TYPES = array(
        self::TYPE_ITEM_OVERPRICE,
        self::TYPE_PERCENTAGE,
        self::TYPE_FIXED_VALUE,
    );

    /**
     * @var int
     */
    protected $ruleId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var float
     */
    protected $value;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $taxClass;

    /**
     * @var array
     */
    protected $availableTaxClasses;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @param Context|string $contextOrType
     * @param string $typeOrName
     * @param string|float $nameOrValue
     * @param float|string $valueOrTaxClass
     * @param string|int $taxClassOrRuleId
     * @param int|null $deprecated
     */
    public function __construct($contextOrType, $typeOrName, $nameOrValue, $valueOrTaxClass, $taxClassOrRuleId, $deprecated = null)
    {
        $context = adp_context();

        if ( is_string($contextOrType) ) {
            $type = $contextOrType;
            $name = $typeOrName;
            $value = $nameOrValue;
            $taxClass = $valueOrTaxClass;
            $ruleId = $taxClassOrRuleId;
        } else {
            $type = $typeOrName;
            $name = $nameOrValue;
            $value = $valueOrTaxClass;
            $taxClass = $taxClassOrRuleId;
            $ruleId = $deprecated;
        }

        if ( ! in_array($type, self::AVAILABLE_TYPES)) {
            $context->handleError(new Exception(sprintf("Coupon type '%s' not supported", $type)));
        }

        $this->availableTaxClasses = $context->getAvailableTaxClassSlugs();

        $this->type     = $type;
        $this->name     = (string)$name;
        $this->value    = floatval($value);
        $this->ruleId   = $ruleId;
        $this->taxClass = in_array($taxClass, $this->availableTaxClasses) ? $taxClass : "";
    }

    public function withContext(Context $context)
    {
        $this->availableTaxClasses = $context->getAvailableTaxClassSlugs();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isType($type)
    {
        return $this->type === $type;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = floatval($value);
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * @param string $taxClass
     */
    public function setTaxClass($taxClass)
    {
        if (in_array($taxClass, $this->availableTaxClasses)) {
            $this->taxClass = $taxClass;
        }
    }

    /**
     * @return string
     */
    public function getTaxClass()
    {
        return $this->taxClass;
    }

    public function isTaxAble()
    {
        return ! empty($this->taxClass);
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
