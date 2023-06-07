<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use WC_Deposits_Cart_Manager;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WooCommerce Deposits
 * Author: WooCommerce
 *
 * @see https://woocommerce.com/products/woocommerce-deposits/
 */
class WcDepositsCmp
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
        return defined("WC_DEPOSITS_VERSION");
    }

    /**
     * @param \WC_Cart $wcCart
     */
    public function updateDepositsData($wcCart)
    {
        if ( ! class_exists("WC_Deposits_Cart_Manager")
             || ! class_exists("WC_Deposits_Product_Manager")
             || ! class_exists("WC_Deposits_Product_Meta")
             || ! class_exists("WC_Deposits_Plans_Manager")
        ) {
            return;
        }

        /**
         * The methods below are cloned but the lines, that change the cart item price, are commented out.
         *
         * @see WC_Deposits_Cart_Manager::get_cart_from_session
         * @see WC_Deposits_Cart_Manager::add_cart_item
         *
         */
        foreach ($wcCart->cart_contents as $cartItemKey => $cartItem) {
            $cartItem['is_deposit']   = ! empty($cartItem['is_deposit']);
            $cartItem['payment_plan'] = ! empty($cartItem['payment_plan']) ? absint($cartItem['payment_plan']) : 0;

            if ( ! empty($cartItem['is_deposit'])) {
                $facade = new WcCartItemFacade($cartItem, $cartItemKey);

                if ($facade->getNewPrice() === null) {
                    $depositAmount = \WC_Deposits_Product_Manager::get_deposit_amount(
                        $cartItem['data'],
                        ! empty($cartItem['payment_plan']) ? $cartItem['payment_plan'] : 0,
                        'order'
                    );
                } else {
                    $product   = $cartItem['data'];
                    $dataPrice = $this->getProductDataPrice($product);
                    $price     = $product->get_price('edit');

                    if ($facade->getNewPrice() !== null) {
                        $product->set_price($dataPrice * ($facade->getNewPrice() / ($facade->getOriginalPrice())));
                    }

                    $depositAmount = \WC_Deposits_Product_Manager::get_deposit_amount(
                        $cartItem['data'],
                        ! empty($cartItem['payment_plan']) ? $cartItem['payment_plan'] : 0,
                        'order'
                    );

                    $product->set_price($price);
                }

                if (false !== $depositAmount) {
                    $cartItem['deposit_amount'] = $depositAmount;

                    // Bookings support
                    $depositMultipleCost = 'yes' === \WC_Deposits_Product_Meta::get_meta(
                            $cartItem['data']->get_id(),
                            '_wc_deposit_multiple_cost_by_booking_persons'
                        );
                    if (isset($cartItem['booking']['_persons']) && $depositMultipleCost) {
                        if (is_array($cartItem['booking']['_persons'])) {
                            $factor = array_sum($cartItem['booking']['_persons']);
                        } else {
                            $factor = $cartItem['booking']['_persons'];
                        }

                        $cartItem['deposit_amount'] = $cartItem['deposit_amount'] * absint($factor);
                    }

                    // Work out %
//                    if ( ! empty($cartItem['payment_plan'])) {
//                        $plan                    = \WC_Deposits_Plans_Manager::get_plan($cartItem['payment_plan']);
//                        $total_percent           = $plan->get_total_percent();
//                        $cartItem['full_amount'] = ($cartItem['data']->get_price() / 100) * $total_percent;
//                        $cartItem['data']->set_price($cartItem['full_amount']);
//                    } else {
//                        $cartItem['full_amount'] = $cartItem['data']->get_price();
//                    }
                    $cartItem['full_amount'] = $cartItem['data']->get_price();
                }
            }


            $wcCart->cart_contents[$cartItemKey] = $cartItem;
        }

        $wcNoFilterWorker = new \ADP\BaseVersion\Includes\WC\WcNoFilterWorker();
        $wcNoFilterWorker->calculateTotals($wcCart, $wcNoFilterWorker::FLAG_ALLOW_TOTALS_HOOKS);
    }

    protected function getProductDataPrice($product)
    {
        $reflection = new \ReflectionClass($product);
        $property   = $reflection->getProperty('data');
        $property->setAccessible(true);
        $data = $property->getValue($product);

        return $data['price'];
    }
}
