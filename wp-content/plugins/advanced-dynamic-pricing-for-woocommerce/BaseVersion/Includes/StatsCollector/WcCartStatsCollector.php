<?php

namespace ADP\BaseVersion\Includes\StatsCollector;

use ADP\BaseVersion\Includes\Core\Cart\ShippingAdjustment;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Models\Order;
use ADP\BaseVersion\Includes\Database\Models\OrderItem;
use ADP\BaseVersion\Includes\Database\Repository\OrderItemRepository;
use ADP\BaseVersion\Includes\Database\Repository\OrderItemRepositoryInterface;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepositoryInterface;
use ADP\BaseVersion\Includes\WC\WcCustomerSessionFacade;
use \ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use WC_Cart;
use WC_Order;
use WC_Order_Item_Product;
use WC_Shipping_Rate;
use WooCommerce;
use function WC;

defined('ABSPATH') or exit;

class WcCartStatsCollector
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderItemRepositoryInterface $orderItemRepository
     */
    protected $orderItemRepository;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context             = adp_context();
        $this->orderRepository     = new OrderRepository();
        $this->orderItemRepository = new OrderItemRepository();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withOrderRepository(OrderRepositoryInterface $repository)
    {
        $this->orderRepository = $repository;
    }

    public function withOrderItemRepository(OrderItemRepositoryInterface $repository)
    {
        $this->orderItemRepository = $repository;
    }

    public function setActionCheckoutOrderProcessed()
    {
        add_action('woocommerce_checkout_order_processed', array($this, 'checkoutOrderProcessed'), 10, 3);
        add_action(
            'woocommerce_checkout_create_order_line_item_object',
            array($this, 'saveInitialPriceToOrderItem'),
            10,
            4
        );
    }

    public function setActionCheckoutOrderProcessedDuringRestApi()
    {
        add_action('woocommerce_order_after_calculate_totals', array($this, 'afterOrderCalculateTotalsDuringRestApi'),
            10,
            2
        );
    }

    public function unsetActionCheckoutOrderProcessed()
    {
        remove_action('woocommerce_checkout_order_processed', array($this, 'checkoutOrderProcessed'), 10);
        remove_action(
            'woocommerce_checkout_create_order_line_item_object',
            array($this, 'saveInitialPriceToOrderItem'),
            10
        );
    }

    public function unsetActionCheckoutOrderProcessedDuringRestApi()
    {
        remove_action(
            'woocommerce_order_after_calculate_totals',
            array($this, 'afterOrderCalculateTotalsDuringRestApi'),
            10
        );
    }

    /**
     * @param bool $andTaxes
     * @param \WC_Abstract_Order $order
     */
    public function afterOrderCalculateTotalsDuringRestApi($andTaxes, $order)
    {
        if ( $order instanceof \WC_Order_Refund ) {
            return;
        }

        if ( ! isset(WC()->cart)) {
            return;
        }

        $orderId = $order->get_id();

        list($orderStats, $productStats) = $this->collectWcCartStats(WC());

        $orderDate = current_time('mysql');

        foreach ($orderStats as $ruleId => $statsItem) {
            $statsItem = array_merge(array(
                'order_id'         => $orderId,
                'rule_id'          => $ruleId,
                'amount'           => 0,
                'extra'            => 0,
                'shipping'         => 0,
                'is_free_shipping' => 0,
                'gifted_amount'    => 0,
                'gifted_qty'       => 0,
                'date'             => $orderDate,
            ), $statsItem);
            $orderRule = Order::fromArray($statsItem);
            $this->orderRepository->addOrderStats($orderRule);
        }

        foreach ($productStats as $productId => $byRule) {
            foreach ($byRule as $ruleId => $statsItem) {
                $statsItem = array_merge(array(
                    'order_id'      => $orderId,
                    'product_id'    => $productId,
                    'rule_id'       => $ruleId,
                    'qty'           => 0,
                    'amount'        => 0,
                    'gifted_amount' => 0,
                    'gifted_qty'    => 0,
                    'date'          => $orderDate,
                ), $statsItem);
                $orderItemRule = OrderItem::fromArray($statsItem);
                $this->orderItemRepository->addProductStats($orderItemRule);
            }
        }
    }

    public function checkoutOrderProcessed($orderId, $postedData, \WC_Order $order)
    {
        if ( ! isset(WC()->cart)) {
            return;
        }

        list($orderStats, $productStats) = $this->collectWcCartStats(WC());

        $orderDate = current_time('mysql');

        foreach ($orderStats as $ruleId => $statsItem) {
            $statsItem = array_merge(array(
                'order_id'         => $orderId,
                'rule_id'          => $ruleId,
                'amount'           => 0,
                'extra'            => 0,
                'shipping'         => 0,
                'is_free_shipping' => 0,
                'gifted_amount'    => 0,
                'gifted_qty'       => 0,
                'date'             => $orderDate,
            ), $statsItem);
            $orderRule = Order::fromArray($statsItem);
            $this->orderRepository->addOrderStats($orderRule);
        }

        foreach ($productStats as $productId => $byRule) {
            foreach ($byRule as $ruleId => $statsItem) {
                $statsItem = array_merge(array(
                    'order_id'      => $orderId,
                    'product_id'    => $productId,
                    'rule_id'       => $ruleId,
                    'qty'           => 0,
                    'amount'        => 0,
                    'gifted_amount' => 0,
                    'gifted_qty'    => 0,
                    'date'          => $orderDate,
                ), $statsItem);
                $orderItemRule = OrderItem::fromArray($statsItem);
                $this->orderItemRepository->addProductStats($orderItemRule);
            }
        }
    }

    /**
     * @param WooCommerce $wc
     *
     * @return array
     */
    private function collectWcCartStats(WooCommerce $wc)
    {
        $orderStats   = array();
        $productStats = array();

        $wcCart = $wc->cart;

        $cartItems = $wcCart->get_cart();
        foreach ($cartItems as $cartKey => $cartItem) {
            $itemFacade = new WcCartItemFacade($this->context, $cartItem, $cartKey);
            $rules      = $itemFacade->getDiscounts();

            if (empty($rules)) {
                continue;
            }

            $productId = $itemFacade->getProductId();
            foreach ($rules as $ruleId => $amounts) {
                $amount = is_array($amounts) ? array_sum($amounts) : $amounts;
                //add stat rows
                if ( ! isset($orderStats[$ruleId])) {
                    $orderStats[$ruleId] = array(
                        'amount'           => 0,
                        'qty'              => 0,
                        'gifted_qty'       => 0,
                        'gifted_amount'    => 0,
                        'shipping'         => 0,
                        'is_free_shipping' => 0,
                        'extra'            => 0
                    );
                }
                if ( ! isset($productStats[$productId][$ruleId])) {
                    $productStats[$productId][$ruleId] = array(
                        'amount'        => 0,
                        'qty'           => 0,
                        'gifted_qty'    => 0,
                        'gifted_amount' => 0
                    );
                }

                if ($itemFacade->isFreeItem()) {
                    $prefix = 'gifted_';
                } else {
                    $prefix = '';
                }
                // order
                $orderStats[$ruleId][$prefix . 'qty']    += $itemFacade->getQty();
                $orderStats[$ruleId][$prefix . 'amount'] += $amount * $itemFacade->getQty();
                // product
                $productStats[$productId][$ruleId][$prefix . 'qty']    += $itemFacade->getQty();
                $productStats[$productId][$ruleId][$prefix . 'amount'] += $amount * $itemFacade->getQty();
            }
        }

        $this->injectWcCartCouponStats($wc, $orderStats);
        $this->injectWcCartFeeStats($wc, $orderStats);
        $this->injectWcCartShippingStats($wc, $orderStats);

        return array($orderStats, $productStats);
    }

    /**
     * @param WooCommerce $wc
     * @param array       $orderStats
     */
    private function injectWcCartCouponStats($wc, array &$orderStats)
    {
        $wcCart          = $wc->cart;
        $wcSessionFacade = new WcCustomerSessionFacade($wc->session);

        $singleCoupons  = $wcSessionFacade->getSingleCoupons();
        $groupedCoupons = $wcSessionFacade->getGroupedCoupons();

        if ( ! $singleCoupons && ! $groupedCoupons) {
            return;
        }

        foreach ($wcCart->get_coupon_discount_totals() as $couponCode => $amount) {
            if (isset($groupedCoupons[$couponCode])) {
                foreach ($groupedCoupons[$couponCode] as $coupon) {
                    $ruleId = $coupon->getRuleId();
                    $value  = $coupon->getValue();

                    if ( ! isset($orderStats[$ruleId])) {
                        $orderStats[$ruleId] = array();
                    }

                    if ( ! isset($orderStats[$ruleId]['extra'])) {
                        $orderStats[$ruleId]['extra'] = 0.0;
                    }

                    $orderStats[$ruleId]['extra'] += $value;
                }
            } elseif (isset($singleCoupons[$couponCode])) {
                $coupon = $singleCoupons[$couponCode];
                $ruleId = $coupon->getRuleId();

                if ( ! isset($orderStats[$ruleId])) {
                    $orderStats[$ruleId] = array();
                }

                if ( ! isset($orderStats[$ruleId]['extra'])) {
                    $orderStats[$ruleId]['extra'] = 0.0;
                }

                $orderStats[$ruleId]['extra'] += $amount;
            }
        }
    }

    /**
     * @param WooCommerce $wc
     * @param array       $orderStats
     */
    private function injectWcCartFeeStats($wc, array &$orderStats)
    {
        $wcSessionFacade = new WcCustomerSessionFacade($wc->session);

        $fees = $wcSessionFacade->getFees();

        if ( ! $fees) {
            return;
        }

        foreach ($fees as $fee) {
            $ruleId = $fee->getRuleId();

            if ( ! isset($orderStats[$ruleId])) {
                $orderStats[$ruleId] = array();
            }

            if ( ! isset($orderStats[$ruleId]['extra'])) {
                $orderStats[$ruleId]['extra'] = 0.0;
            }

            $orderStats[$ruleId]['extra'] -= $fee->getAmount();
        }
    }

    /**
     * @param WooCommerce $wc
     * @param array $orderStats
     */
    private function injectWcCartShippingStats(WooCommerce $wc, array &$orderStats)
    {
        $shippings = $wc->session->get('chosen_shipping_methods');
        if (empty($shippings)) {
            return;
        }

        $appliedRulesKey = 'adp_adjustments';

        foreach ($shippings as $packageId => $shippingRateKey) {
            $packages = $wc->shipping()->get_packages();
            if (isset($packages[$packageId]['rates'][$shippingRateKey])) {
                /** @var WC_Shipping_Rate $shRate */
                $shRate     = $packages[$packageId]['rates'][$shippingRateKey];
                $shRateMeta = $shRate->get_meta_data();

                $isFreeShipping = isset($shRateMeta['adp_type']) && $shRateMeta['adp_type'] === "free"; //notice
                $adpRules       = isset($shRateMeta[$appliedRulesKey]) ? $shRateMeta[$appliedRulesKey] : false;

                if ( ! empty($adpRules) && is_array($adpRules)) {
                    foreach ($adpRules as $rule) {
                        /**
                         * @var ShippingAdjustment $rule
                         */
                        $ruleId = $rule->getRuleId();
                        $amount = $rule->getAmount();
                        if ( ! isset($orderStats[$ruleId])) {
                            $orderStats[$ruleId] = array();
                        }

                        if ( ! isset($orderStats[$ruleId]['shipping'])) {
                            $orderStats[$ruleId]['shipping'] = 0.0;
                        }

                        $orderStats[$ruleId]['shipping']         += $amount;
                        $orderStats[$ruleId]['is_free_shipping'] = $isFreeShipping;
                    }
                }

            }
        }
    }

    /**
     * @param WC_Order_Item_Product $item
     * @param string $cartItemKey
     * @param array $values
     * @param WC_Order $order
     *
     * @return WC_Order_Item_Product
     */
    public function saveInitialPriceToOrderItem($item, $cartItemKey, $values, $order)
    {
        if ( ! empty($values['wdp_rules'])) {
            $item->add_meta_data('_wdp_rules', $values['wdp_rules']);
        }

        return $item;
    }
}
