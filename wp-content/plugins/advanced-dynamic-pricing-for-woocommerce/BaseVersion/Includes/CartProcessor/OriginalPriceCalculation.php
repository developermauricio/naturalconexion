<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Compatibility\TmExtraOptionsCmp;
use ADP\BaseVersion\Includes\ProductExtensions\ProductExtension;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use ADP\BaseVersion\Includes\Context;
use Exception;
use ReflectionClass;
use ReflectionException;
use WC_Product;

defined('ABSPATH') or exit;

class OriginalPriceCalculation
{
    const USE_SALE_PRICE = 'sale_price';
    const DISCOUNT_REGULAR_PRICE = 'discount_regular';
    const DISCOUNT_SALE_PRICE = 'discount_sale';
    const COMPARE_WC_AND_ADP = 'compare_discounted_and_sale';

    /**
     * @var bool
     */
    public $isReadOnlyPrice = false;

    /**
     * @var float
     */
    public $priceToAdjust;

    /**
     * @var float
     */
    public $trdPartyAdjustmentsAmount;

    /**
     * @var float
     */
    public $basePrice;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param null $deprecated
     *
     * @throws Exception
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
        $priceMod      = $this->context->getOption('discount_for_onsale');

        if ( ! $this->isValidPriceMod($priceMod)) {
            throw new Exception("Wrong price mod");
        }
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * TODO SHOULD GUARANTEE THAT PRICE WITHOUT TAX. NEED TO MAXIMIZE PRICE AND OVERRIDE CENTS
     *
     * @param WcCartItemFacade $wcCartItem
     *
     * @return OriginalPriceCalculation
     */
    public function process(WcCartItemFacade $wcCartItem)
    {
        $context = $this->context;

        $priceMod             = $context->getOption('discount_for_onsale');
        $prodPropsWithFilters = $this->context->getOption('initial_price_context') === 'view';

        $product = $wcCartItem->getProduct();
        $product = apply_filters('adp_get_original_product_from_cart', $product, $wcCartItem);
        /** @var $product WC_Product */

        $this->isReadOnlyPrice = false;

        $productExt = new ProductExtension($this->context, $product);

        if ($productExt->getCustomPrice() !== null ) {
            $this->priceToAdjust             = $product->get_price('edit');
            $this->trdPartyAdjustmentsAmount = 0.0;
            $this->basePrice                 = $this->priceToAdjust;
        } elseif (!(($tmCmp = new TmExtraOptionsCmp()) && $tmCmp->isActive()) && $wcCartItem->getInitialCustomPrice()) {
            $this->priceToAdjust = $this->getPrice($product, $wcCartItem, $prodPropsWithFilters, true);

            try {
                $reflection = new ReflectionClass($product);
                $property   = $reflection->getProperty('changes');
                $property->setAccessible(true);
                $changes = $property->getValue($product);
                $property->setValue($product, array());
            } catch (ReflectionException $exception) {
                $property = null;
            }

            $cleanPrice                      = $this->getPrice($product, $wcCartItem, false, false);
            $this->trdPartyAdjustmentsAmount = $this->priceToAdjust - $cleanPrice;
            $this->basePrice                 = $cleanPrice;

            if (isset($property, $changes)) {
                $property->setValue($product, $changes);
            }
        } elseif ($this->getIsOnSale($product, $wcCartItem, $prodPropsWithFilters, true)) {
            if ($priceMod === self::USE_SALE_PRICE || $priceMod === self::DISCOUNT_SALE_PRICE) {
                $this->priceToAdjust             = $this->getSalePrice($product, $wcCartItem, $prodPropsWithFilters,
                    true);
                $cleanPrice                      = $this->getSalePrice($product, $wcCartItem, false, false);
                $this->trdPartyAdjustmentsAmount = $this->priceToAdjust - $cleanPrice;
                $this->basePrice                 = $cleanPrice;

                if ($priceMod === self::USE_SALE_PRICE) {
                    $this->isReadOnlyPrice = true;
                }

            } elseif ($priceMod === self::DISCOUNT_REGULAR_PRICE || $priceMod === self::COMPARE_WC_AND_ADP) {
                $this->priceToAdjust             = $this->getRegularPrice($product, $wcCartItem, $prodPropsWithFilters,
                    true);
                $cleanPrice                      = $this->getRegularPrice($product, $wcCartItem, false, false);
                $this->trdPartyAdjustmentsAmount = $this->priceToAdjust - $cleanPrice;
                $this->basePrice                 = $cleanPrice;
            }
        } else {
            $this->priceToAdjust             = $this->getPrice($product, $wcCartItem, $prodPropsWithFilters, true);
            $cleanPrice                      = $this->getPrice($product, $wcCartItem, false, false);
            $this->trdPartyAdjustmentsAmount = $this->priceToAdjust - $cleanPrice;
            $this->basePrice                 = $cleanPrice;
        }

        return $this;
    }

