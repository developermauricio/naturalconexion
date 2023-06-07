<?php

namespace ADP\BaseVersion\Includes\PriceDisplay;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\ProductExtensions\ProductExtension;
use ADP\BaseVersion\Includes\SpecialStrategies\CompareStrategy;
use ADP\BaseVersion\Includes\SpecialStrategies\OverrideCentsStrategy;
use ADP\BaseVersion\Includes\WC\PriceFunctions;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use WC_Product;

defined('ABSPATH') or exit;

class ProcessedProductSimple
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CompareStrategy
     */
    protected $compareStrategy;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @var WC_Product
     */
    protected $product;

    /**
     * @var float
     */
    protected $qty;

    /**
     * @var float
     */
    protected $qtyAlreadyInCart;

    /**
     * @var CartItem[]
     */
    protected $cartItems;

    /**
     * @var FreeCartItem[]
     */
    protected $freeCartItems;

    /**
     * @var OverrideCentsStrategy
     */
    protected $overrideCentsStrategy;

    /**
     * @var float|null
     */
    protected $originalPriceToDisplay;

    /**
     * @param Context|WC_Product $contextOrProduct
     * @param WC_Product|array<int, CartItem> $productOrCartItems
     * @param array<int, CartItem>|null $deprecated
     */
    public function __construct($contextOrProduct, $productOrCartItems, $deprecated = null, $freeCartItems = [], $listOfFreeCartItemChoices = [])
    {
        $this->context         = adp_context();
        $this->compareStrategy = new CompareStrategy();
        $this->product         = $contextOrProduct instanceof WC_Product ? $contextOrProduct : $productOrCartItems;
        $this->cartItems       = is_array($productOrCartItems) ? $productOrCartItems : $deprecated;
        $this->freeCartItems   = $freeCartItems;
        $this->listOfFreeCartItemChoices = $listOfFreeCartItemChoices;

        $qty = floatval(0);
        foreach ($this->cartItems as $cartItem) {
            $qty += $cartItem->getQty();
        }
        $this->qty = $qty;

        $this->qtyAlreadyInCart = floatval(0);

        $this->priceFunctions = new PriceFunctions();

        $this->overrideCentsStrategy = new OverrideCentsStrategy();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param int|null $pos
     *
     * @return float|null
     */
    public function getOriginalPrice($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }

        return isset($item) ? $item->getOriginalPrice() : null;
    }

    public function setOriginalPriceToDisplay($price, $pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }

        $item->setOriginalPriceToDisplay((float)$price);
    }

    /**
     * @param int|null $pos
     *
     * @return float|null
     */
    public function getOriginalPriceToDisplay($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }

        return isset($item) ? $item->getOriginalPriceToDisplay() : null;
    }

    /**
     * @param int|null $pos
     *
     * @return float|null
     */
    public function getCalculatedPrice($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }
        $price = $item->getPrice();
        if ($this->context->getOption('show_unmodified_price_if_discounts_with_coupon')) {
            $totalAdjustments = array_sum(array_map(function ($amounts) {
                return array_sum($amounts);
            }, $item->getDiscounts()));
            $price            = $item->getOriginalPrice() - $totalAdjustments;
        }

        return $this->overrideCentsStrategy->maybeOverrideCentsForItem($price, $item);
    }

    /**
     * @param int|null $pos
     *
     * @return float|null
     */
    public function getPrice($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }

        $totalAdjustments = array_sum(array_map(function ($amounts) {
            return array_sum($amounts);
        }, $item->getDiscounts()));

        return ! $this->compareStrategy->floatsAreEqual($totalAdjustments,
            0) ? $this->overrideCentsStrategy->maybeOverrideCentsForItem($item->getPrice(),
            $item) : $item->getOriginalPrice();
    }

    /**
     * @param int|null $pos
     *
     * @return bool
     */
    public function areRulesApplied($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return false;
        }

        $totalAdjustments = array_sum(array_map(function ($amounts) {
            return array_sum($amounts);
        }, $item->getHistory()));

        return ! $this->compareStrategy->floatsAreEqual($totalAdjustments, 0);
    }

    /**
     * @return bool
     */
    public function areRulesAppliedAtAll()
    {
        foreach ($this->cartItems as $item) {
            $totalAdjustments = array_sum(array_map(function ($amounts) {
                return array_sum($amounts);
            }, $item->getHistory()));

            if ( ! $this->compareStrategy->floatsAreEqual($totalAdjustments, 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int|null $pos
     *
     * @return array<int, array<int,int>>
     */
    public function getHistory($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return array();
        }

        return $item->getHistory();
    }

    /**
     * @param int|null $pos
     *
     * @return array<int, array<int, int>>
     */
    public function getDiscounts($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return array();
        }

        return $item->getDiscounts();
    }

    /**
     * @param int|null $pos
     *
     * @return bool
     */
    public function isPriceChanged($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return false;
        }

        $totalAdjustments = array_sum(array_map(function ($amounts) {
            return array_sum($amounts);
        }, $item->getDiscounts()));

        return ! $this->compareStrategy->floatsAreEqual($totalAdjustments, 0);
    }

    /**
     * @param int|null $pos
     *
     * @return bool
     */
    public function isDiscounted($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return false;
        }

        $totalAdjustments = array_sum(array_map(function ($amounts) {
            return array_sum($amounts);
        }, $item->getDiscounts()));

        return $totalAdjustments > 0;
    }

    /**
     * @param int|null $pos
     *
     * @return bool
     */
    public function isAffectedByRangeDiscount($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return false;
        }

        $affected  = false;
        $discounts = $item->getObjDiscounts();
        foreach ($discounts as $discount) {
            if ($discount->isType($discount::SOURCE_SINGLE_ITEM_RANGE) || $discount->isType($discount::SOURCE_PACKAGE_RANGE)) {
                $affected = true;
                break;
            }
        }

        return $affected;
    }

    /**
     * @param int|null $pos
     *
     * @return int|null
     */
    public function getPos($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }

        return $item->getPos();
    }

    /**
     * @param int|null $pos
     *
     * @return WcCartItemFacade|null
     */
    public function getWcCartItem($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }

        return $item->getWcItem();
    }

    /**
     * @param int|null $pos
     *
     * @return float|null
     */
    public function getMinDiscountRangePrice($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return null;
        }

        return $item->getMinDiscountRangePrice();
    }

    /**
     * @param int|null $pos
     *
     * @return CartItem|null
     */
    protected function getItemByPos($pos = null)
    {
        $pos  = is_numeric($pos) ? intval($pos) : null;
        $item = null;

        if (is_null($pos)) {
            $item = reset($this->cartItems);
            $item = $item !== false ? $item : null;
        } else {
            $counter = floatval(0);
            foreach ($this->cartItems as $cartItem) {
                if ($counter < $pos && $pos <= ($counter + $cartItem->getQty())) {
                    $item = $cartItem;
                    break;
                }

                $counter += $cartItem->getQty();
            }
        }

        return $item;
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @return WC_Product
     */
    public function getProduct()
    {
        return $this->product;
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

    public function calculateSubtotal($qty = 1.0) {
        $subtotal = floatval(0);
        $qtyLeft = $qty;

        foreach ( $this->cartItems as $item ) {
            $requiredQty = min($qtyLeft, $item->getQty());

            $price = $item->getPrice();
            if ($this->context->getOption('show_unmodified_price_if_discounts_with_coupon')) {
                $totalAdjustments = array_sum(array_map(function ($amounts) {
                    return array_sum($amounts);
                }, $item->getDiscounts()));
                $price            = $item->getOriginalPrice() - $totalAdjustments;
            }

            $subtotal += $price * $requiredQty;
            $qtyLeft -= $requiredQty;
        }

        if ( $qtyLeft > 0 ) {
            if ( $item = reset($this->cartItems) ) {
                $subtotal += $item->getOriginalPrice() * $qtyLeft;
            }
        }

        return $subtotal;
    }

    /**
     * @param float $qty
     * @param bool $strikethrough
     *
     * @return string
     */
    protected function getHtml($qty = 1.0, $strikethrough = true)
    {
        $priceFunc = $this->priceFunctions;

        $clcPrice           = $this->getCalculatedPrice();
        $calcPriceToDisplay = $priceFunc->getPriceToDisplay($this->getProduct(), ['price' => $clcPrice, 'qty' => $qty]);

        if ($strikethrough) {
            $origPriceToDisplay = $priceFunc->getPriceToDisplay($this->getProduct(),
                array('price' => $this->getOriginalPrice(), 'qty' => $qty));

            if ($calcPriceToDisplay < $origPriceToDisplay) {
                $priceHtml = $priceFunc->formatSalePrice(
                        $origPriceToDisplay,
                        $calcPriceToDisplay
                    ) . $this->getProduct()->get_price_suffix($clcPrice);
            } else {
                $priceHtml = $priceFunc->format($calcPriceToDisplay) . $this->getProduct()->get_price_suffix($clcPrice);
            }
        } else {
            $priceHtml = $priceFunc->format($calcPriceToDisplay) . $this->getProduct()->get_price_suffix($clcPrice);
        }

        return $priceHtml;
    }

    /**
     * @return float
     */
    public function getQtyAlreadyInCart()
    {
        return $this->qtyAlreadyInCart;
    }

    /**
     * @param float $qtyAlreadyInCart
     */
    public function setQtyAlreadyInCart($qtyAlreadyInCart)
    {
        $this->qtyAlreadyInCart = $qtyAlreadyInCart;
    }

    /**
     * @param int|null $pos
     *
     * @return bool
     */
    public function isUsingCustomPrice($pos = null)
    {
        $item = $this->getItemByPos($pos);

        if ( ! isset($item)) {
            return false;
        }

        $product    = $item->getWcItem()->getProduct();
        $productExt = new ProductExtension($this->context, $product);

        return $productExt->getCustomPrice() !== null;
    }

    public function getFreeCartItems() {
        return $this->freeCartItems;
    }

    public function getListOfFreeCartItemChoices() {
        return $this->listOfFreeCartItemChoices;
    }
}
