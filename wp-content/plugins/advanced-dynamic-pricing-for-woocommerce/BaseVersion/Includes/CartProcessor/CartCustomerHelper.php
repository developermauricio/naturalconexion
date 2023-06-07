<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Core\Cart\CartCustomer;
use ADP\BaseVersion\Includes\Context;
use WC_Order;

defined('ABSPATH') or exit;

class CartCustomerHelper
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CartCustomer
     */
    protected $cartCustomer;

    /**
     * @param Context|CartCustomer $contextOrCartCustomer
     * @param CartCustomer|null $deprecated
     */
    public function __construct($contextOrCartCustomer, $deprecated = null)
    {
        $this->context      = adp_context();
        $this->cartCustomer = $contextOrCartCustomer instanceof CartCustomer ? $contextOrCartCustomer : $deprecated;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function isLoggedIn()
    {
        $user         = $this->context->getCurrentUser();
        $cartCustomer = $this->cartCustomer;

        return $this->context->isUserLoggedIn() && $user->ID === $cartCustomer->getId();
    }

    /**
     * @param string $time
     *
     * @return int
     */
    public function getOrderCountAfter($time)
    {
        $time = $this->convertForStrToTime($time);

        if ($time === false) {
            return floatval(0);
        }

        $args = array(
            'post_status' => array_keys(wc_get_order_statuses()),
        );

        if ( ! empty($time)) {
            if ($this->context->isHPOSEnabled()) {
                $args['date_after'] = $time;
            } else {
                $args['date_query'] = array(
                    array(
                        'column' => 'post_date',
                        'after' => $time,
                    ),
                );
            }
        }

        return count($this->getOrderIds($args));
    }

    /**
     * @param string $time
     *
     * @return int
     */
    public function getOrderCountAfterPaidStatuses($time)
    {
        $time = $this->convertForStrToTime($time);

        if ($time === false) {
            return floatval(0);
        }

        $args = array(
            'post_status' => $this->getPreparedIsPaidOrderStatuses(),
        );

        if ( ! empty($time)) {
            if ($this->context->isHPOSEnabled()) {
                $args['date_after'] = $time;
            } else {
                $args['date_query'] = array(
                    array(
                        'column' => 'post_date',
                        'after' => $time,
                    ),
                );
            }
        }

        return count($this->getOrderIds($args));
    }

    /**
     * @return WC_Order|null
     */
    public function getLastPaidOrder()
    {
        $order_ids = $this->getOrderIds(array(
            'post_status' => array('wc-completed'),
            'numberposts' => 1,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ));

        $order = wc_get_order(array_pop($order_ids));

        return $order instanceof WC_Order ? $order : null;
    }

    /**
     * @return bool
     */
    public function isFirstOrder()
    {
        $orderIds = $this->getOrderIds(array(
            'post_status' => $this->getPreparedIsPaidOrderStatuses(),
            'numberposts' => 1,
        ));

        return count($orderIds) > 0;
    }

    /**
     * @param string $time
     *
     * @return float
     */
    public function getTotalSpendAmount($time)
    {
        $time = $this->convertForStrToTime($time);

        if ($time === false) {
            return floatval(0);
        }

        $args = array(
            'post_status' => array('wc-completed'),
        );

        if ( ! empty($time)) {
          if ($this->context->isHPOSEnabled()) {
                $args['date_after'] = $time;
            } else {
                $args['date_query'] = array(
                    array(
                        'column' => 'post_date',
                        'after' => $time,
                    ),
                );
            }
        }

        $order_ids = $this->getOrderIds($args);

        $orders = array_filter(array_map('wc_get_order', $order_ids));

        if ( ! count($orders)) {
            return floatval(0);
        }

        return array_sum(array_map(function ($order) {
            /**
             * @var $order WC_Order
             */
            return $order->get_total() - $order->get_total_refunded();
        }, $orders));
    }

    /**
     * @return float
     */
    public function getAvgSpendAmount()
    {
        $order_ids = $this->getOrderIds(array(
            'post_status' => array('wc-completed'),
        ));

        $orders = array_filter(array_map('wc_get_order', $order_ids));

        if ( ! count($orders)) {
            return floatval(0);
        }

        return array_sum(array_map(function ($order) {
                return $order->get_total();
            }, $orders)) / count($orders);
    }

    /**
     * @param array $args
     *
     * @return array<int,int>
     */
    protected function getOrderIds($args = array()): array
    {
        if ($this->cartCustomer->isGuest()) {
            return array();
        }

        $args = array_merge(array(
            'numberposts' => -1,
            'orderby'     => 'date',
            'order'       => 'DESC',
            'customer_id' => $this->cartCustomer->getId(),
            'post_type'   => wc_get_order_types(),
            'post_status' => array_keys(wc_get_order_statuses()),
            'return'      => 'ids',

        ), $args);

        return wc_get_orders($args);
    }

    /**
     * @return array<int,string>
     */
    protected function getPreparedIsPaidOrderStatuses()
    {
        return array_map(function ($status) {
            return 'wc-' !== substr($status, 0, 3) ? 'wc-' . $status : $status;
        }, wc_get_is_paid_statuses());
    }

    /**
     * @param string $time
     *
     * @return false|int|string
     */
    public function convertForStrToTime($time)
    {
        if ( ! $time or ! is_string($time)) {
            return false;
        }

        if ('all_time' == $time) {
            $time = 0;
        } elseif ('now' == $time) {
            $time = 'today';
        } elseif ('this week' == $time) {
            $time = 'last monday';
        } elseif ('this month' == $time) {
            $time = 'first day of ' . date('F Y', current_time('timestamp'));
        } elseif ('this year' == $time) {
            $time = 'first day of January ' . date('Y', current_time('timestamp'));
        }

        return $time;
    }
}
