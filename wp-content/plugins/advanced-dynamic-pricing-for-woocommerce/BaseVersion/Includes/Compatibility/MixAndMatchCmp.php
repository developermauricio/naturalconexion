<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined( 'ABSPATH' ) or exit;

/**
 * Plugin Name: WooCommerce Mix and Match Products
 * Author: Kathy Darling, Matty Cohen
 *
 * @see http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 */
class MixAndMatchCmp {
    /**
     * @var Context
     */
    protected $context;

    public function __construct() {
        $this->context = adp_context();
    }

    public function withContext( Context $context ) {
        $this->context = $context;
    }

    public function addFilters()
    {
        add_filter('adp_product_get_price', function ($price, $product, $variation, $qty, $trdPartyData, $facade) {
            if ($facade === null) {
                return $price;
            }

            if ($this->isMixAndMatchChild($facade)) {
                $price = $product->get_price();
            }

            return $price;
        }, 10, 6);
    }

    /**
     * @return bool
     */
    public function isActive() {
        return class_exists( "WC_Mix_and_Match" ) || class_exists( "WC_Mix_and_Match" );
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return bool
     */
    public function isMixAndMatchParent( WcCartItemFacade $facade ) {
        $trdPartyData = $facade->getThirdPartyData();

        return isset( $trdPartyData['mnm_contents'] );
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return bool
     */
    public function isMixAndMatchChild( WcCartItemFacade $facade ) {
        $trdPartyData = $facade->getThirdPartyData();

        return isset( $trdPartyData['mnm_container'] );
    }

    /**
     * @return bool
     */
    public function isMixAndMatchProduct( $product ) {
        return $product instanceof \WC_Product_Mix_and_Match;
    }
}
