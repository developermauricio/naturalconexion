<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Impl;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\Fee;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustmentsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\CartAdjUsingCollection;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces\FeeCartAdj;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSetCollection;

defined('ABSPATH') or exit;

class FeeAmountRepeatableSetsCount extends AbstractCartAdjustment implements FeeCartAdj, CartAdjustment, CartAdjUsingCollection
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
        return 'fee_repeatable_sets_count__amount';
    }

    public static function getLabel()
    {
        return __('Add fixed fee to each item line affected by rule', 'advanced-dynamic-pricing-for-woocommerce');
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

    /**
     * @param Rule $rule
     * @param Cart $cart
     */
    public function applyToCart($rule, $cart)
    {
    }

    /**
     * @param Rule $rule
     * @param Cart $cart
     * @param CartItemsCollection $itemsCollection
     */
    public function applyToCartWithItems($rule, $cart, $itemsCollection)
    {
        $context   = $cart->getContext()->getGlobalContext();
        $tax_class = ! empty($this->feeTaxClass) ? $this->feeTaxClass : "";

        for ($i = 0; $i < $itemsCollection->getCount(); $i++) {
            $cart->addFee(
                new Fee($context, Fee::TYPE_FIXED_VALUE, $this->feeName, $this->feeValue, $tax_class, $rule->getId())
            );
        }
    }

    /**
     * @param Rule $rule
     * @param Cart $cart
     * @param CartSetCollection $setCollection
     */
    public function applyToCartWithSets($rule, $cart, $setCollection)
    {
        $context   = $cart->getContext()->getGlobalContext();
        $taxClass = ! empty($this->feeTaxClass) ? $this->feeTaxClass : "";

        for ($i = 0; $i < count($setCollection->getSets()); $i++) {
            $cart->addFee(
                new Fee($context, Fee::TYPE_FIXED_VALUE, $this->feeName, $this->feeValue, $taxClass, $rule->getId())
            );
        }
    }

    public function translate()
    {
        $this->feeName = _x(
            $this->feeName,
            "Repeatable set count fee name from rule",
            'advanced-dynamic-pricing-for-woocommerce'
        );
    }
}
