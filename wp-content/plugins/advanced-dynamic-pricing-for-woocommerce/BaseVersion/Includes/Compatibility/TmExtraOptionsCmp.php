<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItemAddon;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined('ABSPATH') or exit;

/**
 * Plugin Name: WooCommerce TM Extra Product Options
 * Author: ThemeComplete
 */
class TmExtraOptionsCmp
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

    public function isActive()
    {
        return defined('THEMECOMPLETE_EPO_PLUGIN_FILE');
    }

    /**
     * @param WcCartItemFacade $wcCartItemFacade
     *
     * @return array<int, CartItemAddon>
     */
    public function getAddonsFromCartItem(WcCartItemFacade $wcCartItemFacade)
    {
        $thirdPartyData = $wcCartItemFacade->getThirdPartyData();
        $addonsData     = $thirdPartyData['tmcartepo'] ?? [];

        $addons = [];
        foreach ($addonsData as $addonData) {
            $key   = $addonData['name'] ?? null;
            $value = $addonData['value'] ?? null;
            $price = $addonData['price'] ?? null;

            if ($key === null || $value === null || $price === null) {
                continue;
            }

            if (is_string($price)) {
                $price = str_replace($this->context->priceSettings->getThousandSeparator(), "", $price);
                $price = str_replace($this->context->priceSettings->getDecimalSeparator(), ".", $price);
                $price = (float)$price;
            }

            $addon           = new CartItemAddon($key, $value, $price);
            $addon->currency = $wcCartItemFacade->getCurrency();

            $addons[] = $addon;
        }

        return $addons;
    }

    public function removeKeysFromFreeCartItem(WcCartItemFacade $wcCartItemFacade)
    {
        $wcCartItemFacade->deleteThirdPartyData("tmhasepo");
        $wcCartItemFacade->deleteThirdPartyData("tmcartepo");
        $wcCartItemFacade->deleteThirdPartyData("tmcartfee");
        $wcCartItemFacade->deleteThirdPartyData("tmpost_data");
        $wcCartItemFacade->deleteThirdPartyData("tmdata");
        $wcCartItemFacade->deleteThirdPartyData("tm_cart_item_key");
        $wcCartItemFacade->deleteThirdPartyData("tm_epo_product_original_price");
        $wcCartItemFacade->deleteThirdPartyData("tm_epo_options_prices");
        $wcCartItemFacade->deleteThirdPartyData("tm_epo_product_price_with_options");
        $wcCartItemFacade->deleteThirdPartyData("associated_products_price");
    }

    public function removeKeysFromFreeWcCartItem(&$cartItemData)
    {
        unset($cartItemData["tmhasepo"]);
        unset($cartItemData["tmcartepo"]);
        unset($cartItemData["tmcartfee"]);
        unset($cartItemData["tmpost_data"]);
        unset($cartItemData["tmdata"]);
        unset($cartItemData["tm_cart_item_key"]);
        unset($cartItemData["tm_epo_product_original_price"]);
        unset($cartItemData["tm_epo_options_prices"]);
        unset($cartItemData["tm_epo_product_price_with_options"]);
        unset($cartItemData["associated_products_price"]);
    }
}
