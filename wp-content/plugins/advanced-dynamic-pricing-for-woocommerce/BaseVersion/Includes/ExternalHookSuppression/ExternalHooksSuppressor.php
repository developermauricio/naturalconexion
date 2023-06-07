<?php

namespace ADP\BaseVersion\Includes\ExternalHookSuppression;

use ADP\BaseVersion\Includes\Context;
use ADP\Factory;
use ADP\HighLander\HighLander;
use ADP\HighLander\Queries\ClassMethodFilterQuery;
use ADP\HighLander\Queries\TagFilterQuery;

defined('ABSPATH') or exit;

class ExternalHooksSuppressor
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

    public function registerHookSuppressor()
    {
        add_action("wp_loaded", array($this, 'removeExternalHooks'));
    }

    public function removeExternalHooks()
    {
        $allowedHooks = [
            //Filters
            "woocommerce_get_price_html"            => [
                [Factory::getClassName('PriceDisplay_PriceDisplay'), "hookPriceHtml"],
                [
                    Factory::getClassName('CartProcessor_FreeAutoAddItemsController'),
                    "hookModifyPriceHtmlWhenSelectGift",
                ],
            ],
            "woocommerce_product_is_on_sale"        => [
                [Factory::getClassName('PriceDisplay_PriceDisplay'), "hookIsOnSale"],
                [Factory::getClassName('PriceDisplay_PriceDisplay'), "hookIsOnSaleReplacement"],
            ],
            "woocommerce_product_get_sale_price"    => [
                [Factory::getClassName('PriceDisplay_PriceDisplay'), "hookGetSalePrice"],
            ],
            "woocommerce_product_get_regular_price" => [
                [Factory::getClassName('PriceDisplay_PriceDisplay'), "hookGetRegularPrice"],
            ],
            "woocommerce_variable_price_html"       => [],
            "woocommerce_variable_sale_price_html"  => [],
            "woocommerce_cart_item_price"           => [
                [Factory::getClassName('WC_WcCartItemDisplayExtensions'), "wcCartItemPrice"],
            ],
            "woocommerce_cart_item_subtotal"        => [
                [Factory::getClassName('WC_WcCartItemDisplayExtensions'), "wcCartItemSubtotal"],
                [
                    Factory::getClassName('Advertising_AdvertisingMessage_AdvertisingMessageDisplay'),
                    "showMessageAtCartPageForItem",
                ],
            ],
            //Actions
            "woocommerce_checkout_order_processed"  => [
                [Factory::getClassName('StatsCollector_WcCartStatsCollector'), "checkoutOrderProcessed"],
            ],
            "woocommerce_before_calculate_totals"   => [
                [Factory::getClassName('LoadStrategies_RestApi'), "initProcessActionIfCartWasLoaded"],
            ], //nothing allowed!
        ];

        $highLander = new HighLander();
        $queries    = array();

        $tagQuery = new TagFilterQuery();
        $tagQuery->setList(array_keys($allowedHooks))->setAction($tagQuery::ACTION_REMOVE_ALL_IN_TAG);
        $queries[] = $tagQuery;

        foreach ($allowedHooks as $tag => $hooks) {
            $query = new ClassMethodFilterQuery();
            $query->setList($hooks)->setAction($query::ACTION_SAVE)->useTag($tag);

            $queries[] = $query;
        }
        $highLander->setQueries($queries);

        $highLander->execute();
    }
}
