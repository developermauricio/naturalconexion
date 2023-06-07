<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItemAddon;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Custom Product Addons
 * Author: WooCommerce
 *
 * @see https://woocommerce.com/products/product-add-ons/
 */
class WcProductAddonsCmp
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
        return defined('WC_PRODUCT_ADDONS_VERSION');
    }

    /**
     * @param WcCartItemFacade $wcCartItemFacade
     *
     * @return array<int, CartItemAddon>
     */
    public function getAddonsFromCartItem(WcCartItemFacade $wcCartItemFacade)
    {
        $thirdPartyData = $wcCartItemFacade->getThirdPartyData();
        $addonsData     = $thirdPartyData['addons'] ?? [];

        $addons = [];
        foreach ($addonsData as $addonData) {
            $key   = $addonData['name'] ?? null;
            $value = $addonData['value'] ?? null;
            $price = null;
            $priceType = $addonData['price_type'] ?? null;
            $addonPrice = $addonData['price'] ?? null;

            if (is_string($addonPrice)) {
                $addonPrice = str_replace($this->context->priceSettings->getThousandSeparator(), "", $addonPrice);
                $addonPrice = str_replace($this->context->priceSettings->getDecimalSeparator(), ".", $addonPrice);
                $addonPrice = (float)$addonPrice;
            }

            if ( $priceType === "percentage_based" ) {
                if ( $addonPrice !== null ) {
                    $price = (float) ( $this->getProductProtectedProp($wcCartItemFacade->getProduct(), 'price') * ( $addonPrice / 100 ) );
                }
            } else if ( $priceType === "flat_fee" ) {
                $price = (float) $addonPrice / $wcCartItemFacade->getQty();
            } else {
                $price = (float) $addonPrice;
            }

            if ($key === null || $value === null || $price === null) {
                continue;
            }

            $addon           = new CartItemAddon($key, $value, $price);
            $addon->currency = $wcCartItemFacade->getCurrency();

            $addons[] = $addon;
        }

        return $addons;
    }

    /**
     * @param \WC_Product $product
     * @param string $prop
     *
     * @return mixed|null
     */
    protected function getProductProtectedProp($product, $prop)
    {
        try {
            $reflection = new \ReflectionClass($product);
            $property   = $reflection->getProperty('data');
            $property->setAccessible(true);
        } catch (\ReflectionException $e) {
            $property = null;
        }

        return $property->getValue($product)[$prop] ?? null;
    }
}
