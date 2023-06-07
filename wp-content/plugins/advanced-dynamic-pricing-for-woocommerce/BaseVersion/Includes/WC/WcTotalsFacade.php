<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCartItem;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponCart;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponInterface;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\WcCouponCart;
use ADP\BaseVersion\Includes\Core\Cart\Fee;
use ADP\BaseVersion\Includes\Core\Cart\ShippingAdjustment;
use WC_Cart;

defined('ABSPATH') or exit;

/**
 * @deprecated
 */
class WcTotalsFacade
{
    const KEY_TOTALS_ADP = 'adp';

    const KEY_FEE = 'fee';
    const KEY_COUPONS = 'coupons';
    const KEY_SHIPPING = 'shipping';
    const KEY_INITIAL_TOTALS = 'initial_totals';
    const KEY_REGULAR_TOTALS = 'regular_totals';
    const KEY_CURRENCY = 'currency';

    /**
     * @var WC_Cart|null
     */
    protected $wcCart;

    /**
     * @var Context
     */
    protected $context;

    /**
     * WcTotalsFacade constructor.
     *
     * @param WC_Cart|null|Context $wcCartOrContext
     * @param WC_Cart|null $deprecated
     */
    public function __construct($wcCartOrContext, $deprecated = null)
    {
        $this->context = adp_context();
        $this->wcCart  = $wcCartOrContext instanceof WC_Cart ? $wcCartOrContext : $deprecated;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param array<int, Fee> $fees
     */
    public function insertFeesData($fees)
    {
        if ( ! $this->wcCart) {
            return;
        }

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

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP])) {
            $totals[self::KEY_TOTALS_ADP] = array();
        }
        $totals[self::KEY_TOTALS_ADP][self::KEY_FEE] = $listOfFees;
        $this->wcCart->set_totals($totals);
    }

    /**
     * @return array<int, Fee>
     */
    public function getFees()
    {
        if ( ! $this->wcCart) {
            return array();
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP], $totals[self::KEY_TOTALS_ADP][self::KEY_FEE])) {
            return array();
        }

        $fees = array();
        foreach ($totals[self::KEY_TOTALS_ADP][self::KEY_FEE] as $feeData) {
            $fee = new Fee(
                $this->context,
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
     * @param array<string, CouponInterface> $singleCoupons
     * @param array<string, CouponInterface> $wcSingleCoupons
     */
    public function insertCouponsData($groupedCoupons, $singleCoupons, $wcSingleCoupons)
    {
        if ( ! $this->wcCart) {
            return;
        }

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

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP])) {
            $totals[self::KEY_TOTALS_ADP] = array();
        }
        $totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS] = array(
            'group'     => $groupCouponsData,
            'single'    => $singleCouponsData,
            'wc_single' => $wcSingleCouponsData,
        );
        $this->wcCart->set_totals($totals);
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
                'code'             => $coupon->getCode(),
                'ruleId'           => $coupon->getRuleId(),
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

        $type = $data['type'];

        if (in_array($type, CouponCart::AVAILABLE_TYPES)) {
            $coupon = new CouponCart($this->context, $type, $data['code'], $data['value'], $data['ruleId']);
        } else {
            if (isset($this->wcCart->cart_contents[$data['affectedCartItem']])) {
                $affectedCartItem = new WcCartItemFacade($this->context,
                    $this->wcCart->cart_contents[$data['affectedCartItem']], $data['affectedCartItem']);
                $affectedCartItem->setQty($data['affectedQty']);
            } else {
                $affectedCartItem = null;
            }

            $coupon = new CouponCartItem(
                $this->context,
                $type,
                $data['code'],
                $data['value'],
                $data['ruleId'],
                $affectedCartItem
            );
        }

        return $coupon;
    }

    /**
     * @return array<int, array<int, CouponInterface>>
     */
    public function getGroupedCoupons()
    {
        if ( ! $this->wcCart) {
            return array();
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP], $totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS])) {
            return array();
        }

        $groupedCoupons = array();
        foreach ($totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS] as $key => $data) {
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
        if ( ! $this->wcCart) {
            return array();
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP], $totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS])) {
            return array();
        }

        $singleCoupons = array();
        foreach ($totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS] as $key => $data) {
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
     * @return array<int, string>
     */
    public function getAdpCoupons()
    {
        if ( ! $this->wcCart) {
            return array();
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP], $totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS])) {
            return array();
        }

        return array_unique(
            array_merge(
                array_keys($totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS]['group']),
                array_keys($totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS]['single']),
                array_keys($totals[self::KEY_TOTALS_ADP][self::KEY_COUPONS]['wc_single'])
            )
        );
    }

    /**
     * @param array<int, ShippingAdjustment> $adjustments
     */
    public function insertShippingData($adjustments)
    {
        if ( ! $this->wcCart) {
            return;
        }

        $adjustmentData = array();

        foreach ($adjustments as $adjustment) {
            $adjustmentData[] = array(
                'type'   => $adjustment->getType(),
                'value'  => $adjustment->getValue(),
                'ruleId' => $adjustment->getRuleId(),
                'amount' => $adjustment->getAmount(),
            );
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP])) {
            $totals[self::KEY_TOTALS_ADP] = array();
        }
        $totals[self::KEY_TOTALS_ADP][self::KEY_SHIPPING] = $adjustmentData;
        $this->wcCart->set_totals($totals);
    }

    /**
     * @return array<int, ShippingAdjustment>
     */
    public function getShippingAdjustments()
    {
        if ( ! $this->wcCart) {
            return array();
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP], $totals[self::KEY_TOTALS_ADP][self::KEY_SHIPPING])) {
            return array();
        }

        $adjustments = array();
        foreach ($totals[self::KEY_TOTALS_ADP][self::KEY_SHIPPING] as $key => $adjustmentData) {
            $adj = new ShippingAdjustment(
                $this->context,
                $adjustmentData['type'],
                $adjustmentData['value'],
                $adjustmentData['ruleId']
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
        if ( ! $this->wcCart) {
            return;
        }

        unset($initialTotals[self::KEY_TOTALS_ADP]);
        $totals = $this->wcCart->get_totals();

        if ( ! isset($totals[self::KEY_TOTALS_ADP])) {
            $totals[self::KEY_TOTALS_ADP] = array();
        }
        $totals[self::KEY_TOTALS_ADP][self::KEY_INITIAL_TOTALS] = $initialTotals;
        $this->wcCart->set_totals($totals);
    }

    /**
     * @return array
     */
    public function getInitialTotals()
    {
        if ( ! $this->wcCart) {
            return array();
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP], $totals[self::KEY_TOTALS_ADP][self::KEY_INITIAL_TOTALS])) {
            return array();
        }

        return $totals[self::KEY_TOTALS_ADP][self::KEY_INITIAL_TOTALS];
    }

    /**
     * @param array $regularTotals
     */
    public function insertRegularTotals($regularTotals)
    {
        if ( ! $this->wcCart) {
            return;
        }

        unset($regularTotals[self::KEY_TOTALS_ADP]);
        $totals = $this->wcCart->get_totals();

        if ( ! isset($totals[self::KEY_TOTALS_ADP])) {
            $totals[self::KEY_TOTALS_ADP] = array();
        }
        $totals[self::KEY_TOTALS_ADP][self::KEY_REGULAR_TOTALS] = $regularTotals;
        $this->wcCart->set_totals($totals);
    }

    /**
     * @return array
     */
    public function getRegularTotals()
    {
        if ( ! $this->wcCart) {
            return array();
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP], $totals[self::KEY_TOTALS_ADP][self::KEY_REGULAR_TOTALS])) {
            return array();
        }

        return $totals[self::KEY_TOTALS_ADP][self::KEY_REGULAR_TOTALS];
    }

    public function insertCurrency($currencyCode)
    {
        if ( ! $this->wcCart) {
            return;
        }

        $totals = $this->wcCart->get_totals();
        if ( ! isset($totals[self::KEY_TOTALS_ADP])) {
            $totals[self::KEY_TOTALS_ADP] = array();
        }
        $totals[self::KEY_TOTALS_ADP][self::KEY_CURRENCY] = $currencyCode;
        $this->wcCart->set_totals($totals);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        if ($this->wcCart) {
            $totals = $this->wcCart->get_totals();

            if (isset($totals[self::KEY_TOTALS_ADP][self::KEY_CURRENCY])) {
                return $totals[self::KEY_TOTALS_ADP][self::KEY_CURRENCY];
            }
        }

        return $this->context->getCurrencyCode();
    }

    /**
     * @param WC_Cart $fromWcCart
     * @param WC_Cart $toWcCart
     *
     * @return void
     */
    public static function copyAdpTotals($fromWcCart, $toWcCart)
    {
        $fromTotals = $fromWcCart->get_totals();
        $toTotals   = $toWcCart->get_totals();

        if (isset($fromTotals[self::KEY_TOTALS_ADP])) {
            $toTotals[self::KEY_TOTALS_ADP] = $fromTotals[self::KEY_TOTALS_ADP];
        }

        $toWcCart->set_totals($toTotals);
    }
}
