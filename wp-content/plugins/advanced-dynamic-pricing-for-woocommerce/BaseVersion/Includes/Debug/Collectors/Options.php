<?php

namespace ADP\BaseVersion\Includes\Debug\Collectors;

use ADP\BaseVersion\Includes\Context;
use WC_Customer;
use WC_Tax;

defined('ABSPATH') or exit;

class Options
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
     * @return array
     */
    public function collect()
    {
        return array(
            'wdp' => $this->context->getSettings()->getOptions(),
            'wdp_compatibility' => $this->context->getCompatibilitySettings()->getOptions(),
            'wc' => $this->getWcOptions(),
        );
    }

    public function getWcOptions()
    {
        $wcCustomer = new WC_Customer($this->context->getCurrentUser()->ID);

        $rates = array();
        $slugs = WC_Tax::get_tax_class_slugs();
        foreach (
            array_merge(array('standard' => ''),
                array_combine(array_values($slugs), array_values($slugs))) as $key => $taxClassSlug
        ) {
            $rates[$key] = WC_Tax::get_rates_for_tax_class($taxClassSlug);
        }

        return array(
            'woocommerce_calc_taxes'            => wc_tax_enabled(),
            'woocommerce_ship_to_countries'     => wc_shipping_enabled(),
            'woocommerce_prices_include_tax'    => wc_prices_include_tax(),
            'woocommerce_enable_coupons'        => wc_coupons_enabled(),
            'woocommerce_tax_round_at_subtotal' => get_option('woocommerce_tax_round_at_subtotal'),
            'tax_rates'                         => $rates,
            'customer_tax_rates'                => WC_Tax::get_rates('', $wcCustomer),
            'base_tax_rates'                    => WC_Tax::get_base_tax_rates(''),
        );
    }

}
