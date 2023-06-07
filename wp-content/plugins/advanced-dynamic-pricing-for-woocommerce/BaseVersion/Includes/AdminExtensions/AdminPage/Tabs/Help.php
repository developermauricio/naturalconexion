<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Tabs;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminTabInterface;

defined('ABSPATH') or exit;

class Help implements AdminTabInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var Context
     */
    protected $context;

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
        $this->title   = self::getTitle();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function handleSubmitAction()
    {
        // do nothing
    }

    public function getViewVariables()
    {
        $tilesHelpInfo = $this->getTilesHelpInfo();
        return compact('tilesHelpInfo');
    }

    public static function getRelativeViewPath()
    {
        return 'admin_page/tabs/help.php';
    }

    public static function getHeaderDisplayPriority()
    {
        return 50;
    }

    public static function getKey()
    {
        return 'help';
    }

    public static function getTitle()
    {
        return __('Help', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public function enqueueScripts()
    {
        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";
        wp_enqueue_style('wdp_options-styles', $baseVersionUrl . 'assets/css/help.css', array(), WC_ADP_VERSION);
    }

    public function registerAjax()
    {

    }

    protected function getTilesHelpInfo(){
        return array(
            array(
                'title' => __('Getting started', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('Plugins installation, activation and updates.', 'advanced-dynamic-pricing-for-woocommerce'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/getting-started/'),
            ),
            array(
                'title' => __('Rules List', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('Describe settings of the rules list, talking about rules priority.', 'advanced-dynamic-pricing-for-woocommerce'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/rules-list/'),
            ),
            array(
                'title' => __('Need more settings? Go to PRO'),
                'description' => __('Difference between free and pro.'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/go-to-pro/')
            ),
            array(
                'title' => __('Creating a Rule', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('How to add a new rule.', 'advanced-dynamic-pricing-for-woocommerce'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/creating-a-rule/')
            ),
            array(
                'title' => __('Rules Settings', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('More detailed about applying of the sections/rules, as a coupon.'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/rules-settings/')
            ),
            array(
                'title' => __('Settings', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('Detailed information about plugin settings', 'advanced-dynamic-pricing-for-woocommerce'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/settings/')
            ),
            array(
                'title' => __('Frequently Created Rules', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('How to create a rule for the most popular discounts scenario.', 'advanced-dynamic-pricing-for-woocommerce'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/frequently-created-rules/')
            ),
            array(
                'title' => __('Rules Sections', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('Detailed information about every rule sections: from Product filter to Limits.', 'advanced-dynamic-pricing-for-woocommerce'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/rules-sections/')
            ),
            array(
                'title' => __('Tools', 'advanced-dynamic-pricing-for-woocommerce'),
                'description' => __('More detailed about Tools tab.', 'advanced-dynamic-pricing-for-woocommerce'),
                'link' => esc_url('https://docs.algolplus.com/algol_pricing/tools-free/')
            ),
        );
    }
}
