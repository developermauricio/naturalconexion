<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\PriceFormatters\DefaultFormatter;
use ADP\BaseVersion\Includes\PriceDisplay\PriceFormatters\DiscountRangeFormatter;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\SpecialStrategies\OverrideCentsStrategy;
use ADP\BaseVersion\Includes\WC\PriceFunctions;
use ADP\Factory;

defined('ABSPATH') or exit;

class SimpleProductPriceHtml implements ConcreteProductPriceHtml
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProcessedProductSimple
     */
    protected $processedProduct;

    /**
     * @var bool
     */
    protected $striked;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @var OverrideCentsStrategy
     */
    private $overrideCentsStrategy;

    /**
     * @param Context|ProcessedProductSimple $contextOrProcessedProduct
     * @param ProcessedProductSimple|null $deprecated
     */
    public function __construct($contextOrProcessedProduct, $deprecated = null)
    {
        $this->context          = adp_context();
        $this->processedProduct = $contextOrProcessedProduct instanceof ProcessedProductSimple ? $contextOrProcessedProduct : $deprecated;
        $this->priceFunctions   = new PriceFunctions();

        $this->striked = false;
        $this->overrideCentsStrategy = new OverrideCentsStrategy();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return float|null
     */
    public function getOriginalPrice()
    {
        return $this->priceFunctions->getPriceToDisplay(
            $this->processedProduct->getProduct(),
            array(
                'price' => $this->processedProduct->getOriginalPrice(),
            )
        );
    }

    /**
     * @return float|null
     */
    public function getDiscountedPrice()
    {
        return $this->priceFunctions->getPriceToDisplay(
            $this->processedProduct->getProduct(),
            array(
                'price' => $this->processedProduct->getCalculatedPrice(),
            )
        );
    }

    /**
     * @param float $qty
     *
     * @return float|null
     */
    public function getOriginalSubtotal($qty)
    {
        return $this->priceFunctions->getPriceToDisplay(
            $this->processedProduct->getProduct(),
            array(
                'price' => $this->processedProduct->getOriginalPrice(),
                'qty'   => $qty
            )
        );
    }

    /**
     * @param float $qty
     *
     * @return float|null
     */
    public function getDiscountedSubtotal($qty)
    {
        return $this->priceFunctions->getPriceToDisplay(
            $this->processedProduct->getProduct(),
            array(
                'price' => $this->processedProduct->calculateSubtotal($qty),
            )
        );
    }

    public function withStriked($striked)
    {
        $this->striked = (bool)$striked;
    }

    /**
     * @param string $priceHtml
     *
     * @return string
     */
    public function getFormattedPriceHtml($priceHtml)
    {
        $processedProduct       = $this->processedProduct;
        $discountRangeFormatter = new DiscountRangeFormatter($this->context);
        $discountRangeFormatter->setFormatterTemplate();
        $defaultFormatter = new DefaultFormatter($this->context);

        if ($discountRangeFormatter->isNeeded($processedProduct)) {
            return $discountRangeFormatter->getHtml($processedProduct);
        }

        if ($processedProduct->areRulesApplied()) {
            $priceHtml = $this->getHtml(1.0);
        }

        return $defaultFormatter->isNeeded($processedProduct)
            ? $defaultFormatter->getHtml($priceHtml, $processedProduct)
            : $priceHtml;
    }

    /**
     * @param string $priceHtml
     *
     * @return string
     */
    public function getPriceHtmlWithoutFormatting($priceHtml)
    {
        $processedProduct       = $this->processedProduct;

        if ($processedProduct->areRulesApplied()) {
            $priceHtml = $this->getHtml(1.0);
        }

        return $priceHtml;
    }

    public function getFormattedSubtotalHtml($qty)
    {
        return $this->getHtml($qty);
    }

    public function getFormattedSubtotalHtmlWithoutPriceSuffix($qty)
    {
        return $this->getHtml($qty, false);
    }

    /**
     * @param float $qty
     * @param bool $addPriceSuffix
     *
     * @return string
     */
    protected function getHtml($qty = 1.0, $addPriceSuffix = true)
    {
        $priceFunc        = $this->priceFunctions;
        $processedProduct = $this->processedProduct;
        $product          = $processedProduct->getProduct();

        $calcPrice = $this->overrideCentsStrategy->maybeOverrideCentsForProduct(
            $priceFunc->getPriceToDisplay($product, ['price' => $processedProduct->calculateSubtotal($qty)]),
            $product
        );

        $priceSuffix = $addPriceSuffix ? $product->get_price_suffix($processedProduct->getCalculatedPrice()) : "";
        $settings = $this->context->getSettings();
        $useRegularPrice = $settings->getOption('regular_price_for_striked_price');
        if ($this->striked) {
            $origPrice = $priceFunc->getPriceToDisplay(
                $product,
                array(
                    'price' => $useRegularPrice ? $product->get_regular_price('edit') : $processedProduct->getOriginalPriceToDisplay(),
                    'qty'   => $qty
                )
            );

            if ($calcPrice < $origPrice) {
                $priceHtml = $priceFunc->formatSalePrice($origPrice, $calcPrice) . $priceSuffix;
            } else {
                $priceHtml = $priceFunc->format($calcPrice) . $priceSuffix;
            }
        } else {
            $priceHtml = $priceFunc->format($calcPrice) . $priceSuffix;
        }

        return $priceHtml;
    }

    public function getPriceHtml()
    {
        $product = $this->processedProduct->getProduct();

        return Factory::callStaticMethod(
            'PriceDisplay_PriceDisplay',
            'processWithout',
            array($product, 'get_price_html')
        );
    }
}
