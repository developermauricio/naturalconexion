<?php

namespace ADP\BaseVersion\Includes\LoadStrategies;

use ADP\BaseVersion\Includes\AdminExtensions\AdminPage;
use ADP\BaseVersion\Includes\AdminExtensions\MetaBoxes;
use ADP\BaseVersion\Includes\AdminExtensions\WcOrderPreviewExtensions;
use ADP\BaseVersion\Includes\AdminExtensions\WcProductPageExtensions;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Updater\Updater;
use ADP\Factory;

defined('ABSPATH') or exit;

class AdminCommon implements LoadStrategy
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function start()
    {
        Updater::update();

        /**
         * @var AdminPage $adminPage
         */
        $adminPage = Factory::get('AdminExtensions_AdminPage');
        $adminPage->registerPage();
        if ($this->context->isPluginAdminPage()) {
            $adminPage->register();
        }

        $this->context->adminNotice->register();

        $wcProductPageExt = new WcProductPageExtensions();
        $wcProductPageExt->register();

        $metaBoxes = new MetaBoxes();
        $metaBoxes->register();

        $orderPreview = new WcOrderPreviewExtensions();
        $orderPreview->register();

        /** @see Functions::install() */
        Factory::callStaticMethod("Functions", 'install');
    }
}
