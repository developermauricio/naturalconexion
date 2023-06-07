<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

/**
 * Theme Name: Shoptimizer
 * Author: CommerceGurus
 *
 * @see https://www.commercegurus.com/product/shoptimizer/
 */
class ShoptimizerCmp
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

    public function isActive()
    {
        return defined('SHOPTIMIZER_VERSION');
    }

    public function applyCompatibility() {
        remove_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_change_displayed_sale_price_html', 7 );
        remove_action( 'woocommerce_single_product_summary', 'shoptimizer_change_displayed_sale_price_html', 10 );
        add_action( 'woocommerce_before_shop_loop_item_title', array($this, 'changeDisplayedSalePriceHtml'), 7 );
        add_action( 'woocommerce_single_product_summary', array($this, 'changeDisplayedSalePriceHtml'), 10 );
    }

    public function changeDisplayedSalePriceHtml() {

        global $product;
        $shoptimizer_sale_badge = '';

        $shoptimizer_layout_woocommerce_display_badge = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge' );
        $shoptimizer_layout_woocommerce_display_badge_type = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge_type' );

        if ( $product->is_on_sale() && ! $product->is_type( 'grouped' ) && ! $product->is_type( 'bundle' ) ) {

            if ( $product instanceof \WC_Product_Variable ) {
                $percentages = array();

                $children = [];
                if ($product instanceof \WC_Product_Variable) {
                    $children = $product->get_visible_children();
                } else {
                    $children = $product->get_children();
                }

                foreach ( $children as $child ) {
                    $childProduct = wc_get_product($child);
                    if ($childProduct->get_sale_price()) {
                        $percentages[] = round(100 - ($childProduct->get_sale_price() / $childProduct->get_regular_price() * 100));
                    }
                }

                // Keep the highest value.
                if ( !empty( $percentages ) ) {
                    $percentage = max( $percentages ) . '%';
                }
            } else {
                $regular_price = (float) $product->get_regular_price();
                $sale_price    = (float) $product->get_sale_price();

                $percentage = round( 100 - ( $sale_price / $regular_price * 100 ), 0 ) . '%';
            }

            if( isset( $percentage ) && $percentage > 0 ) {
                if ( 'bubble' === $shoptimizer_layout_woocommerce_display_badge_type ) {
                    $shoptimizer_sale_badge .= sprintf( __( '<span class="sale-item product-label type-bubble">-%s</span>', 'shoptimizer' ), $percentage );
                } else {
                    $shoptimizer_sale_badge .= sprintf( __( '<span class="sale-item product-label type-circle">-%s</span>', 'shoptimizer' ), $percentage );
                }
            }

        }

        if ( true === $shoptimizer_layout_woocommerce_display_badge ) {
            echo shoptimizer_safe_html( $shoptimizer_sale_badge );
        }

    }
}
