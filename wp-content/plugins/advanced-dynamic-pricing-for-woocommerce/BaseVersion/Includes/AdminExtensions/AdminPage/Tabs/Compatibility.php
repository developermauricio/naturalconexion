<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Tabs;

use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminTabInterface;
use ADP\BaseVersion\Includes\Context;

class Compatibility implements AdminTabInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @inheritDoc
     */
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

    public function registerAjax()
    {

    }

    public function enqueueScripts()
    {

    }

    /**
     * @inheritDoc
     */
    public function getViewVariables()
    {
        return array();
    }

    /**
     * @inheritDoc
     */
    public static function getHeaderDisplayPriority()
    {
        return 150;
    }

    /**
     * @inheritDoc
     */
    public static function getRelativeViewPath()
    {
        return 'admin_page/tabs/compatibility.php';
    }

    /**
     * @inheritDoc
     */
    public static function getKey()
    {
        return 'compatibility';
    }

    /**
     * @inheritDoc
     */
    public static function getTitle()
    {
        return __('Compatibility', 'advanced-dynamic-pricing-for-woocommerce') . "&nbsp;&#x1f512;";
    }
}
