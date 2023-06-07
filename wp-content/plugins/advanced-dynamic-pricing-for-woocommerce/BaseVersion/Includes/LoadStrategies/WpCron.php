<?php

namespace ADP\BaseVersion\Includes\LoadStrategies;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Engine;
use ADP\Factory;

defined('ABSPATH') or exit;

class WpCron implements LoadStrategy
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
         * Do not call @see wc_get_chosen_shipping_method_ids() in CRON.
         *
         * Sometimes we calculate product in CRON.
         * So, if we do this with a condition which calls @see CartTotals::getSubtotal() ('subtotal' for example),
         * we need to include wc_get_chosen_shipping_method_ids().
         * It is happening in @see \WooCommerce::frontend_includes() which is skip in CRON requests.
         *
         * Maybe you think that a forced call 'frontend_includes' during CRON is a good idea. No way ;)
         * Anyway, we do not need the chosen shipping methods during CRON. It does not make sense.
         *
         */
        add_filter('woocommerce_apply_base_tax_for_local_pickup', "__return_false");

        /**
         * @var Engine $engine
         */
        $engine = Factory::get("Engine", WC()->cart);

        // Should we install all price display hooks?
        $engine->installProductProcessorWithEmptyCart();

        /** @see Functions::install() */
        Factory::callStaticMethod("Functions", 'install');
    }
}
