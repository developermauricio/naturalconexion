<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItemAddon;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Flexible Product Fields PRO WooCommerce
 * Author: WP Desk
 */
class FlexibleProductFieldsCmp
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
        return defined('FLEXIBLE_PRODUCT_FIELDS_PRO_VERSION');
    }

    /**
     * @param WcCartItemFacade $wcCartItemFacade
     *
     * @return array<int, CartItemAddon>
     */
    public function getAddonsFromCartItem(WcCartItemFacade $wcCartItemFacade)
    {
        $thirdPartyData = $wcCartItemFacade->getThirdPartyData();
        $addonsData     = $thirdPartyData['flexible_product_fields'] ?? [];

        $addons = [];
        foreach ($addonsData as $addonData) {
            $key   = $addonData['name'] ?? null;
            $value = $addonData['value'] ?? null;
            $price = $addonData['price'] ?? null;

            if ($key === null || $value === null || $price === null) {
                continue;
            }

            $addon           = new CartItemAddon($key, $value, $price);
            $addon->currency = $wcCartItemFacade->getCurrency();

            $addons[] = $addon;
        }

        return $addons;
    }

}
