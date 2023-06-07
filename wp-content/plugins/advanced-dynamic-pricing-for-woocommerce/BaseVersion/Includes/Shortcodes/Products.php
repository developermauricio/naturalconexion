<?php

namespace ADP\BaseVersion\Includes\Shortcodes;

use ADP\BaseVersion\Includes\Context;
use WC_Shortcode_Products;

defined('ABSPATH') or exit;

abstract class Products extends WC_Shortcode_Products
{
    const NAME = '';
    const STORAGE_KEY = '';

    /**
     * @var Context
     */
    protected $context;

    public static function register()
    {
        add_shortcode(static::NAME, function ($atts) {
            return static::create($atts);
        });
    }

    public function __construct($attributes = array(), $type = 'products')
    {
        $this->context = adp_context();
        parent::__construct($attributes, $type);
    }

    /**
     * @param array|string $atts
     * @param Context $context
     *
     * @return string
     */
    public static function create($atts)
    {

        // apply legacy [sale_products] attributes
        $atts = array_merge(array(
            'limit'        => '12',
            'columns'      => '4',
            'orderby'      => 'title',
            'order'        => 'ASC',
            'category'     => '',
            'cat_operator' => 'IN',
        ), (array)$atts);

        $shortcode = new static($atts, static::NAME);

        return $shortcode->get_content();
    }

    /**
     * @param null $deprecated
     *
     * @return mixed
     */
    public static function getCachedProductsIds($deprecated = null)
    {

        // Load from cache.
        $productIds = get_transient(static::STORAGE_KEY);

        // Valid cache found.
        if (false !== $productIds) {
            return $productIds;
        }

        return static::updateCachedProductsIds();
    }

    /**
     * @param null $deprecated
     *
     * @return mixed
     */
    public static function updateCachedProductsIds($deprecated = null)
    {

        $product_ids = static::getProductsIds();

        set_transient(static::STORAGE_KEY, $product_ids, DAY_IN_SECONDS * 30);

        return $product_ids;
    }

    public static function cachedProductsCount()
    {
        return count(static::getProductsIds());
    }

    public static function clearCache()
    {
        delete_transient(static::STORAGE_KEY);
    }

    public static function partialUpdateCachedProductsIds($from, $count)
    {
        $result     = static::getProductsIds($from, $count);
        $productIds = get_transient(static::STORAGE_KEY);
        $productIds = $productIds ? array_merge($productIds, $result) : $result;

        set_transient(static::STORAGE_KEY, $productIds, DAY_IN_SECONDS * 30);

        return $productIds;
    }

}
