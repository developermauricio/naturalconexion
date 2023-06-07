<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Tabs;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminTabInterface;

defined('ABSPATH') or exit;

class Collections implements AdminTabInterface
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

    public static function getRelativeViewPath()
    {
        return 'admin_page/tabs/collections.php';
    }

    public static function getHeaderDisplayPriority()
    {
        return 60;
    }

    public static function getKey()
    {
        return 'product_collections';
    }

    public static function getTitle()
    {
        return __('Product Collections', 'advanced-dynamic-pricing-for-woocommerce') . "&nbsp;&#x1f512;";
    }

    public function getViewVariables()
    {
        return array();
    }

    public function enqueueScripts()
    {
    }

    public function registerAjax()
    {

    }
}
