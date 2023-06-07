<?php

namespace ADP\BaseVersion\Includes;

use ADP\BaseVersion\Includes\CartProcessor\CartProcessor;
use ADP\BaseVersion\Includes\CartProcessor\FreeAutoAddItemsController;
use ADP\BaseVersion\Includes\Compatibility\CTXFeedCmp;
use ADP\BaseVersion\Includes\Compatibility\SmartCouponsCmp;
use ADP\BaseVersion\Includes\Compatibility\WcSubscriptionsCmp;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Debug\CalculationProfiler;
use ADP\BaseVersion\Includes\PriceDisplay\PriceDisplay;
use ADP\BaseVersion\Includes\PriceDisplay\Processor;
use ADP\BaseVersion\Includes\PriceDisplay\WcProductProcessor\InCartWcProductProcessor;
use ADP\BaseVersion\Includes\PriceDisplay\WcProductProcessor\IWcProductProcessor;
use ADP\BaseVersion\Includes\WC\WcCartItemDisplayExtensions;
use ADP\Factory;
use WC_Cart;

defined('ABSPATH') or exit;

class Engine
{
    /**
     * @var CartProcessor
     */
    protected $cartProcessor;

    /**
     * @var IWcProductProcessor
     */
    protected $productProcessor;

    /**
     * @var PriceDisplay
     */
    protected $priceDisplay;

    /**
     * @var CalculationProfiler
     */
    protected $profiler;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var FreeAutoAddItemsController
     */
    protected $freeItemsController;

    /**
     * @var WcCartItemDisplayExtensions
     */
    protected $cartItemDisplayExtensions;

    /**
     * @param Context|WC_Cart|null $contextOrWcCart
     * @param WC_Cart|null $deprecated
     *
     * WC_Cart Can be null! e.g. during REST API requests
     */
    public function __construct($contextOrWcCart, $deprecated = null)
    {
        $this->context                   = adp_context();
        $wcCart                          = $contextOrWcCart instanceof WC_Cart ? $contextOrWcCart : $deprecated;
        $this->cartProcessor             = Factory::get('CartProcessor_CartProcessor', $wcCart);

        if ($this->context->getOption("process_product_strategy") === "when") {
            $this->productProcessor = new Processor();
        } elseif ($this->context->getOption("process_product_strategy") === "after") {
            $this->productProcessor = new InCartWcProductProcessor();
        } else {
            throw new \Exception("Missing process product strategy for value: " . $this->context->getOption("process_product_strategy"));
        }

        $this->priceDisplay              = Factory::get('PriceDisplay_PriceDisplay', $this->productProcessor);
        $this->cartItemDisplayExtensions = Factory::get('WC_WcCartItemDisplayExtensions');
        $this->profiler                  = Factory::get(
            "Debug_CalculationProfiler",
            $this->cartProcessor,
            $this->productProcessor
        );
        $customizer                      = Factory::get('CustomizerExtensions_CustomizerExtensions', $this->context);

        $this->freeItemsController       = Factory::get('CartProcessor_FreeAutoAddItemsController', $customizer);
        $this->freeItemsController->installHooks();

        (new SmartCouponsCmp())->addActionToMoveAction();

        $ctxFeedCmp = new CTXFeedCmp();
        if ($ctxFeedCmp->isActive()) {
            $ctxFeedCmp->prepareHooks();
        }
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
        $this->cartProcessor->withContext($context);
        $this->productProcessor->withContext($context);
        $this->priceDisplay->withContext($context);
        $this->cartItemDisplayExtensions->withContext($context);
        $this->profiler->withContext($context);
        $this->freeItemsController->withContext($context);
    }

    /**
     * Install main hooks.
     *
     * We start processing the cart at 'wp_loaded'. It is obvious.
     *
     * 'Coupon, Fee and Shipping Rate' hooks are required, because we do not want to lost our adjustments,
     * after the 3rd party calls WC_Cart->calculate_totals().
     * @see \WC_Cart::calculate_totals()
     *
     * So, we always process Coupons, Fees and Shipping Rates, but the price change is controlled
     * by the internal adjustments, which are updated only after $this->process()
     * @see CartProcessor::process()
     * @see CartProcessor::applyTotals()
     *
     *
     * get_cart_from_session - 10
     * @see WC_Cart_Session::get_cart_from_session()
     *
     * wc form handle - 20
     * @see WC_Form_Handler
     */
    public function installCartProcessAction()
    {
        add_action('wp_loaded', array($this, 'firstTimeProcessCart'), 15);
        $this->cartProcessor->installActionFirstProcess();
    }

    public function installProductProcessorWithEmptyCart()
    {
        $this->process(true);
    }

    public function firstTimeProcessCart()
    {
        /**
         * Force "yes" value for option Woocommerce->Settings->Tax->Round tax at subtotal level, instead of rounding per line
         * Sometimes subtotal rounds up incorrectly
         *
         * e.g.
         * Rule: 3 items for 29 (fixed price)
         * Cart: 3 items with costs: 64, 45, 45
         */
        if ($this->context->getOption('is_calculate_based_on_wc_precision')) {
            add_filter("pre_option_woocommerce_tax_round_at_subtotal", function ($pre, $option, $default) {
                return 'yes';
            }, 10, 3);
        }

        $this->process(true);

        $hookPriority = intval(apply_filters('wdp_calculate_totals_hook_priority', PHP_INT_MAX));
        add_action('woocommerce_after_calculate_totals', array($this, 'afterCalculateTotals'), $hookPriority);

        /**
         * Force checkout page context
         */
        add_action('woocommerce_checkout_process', function () {
            $context = $this->context;
            $context->setProps(array($context::WC_CHECKOUT_PAGE => true));
            $this->process();
        }, PHP_INT_MAX);

        /**
         * During 'wc-ajax=update_order_review' we change context to CHECKOUT page.
         * Condition 'cart payment method' works only at checkout page.
         */
        if ( apply_filters('wdp_checkout_update_order_review_process_enabled', true) ) {
            add_action('woocommerce_checkout_update_order_review', function () {
                $context = $this->context;
                $context->setProps(array($context::WC_CHECKOUT_PAGE => true));
            }, PHP_INT_MAX);
        }
    }

    public function process($first = false)
    {
        $cart = $this->cartProcessor->process($first);
        $this->productProcessor->withCart($cart);
        $this->priceDisplay->initHooks();
        $this->cartItemDisplayExtensions->register();
        $this->freeItemsController->withCart($cart);
    }

//	public function woocommerce_checkout_update_order_review() {
//		$this->price_display->remove_price_hooks();
//		$this->process_cart();
//		$this->price_display->restore_hooks();
//	}

    public function afterCalculateTotals()
    {
//		$this->priceDisplay->remove_price_hooks();
        if (WcSubscriptionsCmp::isRecurringCartCalculation()) {
            return;
        }

        $this->process(false);
//		$this->priceDisplay->restore_hooks();
    }

    /**
     * @return CartProcessor
     */
    public function getCartProcessor(): CartProcessor
    {
        return $this->cartProcessor;
    }

    /**
     * @return IWcProductProcessor
     */
    public function getProductProcessor(): IWcProductProcessor
    {
        return $this->productProcessor;
    }

    /**
     * @return CalculationProfiler
     */
    public function getProfiler(): CalculationProfiler
    {
        return $this->profiler;
    }

    /**
     * @return PriceDisplay
     */
    public function getPriceDisplay(): PriceDisplay
    {
        return $this->priceDisplay;
    }

}
