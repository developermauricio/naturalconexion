<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Core\Cart\CartCustomer;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\AddedRecommendedAutoAddItems;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\RemovedAutoAddItems;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\RemovedFreeItems;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\RemovedRecommendedPromotions;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCart;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponInterface;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\WcCouponCart;
use ADP\BaseVersion\Includes\Core\Cart\CustomerTax;
use ADP\BaseVersion\Includes\Core\Cart\Fee;
use ADP\BaseVersion\Includes\Core\Cart\ShippingAdjustment;
use WC_Session_Handler;

defined('ABSPATH') or exit;

class WcCustomerSessionFacade
{
    const ADP_SESSION_KEY = 'adp';

    const WC_CHOSEN_PAYMENT_METHOD_KEY = 'chosen_payment_method';
    const WC_CHOSEN_SHIPPING_METHODS_KEY = 'chosen_shipping_methods';

    const ADP_KEY_REMOVED_FREE_ITEMS_LIST = 'removed_free_items_list';
    const ADP_KEY_REMOVED_AUTO_ADD_ITEMS_LIST = 'removed_auto_add_items_list';
    const ADP_KEY_ADDED_RECOMMENDED_AUTO_ADD_ITEMS_LIST = 'added_recommended_auto_add_items_list';
    const ADP_KEY_REMOVED_RECOMMENDED_PROMOTIONS_LIST = 'remove_recommended_promotions';

    const ADP_KEY_TOTALS = 'totals';

    const ADP_KEY_FEE = 'fee';
    const ADP_KEY_COUPONS = 'coupons';
    const ADP_KEY_SHIPPING = 'shipping';
    const ADP_KEY_INITIAL_TOTALS = 'initial_totals';
    const ADP_KEY_REGULAR_TOTALS = 'regular_totals';
    const ADP_KEY_CURRENCY = 'currency';
    const ADP_CUSTOMER_TAX_AJD = 'adp_customer_tax_adj';

    /**
     * @var WC_Session_Handler
     */
    protected $wcCustomerSession;

    /**
     * @var string
     */
    protected $chosenPaymentMethod;

    /**
     * @var array<int,string>
     */
    protected $chosenShippingMethods;

    /**
     * @var array<int,RemovedFreeItems>
     */
    protected $removedFreeItemsList;

    /**
     * @var array<int,RemovedAutoAddItems>
     */
    protected $removedAutoAddItemsList;

    /**
     * @var array<int,AddedRecommendedAutoAddItems>
     */
    protected $addedRecommendedAutoAddItemsList;

    /**
     * @var array<int,CartCustomer\RemovedRecommendedPromotions>
     */
    protected $removedRecommendedPromotions;

    /**
     * @var array
     */
    protected $totals;

    /**
     * @param WC_Session_Handler|null $wcCustomerSession
     */
    public function __construct($wcCustomerSession)
    {
        if ($wcCustomerSession instanceof WC_Session_Handler) {
            $this->wcCustomerSession = $wcCustomerSession;
        }

        $this->chosenPaymentMethod   = "";
        $this->chosenShippingMethods = array();
        $this->initAdpProps();

        $this->load($this->wcCustomerSession);
    }

    /**
     * @param CartCustomer $customer
     */
    public function fetchPropsFromCustomer($customer)
    {
        if ( ! $this->isValid()) {
            return;
        }

        $this->setRemovedFreeItemsList($customer->getRemovedFreeItemsList());
        $this->setRemovedAutoAddItemsList($customer->getRemovedAutoAddItemsList());
        $this->setAddedRecommendedAutoAddItemsList($customer->getAddedRecommendedAutoAddItemsList());
        $this->setRemovedRecommendedPromotions($customer->getRemovedRecommendedPromotionsList());
    }

    /**
     * @param WC_Session_Handler|null $wcCustomerSession
     */
    protected function load($wcCustomerSession)
    {
        if ( ! $wcCustomerSession instanceof WC_Session_Handler) {
            return;
        }

        $this->chosenPaymentMethod   = $wcCustomerSession->get(self::WC_CHOSEN_PAYMENT_METHOD_KEY, '');
        $this->chosenShippingMethods = $wcCustomerSession->get(self::WC_CHOSEN_SHIPPING_METHODS_KEY, array());
        $this->loadAdpProps($wcCustomerSession->get(self::ADP_SESSION_KEY, array()));
    }

