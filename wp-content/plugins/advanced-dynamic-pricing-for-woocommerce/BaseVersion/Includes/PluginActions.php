<?php

namespace ADP\BaseVersion\Includes;

use ADP\BaseVersion\Includes\AdminExtensions\AdminNotice;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage;
use ADP\BaseVersion\Includes\Database\Database;

defined('ABSPATH') or exit;

class PluginActions
{
    /**
     * @var string
     */
    protected $pluginFileFullPath;

    /**
     * @param string|null $pluginFileFullPath
     */
    public function __construct($pluginFileFullPath)
    {
        $this->pluginFileFullPath = $pluginFileFullPath;
    }

    /**
     *  Only a static class method or function can be used in an uninstall hook.
     */
    public function register()
    {
        if ($this->pluginFileFullPath && file_exists($this->pluginFileFullPath)) {
            register_activation_hook($this->pluginFileFullPath, array($this, 'install'));
            register_deactivation_hook($this->pluginFileFullPath, array($this, 'deactivate'));
            add_filter(
                'plugin_action_links_' . plugin_basename(WC_ADP_PLUGIN_PATH . WC_ADP_PLUGIN_FILE),
                array($this, 'settingsLink')
            );
        }
    }

    public function settingsLink($actions)
    {
        $settingsLink = sprintf(
            '<a href=%s>%s</a>',
            admin_url('admin.php?page=' . AdminPage::SLUG),
            __('Settings', 'advanced-dynamic-pricing-for-woocommerce')
        );
        array_unshift($actions, $settingsLink);

        return $actions;
    }

    public function singleInstall()
    {
        Database::createDatabase();
        do_action('wdp_install');
    }

    /** @param boolean $networkWide */
    public function install($networkWide)
    {
        global $wpdb;

        if (is_multisite() && $networkWide) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                $this->singleInstall();
                restore_current_blog();
            }
        } else {
            $this->singleInstall();
        }
    }

    public function deactivate()
    {
        AdminNotice::cleanUp();
    }

    /**
     * Method required for tests
     */
    public function uninstall()
    {
        $file = WC_ADP_PLUGIN_PATH . 'uninstall.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
}