    /**
     * @param string $priceMod
     *
     * @return bool
     */
    private function isValidPriceMod($priceMod)
    {
        return in_array($priceMod, array(
            self::USE_SALE_PRICE,
            self::DISCOUNT_SALE_PRICE,
            self::DISCOUNT_REGULAR_PRICE,
            self::COMPARE_WC_AND_ADP
        ));
    }

    /**
     * @param WC_Product $product
     * @param WcCartItemFacade $wcCartItem
     * @param bool $withWcFilters
     * @param bool $withAdpFilters
     *
     * @return bool
     */
    protected function getIsOnSale(
        $product,
        $wcCartItem,
        $withWcFilters,
        $withAdpFilters
    ) {
        $result = $product->is_on_sale($withWcFilters ? 'view' : 'edit');
        if ($withAdpFilters) {
            $result = apply_filters("adp_get_original_product_is_on_sale_from_cart", $result, $product, $wcCartItem);
        }

        return boolval($result);
    }

    /**
     * @param WC_Product $product
     * @param WcCartItemFacade $wcCartItem
     * @param bool $withWcFilters
     * @param bool $withAdpFilters
     *
     * @return float|null
     */
    protected function getRegularPrice(
        $product,
        $wcCartItem,
        $withWcFilters,
        $withAdpFilters
    ) {
        $result = $product->get_regular_price($withWcFilters ? 'view' : 'edit');
        if ($withAdpFilters) {
            $result = apply_filters("adp_get_original_product_regular_price_from_cart", $result, $product,
                $wcCartItem);
        }

        return '' !== $result ? floatval($result) : null;
    }

    /**
     * @param WC_Product $product
     * @param WcCartItemFacade $wcCartItem
     * @param bool $withWcFilters
     * @param bool $withAdpFilters
     *
     * @return float|null
     */
    protected function getSalePrice(
        $product,
        $wcCartItem,
        $withWcFilters,
        $withAdpFilters
    ) {
        $result = null;

        /** Always remember about scheduled WC sales */
        if ($product->is_on_sale($withWcFilters ? 'view' : 'edit')) {
            $result = $product->get_sale_price($withWcFilters ? 'view' : 'edit');
        }

        if ($withAdpFilters) {
            $result = apply_filters("adp_get_original_product_sale_price_from_cart", $result, $product, $wcCartItem);
        }

        return '' !== $result ? floatval($result) : null;
    }

    /**
     * @param WC_Product $product
     * @param WcCartItemFacade $wcCartItem
     * @param bool $withWcFilters
     * @param bool $withAdpFilters
     *
     * @return float|null
     */
    protected function getPrice(
        $product,
        $wcCartItem,
        $withWcFilters,
        $withAdpFilters
    ) {
        $result = $product->get_price($withWcFilters ? 'view' : 'edit');
        if ($withAdpFilters) {
            $result = apply_filters("adp_get_original_product_initial_price_from_cart", $result, $product,
                $wcCartItem);
        }

        return '' !== $result ? floatval($result) : null;
    }
}
