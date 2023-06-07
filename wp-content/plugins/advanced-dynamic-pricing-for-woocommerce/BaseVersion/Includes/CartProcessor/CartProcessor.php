<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Compatibility\AvataxCmp;
use ADP\BaseVersion\Includes\Compatibility\FacebookCommerceCmp;
use ADP\BaseVersion\Includes\Compatibility\GiftCardsSomewhereWarmCmp;
use ADP\BaseVersion\Includes\Compatibility\MixAndMatchCmp;
use ADP\BaseVersion\Includes\Compatibility\PDFProductVouchersCmp;
use ADP\BaseVersion\Includes\Compatibility\PhoneOrdersCmp;
use ADP\BaseVersion\Includes\Compatibility\ShoptimizerCmp;
use ADP\BaseVersion\Includes\Compatibility\SomewhereWarmBundlesCmp;
use ADP\BaseVersion\Includes\Compatibility\SomewhereWarmCompositesCmp;
use ADP\BaseVersion\Includes\Compatibility\TmExtraOptionsCmp;
use ADP\BaseVersion\Includes\Compatibility\WcDepositsCmp;
use ADP\BaseVersion\Includes\Compatibility\WcsAttCmp;
use ADP\BaseVersion\Includes\Compatibility\WcSubscriptionsCmp;
use ADP\BaseVersion\Includes\Compatibility\WpcBundleCmp;
use ADP\BaseVersion\Includes\Compatibility\YithBundlesCmp;
use ADP\BaseVersion\Includes\Compatibility\YithGiftCardsCmp;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\FreeCartItem;
use ADP\BaseVersion\Includes\Core\CartCalculator;
use ADP\BaseVersion\Includes\Debug\CartCalculatorListener;
use ADP\BaseVersion\Includes\Enums\ShippingMethodEnum;
use ADP\BaseVersion\Includes\ProductExtensions\ProductExtension;
use ADP\BaseVersion\Includes\SpecialStrategies\CompareStrategy;
use ADP\BaseVersion\Includes\SpecialStrategies\OverrideCentsStrategy;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use ADP\BaseVersion\Includes\WC\WcNoFilterWorker;
use ADP\Factory;
use ReflectionClass;
use ReflectionException;
use WC_Cart;
use WC_Product;
use WC_Product_Variation;

defined('ABSPATH') or exit;

class CartProcessor
{
    /**
     * @var WC_Cart
     */
    protected $wcCart;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var WcNoFilterWorker
     */
    protected $wcNoFilterWorker;

    /**
     * @var CartCalculator
     */
    protected $calc;

    /**
     * @var ICartCouponsProcessor
     */
    protected $cartCouponsProcessor;

    /**
     * @var CartFeeProcessor
     */
    protected $cartFeeProcessor;

    /**
     * @var CartShippingProcessor
     */
    protected $shippingProcessor;

    /**
     * @var TaxExemptProcessor
     */
    protected $taxExemptProcessor;

    /**
     * @var CartBuilder
     */
    protected $cartBuilder;

    /**
     * @var CartCalculatorListener
     */
    protected $listener;

    /**
     * @var PhoneOrdersCmp
     */
    protected $poCmp;

    /**
     * @var OverrideCentsStrategy
     */
    protected $overrideCentsStrategy;

    /**
     * @var CompareStrategy
     */
    protected $compareStrategy;

    /**
     * @var WcSubscriptionsCmp
     */
    protected $wcSubsCmp;

    /**
     * @var WcsAttCmp
     */
    protected $wcsAttCmp;

    /**
     * @var PDFProductVouchersCmp
     */
    protected $vouchers;

    /**
     * @var SomewhereWarmBundlesCmp
     */
    protected $bundlesCmp;

    /**
     * @var SomewhereWarmCompositesCmp
     */
    protected $compositesCmp;

    /**
     * @var wcDepositsCmp
     */
    protected $wcDepositsCmp;

    /**
     * @var GiftCardsSomewhereWarmCmp
     */
    protected $giftCart;

    /**
     * @var FacebookCommerceCmp
     */
    protected $facebookCommerce;

    /**
     * @var TmExtraOptionsCmp
     */
    protected $tmExtraOptCmp;

    /**
     * @var AvataxCmp
     */
    protected $avataxCmp;

    /**
     * @var WpcBundleCmp
     */
    protected $wpcBundleCmp;

    /**
     * @var YithBundlesCmp
     */
    protected $yithBundlesCmp;

