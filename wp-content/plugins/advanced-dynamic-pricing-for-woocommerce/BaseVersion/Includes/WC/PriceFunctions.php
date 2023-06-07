<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use WC_Product;
use WC_Tax;

defined('ABSPATH') or exit;

/**
 * todo add tests!
 *
 * @package ADP\BaseVersion\Includes\External\WC
 */
class PriceFunctions
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $currency
     *
     * @return string
     */
    public function getCurrencySymbol($currency = '')
    {
        if ($this->context->isUsingGlobalPriceSettings()) {
            return get_woocommerce_currency_symbol($currency);
        }

        $currencyController = $this->context->currencyController;

        if ( ! $currency) {
            $currency = $currencyController->getCurrentCurrency()->getCode();
        }

        $symbols = $currencyController->getCurrencySymbols();

        return isset($symbols[$currency]) ? $symbols[$currency] : '';
    }

    /**
     * @param float $price
     * @param array $args
     *
     * @return string
     */
    public function format($price, $args = array())
    {
        if ($this->context->isUsingGlobalPriceSettings()) {
            return wc_price($price, $args);
        }

        $priceSettings   = $this->context->priceSettings;
        $currentCurrency = $this->context->currencyController->getCurrentCurrency();

        $args = wp_parse_args(
            $args, array(
            'ex_tax_label'       => false,
            'currency'           => $currentCurrency->getCode(),
            'decimal_separator'  => $priceSettings->getDecimalSeparator(),
            'thousand_separator' => $priceSettings->getThousandSeparator(),
            'decimals'           => $priceSettings->getDecimals(),
            'price_format'       => $priceSettings->getPriceFormat(),
        ));

        $negative = $price < 0;
        $price    = floatval($negative ? $price * -1 : $price);
        $price    = number_format($price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator']);

        $formattedPrice = ($negative ? '-' : '') . sprintf($args['price_format'],
                '<span class="woocommerce-Price-currencySymbol">' . $this->getCurrencySymbol($args['currency']) . '</span>',
                $price);
        $return          = '<span class="woocommerce-Price-amount amount">' . $formattedPrice . '</span>';

        if ($args['ex_tax_label'] && $priceSettings->isTaxEnabled()) {
            $return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>'; // todo replace global WC()->countries->ex_tax_or_vat()
        }

        return $return;
    }

    /**
     * @param WC_Product $product WC_Product object.
     * @param array $args Optional arguments to pass product quantity and price.
     *
     * @return float|null
     * @see wc_get_price_including_tax()
     */
    public function getPriceIncludingTax($product, $args = array())
    {
        if ($this->context->isUsingGlobalPriceSettings()) {
            $this->forcePriceDecimals();
            $price = wc_get_price_including_tax($product, $args);
            $this->stopForcePriceDecimals();

            return $price !== '' ? (float)$price : null;
        }

        $args = wp_parse_args(
            $args, array(
            'qty'                             => '',
            'price'                           => '',
            'adjust_non_base_location_prices' => true,
        ));

        // always get product product price without hooks!
        $price = '' !== $args['price'] ? max(0.0, (float)$args['price']) : $product->get_price('edit');

        $qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;

        if ('' === $price) {
            return null;
        } elseif (empty($qty)) {
            return 0.0;
        }

        $priceSettings = $this->context->priceSettings;

        $linePrice   = $price * $qty;
        $returnPrice = (float)$linePrice;

        if ($product->is_taxable()) {
            if ( ! $priceSettings->isIncludeTax()) {
                $taxRates = WC_Tax::get_rates($product->get_tax_class());
                $taxes    = WC_Tax::calc_tax($linePrice, $taxRates, false);

                if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                    $taxesTotal = array_sum($taxes);
                } else {
                    $taxesTotal = array_sum(array_map('wc_round_tax_total', $taxes));
                }

                $returnPrice = round($linePrice + $taxesTotal, $priceSettings->getDecimals());
            } else {
                $taxRates     = WC_Tax::get_rates($product->get_tax_class());
                $baseTaxRates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));

                /**
                 * If the customer is excempt from VAT, remove the taxes here.
                 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
                 */
                if ( ! empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) { // @codingStandardsIgnoreLine.
                    $removeTaxes = $args['adjust_non_base_location_prices'] ? WC_Tax::calc_tax($linePrice,
                        $baseTaxRates, true) : WC_Tax::calc_tax($linePrice, $taxRates, true);

                    if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                        $removeTaxesTotal = array_sum($removeTaxes);
                    } else {
                        $removeTaxesTotal = array_sum(array_map('wc_round_tax_total', $removeTaxes));
                    }

                    $returnPrice = round($linePrice - $removeTaxesTotal, $priceSettings->getDecimals());

                    /**
                     * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
                     * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
                     * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
                     */
                } elseif ($taxRates !== $baseTaxRates && $args['adjust_non_base_location_prices']) {
                    $baseTaxes   = WC_Tax::calc_tax($linePrice, $baseTaxRates, true);
                    $moddedTaxes = WC_Tax::calc_tax($linePrice - array_sum($baseTaxes), $taxRates, false);

                    if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                        $baseTaxesTotal   = array_sum($baseTaxes);
                        $moddedTaxesTotal = array_sum($moddedTaxes);
                    } else {
                        $baseTaxesTotal   = array_sum(array_map('wc_round_tax_total', $baseTaxes));
                        $moddedTaxesTotal = array_sum(array_map('wc_round_tax_total', $moddedTaxes));
                    }

                    $returnPrice = round($linePrice - $baseTaxesTotal + $moddedTaxesTotal,
                        $priceSettings->getDecimals());
                }
            }
        }

        return $returnPrice;
    }

    /**
     * @param WC_Product $product WC_Product object.
     * @param array $args Optional arguments to pass product quantity and price.
     *
     * @return float|null
     * @see wc_get_price_excluding_tax()
     */
    public function getPriceExcludingTax($product, $args = array())
    {
        if ($this->context->isUsingGlobalPriceSettings()) {
            $this->forcePriceDecimals();
            $price = wc_get_price_excluding_tax($product, $args);
            $this->stopForcePriceDecimals();

            return $price !== '' ? (float)$price : null;
        }

        $args = wp_parse_args(
            $args, array(
            'qty'                             => '',
            'price'                           => '',
            'adjust_non_base_location_prices' => true,
        ));

        // always get product product price without hooks!
        $price = '' !== $args['price'] ? max(0.0, (float)$args['price']) : $product->get_price();

        $qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;

        if ('' === $price) {
            return null;
        } elseif (empty($qty)) {
            return 0.0;
        }

        $priceSettings = $this->context->priceSettings;

        $linePrice = (float)($price * $qty);

        if ($product->is_taxable() && $priceSettings->isIncludeTax()) {
            $taxRates     = WC_Tax::get_rates($product->get_tax_class());
            $baseTaxRates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
            $removeTaxes  = $args['adjust_non_base_location_prices']
                ? WC_Tax::calc_tax($linePrice, $baseTaxRates,true)
                : WC_Tax::calc_tax($linePrice, $taxRates, true);
            $returnPrice = $linePrice - (float)array_sum($removeTaxes); // Unrounded since we're dealing with tax inclusive prices. Matches logic in cart-totals class. @see adjust_non_base_location_price.
        } else {
            $returnPrice = $linePrice;
        }

        return $returnPrice;
    }

    /**
     * @param WC_Product $product WC_Product object.
     * @param array $args Optional arguments to pass product quantity and price.
     *
     * @return float|null
     * @see wc_get_price_to_display()
     */
    public function getPriceToDisplay($product, $args = array())
    {
        if ($this->context->isUsingGlobalPriceSettings()) {
            $this->forcePriceDecimals();
            $price = wc_get_price_to_display($product, $args);
            $this->stopForcePriceDecimals();

            return $price;
        }

        $args = wp_parse_args(
            $args, array(
            'qty'   => 1,

            // always get product product price without hooks!
            'price' => $product->get_price('edit'),
        ));

        $price = $args['price'];
        $qty   = $args['qty'];

        return 'incl' === $this->context->getTaxDisplayShopMode() ? $this->getPriceIncludingTax($product,
            array('qty' => $qty, 'price' => $price,)) : $this->getPriceExcludingTax($product,
            array('qty' => $qty, 'price' => $price,));
    }

    /**
     * @param string $from Price from.
     * @param string $to Price to.
     *
     * @return string
     * @see wc_format_price_range()
     */
    function formatRange($from, $to)
    {
        if ($this->context->isUsingGlobalPriceSettings()) {
            return wc_format_price_range($from, $to);
        }

        /* translators: 1: price from 2: price to */

        return sprintf(_x('%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce'),
            is_numeric($from) ? wc_price($from) : $from, is_numeric($to) ? wc_price($to) : $to);
    }

    /**
     * @param string $regularPrice Regular price.
     * @param string $salePrice Sale price.
     *
     * @return string
     * @see wc_format_sale_price()
     */
    function formatSalePrice($regularPrice, $salePrice)
    {
        if ($this->context->isUsingGlobalPriceSettings()) {
            return wc_format_sale_price($regularPrice, $salePrice);
        }

        $del = is_numeric($regularPrice) ? $this->format($regularPrice) : $regularPrice;
        $ins = is_numeric($salePrice) ? $this->format($salePrice) : $salePrice;

        return '<del>' . $del . '</del> <ins>' . $ins . '</ins>';
    }

    /**
     * @param ProcessedProductSimple $prod
     * @param float|null $price
     *
     * @return float
     */
    public function getProcProductPriceToDisplay(ProcessedProductSimple $prod, $price = null)
    {
        if (is_null($price)) {
            $price = $prod->getPrice();
        }

        return $this->getPriceToDisplay($prod->getProduct(), array('price' => $price, 'qty' => 1));
    }

    protected function forcePriceDecimals()
    {
        if ( ! $this->context->getOption('is_calculate_based_on_wc_precision')) {
            add_filter('wc_get_price_decimals', array($this, 'setPriceDecimals'), 10, 0);
        }
    }

    public function setPriceDecimals()
    {
        return $this->context->priceSettings->getDecimals() + 2;
    }

    protected function stopForcePriceDecimals()
    {
        remove_filter('wc_get_price_decimals', array($this, 'setPriceDecimals'), 10);
    }
}
