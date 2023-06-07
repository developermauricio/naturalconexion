<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\PriceFormatters\DiscountRangeFormatter;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;
use ADP\BaseVersion\Includes\PriceDisplay\ProductPriceDisplay;
use ADP\BaseVersion\Includes\WC\PriceFunctions;
use ADP\Factory;

defined('ABSPATH') or exit;

class VariableProductPriceHtml implements ConcreteProductPriceHtml
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProcessedVariableProduct
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
     * @param Context|ProcessedVariableProduct $contextOrProcessedProduct
     * @param ProcessedVariableProduct|null $deprecated
     */
    public function __construct($contextOrProcessedProduct, $deprecated = null)
    {
        $this->context          = adp_context();
        $this->processedProduct = $contextOrProcessedProduct instanceof ProcessedVariableProduct ? $contextOrProcessedProduct : $deprecated;
        $this->priceFunctions   = new PriceFunctions();

        $this->striked = false;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withStriked($striked)
    {
        $this->striked = (bool)$striked;
    }

    public function getFormattedPriceHtml($priceHtml)
    {
        $processedProduct       = $this->processedProduct;
        $discountRangeFormatter = new DiscountRangeFormatter($this->context);
        $discountRangeFormatter->setFormatterTemplate();

        if ($discountRangeFormatter->isNeeded($processedProduct)) {
            return $discountRangeFormatter->getHtml($processedProduct);
        }

        if ($processedProduct->areRulesApplied()) {
            $priceHtml = $this->getHtml(1.0);
        }

        return $priceHtml;
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

    public function getPriceHtml()
    {
        $product = $this->processedProduct->getProduct();

        return Factory::callStaticMethod(
            'PriceDisplay_PriceDisplay',
            'processWithout',
            array($product, 'get_price_html')
        );
    }

    /**
     * @param float $qty
     * @param bool $addPriceSuffix
     *
     * @return string
     */
    protected function getHtml($qty = 1.0, $addPriceSuffix = true)
    {
        $priceFunc           = $this->priceFunctions;
        $processedProduct    = $this->processedProduct;
        $lowestPriceProduct  = $processedProduct->getLowestPriceProduct();
        $highestPriceProduct = $processedProduct->getHighestPriceProduct();

        if (is_null($lowestPriceProduct)) {
            return "";
        }

        /** @var VariationProductPriceHtml $lowestPriceProductPriceDisplay */
        $lowestPriceProductPriceDisplay = ProductPriceDisplay::create($this->context, $lowestPriceProduct);
        $lowestPriceProductPriceDisplay->withStriked($this->striked);

        if (is_null($highestPriceProduct)) {
            $priceHtml = $lowestPriceProductPriceDisplay->getPriceHtml();
        } else {
            $lowestPriceToDisplay  = $priceFunc->getPriceToDisplay(
                $lowestPriceProduct->getProduct(),
                array(
                    'price' => $lowestPriceProduct->getPrice(),
                    'qty'   => $qty,
                )
            );
            $highestPriceToDisplay = $priceFunc->getPriceToDisplay($highestPriceProduct->getProduct(),
                array(
                    'price' => $highestPriceProduct->getPrice(),
                    'qty'   => $qty,
                )
            );

            $priceSuffix = $addPriceSuffix ? $lowestPriceProduct->getProduct()->get_price_suffix() : "";

            if ($lowestPriceToDisplay < $highestPriceToDisplay) {
                $priceHtml = $this->priceFunctions->formatRange(
                        $lowestPriceToDisplay,
                        $highestPriceToDisplay
                    ) . $priceSuffix;
            } else {
                $priceHtml = $lowestPriceProductPriceDisplay->getPriceHtmlWithoutFormatting(
                    $lowestPriceProductPriceDisplay->getPriceHtml()
                );
            }
        }

        return $priceHtml;
    }

    public function getLowestOriginalPrice()
    {
        $lowestOrigPriceProduct = $this->processedProduct->getLowestInitialPriceProduct();
        $settings = $this->context->getSettings();
        $useRegularPrice = $settings->getOption('regular_price_for_striked_price');

        if ( $lowestOrigPriceProduct === null ) {
            return null;
        }

        $product = $lowestOrigPriceProduct->getProduct();

        return $this->priceFunctions->getPriceToDisplay(
            $product,
            array(
                'price' => $useRegularPrice ? $product->get_regular_price('edit') : $lowestOrigPriceProduct->getOriginalPrice(),
            )
        );
    }

    public function getHighestOriginalPrice()
    {
        $highestOrigPriceProduct = $this->processedProduct->getHighestInitialPriceProduct();
        $settings = $this->context->getSettings();
        $useRegularPrice = $settings->getOption('regular_price_for_striked_price');

        if ( $highestOrigPriceProduct === null ) {
            return null;
        }

        $product = $highestOrigPriceProduct->getProduct();

        return $this->priceFunctions->getPriceToDisplay(
            $product,
            array(
                'price' => $useRegularPrice ? $product->get_regular_price('edit') : $highestOrigPriceProduct->getOriginalPrice(),
            )
        );
    }

    public function getLowestDiscountedPrice()
    {
        $lowestPriceProduct = $this->processedProduct->getLowestPriceProduct();

        if ( $lowestPriceProduct === null ) {
            return null;
        }

        return $this->priceFunctions->getPriceToDisplay(
            $lowestPriceProduct->getProduct(),
            array(
                'price' => $lowestPriceProduct->getCalculatedPrice(),
            )
        );
    }

    public function getHighestDiscountedPrice()
    {
        $highestPriceProduct = $this->processedProduct->getHighestPriceProduct();

        if ( $highestPriceProduct === null ) {
            return null;
        }

        return $this->priceFunctions->getPriceToDisplay(
            $highestPriceProduct->getProduct(),
            array(
                'price' => $highestPriceProduct->getCalculatedPrice(),
            )
        );
    }

    public function getLowestOriginalSubtotal($qty)
    {
        $lowestOrigPriceProduct = $this->processedProduct->getLowestInitialPriceProduct();
        $settings = $this->context->getSettings();
        $useRegularPrice = $settings->getOption('regular_price_for_striked_price');

        if ( $lowestOrigPriceProduct === null ) {
            return null;
        }

        $product = $lowestOrigPriceProduct->getProduct();

        return $this->priceFunctions->getPriceToDisplay(
            $product,
            array(
                'price' => $useRegularPrice ? $product->get_regular_price('edit') : $lowestOrigPriceProduct->getOriginalPrice(),
                'qty'   => $qty,
            )
        );
    }

    public function getHighestOriginalSubtotal($qty)
    {
        $highestOrigPriceProduct = $this->processedProduct->getHighestInitialPriceProduct();
        $settings = $this->context->getSettings();
        $useRegularPrice = $settings->getOption('regular_price_for_striked_price');

        if ( $highestOrigPriceProduct === null ) {
            return null;
        }

        $product = $highestOrigPriceProduct->getProduct();

        return $this->priceFunctions->getPriceToDisplay(
            $product,
            array(
                'price' => $useRegularPrice ? $product->get_regular_price('edit') : $highestOrigPriceProduct->getOriginalPrice(),
                'qty'   => $qty,
            )
        );
    }

    public function getLowestDiscountedSubtotal($qty)
    {
        $lowestPriceProduct = $this->processedProduct->getLowestPriceProduct();

        if ( $lowestPriceProduct === null ) {
            return null;
        }

        return $this->priceFunctions->getPriceToDisplay(
            $lowestPriceProduct->getProduct(),
            array(
                'price' => $lowestPriceProduct->getCalculatedPrice(),
                'qty'   => $qty,
            )
        );
    }

    public function getHighestDiscountedSubtotal($qty)
    {
        $highestPriceProduct = $this->processedProduct->getHighestPriceProduct();

        if ( $highestPriceProduct === null ) {
            return null;
        }

        return $this->priceFunctions->getPriceToDisplay(
            $highestPriceProduct->getProduct(),
            array(
                'price' => $highestPriceProduct->getCalculatedPrice(),
                'qty'   => $qty,
            )
        );
    }
}
