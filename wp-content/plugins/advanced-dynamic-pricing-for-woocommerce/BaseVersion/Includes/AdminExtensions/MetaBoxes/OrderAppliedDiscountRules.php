<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\MetaBoxes;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Models\Order;
use ADP\BaseVersion\Includes\Database\Models\Rule;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use WP_Post;

defined('ABSPATH') or exit;

class OrderAppliedDiscountRules
{
    /**
     * @var null|array<int, array{order: Order, rule: Rule}>
     */
    private static $rules = null;

    public static function init()
    {
        global $post, $theorder;

        if (!$post && !$theorder) {
            return;
        }

        $orderRepository = new OrderRepository();
        self::$rules = $orderRepository->getAppliedRulesForOrder($post->ID ?? $theorder->get_id());

        if ( ! empty(self::$rules)) {
            add_meta_box(
                'wdp-order-applied-rules',
                __('Applied discounts', 'advanced-dynamic-pricing-for-woocommerce'),
                array(__CLASS__, 'output'),
                adp_context()->isHPOSEnabled() ? wc_get_page_screen_id('shop-order') : 'shop_order',
                'side',
                'core'
            );
        }
    }

    /**
     * Output the metaBox.
     *
     * @param WP_Post $post
     */
    public static function output($post)
    {
        $context = new Context();
        $rules   = self::$rules;

        ?>
        <style> .wdp-aplied-rules, .wdp-aplied-rules td:first-child {
                width: 100%;
            } </style>
        <table class="wdp-aplied-rules">
            <?php foreach (self::$rules as $row):
                $order = $row['order'];
                $rule = $row['rule'];
                $amount = self::ruleAmount($order);

                if ($context->isHideRulesWithoutDiscountInOrderEditPage() && empty($amount)) {
                    continue;
                }

                ?>
                <tr>
                    <td>
                        <?php
                        echo $rule ? sprintf('<a href="%s">%s</a>', self::ruleUrl($rule), $rule->title) : "";
                        ?>
                    </td>
                    <td><?php
                        echo empty($amount) ? '-' : wc_price($amount);
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    private static function ruleUrl($row)
    {
        return add_query_arg(array(
            'rule_id' => $row->id,
            'tab'     => 'rules',
        ), admin_url('admin.php?page=wdp_settings'));
    }

    private static function ruleAmount($row)
    {
        return floatval($row->amount + $row->extra + $row->giftedAmount);
    }
}
