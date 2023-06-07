<?php

namespace ADP\BaseVersion\Includes\PriceDisplay;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\PriceFunctions;
use WC_Product;
use WC_Product_Grouped;
use WC_Product_Variable;

defined('ABSPATH') or exit;

class ProcessedVariableProduct
{
    const KEY_LOWEST_PRICE_PROD = 'min_calculated_price';
    const KEY_LOWEST_RANGE_PRICE_PROD = 'min_range_calculated_price';
    const KEY_HIGHEST_PRICE_PROD = 'max_calculated_price';
    const KEY_LOWEST_INITIAL_PRICE_PROD = 'min_initial_price';
    const KEY_HIGHEST_INITIAL_PRICE_PROD = 'max_initial_price';

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
     * @var float
     */
    protected $isFullyAffectedByRangeDiscount;

    /**
     * @var bool
     */
    protected $usingCustomPrice = false;

    /**
     * @param Context|WC_Product_Variable|WC_Product_Grouped $contextOrProduct
     * @param WC_Product_Variable|WC_Product_Grouped|float $productOrQty
     * @param float|null $deprecated
     */
    public function __construct($contextOrProduct, $productOrQty, $deprecated = null)
    {
        $this->context = adp_context();

        if ($contextOrProduct instanceof WC_Product_Variable || $contextOrProduct instanceof WC_Product_Grouped) {
            $this->product = $contextOrProduct;
        } else {
            $this->product = $productOrQty;
        }

        $this->qty                            = is_float($productOrQty) ? $productOrQty : $deprecated;
        $this->rulesApplied                   = false;
        $this->priceChanged                   = false;
        $this->isDiscounted                   = false;
        $this->usingCustomPrice               = false;
        $this->isFullyAffectedByRangeDiscount = null;
        $this->childSummary                   = array();

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
            /** @var ProcessedProductSimple $prod */
            $prod  = $this->childSummary[self::KEY_LOWEST_PRICE_PROD];
            $price = $prod->getPrice();
        } else {
            $price = null;
        }

