<?php
namespace PixelYourSite;


function isWcfActive() {
    return function_exists('wcf');
}

function isWcfProActive() {
    return function_exists('wcf_pro');
}

function isWcfLanding () {
    if(isWcfActive())  {
        return _is_wcf_landing_type();
    }
   return false;
}
function isWcfCheckoutPage() {
    if(isWcfActive())  {
        return _is_wcf_checkout_type();
    }
    return false;
}
function isWcfUpSale () {
    if(isWcfActive())  {
        return _is_wcf_upsell_type();
    }
    return false;
}
function isWcfDownSale () {
    if(isWcfActive())  {
        return _is_wcf_downsell_type();
    }
    return false;
}

function isWcfThankyou () {
    if(isWcfActive())  {
        return _is_wcf_thankyou_type();
    }
    return false;
}

function isWcfStep() {

    if(isWcfActive())  {
        return wcf()->utils->is_step_post_type();
    }

    return false;
}

function isWcfSeparateOrders() {
    return function_exists('wcf_pro')
        && wcf_pro()->utils->is_separate_offer_order();
}
function isNextWcfCheckoutPage() {
    return isNextWcfStep("checkout");
}
function isNextWcfStep ($stepType) {
    if(isWcfActive())  {
        $stepId = _get_wcf_step_id();
        if($stepId) {
            $flow_id = wcf()->utils->get_flow_id_from_step_id( $stepId );
            $steps = wcf()->utils->get_flow_steps($flow_id);
            for($i = 0; $i < count($steps); $i++) {
                if($steps[$i]['id'] == $stepId && $i+1 < count($steps)) {
                    return $steps[$i + 1]['type'] == $stepType;
                }
            }
        }

    }
    return false;
}

function getPrevWcfStep () {
    if(isWcfActive())  {
        $stepId = _get_wcf_step_id();
        if($stepId) {
            $flow_id = wcf()->utils->get_flow_id_from_step_id( $stepId );
            $steps = wcf()->utils->get_flow_steps($flow_id);
            for($i = 0; $i < count($steps); $i++) {
                if($steps[$i]['id'] == $stepId && $i != 0) {
                    return $steps[$i - 1];
                }
            }
        }

    }
    return null;
}

function getWcfFlowTitle() {
    global $post;
    $flow_id = wcf()->utils->get_flow_id_from_step_id( $post->ID );
    return get_the_title($flow_id);
}

function getWcfCurrentStep() {
    if(isWcfActive()) {
        $stepId = _get_wcf_step_id();
        if($stepId) {
            return wcf_get_step($stepId);
        }
    }
    return null;
}
function getWcfFlowCheckoutProducts() {
    $wcfProducts = [];
    if(isWcfActive()) {
        $stepId = _get_wcf_step_id();
        if($stepId) {
            $flow_id = wcf()->utils->get_flow_id_from_step_id( $stepId );
            $wcfProducts = getWcfCheckoutProducts($flow_id);
        }
    }
    return $wcfProducts;
}

function getWcfOfferProduct($post_id) {

    $product = get_post_meta( $post_id, 'wcf-offer-product', true );
    $qty = get_post_meta( $post_id, 'wcf-offer-quantity', true );
    $discount_value = get_post_meta( $post_id, 'wcf-offer-discount-value', true );
    $discount_type = get_post_meta( $post_id, 'wcf-offer-discount', true );

    return [
        'product' => $product[0],
        'quantity' => $qty,
        'discount_value' => $discount_value,
        'discount_type' => $discount_type
    ];
}

function getWcfFlowBumpProduct() {
    global $post;

    $product = get_post_meta( $post->ID, 'wcf-order-bump-product', true );
    $qty = get_post_meta( $post->ID, 'wcf-order-bump-product-quantity', true );
    $discount_value = get_post_meta( $post->ID, 'wcf-order-bump-discount-value', true );
    $discount_type = get_post_meta( $post->ID, 'wcf-order-bump-discount', true );

    if(!$product) return null;

    return [
        'product' => $product[0],
        'quantity' => $qty,
        'discount_value' => $discount_value,
        'discount_type' => $discount_type
    ];
}

/* return array with items product(it is id), quantity, discount_type, discount_value  and other*/
function getWcfCheckoutProducts($flow_id) {
    $steps = wcf()->utils->get_flow_steps($flow_id);
    $wcfProducts = [];
    if(is_array($steps)) {
        foreach ($steps as $step) {
            if($step['type'] == 'checkout') {
                $checkout_id = $step['id'];
                $products = wcf()->utils->get_selected_checkout_products( $checkout_id );
                foreach ( $products as $index => $data ) {
                    if ( empty( $data['add_to_cart'] ) || 'no' === $data['add_to_cart'] ) {
                        continue;
                    }
                    $wcfProducts[]=$data;
                }
                break;
            }
        }
    }
    return $wcfProducts;
}

/**
 * @param \WC_Product $product
 * @param $args
 * @return float|-1
 */
function getWfcProductSalePrice($product,$args) {
    if($product) {
        if(!empty($args['discount_value']) && !empty($args['discount_type'])) {
            $productPrice = $product->get_price();
            if($args['discount_type'] == "discount_percent") {
                $percent = $args['discount_value']/100;
                return $productPrice - $productPrice*$percent;
            } elseif ($args['discount_type'] == "discount_price") {
                return $productPrice - $args['discount_value'];
            }
        }
    }
    return -1;
}

function wcf_get_offer_shipping($step_id) {
    return floatval( wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-flat-shipping-value' ) );
}