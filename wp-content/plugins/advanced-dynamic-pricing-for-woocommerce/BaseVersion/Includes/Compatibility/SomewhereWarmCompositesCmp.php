<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WooCommerce Composite Products
 * Author: SomewhereWarm
 *
 * @see https://woocommerce.com/products/composite-products/
 */
class SomewhereWarmCompositesCmp
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
        return class_exists("WC_Composite_Products");
    }

    public function isAllowToProcessPricedIndividuallyItems()
    {
        return apply_filters('adp_allow_to_process_priced_individually_composite_items', false);
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return bool
     */
    public function isCompositeItem(WcCartItemFacade $facade)
    {
        return function_exists('wc_cp_maybe_is_composited_cart_item') && wc_cp_maybe_is_composited_cart_item($facade->getThirdPartyData());
    }

    /**
     * @param WcCartItemFacade $facade
     * @param \WC_Cart $WcCart
     *
     * @return bool
     */
    public function isCompositeItemNotPricedIndividually(WcCartItemFacade $facade, \WC_Cart $WcCart)
    {
        $thirdPartyData = $facade->getThirdPartyData();

        if ( ! (function_exists('wc_cp_maybe_is_composited_cart_item') && wc_cp_maybe_is_composited_cart_item($thirdPartyData))) {
            return false;
        }

        /** @var \WC_Product_Composite $composite */
        $composite = WC()->cart->cart_contents[$thirdPartyData['composite_parent']]['data'];
        $component = $composite->get_component($thirdPartyData['composite_item']);

        return $component && ! $component->is_priced_individually();
    }
}
