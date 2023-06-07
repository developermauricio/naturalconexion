<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use WC_Order_Item_Product;

defined('ABSPATH') or exit;

class CustomerSubscriptions extends AbstractCondition implements ListComparisonCondition
{
    const AT_LEAST_ONE = 'at_least_one';
    const ALL = 'all';
    const ONLY = 'only';
    const NONE = 'none';

    const AVAILABLE_COMP_METHODS = array(
        self::AT_LEAST_ONE,
        self::ALL,
        self::ONLY,
        self::NONE,
    );

    /**
     * @var array
     */
    protected $comparisonList;
    /**
     * @var string
     */
    protected $comparisonMethod;

    public function check($cart)
    {
        $comparisonMethod = $this->comparisonMethod;
        $comparisonList   = empty($this->comparisonList) ? array() : $this->comparisonList;

        if ( ! function_exists('wcs_get_users_subscriptions') or ! is_user_logged_in()) {
            return false;
        }

        $subscriptions = wcs_get_users_subscriptions();

        $productIds = array();
        foreach ($subscriptions as $subscriptionKey => $subscription) {
            if ($subscription->has_status('active')) {
                foreach ($subscription->get_items() as $itemKey => $item) {
                    /**
                     * @var $item WC_Order_Item_Product
                     */
                    $productId = $item->get_product_id();
                    $product   = CacheHelper::getWcProduct($productId);
                    if ($product->is_type(array(
                        'subscription',
                        'subscription_variation',
                        'variable-subscription'
                    ))) {
                        $productIds[] = $productId;
                    }
                }
            }
        }
        $productIds = array_unique($productIds);

        return $this->compareLists($productIds, $comparisonList, $comparisonMethod);
    }

    public static function getType()
    {
        return 'subscriptions';
    }

    public static function getLabel()
    {
        return __('Active subscriptions', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/customer/subscriptions.php';
    }

    public static function getGroup()
    {
        return ConditionsLoader::GROUP_CUSTOMER;
    }

    /**
     * @param array|string $comparisonList
     */
    public function setComparisonList($comparisonList)
    {
        gettype($comparisonList) === 'array' ? $this->comparisonList = $comparisonList : $this->comparisonList = null;
    }

    public function setListComparisonMethod($comparisonMethod)
    {
        in_array(
            $comparisonMethod,
            self::AVAILABLE_COMP_METHODS
        ) ? $this->comparisonMethod = $comparisonMethod : $this->comparisonMethod = null;
    }

    public function getComparisonList()
    {
        return $this->comparisonList;
    }

    public function getListComparisonMethod()
    {
        return $this->comparisonMethod;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->comparisonMethod) and ! is_null($this->comparisonList);
    }
}
