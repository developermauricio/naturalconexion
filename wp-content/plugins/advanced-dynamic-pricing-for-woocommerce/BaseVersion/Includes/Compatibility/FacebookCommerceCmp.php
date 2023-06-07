<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Facebook for WooCommerce
 * Author: Facebook
 *
 * @see https://github.com/woocommerce/facebook-for-woocommerce
 */
class FacebookCommerceCmp
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
     * @return bool
     */
    public function isActive()
    {
        return class_exists("WC_Facebook_Loader");
    }

    public function applyCompatibility()
    {
        if ( ! $this->isActive()) {
            return;
        }

        add_filter('wc_facebook_product_price', function ($price, $facebook_price, $product) {
            if ( ! $price) {
                return $price;
            }

            $discountPrice = adp_functions()->getDiscountedProductPrice($product, 1.0, true);
            if (empty($discountPrice)) {
                return $price;
            }

            return is_array($discountPrice) ? (int)(current($discountPrice) * 100) : (int)($discountPrice * 100);
        }, 10, 3);
    }
}
