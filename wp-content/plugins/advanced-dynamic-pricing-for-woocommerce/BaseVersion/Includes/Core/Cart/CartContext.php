<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepository;
use ADP\BaseVersion\Includes\Database\Repository\OrderRepositoryInterface;
use ADP\BaseVersion\Includes\WC\WcCustomerSessionFacade;
use ADP\Factory;

defined('ABSPATH') or exit;

class CartContext
{
    /**
     * @var CartCustomer
     */
    private $customer;

    /**
     * @var array
     */
    private $environment;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var WcCustomerSessionFacade
     */
    protected $sessionFacade;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param CartCustomer $customer
     * @param null $deprecated
     */
    public function __construct(CartCustomer $customer, $deprecated = null)
    {
        $this->customer        = $customer;
        $this->context         = adp_context();
        $this->orderRepository = new OrderRepository();

        /** @var WcCustomerSessionFacade $wcSessionFacade */
        $this->sessionFacade = Factory::get("WC_WcCustomerSessionFacade", null);

        $this->environment = array(
            'timestamp'           => current_time('timestamp'),
            'prices_includes_tax' => $this->context->getIsPricesIncludeTax(),
            'tab_enabled'         => $this->context->getIsTaxEnabled(),
            'tax_display_shop'    => $this->context->getTaxDisplayShopMode(),
        );
    }

    public function withContext(Context $context)
    {
        $this->context = $context;

        $this->environment = array(
            'timestamp'           => $this->environment['timestamp'] ?? current_time('timestamp'),
            'prices_includes_tax' => $this->context->getIsPricesIncludeTax(),
            'tab_enabled'         => $this->context->getIsTaxEnabled(),
            'tax_display_shop'    => $this->context->getTaxDisplayShopMode(),
        );
    }

    public function withOrderRepository(OrderRepositoryInterface $orderRepository) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function datetime($format)
    {
        return date($format, $this->environment['timestamp']);
    }

    /**
     * @return Context
     */
    public function getGlobalContext(): Context
    {
        return $this->context;
    }

    /**
     * @return CartCustomer
     */
    public function getCustomer(): CartCustomer
    {
        return $this->customer;
    }

    /**
     * @return int
     */
    public function time()
    {
        return $this->environment['timestamp'];
    }

    public function getPriceMode()
    {
        return $this->getOption('discount_for_onsale');
    }

    public function isCombineMultipleDiscounts()
    {
        return $this->getOption('combine_discounts');
    }

    public function isCombineMultipleFees()
    {
        return $this->getOption('combine_fees');
    }

    public function getCustomerId()
    {
        return $this->customer->getId();
    }

    public function getCountOfRuleUsages($ruleId)
    {
        return $this->orderRepository->getCountOfRuleUsages($ruleId);
    }

    public function getCountOfRuleUsagesPerCustomer($ruleId, $customerId)
    {
        return $this->orderRepository->getCountOfRuleUsagesPerCustomer($ruleId, $customerId);
    }

    public function isTaxEnabled()
    {
        return isset($this->environment['tab_enabled']) ? $this->environment['tab_enabled'] : false;
    }

    public function isPricesIncludesTax()
    {
        return isset($this->environment['prices_includes_tax']) ? $this->environment['prices_includes_tax'] : false;
    }

    public function getTaxDisplayShop()
    {
        return isset($this->environment['tax_display_shop']) ? $this->environment['tax_display_shop'] : '';
    }

    public function getOption($key, $default = false)
    {
        return $this->context->getOption($key);
    }

    /**
     * @param WcCustomerSessionFacade $sessionFacade
     */
    public function withSession(WcCustomerSessionFacade $sessionFacade)
    {
        if ($sessionFacade instanceof WcCustomerSessionFacade) {
            $this->sessionFacade = $sessionFacade;
        }
    }

    /**
     * @return WcCustomerSessionFacade
     */
    public function getSession(): WcCustomerSessionFacade
    {
        return $this->sessionFacade;
    }
}
