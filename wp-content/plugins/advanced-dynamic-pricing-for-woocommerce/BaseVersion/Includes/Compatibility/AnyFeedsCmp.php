<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Yoast SEO
 * Author: Yoast
 *
 * @see https://yoast.com/wordpress/plugins/yoast-woocommerce-seo/
 */
class AnyFeedsCmp
{
    /**
     * @return bool
     */
    public function isActive()
    {
        return defined("WOOCOMMERCESEA_PLUGIN_VERSION")
               || class_exists("WC_Facebookcommerce"); // part of FacebookCommerceCmp
    }

    public function updateContext(Context $context)
    {
        $context->getSettings()->set("update_prices_while_doing_cron", true);
    }
}
