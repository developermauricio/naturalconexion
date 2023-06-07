<?php

namespace ADP\BaseVersion\Includes\AdminExtensions;

use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepositoryInterface;

defined('ABSPATH') or exit;

class WcOrderPreviewExtensions
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    public function withOrderRepository(OrderRepositoryInterface $repository)
    {
        $this->orderRepository = $repository;
    }

    public function register()
    {
        add_action('woocommerce_admin_order_preview_end', array($this, 'printAppliedDiscountsOrderPreview'));
        add_filter(
            'woocommerce_admin_order_preview_get_order_details',
            array($this, 'addAppliedDiscountsData'),
            10,
            2
        );
    }

    public function printAppliedDiscountsOrderPreview()
    {
        echo '{{{ data.rules_rendered }}}';
    }

    public function addAppliedDiscountsData($exportData, $order)
    {
        $rules = $this->orderRepository->getAppliedRulesForOrder($exportData['order_number']);
        if ( ! empty($rules)) {
            $exportData['rules_rendered'] = $this->previewOrderAppliedDiscountRulesOutout($rules);
        }

        return $exportData;
    }

    protected function previewOrderAppliedDiscountRulesOutout($rules)
    {
        $html = '<style> .wdp-aplied-rules, .wdp-aplied-rules td:first-child { width: 100%; } </style>';

        $html .= '<div class="wc-order-preview-table-wrapper">';
        $html .= '<table class="wc-order-preview-table">';
        $html .= '<tr><td><strong class="ui-sortable-handle">' . __('Applied discounts',
                'advanced-dynamic-pricing-for-woocommerce') . '</strong></td></tr>';
        foreach ($rules as $row) {
            $order = $row['order'];
            $rule = $row['rule'];
            $html .= "<tr>";
            $html .= $rule ? '<td><a href=' . $this->ruleUrl($rule) . '>' . $rule->title . '</a></td><td>' : '<td></td><td>';
            $amount = floatval($order->amount + $order->extra + $order->giftedAmount);
            $html   .= empty($amount) ? '-' : wc_price($amount);
            $html   .= '</td>' . '</tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    protected function ruleUrl($row)
    {
        return add_query_arg(array(
            'rule_id' => $row->id,
            'tab'     => 'rules',
        ), admin_url('admin.php?page=wdp_settings'));
    }
}
