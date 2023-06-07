<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Context\CurrencyController;
use ADP\HighLander\HighLanderShortcuts;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Multicurrency
 * Author: VillaTheme
 *
 * @see https://villatheme.com/extensions/woo-multi-currency/
 */
class VillaThemeMultiCurrencyCmp
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var WOOMULTI_CURRENCY_F_Data|WOOMULTI_CURRENCY_Data
     */
    protected $villaTheme;

    protected $price;

    public function __construct($deprecated = null)
    {
        $this->loadRequirements();
    }

    public function loadRequirements()
    {
        if ( ! did_action('plugins_loaded')) {
            _doing_it_wrong(__FUNCTION__, sprintf(__('%1$s should not be called earlier the %2$s action.',
                'advanced-dynamic-pricing-for-woocommerce'), 'load_requirements', 'plugins_loaded'), WC_ADP_VERSION);
        }

        if (class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $this->villaTheme = \WOOMULTI_CURRENCY_F_Data::get_ins();
        } elseif (class_exists('\WOOMULTI_CURRENCY_Data')) {
            $this->villaTheme = \WOOMULTI_CURRENCY_Data::get_ins();
        } else {
            $this->villaTheme = null;
        }
    }

    public function prepareHooks()
    {
        if ($this->isActive()) {
            HighLanderShortcuts::removeFilters(
                [
                    'woocommerce_package_rates' => [
                        ["WOOMULTI_CURRENCY_Frontend_Shipping", "woocommerce_package_rates"]
                    ],
                ]
            );
        }
    }

    public function customProductPriceCallback($price, $product, $currentCurrency)
    {

        if ($this->context->currencyController->getCurrentCurrency() != $currentCurrency) {
            return $price;
        }

        $product_id = $product->get_id();
        $changes    = $product->get_changes();
        if ($this->isFixedPriceEnabled() && (is_array($changes))) {
            $currentCurrency = $currentCurrency->getCode();// $this->villaTheme->get_current_currency();
            $product_id      = $product->get_id();
            $product_price   = wmc_adjust_fixed_price(json_decode(get_post_meta($product_id, '_regular_price_wmcp',
                true), true));
            $sale_price      = wmc_adjust_fixed_price(json_decode(get_post_meta($product_id, '_sale_price_wmcp', true),
                true));

            if (isset($product_price[$currentCurrency]) && ! $product->is_on_sale('edit')) {
                if ($product_price[$currentCurrency] > 0) {
                    return $product_price[$currentCurrency];
                }
            } elseif (isset($sale_price[$currentCurrency])) {
                if ($sale_price[$currentCurrency] > 0) {
                    return $sale_price[$currentCurrency];

                }
            }
        }

        return $price;
    }


    public function customProductRegularPriceCallback($price, $product, $currentCurrency)
    {
        if ( ! $price || $this->context->currencyController->getCurrentCurrency() != $currentCurrency) {
            return $price;
        }

        $product_id = $product->get_id();
        $changes    = $product->get_changes();
        if ($this->isFixedPriceEnabled() && (is_array($changes))) {
            $currentCurrency = $this->villaTheme->get_current_currency();
            $product_id      = $product->get_id();
            $product_price   = wmc_adjust_fixed_price(json_decode(get_post_meta($product_id, '_regular_price_wmcp',
                true), true));
            if (isset($product_price[$currentCurrency])) {
                if ($product_price[$currentCurrency] > 0) {
                    return $product_price[$currentCurrency];
                }
            }
        }

        return $price;
    }

    public function customProductSalePriceCallback($price, $product, $currentCurrency)
    {
        if ( ! $price || $this->context->currencyController->getCurrentCurrency() != $currentCurrency) {
            return $price;
        }

        $product_id = $product->get_id();
        $changes    = $product->get_changes();
        if ($this->isFixedPriceEnabled() && (is_array($changes))) {

            $currentCurrency = $this->villaTheme->get_current_currency();
            $product_id      = $product->get_id();
            $product_price   = wmc_adjust_fixed_price(json_decode(get_post_meta($product_id, '_sale_price_wmcp', true),
                true));
            if (isset($product_price[$currentCurrency])) {
                if ($product_price[$currentCurrency] > 0) {
                    return $product_price[$currentCurrency];
                }
            }
        }

        return $price;
    }

    public function isFixedPriceEnabled()
    {
        return (bool)$this->villaTheme->check_fixed_price();
    }

    public function isActive()
    {
        return ! is_null($this->villaTheme);
    }

    /**
     * @param string $currency
     *
     * @return array|null
     */
    protected function getCurrencyData($currency)
    {
        if ( ! $this->isActive()) {
            return null;
        }

        $currencyData = null;
        $currencies   = $this->villaTheme->get_list_currencies();

        if (isset($currencies[$currency]) && ! is_null($currencies[$currency])) {
            $currencyData = $currencies[$currency];
        }

        return $currencyData;
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getDefaultCurrency()
    {
        return $this->getCurrency($this->villaTheme->get_default_currency());
    }

    /**
     * @param string $code
     *
     * @return Currency|null
     * @throws \Exception
     */
    protected function getCurrency($code)
    {
        if ( ! $this->isActive()) {
            return null;
        }

        $currencyData = $this->getCurrencyData($code);

        if ( ! $currencyData) {
            return null;
        }

        return new Currency($code, get_woocommerce_currency_symbol($code), $currencyData['rate']);
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getCurrentCurrency()
    {
        return $this->getCurrency($this->villaTheme->get_current_currency());
    }

    public function modifyContext(Context $context)
    {
        $this->context = $context;

        $this->context->currencyController = new CurrencyController($this->context, $this->getDefaultCurrency());
        $this->context->currencyController->setCurrentCurrency($this->getCurrentCurrency());
        if ($this->isFixedPriceEnabled()) {
            $this->context->currencyController->setCustomProductPriceCallback(array(
                $this,
                'customProductPriceCallback'
            ));
            $this->context->currencyController->setCustomProductRegularPriceCallback(array(
                $this,
                'customProductRegularPriceCallback'
            ));
            $this->context->currencyController->setCustomProductSalePriceCallback(array(
                $this,
                'customProductSalePriceCallback'
            ));
        }
    }
}
