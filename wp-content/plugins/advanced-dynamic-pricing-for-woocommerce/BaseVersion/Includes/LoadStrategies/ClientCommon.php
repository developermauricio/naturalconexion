<?php

namespace ADP\BaseVersion\Includes\LoadStrategies;

use ADP\BaseVersion\Includes\Advertising\DiscountMessage;
use ADP\BaseVersion\Includes\CartExtensions\CartExtensions;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\CustomizerExtensions\CustomizerExtensions;
use ADP\BaseVersion\Includes\Database\Repository\OrderItemRepository;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Debug\AdminBounceBack;
use ADP\BaseVersion\Includes\Debug\CalculationProfiler;
use ADP\BaseVersion\Includes\Debug\DebugBar;
use ADP\BaseVersion\Includes\Engine;
use ADP\BaseVersion\Includes\ExternalHookSuppression\ExternalHooksSuppressor;
use ADP\BaseVersion\Includes\SEO\StructuredData;
use ADP\BaseVersion\Includes\Shortcodes\BogoProducts as BogoProductsShortCode;
use ADP\BaseVersion\Includes\Shortcodes\CategoryRangeDiscountTableShortcode;
use ADP\BaseVersion\Includes\Shortcodes\OnSaleProducts as OnSaleProductsShortCode;
use ADP\BaseVersion\Includes\Shortcodes\ProductRangeDiscountTableShortcode;
use ADP\BaseVersion\Includes\StatsCollector\WcCartStatsCollector;
use ADP\BaseVersion\Includes\VolumePricingTable\RangeDiscountTableDisplay;
use ADP\BaseVersion\Includes\WC\WcProductCustomAttributesCache;
use ADP\Factory;

defined('ABSPATH') or exit;

class ClientCommon implements LoadStrategy
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Engine
     */
    protected $engine;

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
        /**
         * @var CustomizerExtensions $customizer
         * @var DiscountMessage $discountMessage
         * @var Engine $engine
         * @var ExternalHooksSuppressor $hookSuppressor
         * @var StructuredData $structuredData
         */
        $customizer      = Factory::get("CustomizerExtensions_CustomizerExtensions");
        $discountMessage = Factory::get("Advertising_DiscountMessage", $customizer);
        $engine          = Factory::get("Engine", WC()->cart);
        $structuredData  = Factory::get("SEO_StructuredData", $engine);
        $structuredData->install();
        $hookSuppressor  = new ExternalHooksSuppressor();

        $this->engine = $engine;

        $discountTable = new RangeDiscountTableDisplay($customizer);
        ProductRangeDiscountTableShortcode::register($customizer);
        CategoryRangeDiscountTableShortcode::register($customizer);

        $customizer->register();

        if ($this->context->getOption('support_shortcode_products_on_sale')) {
            /** @see OnSaleProductsShortCode::register() */
            Factory::callStaticMethod("Shortcodes_OnSaleProducts", 'register');
        }

        if ($this->context->getOption('support_shortcode_products_bogo')) {
            /** @see BogoProductsShortCode::register() */
            Factory::callStaticMethod("Shortcodes_BogoProducts", 'register');
        }

        if ($this->context->getOption('suppress_other_pricing_plugins')) {
            $hookSuppressor->registerHookSuppressor();
        }

        $wcCartStatsCollector = new WcCartStatsCollector();
        $wcCartStatsCollector->setActionCheckoutOrderProcessed();

        $engine->installCartProcessAction();
        if (is_super_admin($this->context->getCurrentUser()->ID)) {
            $profiler = $engine->getProfiler();
            $this->installReportAdminBounceBackAction($profiler);
            if ($this->context->getOption("show_debug_bar")) {
                $profiler->installActionCollectReport();
                $this->installDebugBar($profiler);
            }
        }

        $discountMessage->setThemeOptions($customizer);
        $discountTable->installRenderHooks();

        $cartExtensions = new CartExtensions();
        $cartExtensions->hideCouponWordInTotals();
        $cartExtensions->removeDeleteLinkForAdpCoupons();
        $cartExtensions->attachCssClassToGiftedCartItems();
        $cartExtensions->fillCartItemWhenOrderAgain();
        $cartExtensions->forceCartUpdateForShipping();

        /** @see Functions::install() */
        Factory::callStaticMethod("Functions", 'install', $engine);

        /** @var WcProductCustomAttributesCache $productAttributesCache */
        $productAttributesCache  = Factory::get("WC_WcProductCustomAttributesCache");
        $productAttributesCache->installHooks();
    }

    /**
     * @param CalculationProfiler $profiler
     */
    public function installDebugBar(CalculationProfiler $profiler)
    {
        /** @var DebugBar $debugBar */
        $debugBar = Factory::get("Debug_DebugBar", $profiler);

        $debugBar->register_assets();
        $debugBar->install_action_to_render_bar_templates();
        $debugBar->installActionToAddIframe();
        $debugBar->installActionToRenderBar();
    }

    public function installReportAdminBounceBackAction(CalculationProfiler $profiler)
    {
        /** @var AdminBounceBack $adminBounceBack */
        $adminBounceBack = Factory::get("Debug_AdminBounceBack", $profiler);
        $adminBounceBack->catchBounceEvent();
    }
}
