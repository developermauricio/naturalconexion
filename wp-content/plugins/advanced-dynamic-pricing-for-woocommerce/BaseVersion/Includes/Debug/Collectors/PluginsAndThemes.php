<?php

namespace ADP\BaseVersion\Includes\Debug\Collectors;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class PluginsAndThemes
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

    public function collect()
    {
        return array(
            'plugins' => $this->getAllPlugins(),
            'theme'   => $this->getThemeInfo(),
        );
    }

    /**
     * Get all plugins grouped into activated or not.
     * Copied from WC_Tracker
     *
     * @return array
     * @see WC_Tracker
     *
     */
    private function getAllPlugins()
    {
        // Ensure get_plugins function is loaded.
        if ( ! function_exists('get_plugins')) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $plugins             = get_plugins();
        $active_plugins_keys = get_option('active_plugins', array());
        $active_plugins      = array();

        foreach ($plugins as $k => $v) {
            // Take care of formatting the data how we want it.
            $formatted         = array();
            $formatted['name'] = strip_tags($v['Name']);
            if (isset($v['Version'])) {
                $formatted['version'] = strip_tags($v['Version']);
            }
            if (isset($v['Author'])) {
                $formatted['author'] = strip_tags($v['Author']);
            }
            if (isset($v['Network'])) {
                $formatted['network'] = strip_tags($v['Network']);
            }
            if (isset($v['PluginURI'])) {
                $formatted['plugin_uri'] = strip_tags($v['PluginURI']);
            }
            if (in_array($k, $active_plugins_keys)) {
                // Remove active plugins from list so we can show active and inactive separately.
                unset($plugins[$k]);
                $active_plugins[$k] = $formatted;
            } else {
                $plugins[$k] = $formatted;
            }
        }

        return array(
            'active_plugins'   => $active_plugins,
            'inactive_plugins' => $plugins,
        );
    }

    /**
     * Get the current theme info, theme name and version.
     * Copied from WC_Tracker
     *
     * @return array
     * @see WC_Tracker
     *
     */
    protected function getThemeInfo()
    {
        $currentTheme = $this->context->getCurrentTheme();

        return array(
            'name'        => $currentTheme->get("Name"),
            'version'     => $currentTheme->get("Version"),
            'child_theme' => $currentTheme->get_stylesheet() !== $currentTheme->get_template(),
            'wc_support'  => current_theme_supports('woocommerce'), // nothing we can do with this for now :(
        );
    }
}
