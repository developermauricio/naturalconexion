<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use WC_Customer;
use WC_Session;

defined('ABSPATH') or exit;

class TaxExemptProcessor
{
    const TAX_EXEMPT_KEY_ORIGINAL = 'adp_rule_tax_exempt_original';
    const TAX_EXEMPT_KEY_NEW = 'adp_rule_tax_exempt_new';

    /**
     * @var Context
     */
    protected $context;

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param WC_Customer|null $wcCustomer
     * @param WC_Session|null $wcSession
     */
    public function maybeRevertTaxExempt($wcCustomer, $wcSession)
    {
        if ( ! isset($wcCustomer, $wcSession)) {
            return;
        }

        $taxExemptOriginal = $wcSession->__isset(self::TAX_EXEMPT_KEY_ORIGINAL) ? $wcSession->get(self::TAX_EXEMPT_KEY_ORIGINAL) : null;
        $taxExemptNew      = $wcSession->__isset(self::TAX_EXEMPT_KEY_NEW) ? $wcSession->get(self::TAX_EXEMPT_KEY_NEW) : null;
        $taxExemptCurrent  = $wcCustomer->get_is_vat_exempt();

        if (isset($taxExemptOriginal, $taxExemptNew) && $taxExemptNew === $taxExemptCurrent) {
            $wcCustomer->set_is_vat_exempt($taxExemptOriginal);
        } else {
            $wcSession->set(self::TAX_EXEMPT_KEY_ORIGINAL, WC()->customer->get_is_vat_exempt());
        }

        $wcSession->__unset(self::TAX_EXEMPT_KEY_NEW);
    }

    /**
     * @param Cart $cart
     * @param WC_Customer $wcCustomer
     * @param WC_Session $wcSession
     */
    public function installTaxExemptFromNewCart($cart, $wcCustomer, $wcSession)
    {
        if ( ! isset($wcCustomer, $wcSession)) {
            return;
        }

        $taxExempt = $cart->getContext()->getCustomer()->isVatExempt();
        if ( $customerTaxAdj = $cart->getContext()->getCustomer()->getCustomerTaxAdj() ) {
            $taxExempt = ! $customerTaxAdj->isWithTax();
        }
        $wcCustomer->set_is_vat_exempt($taxExempt);
        $wcSession->set(self::TAX_EXEMPT_KEY_NEW, $taxExempt);
    }

    /**
     * @param Cart $cart
     */
    public function updateTotals($cart)
    {
        $cart->getContext()->getSession()->insertCustomerTaxAdj($cart->getContext()->getCustomer()->getCustomerTaxAdj());
    }
}
