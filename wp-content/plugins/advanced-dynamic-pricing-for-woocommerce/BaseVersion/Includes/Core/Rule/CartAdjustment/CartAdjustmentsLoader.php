<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment;

use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces;
use ADP\Factory;
use Exception;

defined('ABSPATH') or exit;

class CartAdjustmentsLoader
{
    const KEY = 'cart_adjustments';

    const LIST_TYPE_KEY = 'type';
    const LIST_LABEL_KEY = 'label';
    const LIST_TEMPLATE_PATH_KEY = 'path';

    const GROUP_DISCOUNT = 'discount';
    const GROUP_COUPON = 'coupon';
    const GROUP_FEE = 'fee';
    const GROUP_SHIPPING = 'shipping';

    /**
     * @var array
     */
    protected $groups = array();

    /**
     * @var string[]
     */
    protected $items = array();

    public function __construct()
    {
        $this->initGroups();

        foreach (Factory::getClassNames('Core_Rule_CartAdjustment_Impl') as $className) {
            /**
             * @var $className CartAdjustment
             */
            $this->items[$className::getType()] = $className;
        }
    }

    protected function initGroups()
    {
        $this->groups[self::GROUP_DISCOUNT] = __('Discount', 'advanced-dynamic-pricing-for-woocommerce');
        $this->groups[self::GROUP_COUPON]   = __('Coupons', 'advanced-dynamic-pricing-for-woocommerce');
        $this->groups[self::GROUP_FEE]      = __('Fee', 'advanced-dynamic-pricing-for-woocommerce');
        $this->groups[self::GROUP_SHIPPING] = __('Shipping', 'advanced-dynamic-pricing-for-woocommerce');
    }

    /**
     * @param array $data
     *
     * @return CartAdjustment
     * @throws Exception
     */
    public function build($data)
    {
        if (empty($data['type'])) {
            throw new Exception('Missing cart adjustment type');
        }

        $adj = $this->create($data['type']);
        $this->fillAdjustment($adj, $data);

        if ($adj->isValid()) {
            return $adj;
        } else {
            throw new Exception('Wrong cart adjustment');
        }
    }

    /**
     * @param CartAdjustment $adj
     * @param array $data
     */
    protected function fillAdjustment(&$adj, $data)
    {
        if ($adj instanceof Interfaces\CouponCartAdj) {
            $adj->setCouponValue($data['options'][$adj::COUPON_VALUE_KEY]);
            $adj->setCouponCode($data['options'][$adj::COUPON_CODE_KEY]);
        }
        if ($adj instanceof Interfaces\CouponListCartAdj) {
            $adj->setCouponList($data['options'][$adj::COUPON_LIST_KEY] ?? []);
        }
        if ($adj instanceof Interfaces\FeeCartAdj) {
            $adj->setFeeValue($data['options'][$adj::FEE_VALUE_KEY]);
            $adj->setFeeName($data['options'][$adj::FEE_NAME_KEY]);
            $adj->setFeeTaxClass($data['options'][$adj::FEE_TAX_CLASS_KEY]);
        }
        if ($adj instanceof Interfaces\ShippingCartAdj) {
            $adj->setShippingCartAdjValue($data['options'][$adj::SHIPPING_CARTADJ_VALUE]);
        }
        if ($adj instanceof Interfaces\ShippingMethodCartAdj) {
            $adj->setShippingCartAdjMethod($data['options'][$adj::SHIPPING_CARTADJ_METHOD]);
        }
    }

    /**
     * @param string $type
     *
     * @return CartAdjustment
     * @throws Exception
     */
    public function create($type)
    {
        if (isset($this->items[$type])) {
            $className = $this->items[$type];

            return new $className($type);
        } else {
            throw new Exception('Wrong cart adjustment');
        }
    }

    public function getAsList()
    {
        $list = array();

        foreach ($this->items as $type => $className) {
            /**
             * @var $className CartAdjustment
             */

            $list[$className::getGroup()][] = array(
                self::LIST_TYPE_KEY          => $className::getType(),
                self::LIST_LABEL_KEY         => $className::getLabel(),
                self::LIST_TEMPLATE_PATH_KEY => $className::getTemplatePath(),
            );
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getGroupLabel($key)
    {
        return isset($this->groups[$key]) ? $this->groups[$key] : null;
    }

    public function getItems()
    {
        return $this->items;
    }
}