    /**
     * CartProcessor constructor.
     *
     * @param Context|WC_Cart $contextOrWcCart
     * @param WC_Cart|CartCalculator|null $wcCartOrCalc
     * @param CartCalculator|null $deprecated
     */
    public function __construct($contextOrWcCart, $wcCartOrCalc = null, $deprecated = null)
    {
        $this->context          = adp_context();
        $this->wcCart           = $contextOrWcCart instanceof WC_Cart ? $contextOrWcCart : $wcCartOrCalc;
        $calc                   = $wcCartOrCalc instanceof CartCalculator ? $wcCartOrCalc : $deprecated;
        $this->wcNoFilterWorker = new WcNoFilterWorker();
        $this->listener         = new CartCalculatorListener();

        if ($calc instanceof CartCalculator) {
            $this->calc = $calc;
        } else {
            $this->calc = Factory::callStaticMethod(
                "Core_CartCalculator",
                'make',
                $this->listener
            );
            /** @see CartCalculator::make() */
        }

        if ( $this->context->isUseMergedCoupons() ) {
            $this->cartCouponsProcessor  = Factory::get("CartProcessor_CartCouponsProcessorMerge");
        } else {
            $this->cartCouponsProcessor  = Factory::get("CartProcessor_CartCouponsProcessor");
        }
        $this->cartFeeProcessor      = new CartFeeProcessor();
        $this->shippingProcessor     = Factory::get("CartProcessor_CartShippingProcessor");
        $this->taxExemptProcessor    = new TaxExemptProcessor();
        $this->cartBuilder           = new CartBuilder();
        $this->poCmp                 = new PhoneOrdersCmp();
        $this->overrideCentsStrategy = new OverrideCentsStrategy();
        $this->compareStrategy       = new CompareStrategy();
        $this->wcSubsCmp             = new WcSubscriptionsCmp();
        $this->wcsAttCmp             = new WcsAttCmp();
        $this->vouchers              = new PDFProductVouchersCmp();
        $this->bundlesCmp            = new SomewhereWarmBundlesCmp();
        $this->compositesCmp         = new SomewhereWarmCompositesCmp();
        $this->tmExtraOptCmp         = new TmExtraOptionsCmp();
        $this->wcDepositsCmp         = new WcDepositsCmp();
        $this->yithGiftCardsCmp      = new YithGiftCardsCmp();
        $this->giftCart              = new GiftCardsSomewhereWarmCmp();
        $this->facebookCommerce      = new FacebookCommerceCmp();
        $this->shoptimizerCmp        = new ShoptimizerCmp();
        $this->avataxCmp             = new AvataxCmp();
        $this->wpcBundleCmp          = new WpcBundleCmp();
        $this->yithBundlesCmp        = new YithBundlesCmp();

        if ($this->giftCart->isActive()) {
            $this->giftCart->applyCompatibility();
        }
        if ($this->yithGiftCardsCmp->isActive()) {
            $this->yithGiftCardsCmp->applyCompatibility();
        }
        if ($this->bundlesCmp->isActive()) {
            $this->bundlesCmp->addFilters();
        }
        if ($this->yithBundlesCmp->isActive()) {
            $this->yithBundlesCmp->addFilters();
        }
        if ($this->facebookCommerce->isActive()) {
            $this->facebookCommerce->applyCompatibility();
        }
        if ($this->shoptimizerCmp->isActive()) {
            $this->shoptimizerCmp->applyCompatibility();
        }
        if ($this->avataxCmp->isActive()) {
            $this->avataxCmp->applyCompatibility();
        }
        if ($this->wpcBundleCmp->isActive()) {
            $this->wpcBundleCmp->addFilters();
        }

        $mixAndMatch = new MixAndMatchCmp();
        if ($mixAndMatch->isActive()) {
            $mixAndMatch->addFilters();
        }
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
        $this->cartBuilder->withContext($context);
        $this->poCmp->withContext($context);
        $this->overrideCentsStrategy->withContext($context);
        $this->compareStrategy->withContext($context);
        $this->wcSubsCmp->withContext($context);
        $this->wcsAttCmp->withContext($context);
        $this->vouchers->withContext($context);
        $this->bundlesCmp->withContext($context);
        $this->compositesCmp->withContext($context);
        $this->wcDepositsCmp->withContext($context);
        $this->giftCart->withContext($context);
        $this->facebookCommerce->withContext($context);
    }

    public function installActionFirstProcess()
    {
        $this->cartCouponsProcessor->installActions();
        $this->cartFeeProcessor->setFilterToCalculateFees();
        $this->shippingProcessor->setFilterToEditPackageRates();
        $this->shippingProcessor->setFilterToEditShippingMethodLabel();
        $this->shippingProcessor->setFilterForShippingChosenMethod();

        $wpCleverBundleCmp = new WpcBundleCmp();
        if ( $wpCleverBundleCmp->isActive() ) {
            $wpCleverBundleCmp->callActionBeforeCalculateTotalsBeforeOurFirstRun();
        }

        add_filter(
            'woocommerce_update_cart_validation',
            array($this, 'filterCheckCartItemExistenceBeforeUpdate'),
            10,
            4
        );
    }

    /**
     * The main process function.
     * WC_Cart -> Cart -> Cart processing -> New Cart -> modifying global WC_Cart
     *
     * @param bool $first
     *
     * @return Cart
     */
    public function process($first = false)
    {
        $wcCart           = $this->wcCart;
        $wcNoFilterWorker = $this->wcNoFilterWorker;

        $this->syncCartItemHashes($wcCart);

        $this->listener->processStarted($wcCart, WC()->session);
        $this->taxExemptProcessor->maybeRevertTaxExempt(WC()->customer, WC()->session);
        $cart = $this->cartBuilder->create(WC()->customer, WC()->session);
        $this->listener->cartCreated($cart);

        /**
         * Do not use @see WC_Cart::is_empty
         * It causes 'Get basket should not be called before the wp_loaded action.' error during REST API request
         */
        if (!$wcCart) {
            return $cart;
        }

        if (count(array_filter($wcCart->get_cart_contents())) === 0) {
            $this->cartBuilder->addOriginCoupons($cart, $wcCart);
            return $cart;
        }

        $chosenShippingMethods    = WC()->session->get("chosen_shipping_methods");
        $chosenOwnShippingMethods = array();

        if (is_array($chosenShippingMethods)) {
            foreach ($chosenShippingMethods as $index => $chosenShippingMethod) {
                if (strpos($chosenShippingMethod, ShippingMethodEnum::TYPE_ADP_FREE_SHIPPING) !== false) {
                    $chosenOwnShippingMethods[$index] = $chosenShippingMethod;
                }
            }
        }

        $this->poCmp->woocsModifyContext();

        // add previously added free and auto add items to internal Cart and remove them from WC_Cart
        $this->processFreeItems($cart, $wcCart);
        $this->processAutoAddItems($cart, $wcCart);
        $this->eliminateClones($wcCart);

        $this->poCmp->sanitizeWcCart($wcCart);

        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $facade  = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
            $product = $facade->getProduct();
            $productExt = new ProductExtension($this->context, $product);
            $productExt->setCustomPrice(
                apply_filters(
                    "adp_product_get_price",
                    null,
                    $product,
                    $facade->getVariation(),
                    $facade->getQty(),
                    $facade->getThirdPartyData(),
                    $facade
                )
            );

            $wcCart->cart_contents[$cartKey] = $facade->getData();
        }