        return $price;
    }

    /**
     * @return ProcessedProductSimple|null
     */
    public function getLowestPriceProduct()
    {
        if (isset($this->childSummary[self::KEY_LOWEST_PRICE_PROD])) {
            /** @var ProcessedProductSimple $prod */
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
            /** @var ProcessedProductSimple $prod */
            $prod  = $this->childSummary[self::KEY_HIGHEST_PRICE_PROD];
            $price = $prod->getPrice();
        } else {
            $price = null;
        }

        return $price;
    }

    /**
     * @return ProcessedProductSimple|null
     */
    public function getHighestPriceProduct()
    {
        if (isset($this->childSummary[self::KEY_HIGHEST_PRICE_PROD])) {
            /** @var ProcessedProductSimple $prod */
            $prod = $this->childSummary[self::KEY_HIGHEST_PRICE_PROD];
        } else {
            $prod = null;
        }

        return $prod;
    }

    /**
     * @return ProcessedProductSimple|null
     */
    public function getLowestRangeDiscountPriceProduct()
    {
        if (isset($this->childSummary[self::KEY_LOWEST_RANGE_PRICE_PROD])) {
            /** @var ProcessedProductSimple $prod */
            $prod = $this->childSummary[self::KEY_LOWEST_RANGE_PRICE_PROD];
        } else {
            $prod = null;
        }

        return $prod;
    }

    /**
     * @return ProcessedProductSimple|null
     */
    public function getLowestInitialPriceProduct()
    {
        if (isset($this->childSummary[self::KEY_LOWEST_INITIAL_PRICE_PROD])) {
            /** @var ProcessedProductSimple $prod */
            $prod = $this->childSummary[self::KEY_LOWEST_INITIAL_PRICE_PROD];
        } else {
            $prod = null;
        }

        return $prod;
    }

    /**
     * @return ProcessedProductSimple|null
     */
    public function getHighestInitialPriceProduct()
    {
        if (isset($this->childSummary[self::KEY_HIGHEST_INITIAL_PRICE_PROD])) {
            /** @var ProcessedProductSimple $prod */
            $prod = $this->childSummary[self::KEY_HIGHEST_INITIAL_PRICE_PROD];
        } else {
            $prod = null;
        }

        return $prod;
    }

    /**
     * @param ProcessedProductSimple $product
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

        $childPrice = $product->getPrice();

        if (is_null($this->getLowestPrice()) || ($childPrice < $this->getLowestPrice())) {
            $this->childSummary[self::KEY_LOWEST_PRICE_PROD] = $product;
        }

        if (is_null($this->getHighestPrice()) || ($this->getHighestPrice() < $childPrice)) {
            $this->childSummary[self::KEY_HIGHEST_PRICE_PROD] = $product;
        }

        if ($product->isAffectedByRangeDiscount()) {
            if (isset($this->childSummary[self::KEY_LOWEST_RANGE_PRICE_PROD])) {
                /** @var ProcessedProductSimple $prod */
                $prod             = $this->childSummary[self::KEY_LOWEST_RANGE_PRICE_PROD];
                $lowestRangePrice = $prod->getMinDiscountRangePrice();
            } else {
                $lowestRangePrice = null;
            }

            if (is_null($lowestRangePrice) || ($childPrice < $lowestRangePrice)) {
                $this->childSummary[self::KEY_LOWEST_RANGE_PRICE_PROD] = $product;
            }

            if (is_null($this->isFullyAffectedByRangeDiscount)) {
                $this->isFullyAffectedByRangeDiscount = true;
            }
        } else {
            $this->isFullyAffectedByRangeDiscount = false;
        }

        $originalChildPrice  = $product->getOriginalPrice();
        $lowestInitialPrice  = $this->getLowestInitialPriceProduct() ? $this->getLowestInitialPriceProduct()->getOriginalPrice() : null;
        $highestInitialPrice = $this->getHighestInitialPriceProduct() ? $this->getHighestInitialPriceProduct()->getOriginalPrice() : null;

        if (is_null($lowestInitialPrice) || ($originalChildPrice < $lowestInitialPrice)) {
            $this->childSummary[self::KEY_LOWEST_INITIAL_PRICE_PROD] = $product;
        }

        if (is_null($highestInitialPrice) || ($highestInitialPrice < $originalChildPrice)) {
            $this->childSummary[self::KEY_HIGHEST_INITIAL_PRICE_PROD] = $product;
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
     * @deprecated use ProductPriceDisplay instead
     *
     * @param bool $strikethrough
     *
     * @return string
     */
    public function getPriceHtml($strikethrough = true)
    {
        return $this->getHtml(1, $strikethrough);
    }

    /**
     * @deprecated use ProductPriceDisplay instead
     *
     * @param bool $strikethrough
     *
     * @return string
     */
    public function getSubtotalHtml($strikethrough = true)
    {
        return $this->getHtml($this->getQty(), $strikethrough);
    }

    /**
     * @param float $qty
     * @param bool $strikethrough
     *
     * @return string
     */
    protected function getHtml($qty, $strikethrough = true)
    {
        $priceFunc           = $this->priceFunctions;
        $lowestPriceProduct  = $this->getLowestPriceProduct();
        $highestPriceProduct = $this->getHighestPriceProduct();

        if (is_null($lowestPriceProduct)) {
            return "";
        }

        if (is_null($highestPriceProduct)) {
            $priceHtml = $lowestPriceProduct->getPriceHtml($strikethrough);
        } else {
            $lowestPriceToDisplay  = $priceFunc->getPriceToDisplay($lowestPriceProduct->getProduct(),
                array(
                    'price' => $lowestPriceProduct->getPrice(),
                    'qty'   => $qty
                ));
            $highestPriceToDisplay = $priceFunc->getPriceToDisplay($highestPriceProduct->getProduct(),
                array(
                    'price' => $highestPriceProduct->getPrice(),
                    'qty'   => $qty
                ));

            if ($lowestPriceToDisplay < $highestPriceToDisplay) {
                $priceHtml = $this->priceFunctions->formatRange($lowestPriceToDisplay,
                        $highestPriceToDisplay) . $this->getProduct()->get_price_suffix();
            } else {
                $priceHtml = $lowestPriceProduct->getPriceHtml($strikethrough);
            }
        }

        return $priceHtml;
    }

    /**
     * @return bool
     */
    public function isAffectedByRangeDiscount()
    {
        return boolval(apply_filters('wdp_is_processed_variable_product_fully_affected_by_range_discount',
            $this->isFullyAffectedByRangeDiscount));
    }

    /**
     * @return bool
     */
    public function isUsingCustomPrice()
    {
        return $this->usingCustomPrice;
    }

    public function getPrice() {
        return $this->getLowestPrice();
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }
}
