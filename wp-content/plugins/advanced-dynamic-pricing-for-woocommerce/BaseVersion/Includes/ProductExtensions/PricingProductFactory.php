<?php

namespace ADP\BaseVersion\Includes\ProductExtensions;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class PricingProductFactory extends \WC_Product_Factory
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct()
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get a product.
     *
     * @param \WC_Product|\WP_Post|int|bool $product_id Product instance, post instance, numeric or false to use global $post.
     * @param array $deprecated Previously used to pass arguments to the factory, e.g. to force a type.
     *
     * @return \WC_Product|bool Product object or false if the product cannot be loaded.
     */
    public function get_product($product_id = false, $deprecated = array())
    {
        $product = parent::get_product($product_id, $deprecated);

        if ($product === false) {
            return $product;
        }

        $variationAttributes = $product instanceof \WC_Product_Variation ? $product->get_variation_attributes() : array();

        $productExt = new ProductExtension($this->context, $product);
        $productExt->setCustomPrice(
            apply_filters("adp_product_get_price", null, $product, $variationAttributes, 1, array(), null)
        );

        return $product;
    }
}
