<?php

namespace ADP\BaseVersion\Includes\ProductExtensions;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class ProductExtensions
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

    public function replaceWcProductFactory()
    {
        if ( did_action('woocommerce_init') ) {
            WC()->product_factory = new PricingProductFactory();
        }
    }
}
