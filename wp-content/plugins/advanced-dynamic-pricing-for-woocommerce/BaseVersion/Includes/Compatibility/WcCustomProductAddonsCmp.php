<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItemAddon;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Woocommerce Custom Product Addons
 * Author: Acowebs
 *
 * @see https://acowebs.com/woo-custom-product-addons/
 */
class WcCustomProductAddonsCmp
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
        return defined('WCPA_VERSION');
    }

    /**
     * @param WcCartItemFacade $wcCartItemFacade
     *
     * @return array<int, CartItemAddon>
     */
    public function getAddonsFromCartItem(WcCartItemFacade $wcCartItemFacade)
    {
        $thirdPartyData = $wcCartItemFacade->getThirdPartyData();
        $addonsData     = $thirdPartyData['wcpa_data'] ?? [];

        $addons = [];
        foreach ($addonsData as $addonData) {
            $key   = $addonData['extra']->name ?? null;
            $fields = $addonData['fields'] ?? null;

            if ($key === null || $fields === null) {
                continue;
            }

            foreach ($fields as $field) {
                $value = $field[0]['value'] ?? null;
                $price = $field[0]['price'] ?? null;

                if (isset($field[0]['is_show_price'])) {
                    $isShowPrice = $field[0]['is_show_price'] === true || $field[0]['is_show_price'] === 'true';
                } else {
                    $isShowPrice = true;
                }

                 if (is_array($value) && is_array($price)) {
                    foreach ($value as $k => $valueI) {
                        if (is_object($valueI)) {
                            $subValue   = $valueI ?? null;
                            $subLabel   = $valueI->get_title() ?? null;
                            $subPrice   = $price[$k] ?? null;

                            if ($k === null || $subValue === null || $subLabel === null) {
                                continue;
                            }

                            $subPrice        = $this->priceToFloat($subPrice);
                            $addon           = new CartItemAddon($key . "_" . $k, $subValue, !$isShowPrice ? $subPrice : 0.0);
                            $addon->label    = $subLabel;
                            $addon->currency = $wcCartItemFacade->getCurrency();

                            $addons[] = $addon;
                        } else {
                            $index    = $valueI['i'] ?? null;
                            $subValue = $valueI['value'] ?? null;
                            $subLabel = $valueI['label'] ?? null;
                            $subPrice = $price[$index] ?? null;

                            if ($index === null || $subValue === null || $subLabel === null) {
                                continue;
                            }

                            $subPrice        = $this->priceToFloat($subPrice);
                            $addon           = new CartItemAddon($key . "_" . $index, $subValue, !$isShowPrice ? $subPrice : 0.0);
                            $addon->label    = $subLabel;
                            $addon->currency = $wcCartItemFacade->getCurrency();

                            $addons[] = $addon;
                        }
                    }
                } else {
                    $price           = $this->priceToFloat($price);
                    $addon           = new CartItemAddon($key, $value, !$isShowPrice ? $price : 0.0);
                    $addon->label    = $addonData['label'] ?? $key;
                    $addon->currency = $wcCartItemFacade->getCurrency();
                    $addons[]        = $addon;
                }

            }
        }

        return $addons;
    }

    public function calculateCost($initialCost, $addons, $thirdPartyItemData)
    {
        $addonsCost = array_sum(array_column($addons, 'price'));

        if (isset($thirdPartyItemData['wcpa_cart_rules'])) {
            if (!empty($thirdPartyItemData['wcpa_cart_rules']['price_overide'])) {
                $initialCost = max($initialCost, $addonsCost);
            } else {
                $initialCost += $addonsCost;
            }
        } else {
            $initialCost += $addonsCost;
        }

        return $initialCost;
    }

    /**
     * @param mixed $price
     *
     * @return float
     */
    protected function priceToFloat($price)
    {
        if (is_string($price)) {
            $price = str_replace($this->context->priceSettings->getThousandSeparator(), "", $price);
            $price = str_replace($this->context->priceSettings->getDecimalSeparator(), ".", $price);
        }

        return (float)$price;
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
