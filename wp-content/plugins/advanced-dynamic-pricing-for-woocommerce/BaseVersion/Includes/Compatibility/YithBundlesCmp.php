<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined('ABSPATH') or exit;

/**
 * Plugin Name: YITH WooCommerce Product Bundles
 * Author: YITH
 *
 * @see https://wordpress.org/plugins/yith-woocommerce-product-bundles/
 */
class YithBundlesCmp
{
    /**
     * @var Context
     */
    private $context;

    public function __construct()
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function addFilters()
    {
        add_filter('adp_product_get_price', function ($price, $product, $variation, $qty, $trdPartyData, $facade) {
            if ($facade === null) {
                return $price;
            }

            if ($this->isBundled($facade)) {
                $price = 0.0;
            }

            return $price;
        }, 10, 6);
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return bool
     */
    public function isBundled( WcCartItemFacade $facade ) {
        $trdPartyData = $facade->getThirdPartyData();

        return isset( $trdPartyData['bundled_by'] );
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return bool
     */
    public function isBundle( WcCartItemFacade $facade ) {
        $trdPartyData = $facade->getThirdPartyData();

        return isset( $trdPartyData['yith_parent'] ) && isset( $trdPartyData['bundled_items'] );
    }

    public function isActive()
    {
        return defined('YITH_WCPB_VERSION');
    }
}
