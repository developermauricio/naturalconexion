<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Context\CurrencyController;
use WC_Subscriptions_Product;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WOOCS - WooCommerce Currency Switcher
 * Author: realmag777
 *
 * @see https://wordpress.org/plugins/woocommerce-currency-switcher/
 */
class WoocsCmp
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \WOOCS|null
     */
    protected $woocs;

    public function __construct($deprecated = null)
    {
        $this->loadRequirements();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return ! is_null($this->woocs) && ($this->woocs instanceof \WOOCS);
    }

    public function loadRequirements()
    {
        if ( ! did_action('plugins_loaded')) {
            _doing_it_wrong(__FUNCTION__, sprintf(__('%1$s should not be called earlier the %2$s action.',
                'advanced-dynamic-pricing-for-woocommerce'), 'load_requirements', 'plugins_loaded'), WC_ADP_VERSION);
        }

        $this->woocs = isset($GLOBALS['WOOCS']) ? $GLOBALS['WOOCS'] : null;
    }

    public function prepareHooks()
    {
        if ($this->isActive()) {
            remove_action('woocommerce_package_rates', array($this->woocs, 'woocommerce_package_rates'), 9999);
        }
    }

    public function modifyContext(Context $context)
    {
        $this->context = $context;
        $this->context->currencyController = new CurrencyController($this->context, $this->getDefaultCurrency());

        if ($this->woocs->is_multiple_allowed) {
            $this->context->currencyController->setCurrentCurrency($this->getCurrentCurrency());
        }

        $this->context->priceSettings->setDecimals(wc_get_price_decimals());

        if ($currencyData = $this->getCurrencyData($this->woocs->current_currency)) {
            $priceSettings = $this->context->priceSettings;

            switch ($currencyData['position']) {
                case 'left':
                    $format = $priceSettings::FORMAT_LEFT;
                    break;
                case 'right':
                    $format = $priceSettings::FORMAT_RIGHT;
                    break;
                case 'left_space':
                    $format = $priceSettings::FORMAT_LEFT_SPACE;
                    break;
                case 'right_space':
                    $format = $priceSettings::FORMAT_RIGHT_SPACE;
                    break;
                default:
                    $format = null;
                    break;
            }
            if (isset($currencyData['decimals'])) {
                $priceSettings->setDecimals($currencyData['decimals']);
            }
            $priceSettings->setPriceFormat($format);
        }

        if ($this->woocs->is_fixed_enabled) {
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

    /**
     * @param float $price
     * @param \WC_Product $product
     * @param Currency $currency
     *
     * @return float
     */
    public function customProductPriceCallback($price, \WC_Product $product, Currency $currency)
    {
        $priceSettings = $this->context->priceSettings;
        if ($currency == $this->context->currencyController->getCurrentCurrency()) {
            $tmp_val = $this->woocs->_get_product_fixed_price($product, null, $price, $priceSettings->getDecimals());

            return $tmp_val === -1 ? $price : $tmp_val;
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
        $priceSettings = $this->context->priceSettings;
        if ($currency == $this->context->currencyController->getCurrentCurrency()) {
            $tmp_val = $this->woocs->_get_product_fixed_price($product, null, $price, $priceSettings->getDecimals(),
                'sale');

            return $tmp_val === -1 ? $price : $tmp_val;
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
        $priceSettings = $this->context->priceSettings;
        if ($currency == $this->context->currencyController->getCurrentCurrency()) {
            $tmp_val = $this->woocs->_get_product_fixed_price($product, null, $price, $priceSettings->getDecimals(),
                'regular');

            return $tmp_val === -1 ? $price : $tmp_val;
        }

        return $price;
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getDefaultCurrency()
    {
        return $this->getCurrency($this->woocs->default_currency);
    }

    /**
     * @return Currency|null
     * @throws \Exception
     */
    protected function getCurrentCurrency()
    {
        return $this->getCurrency($this->woocs->current_currency);
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

        return new Currency($code, $currencyData['symbol'], $currencyData['rate']);
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
        $currencies   = $this->woocs->get_currencies();

        if (isset($currencies[$currency]) && ! is_null($currencies[$currency])) {
            $currencyData = $currencies[$currency];
        }

        return $currencyData;
    }
}
