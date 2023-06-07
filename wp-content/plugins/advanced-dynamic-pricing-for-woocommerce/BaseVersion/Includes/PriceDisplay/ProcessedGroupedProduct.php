<?php

namespace ADP\BaseVersion\Includes\PriceDisplay;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\PriceFunctions;
use WC_Product;
use WC_Product_Grouped;
use WC_Product_Variable;

defined('ABSPATH') or exit;

class ProcessedGroupedProduct
{
    const KEY_LOWEST_PRICE_PROD = 'min_calculated_price';
    const KEY_HIGHEST_PRICE_PROD = 'max_calculated_price';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @var WC_Product_Variable
     */
    protected $product;

    /**
     * @var bool
     */
    protected $rulesApplied = false;

    /**
     * @var bool
     */
    protected $priceChanged = false;

    /**
     * @var bool
     */
    protected $isDiscounted = false;

    /**
     * @var array
     */
    protected $childSummary;

    /**
     * @var array
     */
    protected $children;

    /**
     * @var float
     */
    protected $qty;

    /**
     * @var bool
     */
    protected $usingCustomPrice = false;

    /**
     * @param WC_Product_Grouped $product
     * @param float $qty
     */
    public function __construct($product, $qty)
    {
        $this->context = adp_context();
        $this->product = $product;
        $this->qty = $qty;
        $this->rulesApplied = false;
        $this->priceChanged = false;
        $this->isDiscounted = false;
        $this->usingCustomPrice = false;
        $this->childSummary = array();

        $this->priceFunctions = new PriceFunctions();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return float|null
     */
    public function getLowestPrice()
    {
        if (isset($this->childSummary[self::KEY_LOWEST_PRICE_PROD])) {
            /** @var ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct $prod */
            $prod = $this->childSummary[self::KEY_LOWEST_PRICE_PROD];
            if ($prod instanceof ProcessedVariableProduct || $prod instanceof ProcessedGroupedProduct) {
                $price = $prod->getLowestPrice();
            } else {
                $price = $prod->getPrice();
            }
        } else {
            $price = null;
        }

        return $price;
    }

    /**
     * @return ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct|null
     */
    public function getLowestPriceProduct()
    {
        if (isset($this->childSummary[self::KEY_LOWEST_PRICE_PROD])) {
            /** @var ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct $prod */
            $prod = $this->childSummary[self::KEY_LOWEST_PRICE_PROD];
        } else {
            $prod = null;
        }

        return $prod;
    }

    /**
     * @return float|null
     */
    public function getHighestPrice()
    {
        if (isset($this->childSummary[self::KEY_HIGHEST_PRICE_PROD])) {
            /** @var ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct $prod */
            $prod = $this->childSummary[self::KEY_HIGHEST_PRICE_PROD];
            if ($prod instanceof ProcessedVariableProduct || $prod instanceof ProcessedGroupedProduct) {
                $price = $prod->getLowestPrice();
            } else {
                $price = $prod->getPrice();
            }
        } else {
            $price = null;
        }

        return $price;
    }

    /**
     * @return ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct|null
     */
    public function getHighestPriceProduct()
    {
        if (isset($this->childSummary[self::KEY_HIGHEST_PRICE_PROD])) {
            /** @var ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct $prod */
            $prod = $this->childSummary[self::KEY_HIGHEST_PRICE_PROD];
        } else {
            $prod = null;
        }

        return $prod;
    }

    /**
     * @param ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct $product
     */
    public function useChild($product)
    {
        if ($product->areRulesApplied()) {
            $this->rulesApplied = true;
            $this->priceChanged = $product->isPriceChanged();
        }

        if ($product->isDiscounted()) {
            $this->isDiscounted = true;
        }

        if ($product instanceof ProcessedVariableProduct || $product instanceof ProcessedGroupedProduct) {
            $childPrice = $product->getLowestPrice();
        } else {
            $childPrice = $product->getPrice();
        }

        if ($childPrice !== null) {
            if (is_null($this->getLowestPrice()) || ($childPrice < $this->getLowestPrice())) {
                $this->childSummary[self::KEY_LOWEST_PRICE_PROD] = $product;
            }

            if (is_null($this->getHighestPrice()) || ($this->getHighestPrice() < $childPrice)) {
                $this->childSummary[self::KEY_HIGHEST_PRICE_PROD] = $product;
            }
        }

        if ($product->isUsingCustomPrice()) {
            $this->usingCustomPrice = true;
        }

        $this->children[] = $product;
    }

    /**
     * @return bool
     */
    public function areRulesApplied()
    {
        return $this->rulesApplied;
    }

    /**
     * @return bool
     */
    public function isPriceChanged()
    {
        return $this->priceChanged;
    }

    /**
     * @return bool
     */
    public function isDiscounted()
    {
        return $this->isDiscounted;
    }

    /**
     * @return WC_Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param float $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * @return bool
     */
    public function isUsingCustomPrice()
    {
        return $this->usingCustomPrice;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

}
