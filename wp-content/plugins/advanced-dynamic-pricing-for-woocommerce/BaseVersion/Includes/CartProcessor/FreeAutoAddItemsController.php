<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\CustomizerExtensions\CustomizerExtensions;
use ADP\BaseVersion\Includes\TemplateLoader;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer;
use ADP\Factory;
use ADP\BaseVersion\Includes\WC\WcCustomerSessionFacade;

defined('ABSPATH') or exit;

class FreeAutoAddItemsController
{
    const RESTORE_DELETED_FREE_ITEMS_BY_HASH_REQUEST_KEY = 'adp_restore_deleted_items';

    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CustomizerExtensions
     */
    protected $customizer;

    /**
     * @param CustomizerExtensions $customizer
     */
    public function __construct($customizer)
    {
        $this->context    = adp_context();
        $this->customizer = $customizer;
        $this->cart       = null;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param Cart $cart
     */
    public function withCart($cart)
    {
        if ($cart instanceof Cart) {
            $this->cart = $cart;
        }
    }

    public function installHooks()
    {
        add_action('woocommerce_remove_cart_item', array($this, 'handleFreeCartItemRemove'), 10, 2);
        add_action('woocommerce_after_cart_item_quantity_update', array($this, 'handleFreeCartItemUpdate'), 10, 4);
        add_action('wp_loaded', array($this, 'handleRestoreDeletedItems'), 16, 0);
        add_action('woocommerce_checkout_create_order', array($this, "onCreateOrder"), 10, 0);

        add_action('woocommerce_cart_contents', array($this, 'insertRemovedFreeCartItemStubInCart'), 10, 0);
        add_action('woocommerce_mini_cart_contents', array($this, 'insertRemovedFreeMiniCartItemStubInCart'), 10, 0);
    }

    public function handleRestoreDeletedItems()
    {
        if ( ! $this->cart) {
            return;
        }

        if ( ! isset($_REQUEST[self::RESTORE_DELETED_FREE_ITEMS_BY_HASH_REQUEST_KEY])) {
            return;
        }

        $hash = $_REQUEST[self::RESTORE_DELETED_FREE_ITEMS_BY_HASH_REQUEST_KEY];

        /** @var CartCustomer $customer */
        $customer         = $this->cart->getContext()->getCustomer();
        $removedFreeItems = $customer->getRemovedFreeItems($hash);
        $removedFreeItems->purge();

        $wcSessionFacade = $this->cart->getContext()->getSession();
        if ($wcSessionFacade->isValid()) {
            $wcSessionFacade->fetchPropsFromCustomer($customer);
            $wcSessionFacade->push();
        }

        wp_redirect(wc_get_page_permalink('cart'));
        die();
    }


    public function insertRemovedFreeCartItemStubInCart()
    {
        if ( ! $this->cart) {
            return;
        }

        $existingHashes = array();
        foreach ($this->cart->getFreeItems() as $freeCartItem) {
            if ( ! $freeCartItem->isSelected()) {
                $existingHashes[] = $freeCartItem->getAssociatedGiftHash();
            }
        }

        foreach ($this->cart->getContext()->getCustomer()->getRemovedFreeItemsList() as $removedFreeItems) {
            $qty  = $removedFreeItems->getTotalQty();
            $hash = $removedFreeItems->getGiftHash();

            if ($qty <= 0) {
                continue;
            }

            if ( ! in_array($hash, $existingHashes, true)) {
                continue;
            }

            $cartUrl = add_query_arg(array(self::RESTORE_DELETED_FREE_ITEMS_BY_HASH_REQUEST_KEY => $hash),
                wc_get_page_permalink('cart'));

            echo TemplateLoader::wdpGetTemplate("removed-free-cart-item-stub.php", array(
                'hash'    => $hash,
                'cartUrl' => $cartUrl,
                'qty'     => $qty,
                'options' => $this->context->getSettings()->getOptions(),
            ));
            ?>

            <?php
        }
    }




    /**
     * @param string $cartItemKey
     * @param \WC_Cart $wcCart
     */
    public function handleFreeCartItemRemove($cartItemKey, $wcCart)
    {
        if ( ! $this->cart) {
            return;
        }

        $cartItem = $wcCart->cart_contents[$cartItemKey];
        $facade   = new WcCartItemFacade($this->cart->getContext()->getGlobalContext(), $cartItem, $cartItemKey);
        if ($facade->isFreeItem() && ! $facade->isSelectedFreeCartItem()) {
            /** @var CartCustomer $customer */
            $customer         = $this->cart->getContext()->getCustomer();
            $removedFreeItems = $customer->getRemovedFreeItems($facade->getAssociatedHash());
            $removedFreeItems->add($facade->getFreeCartItemHash(), $facade->getQty());

            $wcSessionFacade = $this->cart->getContext()->getSession();
            if ($wcSessionFacade->isValid()) {
                $wcSessionFacade->fetchPropsFromCustomer($customer);
                $wcSessionFacade->push();
            }
        }
    }


    /**
     * @param string $cartItemKey
     * @param string $quantity
     * @param string $oldQuantity
     * @param \WC_Cart $wcCart
     */
    public function handleFreeCartItemUpdate(
        $cartItemKey,
        $quantity,
        $oldQuantity,
        $wcCart
    ) {
        if ( ! $this->cart) {
            return;
        }

        $quantity    = floatval($quantity);
        $oldQuantity = floatval($oldQuantity);

        if ($quantity === $oldQuantity) {
            return;
        }

        $itemFacade = new WcCartItemFacade($this->context, $wcCart->cart_contents[$cartItemKey], $cartItemKey);

        if ($itemFacade->isFreeItem() && ! $itemFacade->isSelectedFreeCartItem()) {
            $customer         = $this->cart->getContext()->getCustomer();
            $removedFreeItems = $customer->getRemovedFreeItems($itemFacade->getAssociatedHash());
            $deletedQty       = $removedFreeItems->get($itemFacade->getFreeCartItemHash());
            $deletedQty       = max(floatval(0), $deletedQty + $oldQuantity - $quantity);
            $removedFreeItems->set($itemFacade->getFreeCartItemHash(), $deletedQty);

            $wcSessionFacade = $this->cart->getContext()->getSession();
            if ($wcSessionFacade->isValid()) {
                $wcSessionFacade->fetchPropsFromCustomer($customer);
                $wcSessionFacade->push();
            }
        }
    }


    public function onCreateOrder()
    {
        /** @var WcCustomerSessionFacade $wcSessionFacade */
        $wcSessionFacade = Factory::get("WC_WcCustomerSessionFacade", WC()->session);
        if ($wcSessionFacade->isValid()) {
            $wcSessionFacade->setRemovedFreeItemsList(array());
            $wcSessionFacade->setRemovedAutoAddItemsList(array());
            $wcSessionFacade->push();
        }
    }

    public function insertRemovedFreeMiniCartItemStubInCart()
    {
        if ( ! $this->cart) {
            return;
        }

        $existingHashes = array();
        foreach ($this->cart->getFreeItems() as $freeCartItem) {
            if ( ! $freeCartItem->isSelected()) {
                $existingHashes[] = $freeCartItem->getAssociatedGiftHash();
            }
        }

        foreach ($this->cart->getContext()->getCustomer()->getRemovedFreeItemsList() as $removedFreeItems) {
            $qty  = $removedFreeItems->getTotalQty();
            $hash = $removedFreeItems->getGiftHash();

            if ($qty <= 0) {
                continue;
            }

            if ( ! in_array($hash, $existingHashes, true)) {
                continue;
            }

            $cartUrl = add_query_arg(array(self::RESTORE_DELETED_FREE_ITEMS_BY_HASH_REQUEST_KEY => $hash),
                wc_get_page_permalink('cart'));

            echo \ADP\BaseVersion\Includes\TemplateLoader::wdpGetTemplate("removed-free-mini-cart-item-stub.php", array(
                'hash'    => $hash,
                'cartUrl' => $cartUrl,
                'qty'     => $qty,
                'options' => $this->context->getSettings()->getOptions(),
            ));
            ?>

            <?php
        }
    }
}