    /**
     * @return string
     */
    public function getChosenPaymentMethod()
    {
        return $this->chosenPaymentMethod;
    }

    /**
     * @return array<int,string>
     */
    public function getChosenShippingMethods()
    {
        return $this->chosenShippingMethods;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->wcCustomerSession instanceof WC_Session_Handler;
    }

    /**
     * @return bool
     */
    public function pushAll()
    {
        if ( ! $this->isValid()) {
            return false;
        }

        $this->wcCustomerSession->set(self::WC_CHOSEN_PAYMENT_METHOD_KEY, $this->chosenPaymentMethod);
        $this->wcCustomerSession->set(self::WC_CHOSEN_SHIPPING_METHODS_KEY, $this->chosenShippingMethods);
        $this->wcCustomerSession->set(self::ADP_SESSION_KEY, $this->prepareAdpPropsToPush());

        return true;
    }

    /**
     * @return bool
     */
    public function push()
    {
        if ( ! $this->isValid()) {
            return false;
        }

        $this->wcCustomerSession->set(self::ADP_SESSION_KEY, $this->prepareAdpPropsToPush());

        return true;
    }

    /**
     * @return array<int,RemovedFreeItems>
     */
    public function getRemovedFreeItemsList()
    {
        return $this->removedFreeItemsList;
    }

    /**
     * @param array<int,RemovedFreeItems> $removedFreeItemsList
     */
    public function setRemovedFreeItemsList($removedFreeItemsList)
    {
        $this->removedFreeItemsList = $removedFreeItemsList;
    }

    /**
     * @param string $giftHash
     *
     * @return RemovedFreeItems|null
     */
    public function getRemovedFreeItems($giftHash)
    {
        $result = null;

        foreach ($this->removedFreeItemsList as $removedFreeItems) {
            if ($removedFreeItems->getGiftHash() === $giftHash) {
                $result = $removedFreeItems;
                break;
            }
        }

        return $result;
    }

    /**
     * @return array<int,RemovedAutoAddItems>
     */
    public function getRemovedAutoAddItemsList()
    {
        return $this->removedAutoAddItemsList;
    }

    /**
     * @param array<int,RemovedAutoAddItems> $removedAutoAddItemsList
     */
    public function setRemovedAutoAddItemsList($removedAutoAddItemsList)
    {
        $this->removedAutoAddItemsList = $removedAutoAddItemsList;
    }

    /**
     * @return array<int, AddedRecommendedAutoAddItems>
     */
    public function getAddedRecommendedAutoAddItemsList()
    {
        return $this->addedRecommendedAutoAddItemsList;
    }

    /**
     * @param array<int, AddedRecommendedAutoAddItems> $addedRecommendedAutoAddItemsList
     */
    public function setAddedRecommendedAutoAddItemsList($addedRecommendedAutoAddItemsList)
    {
        $this->addedRecommendedAutoAddItemsList = $addedRecommendedAutoAddItemsList;
    }

    /**
     * @return array<int, RemovedRecommendedPromotions>
     */
    public function getRemovedRecommendedPromotions()
    {
        return $this->removedRecommendedPromotions;
    }

    /**
     * @param array<int, RemovedRecommendedPromotions> $removedRecommendedPromotions
     */
    public function setRemovedRecommendedPromotions($removedRecommendedPromotions)
    {
        $this->removedRecommendedPromotions = $removedRecommendedPromotions;
    }

    protected function initAdpProps()
    {
        $this->removedFreeItemsList = array();
        $this->removedAutoAddItemsList = array();
        $this->addedRecommendedAutoAddItemsList = array();
        $this->removedRecommendedPromotions = array();
        $this->totals = array();
    }

    public function flush() {
        $this->initAdpProps();

        return $this;
    }

