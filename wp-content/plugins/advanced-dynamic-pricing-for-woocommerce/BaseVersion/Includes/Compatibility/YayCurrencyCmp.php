<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Context\CurrencyController;
use Yay_Currency\Helpers\YayCurrencyHelper;

defined('ABSPATH') or exit;

/**
 * Plugin Name: YayCurrency
 * Author: YayCommerce
 *
 * @see https://wordpress.org/plugins/yaycurrency
 */
class YayCurrencyCmp
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var array
     */
    private $convertedCurrency = array();

    /**
     * @var array
     */
    private $applyCurrency = array();

    public function __construct($deprecated = null)
    {
        $this->loadRequirements();
    }

    public function loadRequirements()
    {
        if (! did_action('plugins_loaded')) {
            _doing_it_wrong(
                __FUNCTION__, sprintf(
                    __(
                        '%1$s should not be called earlier the %2$s action.',
                        'advanced-dynamic-pricing-for-woocommerce'
                    ), 'load_requirements', 'plugins_loaded'
                ), WC_ADP_VERSION
            );
        }

        if(!$this->isActive()) {
            return;
        }

        $this->convertedCurrency = YayCurrencyHelper::converted_currency();
        $this->applyCurrency     = YayCurrencyHelper::get_apply_currency($this->convertedCurrency);
    }

    public function isActive()
    {
        return function_exists('Yay_Currency\\plugin_init');
    }

    public function prepareHooks()
    {
        if ($this->isActive()) {
            $yayCurrency = \Yay_Currency\Engine\FEPages\WooCommerceCurrency::get_instance();
            remove_filter('woocommerce_package_rates', array($yayCurrency, 'change_shipping_cost'), 10, 2);
        }
    }

    public function getCurrencyData($currencyCode)
    {
        $applyCurrency = YayCurrencyHelper::filtered_by_currency_code($currencyCode, $this->convertedCurrency);
        return $applyCurrency;
    }

    protected function getDefaultCurrency()
    {
        $defaultCurrency = get_option('woocommerce_currency');
        return $this->getCurrency($defaultCurrency);
    }

    protected function getCurrency($code)
    {
        $applyCurrency = $this->getCurrencyData($code);
        $rate = YayCurrencyHelper::get_rate_fee($applyCurrency);
        return new Currency($code, get_woocommerce_currency_symbol($code), $rate);
    }

    protected function getCurrentCurrency()
    {
        $currentCurrency = $this->applyCurrency['currency'];
        return $this->getCurrency($currentCurrency);
    }

    public function modifyContext(Context $context)
    {
        $this->context = $context;

        $this->context->currencyController = new CurrencyController($this->context, $this->getDefaultCurrency());
        $this->context->currencyController->setCurrentCurrency($this->getCurrentCurrency());
    }
}
