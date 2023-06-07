<?php

namespace ADP\BaseVersion\Includes\Core;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;
use ADP\BaseVersion\Includes\Database\RulesCollection;

class CartCalculatorPersistent implements ICartCalculator
{
    /**
     * @var PersistentRule
     */
    protected $rule;
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context|PersistentRule $contextOrRule
     * @param PersistentRule|null $deprecated
     */
    public function __construct($contextOrRule, $deprecated = null)
    {
        $this->context = adp_context();
        $this->rule    = $contextOrRule instanceof PersistentRule ? $contextOrRule : $deprecated;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param Cart $cart
     * @param CartItem $item
     *
     * @return bool
     */
    public function processItem(&$cart, $item)
    {
        if ($cart->isEmpty()) {
            return false;
        }

        $appliedRules = 0;

        $proc = $this->rule->buildProcessor($this->context);
        if ($proc->applyToCartItem($cart, $item)) {
            $appliedRules++;
        }

        $result = boolval($appliedRules);

        if ($result) {
            if ('compare_discounted_and_sale' === $this->context->getOption('discount_for_onsale')) {
                $newItems = array();
                foreach ($cart->getItems() as $item) {
                    $productPrice = $item->getOriginalPrice();
                    foreach ($item->getDiscounts() as $ruleId => $amounts) {
                        $productPrice -= array_sum($amounts);
                    }
                    if ($this->context->getOption('is_calculate_based_on_wc_precision')) {
                        $productPrice = round($productPrice, wc_get_price_decimals());
                    }

                    $product     = $item->getWcItem()->getProduct();
                    $wcSalePrice = null;

                    /** Always remember about scheduled WC sales */
                    if ($product->is_on_sale('edit') && $product->get_sale_price('edit') !== '') {
                        $wcSalePrice = floatval($product->get_sale_price('edit'));
                    }

                    if ( ! is_null($wcSalePrice) && $wcSalePrice < $productPrice) {
                        $newItem = new CartItem($item->getWcItem(), $wcSalePrice, $item->getQty(), $item->getPos());

                        foreach ($item->getAttrs() as $attr) {
                            $newItem->addAttr($attr);
                        }

                        foreach ($item->getMarks() as $mark) {
                            $newItem->addMark($mark);
                        }

                        $minDiscountRangePrice = $item->getMinDiscountRangePrice();
                        if ($minDiscountRangePrice !== null) {
                            $minDiscountRangePrice = $minDiscountRangePrice < $wcSalePrice ? $minDiscountRangePrice : $wcSalePrice;
                            $newItem->setMinDiscountRangePrice($minDiscountRangePrice);
                        }

                        $item = $newItem;
                    }

                    $newItems[] = $item;
                }

                $cart->setItems($newItems);
            } elseif ('discount_regular' === $this->context->getOption('discount_for_onsale')) {
                $newItems = array();
                foreach ($cart->getItems() as $item) {
                    $product     = $item->getWcItem()->getProduct();
                    $wcSalePrice = null;

                    /** Always remember about scheduled WC sales */
                    if ($product->is_on_sale('edit') && $product->get_sale_price('edit') !== '') {
                        $wcSalePrice = floatval($product->get_sale_price('edit'));
                    }

                    if ( ! is_null($wcSalePrice) && count($item->getHistory()) == 0) {
                        $newItem = new CartItem($item->getWcItem(), $wcSalePrice, $item->getQty(), $item->getPos());

                        foreach ($item->getAttrs() as $attr) {
                            $newItem->addAttr($attr);
                        }

                        foreach ($item->getMarks() as $mark) {
                            $newItem->addMark($mark);
                        }

                        $minDiscountRangePrice = $item->getMinDiscountRangePrice();
                        if ($minDiscountRangePrice !== null) {
                            $newItem->setMinDiscountRangePrice($minDiscountRangePrice);
                        }

                        $item = $newItem;
                    }

                    $newItems[] = $item;
                }

                $cart->setItems($newItems);
            }
        }

        return $result;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function processCart(&$cart)
    {
        return true;
    }


    public function getRulesCollection()
    {
        return new RulesCollection(array($this->rule));
    }
}
