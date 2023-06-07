<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItemAddon;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

/**
 * Plugin Name: WooCommerce Extra Product Options Pro
 * Author: ThemeHigh
 *
 * @see https://themehigh.com/product/woocommerce-extra-product-options
 * @see https://wordpress.org/plugins/woo-extra-product-options/
 */
class ThemehighExtraOptionsProCmp
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
        return defined('THWEPO_FILE');
    }

    /**
     * @param WcCartItemFacade $wcCartItemFacade
     *
     * @return array<int, CartItemAddon>
     */
    public function getAddonsFromCartItem(WcCartItemFacade $wcCartItemFacade)
    {
        $thirdPartyData = $wcCartItemFacade->getThirdPartyData();
        $addonsData     = $thirdPartyData['thwepo_options'] ?? [];

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
}
