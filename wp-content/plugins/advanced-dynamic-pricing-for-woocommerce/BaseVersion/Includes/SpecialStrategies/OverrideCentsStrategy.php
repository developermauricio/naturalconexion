<?php

namespace ADP\BaseVersion\Includes\SpecialStrategies;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;

defined('ABSPATH') or exit;

class OverrideCentsStrategy
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

    /**
     * @param float $price
     * @param CartItem $item
     *
     * @return float
     */
    public function maybeOverrideCentsForItem($price, $item)
    {
        $product = $item->getWcItem()->getProduct();

        if ($customPrice = apply_filters("wdp_custom_override_cents", false, $price, $this->context, $product, $item)) {
            return $customPrice;
        }

        return $this->maybeOverrideCents($price);
    }

    /**
     * @param float $price
     * @param \WC_Product $product
     *
     * @return float
     */
    public function maybeOverrideCentsForProduct($price, $product)
    {
        if ($customPrice = apply_filters("wdp_custom_override_cents", false, $price, $this->context, $product, null)) {
            return $customPrice;
        }

        return $this->maybeOverrideCents($price);
    }


    /**
     * @param float $price
     *
     * @return float
     */
    public function maybeOverrideCents($price)
    {
        if ( ! $this->context->getOption('is_override_cents')) {
            return $price;
        }

        $pricesEndsWith = $this->context->getOption('prices_ends_with');

        $priceFraction    = $price - intval($price);
        $newPriceFraction = $pricesEndsWith / 100;

        $roundNewPriceFraction = round($newPriceFraction);

        if (0 == intval($price) and 0 < $newPriceFraction) {
            return $newPriceFraction;
        }

        if ($roundNewPriceFraction) {

            if ($priceFraction <= $newPriceFraction - round(1 / 2, 2)) {
                $price = intval($price) - 1 + $newPriceFraction;
            } else {
                $price = intval($price) + $newPriceFraction;
            }

        } else {

            if ($priceFraction >= $newPriceFraction + round(1 / 2, 2)) {
                $price = intval($price) + 1 + $newPriceFraction;
            } else {
                $price = intval($price) + $newPriceFraction;
            }

        }

        return $price;
    }
}
