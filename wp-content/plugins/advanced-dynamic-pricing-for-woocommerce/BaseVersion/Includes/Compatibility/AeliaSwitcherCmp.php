<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Context\CurrencyController;
use ADP\HighLander\HighLanderShortcuts;
use Aelia\WC\CurrencySwitcher\Definitions;
use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Aelia Currency Switcher
 * Author: Aelia
 *
 * @see https://aelia.co/shop/currency-switcher-woocommerce/
 */
class AeliaSwitcherCmp
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var WC_Aelia_CurrencySwitcher
     */
    protected $aeliaCurrencySwitcher;

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
        $this->aeliaCurrencySwitcher = (class_exists("WC_Aelia_CurrencySwitcher") && isset($GLOBALS[WC_Aelia_CurrencySwitcher::$plugin_slug])) ? $GLOBALS[WC_Aelia_CurrencySwitcher::$plugin_slug] : null; //settings_controller
    }

    public function isActive()
    {
        return ! is_null($this->aeliaCurrencySwitcher);
    }

    public function prepareHooks()
    {
        if ($this->isActive()) {
            remove_filter(
                "woocommerce_package_rates",
                [
                    \Aelia\WC\CurrencySwitcher\WC27\WC_Aelia_CurrencyPrices_Manager::Instance(),
                    "woocommerce_package_rates"
                ],
                10
            );
        }
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getDefaultCurrency()
    {
        return $this->getCurrency($this->aeliaCurrencySwitcher->base_currency());
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

        return new Currency($code, get_woocommerce_currency_symbol($code), $this->getExchangeRate($code));
    }


    private function getExchangeRate($selected_currency)
    {
        $settingsController = $this->aeliaCurrencySwitcher->settings_controller();
        // Retrieve exchange rates from the configuration
        /** @var \Aelia\WC\Settings $settingsController */
        $exchange_rates = $settingsController->get_exchange_rates();

        $result = isset($exchange_rates[$selected_currency]) ? $exchange_rates[$selected_currency] : null;
        if (empty($result)) {
            $this->aeliaCurrencySwitcher->trigger_error(Definitions::ERR_INVALID_CURRENCY, E_USER_WARNING,
                array($selected_currency));
            $result = 1.0;
        }

        return $result;
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getCurrentCurrency()
    {
        return $this->getCurrency($this->aeliaCurrencySwitcher->get_selected_currency());
    }

    public function modifyContext(Context $context)
    {
        $this->context = $context;

        $this->context->currencyController = new CurrencyController($this->context, $this->getDefaultCurrency());
        $this->context->currencyController->setCurrentCurrency($this->getCurrentCurrency());

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

    /**
     * @param float $price
     * @param \WC_Product $product
     * @param Currency $currency
     *
     * @return float
     */
    public function customProductPriceCallback($price, \WC_Product $product, Currency $currency)
    {
        if ($currency == $this->context->currencyController->getCurrentCurrency()) {
            /** @var \Aelia\WC\CurrencySwitcher\WC36\WC_Aelia_CurrencyPrices_Manager $manager */
            $manager = \WC_Aelia_CurrencyPrices_Manager::instance();

            if ($product instanceof \WC_Product_Variation) {
                $regularPrices = $manager->get_variation_regular_prices($product);
                $salePrices    = $manager->get_variation_sale_prices($product);
            } else {
                $regularPrices = $manager->get_product_regular_prices($product);
                $salePrices    = $manager->get_product_sale_prices($product);
            }

            if (isset($salePrices[$currency->getCode()]) && $product->is_on_sale('edit')) {
                return $salePrices[$currency->getCode()];
            } elseif (isset($regularPrices[$currency->getCode()])) {
                return $regularPrices[$currency->getCode()];
            }
        }

        return $price;
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
        if ($currency == $this->context->currencyController->getCurrentCurrency()) {
            /** @var \Aelia\WC\CurrencySwitcher\WC36\WC_Aelia_CurrencyPrices_Manager $manager */
            $manager = \WC_Aelia_CurrencyPrices_Manager::instance();

            if ($product instanceof \WC_Product_Variation) {
                $prices = $manager->get_variation_sale_prices($product);
            } else {
                $prices = $manager->get_product_sale_prices($product);
            }

            if (isset($prices[$currency->getCode()])) {
                return $prices[$currency->getCode()];
            }
        }

        return $price;
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
        if ($currency == $this->context->currencyController->getCurrentCurrency()) {
            /** @var \Aelia\WC\CurrencySwitcher\WC36\WC_Aelia_CurrencyPrices_Manager $manager */
            $manager = \WC_Aelia_CurrencyPrices_Manager::instance();

            if ($product instanceof \WC_Product_Variation) {
                $prices = $manager->get_variation_regular_prices($product);
            } else {
                $prices = $manager->get_product_regular_prices($product);
            }

            if (isset($prices[$currency->getCode()])) {
                return $prices[$currency->getCode()];
            }
        }

        return $price;
    }


}
