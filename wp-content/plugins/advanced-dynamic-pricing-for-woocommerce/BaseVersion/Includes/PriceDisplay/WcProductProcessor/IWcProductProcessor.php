<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\WcProductProcessor;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Debug\ProductCalculatorListener;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedGroupedProduct;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;

interface IWcProductProcessor
{
    public function withCart(Cart $cart);

    /**
     * @param \WC_Product|int $theProduct
     * @param float $qty
     * @param array $cartItemData
     *
     * @return ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct|null
     */
    public function calculateProduct($theProduct, $qty = 1.0, $cartItemData = array());

    public function withContext(Context $context);

    /**
     * @return ProductCalculatorListener
     */
    public function getListener();

    /**
     * @return Cart
     */
    public function getCart();
}
