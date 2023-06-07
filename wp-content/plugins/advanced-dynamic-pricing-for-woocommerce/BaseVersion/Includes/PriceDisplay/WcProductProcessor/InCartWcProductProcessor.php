<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\WcProductProcessor;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Compatibility\SomewhereWarmBundlesCmp;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Debug\ProductCalculatorListener;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedGroupedProduct;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;
use ADP\BaseVersion\Includes\ProductExtensions\ProductExtension;
use ADP\BaseVersion\Includes\WC\DataStores\ProductVariationDataStoreCpt;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use ADP\Factory;
use Exception;
use ReflectionClass;
use ReflectionException;
use WC_Product;

class InCartWcProductProcessor implements IWcProductProcessor
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProductCalculatorListener
     */
    protected $listener;

    /**
     * @var Cart
     */
    protected $cart;

    public function __construct()
    {
        $this->context = adp_context();
        $this->listener = new ProductCalculatorListener();
    }

    public function withCart(Cart $cart)
    {
        $this->cart = $cart;
    }

    protected function isCartExists(): bool
    {
        return isset($this->cart);
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param WC_Product|int $theProduct
     * @param float $qty
     * @param array $cartItemData
     *
     * @return ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct|null
     */
    public function calculateProduct($theProduct, $qty = 1.0, $cartItemData = array())
    {
        if (is_numeric($theProduct)) {
            $product = CacheHelper::getWcProduct($theProduct);
        } elseif ($theProduct instanceof WC_Product) {
            $product = clone $theProduct;
        } else {
            return null;
        }

        if ($product instanceof \WC_Product_Grouped) {
            /** @var $processed ProcessedGroupedProduct */
            $processed = Factory::get("PriceDisplay_ProcessedGroupedProduct", $product, $qty);
            $children = array_filter(
                array_map(
                    'wc_get_product',
                    $product->get_children()
                ),
                'wc_products_array_filter_visible_grouped'
            );

            foreach ($children as $childId) {
                $processedChild = $this->calculateProduct($childId, $qty, $cartItemData);

                if (is_null($processedChild)) {
                    continue;
                }

                $processed->useChild($processedChild);
            }
        } elseif ($product instanceof \WC_Product_Variable) {
            /** @var $processed ProcessedVariableProduct */
            $processed = Factory::get("PriceDisplay_ProcessedVariableProduct", $this->context, $product, $qty);
            $children = $product->get_visible_children();

            foreach ($children as $childId) {
                $processedChild = $this->calculate($childId, $qty, $cartItemData, $product);

                if (is_null($processedChild)) {
                    continue;
                }

                $processed->useChild($processedChild);
            }
        } else {
            $processed = $this->calculate($product, $qty, $cartItemData);
        }

        return $processed;
    }

    /**
     * @param WC_Product|int $theProduct
     * @param float $qty
     * @param array $cartItemData
     * @param WC_Product|null $theParentProduct
     *
     * @return ProcessedProductSimple|null
     */
    protected function calculate($theProduct, $qty = 1.0, $cartItemData = array(), $theParentProduct = null)
    {
        if (!$this->isCartExists()) {
            return null;
        }

        if (is_numeric($theProduct)) {
            $product = CacheHelper::getWcProduct($theProduct);
        } elseif ($theProduct instanceof WC_Product) {
            $product = clone $theProduct;
        } else {
            $product = null;
        }

        if ($product === null) {
            return null;
        }

        if (is_numeric($theParentProduct)) {
            $parent = CacheHelper::getWcProduct($theParentProduct);
        } elseif ($theParentProduct instanceof WC_Product) {
            $parent = clone $theParentProduct;
        } else {
            $parent = null;
        }


        $tmpItems = [];
        $currentQty = 0.0;
        $qtyAlreadyInCart = floatval(0);

        $cartItems = [];
        foreach ( $this->cart->getItems() as $loopItem ) {
            $cartItems[] = clone $loopItem;
        }

        if ( has_filter("adp_in_cart_wc_product_processor_cart_items") ) {
            $cartItems = apply_filters(
                "adp_in_cart_wc_product_processor_cart_items",
                $cartItems
            );
        } else {
            if ($this->context->getOption("process_product_strategy_after_use_price") === "first") {
                $cartItems = InCartWcProductProcessorPredefinedSortCallbacks::cartItemsAsIs($cartItems);
            } elseif ($this->context->getOption("process_product_strategy_after_use_price") === "last") {
                $cartItems = InCartWcProductProcessorPredefinedSortCallbacks::cartItemsInReverseOrder($cartItems);
            } elseif ($this->context->getOption("process_product_strategy_after_use_price") === "cheapest") {
                $cartItems = InCartWcProductProcessorPredefinedSortCallbacks::sortCartItemsByPriceAsc($cartItems);
            } elseif ($this->context->getOption("process_product_strategy_after_user_price") === "most_expensive") {
                $cartItems = InCartWcProductProcessorPredefinedSortCallbacks::sortCartItemsByPriceDesc($cartItems);
            }
        }


        foreach ($cartItems as $loopItem) {
            $loopProduct = $loopItem->getWcItem()->getProduct();

            $condition = $loopItem->getWcItem()->getProduct()->get_id() === $product->get_id();

            if ($parent !== null) {
                $condition &= $loopItem->getWcItem()->getProduct()->get_parent_id() === $product->get_parent_id("edit");
            }

            if ($product instanceof \WC_Product_Variation && $loopProduct instanceof \WC_Product_Variation) {
                $loopProductVariationAttributes = $loopProduct->get_variation_attributes();

                foreach ($product->get_variation_attributes() as $key => $value) {
                    $condition &= !isset($loopProductVariationAttributes[$key]) || $loopProductVariationAttributes[$key] !== $value;
                }
            }

            // cart item data
            foreach ($loopItem->getWcItem()->getThirdPartyData() as $key => $value) {
                $condition &= !isset($cartItemData[$key]) || $cartItemData[$key] !== $value;
            }

            if ($condition) {
                $requiredQty = $qty - $currentQty;

                if ($requiredQty > $loopItem->getQty()) {
                    $tmpItems[] = clone $loopItem;
                    $currentQty += $loopItem->getQty();
                } elseif ($requiredQty === $loopItem->getQty()) {
                    $tmpItems[] = clone $loopItem;
                    break;
                } else {
                    $newLoopItem = clone $loopItem;
                    $newLoopItem->setQty($requiredQty);
                    $tmpItems[] = $newLoopItem;
                    break;
                }
            }

            if ($loopProduct->get_id() === $product->get_id()) {
                $qtyAlreadyInCart += $loopItem->getQty();
            }
        }

        $qtyAlreadyInCart = $qtyAlreadyInCart - array_sum(array_map(function ($item) {
                return $item->getQty();
            }, $tmpItems));

        if (count($tmpItems) === 0) {
            return null;
        }

        $tmpItems = apply_filters("adp_before_processed_product", $tmpItems, $this);
        $processedProduct = new ProcessedProductSimple($this->context, $product, $tmpItems);
        $processedProduct->setQtyAlreadyInCart($qtyAlreadyInCart);
        $this->listener->processedProduct($processedProduct);

        return $processedProduct;
    }

    /**
     * @return ProductCalculatorListener
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }
}
