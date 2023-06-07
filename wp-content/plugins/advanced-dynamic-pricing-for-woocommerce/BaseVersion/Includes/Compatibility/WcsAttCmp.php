<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

/**
 * Offer your existing products on subscription, with this powerful add-on for WooCommerce Subscriptions.
 *
 * Plugin Name: WooCommerce All Products For Subscriptions
 * Author: WooCommerce
 *
 * @see https://woocommerce.com/products/all-products-for-woocommerce-subscriptions/
 */
class WcsAttCmp
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var null|\WCS_ATT
     */
    protected $wcsAtt;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
        $this->loadRequirements();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function loadRequirements()
    {
        if ( ! did_action('plugins_loaded')) {
            _doing_it_wrong(__FUNCTION__, sprintf(__('%1$s should not be called earlier the %2$s action.',
                'advanced-dynamic-pricing-for-woocommerce'), 'loadRequirements', 'plugins_loaded'), WC_ADP_VERSION);
        }

        $this->wcsAtt = class_exists("\WCS_ATT") ? \WCS_ATT::instance() : null;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return ! is_null($this->wcsAtt) && ($this->wcsAtt instanceof \WCS_ATT);
    }
}
