<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Context\CurrencyController;
use Aelia\WC\CurrencySwitcher\Definitions;
use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Currency Switcher for WooCommerce
 * Author: WP Wham
 *
 * @see https://wpwham.com/products/currency-switcher-for-woocommerce/
 */
class AlgWcCurrencySwitcherCmp
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Alg_WC_Currency_Switcher
     */
    protected $algWcCurrencySwitcher;

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
        $this->algWcCurrencySwitcher = (function_exists("alg_wc_currency_switcher_plugin")) ? alg_wc_currency_switcher_plugin() : null;
    }

    public function isActive()
    {
        return ! is_null($this->algWcCurrencySwitcher);
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getDefaultCurrency()
    {
        return $this->getCurrency(get_option('woocommerce_currency'));
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
        return alg_wc_cs_get_currency_exchange_rate($selected_currency);
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getCurrentCurrency()
    {
        return $this->getCurrency(alg_wc_currency_switcher_current_currency_code());
    }

    public function modifyContext(Context $context)
    {
        $this->context = $context;

        $this->context->currencyController = new CurrencyController($this->context, $this->getDefaultCurrency());
        $this->context->currencyController->setCurrentCurrency($this->getCurrentCurrency());
    }
}
