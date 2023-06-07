<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Compatibility\MixAndMatchCmp;
use ADP\BaseVersion\Includes\Compatibility\SomewhereWarmBundlesCmp;
use ADP\BaseVersion\Includes\Compatibility\WcSubscriptionsCmp;
use ADP\BaseVersion\Includes\Compatibility\WpcBundleCmp;
use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class WcCartItemDisplayExtensions
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @var WcSubscriptionsCmp
     */
    protected $subscriptionCmp;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();

        $this->priceFunctions  = new PriceFunctions();
        $this->subscriptionCmp = new WcSubscriptionsCmp();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function register()
    {
        add_filter('woocommerce_cart_item_price', array($this, 'wcCartItemPrice'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'wcCartItemSubtotal'), 10, 3);
    }

    /**
     * @param string $price formatted price after wc_price()
     * @param array $cartItem
     * @param string $cartItemKey
     *
     * @return string
     */
    public function wcCartItemPrice($price, $cartItem, $cartItemKey)
    {
        $facade = new WcCartItemFacade($this->context, $cartItem, $cartItemKey);

        $wcBundlesCmp = new SomewhereWarmBundlesCmp();
        if ($wcBundlesCmp->isBundled($facade)) {
            return $price;
        }

        $wpCleverBundleCmp = new WpcBundleCmp();
        if ( $wpCleverBundleCmp->isActive() && $wpCleverBundleCmp->isBundled($facade) ) {
            return $price;
        }

        $mixAndMatchCmp = new MixAndMatchCmp();
        if ( $mixAndMatchCmp->isActive() && $mixAndMatchCmp->isMixAndMatchChild($facade) ) {
            return $price;
        }

        if ($this->context->getOption('show_striked_prices')) {
            $price = $this->wcMainCartItemPrice($price, $cartItem, $cartItemKey);
        }

        return $price;
    }

    /**
     * @param string $price formatted price after wc_price()
     * @param array $cartItem
     * @param string $cartItemKey
     *
     * @return string
     */
    public function wcCartItemSubtotal($price, $cartItem, $cartItemKey)
    {
        if ($this->context->getOption('show_striked_prices')) {
            $price = $this->wcMainCartItemSubtotal($price, $cartItem, $cartItemKey);
        }

        return $price;
    }

    /**
     * @param string $price formatted price after wc_price()
     * @param array $cartItem
     * @param string $cartItemKey
     *
     * @return string
     */
    protected function wcMainCartItemPrice($price, $cartItem, $cartItemKey)
    {
        if ($this->subscriptionCmp->isSetFreeTrial($cartItem)) {
            return $price;
        }

        $context = $this->context;
        $facade  = new WcCartItemFacade($context, $cartItem, $cartItemKey);
        $subsCmp = new WcSubscriptionsCmp($context);

        $newPriceHtml = $price;

        if ('incl' === $context->getTaxDisplayCartMode()) {
            if ($this->context->getOption('regular_price_for_striked_price')) {
                $oldPrice = $facade->getRegularPriceWithoutTax() + $facade->getRegularPriceTax();
            } else {
                $oldPrice = $facade->getOriginalPriceWithoutTax() + $facade->getOriginalPriceTax();
            }

            $newPrice = ($facade->getSubtotal() + $facade->getExactSubtotalTax()) / $facade->getQty();
        } else {
            if ($this->context->getOption('regular_price_for_striked_price')) {
                $oldPrice = $facade->getRegularPriceWithoutTax();
            } else {
                $oldPrice = $facade->getOriginalPriceWithoutTax();
            }

            $newPrice = $facade->getSubtotal() / $facade->getQty();
        }

        $newPrice = apply_filters('wdp_cart_item_new_price', $newPrice, $cartItem, $cartItemKey);
        $oldPrice = apply_filters('wdp_cart_item_initial_price', $oldPrice, $cartItem, $cartItemKey);

        if (is_numeric($newPrice) && is_numeric($oldPrice)) {
            $oldPriceRounded = round($oldPrice, $this->context->priceSettings->getDecimals());
            $newPriceRounded = round($newPrice, $this->context->priceSettings->getDecimals());

            if ($newPriceRounded < $oldPriceRounded) {
                $priceHtml = $this->priceFunctions->formatSalePrice($oldPrice, $newPrice);

                if ($subsCmp->isSubscriptionProduct($facade->getProduct())) {
                    $priceHtml = $subsCmp->maybeAddSubsTail($facade->getProduct(), $priceHtml);
                }
            } elseif ($newPriceRounded === $oldPriceRounded) {
                $priceHtml = $this->priceFunctions->format($oldPrice);

                if ($subsCmp->isSubscriptionProduct($facade->getProduct())) {
                    $priceHtml = $subsCmp->maybeAddSubsTail($facade->getProduct(), $priceHtml);
                }
            } else {
                $priceHtml = $newPriceHtml;
            }
        } else {
            $priceHtml = $newPriceHtml;
        }

        return $priceHtml;
    }

    /**
     * @param string $price formatted price after wc_price()
     * @param array $cartItem
     * @param string $cartItemKey
     *
     * @return string
     */
    protected function wcMainCartItemSubtotal($price, $cartItem, $cartItemKey)
    {
        if ($this->subscriptionCmp->isSetFreeTrial($cartItem)) {
            return $price;
        }

        $context = $this->context;
        $facade  = new WcCartItemFacade($context, $cartItem, $cartItemKey);

        $wpCleverBundleCmp = new WpcBundleCmp();
        if ( $wpCleverBundleCmp->isActive() && $wpCleverBundleCmp->isBundled($facade) ) {
            return $price;
        }

        $mixAndMatchCmp = new MixAndMatchCmp();
        if ( $mixAndMatchCmp->isActive() && $mixAndMatchCmp->isMixAndMatchChild($facade) ) {
            return $price;
        }

        $subsCmp = new WcSubscriptionsCmp($context);

        $newPriceHtml = $price;

        $displayPricesIncludingTax = 'incl' === $context->getTaxDisplayCartMode();

        if ($displayPricesIncludingTax) {
            if ($this->context->getOption('regular_price_for_striked_price')) {
                $oldPrice = $facade->getRegularPriceWithoutTax() + $facade->getRegularPriceTax();
            } else {
                $oldPrice = $facade->getOriginalPriceWithoutTax() + $facade->getOriginalPriceTax();
            }

            $newPrice = ($facade->getSubtotal() + $facade->getExactSubtotalTax()) / $facade->getQty();
        } else {
            if ($this->context->getOption('regular_price_for_striked_price')) {
                $oldPrice = $facade->getRegularPriceWithoutTax();
            } else {
                $oldPrice = $facade->getOriginalPriceWithoutTax();
            }

            $newPrice = $facade->getSubtotal() / $facade->getQty();
        }

        $newPrice *= $facade->getQty();
        $oldPrice *= $facade->getQty();

        $newPrice = apply_filters('wdp_cart_item_subtotal', $newPrice, $cartItem, $cartItemKey);
        $oldPrice = apply_filters('wdp_cart_item_initial_subtotal', $oldPrice, $cartItem, $cartItemKey);

        if (is_numeric($newPrice) && is_numeric($oldPrice)) {
            $oldPriceRounded = round($oldPrice, $this->context->priceSettings->getDecimals());
            $newPriceRounded = round($newPrice, $this->context->priceSettings->getDecimals());

            if ($newPriceRounded < $oldPriceRounded) {
                $priceHtml = $this->priceFunctions->formatSalePrice($oldPrice, $newPrice);

                if ($displayPricesIncludingTax) {
                    if ( ! $context->getIsPricesIncludeTax() && $facade->getExactSubtotalTax() > 0) {
                        $priceHtml .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                    }
                } else {
                    if ($context->getIsPricesIncludeTax() && $facade->getExactSubtotalTax() > 0) {
                        $priceHtml .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                    }
                }

                if ($subsCmp->isSubscriptionProduct($facade->getProduct())) {
                    $priceHtml = $subsCmp->maybeAddSubsTail($facade->getProduct(), $priceHtml);
                }
            } elseif ($newPriceRounded === $oldPriceRounded) {
                $priceHtml = $this->priceFunctions->format($oldPrice);

                if ($subsCmp->isSubscriptionProduct($facade->getProduct())) {
                    $priceHtml = $subsCmp->maybeAddSubsTail($facade->getProduct(), $priceHtml);
                }
            } else {
                $priceHtml = $newPriceHtml;
            }
        } else {
            $priceHtml = $newPriceHtml;
        }

        return $priceHtml;
    }
}
