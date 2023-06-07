<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Compatibility\PhoneOrdersCmp;

class AutoAddCartItem
{
    /**
     * @var \WC_Product
     */
    protected $product;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var float
     */
    protected $originalPrice;

    /**
     * @var float
     */
    public $qty;

    /**
     * @var float
     */
    protected $qtyAlreadyInWcCart;

    /**
     * @var bool
     */
    protected $replaceWithCoupon;

    /**
     * @var string
     */
    protected $replaceCouponCode;

    /**
     * @var bool
     */
    protected $canBeRemoved;

    /**
     * @var bool
     */
    protected $isRecommended;

    /**
     * @var int
     */
    protected $ruleId;

    /**
     * @var array
     */
    public $originalWcCartItem = array();

    /**
     * @var int
     */
    protected $pos;

    /**
     * @var string
     */
    protected $associatedHash;

    /**
     * @var array
     */
    protected $variation;

    /**
     * @var array
     */
    protected $cartItemData;

    /**
     * @var \WC_Product_Variable|null
     */
    protected $parentProduct;

    /**
     * @param \WC_Product $product
     * @param float $qty
     * @param int $ruleId
     * @param string $associatedHash
     *
     * @throws \Exception
     */
    public function __construct($product, $qty, $ruleId, $associatedHash)
    {
        if ( ! ($product instanceof \WC_Product)) {
            throw new \Exception(sprintf("Unsupported class of the product: %s", gettype($product)));
        }

        $this->product            = $product;
        $this->qty                = floatval($qty);
        $this->ruleId             = $ruleId;
        $this->associatedHash = $associatedHash;
        $this->qtyAlreadyInWcCart = 0;
        $this->replaceWithCoupon  = false;
        $this->replaceCouponCode  = '';
        $this->canBeRemoved       = true;
        $this->isRecommended  = false;

        $priceMode = adp_context()->getOption('discount_for_onsale');

        if ($product->is_on_sale('edit')) {
            if ('sale_price' === $priceMode || 'discount_sale' === $priceMode) {
                $this->originalPrice = $product->get_sale_price('edit');
            } else {
                $this->originalPrice = $product->get_regular_price('edit');
            }
        } else {
            $this->originalPrice = $product->get_price('edit');
        }

        $this->price = $this->originalPrice;
        $this->originalPrice = (float)$this->originalPrice;

        if ($product->get_parent_id()) {
            $this->parentProduct = CacheHelper::getWcProduct($product->get_parent_id());
        }

        if ($product instanceof \WC_Product_Variation) {
            $this->setVariation($product->get_variation_attributes());
        } else {
            $this->variation = array();
        }

        $this->cartItemData = array();
    }

    public function __clone() {
        $this->product = CacheHelper::getWcProduct($this->product->get_id());
        $this->parentProduct = $this->parentProduct ? CacheHelper::getWcProduct($this->product->get_parent_id()) : null;
    }

    public function setReplaceWithCoupon($replace)
    {
        $this->replaceWithCoupon = boolval($replace);
    }

    public function isReplaceWithCoupon()
    {
        return $this->replaceWithCoupon;
    }

    public function setCanBeRemoved($canBeRemoved) {
        $this->canBeRemoved = boolval($canBeRemoved);
    }

    public function canBeRemoved() {
        return $this->canBeRemoved;
    }

    public function setIsRecommended($isRecommended) {
        $this->isRecommended = boolval($isRecommended);
    }

    public function isRecommended() {
        return $this->isRecommended;
    }

    public function setQtyAlreadyInWcCart($qty)
    {
        $this->qtyAlreadyInWcCart = $qty;
    }

    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * @return bool
     */
    public function getQtyAlreadyInWcCart()
    {
        return $this->qtyAlreadyInWcCart;
    }

    /**
     * @return \WC_Product
     */
    public function getProduct(): \WC_Product
    {
        return $this->product;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function hash()
    {
        $cartItemData = $this->cartItemData;
        unset($cartItemData[PhoneOrdersCmp::CART_ITEM_SKIP_KEY]);

        $data = array(
            $this->product->get_id(),
            $this->product->get_parent_id(),
            $this->ruleId,
//            $this->initialPrice,
            $this->replaceWithCoupon,
            $this->replaceCouponCode,
            $this->associatedHash,
            $this->variation,
            $this->isRecommended,
            $cartItemData,
        );

        return md5(json_encode($data));
    }

    /**
     * @param int $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param float $qty
     */
    public function setQty($qty)
    {
        if (is_numeric($qty)) {
            $this->qty = floatval($qty);
        }
    }

    /**
     * @return string
     */
    public function getReplaceCouponCode()
    {
        return $this->replaceCouponCode;
    }

    /**
     * @param string $replaceCouponCode
     */
    public function setReplaceCouponCode($replaceCouponCode)
    {
        $this->replaceCouponCode = $replaceCouponCode;
    }

    /**
     * @return string
     */
    public function getAssociatedHash()
    {
        return $this->associatedHash;
    }

    /**
     * @param array<int, string> $variation
     */
    public function setVariation($variation)
    {
        if ( ! is_array($variation)) {
            return;
        }

        if ( ! ($this->product instanceof \WC_Product_Variation) || ! ($this->parentProduct instanceof \WC_Product_Variable)) {
            return;
        }
        $parentAttributes = $this->parentProduct->get_variation_attributes();

        foreach ($parentAttributes as $attributeName => $values) {
            $attributeName = 'attribute_' . sanitize_title($attributeName);
            if (empty($variation[$attributeName])) {
                $variation[$attributeName] = reset($values);
            }
        }

        $this->variation = $variation;
    }

    /**
     * @return array<int, string>
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @param array $cartItemData
     */
    public function setCartItemData($cartItemData)
    {
        $this->cartItemData = $cartItemData;
    }

    /**
     * @return array
     */
    public function getCartItemData()
    {
        return $this->cartItemData;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }
}
