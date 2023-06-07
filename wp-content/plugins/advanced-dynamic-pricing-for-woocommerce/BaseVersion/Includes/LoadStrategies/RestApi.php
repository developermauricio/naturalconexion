<?php

namespace ADP\BaseVersion\Includes\LoadStrategies;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Repository\OrderItemRepository;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use ADP\BaseVersion\Includes\Engine;
use ADP\BaseVersion\Includes\WC\WcProductCustomAttributesCache;
use ADP\BaseVersion\Includes\StatsCollector\WcCartStatsCollector;
use ADP\Factory;

defined('ABSPATH') or exit;

class RestApi implements LoadStrategy
{
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

    public function start()
    {
        /**
         * We do not need this if "WooCommerce Blocks" < 2.6.0 is installed.
         * In future versions method "maybe_init_cart_session" has been removed.
         * @see https://github.com/woocommerce/woocommerce-gutenberg-products-block/commit/5a195cf105133e5b3ac232cfb469ed5c53a3d4bc#diff-17c1ab7a1ea1f97171811713b2a886c1
         *
         * @see WpCron::start() explanation here!
         */
        add_filter('woocommerce_apply_base_tax_for_local_pickup', "__return_false");

        /**
         * @var Engine $engine
         */
        $engine = Factory::get("Engine", WC()->cart);

        // Should we install all price display hooks?
        $engine->installProductProcessorWithEmptyCart();

        $wcCartStatsCollector = new WcCartStatsCollector();
        $wcCartStatsCollector->setActionCheckoutOrderProcessedDuringRestApi();

        add_action('woocommerce_before_calculate_totals', array($this, 'initProcessActionIfCartWasLoaded'), 10, 1);

        /** @see Functions::install() */
        Factory::callStaticMethod("Functions", 'install');

        /** @var WcProductCustomAttributesCache $productAttributesCache */
        $productAttributesCache  = Factory::get("WC_WcProductCustomAttributesCache");
        $productAttributesCache->installHooks();
    }

    public function initProcessActionIfCartWasLoaded($wcCart)
    {
        /** @var Engine $engine */
        $engine = Factory::get("Engine", $wcCart);
        $engine->getCartProcessor()->installActionFirstProcess();
        $engine->firstTimeProcessCart();

        remove_action('woocommerce_before_calculate_totals', array($this, 'initProcessActionIfCartWasLoaded'), 10);
    }
}
