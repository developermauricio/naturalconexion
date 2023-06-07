<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartItemsCollection;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSetCollection;
use ADP\Factory;

defined('ABSPATH') or exit;

class RoleDiscountStrategy
{
    /**
     * @var SingleItemRule|PackageRule
     */
    protected $rule;

    /**
     * @param SingleItemRule|PackageRule $rule
     */
    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    /**
     * @param Cart $cart
     * @param CartItemsCollection $collection
     */
    public function processItems(&$cart, &$collection)
    {
        $roleDiscounts = $this->rule->getRoleDiscounts();

        if ( ! $roleDiscounts) {
            return;
        }

        if ( ! ($currentUserRoles = $cart->getContext()->getCustomer()->getRoles())) {
            return;
        }

        foreach ($roleDiscounts as $roleDiscount) {
            if ( ! count(array_intersect($roleDiscount->getRoles(), $currentUserRoles))) {
                continue;
            }

            if ( ! $roleDiscount->getDiscount()) {
                continue;
            }

            /** @var PriceCalculator $priceCalculator */
            $priceCalculator = Factory::get(
                "Core_RuleProcessor_PriceCalculator",
                $this->rule,
                $roleDiscount->getDiscount()
            );

            foreach ($collection->get_items() as &$item) {
                $priceCalculator->applyItemDiscount($item, $cart, $roleDiscount);
            }
        }
    }

    /**
     * @param Cart $cart
     * @param CartSetCollection $collection
     */
    public function processSets(&$cart, &$collection)
    {
        $roleDiscounts = $this->rule->getRoleDiscounts();

        if ( ! $roleDiscounts) {
            return;
        }

        if ( ! ($currentUserRoles = $cart->getContext()->getCustomer()->getRoles())) {
            return;
        }

        foreach ($roleDiscounts as $roleDiscount) {
            if ( ! count(array_intersect($roleDiscount->getRoles(), $currentUserRoles))) {
                continue;
            }

            /** @var PriceCalculator $priceCalculator */
            $priceCalculator = Factory::get(
                "Core_RuleProcessor_PriceCalculator",
                $this->rule,
                $roleDiscount->getDiscount()
            );
            foreach ($collection->getSets() as $set) {
                $priceCalculator->calculatePriceForSet($set, $cart, $roleDiscount);
            }
        }
    }

}
