<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartContext;
use ADP\BaseVersion\Includes\Core\Cart\ShippingAdjustment;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcShippingRateFacade;
use WC_Cart;
use WC_Shipping_Rate;

defined('ABSPATH') or exit;

class CartShippingProcessor
{
    /**
     * @var array<int, ShippingAdjustment>
     */
    protected $adjustments = array();

    /**
     * @var CartContext
     */
    protected $cartContext;

    /**
     * @var Context
     */
    protected $context;

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param Cart $cart
     */
    public function refresh($cart)
    {
        $this->cartContext = $cart->getContext();
        $this->adjustments = array();

        foreach ($cart->getShippingAdjustments() as $adjustment) {
            /** Free shipping rewrites others */
            if ($adjustment->isType($adjustment::TYPE_FREE)) {
                $this->adjustments = array(clone $adjustment);

                return;
            }

            $this->adjustments[] = clone $adjustment;
        }
    }

    /**
     * @param WC_Cart $wcCart
     */
    public function sanitize($wcCart)
    {
        $this->adjustments = array();
    }

    public function setFilterToEditPackageRates()
    {
        add_filter('woocommerce_package_rates', array($this, 'packageRates'), PHP_INT_MAX, 2);
        add_filter('woocommerce_package_rates', array($this, 'currencyPackageRates'), PHP_INT_MAX - 1, 2);
    }

    public function unsetFilterToEditPackageRates()
    {
        remove_filter('woocommerce_package_rates', array($this, 'packageRates'), PHP_INT_MAX);
        remove_filter('woocommerce_package_rates', array($this, 'currencyPackageRates'), PHP_INT_MAX - 1);
    }