    /**
     * @param array $adpData
     */
    protected function loadAdpProps($adpData)
    {
        if (isset($adpData[self::ADP_KEY_REMOVED_FREE_ITEMS_LIST])) {
            $this->removedFreeItemsList = $adpData[self::ADP_KEY_REMOVED_FREE_ITEMS_LIST];
        }
        if (isset($adpData[self::ADP_KEY_REMOVED_AUTO_ADD_ITEMS_LIST])) {
            $this->removedAutoAddItemsList = $adpData[self::ADP_KEY_REMOVED_AUTO_ADD_ITEMS_LIST];
        }
        if (isset($adpData[self::ADP_KEY_ADDED_RECOMMENDED_AUTO_ADD_ITEMS_LIST])) {
            $this->addedRecommendedAutoAddItemsList = $adpData[self::ADP_KEY_ADDED_RECOMMENDED_AUTO_ADD_ITEMS_LIST];
        }
        if (isset($adpData[self::ADP_KEY_REMOVED_RECOMMENDED_PROMOTIONS_LIST])) {
            $this->removedRecommendedPromotions = $adpData[self::ADP_KEY_REMOVED_RECOMMENDED_PROMOTIONS_LIST];
        }
        if (isset($adpData[self::ADP_KEY_TOTALS])) {
            $this->totals = $adpData[self::ADP_KEY_TOTALS];
        }
    }

    /**
     * @return array
     */
    protected function prepareAdpPropsToPush()
    {
        return array(
            self::ADP_KEY_REMOVED_FREE_ITEMS_LIST => $this->removedFreeItemsList,
            self::ADP_KEY_REMOVED_AUTO_ADD_ITEMS_LIST => $this->removedAutoAddItemsList,
            self::ADP_KEY_ADDED_RECOMMENDED_AUTO_ADD_ITEMS_LIST => $this->addedRecommendedAutoAddItemsList,
            self::ADP_KEY_REMOVED_RECOMMENDED_PROMOTIONS_LIST => $this->removedRecommendedPromotions,
            self::ADP_KEY_TOTALS => $this->totals,
        );
    }

    /**
     * @param array<int, Fee> $fees
     */
    public function insertFeesData($fees)
    {
        $listOfFees = array();
        foreach ($fees as $fee) {
            $listOfFees[] = array(
                'name'     => $fee->getName(),
                'type'     => $fee->getType(),
                'value'    => $fee->getValue(),
                'amount'   => $fee->getAmount(),
                'taxable'  => $fee->isTaxAble(),
                'taxClass' => $fee->getTaxClass(),
                'ruleId'   => $fee->getRuleId(),
            );
        }

        $this->totals[self::ADP_KEY_FEE] = $listOfFees;
    }

    /**
     * @return array<int, Fee>
     */
    public function getFees()
    {
        if ( ! isset($this->totals[self::ADP_KEY_FEE])) {
            return array();
        }

        $fees = array();
        foreach ($this->totals[self::ADP_KEY_FEE] as $feeData) {
            $fee = new Fee(
                adp_context(),
                $feeData['type'],
                $feeData['name'],
                $feeData['value'],
                $feeData['taxClass'],
                $feeData['ruleId']
            );
            $fee->setAmount($feeData['amount']);
            $fees[] = $fee;
        }

        return $fees;
    }

    /**
     * @param array<string, array<int, CouponInterface>> $groupedCoupons
     * @param array<string, CouponInterface>             $singleCoupons
     * @param array<string, CouponInterface>             $wcSingleCoupons
     */
    public function insertCouponsData($groupedCoupons, $singleCoupons, $wcSingleCoupons)
    {
        $groupCouponsData    = array();
        $singleCouponsData   = array();
        $wcSingleCouponsData = array();

        foreach ($groupedCoupons as $couponCode => $coupons) {
            $groupCouponsData[$couponCode] = array();

            foreach ($coupons as $coupon) {
                if ($couponData = $this->getCouponData($coupon)) {
                    $groupCouponsData[$couponCode][] = $couponData;
                }
            }
        }

        foreach ($singleCoupons as $coupon) {
            if ($couponData = $this->getCouponData($coupon)) {
                $singleCouponsData[$coupon->getCode()] = $couponData;
            }
        }

        foreach ($wcSingleCoupons as $coupon) {
            if ($couponData = $this->getCouponData($coupon)) {
                $wcSingleCouponsData[$coupon->getCode()] = $couponData;
            }
        }

        $this->totals[self::ADP_KEY_COUPONS] = array(
            'group'     => $groupCouponsData,
            'single'    => $singleCouponsData,
            'wc_single' => $wcSingleCouponsData,
        );
    }

