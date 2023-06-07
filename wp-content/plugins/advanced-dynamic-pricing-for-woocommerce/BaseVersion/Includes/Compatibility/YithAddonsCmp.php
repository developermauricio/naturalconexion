<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItemAddon;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined('ABSPATH') or exit;

/**
 * Plugin Name: YITH WooCommerce Product Add-ons & Extra Options
 * Author: YITH
 *
 * @see https://wordpress.org/plugins/yith-woocommerce-product-add-ons/
 */
class YithAddonsCmp
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
        return defined('YITH_WAPO');
    }

    /**
     * @param WcCartItemFacade $wcCartItemFacade
     *
     * @return array<int, CartItemAddon>
     * @see \YITH_WAPO_Cart::add_cart_item
     */
    public function getAddonsFromCartItem(WcCartItemFacade $wcCartItemFacade)
    {
        $thirdPartyData = $wcCartItemFacade->getThirdPartyData();
        $addonsData = $thirdPartyData['yith_wapo_options'] ?? [];

        $_product = $wcCartItemFacade->getProduct();
        // WooCommerce Measurement Price Calculator (compatibility).
        if (isset($cart_item['pricing_item_meta_data']['_price'])) {
            $product_price = $cart_item['pricing_item_meta_data']['_price'];
        } else {
            $product_price = \yit_get_display_price($_product);
        }

        $addon_id_check = '';
        $addons = [];
        foreach ($addonsData as $index => $addonData) {
            foreach ($addonData as $key => $value) {
                if ($key && '' !== $value) {
                    $value = stripslashes($value);
                    $explode = explode('-', $key);
                    if (isset($explode[1])) {
                        $addon_id = $explode[0];
                        $option_id = $explode[1];
                    } else {
                        $addon_id = $key;
                        $option_id = $value;
                    }

                    if ($addon_id != $addon_id_check) {
                        $first_free_options_count = 0;
                        $addon_id_check = $addon_id;
                    }

                    $info = \yith_wapo_get_option_info($addon_id, $option_id);

                    if ('percentage' === $info['price_type']) {
                        $option_percentage = floatval($info['price']);
                        $option_percentage_sale = floatval($info['price_sale']);
                        $option_price = ($product_price / 100) * $option_percentage;
                        $option_price_sale = ($product_price / 100) * $option_percentage_sale;
                    } elseif ('multiplied' === $info['price_type']) {
                        $option_price = (float)$info['price'] * (float)$value;
                        $option_price_sale = (float)$info['price_sale'] * (float)$value;
                    } elseif ('characters' === $info['price_type']) {
                        $remove_spaces = apply_filters('yith_wapo_remove_spaces', false);
                        $value = $remove_spaces ? str_replace(' ', '', $value) : $value;
                        $option_price = (float)$info['price'] * strlen($value);
                        $option_price_sale = (float)$info['price_sale'] * strlen($value);
                    } else {
                        $option_price = (float)$info['price'];
                        $option_price_sale = (float)$info['price_sale'];
                    }

                    if ('number' === $info['addon_type']) {
                        if ('value_x_product' === $info['price_method']) {
                            $option_price = $value * $product_price;
                        } else {
                            if ('multiplied' === $info['price_type']) {
                                $option_price = $value * $info['price'];
                                $option_price_sale = 0; // By default 0, since sale price doesn't exists.
                            }
                        }
                    }

                    if ('free' === $info['price_method']) {
                        $option_price = 0;
                        $option_price_sale = 0;
                    }

                    $addon = new CartItemAddon($key, $value, $option_price);
                    $addon->currency = $wcCartItemFacade->getCurrency();
                    $addons[] = $addon;
                }
            }
        }

        return $addons;
    }
}
