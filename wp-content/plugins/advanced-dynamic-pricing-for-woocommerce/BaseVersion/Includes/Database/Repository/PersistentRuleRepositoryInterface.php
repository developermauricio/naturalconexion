<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;
use ADP\BaseVersion\Includes\Database\PersistentRuleCacheObject;

interface PersistentRuleRepositoryInterface {
    /**
     * @param CartItem $item
     * @param float|null $qty
     *
     * @return array<int,PersistentRuleCacheObject>
     * @throws \Exception
     */
    public function getCache($item, $qty = null);

    /**
     * @param \WC_Product $product
     *
     * @return array<int,PersistentRuleCacheObject>
     * @throws \Exception
     */
    public function getCacheWithProduct($product);

    public function addRule($rows, $ruleId);

    public function getAddRuleData($ruleId, Context $context);

    public function removeRule($ruleId);

    /**
     * @param Context $context
     * @param \WC_Product $product
     * @param array $cartItemData
     */
    public function recalculateCacheForProduct($context, $product, $cartItemData = array());

    /**
     * @param Context $context
     * @param \WC_Cart $wcCart
     *
     * @return array<int, PersistentRule>
     */
    public function getRulesFromWcCart($context, $wcCart);

    public function truncate();
}
