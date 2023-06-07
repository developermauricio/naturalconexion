<?php

namespace ADP\BaseVersion\Includes\Core\Cart\Coupon;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use Exception;

defined('ABSPATH') or exit;

class CouponCartItem implements CouponInterface
{
    const TYPE_ITEM_DISCOUNT = 'item';
    const TYPE_FREE_ITEM = 'free_item';

    const AVAILABLE_TYPES = array(
        self::TYPE_ITEM_DISCOUNT,
        self::TYPE_FREE_ITEM,
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
     * @var string
     */
    protected $affectedCartItemKey;

    /**
     * @var float
     */
    protected $affectedCartItemQty;

    /**
     * @param Context $context
     * @param string $type
     * @param string $code
     * @param float $value
     * @param int $ruleId
     * @param WcCartItemFacade|null $affectedCartItem
     */
    public function __construct(
        Context $context,
        string $type,
        string $code,
        float $value,
        int $ruleId,
        $affectedCartItem,
        $affectedCartItemQty = null
    ) {
        if ( ! in_array($type, self::AVAILABLE_TYPES)) {
            $context->handleError(new Exception(sprintf("Coupon type '%s' not supported", $type)));
        }

        $this->type                = $type;
        $this->label               = $code;
        $this->code                = wc_format_coupon_code($code);
        $this->value               = floatval($value);
        $this->ruleId              = $ruleId;
        $this->affectedCartItemKey = "";
        $this->affectedCartItemQty = floatval(0);
        $this->setAffectedCartItem($affectedCartItem);

        if ( $affectedCartItemQty !== null ) {
            $this->affectedCartItemQty = floatval($affectedCartItemQty);
        }
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

    /**
     * @return string
     */
    public function getAffectedCartItemKey()
    {
        return $this->affectedCartItemKey;
    }

    /**
     * @param WcCartItemFacade|null $affectedCartItem
     */
    public function setAffectedCartItem($affectedCartItem)
    {
        if ($affectedCartItem instanceof WcCartItemFacade) {
            $this->affectedCartItemKey = $affectedCartItem->getKey();
            $this->affectedCartItemQty = $affectedCartItem->getQty();
        }
    }

    /**
     * @param float $affectedCartItemQty
     */
    public function setAffectedCartItemQty($affectedCartItemQty)
    {
        $this->affectedCartItemQty = (float)$affectedCartItemQty;
    }

    /**
     * @return float
     */
    public function getAffectedCartItemQty()
    {
        return $this->affectedCartItemQty;
    }
}