    /**
     * @param CouponInterface $coupon
     *
     * @return array
     */
    private function getCouponData(CouponInterface $coupon)
    {
        if ($coupon instanceof CouponCart) {
            return array(
                'type'   => $coupon->getType(),
                'code'   => $coupon->getCode(),
                'value'  => $coupon->getValue(),
                'ruleId' => $coupon->getRuleId(),
            );
        } elseif ($coupon instanceof CouponCartItem) {
            return array(
                'type'             => $coupon->getType(),
                'code'             => $coupon->getCode(),
                'value'            => $coupon->getValue(),
                'ruleId'           => $coupon->getRuleId(),
                'affectedCartItem' => $coupon->getAffectedCartItemKey(),
                'affectedQty'      => $coupon->getAffectedCartItemQty(),
            );
        } elseif ($coupon instanceof WcCouponCart) {
            return array(
                'code'   => $coupon->getCode(),
                'ruleId' => $coupon->getRuleId(),
            );
        }

        return array();
    }

    /**
     * @param array $data
     *
     * @return CouponInterface|null
     */
    private function getCouponFromData($data)
    {
        if ( ! $data) {
            return null;
        }

        $context = adp_context();
        $type    = $data['type'];

        if (in_array($type, CouponCart::AVAILABLE_TYPES)) {
            $coupon = new CouponCart($context, $type, $data['code'], $data['value'], $data['ruleId']);
        } elseif (in_array($type, CouponCartItem::AVAILABLE_TYPES)) {
            if (isset($this->wcCart->cart_contents[$data['affectedCartItem']])) {
                $affectedCartItem = new WcCartItemFacade(
                    $context, $this->wcCart->cart_contents[$data['affectedCartItem']], $data['affectedCartItem']
                );
                $affectedCartItem->setQty($data['affectedQty']);
            } else {
                $affectedCartItem = null;
            }

            $coupon = new CouponCartItem(
                $context, $type, $data['code'], $data['value'], $data['ruleId'], $affectedCartItem
            );
        } else {
            return null;
        }

        return $coupon;
    }

    /**
     * @param array $data
     *
     * @return CouponInterface|null
     */
    private function getWcSingleCouponFromData($data)
    {
        if ( ! $data) {
            return null;
        }

        return new WcCouponCart($data['code'], $data['ruleId']);
    }

    /**
     * @return array<int, array<int, CouponInterface>>
     */
    public function getGroupedCoupons()
    {
        if ( ! isset($this->totals[self::ADP_KEY_COUPONS])) {
            return array();
        }

        $groupedCoupons = array();
        foreach ($this->totals[self::ADP_KEY_COUPONS] as $key => $data) {
            if ($key === 'group') {
                foreach ($data as $code => $coupons) {
                    $groupedCoupons[$code] = array();

                    foreach ($coupons as $couponData) {
                        if ($coupon = $this->getCouponFromData($couponData)) {
                            $groupedCoupons[$code][] = $coupon;
                        }
                    }
                }
            }
        }

        return $groupedCoupons;
    }

    /**
     * @return array<int, CouponInterface>
     */
    public function getSingleCoupons()
    {
        if ( ! isset($this->totals[self::ADP_KEY_COUPONS])) {
            return array();
        }

        $singleCoupons = array();
        foreach ($this->totals[self::ADP_KEY_COUPONS] as $key => $data) {
            if ($key === 'single') {
                foreach ($data as $code => $couponData) {
                    if ($coupon = $this->getCouponFromData($couponData)) {
                        $singleCoupons[$code] = $coupon;
                    }
                }
            }
        }

        return $singleCoupons;
    }

    /**
     * @return array<int, CouponInterface>
     */
    public function getWcSingleCoupons()
    {
        if ( ! isset($this->totals[self::ADP_KEY_COUPONS])) {
            return array();
        }

        $wcSingleCoupons = array();
        foreach ($this->totals[self::ADP_KEY_COUPONS] as $key => $data) {
            if ($key === 'wc_single') {
                foreach ($data as $code => $couponData) {
                    if ($coupon = $this->getWcSingleCouponFromData($couponData)) {
                        $wcSingleCoupons[$code] = $coupon;
                    }
                }
            }
        }

        return $wcSingleCoupons;
    }

