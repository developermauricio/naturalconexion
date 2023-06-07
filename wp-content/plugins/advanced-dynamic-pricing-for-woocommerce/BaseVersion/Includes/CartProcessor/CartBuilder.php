<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Compatibility\MixAndMatchCmp;
use ADP\BaseVersion\Includes\Compatibility\WcSubscriptionsCmp;
use ADP\BaseVersion\Includes\Compatibility\WpcBundleCmp;
use ADP\BaseVersion\Includes\Compatibility\YithBundlesCmp;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartContext;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Compatibility\SomewhereWarmBundlesCmp;
use ADP\BaseVersion\Includes\Compatibility\SomewhereWarmCompositesCmp;
use ADP\BaseVersion\Includes\WC\WcAdpMergedCouponHelper;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use ADP\BaseVersion\Includes\WC\WcCouponFacade;
use ADP\BaseVersion\Includes\WC\WcCustomerConverter;
use ADP\BaseVersion\Includes\WC\WcCustomerSessionFacade;
use ADP\Factory;
use WC_Cart;
use WC_Coupon;
use WC_Customer;

defined('ABSPATH') or exit;

class CartBuilder
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var SomewhereWarmBundlesCmp
     */
    protected $bundlesCmp;

    /**
     * @var WpcBundleCmp
     */
    protected $wpcBundlesCmp;

    /**
     * @var SomewhereWarmCompositesCmp
     */
    protected $compositeCmp;

    /**
     * @var YithBundlesCmp
     */
    protected $yithBundlesCmp;

    /**
     * @var MixAndMatchCmp
     */
    protected $mixAndMatch;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
        $this->bundlesCmp = new SomewhereWarmBundlesCmp();
        $this->wpcBundlesCmp = new WpcBundleCmp();
        $this->compositeCmp = new SomewhereWarmCompositesCmp();
        $this->yithBundlesCmp = new YithBundlesCmp();
        $this->mixAndMatch = new MixAndMatchCmp();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
        $this->bundlesCmp->withContext($context);
        $this->wpcBundlesCmp->withContext($context);
        $this->compositeCmp->withContext($context);
        $this->yithBundlesCmp->withContext($context);
        $this->mixAndMatch->withContext($context);
    }

    /**
     * @param WC_Customer|null $wcCustomer
     * @param \WC_Session_Handler|null $wcSession
     *
     * @return Cart
     */
    public function create($wcCustomer, $wcSession)
    {
        $context = $this->context;
        /** @var WcCustomerConverter $converter */
        $converter = Factory::get("WC_WcCustomerConverter", $context);
        $customer  = $converter->convertFromWcCustomer($wcCustomer, $wcSession);
        $customerId = $customer->getId();
        //in case account was created during checkout
        if ($customerId === 0 && is_user_logged_in()) {
            $newWcCustomer = new \WC_Customer($wcSession->get_customer_id());

            $reflection = new \ReflectionClass($newWcCustomer);
            $property   = $reflection->getProperty('changes');
            $property->setAccessible(true);
            $property->setValue($newWcCustomer, $wcCustomer->get_changes());

            $customer = $converter->convertFromWcCustomer($newWcCustomer, $wcSession);
        }
        $userMeta = get_user_meta($customerId);
        $customer->setMetaData($userMeta ? $userMeta : array());

        $cartContext = new CartContext($customer, $context);
        /** @var WcCustomerSessionFacade $wcSessionFacade */
        $wcSessionFacade = Factory::get("WC_WcCustomerSessionFacade", $wcSession);
        $cartContext->withSession($wcSessionFacade);

        /** @var Cart $cart */
        $cart = Factory::get('Core_Cart_Cart', $cartContext);

        return $cart;
    }

    /**
     *
     * @param Cart $cart
     * @param WC_Cart $wcCart
     */
    public function populateCart($cart, $wcCart)
    {
        $pos = 0;

        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $wrapper = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
            $wrapper->withContext($this->context);

            if ($wrapper->isClone()) {
                continue;
            }

            $item = $wrapper->createItem();
            if ($item) {
                $item->setPos($pos);

                if ($this->bundlesCmp->isBundled($wrapper)) {
                    $item->addAttr($item::ATTR_READONLY_PRICE);
                }

                if ($this->wpcBundlesCmp->isBundled($wrapper)) {
                    $item->addAttr($item::ATTR_READONLY_PRICE);
                }

                if ($this->wpcBundlesCmp->isSmartBundle($wrapper)) {
                    $item->addAttr($item::ATTR_READONLY_PRICE);
                }

                if ( $this->yithBundlesCmp->isActive() ) {
                    if ( $this->yithBundlesCmp->isBundle($wrapper) || $this->yithBundlesCmp->isBundled($wrapper) ) {
                        $item->addAttr($item::ATTR_READONLY_PRICE);
                    }
                }

                if ((new WcSubscriptionsCmp())->isRenewalSubscription($wrapper)) {
                    $item->addAttr($item::ATTR_IMMUTABLE);
                }

                if ($this->compositeCmp->isCompositeItem($wrapper)) {
                    if ($this->compositeCmp->isAllowToProcessPricedIndividuallyItems()) {
                        if ($this->compositeCmp->isCompositeItemNotPricedIndividually($wrapper, $wcCart)) {
                            $item->addAttr($item::ATTR_IMMUTABLE);
                        }
                    } else {
                        $item->addAttr($item::ATTR_IMMUTABLE);
                    }
                }

                if ($this->mixAndMatch->isMixAndMatchParent($wrapper)) {
                    $item->addAttr($item::ATTR_READONLY_PRICE);
                }

                $cart->addToCart($item);
            }

            $pos++;
        }

        /** Save applied coupons. It needs for detect free (gifts) products during current calculation and notify about them. */
        $this->addOriginCoupons($cart, $wcCart);
    }

    /**
     * @param Cart $cart
     * @param WC_Cart $wcCart
     */
    public function addOriginCoupons($cart, $wcCart)
    {
        if ( ! ($wcCart instanceof WC_Cart)) {
            return;
        }

        $adpCoupons = $cart->getContext()->getSession()->getAdpCoupons();

        foreach ($wcCart->get_coupons() as $wcCoupon) {
            /** @var $wcCoupon WC_Coupon */
            $couponCode = $wcCoupon->get_code('edit');

            if ( $this->context->isUseMergedCoupons() ) {
                $mergedCoupon = WcAdpMergedCouponHelper::loadOfCoupon($wcCoupon);

                if ((new \WC_Discounts(WC()->cart))->is_coupon_valid($wcCoupon)) {
                    if ($mergedCoupon->hasRuleTriggerPart()) {
                        $cart->addRuleTriggerCoupon($couponCode);
                    } elseif (! $mergedCoupon->hasAdpPart()) {
                        $cart->addOriginCoupon($couponCode);
                    }
                }
            } else {
                if ($wcCoupon->is_valid()) {
                    if ($wcCoupon->get_discount_type('edit') === WcCouponFacade::TYPE_ADP_RULE_TRIGGER) {
                        $cart->addRuleTriggerCoupon($couponCode);
                    } elseif (!$wcCoupon->get_meta('adp', true) && !in_array($couponCode, $adpCoupons)) {
                        $cart->addOriginCoupon($couponCode);
                    }
                }
            }
        }
    }
}
