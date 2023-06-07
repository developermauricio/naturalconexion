<?php

namespace ADP\BaseVersion\Includes\WC;

use ADP\BaseVersion\Includes\Core\Cart\ShippingAdjustment;
use WC_Shipping_Rate;

defined('ABSPATH') or exit;

class WcShippingRateFacade
{
    const META_KEY_INITIAL_COST = 'adp_initial_cost';
    const META_KEY_INITIAL_COST_TAXES = 'adp_initial_cost_taxes';
    const META_KEY_TYPE = 'adp_type';
    const META_KEY_ADJUSTMENTS = 'adp_adjustments';

    const TYPE_FREE = 'free';
    const TYPE_COMMON = 'common';

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var float
     */
    protected $initialPrice;

    /**
     * @var array
     */
    protected $initialPriceTaxes;

    /**
     * @var array<int, ShippingAdjustment>
     */
    protected $adjustments;

    /**
     * @var WC_Shipping_Rate
     */
    protected $rate;

    /**
     * @param WC_Shipping_Rate $rate
     */
    public function __construct(WC_Shipping_Rate $rate)
    {
        $this->rate        = clone $rate;
        $this->adjustments = array();

        $meta = $this->rate->get_meta_data();

        if (isset($meta[self::META_KEY_INITIAL_COST], $meta[self::META_KEY_INITIAL_COST_TAXES], $meta[self::META_KEY_TYPE], $meta[self::META_KEY_ADJUSTMENTS])) {
            $this->initialPrice      = floatval($meta[self::META_KEY_INITIAL_COST]);
            $this->initialPriceTaxes = (array)$meta[self::META_KEY_INITIAL_COST_TAXES];
            $this->type              = (string)$meta[self::META_KEY_TYPE];
            $this->adjustments       = (array)$meta[self::META_KEY_ADJUSTMENTS];
        } else {
            $this->initialPrice      = floatval($rate->get_cost());
            $this->initialPriceTaxes = (array)$rate->get_taxes();
            $this->type              = null;
        }
    }

    public function sanitize()
    {
        $cost  = $this->initialPrice;
        $taxes = $this->initialPriceTaxes;

        $this->initialPrice      = null;
        $this->initialPriceTaxes = null;
        $this->type              = null;
        $this->adjustments       = array();

        $this->rate->add_meta_data(self::META_KEY_INITIAL_COST, null);
        $this->rate->add_meta_data(self::META_KEY_INITIAL_COST_TAXES, null);
        $this->rate->add_meta_data(self::META_KEY_TYPE, null);
        $this->rate->set_cost($cost);
        $this->rate->set_taxes($taxes);
    }

    /**
     * @param ShippingAdjustment $adjustment
     */
    public function applyAdjustment(ShippingAdjustment $adjustment)
    {
        if ($adjustment instanceof ShippingAdjustment) {
            $this->adjustments[] = $adjustment;
            if ($adjustment->isType($adjustment::TYPE_FREE)) {
                $this->type = self::TYPE_FREE;
            } elseif ($this->type !== self::TYPE_FREE) {
                $this->type = self::TYPE_COMMON;
            }
        }
    }

    /**
     * @param float $newCost
     */
    public function setNewCost($newCost)
    {
        if ( ! is_float($newCost)) {
            return;
        }

        $cost = $this->rate->get_cost();

        // recalculate taxes
        if ($cost > 0) {
            $percentage = $newCost / $cost;
            $taxes      = $this->rate->get_taxes();
            foreach ($taxes as $k => $v) {
                $taxes[$k] = $v * $percentage;
            }
            $this->rate->set_taxes($taxes);
        } else {
            $this->rate->set_taxes(array());
        }

        $this->rate->set_cost($newCost);
    }

    /**
     * Ignore that second argument of 'add_meta_data' method requires type 'string'
     */
    public function modifyMeta()
    {
        $this->rate->add_meta_data(self::META_KEY_INITIAL_COST, $this->initialPrice);
        $this->rate->add_meta_data(self::META_KEY_INITIAL_COST_TAXES, $this->initialPriceTaxes);
        $this->rate->add_meta_data(self::META_KEY_TYPE, $this->type);
        $this->rate->add_meta_data(self::META_KEY_ADJUSTMENTS, $this->adjustments);
    }

    public function isAffected()
    {
        $meta = $this->rate->get_meta_data();

        return isset($meta[self::META_KEY_INITIAL_COST], $meta[self::META_KEY_INITIAL_COST_TAXES], $meta[self::META_KEY_TYPE], $meta[self::META_KEY_ADJUSTMENTS]);
    }

    /**
     * @return float
     */
    public function getInitialPrice()
    {
        return $this->initialPrice;
    }

    /**
     * @return array
     */
    public function getInitialPriceTaxes()
    {
        return $this->initialPriceTaxes;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isType($type)
    {
        return $this->type === $type;
    }

    public function getRate(): WC_Shipping_Rate
    {
        return $this->rate;
    }

    /**
     * @return array<int, ShippingAdjustment>
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }
}
