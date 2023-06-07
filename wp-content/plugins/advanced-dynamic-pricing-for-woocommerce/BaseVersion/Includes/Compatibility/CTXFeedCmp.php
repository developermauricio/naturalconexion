<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\WC\PriceFunctions;

defined('ABSPATH') or exit;

/**
 * Plugin Name: CTX Feed
 * Author: WebAppick
 *
 * @see https://wordpress.org/plugins/webappick-product-feed-for-woocommerce/
 */
class CTXFeedCmp
{
    /**
     * @return bool
     */
    public function isActive()
    {
        return defined("WOO_FEED_FREE_FILE");
    }

    public function prepareHooks()
    {
        $context = adp_context();

        if ($context->getCompatibilityOption("ctx_feed_replace_regular_price_field")) {
            add_filter('woo_feed_filter_product_price', function ($price, $product, $config, $tax) {
                $price = adp_functions()->getDiscountedProductPrice($product, 1, true);
                return $price;
            }, 10, 4);
        }

        if ($context->getCompatibilityOption("ctx_feed_replace_price_field")) {
            add_filter('woo_feed_filter_product_regular_price', function ($price, $product, $config, $tax) {
                $price = adp_functions()->getDiscountedProductPrice($product, 1, true);
                return $price;
            }, 10, 4);
        }

        if ($context->getCompatibilityOption("ctx_feed_replace_sale_price_field")) {
            add_filter('woo_feed_filter_product_sale_price', function ($price, $product, $config, $tax) {
                $price = adp_functions()->getDiscountedProductPrice($product, 1, true);
                return $price;
            }, 10, 4);
        }


        if ($context->getCompatibilityOption("ctx_feed_replace_regular_price_with_tax_field")) {
            add_filter('woo_feed_filter_product_price_with_tax', function ($price, $product, $config, $tax) {
                $price = adp_functions()->getDiscountedProductPrice($product, 1, true);
                $price = (new PriceFunctions())->getPriceIncludingTax($product, ["price" => $price]);
                return $price;
            }, 10, 4);
        }

        if ($context->getCompatibilityOption("ctx_feed_replace_price_with_tax_field")) {
            add_filter('woo_feed_filter_product_regular_price_with_tax', function ($price, $product, $config, $tax) {
                $price = adp_functions()->getDiscountedProductPrice($product, 1, true);
                $price = (new PriceFunctions())->getPriceIncludingTax($product, ["price" => $price]);
                return $price;
            }, 10, 4);
        }

        if ($context->getCompatibilityOption("ctx_feed_replace_sale_price_with_tax_field")) {
            add_filter('woo_feed_filter_product_sale_price_with_tax', function ($price, $product, $config, $tax) {
                $price = adp_functions()->getDiscountedProductPrice($product, 1, true);
                $price = (new PriceFunctions())->getPriceIncludingTax($product, ["price" => $price]);
                return $price;
            }, 10, 4);
        }
    }
}
