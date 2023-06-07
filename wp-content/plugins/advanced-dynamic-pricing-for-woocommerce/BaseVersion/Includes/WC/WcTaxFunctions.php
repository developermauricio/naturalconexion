<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Context;
use Automattic\WooCommerce\Utilities\NumberUtil;

class WcTaxFunctions
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Property for caching, nothing more
     *
     * @var array
     */
    protected $itemTaxRates;

    public function __construct()
    {
        $this->context = adp_context();
        $this->itemTaxRates = [];
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Adjust if the store taxes are not displayed how they are stored.
     * Kicks in when prices excluding tax are displayed including tax.
     *
     * @param float $price
     * @return float
     */
    public function getBasePrice(float $price): float
    {
        if ($this->context->getIsTaxEnabled() && !$this->context->getIsPricesIncludeTax()) {
            $taxClass = apply_filters('woocommerce_price_filter_widget_tax_class', ''); // Uses standard tax class.
            $taxRates = \WC_Tax::get_rates($taxClass);

            if ($taxRates) {
                $price -= \WC_Tax::get_tax_total(\WC_Tax::calc_inclusive_tax($price, $taxRates));
            }
        }

        return $price;
    }

    public function getBaseProductPrice(\WC_Product $product, \WC_Customer $customer): float
    {
        $price = (float)$product->get_price('edit');
        $productIsTaxable = 'taxable' === $product->get_tax_status();
        if ($this->context->getIsTaxEnabled() && !$this->context->getIsPricesIncludeTax() && $productIsTaxable) {
            if (apply_filters('woocommerce_adjust_non_base_location_prices', true)) {
                $base_tax_rates = \WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
            } else {
                /**
                 * If we want all customers to pay the same price on this store, we should not remove base taxes from a VAT exempt user's price,
                 * but just the relevant tax rate. See issue #20911.
                 */
                $base_tax_rates = $this->getProductTaxRates($product, $customer);
            }


            // Work out a new base price without the shop's base tax.
            $taxes = \WC_Tax::calc_tax($price, $base_tax_rates, true);

            // Now we have a new item price (excluding TAX).
            $price = NumberUtil::round($price - array_sum($taxes));
        }

        return $price;
    }

    protected function getProductTaxRates(\WC_Product $product, \WC_Customer $customer): array
    {
        if (!$this->context->getIsTaxEnabled()) {
            return array();
        }

        $taxClass = $product->get_tax_class();
        $itemTaxRates = $this->itemTaxRates[$taxClass] ?? $this->itemTaxRates[$taxClass] = \WC_Tax::get_rates(
                $product->get_tax_class(),
                $customer
            );

        // Allow plugins to filter item tax rates.
//        return apply_filters( 'woocommerce_cart_totals_get_item_tax_rates', $itemTaxRates, $item, $this->cart );
        return $itemTaxRates;
    }
}
