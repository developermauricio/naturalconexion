<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Context\CurrencyController;
use ADP\BaseVersion\Includes\Core\Rule\Internationalization\RuleTranslator;
use ADP\BaseVersion\Includes\Core\Rule\Rule;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WooCommerce Price Based on Country
 * Author: Oscar Gare
 *
 * @see https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/
 */
class PriceBasedOnCountryCmp
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @return bool
     */
    public function isActive()
    {
        return defined("WCPBC_PLUGIN_FILE");
    }

    public function modifyContext(Context $context)
    {
        $this->context = $context;
        $context->currencyController = new CurrencyController($context, $this->getDefaultCurrency());
        $context->currencyController->setCurrentCurrency($this->getCurrentCurrency());

        $context->currencyController->setCustomProductPriceCallback(array(
            $this,
            'customProductPriceCallback'
        ));
        $context->currencyController->setCustomProductRegularPriceCallback(array(
            $this,
            'customProductRegularPriceCallback'
        ));
        $context->currencyController->setCustomProductSalePriceCallback(array(
            $this,
            'customProductSalePriceCallback'
        ));
    }

    /**
     * @param Rule $rule
     *
     * @return Rule
     */
    public function changeRuleCurrency($rule): Rule
    {
        if (!function_exists("wcpbc_get_zone_by_country")) {
            return $rule;
        }

        if (!($zone = wcpbc_get_zone_by_country())) {
            return $rule;
        }

        if ($rate = $zone->get_real_exchange_rate()) {
            $rule = RuleTranslator::setCurrency($rule, $rate);
        }

        return $rule;
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getDefaultCurrency()
    {
        return new Currency(wcpbc_get_base_currency(), get_woocommerce_currency_symbol( wcpbc_get_base_currency() ));
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getCurrentCurrency()
    {
        $currentCurrency = WCPBC()->current_zone ? WCPBC()->current_zone->get_currency() : wcpbc_get_base_currency();
        $exchangeRate = WCPBC()->current_zone ? WCPBC()->current_zone->get_exchange_rate() : 1.0;

        return new Currency($currentCurrency, get_woocommerce_currency_symbol($currentCurrency), $exchangeRate);
    }

    /**
     * @param float $price
     * @param \WC_Product $product
     * @param Currency $currency
     *
     * @return float
     */
    public function customProductPriceCallback($price, \WC_Product $product, Currency $currency)
    {
        if ( ! wcpbc_the_zone() ) {
            return $price;
        }

        $price = $this->getProductProtectedProp($product, 'price');
        $price = wcpbc_the_zone()->get_price_prop($product, $price, "_price");

        return $price !== "" ? self::stringToFloat($this->context, $price) : null;
    }

    /**
     * @param float $price
     * @param \WC_Product $product
     * @param Currency $currency
     *
     * @return float
     */
    public function customProductSalePriceCallback($price, \WC_Product $product, Currency $currency)
    {
        if ( ! wcpbc_the_zone() ) {
            return $price;
        }

        $price = $this->getProductProtectedProp($product, 'regular_price');
        $price = wcpbc_the_zone()->get_price_prop($product, $price, "_sale_price");

        return $price !== "" ? self::stringToFloat($this->context, $price) : null;
    }

    /**
     * @param float $price
     * @param \WC_Product $product
     * @param Currency $currency
     *
     * @return float
     */
    public function customProductRegularPriceCallback($price, \WC_Product $product, Currency $currency)
    {
        if ( ! wcpbc_the_zone() ) {
            return $price;
        }

        $price = $this->getProductProtectedProp($product, 'sale_price');
        $price = wcpbc_the_zone()->get_price_prop($product, $price, "_regular_price");

        return $price !== "" ? self::stringToFloat($this->context, $price) : null;
    }

    protected static function stringToFloat($context, $value)
    {
        if (is_string($value)) {
            $value = str_replace($context->priceSettings->getThousandSeparator(), "", $value);
            $value = str_replace($context->priceSettings->getDecimalSeparator(), ".", $value);
        }

        return (float)$value;
    }

    /**
     * @param \WC_Product $product
     * @param string $prop
     *
     * @return mixed|null
     */
    protected function getProductProtectedProp($product, $prop)
    {
        try {
            $reflection = new \ReflectionClass($product);
            $property   = $reflection->getProperty('data');
            $property->setAccessible(true);
        } catch (\ReflectionException $e) {
            $property = null;
        }

        return $property->getValue($product)[$prop] ?? null;
    }
}
