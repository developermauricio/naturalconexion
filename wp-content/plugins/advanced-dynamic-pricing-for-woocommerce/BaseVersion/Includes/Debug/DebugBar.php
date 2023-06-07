<?php

namespace ADP\BaseVersion\Includes\Debug;

defined('ABSPATH') or exit;

class DebugBar
{
    /**
     * @var CalculationProfiler
     */
    protected $profiler;

    public function __construct(CalculationProfiler $profiler)
    {
        $this->profiler = $profiler;
    }

    public function register_assets()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    public function install_action_to_render_bar_templates()
    {
        add_action('wp_head', function () {
            echo "<div style='display: none;'>";
            include_once WC_ADP_PLUGIN_TEMPLATES_PATH . 'reporter/tabs/base.php';
            include_once WC_ADP_PLUGIN_TEMPLATES_PATH . 'reporter/tabs/cart.php';
            include_once WC_ADP_PLUGIN_TEMPLATES_PATH . 'reporter/tabs/products.php';
            include_once WC_ADP_PLUGIN_TEMPLATES_PATH . 'reporter/tabs/rules.php';
            include_once WC_ADP_PLUGIN_TEMPLATES_PATH . 'reporter/tabs/reports.php';
            echo "</div>";
        });
    }

    /**
     * Support file download
     */
    public function installActionToAddIframe()
    {
        add_action('wp_head', function () {
            echo "<iframe id='wdp_export_new_window_frame' width=0 height=0 style='display:none'></iframe>";
        });
    }

    public function installActionToRenderBar()
    {
        add_action('wp_footer', array($this, 'render'));
    }

    public function render()
    {
        include_once WC_ADP_PLUGIN_TEMPLATES_PATH . 'reporter/main.php';
    }

    public function enqueueScripts()
    {
        $reporter = new ReporterAjax($this->profiler);

        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";
        wp_enqueue_script('wdp_user_report', $baseVersionUrl . 'assets/js/user-report.js', array('jquery'),
            WC_ADP_VERSION);
        $userReportData = array(
            'ajaxurl'    => admin_url('admin-ajax.php'),
            'i'          => array(
                'cart'               => __('Cart', 'advanced-dynamic-pricing-for-woocommerce'),
                'products'           => __('Products', 'advanced-dynamic-pricing-for-woocommerce'),
                'rules'              => __('Rules', 'advanced-dynamic-pricing-for-woocommerce'),
                'items'              => __('Items', 'advanced-dynamic-pricing-for-woocommerce'),
                'coupons'            => __('Coupons', 'advanced-dynamic-pricing-for-woocommerce'),
                'fees'               => __('Fees', 'advanced-dynamic-pricing-for-woocommerce'),
                'replaced_by_coupon' => __('Replaced by coupon', 'advanced-dynamic-pricing-for-woocommerce'),
                'replaced_by_fee'    => __('Replaced by fee', 'advanced-dynamic-pricing-for-woocommerce'),
                'rule_id'            => __('Rule ID', 'advanced-dynamic-pricing-for-woocommerce'),
                'rule'               => __('Rule', 'advanced-dynamic-pricing-for-woocommerce'),
                'shipping'           => __('Shipping', 'advanced-dynamic-pricing-for-woocommerce'),
                'get_system_report'  => __('Get system report', 'advanced-dynamic-pricing-for-woocommerce'),
            ),
            'classes'    => array(
                'replaced_by_coupon' => 'replaced-by-coupon',
                'replaced_by_fee'    => 'replaced-by-fee',
            ),
            'import_key' => $this->profiler->getImportKey(),
            'security' => wp_create_nonce($reporter->getNonceName()),
            'security_param' => $reporter->getNonceParam(),
        );

        wp_localize_script('wdp_user_report', 'user_report_data', $userReportData);

        wp_enqueue_style('wdp_user_report', $baseVersionUrl . 'assets/css/user-report.css', array(), WC_ADP_VERSION);
    }
}