    public function setFilterToEditShippingMethodLabel()
    {
        add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'shippingMethodFullLabel'), 10, 2);
    }

    public function unsetFilterToEditShippingMethodLabel()
    {
        remove_filter('woocommerce_cart_shipping_method_full_label', array($this, 'shippingMethodFullLabel'), 10);
    }

    public function setFilterForShippingChosenMethod()
    {
        add_filter('woocommerce_shipping_chosen_method', array($this, 'hookShippingChosenMethod'), 10, 3);
    }

    public function unsetFilterForShippingChosenMethod()
    {
        remove_filter('woocommerce_shipping_chosen_method', array($this, 'hookShippingChosenMethod'), 10);
    }

    /**
     * To apply shipping we have to clear stored packages in session to allow 'woocommerce_package_rates' filter run
     */
    public function purgeCalculatedPackagesInSession()
    {
        foreach (WC()->shipping()->get_packages() as $index => $value) {
            $key = "shipping_for_package_" . $index;
            unset(WC()->session->$key);
        }
    }

    /**
     * @param array<int,WC_Shipping_Rate> $rates
     * @param array $package
     *
     * @return array<int,WC_Shipping_Rate>
     */
    public function currencyPackageRates($rates, $package)
    {
        foreach ($rates as &$rate) {
            if (isset($rate->taxes)) {
                $taxes = $rate->taxes;
                if ( ! empty($taxes)) {
                    $newTax = array();
                    foreach ($taxes as $key => $tax) {
                        $newTax[$key] = $tax * $this->context->currencyController->getRate();
                    }
                    $rate->set_taxes($newTax);
                }
            }

            if ($this->context->currencyController->isCurrencyChanged()) {
                $rate->set_cost($rate->get_cost() * $this->context->currencyController->getRate());
            }
        }

        return $rates;
    }

    /**
     * @param array<int,WC_Shipping_Rate> $rates
     * @param array $package
     *
     * @return array<int,WC_Shipping_Rate>
     */
    public function packageRates($rates, $package)
    {
        if (count($this->adjustments) < 1) {
            return $rates;
        }

        foreach ($rates as &$rate) {
            $cost = $rate->get_cost();

            $adjustments = array();

            foreach ($this->adjustments as $adjustment) {
                $adjustment = clone $adjustment;
                $amount     = $this->getCalculatedAdjustment($adjustment, $cost);
                if ( ! empty($amount) && ! $adjustment->isMethod(ShippingAdjustment::DEFAULT_SHIPPING_METHOD) && ! $adjustment->isMethod($rate->get_method_id())) {
                    continue;
                }
                $adjustment->setAmount($amount);
                $adjustments[] = $adjustment;
            }

            $appliedAdjustments = array();
            foreach ($adjustments as $adjustment) {
                /** @var ShippingAdjustment $adjustment */

                // maximize discount by default
                $appliedAdjustment = reset($appliedAdjustments);

                if ($appliedAdjustment === false || $appliedAdjustment->getAmount() <= $adjustment->getAmount()) {
                    $appliedAdjustments = array($adjustment);
                }
            }


            $newCost = $cost;
            $this->fixAmounts($newCost, $appliedAdjustments);

            $rateWrapper = new WcShippingRateFacade($rate);
            $rateWrapper->setNewCost($newCost);
            foreach ($appliedAdjustments as $adjustment) {
                $rateWrapper->applyAdjustment($adjustment);
            }
            $rateWrapper->modifyMeta();
            $rate = $rateWrapper->getRate();
        }

        return $rates;
    }

    /**
     * @param $adjustment ShippingAdjustment
     * @param $cost string
     *
     * @return float
     */
    public function getCalculatedAdjustment($adjustment, $cost)
    {
        $amount = "";
        if ($adjustment->isType($adjustment::TYPE_AMOUNT)) {
            $amount = $adjustment->getValue();
        } elseif ($adjustment->isType($adjustment::TYPE_PERCENTAGE)) {
            $amount = $cost * $adjustment->getValue() / 100;
        } elseif ($adjustment->isType($adjustment::TYPE_FIXED_VALUE)) {
            $amount = $cost - $adjustment->getValue();
        } elseif ($adjustment->isType($adjustment::TYPE_FREE)) {
            $amount = $cost;
        }

        return $amount;
    }

    /**
     * @param WC_Cart $wcCart
     */
    public function updateTotals($wcCart)
    {
        $this->cartContext->getSession()->insertShippingData($this->adjustments);
    }

    /**
     * Do not allow negative prices.
     * Remember to change the amounts for the correct story.
     *
     * @param float $rateCost
     * @param array<int,ShippingAdjustment> $appliedAdjustments
     */
    protected function fixAmounts(&$rateCost, &$appliedAdjustments)
    {
        foreach ($appliedAdjustments as &$adjustment) {
            $amount   = $adjustment->getAmount();
            $rateCost -= $amount;

            if ($rateCost < 0) {
                $adjustment->setAmount($amount + $rateCost);
                $rateCost = 0;
            }
        }
    }

    /**
     * @param string $label
     * @param WC_Shipping_Rate $rate
     *
     * @return string
     */
    public function shippingMethodFullLabel($label, $rate)
    {
        $rateWrapper = new WcShippingRateFacade($rate);
        if ( ! $rateWrapper->isAffected()) {
            return $label;
        }

        $initial_cost = $rateWrapper->getInitialPrice();
        $cost         = floatval($rateWrapper->getRate()->get_cost());
        if ($initial_cost === $cost) {
            return $label;
        }

        $initial_tax = array_sum($rateWrapper->getInitialPriceTaxes());

        if (WC()->cart->display_prices_including_tax()) {
            $initial_cost_html = '<del>' . wc_price($initial_cost + $initial_tax) . '</del>';
        } else {
            $initial_cost_html = '<del>' . wc_price($initial_cost) . '</del>';
        }

        $initial_cost_html = preg_replace('/\samount/is', ' wdp-amount', $initial_cost_html);

        return preg_replace('/(<span[^>]*>)/is', $initial_cost_html . ' $1', $label, 1);
    }

    /**
     * @param string $default
     * @param WC_Shipping_Rate $rates
     * @param string $chosenMethod
     *
     * @return mixed
     */
    public function hookShippingChosenMethod($default, $rates, $chosenMethod)
    {
        return isset($rates[$chosenMethod]) ? $chosenMethod : $default;
    }
}
