<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\CartProcessor\CartCustomerHelper;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\BinaryCondition;

defined('ABSPATH') or exit;

class CustomerLogged extends AbstractCondition implements BinaryCondition
{
    const BIN_YES = 'yes';
    const BIN_NO = 'no';

    const AVAILABLE_COMP_METHODS = array(
        self::BIN_YES,
        self::BIN_NO,
    );

    /**
     * @var bool|null
     */
    protected $comparisonValue;

    public function check($cart)
    {
        $context            = $cart->getContext()->getGlobalContext();
        $cartCustomerHelper = new CartCustomerHelper($context, $cart->getContext()->getCustomer());
        $cartCustomerHelper->withContext($context);
        $comparisonValue    = $this->comparisonValue;

        return $cartCustomerHelper->isLoggedIn() === $comparisonValue;
    }

    public static function getType()
    {
        return 'customer_logged';
    }

    public static function getLabel()
    {
        return __('Is logged in', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/customer/is-logged-in.php';
    }

    public static function getGroup()
    {
        return ConditionsLoader::GROUP_CUSTOMER;
    }

    /**
     * @param string|bool $comparisonValue
     */
    public function setComparisonBinValue($comparisonValue)
    {
        if (in_array($comparisonValue, self::AVAILABLE_COMP_METHODS)) {
            $this->comparisonValue = 'yes' === $comparisonValue;
        } elseif ($comparisonValue === true) {
            $this->comparisonValue = true;
        } else {
            $this->comparisonValue = null;
        }
    }

    public function getComparisonBinValue()
    {
        return $this->comparisonValue;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->comparisonValue);
    }
}