        // fill internal Cart from cloned WC_Cart
        // do not use global WC_Cart because we change prices to get correct initial subtotals
        $clonedWcCart     = clone $wcCart;
        $currencySwitcher = $this->context->currencyController;

        if ($currencySwitcher->isCurrencyChanged()) {
            foreach ($clonedWcCart->cart_contents as $cartKey => $wcCartItem) {
                $facade  = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
                $product = $facade->getProduct();

                $product->set_price($currencySwitcher->getCurrentCurrencyProductPrice($product));
                $salePrice = $currencySwitcher->getCurrentCurrencyProductSalePrice($product);
                if ($salePrice !== null) {
                    $product->set_sale_price($salePrice);
                }
                $product->set_regular_price($currencySwitcher->getCurrentCurrencyProductRegularPrice($product));

                $productExt = new ProductExtension($this->context, $product);

                if ($productExt->getCustomPrice() !== null ) {
                    $product->set_price(
                        $currencySwitcher->getCurrentCurrencyProductPriceWithCustomPrice(
                            $product,
                            $productExt->getCustomPrice()
                        )
                    );
                } else {
                    $price_mode = $this->context->getOption('discount_for_onsale');

                    if ($product->is_on_sale('edit')) {
                        if ('sale_price' === $price_mode || 'discount_sale' === $price_mode) {
                            $price = $product->get_sale_price('edit');
                        } else {
                            $price = $product->get_regular_price('edit');
                        }
                    } else {
                        $price = $product->get_price('edit');
                    }

                    $product->set_price($price);
                }

                $facade->setCurrency($currencySwitcher->getCurrentCurrency());
                $clonedWcCart->cart_contents[$cartKey] = $facade->getData();
            }
        } else {
            foreach ($clonedWcCart->cart_contents as $cartKey => $wcCartItem) {
                $facade               = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
                $product              = $facade->getProduct();
                $prodPropsWithFilters = $this->context->getOption('initial_price_context') === 'view';

                $productExt = new ProductExtension($this->context, $product);

                if ($first) {
                    $facade->setInitialCustomPrice(null);

                    if ($productExt->getCustomPrice() !== null) {
                        $facade->setInitialCustomPrice($productExt->getCustomPrice());
                        $product->set_price($productExt->getCustomPrice());
                    } elseif (
                        $prodPropsWithFilters
                        && ! $this->compareStrategy->floatsAreEqual(
                            $product->get_price('edit'),
                            $product->get_price('view')
                        )
                    ) {
                        $facade->setInitialCustomPrice(floatval($product->get_price('view')));
                    } elseif ( ! isset($product->get_changes()['price'])) {
                        self::setProductPriceDependsOnPriceMode($product);
                    } elseif (($tmCmp = new TmExtraOptionsCmp()) && $tmCmp->isActive() && ($item = $facade->createItem()) && count($item->getAddons()) > 0) {
                        self::setProductPriceDependsOnPriceMode($product);
                        $facade->setInitialCustomPrice(floatval($product->get_price('edit')) + $item->getAddonsAmount());
                    } else {
                        $facade->setInitialCustomPrice($product->get_price('edit'));
                    }
                } else {
                    if ($productExt->getCustomPrice() !== null) {
                        $facade->setInitialCustomPrice($productExt->getCustomPrice());
                        $product->set_price($productExt->getCustomPrice());
                    } elseif (
                        $prodPropsWithFilters
                        && ! $this->compareStrategy->floatsAreEqual(
                            $product->get_price('edit'),
                            $product->get_price('view')
                        )
                    ) {
                        self::setProductPriceDependsOnPriceMode($product);
                        $facade->setInitialCustomPrice(floatval($product->get_price('view')));
                    } elseif ($this->poCmp->isCartItemCostUpdateManually($facade)) {
                        $product->set_price($this->poCmp->getCartItemCustomPrice($facade));
                        $product->set_regular_price($this->poCmp->getCartItemCustomPrice($facade));
                        $facade->addAttribute($facade::ATTRIBUTE_IMMUTABLE);
                    } elseif ($facade->getInitialCustomPrice() !== null) {
                        $product->set_price($facade->getInitialCustomPrice());
                    } /**
                     * Catch 3rd party price changes
                     * e.g. during action 'before calculate totals'
                     */ elseif ($facade->getNewPrice() !== null && ! $this->compareStrategy->floatsAreEqual($facade->getNewPrice(),
                            $product->get_price('edit'))) {
                        $facade->setInitialCustomPrice($product->get_price('edit'));
                        $product->set_price($product->get_price('edit'));
                    } else {
                        self::setProductPriceDependsOnPriceMode($product);
                    }

                }

                $clonedWcCart->cart_contents[$cartKey] = $facade->getData();
            }
        }

