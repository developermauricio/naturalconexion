<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock;

defined('ABSPATH') or exit;

class ProductStockItem
{
    /**
     * @var string
     */
    protected $hash;

    /**
     * @var int
     */
    protected $productID;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var array
     */
    protected $variationAttributes;

    /**
     * @var array
     */
    protected $cartItemData;

    /**
     * @var float
     */
    protected $qty;

    /**
     * @param int $prodID
     * @param float $qty
     * @param int $parentId
     * @param array $variationAttributes
     * @param array $cartItemData
     */
    public function __construct(
        $prodID,
        $qty,
        $parentId = 0,
        $variationAttributes = array(),
        $cartItemData = array()
    ) {
        $this->setProductId($prodID);
        $this->setQty($qty);
        $this->setParentId($parentId);
        $this->setVariationAttributes($variationAttributes);
        $this->setCartItemData($cartItemData);

        $this->recalculateHash();
    }

    /**
     * @param float $qty
     */
    public function setQty($qty)
    {
        if (is_numeric($qty)) {
            $this->qty = floatval($qty);
        }
    }

    public function addQty($qty)
    {
        if (is_numeric($qty)) {
            $this->qty += floatval($qty);
        }
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param int $productId
     */
    protected function setProductId($productId)
    {
        $productId = intval($productId);

        if ($productId) {
            $this->productID = $productId;
        }
    }

    /**
     * @param int $parentId
     */
    protected function setParentId($parentId)
    {
        $this->parentId = intval($parentId);
    }

    /**
     * @param array $attributes
     */
    protected function setVariationAttributes($attributes)
    {
        $this->variationAttributes = array();

        return;
        if (is_array($attributes)) {
            $this->variationAttributes = $attributes;
        }
    }

    /**
     * @param array $cartItemData
     */
    protected function setCartItemData($cartItemData)
    {
        if (is_array($cartItemData)) {
            $this->cartItemData = $cartItemData;
        }
    }

    protected function recalculateHash()
    {
        $idParts = array($this->productID);

        if ($this->parentId && floatval(0) !== $this->parentId) {
            $idParts[] = $this->parentId;
        }

        if ( ! empty($this->variationAttributes)) {
            $variationKey = '';
            foreach ($this->variationAttributes as $key => $value) {
                $variationKey .= trim($key) . trim($value);
            }
            $idParts[] = $variationKey;
        }

        if ( ! empty($this->cartItemData)) {
            $cartItemDataKey = '';
            foreach ($this->cartItemData as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = http_build_query($value);
                }
                $cartItemDataKey .= trim($key) . trim($value);

            }
            $idParts[] = $cartItemDataKey;
        }

        $this->hash = md5(implode('_', $idParts));
    }
}
