<?php


namespace ADP\BaseVersion\Includes\CartExtensions;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepositoryInterface;
use ADP\BaseVersion\Includes\WC\WcAdpMergedCouponHelper;
use WC_Coupon;

defined('ABSPATH') or exit;

class CartExtensions
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context        = adp_context();
        $this->ruleRepository = new RuleRepository();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withRuleRepository(RuleRepositoryInterface $repository)
    {
        $this->ruleRepository = $repository;
    }

    public function hideCouponWordInTotals()
    {
        if ($this->context->getOption('hide_coupon_word_in_totals')) {
            /**
             * Same hook is added by Settings::__construct().
             * In this case hook is fired during http/https requests.
             */
            add_filter(
                'woocommerce_cart_totals_coupon_label',
                array($this, 'hookWoocommerceCartTotalsCouponLabel'),
                5,
                2
            );
        }
    }

    /**
     * @param string $html
     * @param WC_Coupon $wcCoupon
     *
     * @return string
     */
    public function hookWoocommerceCartTotalsCouponLabel($html, $wcCoupon)
    {
        if ($this->context->isUseMergedCoupons()) {
            $mergedCoupon = WcAdpMergedCouponHelper::loadOfCoupon($wcCoupon);

            if ($mergedCoupon->hasAdpPart()) {
                $html = $mergedCoupon->getCode();
            }
        } else {
            if ($wcCoupon->get_virtual() && ($adpMeta = $wcCoupon->get_meta('adp', true))) {
                if (!empty($adpMeta['parts']) && count($adpMeta['parts']) < 2) {
                    $adp_coupon = array_pop($adpMeta['parts']);
                    $html = $adp_coupon->getLabel();
                } else {
                    $html = $wcCoupon->get_code();
                }
            }
        }

        return $html;
    }

    public function removeDeleteLinkForAdpCoupons()
    {
        add_filter('woocommerce_cart_totals_coupon_html', array($this, 'hookWoocommerceCartTotalsCouponHtml'), 10, 3);
    }

    /**
     * @param string $couponHtml
     * @param WC_Coupon $wcCoupon
     * @param string $discountAmountHtml
     *
     * @return string
     */
    public function hookWoocommerceCartTotalsCouponHtml($couponHtml, $wcCoupon, $discountAmountHtml)
    {
        /** @var WC_Coupon $wcCoupon */
        if ($this->context->isUseMergedCoupons()) {
            $mergedCoupon = WcAdpMergedCouponHelper::loadOfCoupon($wcCoupon);

            if ($mergedCoupon->hasAdpPart() && !$mergedCoupon->hasRuleTriggerPart()) {
                $couponHtml = preg_replace('#<a(.*?)class="woocommerce-remove-coupon"(.*?)</a>#', '', $couponHtml);
            }
        } else {
            if ($wcCoupon->get_virtual() && $wcCoupon->get_meta('adp', true)) {
                $couponHtml = preg_replace('#<a(.*?)class="woocommerce-remove-coupon"(.*?)</a>#', '', $couponHtml);
            }
        }

        return $couponHtml;
    }

    /**
     * Additional css class for free item line
     */
    public function attachCssClassToGiftedCartItems()
    {
        add_filter('woocommerce_cart_item_class', array($this, 'hookWoocommerceCartItemClass'), 10, 3);
    }

    /**
     * @param string $strClasses
     * @param array $cartItem
     * @param string $cartItemKey
     *
     * @return string
     */
    public function hookWoocommerceCartItemClass($strClasses, $cartItem, $cartItemKey)
    {
        $classes = explode(' ', $strClasses);
        if ( ! empty($cartItem['wdp_gifted'])) {
            $classes[] = 'wdp_free_product';
        }

        if ( ! empty($cartItem['wdp_rules']) && (float)$cartItem['data']->get_price() == 0) {
            $classes[] = 'wdp_zero_cost_product';
        }

        return implode(' ', $classes);
    }

    public function fillCartItemWhenOrderAgain()
    {
        add_filter(
            'woocommerce_order_again_cart_item_data',
            array($this, 'hookWoocommerceOrderAgainCartItemData'),
            10,
            3
        );
    }

    /**
     * @param array $cartItem
     * @param \WC_Order_Item $item
     * @param \WC_Order $order
     *
     * @return array mixed
     */
    public function hookWoocommerceOrderAgainCartItemData($cartItem, $item, $order)
    {
        if (
            apply_filters(
                'wdp_order_again_cart_item_load_with_order_deals',
                false,
                $cartItem,
                $item,
                $order)
        ) {
            $rules = $item->get_meta('_wdp_rules');
            if ( ! empty($rules)) {
                $cartItem['wdp_rules']     = $rules;
                $cartItem['wdp_immutable'] = true;
            }
        }

        return $cartItem;
    }

    /**
     * To enqueue script which forcing the cart update after calculating shipping in the cart or the checkout page.
     */
    public function forceCartUpdateForShipping()
    {
        add_action('wp_print_styles', array($this, 'hookWpPrintStylesForCartUpdate'));
    }

    public function hookWpPrintStylesForCartUpdate()
    {
        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";

        if ($this->ruleRepository->isConditionTypeActive(array('customer_shipping_method'))) {
            wp_enqueue_script(
                'wdp_update_cart',
                $baseVersionUrl . 'assets/js/update-cart.js',
                array('wc-cart'),
                WC_ADP_VERSION
            );
        }
    }
}
