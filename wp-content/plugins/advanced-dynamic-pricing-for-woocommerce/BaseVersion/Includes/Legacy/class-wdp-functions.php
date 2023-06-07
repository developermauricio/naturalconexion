<?php
if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WDP_Functions
{
    public static function get_gifted_cart_products()
    {
        return adp_functions()->getGiftedCartProducts();
    }

    public static function get_active_rules_for_product($productId, $qty = 1, $useEmptyCart = false)
    {
        return adp_functions()->getActiveRulesForProduct($productId, $qty, $useEmptyCart);
    }

    /**
     *
     * @param array $arrayOfProducts
     * array[]['product_id']
     * array[]['qty']
     * @param boolean $plain Type of returning array. With False returns grouped by rules
     *
     * @return array
     * @throws Exception
     *
     */
    public static function get_discounted_products_for_cart($arrayOfProducts, $plain = false)
    {
        return adp_functions()->getDiscountedProductsForCart($arrayOfProducts, $plain);
    }


    /**
     * @param int|WC_product $theProduct
     * @param int $qty
     * @param bool $useEmptyCart
     *
     * @return float|array|null
     * float for simple product
     * array is (min, max) range for variable
     * null if product is incorrect
     */
    public static function get_discounted_product_price($theProduct, $qty, $useEmptyCart = true)
    {
        return adp_functions()->getDiscountedProductPrice($theProduct, $qty, $useEmptyCart);
    }

    public static function process_cart_manually()
    {
        adp_functions()->processCartManually();
    }
}