        $flags = array();
        if ($this->wcSubsCmp->isActive() && $this->wcsAttCmp->isActive()) {
            $flags[] = $wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS;
        }

        if ($this->bundlesCmp->isActive()) {
            $flags[] = $wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS;
        }

        if ($this->context->getOption("disable_shipping_calc_during_process", false)) {
            $flags[] = $wcNoFilterWorker::FLAG_DISALLOW_SHIPPING_CALCULATION;
        }

        $flags = apply_filters("adp_calculate_totals_flags_for_cloned_cart_before_process", $flags, $wcNoFilterWorker, $first, $clonedWcCart, $this);
        $wcNoFilterWorker->calculateTotals($clonedWcCart, ...$flags);
        $this->cartBuilder->populateCart($cart, $clonedWcCart);
        $this->listener->cartCompleted($cart);
        // fill internal Cart from cloned WC_Cart ended

        // Delete all 'pricing' data from the cart
        $this->sanitizeWcCart($wcCart);
        $this->cartCouponsProcessor->sanitize($wcCart);
        $this->cartFeeProcessor->sanitize($wcCart);
        $this->shippingProcessor->sanitize($wcCart);

        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $facade  = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
            $product = $facade->getProduct();
            $productExt = new ProductExtension($this->context, $product);
            if ($productExt->getCustomPrice() !== null ) {
                $product->set_price($productExt->getCustomPrice());
            }

