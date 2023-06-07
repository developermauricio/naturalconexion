<?php

namespace ADP\BaseVersion\Includes\Compatibility;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use ADP\BaseVersion\Includes\WC\WcNoFilterWorker;

defined('ABSPATH') or exit;

/**
 * Plugin Name: Phone Orders for WooCommerce
 * Author: AlgolPlus
 *
 * @see https://algolplus.com/plugins/downloads/phone-orders-woocommerce-pro/
 */
class PhoneOrdersCmp
{
    const CART_ITEM_COST_KEY = 'wpo_item_cost';
    const CART_ITEM_ID_KEY = 'wpo_key';
    const CART_ITEM_COST_UPDATED_MANUALLY_KEY = 'cost_updated_manually';
    const CART_ITEM_ALLOW_PO_DISCOUNT_KEY = 'allow_po_discount';
    const CART_ITEM_SKIP_KEY = 'wpo_skip_item';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var WcNoFilterWorker
     */
    protected $wcNoFilterWorker;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context          = adp_context();
        $this->wcNoFilterWorker = new WcNoFilterWorker();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function sanitizeWcCart($wcCart)
    {
        $needToUpdate = false;
        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $facade = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);

            $trdPartyData = $facade->getThirdPartyData();

            $needToUpdate = isset(
                $trdPartyData[self::CART_ITEM_ID_KEY],
                $trdPartyData[self::CART_ITEM_COST_KEY],
                $trdPartyData[self::CART_ITEM_COST_UPDATED_MANUALLY_KEY],
                $trdPartyData[self::CART_ITEM_ALLOW_PO_DISCOUNT_KEY]
            );
        }

        if ( ! $needToUpdate) {
            return;
        }

        $newCartContents = array();
        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $facade = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);

            $facade->deleteThirdPartyData(self::CART_ITEM_ID_KEY);
            if ( ! $this->isCartItemCostUpdateManually($facade)) {
                $facade->deleteThirdPartyData(self::CART_ITEM_COST_KEY);
                $facade->deleteThirdPartyData(self::CART_ITEM_COST_UPDATED_MANUALLY_KEY);
                $facade->deleteThirdPartyData(self::CART_ITEM_ALLOW_PO_DISCOUNT_KEY);
            }

            $newCartKey = $facade::generateCartId(
                $facade->getProductId(),
                $facade->getVariationId(),
                $facade->getVariation(),
                $facade->getThirdPartyData()
            );

            if (isset($newCartContents[$newCartKey])) {
                $existingFacade = new WcCartItemFacade($this->context, $newCartContents[$newCartKey], $newCartKey);
                $existingFacade->setQty($existingFacade->getQty() + $facade->getQty());
                $newCartContents[$newCartKey] = $existingFacade->getData();
            } else {
                $facade->setKey($newCartKey);
                $newCartContents[$newCartKey] = $facade->getData();
            }
        }
        $wcCart->cart_contents = $newCartContents;
        $this->wcNoFilterWorker->calculateTotals($wcCart);
    }

    public function forceToSkipFreeCartItems($wcCart)
    {
        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $facade = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);

            if ($facade->isFreeItem()) {
                $facade->setThirdPartyData(self::CART_ITEM_SKIP_KEY, true);
                $wcCart->cart_contents[$cartKey] = $facade->getData();
            }
        }
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return true|false|null
     */
    public function isCartItemCostUpdateManually(WcCartItemFacade $facade)
    {
        $trdPartyData = $facade->getThirdPartyData();

        return isset($trdPartyData[self::CART_ITEM_COST_UPDATED_MANUALLY_KEY]) ? $trdPartyData[self::CART_ITEM_COST_UPDATED_MANUALLY_KEY] : null;
    }

    /**
     * @param WcCartItemFacade $facade
     *
     * @return float|null
     */
    public function getCartItemCustomPrice(WcCartItemFacade $facade)
    {
        $trdPartyData = $facade->getThirdPartyData();

        return isset($trdPartyData[self::CART_ITEM_COST_KEY]) ? $trdPartyData[self::CART_ITEM_COST_KEY] : null;
    }

    public function woocsModifyContext()
    {
        $woocsCmp = new WoocsCmp();
        if ($woocsCmp->isActive()) {
            $woocsCmp->modifyContext($this->context);
        }
    }
}
