<?php

namespace ADP\BaseVersion\Includes\Core\Cart\Coupon;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use Exception;

defined('ABSPATH') or exit;

class CouponCart implements CouponInterface
{
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED_VALUE = 'fixed_value';

    const AVAILABLE_TYPES = array(
        self::TYPE_PERCENTAGE,
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
     * @var string
     */
    protected $code;

    /**
     * @var string Original coupon name
     */
    protected $label;

    /**
     * @var float
     */
    protected $maxDiscount;

    /**
     * @var WcCartItemFacade
     */
    protected $affectedCartItem;

    /**
     * @param Context $context
     * @param string $type
     * @param string $code
     * @param float|string $value
     * @param int $ruleId
     */
    public function __construct($context, $type, $code, $value, $ruleId)
    {
        if ( ! in_array($type, self::AVAILABLE_TYPES)) {
            $context->handleError(new Exception(sprintf("Coupon type '%s' not supported", $type)));
        }

        $this->type   = $type;
        $this->label  = $code;
        $this->code   = wc_format_coupon_code($code);
        $this->value  = floatval($value);
        $this->ruleId = $ruleId;
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
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = (string)$code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
     * @param float $amount
     */
    public function setMaxDiscount($amount)
    {
        $this->maxDiscount = $amount;
    }

    /**
     * @return float
     */
    public function getMaxDiscount()
    {
        return $this->maxDiscount;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isMaxDiscountDefined()
    {
        return isset($this->maxDiscount) && $this->maxDiscount > 0;
    }
}
