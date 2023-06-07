<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Gift Cards by SomewhereWarm
 * Author: SomewhereWarm
 *
 * @see https://woocommerce.com/products/gift-cards/
 */
class GiftCardsSomewhereWarmCmp
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var WC_Gift_Cards
     */
    private $giftCard;

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
                'advanced-dynamic-pricing-for-woocommerce'), 'load_requirements', 'plugins_loaded'), WC_ADP_VERSION);
        }

        $this->loadGift();
    }

    private function loadGift()
    {
        if (function_exists('WC_GC')) {
            $this->giftCard = WC_GC();
        }
    }

    public function applyCompatibility()
    {
        if ( ! $this->isActive()) {
            return;
        }

        add_action('wdp_calculate_totals_hook_priority', function ($priority) {
            return $priority - 2;
        });

        $gift_cart = $this->giftCard->cart;
        if (false === ($priority1 = has_action('woocommerce_after_calculate_totals',
                array($gift_cart, 'after_calculate_totals')))) {
            return;
        }

        if (false === ($priority2 = has_action('woocommerce_after_calculate_totals',
                array('WC_GC_Compatibility', 'decrease_cart_totals_recursive_counter')))) {
            return;
        }
        remove_action('woocommerce_after_calculate_totals', array($gift_cart, 'after_calculate_totals'), $priority1);
        remove_action('woocommerce_after_calculate_totals',
            array('WC_GC_Compatibility', 'decrease_cart_totals_recursive_counter'), $priority2);
        add_action('woocommerce_after_calculate_totals', array($gift_cart, 'after_calculate_totals'), PHP_INT_MAX - 1);
        add_action('woocommerce_after_calculate_totals',
            array('WC_GC_Compatibility', 'decrease_cart_totals_recursive_counter'), PHP_INT_MAX);
    }

    public function isActive()
    {
        return ! is_null($this->giftCard);
    }

}
