<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

/**
 * Plugin Name: YITH WooCommerce Gift Cards
 * Author: YITH
 *
 * @see https://wordpress.org/plugins/yith-woocommerce-gift-cards/
 */
class YithGiftCardsCmp
{
    /**
     * @var Context
     */
    private $context;

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function applyCompatibility()
    {
        if ( ! $this->isActive()) {
            return;
        }

        add_filter('adp_get_original_product_from_cart', function($product, $wcCartItem) {
            if ($product instanceof \WC_Product_Gift_Card) {
                $product->adpCustomInitialPrice = $product->get_price();
            }
            return $product;
        }, 10, 2);
    }

    public function isActive()
    {
        return defined('YITH_YWGC_PLUGIN_NAME');
    }
}
