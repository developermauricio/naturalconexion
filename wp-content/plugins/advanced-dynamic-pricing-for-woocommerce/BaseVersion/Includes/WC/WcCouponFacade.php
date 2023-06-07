<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Coupon\CouponInterface;

defined('ABSPATH') or exit;

class WcCouponFacade
{
    const TYPE_PERCENT = 'percent';
    const TYPE_FIXED_CART = 'fixed_cart';
    const TYPE_FIXED_PRODUCT = 'fixed_product';

    const TYPE_CUSTOM_PERCENT_WITH_LIMIT = 'wdp_percent_limit_coupon';
    const TYPE_ADP_FIXED_CART_ITEM = 'adp_fixed_cart_item';
    const TYPE_ADP_RULE_TRIGGER = 'adp_rule_trigger';

    const KEY_ADP = 'adp';
    const KEY_ADP_PARTS = 'parts';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \WC_Coupon
     */
    public $coupon;

    /**
     * @var array<int, CouponInterface>
     */
    protected $parts;

    /**
     * @param Context|\WC_Coupon $context
     * @param null $deprecated
     */
    public function __construct($couponOrContext, $deprecated = null)
    {
        $this->context = adp_context();
        $this->coupon  = $couponOrContext instanceof \WC_Coupon ? $couponOrContext : $deprecated;

        $this->parts = array();
        $this->fetchData();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    protected function fetchData()
    {
        $adpMeta = $this->coupon->get_meta(self::KEY_ADP, true);

        $this->parts = isset($adpMeta[self::KEY_ADP_PARTS]) ? $adpMeta[self::KEY_ADP_PARTS] : array();
    }

    /**
     * @return array<int, CouponInterface>
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @param array<int, CouponInterface> $parts
     */
    public function setParts($parts)
    {
        $this->parts = array();

        foreach ($parts as $part) {
            if ($part instanceof CouponInterface) {
                $this->parts[] = $part;
            }
        }
    }

    public function updateCoupon()
    {
        $this->coupon->update_meta_data(self::KEY_ADP, array(
            self::KEY_ADP_PARTS => $this->parts,
        ));
    }
}
