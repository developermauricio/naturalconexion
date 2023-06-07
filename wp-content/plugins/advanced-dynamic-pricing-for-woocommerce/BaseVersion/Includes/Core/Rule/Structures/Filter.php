<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

defined('ABSPATH') or exit;

class Filter
{
    const METHOD_EQUAL = 'eq';
    const METHOD_NOT_EQUAL = 'not_eq';
    const METHOD_IN_LIST = 'in_list';
    const METHOD_NOT_IN_LIST = 'not_in_list';

    const AVAILABLE_METHODS = array(
        self::METHOD_EQUAL,
        self::METHOD_NOT_EQUAL,
        self::METHOD_IN_LIST,
        self::METHOD_NOT_IN_LIST,
    );

    const TYPE_ANY = 'any';
    const TYPE_PRODUCT = 'products';
    const TYPE_CATEGORY = 'product_categories';
    const TYPE_CATEGORY_SLUG = 'product_category_slug';
    const TYPE_ATTRIBUTE = 'product_attributes';
    const TYPE_TAG = 'product_tags';
    const TYPE_SKU = 'product_sku';
    const TYPE_SELLERS = 'product_sellers';
    const TYPE_COLLECTIONS = 'product_collections';

    const AVAILABLE_TYPES = array(
        self::TYPE_ANY,
        self::TYPE_PRODUCT,
        self::TYPE_CATEGORY,
        self::TYPE_CATEGORY_SLUG,
        self::TYPE_ATTRIBUTE,
        self::TYPE_TAG,
        self::TYPE_SKU,
        self::TYPE_SELLERS,
        self::TYPE_COLLECTIONS,
    );

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var bool
     */
    protected $excludeWcOnSale;

    /**
     * @var bool
     */
    protected $excludeAlreadyAffected;

    /**
     * @var bool
     */
    protected $excludeBackorder;

    /**
     * @var bool
     */
    protected $excludeMatchedPreviousFilters;

    /**
     * @var int[]
     */
    protected $excludeProductIds;

    /**
     * @var int
     */
    protected $collectedQtyInCart;

    public function __construct()
    {
        $this->method = self::METHOD_IN_LIST;
        $this->excludeWcOnSale = false;
        $this->excludeAlreadyAffected = false;
        $this->excludeBackorder = false;
        $this->excludeMatchedPreviousFilters = false;
        $this->collectedQtyInCart = 0;
    }

    public function isValid()
    {
        return isset($this->type, $this->method);
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType($type)
    {
        /**
         * Do not check because of custom taxonomies.
         * @see \ADP\BaseVersion\Includes\Core\RuleProcessor\ProductFiltering::checkProductSuitability()
         */
        $this->type = $type;

        return $this;
    }

    /**
     * @param string|null $method
     *
     * @return self
     */
    public function setMethod($method)
    {
        if (in_array($method, self::AVAILABLE_METHODS)) {
            $this->method = $method;
        }

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param bool $excludeWcOnSale
     */
    public function setExcludeWcOnSale($excludeWcOnSale)
    {
        $this->excludeWcOnSale = boolval($excludeWcOnSale);
    }

    /**
     * @return bool
     */
    public function isExcludeWcOnSale()
    {
        return $this->excludeWcOnSale;
    }

    /**
     * @param bool $excludeAlreadyAffected
     */
    public function setExcludeAlreadyAffected($excludeAlreadyAffected)
    {
        $this->excludeAlreadyAffected = boolval($excludeAlreadyAffected);
    }

    /**
     * @return bool
     */
    public function isExcludeAlreadyAffected()
    {
        return $this->excludeAlreadyAffected;
    }

    /**
     * @param array<int,int> $excludeProductIds
     */
    public function setExcludeProductIds($excludeProductIds)
    {
        $this->excludeProductIds = $excludeProductIds;
    }

    /**
     * @return bool
     */
    public function isExcludeBackorder()
    {
        return $this->excludeBackorder;
    }

    /**
     * @param bool $excludeBackorder
     */
    public function setExcludeBackorder($excludeBackorder)
    {
        $this->excludeBackorder = boolval($excludeBackorder);
    }

    /**
     * @return bool
     */
    public function isExcludeMatchedPreviousFilters()
    {
        return $this->excludeMatchedPreviousFilters;
    }

    /**
     * @param bool $excludeMatchedPreviousFilters
     */
    public function setExcludeMatchedPreviousFilters($excludeMatchedPreviousFilters)
    {
        $this->excludeMatchedPreviousFilters = $excludeMatchedPreviousFilters;
    }

    /**
     * @return array<int,int>|null
     */
    public function getExcludeProductIds()
    {
        return $this->excludeProductIds;
    }

    /**
     * @param int $collectedQty
     */
    public function setCollectedQtyInCart($collectedQty)
    {
        $this->collectedQtyInCart = $collectedQty;
    }

    /**
     * @return int
     */
    public function getCollectedQtyInCart()
    {
        return $this->collectedQtyInCart;
    }
}
