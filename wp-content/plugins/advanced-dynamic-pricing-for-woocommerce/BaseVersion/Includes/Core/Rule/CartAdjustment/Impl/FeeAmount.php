<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Impl;

use ADP\BaseVersion\Includes\Core\Cart\Fee;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustmentsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\FeeCartAdj;

defined('ABSPATH') or exit;

class FeeAmount extends AbstractCartAdjustment implements FeeCartAdj, CartAdjustment
{
    /**
     * @var float
     */
    protected $feeValue;

    /**
     * @var string
     */
    protected $feeName;

    /**
     * @var string
     */
    protected $feeTaxClass;

    public static function getType()
    {
        return 'fee__amount';
    }

    public static function getLabel()
    {
        return __('Fixed fee, once', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'cart_adjustments/fee.php';
    }

    public static function getGroup()
    {
        return CartAdjustmentsLoader::GROUP_FEE;
    }

    public function __construct()
    {
        $this->amountIndexes = array('feeValue');
    }

    /**
     * @param float $feeValue
     */
    public function setFeeValue($feeValue)
    {
        $this->feeValue = $feeValue;
    }

    /**
     * @param string $feeName
     */
    public function setFeeName($feeName)
    {
        $this->feeName = $feeName;
    }

    /**
     * @param string $taxClass
     */
    public function setFeeTaxClass($taxClass)
    {
        $this->feeTaxClass = $taxClass;
    }

    public function getFeeValue()
    {
        return $this->feeValue;
    }

    public function getFeeName()
    {
        return $this->feeName;
    }

    public function getFeeTaxClass()
    {
        return $this->feeTaxClass;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return isset($this->feeValue) or isset($this->feeName) or isset($this->feeTaxClass);
    }

    public function applyToCart($rule, $cart)
    {
        $context = $cart->getContext()->getGlobalContext();
        $cart->addFee(
            new Fee(
                $context,
                Fee::TYPE_FIXED_VALUE,
                $this->feeName,
                $this->feeValue,
                $this->feeTaxClass,
                $rule->getId()
            )
        );
    }

    public function translate()
    {
        $this->feeName = _x($this->feeName, "Fee name from rule", 'advanced-dynamic-pricing-for-woocommerce');
    }
}
