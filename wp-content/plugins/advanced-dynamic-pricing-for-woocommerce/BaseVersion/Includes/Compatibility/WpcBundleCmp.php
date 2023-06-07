<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined( 'ABSPATH' ) or exit;

/**
 * Plugin Name: WPC Product Bundles for WooCommerce
 * Author: WPClever
 *
 * @see https://wpclever.net/downloads/product-bundles/
 */
class WpcBundleCmp {
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

    public function callActionBeforeCalculateTotalsBeforeOurFirstRun()
    {
        add_action('wp_loaded', function () {
            /**
             * @see \WooCommerce::init
             * if ( $this->is_request( 'frontend' ) ) {
             *      wc_load_cart();
             * }
             */
            $context = $this->context;
            if (
                (!$context->is($context::ADMIN) || $context->is($context::AJAX))
                && ! $context->is($context::WP_CRON)
                && ! $context->is($context::REST_API)
            ) {
                do_action('woocommerce_before_calculate_totals', WC()->cart);
            }
        }, 14);
    }

    public function addFilters()
    {
    }

    /**
     * @return bool
     */
    public function isActive() {
        return class_exists( "WPCleverWoosb" ) || class_exists( "WC_Product_Woosb" );
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return bool
     */
    public function isBundled( WcCartItemFacade $facade ) {
        $trdPartyData = $facade->getThirdPartyData();

        return isset( $trdPartyData['woosb_parent_id'] );
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return bool
     */
    public function isSmartBundle( WcCartItemFacade $facade ) {
        $trdPartyData = $facade->getThirdPartyData();

        return isset( $trdPartyData['woosb_key'] ) && ! isset( $trdPartyData['woosb_parent_id'] );
    }

    /**
     * @return bool
     */
    public function isBundleProduct( $product ) {
        return $product instanceof \WC_Product_Woosb;
    }
}
