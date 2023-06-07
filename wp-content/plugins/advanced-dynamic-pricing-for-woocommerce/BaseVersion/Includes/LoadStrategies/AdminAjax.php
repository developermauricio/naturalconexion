<?php

namespace ADP\BaseVersion\Includes\LoadStrategies;

use ADP\BaseVersion\Includes\AdminExtensions\AdminPage;
use ADP\BaseVersion\Includes\AdminExtensions\Ajax;
use ADP\BaseVersion\Includes\AdminExtensions\MetaBoxes;
use ADP\BaseVersion\Includes\AdminExtensions\WcOrderPreviewExtensions;
use ADP\BaseVersion\Includes\AdminExtensions\WcProductPageExtensions;
use ADP\BaseVersion\Includes\Advertising\DiscountMessage;
use ADP\BaseVersion\Includes\CartExtensions\CartExtensions;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\CustomizerExtensions\CustomizerExtensions;
use ADP\BaseVersion\Includes\Database\Repository\OrderItemRepository;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Debug\ReporterAjax;
use ADP\BaseVersion\Includes\Engine;
use ADP\BaseVersion\Includes\PriceDisplay\PriceAjax;
use ADP\BaseVersion\Includes\Shortcodes\CategoryRangeDiscountTableShortcode;
use ADP\BaseVersion\Includes\Shortcodes\ProductRangeDiscountTableShortcode;
use ADP\BaseVersion\Includes\StatsCollector\WcCartStatsCollector;
use ADP\BaseVersion\Includes\VolumePricingTable\RangeDiscountTableAjax;
use ADP\BaseVersion\Includes\WC\WcProductCustomAttributesCache;
use ADP\Factory;

defined('ABSPATH') or exit;

class AdminAjax implements LoadStrategy
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function start()
    {
        /**
         * @var CustomizerExtensions $customizer
         * @var DiscountMessage $discountMessage
         * @var AdminPage $adminPage
         * @var Engine $engine
         */
        $customizer      = Factory::get("CustomizerExtensions_CustomizerExtensions");
        $discountMessage = Factory::get("Advertising_DiscountMessage", $customizer);
        $adminPage       = Factory::get('AdminExtensions_AdminPage');
        $engine          = Factory::get("Engine", WC()->cart);

        $this->engine = $engine;

        $priceAjax = new PriceAjax($engine);

        $adminPage->registerAjax();

        /** @var $ajax Ajax */
        $ajax = Factory::get('AdminExtensions_Ajax');
        $ajax->register();

        $tableAjax = new RangeDiscountTableAjax($customizer);
        $tableAjax->register();
        if ( ! $this->context->is(Context::CUSTOMIZER)) {
            $discountMessage->setThemeOptions($customizer);
        }

        $this->context->adminNotice->register();

        $wcProductPageExt = new WcProductPageExtensions();
        $wcProductPageExt->register();

        /** Registering "CartExtensions" is really necessary. Some themes load the cart using ajax. */
        $cartExtensions = new CartExtensions();
        $cartExtensions->hideCouponWordInTotals();
        $cartExtensions->removeDeleteLinkForAdpCoupons();

        $metaBoxes = new MetaBoxes();
        $metaBoxes->register();

        $orderPreview = new WcOrderPreviewExtensions();
        $orderPreview->register();

        $engine->installCartProcessAction();
        if (is_super_admin($this->context->getCurrentUser()->ID)) {
            $profiler     = $engine->getProfiler();
            $profilerAjax = new ReporterAjax($profiler);
            $profilerAjax->register();
            if ($this->context->getOption("show_debug_bar")) {
                $profiler->installActionCollectReport();
            }
        }

        $priceAjax->register();

        $wcCartStatsCollector = new WcCartStatsCollector();
        $wcCartStatsCollector->setActionCheckoutOrderProcessed();

        if ($this->context->getOption('update_cross_sells')) {
            add_filter('woocommerce_add_to_cart_fragments', array($this, 'woocommerceAddToCartFragments'), 10, 2);
        }

        /** Register shortcodes for quick view */
        ProductRangeDiscountTableShortcode::register($customizer);
        CategoryRangeDiscountTableShortcode::register($customizer);

        /** @see Functions::install() */
        Factory::callStaticMethod("Functions", 'install', $engine);

        /** @var WcProductCustomAttributesCache $productAttributesCache */
        $productAttributesCache  = Factory::get("WC_WcProductCustomAttributesCache");
        $productAttributesCache->installHooks();
    }

    public function woocommerceAddToCartFragments($fragments)
    {
        /**
         * Fix incorrect add-to-cart url in cross-sells elements.
         * We need to remove "wc-ajax" argument because WC_Product children in method add_to_cart_url() use
         * add_query_arg() with current url.
         * Do not forget to set current url to cart_url.
         */
        $_SERVER['REQUEST_URI'] = remove_query_arg('wc-ajax', wc_get_cart_url());

        ob_start();
        woocommerce_cross_sell_display();
        $text = trim(ob_get_clean());
        if (empty($text)) {
            $text = '<div class="cross-sells"></div>';
        }
        $fragments['div.cross-sells'] = $text;

        return $fragments;
    }
}
