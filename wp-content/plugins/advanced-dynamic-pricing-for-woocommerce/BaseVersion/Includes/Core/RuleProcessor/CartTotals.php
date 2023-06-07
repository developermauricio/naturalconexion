<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCart;
use ADP\BaseVersion\Includes\WC\WcCustomerConverter;
use ADP\Factory;

defined('ABSPATH') or exit;

class CartTotals
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var WcCustomerConverter
     */
    protected $wcCustomerConverter;

    /**
     * @param Cart $cart
     */
    public function __construct($cart)
    {
        $this->cart                = $cart;
        $this->wcCustomerConverter = Factory::get(
            "WC_WcCustomerConverter",
            $cart->getContext()->getGlobalContext()
        );
    }

    /**
     * @param bool $inclTax
     *
     * @return float
     */
    protected function calculateItemsSubtotalsWithoutImmutable($inclTax = true)
    {
        /** @see \WC_Cart_Totals::calculate_item_subtotals */

        $cart                            = $this->cart;
        $cartContext                     = $cart->getContext();
        $context                         = $cartContext->getGlobalContext();
        $adjust_non_base_location_prices = apply_filters('woocommerce_adjust_non_base_location_prices', true);
        $is_customer_vat_exempt          = $cart->getContext()->getCustomer()->isVatExempt();
        $calculate_tax                   = $context->getIsTaxEnabled() && ! $is_customer_vat_exempt;

        $itemsSubtotals = floatval(0);
        foreach ($cart->getItems() as $item) {
            if (in_array("immutable", $item->getAttrs())) {
                continue;
            }
            $product          = $item->getWcItem()->getProduct();
            $priceIncludesTax = $context->getIsPricesIncludeTax();
            $taxable          = $context->getIsTaxEnabled() && 'taxable' === $product->get_tax_status();

            if ($item->isPriceChanged()) {
                $price = $item->getTotalPrice();
            } else {
                $price = $product->is_on_sale('edit') ? (float)$product->get_sale_price('edit') : $item->getPrice();
                $price *= $item->getQty();
            }

            $wcCustomer = $this->wcCustomerConverter->convertToWcCustomer($cartContext->getCustomer());

            if ($context->getIsTaxEnabled() && WC()->session !== null) {
                // Some contexts ( e.g. REST API ) does not have initiated session. Do not calculate taxes in that case.
                if (WC()->session === null) {
                    $tax_rates = array();
                } else {
                    $tax_rates = \WC_Tax::get_rates($product->get_tax_class(), $wcCustomer);
                }
            } else {
                $tax_rates = array();
            }

            if ($priceIncludesTax) {
                if ($is_customer_vat_exempt) {

                    /** @see \WC_Cart_Totals::remove_item_base_taxes */
                    if ($priceIncludesTax && $taxable) {
                        if (apply_filters('woocommerce_adjust_non_base_location_prices', true)) {
                            $base_tax_rates = \WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
                        } else {
                            $base_tax_rates = $tax_rates;
                        }

                        // Work out a new base price without the shop's base tax.
                        $taxes = \WC_Tax::calc_tax($price, $base_tax_rates, true);

                        // Now we have a new item price (excluding TAX).
                        $price            = round($price - array_sum($taxes));
                        $priceIncludesTax = false;
                    }

                } elseif ($adjust_non_base_location_prices) {

                    /** @see \WC_Cart_Totals::adjust_non_base_location_price */
                    if ($priceIncludesTax && $taxable) {
                        $base_tax_rates = \WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));

                        if ($tax_rates !== $base_tax_rates) {
                            // Work out a new base price without the shop's base tax.
                            $taxes     = \WC_Tax::calc_tax($price, $base_tax_rates, true);
                            $new_taxes = \WC_Tax::calc_tax($price - array_sum($taxes), $tax_rates, false);

                            // Now we have a new item price.
                            $price = $price - array_sum($taxes) + array_sum($new_taxes);
                        }
                    }

                }
            }

            $subtotal     = $price;
            $subtotal_tax = floatval(0);

            if ($calculate_tax && $taxable) {
                $subtotal_taxes = \WC_Tax::calc_tax($subtotal, $tax_rates, $priceIncludesTax);
                $subtotal_tax   = array_sum(array_map(array($this, 'roundLineTax'), $subtotal_taxes));

                if ($priceIncludesTax) {
                    // Use unrounded taxes so we can re-calculate from the orders screen accurately later.
                    $subtotal = $subtotal - array_sum($subtotal_taxes);
                }
            }

            $itemsSubtotals += $subtotal;

            if ($inclTax) {
                $itemsSubtotals += $subtotal_tax;
            }
        }

        return $itemsSubtotals;
    }

    /**
     * @param bool $inclTax
     *
     * @return float
     */
    protected function calculateItemsSubtotals($inclTax = true)
    {
        /** @see \WC_Cart_Totals::calculate_item_subtotals */

        $cart                            = $this->cart;
        $cartContext                     = $cart->getContext();
        $context                         = $cartContext->getGlobalContext();
        $adjust_non_base_location_prices = apply_filters('woocommerce_adjust_non_base_location_prices', true);
        $is_customer_vat_exempt          = $cart->getContext()->getCustomer()->isVatExempt();
        $calculate_tax                   = $context->getIsTaxEnabled() && ! $is_customer_vat_exempt;

        $itemsSubtotals = floatval(0);
        foreach ($cart->getItems() as $item) {
            $product          = $item->getWcItem()->getProduct();
            $priceIncludesTax = $context->getIsPricesIncludeTax();
            $taxable          = $context->getIsTaxEnabled() && 'taxable' === $product->get_tax_status();

            if ($item->isPriceChanged()) {
                $price = $item->getTotalPrice();
            } else {
                $price = $product->is_on_sale('edit') ? (float)$product->get_sale_price('edit') : $item->getPrice();
                $price *= $item->getQty();
            }

            $wcCustomer = $this->wcCustomerConverter->convertToWcCustomer($cartContext->getCustomer());

            if ($context->getIsTaxEnabled()) {
                $tax_rates = \WC_Tax::get_rates($product->get_tax_class(), $wcCustomer);
            } else {
                $tax_rates = array();
            }

            if ($priceIncludesTax) {
                if ($is_customer_vat_exempt) {

                    /** @see \WC_Cart_Totals::remove_item_base_taxes */
                    if ($priceIncludesTax && $taxable) {
                        if (apply_filters('woocommerce_adjust_non_base_location_prices', true)) {
                            $base_tax_rates = \WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
                        } else {
                            $base_tax_rates = $tax_rates;
                        }

                        // Work out a new base price without the shop's base tax.
                        $taxes = \WC_Tax::calc_tax($price, $base_tax_rates, true);

                        // Now we have a new item price (excluding TAX).
                        $price            = round($price - array_sum($taxes));
                        $priceIncludesTax = false;
                    }

                } elseif ($adjust_non_base_location_prices) {

                    /** @see \WC_Cart_Totals::adjust_non_base_location_price */
                    if ($priceIncludesTax && $taxable) {
                        $base_tax_rates = \WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));

                        if ($tax_rates !== $base_tax_rates) {
                            // Work out a new base price without the shop's base tax.
                            $taxes     = \WC_Tax::calc_tax($price, $base_tax_rates, true);
                            $new_taxes = \WC_Tax::calc_tax($price - array_sum($taxes), $tax_rates, false);

                            // Now we have a new item price.
                            $price = $price - array_sum($taxes) + array_sum($new_taxes);
                        }
                    }

                }
            }

            $subtotal     = $price;
            $subtotal_tax = floatval(0);

            if ($calculate_tax && $taxable) {
                $subtotal_taxes = \WC_Tax::calc_tax($subtotal, $tax_rates, $priceIncludesTax);
                $subtotal_tax   = array_sum(array_map(array($this, 'roundLineTax'), $subtotal_taxes));

                if ($priceIncludesTax) {
                    // Use unrounded taxes so we can re-calculate from the orders screen accurately later.
                    $subtotal = $subtotal - array_sum($subtotal_taxes);
                }
            }

            $itemsSubtotals += $subtotal;

            if ($inclTax) {
                $itemsSubtotals += $subtotal_tax;
            }
        }

        return $itemsSubtotals;
    }

    protected static function roundLineTax($value, $in_cents = true)
    {
        if ( ! self::roundAtSubtotal()) {
            $value = wc_round_tax_total($value, $in_cents ? 0 : null);
        }

        return $value;
    }

    protected static function roundAtSubtotal()
    {
        return 'yes' === get_option('woocommerce_tax_round_at_subtotal');
    }

    /**
     * @param bool $inclTax
     *
     * @return float
     */
    public function getSubtotal($inclTax = false)
    {
        return $this->calculateItemsSubtotals($inclTax);
    }

    /**
     * @param bool $inclTax
     *
     * @return float
     */
    public function getSubtotalWithoutImmutable($inclTax = false)
    {
        return $this->calculateItemsSubtotalsWithoutImmutable($inclTax);
    }

    public function calculateTotalDiscounts($inclTax = false)
    {
        $itemsSubtotals = $this->getSubtotalWithoutImmutable($inclTax);

        $items = array();
        foreach ($this->cart->getItems() as $cartItem) {
            $cart_item_key = $cartItem->getWcItem()->getKey();
            $cart_item = $cartItem->getWcItem()->getData();

            $item = (object)array(
                'object' => null,
                'tax_class' => '',
                'taxable' => false,
                'quantity' => 0,
                'product' => false,
                'price_includes_tax' => false,
                'subtotal' => 0,
                'subtotal_tax' => 0,
                'subtotal_taxes' => array(),
                'total' => 0,
                'total_tax' => 0,
                'taxes' => array(),
            );
            $item->key = $cart_item_key;
            $item->object = $cart_item;
            $item->tax_class = $cart_item['data']->get_tax_class();
            $item->taxable = 'taxable' === $cart_item['data']->get_tax_status();
            $item->price_includes_tax = wc_prices_include_tax();
            $item->quantity = $cart_item['quantity'];
            $item->price = wc_add_number_precision_deep(
                (float)$cart_item['data']->get_price() * (float)$cart_item['quantity']
            );
            $item->product = $cart_item['data'];
            $item->tax_rates = $this->getItemTaxRates($item);
            /** @see \WC_Cart_Totals::get_item_tax_rates() */
            $items[$cart_item_key] = $item;
        }

        /** @see \WC_Cart_Totals::get_coupons_from_cart() */
        $coupons = $this->getCouponsFromCart();
        $discounts = new \WC_Discounts(WC()->cart);
        $discounts->set_items($items);
        foreach ($coupons as $coupon) {
            $discounts->apply_coupon($coupon);
        }
        $couponDiscountAmountTotal = array_sum($discounts->get_discounts_by_coupon(false));

        foreach ($this->cart->getCoupons() as $coupon) {
            if ($coupon instanceof CouponCart) {
                if ($coupon->isType($coupon::TYPE_FIXED_VALUE)) {
                    $couponDiscountAmountTotal += $coupon->getValue();
                } elseif ($coupon->isType($coupon::TYPE_PERCENTAGE)) {
                    $couponDiscountAmountTotal += $itemsSubtotals * $coupon->getValue() / 100;
                }
            } elseif ($coupon instanceof CouponCartItem) {
                $couponDiscountAmountTotal += $coupon->getValue() * $coupon->getAffectedCartItemQty();
            }
        }

        return $couponDiscountAmountTotal;
    }

    protected function getItemTaxRates($item)
    {
        if (!wc_tax_enabled()) {
            return array();
        }
        $tax_class = $item->product->get_tax_class();
        $item_tax_rates = isset($this->item_tax_rates[$tax_class]) ? $this->item_tax_rates[$tax_class] : $this->item_tax_rates[$tax_class] = \WC_Tax::get_rates(
            $item->product->get_tax_class(),
            WC()->cart->get_customer()
        );

        // Allow plugins to filter item tax rates.
//        return apply_filters( 'woocommerce_cart_totals_get_item_tax_rates', $item_tax_rates, $item, $this->cart );
        return $item_tax_rates;
    }

    protected function getCouponsFromCart()
    {
        $coupons = WC()->cart->get_coupons();

        foreach ($coupons as $coupon) {
            switch ($coupon->get_discount_type()) {
                case 'fixed_product':
                    $coupon->sort = 1;
                    break;
                case 'percent':
                    $coupon->sort = 2;
                    break;
                case 'fixed_cart':
                    $coupon->sort = 3;
                    break;
                default:
                    $coupon->sort = 0;
                    break;
            }

            // Allow plugins to override the default order.
            $coupon->sort = apply_filters('woocommerce_coupon_sort', $coupon->sort, $coupon);
        }

        uasort($coupons, array($this, 'sortCouponsCallback'));

        return $coupons;
    }

    /**
     * @see \WC_Cart_Totals::sort_coupons_callback()
     */
    protected function sortCouponsCallback($a, $b)
    {
        if ($a->sort === $b->sort) {
            if ($a->get_limit_usage_to_x_items() === $b->get_limit_usage_to_x_items()) {
                if ($a->get_amount() === $b->get_amount()) {
                    return $b->get_id() - $a->get_id();
                }
                return ($a->get_amount() < $b->get_amount()) ? -1 : 1;
            }
            return ($a->get_limit_usage_to_x_items() < $b->get_limit_usage_to_x_items()) ? -1 : 1;
        }
        return ($a->sort < $b->sort) ? -1 : 1;
    }
}
