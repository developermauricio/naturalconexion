<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\NoItemRule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Exceptions\RuleExecutionTimeout;
use ADP\BaseVersion\Includes\Core\RuleProcessor\ProductStock\ProductStockController;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\Factory;
use Exception;
use WC_Product;

defined('ABSPATH') or exit;

class NoItemRuleProcessor implements RuleProcessor
{
    const STATUS_OUT_OF_TIME = -2;
    const STATUS_UNEXPECTED_ERROR = -1;
    const STATUS_NO_INFO = 0;
    const STATUS_STARTED = 1;
    const STATUS_DISABLED_WITH_FORCE = 2;
    const STATUS_LIMITS_NOT_PASSED = 3;
    const STATUS_CONDITIONS_NOT_PASSED = 4;
    const STATUS_FILTERS_NOT_PASSED = 5;
    const STATUS_DISABLED_BY_COUPON_CODE_TRIGGER = 6;
    const STATUS_DISABLED_BY_DATE = 7;

    protected $status;
    protected $lastUnexpectedErrorMessage;

    /**
     * @var float Rule start timestamp
     */
    protected $execRuleStart;

    /**
     * @var float Rule start timestamp
     */
    protected $lastExecTime;

    /**
     * @var NoItemRule
     */
    protected $rule;

    /**
     * @var NoItemRule
     */
    protected $originalRule;

    /**
     * @var Context
     */
    protected $context;

    /**
     * The way how we check conditions
     * @var ConditionsCheckStrategy
     */
    protected $conditionsCheckStrategy;

    /**
     * The way how we check limits
     * @var LimitsCheckStrategy
     */
    protected $limitsCheckStrategy;

    /**
     * The way how we apply cart adjustments
     * @var CartAdjustmentsApplyStrategy
     */
    protected $cartAdjustmentsApplyStrategy;

    /**
     * @var RuleTimer
     */
    protected $ruleTimer;

    /**
     * The way how we gift items
     * @var GiftStrategy
     */
    protected $giftStrategy;

    /**
     * The way how we auto add items
     * @var AutoAddStrategy
     */
    protected $autoAddStrategy;

    /**
     * @var ActivationTriggerStrategy
     */
    protected $activationTriggerStrategy;

    /**
     * @var ProductStockController
     */
    protected $ruleUsedStock;

    /**
     * @param Context|NoItemRule $contextOrRule
     * @param NoItemRule|null $deprecated
     *
     * @throws Exception
     */
    public function __construct($contextOrRule, $deprecated = null)
    {
        $this->context = adp_context();
        $rule = $contextOrRule instanceof NoItemRule ? $contextOrRule : $deprecated;

        if ( ! ($rule instanceof NoItemRule)) {
            $this->context->handleError(new Exception("Wrong rule type"));
        }

        $this->rule         = clone $rule;
        $this->originalRule = $rule;

        $this->conditionsCheckStrategy      = new ConditionsCheckStrategy($rule);
        $this->limitsCheckStrategy          = new LimitsCheckStrategy($rule);
        $this->cartAdjustmentsApplyStrategy = new CartAdjustmentsApplyStrategy($rule);
        $this->ruleTimer                    = new RuleTimer($rule);
        $this->ruleUsedStock                = new ProductStockController();
        $this->giftStrategy                 = Factory::get('Core_RuleProcessor_GiftStrategy', $rule, $this->ruleUsedStock);
        $this->autoAddStrategy              = Factory::get('Core_RuleProcessor_AutoAddStrategy', $rule, $this->ruleUsedStock);
        $this->activationTriggerStrategy    = new ActivationTriggerStrategy($rule);
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return NoItemRule
     */
    public function getRule()
    {
        return $this->originalRule;
    }

    public function applyToCart($cart)
    {
        $this->ruleTimer->start();

        global $wp_filter;
        $current_wp_filter = $wp_filter;

        try {
            $this->process($cart);
        } catch (RuleExecutionTimeout $e) {
            $this->status = self::STATUS_OUT_OF_TIME;
            $this->ruleTimer->handleOutOfTime();
        }

        $wp_filter = $current_wp_filter;

        $this->ruleTimer->finish();

        return true;
    }

    /**
     * @param Cart $cart
     *
     * @throws RuleExecutionTimeout
     */
    protected function process($cart)
    {
        $this->status = self::STATUS_STARTED;

        $this->rule = apply_filters('adp_before_apply_rule', $this->rule, $this, $cart);

        if ( ! apply_filters('adp_is_apply_rule', true, $this->rule, $this, $cart)) {
            $this->status = self::STATUS_DISABLED_WITH_FORCE;

            return;
        }

        if ( ! $this->activationTriggerStrategy->canBeAppliedByDate($cart)) {
            $this->status = self::STATUS_DISABLED_BY_DATE;

            return;
        }

        if ( ! $this->activationTriggerStrategy->canBeAppliedUsingCouponCode($cart)) {
            $this->status = self::STATUS_DISABLED_BY_COUPON_CODE_TRIGGER;

            return;
        }

        if ( ! $this->isRuleMatchedCart($cart)) {
            return;
        }
        $this->ruleTimer->checkExecutionTime();

        $this->addGifts($cart);
        $this->ruleTimer->checkExecutionTime();

        $this->addAutoAdds($cart);
        $this->ruleTimer->checkExecutionTime();

        $this->applyCartAdjustments($cart);
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function isRuleMatchedCart($cart)
    {
        if ( ! $this->checkLimits($cart)) {
            $this->status = $this::STATUS_LIMITS_NOT_PASSED;

            return false;
        }

        if ( ! $this->checkConditions($cart)) {
            $this->status = $this::STATUS_CONDITIONS_NOT_PASSED;

            return false;
        }

        return true;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function checkLimits($cart)
    {
        return $this->limitsCheckStrategy->check($cart);
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function checkConditions($cart)
    {
        return $this->conditionsCheckStrategy->check($cart);
    }

    /**
     * @param Cart $cart
     */
    protected function applyCartAdjustments($cart)
    {
        $this->cartAdjustmentsApplyStrategy->applyToCart($cart);
    }

    /**
     * @param Cart $cart
     */
    protected function addGifts($cart)
    {
        if ( ! $this->giftStrategy->canGift()) {
            return;
        }

        $this->giftStrategy->addGifts($cart);
    }

    /**
     * @param Cart $cart
     */
    protected function addAutoAdds($cart)
    {
        if ( ! $this->autoAddStrategy->canAutoAdd()) {
            return;
        }

        $this->autoAddStrategy->addAutoAdds($cart);
    }

    /**
     * @return float
     */
    public function getLastExecTime()
    {
        return $this->ruleTimer->getLastExecTime();
    }

    /**
     * @param Cart $cart
     * @param WC_Product $product
     * @param bool $checkConditions
     *
     * @return bool
     */
    public function isProductMatched($cart, $product, $checkConditions = false)
    {
        if ( ! ($product instanceof WC_Product)) {
            return false;
        }

        if ( ! $this->checkLimits($cart)) {
            return false;
        }

        if ($checkConditions && ! $this->checkConditions($cart)) {
            return false;
        }

        return true;
    }
}