    /**
     * @return array<int, string>
     */
    public function getAdpCoupons()
    {
        if ( ! isset($this->totals[self::ADP_KEY_COUPONS])) {
            return array();
        }

        return array_unique(
            array_merge(
                array_keys($this->totals[self::ADP_KEY_COUPONS]['group']),
                array_keys($this->totals[self::ADP_KEY_COUPONS]['single']),
                array_keys($this->totals[self::ADP_KEY_COUPONS]['wc_single'])
            )
        );
    }

    /**
     * @return array<int, string>
     */
    public function getCustomAdpCoupons()
    {
        if ( ! isset($this->totals[self::ADP_KEY_COUPONS])) {
            return array();
        }

        return array_unique(
            array_merge(
                array_keys($this->totals[self::ADP_KEY_COUPONS]['group']),
                array_keys($this->totals[self::ADP_KEY_COUPONS]['single'])
            )
        );
    }

    /**
     * @param array<int, ShippingAdjustment> $adjustments
     */
    public function insertShippingData($adjustments)
    {
        $adjustmentData = array();

        foreach ($adjustments as $adjustment) {
            $adjustmentData[] = array(
                'type'   => $adjustment->getType(),
                'value'  => $adjustment->getValue(),
                'ruleId' => $adjustment->getRuleId(),
                'amount' => $adjustment->getAmount(),
            );
        }

        $this->totals[self::ADP_KEY_SHIPPING] = $adjustmentData;
    }

    /**
     * @return array<int, ShippingAdjustment>
     */
    public function getShippingAdjustments()
    {
        if ( ! isset($this->totals[self::ADP_KEY_SHIPPING])) {
            return array();
        }

        $adjustments = array();
        foreach ($this->totals[self::ADP_KEY_SHIPPING] as $key => $adjustmentData) {
            $adj = new ShippingAdjustment(
                adp_context(), $adjustmentData['type'], $adjustmentData['value'], $adjustmentData['ruleId']
            );
            $adj->setAmount($adjustmentData['amount']);
            $adjustments[] = $adj;
        }

        return $adjustments;
    }

    /**
     * @param array $initialTotals
     */
    public function insertInitialTotals($initialTotals)
    {
        $this->totals[self::ADP_KEY_INITIAL_TOTALS] = $initialTotals;
    }

    /**
     * @return array
     */
    public function getInitialTotals()
    {
        if ( ! isset($this->totals[self::ADP_KEY_INITIAL_TOTALS])) {
            return array();
        }

        return $this->totals[self::ADP_KEY_INITIAL_TOTALS];
    }

    /**
     * @param array $regularTotals
     */
    public function insertRegularTotals($regularTotals)
    {
        $this->totals[self::ADP_KEY_REGULAR_TOTALS] = $regularTotals;
    }

    /**
     * @return array
     */
    public function getRegularTotals()
    {
        if ( ! isset($this->totals[self::ADP_KEY_REGULAR_TOTALS])) {
            return array();
        }

        return $this->totals[self::ADP_KEY_REGULAR_TOTALS];
    }

    public function insertCurrency($currencyCode)
    {
        $this->totals[self::ADP_KEY_CURRENCY] = $currencyCode;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        if ( ! isset($this->totals[self::ADP_KEY_CURRENCY])) {
            return adp_context()->getCurrencyCode();
        }

        return $this->totals[self::ADP_KEY_CURRENCY];
    }

    /**
     * @param CustomerTax|null $customerTaxAdj
     */
    public function insertCustomerTaxAdj($customerTaxAdj)
    {
        if ( ! $customerTaxAdj ) {
            return;
        }

        $this->totals[self::ADP_CUSTOMER_TAX_AJD] = [
            "withTax" => $customerTaxAdj->isWithTax(),
            "ruleId" => $customerTaxAdj->getRuleId(),
        ];
    }

    public function getCustomerTaxAdj()
    {
        if (!isset($this->totals[self::ADP_CUSTOMER_TAX_AJD])) {
            return null;
        }

        $data = $this->totals[self::ADP_CUSTOMER_TAX_AJD];
        return new CustomerTax($data['withTax'], $data['ruleId']);
    }
}
