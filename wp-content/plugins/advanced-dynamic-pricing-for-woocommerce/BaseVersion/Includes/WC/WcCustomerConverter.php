<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer;
use ADP\Factory;

defined('ABSPATH') or exit;

class WcCustomerConverter
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
     * @param \WC_Customer|null $wcCustomer
     * @param \WC_Session_Handler|null $wcSession
     *
     * @return CartCustomer
     */
    public function convertFromWcCustomer($wcCustomer, $wcSession = null)
    {
        $context = $this->context;
        /** @var CartCustomer $customer */
        $customer = Factory::get("Core_Cart_CartCustomer");

        if ( ! is_null($wcCustomer)) {
            $customer->setId($wcCustomer->get_id());
            $customer->setBillingAddress($wcCustomer->get_billing(''));
            $customer->setShippingAddress($wcCustomer->get_shipping(''));
            $customer->setIsVatExempt($wcCustomer->get_is_vat_exempt());
        }

        /** @var WcCustomerSessionFacade $wcSessionFacade */
        $wcSessionFacade = Factory::get("WC_WcCustomerSessionFacade", $wcSession);
        if ($wcSessionFacade->isValid()) {
            if ($context->isUseSelectedPaymentMethodEverywhere()
                || $context->is($context::WC_CHECKOUT_PAGE)
            ) {
                $customer->setSelectedPaymentMethod($wcSessionFacade->getChosenPaymentMethod());
            }

            if ($context->isUseSelectedShippingMethodsEverywhere()
                || $context->is($context::WC_CHECKOUT_PAGE)
                || $context->is($context::WC_CART_PAGE)
                || ! $context->isCatalog()
            ) {
                $customer->setSelectedShippingMethods($wcSessionFacade->getChosenShippingMethods());
            }

            $customer->setRemovedFreeItemsList($wcSessionFacade->getRemovedFreeItemsList());
            $customer->setRemovedAutoAddItemsList($wcSessionFacade->getRemovedAutoAddItemsList());
            $customer->setAddedRecommendedAutoAddItemsList($wcSessionFacade->getAddedRecommendedAutoAddItemsList());
            $customer->setRemovedRecommendedPromotionsList($wcSessionFacade->getRemovedRecommendedPromotions());
        }

        $wpUser = new \WP_User($customer->getId());
        $roles = apply_filters("wdp_current_user_roles", $wpUser->roles, $wpUser);
        $customer->setRoles($roles);

        return $customer;
    }

    /**
     * @param CartCustomer $customer
     *
     * @return \WC_Customer
     */
    public function convertToWcCustomer(CartCustomer $customer): \WC_Customer
    {
        $wcCustomer = new \WC_Customer();

        $wcCustomer->set_id($customer->getId());

        $wcCustomer->set_billing_country($customer->getBillingCountry());
        $wcCustomer->set_billing_state($customer->getBillingState());
        $wcCustomer->set_billing_postcode($customer->getBillingPostCode());
        $wcCustomer->set_billing_city($customer->getBillingCity());

        $wcCustomer->set_shipping_country($customer->getShippingCountry());
        $wcCustomer->set_shipping_state($customer->getShippingState());
        $wcCustomer->set_shipping_postcode($customer->getShippingPostCode());
        $wcCustomer->set_shipping_city($customer->getShippingCity());

        $wcCustomer->set_is_vat_exempt($customer->isVatExempt());

        return $wcCustomer;
    }

}
