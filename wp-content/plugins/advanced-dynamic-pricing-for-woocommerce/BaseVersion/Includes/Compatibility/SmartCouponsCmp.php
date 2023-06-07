<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WooCommerce Smart Coupons
 * Author: StoreApps
 *
 * @see https://woocommerce.com/products/smart-coupons/
 */
class SmartCouponsCmp
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \WC_Smart_Coupons|null
     */
    protected $instance;

    public function __construct()
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
            _doing_it_wrong(
                __FUNCTION__,
                sprintf(
                    __(
                        '%1$s should not be called earlier the %2$s action.',
                        'advanced-dynamic-pricing-for-woocommerce'
                    ),
                    'loadRequirements',
                    'plugins_loaded'
                ),
                WC_ADP_VERSION
            );
        }

        $this->instance = class_exists("\WC_Smart_Coupons") ? \WC_Smart_Coupons::get_instance() : null;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return ($this->instance instanceof \WC_Smart_Coupons);
    }

    public function addActionToMoveAction()
    {
        if ( did_action('wp_loaded') ) {
            $this->moveAfterCalculateTotalsAction();
        } else {
            add_action('wp_loaded', [$this, 'moveAfterCalculateTotalsAction'], 21);
        }
    }

    public function moveAfterCalculateTotalsAction()
    {
        if (false === has_action(
                'woocommerce_after_calculate_totals',
                [$this->instance, 'smart_coupons_after_calculate_totals']
            )) {
            return;
        }
        remove_action(
            'woocommerce_after_calculate_totals',
            [$this->instance, 'smart_coupons_after_calculate_totals'],
            999
        );
        add_action(
            'woocommerce_after_calculate_totals',
            [$this->instance, 'smart_coupons_after_calculate_totals'],
            PHP_INT_MAX
        );
    }
}
