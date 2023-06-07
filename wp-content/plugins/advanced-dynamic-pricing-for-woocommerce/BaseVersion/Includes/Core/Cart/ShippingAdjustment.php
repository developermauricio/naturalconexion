<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Context;
use Exception;

defined('ABSPATH') or exit;

class ShippingAdjustment
{
    const DEFAULT_SHIPPING_METHOD = 'all';

    const TYPE_FREE = 'free';
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_AMOUNT = 'fixed_amount';
    const TYPE_FIXED_VALUE = 'fixed_value';

    const AVAILABLE_TYPES = array(
        self::TYPE_FREE,
        self::TYPE_PERCENTAGE,
        self::TYPE_AMOUNT,
        self::TYPE_FIXED_VALUE,
    );

    /**
     * @var integer
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
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $shippingMethod;

    /**
     * @param Context|string $contextOrType
     * @param string|float $typeOrValue
     * @param float|string|int $valueOrRuleId
     * @param int|null $deprecated
     */
    public function __construct($contextOrType, $typeOrValue, $valueOrRuleId, $deprecated = null)
    {
        $context = adp_context();

        if (is_string($contextOrType)) {
            $type   = $contextOrType;
            $value  = $typeOrValue;
            $ruleId = $valueOrRuleId;
        } else {
            $type   = $typeOrValue;
            $value  = $valueOrRuleId;
            $ruleId = $deprecated;
        }

        if ( ! in_array($type, self::AVAILABLE_TYPES)) {
            $context->handleError(new Exception(sprintf("Shipping adjustment type '%s' not supported", $type)));
        }

        $this->type           = $type;
        $this->value          = floatval($value);
        $this->ruleId         = $ruleId;
        $this->shippingMethod = self::DEFAULT_SHIPPING_METHOD;
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
     * @param string $shippingMethod
     */
    public function setMethod($shippingMethod)
    {
        $this->shippingMethod = strval($shippingMethod);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @param string $methodId
     *
     * @return bool
     */
    public function isMethod($methodId)
    {
        return $this->shippingMethod === strval($methodId);
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

    public function setAmount($amount)
    {
        $this->amount = floatval($amount);
    }

    /**
     * @return float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
