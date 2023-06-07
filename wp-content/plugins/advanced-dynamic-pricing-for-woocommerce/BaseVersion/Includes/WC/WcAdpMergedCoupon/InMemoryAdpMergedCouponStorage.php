<?php

namespace ADP\BaseVersion\Includes\WC\WcAdpMergedCoupon;

class InMemoryAdpMergedCouponStorage
{
    /**
     * @var self
     */
    protected static $instance = null;

    /** @var array<string, WcAdpMergedCoupon> */
    protected $data;

    protected function __construct()
    {
        $this->data = [];
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return static::$instance;
    }

    public function insertOrUpdate(WcAdpMergedCoupon $wcAdpMergedCoupon)
    {
        $this->data[$wcAdpMergedCoupon->getCode()] = $wcAdpMergedCoupon;
    }

    /** @return WcAdpMergedCoupon|null */
    public function getByCodeOrNull(string $code)
    {
        return $this->data[$code] ?? null;
    }

    public function purge()
    {
        $this->data = [];
    }

    public function getAllKeys()
    {
        return array_keys($this->data);
    }
}