            $wcCart->cart_contents[$cartKey] = $facade->getData();
        }

        /**
         * Add flag 'FLAG_ALLOW_PRICE_HOOKS'
         * because some plugins set price using 'get_price' hooks instead of modify WC_Product property.
         */
        $flags = array($wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS);
        if ($this->context->getOption("disable_shipping_calc_during_process", false) && !did_action( "wpo_before_update_cart" )) {
            $flags[] = $wcNoFilterWorker::FLAG_DISALLOW_SHIPPING_CALCULATION;
        }
        $wcNoFilterWorker->calculateTotals($wcCart, ...$flags);
        // Delete all 'pricing' data from the cart ended

        $result = $this->calc->processCart($cart);

        if ($result) {
            $cart->setAnyRulesApplied(true);

            do_action('wdp_before_apply_to_wc_cart', $this, $wcCart, $cart);

            //TODO Put to down items that are not filtered?

            /**
             * Rearrange free cart items if option is enabled.
             * We should merge items for saving 'qtyAlreadyInWcCart' property.
             */
            if (
                $this->context->getOption('free_products_as_coupon', false)
                && $this->context->getOption('free_products_coupon_name', false)
            ) {
                $freeProducts = $cart->getFreeItems();
                $cart->purgeFreeItems();

                foreach ( $freeProducts as $freeProduct ) {
                    $freeProduct->setReplaceWithCoupon(true);
                    $freeProduct->setReplaceCouponCode($this->context->getOption('free_products_coupon_name'));
                    $cart->addToCart($freeProduct);
                }
            }

            $freeProductsMapping = $this->calculateFreeProductsMapping($cart, $clonedWcCart);

            $flags = array($wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS);
            if ($this->context->getOption("disable_shipping_calc_during_process", false)) {
                $flags[] = $wcNoFilterWorker::FLAG_DISALLOW_SHIPPING_CALCULATION;
            }

            // Here we have an initial cart with full-price free products
            // Save the totals of the initial cart to show the difference
            // Use the flag 'FLAG_ALLOW_PRICE_HOOKS' to get filtered product prices
            if ($currencySwitcher->isCurrencyChanged()) {
                $wcNoFilterWorker->calculateTotals($clonedWcCart);
            } else {
                $flags[] = $wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS;
                $wcNoFilterWorker->calculateTotals($clonedWcCart, ...$flags);
            }
            $initialTotals = $clonedWcCart->get_totals();

            $this->addFreeItems($freeProductsMapping, $clonedWcCart, $cart, $wcCart, $flags);

            $flags = array();
            if ($this->context->getOption("disable_shipping_calc_during_process", false)) {
                $flags[] = $wcNoFilterWorker::FLAG_DISALLOW_SHIPPING_CALCULATION;
            }

            $wcNoFilterWorker->calculateTotals($wcCart, ...$flags);
            // process free and auto added items ended

            $this->addCommonItems($cart, $wcCart);

            $this->cartCouponsProcessor->applyCouponsToWcCart($cart, $wcCart);
            $this->applyTotals($cart, $wcCart);

            if (count($chosenOwnShippingMethods) > 0) {
                $chosenShippingMethods = WC()->session->get("chosen_shipping_methods");
                foreach ($chosenOwnShippingMethods as $index => $chosenOwnShippingMethod) {
                    $chosenShippingMethods[$index] = $chosenOwnShippingMethod;
                }
                WC()->session->set("chosen_shipping_methods", $chosenShippingMethods);
            }

            $this->taxExemptProcessor->installTaxExemptFromNewCart($cart, WC()->customer, WC()->session);

            $flags = array();

            if ($this->vouchers->isActive()) {
                $flags[] = $wcNoFilterWorker::FLAG_ALLOW_TOTALS_HOOKS;
            }

            if (
                $this->wcSubsCmp->isActive() && $this->wcsAttCmp->isActive()
            ) {
                $flags[] = $wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS;
            }

            if ($this->bundlesCmp->isActive()) {
                $flags[] = $wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS;
            }

            if ($this->compositesCmp->isActive()) {
                $flags[] = $wcNoFilterWorker::FLAG_ALLOW_PRICE_HOOKS;
            }

            $wcNoFilterWorker->calculateTotals($wcCart, ...$flags);

            $this->taxExemptProcessor->updateTotals($cart);
            $this->cartCouponsProcessor->updateTotals($wcCart);
            $this->cartFeeProcessor->updateTotals($wcCart);
            $this->shippingProcessor->updateTotals($wcCart);
            $cart->getContext()->getSession()->insertInitialTotals($initialTotals);
            $cart->getContext()->getSession()->push();
            $wcCart->set_session(); // Push updated totals into the session. Should be after 'updateTotals'

            if ($this->context->getOption('show_message_after_add_free_product')) {
                $this->notifyAboutAddedFreeItems($cart);
            }

            if ($this->wcDepositsCmp->isActive()) {
                $this->wcDepositsCmp->updateDepositsData($wcCart);
            }

            if ( $this->context->getOption('regular_price_for_striked_price') ) {
                $this->insertRegularTotals($wcCart, $cart, $flags);
            }

            $this->postApplyProcess($first, $cart, $wcCart);

            do_action('wdp_after_apply_to_wc_cart', $this, $cart, $wcCart);
            $this->poCmp->forceToSkipFreeCartItems($wcCart);
        } else {
            $cart->getContext()->getSession()->flush()->push();
        }

        $this->listener->processFinished($wcCart, WC()->session);

        do_action('wdp_process_complete', $wcCart, $result, $cart, $this);

        return $cart;
    }

    /**
     * Merge cloned items into the 'locomotive' item. Destroy them after.
     * If the 'locomotive' item has been removed, promote the first clone.
     *
     * @param WC_Cart $wcCart
     */
    protected function eliminateClones($wcCart)
    {
        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $wrapper = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);

            if ($wrapper->getOriginalKey()) {
                if (isset($wcCart->cart_contents[$wrapper->getOriginalKey()])) {
                    $originalWrapper = new WcCartItemFacade($this->context,
                        $wcCart->cart_contents[$wrapper->getOriginalKey()], $wrapper->getOriginalKey());
                    $originalWrapper->setQty($originalWrapper->getQty() + $wrapper->getQty());
                    $wcCart->cart_contents[$originalWrapper->getKey()] = $originalWrapper->getData();
                } else {
                    /** The 'locomotive' is not in cart. Promote the clone! */
                    $wrapper->setKey($wrapper->getOriginalKey());
                    $wrapper->setOriginalKey(null);
                    $wcCart->cart_contents[$wrapper->getKey()] = $wrapper->getData();
                }

                /** do not forget to remove clone */
                unset($wcCart->cart_contents[$cartKey]);
            }
        }
    }

    /**
     * @param $cart Cart
     * @param $wcCart WC_Cart
     */
    protected function processFreeItems($cart, $wcCart)
    {
        $pos = 0;
        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $wrapper = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
            if ($wrapper->isFreeItem()) {
                if ( $this->tmExtraOptCmp->isActive() ) {
                    $this->tmExtraOptCmp->removeKeysFromFreeCartItem($wrapper);
                }
                $item = $wrapper->createItem();
                $item->setPos($pos);
                $cart->addToCart($item);
                unset($wcCart->cart_contents[$cartKey]);
            }

            $pos++;
        }
    }

    /**
     * @param $cart Cart
     * @param $wcCart WC_Cart
     */
    protected function processAutoAddItems($cart, $wcCart)
    {
        $pos = 0;
        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $wrapper = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
            if ($wrapper->isAutoAddItem()) {
                $item = $wrapper->createItem();
                $item->setPos($pos);
                $cart->addToCart($item);
                unset($wcCart->cart_contents[$cartKey]);
            }

            $pos++;
        }
    }

    /**
     * @param WC_Cart $wcCart
     */
    public function sanitizeWcCart($wcCart)
    {
        foreach ($wcCart->cart_contents as $cartKey => $wcCartItem) {
            $wrapper = new WcCartItemFacade($this->context, $wcCartItem, $cartKey);
            $wrapper->sanitize();
            $wcCart->cart_contents[$cartKey] = $wrapper->getData();
        }
    }

    /**
     * @param Cart $cart
     *
     * @return array<int, CartItem>
     */
    protected function getCommonItemsFromCart($cart)
    {
        return apply_filters('wdp_internal_cart_items_before_apply', $cart->getItems(), $this);
    }

    /**
     * @param Cart $cart
     * @param WC_Cart $wcCart
     *
     */
    protected function addCommonItems($cart, $wcCart)
    {
        $cartContext = $cart->getContext();

        $items = $this->getCommonItemsFromCart($cart);

        $processedItemKeys = array();

        foreach ($items as $item) {
            /** have to clone! because of split items are having the same WC_Product object */
            $facade = clone $item->getWcItem();

            $productPrice = $item->getOriginalPrice();
            foreach ($item->getDiscounts() as $ruleId => $amounts) {
                $productPrice -= array_sum($amounts);
            }
            if ($cartContext->getOption('is_calculate_based_on_wc_precision')) {
                $productPrice = round($productPrice, wc_get_price_decimals());
            }

            $facade->setOriginalPrice($facade->getProduct()->get_price('edit'));
            $productPrice = $this->overrideCentsStrategy->maybeOverrideCentsForItem($productPrice, $item);

            $facade->setNewPrice($productPrice);
            $facade->setHistory($item->getHistory());
            $facade->setDiscounts($item->getDiscounts());

            $facade->setOriginalPriceWithoutTax($facade->getSubtotal() / $facade->getQty());
            $facade->setOriginalPriceTax($facade->getExactSubtotalTax() / $facade->getQty());
            $facade->setQty($item->getQty());

            if (in_array($facade->getKey(), $processedItemKeys)) {
                $originalCartItemKey = $facade->getKey();
                $facade->setOriginalKey($originalCartItemKey);

                $cartItemKey = $wcCart->generate_cart_id(
                    $facade->getProductId(),
                    $facade->getVariationId(),
                    $facade->getVariation(),
                    $facade->getCartItemData()
                );

                if (isset($wcCart->cart_contents[$cartItemKey])) {
                    $alreadyProcessedItemFacade = new WcCartItemFacade($this->context,
                        $wcCart->cart_contents[$cartItemKey], $cartItemKey);
                    $alreadyProcessedItemFacade->setQty($alreadyProcessedItemFacade->getQty() + $facade->getQty());
                    $wcCart->cart_contents[$cartItemKey] = $alreadyProcessedItemFacade->getData();
                    continue;
                }

                $facade->setKey($cartItemKey);
            }

            $wcCart->cart_contents[$facade->getKey()] = $facade->getData();
            $processedItemKeys[]                      = $facade->getKey();
        }
    }

    /**
     * @param Cart $cart
     * @param WC_Cart $wcCart
     */
    public function applyTotals($cart, $wcCart)
    {
        $this->cartFeeProcessor->refreshFees($cart);

        if ( ! $this->context->getOption("disable_shipping_calc_during_process", false)) {
            $this->shippingProcessor->purgeCalculatedPackagesInSession();
        }
        $this->shippingProcessor->refresh($cart);
    }

    /**
     * @param Cart $cart
     */
    public function notifyAboutAddedFreeItems($cart)
    {
        $freeItems = $cart->getFreeItems();
        foreach ($freeItems as $freeItem) {
            $freeItemTmp = clone $freeItem;
            $giftedQty   = $freeItemTmp->qty - $freeItem->getQtyAlreadyInWcCart();
            if ($giftedQty > 0) {
                $this->addNoticeAddedFreeProduct($freeItem->getProduct(), $giftedQty);
            } elseif ($freeItemTmp->qty > 0 && $giftedQty < 0) {
                $this->addNoticeRemovedFreeProduct($freeItem->getProduct(), -$giftedQty);
            }
        }
    }

    protected function addNoticeAddedFreeProduct($product, $qty)
    {
        $template = $this->context->getOption('message_template_after_add_free_product');
        $template = _x(
            $template,
            "Show message after adding free product|Output template",
            "advanced-dynamic-pricing-for-woocommerce"
        );
        $arguments = array(
            '{{qty}}'          => $qty,
            '{{product_name}}' => $product->get_name(),
        );
        $message   = str_replace(array_keys($arguments), array_values($arguments), $template);
        $type      = 'success';
        $data      = array('adp' => true);

        wc_add_notice($message, $type, $data);
    }

    protected function addNoticeRemovedFreeProduct($product, $qty)
    {
        $template  = __("Removed {{qty}} free {{product_name}}", 'advanced-dynamic-pricing-for-woocommerce');
        $arguments = array(
            '{{qty}}'          => $qty,
            '{{product_name}}' => $product->get_name(),
        );
        $message   = str_replace(array_keys($arguments), array_values($arguments), $template);
        $type      = 'success';
        $data      = array('adp' => true);

        wc_add_notice($message, $type, $data);
    }

    protected function addNoticeIfNotExists($message, $type = 'success', $data = array())
    {
        $exists = false;
        $notices = wc_get_notices($type);

        foreach ( $notices as $notice ) {
            $text = $notice['notice'] ?? null;
            $noticeData = $notice['data'] ?? [];

            if ( $text && $message === $text && $data === $noticeData ) {
                $exists = true;
                break;
            }
        }

        if ( ! $exists ) {
            wc_add_notice($message, $type, $data);
        }
    }

    /**
     * @return CartCalculatorListener
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     * @return WcNoFilterWorker
     */
    public function getWcNoFilterWorker()
    {
        return $this->wcNoFilterWorker;
    }

    /**
     * You can delete the item during \WC_Cart::set_quantity() if qty is set to 0.
     * This action triggers \WC_Cart::calculate_totals() and calls our cart processor.
     * After $this->eliminateClones() the hashes of the items may change and wc-form-handler will throw the error.
     * e.g. you are removing the 'locomotive' item and the first clone becomes 'loco', so the hash of the clone item is replaced.
     *
     * To prevent this, we double check for existence.
     *
     * @param bool $passedValidation
     * @param string $cartItemKey
     * @param array $values
     * @param int|float $quantity
     *
     * @return bool
     */
    public function filterCheckCartItemExistenceBeforeUpdate(
        $passedValidation,
        $cartItemKey,
        $values,
        $quantity
    ) {
        if ( ! isset(WC()->cart->cart_contents[$cartItemKey])) {
            $passedValidation = false;
        }

        return $passedValidation;
    }

    /**
     * @param WC_Product $product
     */
    protected function setProductPriceDependsOnPriceMode($product)
    {
        $priceMode = $this->context->getOption('discount_for_onsale');

        try {
            $reflection = new ReflectionClass($product);
            $property   = $reflection->getProperty('changes');
            $property->setAccessible(true);
            $changes = $property->getValue($product);
            unset($changes['price']);
            $property->setValue($product, $changes);
        } catch (ReflectionException $exception) {
            $property = null;
        }

        if ($product->is_on_sale('edit')) {
            if ('sale_price' === $priceMode || 'discount_sale' === $priceMode) {
                $price = $product->get_sale_price('edit');
            } else {
                $price = $product->get_regular_price('edit');
            }
        } else {
            $price = $product->get_price('edit');
        }

        $product->set_price($price);
    }

    /**
     * In case if index of the $wcCart->cart_contents element is not equal value by index 'key' of element
     *
     * Scheme of $wcCart->cart_contents
     *
     * [
     *   ['example_hash'] =>
     *      [
     *          'key' => 'example_hash_in_the_element'
     *          ...
     *      ]
     * ]
     *
     * So, sometimes 'example_hash' does not equal 'example_hash_in_the_element', but it should!
     * This method solves the problem.
     *
     * @param WC_Cart|null $wcCart
     */
    protected function syncCartItemHashes($wcCart)
    {
        /**
         * Do not use @see WC_Cart::is_empty
         * It causes 'Get basket should not be called before the wp_loaded action.' error during REST API request
         */
        if ( ! $wcCart || count(array_filter($wcCart->get_cart_contents())) === 0) {
            return;
        }

        foreach ($wcCart->cart_contents as $cartItemHash => $cartItem) {
            if (isset($this->wcCart->cart_contents[$cartItemHash][WcCartItemFacade::KEY_KEY])) {
                $this->wcCart->cart_contents[$cartItemHash][WcCartItemFacade::KEY_KEY] = $cartItemHash;
            }
        }
    }

    /**
     * @param boolean $first
     * @param Cart $cart
     * @param WC_Cart $wcCart
     */
    protected function postApplyProcess($first, $cart, $wcCart)
    {
        //make split items be together
        $cartContents = $wcCart->get_cart_contents();
        $cartKeys = array_keys($cartContents);
        $newOrderCartContents = array();
        $cartItemsWithOriginalKeys = array();
        $needNewOrder = false;
        foreach ($cartContents as $cartItem) {
            $facade = new WcCartItemFacade($this->context, $cartItem);
            if ($facade->getOriginalKey()) {
                $needNewOrder = true;
                $cartItemsWithOriginalKeys[$facade->getKey()] = $facade->getOriginalKey();
            }
        }
        if ($needNewOrder) {
            foreach ($cartItemsWithOriginalKeys as $key => $originalKey) {
                $movedKey = array_splice($cartKeys, array_search($key, $cartKeys), 1);
                array_splice($cartKeys, array_search($originalKey, $cartKeys) + 1, 0, $movedKey);
            }
            foreach ($cartKeys as $key) {
                $newOrderCartContents[$key] = $cartContents[$key];
            }
            $wcCart->set_cart_contents($newOrderCartContents);
        }
    }

    /**
     * @param WC_Cart $wcCart
     * @param Cart $cart
     * @param array<int, string> $flags
     */
    protected function insertRegularTotals($wcCart, $cart, $flags)
    {
        $clonedWcCartForRegular = clone $wcCart;
        foreach ($clonedWcCartForRegular->cart_contents as $cartItemKey => $wcCartItem) {
            $facade = new WcCartItemFacade($wcCartItem, $cartItemKey);
            $facade->getProduct()->set_price($facade->getProduct()->get_regular_price('edit'));
            $clonedWcCartForRegular->cart_contents[$cartItemKey] = $facade->getData();
        }

        $this->wcNoFilterWorker->calculateTotals($clonedWcCartForRegular, ...$flags);

        foreach ($clonedWcCartForRegular->cart_contents as $cartItemKey => $wcCartItem) {
            if ( ! isset($wcCart->cart_contents[$cartItemKey])) {
                continue;
            }

            $facade             = new WcCartItemFacade($wcCartItem, $cartItemKey);
            $globalWcCartFacade = new WcCartItemFacade($wcCart->cart_contents[$cartItemKey], $cartItemKey);

            $globalWcCartFacade->setRegularPriceWithoutTax($facade->getSubtotal() / $facade->getQty());
            $globalWcCartFacade->setRegularPriceTax($facade->getSubtotalTax() / $facade->getQty());

            $wcCart->cart_contents[$cartItemKey] = $globalWcCartFacade->getData();
        }

        $cart->getContext()->getSession()->insertInitialTotals($clonedWcCartForRegular->get_totals());
    }

    /**
     * @param $freeProductsMapping
     * @param WC_Cart $clonedWcCart
     * @param Cart $cart
     * @param WC_Cart $wcCart
     * @param array<int, string> $flags
     */
    protected function addFreeItems($freeProductsMapping, $clonedWcCart, $cart, $wcCart, $flags)
    {
        $wcNoFilterWorker = $this->wcNoFilterWorker;

        foreach ($freeProductsMapping as $loopCartItemKey => $freeItems) {
            foreach ($freeItems as $freeItem) {
                /** @var FreeCartItem $freeItem */

                $facade = new WcCartItemFacade($this->context, $clonedWcCart->cart_contents[$loopCartItemKey],
                    $loopCartItemKey);

                $rules = array($freeItem->getRuleId() => array($freeItem->getInitialPrice()));

                $cartItemQty = $facade->getQty();
                $facade->setQty($freeItem->getQty());

                $facade->setOriginalPrice($facade->getProduct()->get_price('edit'));

                $facade->addAttribute($facade::ATTRIBUTE_FREE);

                /**
                 * @var CouponCartItem|null $coupon
                 *
                 * We must keep the reference, because the affected items are not yet known
                 */
                $coupon = null;

                if ($freeItem->isReplaceWithCoupon()) {
                    // no need to change the price, it is already full
                    $facade->setDiscounts(array());

                    if ($this->context->priceSettings->isIncludeTax()) {
                        $couponAmount = $facade->getSubtotal() + $facade->getExactSubtotalTax();
                    } else {
                        $couponAmount = $facade->getSubtotal();
                    }
                    $couponAmount = ($couponAmount / $cartItemQty) * $freeItem->getQty();

                    $coupon = new CouponCartItem(
                        $this->context,
                        CouponCartItem::TYPE_FREE_ITEM,
                        $freeItem->getReplaceCouponCode(),
                        $couponAmount / $freeItem->getQty(),
                        $freeItem->getRuleId(),
                        null
                    );

                    $cart->addCoupon($coupon);

                    $facade->setReplaceWithCoupon(true);
                    $facade->setReplaceCouponCode($freeItem->getReplaceCouponCode());
                } else {
                    $facade->setNewPrice(0);
                    $facade->setDiscounts($rules);
                }

                $facade->setOriginalPriceWithoutTax($facade->getSubtotal() / $cartItemQty);
                $facade->setOriginalPriceTax($facade->getExactSubtotalTax() / $cartItemQty);
                $facade->setHistory($rules);
                $facade->setAssociatedHash($freeItem->getAssociatedGiftHash());
                $facade->setFreeCartItemHash($freeItem->hash());
                $facade->setSelectedFreeCartItem($freeItem->isSelected());

                $cartItemKey = $wcNoFilterWorker->addToCart($wcCart, $facade->getProductId(), $facade->getQty(),
                    $facade->getVariationId(), $facade->getVariation(), $facade->getCartItemData());

                $newFacade = new WcCartItemFacade(
                    $this->context,
                    $wcCart->cart_contents[$cartItemKey],
                    $cartItemKey
                );
                $newFacade->setNewPrice($facade->getProduct()->get_price('edit'));
                $wcCart->cart_contents[$cartItemKey] = $newFacade->getData();

                if (isset($coupon)) {
                    $coupon->setAffectedCartItem($newFacade);
                }
            }
        }
    }

    protected function calculateFreeProductsMapping($cart, $clonedWcCart)
    {
        // process free items
        /** @var $freeProducts FreeCartItem[] */
        $freeProducts = apply_filters('wdp_internal_free_products_before_apply', $cart->getFreeItems(), $this);

        $wcNoFilterWorker = $this->wcNoFilterWorker;
        $currencySwitcher = $this->context->currencyController;

        $freeProductsMapping = array();
        foreach ($freeProducts as $index => $freeItem) {
            $product = $freeItem->getProduct();

            $product_id = $product->get_id();
            if ($product instanceof WC_Product_Variation) {
                /** @var WC_Product_Variation $product */
                $variationId = $product_id;
                $product_id  = $product->get_parent_id();
                $variation   = $freeItem->getVariation();
            } else {
                $variationId = 0;
                $variation   = array();
            }

            $cartItemData = $freeItem->getCartItemData();

            if ($cartItemKey = $wcNoFilterWorker->addToCart($clonedWcCart, $product_id, $freeItem->qty,
                $variationId, $variation, $cartItemData)) {

                if ( ! isset($freeProductsMapping[$cartItemKey])) {
                    $freeProductsMapping[$cartItemKey] = array();
                }

                $freeProductsMapping[$cartItemKey][] = $freeItem;

                if ($currencySwitcher->isCurrencyChanged()) {
                    $facade = new WcCartItemFacade($this->context, $clonedWcCart->cart_contents[$cartItemKey],
                        $cartItemKey);

                    $product = $facade->getProduct();
                    $product->set_price($currencySwitcher->getCurrentCurrencyProductPrice($product));
                    $salePrice = $currencySwitcher->getCurrentCurrencyProductSalePrice($product);
                    if ($salePrice !== null) {
                        $product->set_sale_price($salePrice);
                    }
                    $product->set_regular_price($currencySwitcher->getCurrentCurrencyProductRegularPrice($product));

                    $price_mode = $this->context->getOption('discount_for_onsale');

                    if ($product->is_on_sale('edit')) {
                        if ('sale_price' === $price_mode || 'discount_sale' === $price_mode) {
                            $price = $product->get_sale_price('edit');
                        } else {
                            $price = $product->get_regular_price('edit');
                        }
                    } else {
                        $price = $product->get_price('edit');
                    }

                    $product->set_price($price);

                    $facade->setCurrency($currencySwitcher->getCurrentCurrency());
                    $clonedWcCart->cart_contents[$cartItemKey] = $facade->getData();
                }
            }
        }

        return $freeProductsMapping;
    }
}
