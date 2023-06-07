<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\PriceFormatters;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\PriceDisplay\ProductPriceDisplay;
use ADP\BaseVersion\Includes\WC\PriceFunctions;

defined('ABSPATH') or exit;

class TotalProductPriceFormatter
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context   = adp_context();
        $this->formatter = new Formatter();

        $template = _x(
            htmlspecialchars_decode(
                $this->context->getOption(
                    "total_price_for_product_template",
                    "Total price : {{striked_total}}"
                )
            ),
            "Total price for product template",
            "advanced-dynamic-pricing-for-woocommerce"
        );

        $this->formatter->setTemplate($template);
        $this->priceFunctions = new PriceFunctions();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param \WC_Product $product
     * @param float $qty
     *
     * @return string
     */
    public function getHtmlAreRulesNotApplied($product, $qty)
    {
        $currencySwitcher = $this->context->currencyController;
        if ($currencySwitcher->isCurrencyChanged()) {
            $price = $currencySwitcher->getCurrentCurrencyProductPrice($product);
            $regularPrice = $currencySwitcher->getCurrentCurrencyProductRegularPrice($product);
        } else {
            $price = $product->get_price('edit');
            $regularPrice = $product->get_regular_price('edit');
        }

        if ( $regularPrice === "" ) {
            return "";
        }

        $isOnSale     = $product->is_on_sale('edit');
        $amountSaved = $regularPrice - $price;
        $percentageSaved = round($amountSaved / $regularPrice * 100, 2);

        $strikedTotal = $isOnSale ?
            $this->priceFunctions->formatSalePrice(
                $this->priceFunctions->getPriceToDisplay($product,
                    array('qty' => $qty, 'price' => $regularPrice)),
                $this->priceFunctions->getPriceToDisplay($product, array('qty' => $qty, 'price' => $price))
            ) :
            $this->priceFunctions->format(
                $this->priceFunctions->getPriceToDisplay($product,
                    array('qty' => $qty, 'price' => $regularPrice))
            );

        $total = $this->priceFunctions->format(
            $this->priceFunctions->getPriceToDisplay($product,
                array('qty' => $qty, 'price' => $regularPrice))
        );

        $replacements = array(
            'striked_total'    => $strikedTotal,
            'total'            => $total,
            'price_suffix'     => $product->get_price_suffix($price, $qty),
            'amount_saved'     => $this->priceFunctions->format($amountSaved),
            'percentage_saved' => $percentageSaved . '%',
        );

        $replacements = apply_filters(
            "adp_total_product_price_formatter_replacements",
            $replacements,
            null,
            $product,
            $this
        );


        return $this->formatter->applyReplacements($replacements);
    }

    /**
     * @param \WC_Product $product
     * @param float $qty
     *
     * @return string
     */
    public function getHtmlNotIsModifyNeeded($product, $qty)
    {
        $strikedTotal = $this->priceFunctions->format(
            $this->priceFunctions->getPriceToDisplay(
                $product,
                array('qty' => $qty)
            )
        );
        $total        = $strikedTotal;
        $amountSaved = $this->priceFunctions->format(0);
        $percentageSaved = '0%';

        $replacements = array(
            'striked_total'    => $strikedTotal,
            'total'            => $total,
            'price_suffix'     => $product->get_price_suffix($product->get_price('edit'), $qty),
            'amount_saved'     => $amountSaved,
            'percentage_saved' => $percentageSaved,
        );

        $replacements = apply_filters(
            "adp_total_product_price_formatter_replacements",
            $replacements,
            null,
            $product,
            $this
        );

        return $this->formatter->applyReplacements($replacements);
    }

    /**
     * @param ProcessedProductSimple $processedProduct
     *
     * @return string
     */
    public function getHtmlProcessedProductSimple($processedProduct)
    {
        $subtotal      = $processedProduct->calculateSubtotal($processedProduct->getQty());
        $qty           = $processedProduct->getQty();
        $origPrice     = $processedProduct->getOriginalPrice() * $qty;

        $amountSaved = $origPrice - $subtotal;
        if ( $origPrice === 0.0 ) {
            $percentageSaved = 0.0;
        } else {
            $percentageSaved = round($amountSaved / $origPrice * 100, 2);
        }

        /** @var ConcreteProductPriceHtml $prodPriceDisplay */
        $prodPriceDisplay = ProductPriceDisplay::create($this->context, $processedProduct);
        $prodPriceDisplay->withStriked($this->context->getOption('show_striked_prices_product_page', true));
        $strikedTotal  = $prodPriceDisplay->getFormattedSubtotalHtmlWithoutPriceSuffix($qty);

        $total         = $this->priceFunctions->format(
            $this->priceFunctions->getPriceToDisplay(
                $processedProduct->getProduct(),
                array(
                    'price' => $subtotal,
                )
            )
        );

        $replacements = array(
            'striked_total'    => $strikedTotal,
            'total'            => $total,
            'price_suffix'     => $processedProduct->getProduct()->get_price_suffix($subtotal, 1),
            'amount_saved'     => $this->priceFunctions->format($amountSaved),
            'percentage_saved' => $percentageSaved . '%',
        );

        $replacements = apply_filters(
            "adp_total_product_price_formatter_replacements",
            $replacements,
            $processedProduct,
            $processedProduct->getProduct(),
            $this
        );

        return $this->formatter->applyReplacements($replacements);
    }
}
