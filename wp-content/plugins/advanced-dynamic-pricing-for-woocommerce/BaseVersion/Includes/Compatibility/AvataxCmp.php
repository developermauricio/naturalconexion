<?php

namespace ADP\BaseVersion\Includes\Compatibility;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WooCommerce AvaTax
 * Author: SkyVerge
 *
 * @see https://woocommerce.com/products/woocommerce-avatax/
 */
class AvataxCmp
{
    /**
     * @return bool
     */
    public function isActive()
    {
        return class_exists('WC_AvaTax');
    }

    public function applyCompatibility()
    {
        add_filter("wdp_calculate_totals_hook_priority", function ($p) {
            return 900;
        }, 10, 1);
    }
}
