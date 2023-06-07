<?php

namespace ADP\BaseVersion\Includes\AdminExtensions;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminTabInterface;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Tabs\Options;

defined('ABSPATH') or exit;

class AdminPage
{
    const SLUG = 'wdp_settings';
    const TAB_REQUEST_KEY = 'tab';

    /**
     * @var AdminTabInterface[]
     */
    protected $tabs;

    /**
     * @var AdminTabInterface
     */
    protected $currentTab;

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

    public function register()
    {
        $this->prepareTabs();
        $this->sortTabsByPriority();
        $this->detectCurrentTab();

        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
        add_filter('script_loader_src', array($this, 'doNotLoadExternalSelect2'), PHP_INT_MAX, 2);
        add_filter('script_loader_src', array($this, 'doNotLoadExternalPostbox'), PHP_INT_MAX, 2);
    }

    public function registerAjax()
    {
        $this->prepareTabs();
        foreach ($this->tabs as $tab) {
            $tab->registerAjax();
        }
    }

    public function registerPage()
    {
        add_action('admin_menu', function () {
            add_submenu_page(
                'woocommerce',
                __('Pricing Rules', 'advanced-dynamic-pricing-for-woocommerce'),
                __('Pricing Rules', 'advanced-dynamic-pricing-for-woocommerce'),
                'manage_woocommerce',
                self::SLUG,
                array($this, 'showAdminPage'));
        });
    }

    public function showAdminPage()
    {
        $this->currentTab->handleSubmitAction();

        $tabs        = $this->tabs;
        $current_tab = $this->currentTab;
        include WC_ADP_PLUGIN_VIEWS_PATH . 'admin_page/admin_page.php';
    }

    public function renderCurrentTab()
    {
        $viewVariables = $this->currentTab->getViewVariables();
        if (is_array($viewVariables)) {
            extract($viewVariables);
        }

        $tabHandler = $this->currentTab;
        include WC_ADP_PLUGIN_VIEWS_PATH . $tabHandler::getRelativeViewPath();
    }

    public function enqueueScripts()
    {
        if ($this->context->is($this->context::ADP_PLUGIN_PAGE)) {
            wp_dequeue_style('wwp-style');
        }

        $currentTab    = $this->currentTab;
        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";

        // Enqueue script for handling the meta boxes
        wp_enqueue_script('wdp_postbox', $baseVersionUrl . 'assets/js/postbox.js',
            array('jquery', 'jquery-ui-sortable'), WC_ADP_VERSION);

        // jQuery UI Datepicker
        wp_enqueue_script('jquery-ui-datepicker');

        // jQuery UI Datepicker styles
        wp_enqueue_style('wdp_jquery-ui', $baseVersionUrl . 'assets/jquery-ui/jquery-ui.min.css', array(), '1.11.4');

        // Enqueue Select2 related scripts and styles
        wp_enqueue_script('wdp_select2', $baseVersionUrl . 'assets/js/select2/select2.full.min.js', array('jquery'),
            '4.0.3');
        wp_enqueue_style('wdp_select2', $baseVersionUrl . 'assets/css/select2/select2.css', array(), '4.0.3');

        if ($currentTab::getKey() !== Options::getKey()) {
            // Enqueue jquery mobile related scripts and styles (for flip switch)
            // styles below are replacing option sections styles
            wp_enqueue_script('jquery-mobile-scripts',
                $baseVersionUrl . 'assets/jquery.mobile/jquery.mobile.custom.min.js', array('jquery'));
            wp_enqueue_style('jquery-mobile-styles',
                $baseVersionUrl . 'assets/jquery.mobile/jquery.mobile.custom.structure.min.css');
            wp_enqueue_style('jquery-mobile-theme-styles',
                $baseVersionUrl . 'assets/jquery.mobile/jquery.mobile.custom.theme.css');
        }

        // Backend styles
        wp_enqueue_style('wdp_settings-styles', $baseVersionUrl . 'assets/css/settings.css', array(), WC_ADP_VERSION);

        // DateTime Picker
        wp_enqueue_script('wdp_datetimepicker-scripts',
            $baseVersionUrl . 'assets/datetimepicker/jquery.datetimepicker.full.min.js', array('jquery'));
        wp_enqueue_style('wdp_datetimepicker-styles',
            $baseVersionUrl . 'assets/datetimepicker/jquery.datetimepicker.min.css', array());


        $this->currentTab->enqueueScripts();
    }

    protected function detectCurrentTab()
    {
        $currentTabKey = null;

        if (isset($_REQUEST[self::TAB_REQUEST_KEY])) {
            $currentTabKey = $_REQUEST[self::TAB_REQUEST_KEY];
        }

        if ( ! isset($this->tabs[$currentTabKey])) {
            $currentTabKey = key($this->tabs);
        }

        $this->currentTab = $this->tabs[$currentTabKey];
    }

    protected function prepareTabs()
    {
        $tabsNamespace = __NAMESPACE__ . "\AdminPage\Tabs\\";
        foreach (glob(dirname(__FILE__) . "/AdminPage/Tabs/*") as $filename) {
            $tab       = str_replace(".php", "", basename($filename));
            $classname = $tabsNamespace . $tab;

            if (class_exists($classname)) {
                $tabHandler = new $classname($this->context);
                /**
                 * @var $tabHandler AdminTabInterface
                 */

                $this->tabs[$tabHandler::getKey()] = $tabHandler;
            }
        }
    }

    protected function sortTabsByPriority()
    {
        uasort($this->tabs, function ($tab1, $tab2) {
            /**
             * @var $tab1 AdminTabInterface
             * @var $tab2 AdminTabInterface
             */

            if ($tab1::getHeaderDisplayPriority() <= $tab2::getHeaderDisplayPriority()) {
                return -1;
            } else {
                return 1;
            }
        });
    }

    public function doNotLoadExternalSelect2($src, $handle)
    {
        // don't load ANY select2.js / select2.min.js  and OUTDATED select2.full.js
        if ( ! preg_match('/\/select2\.full\.js\?ver=[1-3]/', $src) && ! preg_match('/\/select2\.min\.js/',
                $src) && ! preg_match('/\/select2\.js/', $src)) {
            return $src;
        }

        return "";
    }

    public function doNotLoadExternalPostbox($src, $handle)
    {

        if ( ! preg_match('/postbox\.?(min|full)?\.js(\?ver=[0-9.]+)?/', $src) || $handle === "wdp_postbox") {
            return $src;
        }

        return "";
    }
}
