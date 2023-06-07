<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use WC_Subscriptions_Product;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WooCommerce Subscriptions
 * Author: WooCommerce
 *
 * @see https://woocommerce.com/products/woocommerce-subscriptions/
 */
class WcSubscriptionsCmp
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var bool
     */
    protected $isActive;

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

        $this->isActive = class_exists("\WC_Subscriptions") && defined("WCS_INIT_TIMESTAMP");
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param \WC_Product $product
     *
     * @return bool
     */
    public function isSubscriptionProduct($product)
    {
        return class_exists('WC_Subscriptions_Product') && WC_Subscriptions_Product::is_subscription($product);
    }

    /**
     * @param WcCartItemFacade $facade
     * @return bool
     */
    public function isRenewalSubscription( WcCartItemFacade $facade ) {
        $trdPartyData = $facade->getThirdPartyData();

        return isset( $trdPartyData['subscription_renewal'] );
    }

    /**
     * @param \WC_Product $product
     * @param string $priceHtml
     *
     * @return bool
     */
    public function maybeAddSubsTail($product, $priceHtml)
    {
        if ( ! class_exists('WC_Subscriptions_Product')) {
            return $priceHtml;
        }

        return WC_Subscriptions_Product::get_price_string($product,
            array('price' => $priceHtml, 'tax_calculation' => $this->context->getTaxDisplayCartMode()));
    }

    public static function isRecurringCartCalculation()
    {
        if (method_exists('\WC_Subscriptions_Cart', 'get_calculation_type')) {
            return \WC_Subscriptions_Cart::get_calculation_type() === 'recurring_total';
        }

        return false;
    }

    /**
     * @param array $cartItem
     *
     * @return bool
     */
    public function isSetFreeTrial($cartItem)
    {
        if ( ! $this->isActive()) {
            return false;
        }
        if (
            \WC_Subscriptions_Synchroniser::is_product_synced($cartItem['data']) &&
            ! \WC_Subscriptions_Synchroniser::is_payment_upfront($cartItem['data']) &&
            ! \WC_Subscriptions_Synchroniser::is_product_prorated($cartItem['data']) &&
            ! \WC_Subscriptions_Synchroniser::is_today(\WC_Subscriptions_Synchroniser::calculate_first_payment_date($cartItem['data'],
                'timestamp'))
        ) {
            return true;
        }

        return false;
    }

    public function setHooksBeforeCalculateTotals()
    {
        \WC_Subscriptions_Synchroniser::maybe_set_free_trial();
        //The commented lines represent the hooks at 'woocommerce_before_calculate_totals'.
        //May be handy for the future usage
//		\WC_Subscriptions_Coupon::remove_coupons( $wcCart );
        \WC_Subscriptions_Cart::add_calculation_price_filter();
//		\WC_Subscriptions_Switcher::calculate_prorated_totals( $wcCart );
//		\WC_Subscriptions_Switcher::maybe_set_free_trial();

        if ( ! has_action('woocommerce_calculated_total')) {
            add_filter('woocommerce_calculated_total', '\WC_Subscriptions_Cart::calculate_subscription_totals', 1000,
                2);
        }
    }

    public function removeHooksAfterCalculateTotals()
    {
        \WC_Subscriptions_Cart::remove_calculation_price_filter();
    }
}
