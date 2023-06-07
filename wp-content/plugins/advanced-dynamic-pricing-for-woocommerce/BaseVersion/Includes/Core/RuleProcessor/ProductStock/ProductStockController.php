<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Cache\CacheHelper;

defined('ABSPATH') or exit;

class ProductStockController
{
    /**
     * @var array<int,ProductStockItem>
     */
    protected $stockItems;

    public function __construct()
    {
        $this->stockItems = array();
    }

    /**
     * @param int $productId
     * @param float $qty
     * @param int $parentId
     * @param array $variationAttributes
     * @param array $cartItemData
     */
    public function add(
        $productId,
        $qty,
        $parentId = 0,
        $variationAttributes = array(),
        $cartItemData = array()
    ) {
        if ( ! is_numeric($productId) || ! is_numeric($productId)) {
            return;
        }

        $productId = intval($productId);
        $qty       = floatval($qty);

        $this->addItem(new ProductStockItem($productId, $qty, $parentId, $variationAttributes, $cartItemData));
    }

    /**
     * @param ProductStockItem $item
     */
    public function addItem($item)
    {
        if ( ! ($item instanceof ProductStockItem)) {
            return;
        }

        foreach ($this->stockItems as $loopItem) {
            if ($loopItem->getHash() === $item->getHash()) {
                $loopItem->addQty($item->getQty());

                return;
            }
        }

        $this->stockItems[] = $item;
    }

    /**
     * @param int $productId
     * @param int $parentId
     * @param array $variationAttributes
     * @param array $cartItemData
     *
     * @return float
     */
    public function get(
        $productId,
        $parentId = 0,
        $variationAttributes = array(),
        $cartItemData = array()
    ) {
        $stockItem = new ProductStockItem($productId, floatval(0), $parentId, $variationAttributes, $cartItemData);

        foreach ($this->stockItems as $loopItem) {
            if ($loopItem->getHash() === $stockItem->getHash()) {
                return $loopItem->getQty();
            }
        }

        return floatval(0);
    }

    public function purge()
    {
        $this->stockItems = array();
    }

    /**
     * @param int $productId
     * @param float $qtyRequired
     * @param int $parentId
     * @param array $variationAttributes
     * @param array $cartItemData
     *
     * @return float
     */
    public function getQtyAvailableForSale(
        $productId,
        $qtyRequired,
        $parentId = 0,
        $variationAttributes = array(),
        $cartItemData = array()
    ) {
        $product = CacheHelper::getWcProduct($productId);

        if ( ! $product->is_in_stock()) {
            $qty = 0;
        } elseif ($product->managing_stock()) {
            if ($product->backorders_allowed()) {
                $qty = $qtyRequired;
            } else {
                $availableForNow = $product->get_stock_quantity() - $this->get($productId, $parentId,
                        $variationAttributes, $cartItemData);
                $availableForNow = max(0, $availableForNow);
                $qty             = $availableForNow >= $qtyRequired ? $qtyRequired : $availableForNow;
            }
        } else {
            $qty = $qtyRequired;
        }

        return floatval($qty);
    }

    /**
     * @param Cart $cart
     */
    public function initFromCart($cart)
    {
        $this->purge();

        foreach ($cart->getFreeItems() as $item) {
            $stockItem = new ProductStockItem(
                $item->getProduct()->get_id(),
                $item->getQty(),
                $item->getProduct()->get_parent_id()
            );

            $this->addItem($stockItem);
        }

        foreach ($cart->getItems() as $item) {
            $facade  = $item->getWcItem();
            $product = $facade->getProduct();

            $stockItem = new ProductStockItem(
                $product->get_id(),
                $item->getQty(),
                $product->get_parent_id(),
                $facade->getVariation(),
                $facade->getThirdPartyData()
            );

            $this->addItem($stockItem);
        }
    }
}
