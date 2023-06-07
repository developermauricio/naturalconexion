<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

defined('ABSPATH') or exit;

class PackageItem
{
    /**
     * @var float
     */
    protected $qty;

    /**
     * @var float
     */
    protected $qtyEnd;

    /**
     * @var array<int, PackageItemFilter>
     */
    protected $filters;

    /**
     * @var array<int,Filter>
     */
    protected $excludes;

    /**
     * @var string
     */
    protected $limitation;

    const LIMITATION_NONE = 'none';
    const LIMITATION_SAME_PRODUCT = 'product';
    const LIMITATION_UNIQUE_PRODUCT = 'unique_product';
    const LIMITATION_SAME_VARIATION = 'variation';
    const LIMITATION_UNIQUE_VARIATION = 'unique_variation';
    const LIMITATION_SAME_HASH = 'same_hash';
    const LIMITATION_UNIQUE_HASH = 'unique';

    const AVAILABLE_LIMITATIONS = array(
        self::LIMITATION_NONE,
        self::LIMITATION_SAME_PRODUCT,
        self::LIMITATION_UNIQUE_PRODUCT,
        self::LIMITATION_SAME_VARIATION,
        self::LIMITATION_UNIQUE_VARIATION,
        self::LIMITATION_SAME_HASH,
        self::LIMITATION_UNIQUE_HASH,
    );

    public function __construct()
    {
        $this->filters    = array();
        $this->excludes   = array();
        $this->qty        = floatval(0);
        $this->limitation = self::LIMITATION_NONE;
    }

    public function setQty($qty)
    {
        $qty = floatval($qty);

        if ($qty > 0) {
            $this->qty = $qty;
        }
    }

    public function setQtyEnd($qty)
    {
        $qty = floatval($qty);

        if ($qty > 0) {
            $this->qtyEnd = $qty;
        }
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @return float|null
     */
    public function getQtyEnd()
    {
        return $this->qtyEnd;
    }

    /**
     * @param PackageItemFilter $filter
     */
    public function addFilter($filter)
    {
        if ($filter instanceof PackageItemFilter) {
            $this->filters[] = $filter;
        }
    }

    /**
     * @param array<int,PackageItemFilter> $filters
     */
    public function setFilters($filters)
    {
        $this->filters = array();

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @return array<int,PackageItemFilter>
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param Filter $filter
     */
    public function addExclude($filter)
    {
        if ($filter instanceof Filter) {
            $this->excludes[] = $filter;
        }
    }

    /**
     * @param array<int,Filter> $filters
     */
    public function setExcludes($filters)
    {
        $this->excludes = array();

        foreach ($filters as $filter) {
            $this->addExclude($filter);
        }
    }

    /**
     * @return array<int,Filter>
     */
    public function getExcludes()
    {
        return $this->excludes;
    }

    /**
     * @param string $limitation
     */
    public function setLimitation($limitation)
    {
        $this->limitation = in_array($limitation, self::AVAILABLE_LIMITATIONS) ? $limitation : null;
    }

    /**
     * @return string
     */
    public function getLimitation()
    {
        return $this->limitation;
    }
}
